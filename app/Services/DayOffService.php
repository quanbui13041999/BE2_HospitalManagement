<?php
// ═══════════════════════════════════════════════════════════════════════════════
// app/Services/DayOffService.php
// ═══════════════════════════════════════════════════════════════════════════════
namespace App\Services;

use App\Mail\AppointmentRescheduleMail;
use App\Mail\DoctorDayOffNotification;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorDayOff;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DayOffService
{
    /**
     * Luồng chính khi bác sĩ đăng ký nghỉ:
     *
     *  1. Lấy danh sách ngày cần block (1 ngày hoặc range)
     *  2. Lọc schedule theo session (all / morning / afternoon)
     *  3. Block schedule (status = 'blocked')
     *  4. Tìm appointment bị ảnh hưởng
     *  5. Tìm bác sĩ cùng khoa → gợi ý lịch thay thế
     *  6. Gửi email hỏi bệnh nhân có muốn dời lịch không
     *
     * @return array { blocked_schedules: int, affected_appointments: int, emails_sent: int }
     */
    public function process(array $data): array
    {
        $doctorId = $data['doctor_id'];
        $session  = $data['session'];     // all | morning | afternoon
        $reason   = $data['reason'] ?? '';
        $type     = $data['type'];

        $dates = $this->buildDateRange($data['date'], $data['end_date'] ?? null);

        $blockedCount  = 0;
        $affectedCount = 0;
        $emailsSent    = 0;
        $pendingEmails = [];
        $doctorNotification = null;

        // Bác sĩ cùng khoa để gợi ý
        $doctor       = Doctor::with('department', 'user')->findOrFail($doctorId);
        $alterDoctors = $doctor->sameDepDoctors();

        DB::transaction(function () use (
            $doctorId, $session, $reason, $type, $dates,
            $doctor, $alterDoctors, $data,
            &$blockedCount, &$affectedCount, &$pendingEmails, &$doctorNotification
        ) {
            foreach ($dates as $date) {
                // Tạo / ghi nhận ngày nghỉ trong bảng DoctorDaysOff
                DoctorDayOff::firstOrCreate(
                    [
                        'doctor_id' => $doctorId,
                        'off_date'  => $date,
                    ],
                    [
                        'reason'     => $reason ?: $type,
                        'created_at' => now(),
                    ]
                );

                // Lấy các schedule của bác sĩ trong ngày đó - tìm tất cả không phải chỉ active
                // vì schedule có thể có các status khác ngoài 'active', 'Hoạt động'
                /** @var EloquentCollection<int, DoctorSchedule> $schedules */
                $schedules = DoctorSchedule::forDoctor($doctorId)
                    ->where('work_date', $date)
                    ->whereNotIn('status', ['blocked', 'Đã hủy'])
                    ->get();

                Log::info('Day-off: Found all non-blocked schedules', [
                    'doctor_id' => $doctorId,
                    'date' => $date,
                    'schedule_count' => $schedules->count(),
                    'statuses' => $schedules->pluck('status')->unique()->toArray(),
                ]);

                // Lọc theo buổi nếu cần
                $toBlock = $this->filterBySession($schedules, $session);

                foreach ($toBlock as $schedule) {
                    // ── Block schedule ──────────────────────────────────────
                    $schedule->update([
                        'status' => 'blocked',
                        'note'   => "[{$type}] {$reason}",
                    ]);
                    $blockedCount++;
                    
                    Log::info('Day-off: Schedule blocked', [
                        'schedule_id' => $schedule->schedule_id,
                        'work_date' => $schedule->work_date,
                    ]);

                    // ── Xử lý appointment bị ảnh hưởng ─────────────────────
                    $affected = $schedule->activeAppointments();
                    
                    Log::info('Day-off: Affected appointments found', [
                        'schedule_id' => $schedule->schedule_id,
                        'affected_count' => $affected->count(),
                    ]);

                    foreach ($affected as $appt) {
                        // Đổi trạng thái appointment sang 'Bác sĩ nghỉ'
                        $updated = Appointment::where('appointment_id', $appt->appointment_id)
                            ->update([
                                'status'        => 'Bác sĩ nghỉ',
                                'cancel_reason' => "{$type}: {$reason}",
                            ]);
                        
                        if ($updated) {
                            $affectedCount++;
                            Log::info('Day-off: Appointment updated', [
                                'appointment_id' => $appt->appointment_id,
                                'status' => 'Bác sĩ nghỉ',
                            ]);
                        } else {
                            Log::warning('Day-off: Failed to update appointment', [
                                'appointment_id' => $appt->appointment_id,
                            ]);
                        }

                        // Gợi ý slot thay thế từ bác sĩ cùng khoa
                        $alternatives = $this->findAlternativeSlots(
                            $alterDoctors,
                            $appt->appointment_time
                        );

                        if ($appt->user && $appt->user->email) {
                            $pendingEmails[] = [
                                'email'        => $appt->user->email,
                                'patient'      => $appt->user,
                                'appointment'  => $appt,
                                'doctor'       => $doctor,
                                'reason'       => $reason,
                                'type'         => $type,
                                'alternatives' => $alternatives,
                            ];
                        }
                    }
                }
            }

            // Prepare notification email for doctor after commit
            if ($affectedCount > 0 && $doctor->user && $doctor->user->email) {
                $doctorNotification = [
                    'email' => $doctor->user->email,
                    'mail'  => new DoctorDayOffNotification(
                        doctor: $doctor,
                        data: array_merge($data, [
                            'blocked_schedules'    => $blockedCount,
                            'affected_appointments' => $affectedCount,
                        ]),
                    ),
                ];
            }
        });

        Log::info('Day-off: Sending patient reschedule emails', [
            'total_pending' => count($pendingEmails),
        ]);

        foreach ($pendingEmails as $item) {
            try {
                Mail::to($item['email'])->send(new AppointmentRescheduleMail(
                    patient:      $item['patient'],
                    appointment:  $item['appointment'],
                    doctor:       $item['doctor'],
                    reason:       $item['reason'],
                    type:         $item['type'],
                    alternatives: $item['alternatives'],
                ));
                $emailsSent++;
                Log::info('Day-off: Reschedule email sent', [
                    'email' => $item['email'],
                    'appointment_id' => $item['appointment']->appointment_id ?? null,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to send day-off reschedule email', [
                    'email' => $item['email'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        if ($doctorNotification) {
            try {
                Mail::to($doctorNotification['email'])->send($doctorNotification['mail']);
                Log::info('Day-off: Doctor notification sent', [
                    'email' => $doctorNotification['email'],
                    'affected_count' => $affectedCount,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to send day-off notification to doctor', [
                    'email' => $doctorNotification['email'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('Day-off process completed', [
            'blocked_schedules' => $blockedCount,
            'affected_appointments' => $affectedCount,
            'emails_sent' => $emailsSent,
        ]);

        return [
            'blocked_schedules'     => $blockedCount,
            'affected_appointments' => $affectedCount,
            'emails_sent'           => $emailsSent,
        ];
    }

    /**
     * Ước lượng số appointment bị ảnh hưởng bởi yêu cầu nghỉ của bác sĩ.
     *
     * Có thể dùng để hiển thị trước khi chốt ngày nghỉ.
     */
    public function estimateAffectedAppointments(array $data): int
    {
        $doctorId = $data['doctor_id'];
        $session  = $data['session'];
        $dates    = $this->buildDateRange($data['date'], $data['end_date'] ?? null);

        $count = 0;
        foreach ($dates as $date) {
            $schedules = DoctorSchedule::forDoctor($doctorId)
                ->active()
                ->where('work_date', $date)
                ->get();

            $toBlock = $this->filterBySession($schedules, $session);
            foreach ($toBlock as $schedule) {
                $count += $schedule->activeAppointments()->count();
            }
        }

        return $count;
    }

    /**
     * Lấy danh sách ngày nghỉ của bác sĩ (để hiển thị danh sách).
     *
     * @return Collection<DoctorSchedule>
     */
    public function listDayOffs(int $doctorId): Collection
    {
        return DoctorSchedule::forDoctor($doctorId)
            ->where('status', 'blocked')
            ->where('work_date', '>=', Carbon::today())
            ->orderBy('work_date')
            ->get()
            ->groupBy('work_date') // gom theo ngày để hiển thị dễ hơn
            ->map(function ($group, $date) {
                return [
                    'date'     => $date,
                    'sessions' => $group->map(fn ($s) => [
                        'schedule_id' => $s->schedule_id,
                        'start_time'  => $s->start_time,
                        'end_time'    => $s->end_time,
                        'note'        => $s->note,
                    ]),
                ];
            })
            ->values();
    }

    /**
     * Huỷ ngày nghỉ — mở lại schedule, nhưng KHÔNG tự đặt lại appointment
     * (bệnh nhân tự đặt mới sau email gợi ý).
     */
    public function cancel(int $scheduleId): bool
    {
        $schedule = DoctorSchedule::findOrFail($scheduleId);
        $schedule->update(['status' => 'Hoạt động', 'note' => null]);

        // Nếu không còn ca bị block cùng ngày thì xoá bản ghi DoctorDayOff.
        $blockedExists = DoctorSchedule::where('doctor_id', $schedule->doctor_id)
            ->where('work_date', $schedule->work_date)
            ->where('status', 'blocked')
            ->exists();

        if (!$blockedExists) {
            DoctorDayOff::where('doctor_id', $schedule->doctor_id)
                ->where('off_date', $schedule->work_date)
                ->delete();
        }

        return true;
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /** Tạo mảng ngày từ start đến end (inclusive) */
    private function buildDateRange(string $start, ?string $end): array
    {
        if (!$end || $end === $start) {
            return [$start];
        }

        $dates   = [];
        $current = Carbon::parse($start);
        $last    = Carbon::parse($end);

        while ($current->lte($last)) {
            $dates[] = $current->toDateString();
            $current->addDay();
        }
        return $dates;
    }

    /**
     * Lọc schedule theo buổi.
     *
     * Quy ước: ca sáng → start_time < '12:00:00'
     *           ca chiều → start_time >= '12:00:00'
     *
     * @param  EloquentCollection<int, DoctorSchedule>  $schedules
     * @return EloquentCollection<int, DoctorSchedule>
     */
    private function filterBySession(EloquentCollection $schedules, string $session): EloquentCollection
    {
        return match ($session) {
            'morning'   => $schedules->filter(fn ($s) => $s->start_time < '12:00:00'),
            'afternoon' => $schedules->filter(fn ($s) => $s->start_time >= '12:00:00'),
            default     => $schedules, // 'all'
        };
    }

    /**
     * Tìm slot trống của bác sĩ thay thế gần ngày bị huỷ nhất.
     *
     * Trả mảng tối đa 3 gợi ý, mỗi gợi ý gồm:
     *  { doctor, schedule, available_slots }
     *
     * @param  Collection  $alterDoctors  Bác sĩ cùng khoa
     * @param  \Carbon\Carbon  $originalTime  Thời điểm hẹn cũ
     */
    private function findAlternativeSlots(Collection $alterDoctors, $originalTime): array
    {
        $searchFrom = Carbon::parse($originalTime)->startOfDay();
        $searchTo   = $searchFrom->copy()->addDays(7);   // tìm trong vòng 7 ngày

        $suggestions = [];

        foreach ($alterDoctors as $altDoctor) {
            $schedule = DoctorSchedule::forDoctor($altDoctor->doctor_id)
                ->active()
                ->betweenDates($searchFrom->toDateString(), $searchTo->toDateString())
                ->get()
                ->filter(fn ($s) => $s->availableSlots() > 0)
                ->sortBy('work_date')
                ->first();

            if ($schedule) {
                $suggestions[] = [
                    'doctor'           => $altDoctor,
                    'schedule'         => $schedule,
                    'available_slots'  => $schedule->availableSlots(),
                ];
            }

            if (count($suggestions) >= 3) break;
        }

        return $suggestions;
    }
}