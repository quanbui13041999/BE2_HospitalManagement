<?php

namespace App\Services;

use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentRescheduleMail;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\User\SlotHoldService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\ActivityLogService;

/**
 * AppointmentService
 *
 * Xử lý toàn bộ business logic liên quan tới appointment.
 * Tích hợp SlotHoldService để xác nhận hold trước khi tạo lịch chính thức.
 */
class AppointmentService
{
    public function __construct(protected SlotHoldService $slotHoldService)
    {
    }

    // ─────────────────────────────────────────────────────────────
    // FORM DATA
    // ─────────────────────────────────────────────────────────────

    public function getCreateFormData(): array
    {
        $user = Auth::user();

        $departments = DB::table('departments')
            ->where('status', 1)
            ->orderBy('department_name')
            ->get();

        $services = DB::table('services')
            ->leftJoin('serviceprices', function ($join) {
                $join->on('serviceprices.service_id', '=', 'services.service_id')
                    ->where('serviceprices.price_type', 'Thường')
                    ->whereNull('serviceprices.end_date');
            })
            ->where('services.status', 1)
            ->select('services.*', 'serviceprices.price')
            ->orderBy('services.service_name')
            ->get();

        $doctorsByDept = DB::table('doctors')
            ->leftJoinSub(
                DB::table('reviews')
                    ->select(
                        'doctor_id',
                        DB::raw('ROUND(AVG(rating),1) as avg_rating'),
                        DB::raw('COUNT(*) as total_reviews')
                    )
                    ->groupBy('doctor_id'),
                'rv',
                'rv.doctor_id',
                '=',
                'doctors.doctor_id'
            )
            ->where('doctors.status', 1)
            ->select(
                'doctors.doctor_id',
                'doctors.department_id',
                'doctors.full_name',
                'doctors.experience',
                'doctors.price',
                'doctors.avatar_url',
                'doctors.bio',
                DB::raw('COALESCE(rv.avg_rating, 0) as avg_rating'),
                DB::raw('COALESCE(rv.total_reviews, 0) as total_reviews')
            )
            ->get()
            ->groupBy('department_id')
            ->mapWithKeys(fn($group, $key) => [(string) $key => $group])
            ->toArray();

        $scheduleData = $this->getAvailableSchedules();

        return compact('departments', 'services', 'doctorsByDept', 'scheduleData', 'user');
    }

    public function getAvailableSchedules(): array
    {
        return DB::table('doctorschedules')
            ->leftJoinSub(
                DB::table('appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', SlotHoldService::HOLD_STATUS, 'Bác sĩ nghỉ'])
                    ->groupBy('schedule_id'),
                'bk',
                'bk.schedule_id',
                '=',
                'doctorschedules.schedule_id'
            )
            ->whereBetween('doctorschedules.work_date', [
                now()->toDateString(),
                now()->addDays(13)->toDateString(),
            ])
            ->where('doctorschedules.status', 'Hoạt động')
            ->select('doctorschedules.*', DB::raw('COALESCE(bk.booked_count,0) as booked_count'))
            ->get()
            ->groupBy(fn($r) => $r->doctor_id . '_' . $r->work_date)
            ->toArray();
    }

    public function getSchedulesForDoctor(int $doctorId, string $workDate): array
    {
        return DB::table('doctorschedules')
            ->leftJoinSub(
                DB::table('appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', SlotHoldService::HOLD_STATUS, 'Bác sĩ nghỉ'])
                    ->groupBy('schedule_id'),
                'bk',
                'bk.schedule_id',
                '=',
                'doctorschedules.schedule_id'
            )
            ->where('doctorschedules.doctor_id', $doctorId)
            ->where('doctorschedules.work_date', $workDate)
            ->where('doctorschedules.status', 'Hoạt động')
            ->select(
                'doctorschedules.schedule_id',
                'doctorschedules.work_date',
                'doctorschedules.start_time',
                'doctorschedules.end_time',
                'doctorschedules.max_slot',
                'doctorschedules.slot_duration',
                DB::raw('COALESCE(bk.booked_count, 0) as booked_count')
            )
            ->orderBy('doctorschedules.start_time')
            ->get()
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────
    // CREATE APPOINTMENT (tích hợp SlotHold)
    // ─────────────────────────────────────────────────────────────

    /**
     * Tạo lịch khám mới.
     *
     * Luồng:
     *  1. Xác nhận hold (nếu có) → chuyển 'Giữ slot' → 'Chờ xác nhận'
     *     Nếu không có hold → kiểm tra slot còn chỗ rồi insert mới.
     *  2. Tính queue_number, ghi notification, log, gửi email.
     *  3. Nếu ngày hôm nay → tự động check-in hàng đợi.
     *
     * @throws Exception
     */
    public function createAppointment(int $userId, array $data): array
    {
        $schedule = $this->validateSchedule($data['schedule_id'], $data['work_date']);
        if (!$schedule) {
            throw new Exception('Lịch khám không tồn tại hoặc ngày khám không khớp.');
        }

        $appointmentDatetime = $data['work_date'] . ' ' . $data['appointment_time'] . ':00';
        $appointmentEndtime  = $this->calculateAppointmentEndTime(
            $data['work_date'],
            $data['appointment_time'],
            $schedule->slot_duration ?? 15
        );
        $queueNumber = $this->calculateQueueNumber($data['schedule_id'], $data['appointment_time']);

        $appointmentId = null;

        DB::beginTransaction();
        try {
            // ── Check for duplicate INSIDE transaction to prevent race condition ──
            $alreadyBooked = DB::table('appointments')
                ->where('user_id', $userId)
                ->where('schedule_id', $data['schedule_id'])
                ->whereNotIn('status', ['Đã hủy', 'Dời lịch'])
                ->lockForUpdate()
                ->first();

            if ($alreadyBooked) {
                throw new Exception('Bạn đã đặt lịch khám cho khung giờ này rồi.');
            }

            // ── Thử xác nhận hold trước ──────────────────────────
            // NOTE: SlotHoldService::confirmHold() chỉ nhận (appointmentId, userId, ?note)
            // vì hold đang được tạo bằng POST /api/slot-hold.
            // Nếu chưa có appointmentId hold, không thể confirm theo cách hiện tại.
            // Trường hợp này sẽ bỏ qua confirmHold và xử lý theo flow “không có hold” bên dưới.
            $confirmedFromHold = 0;


            if ($confirmedFromHold > 0) {
                // Hold tồn tại và được xác nhận thành công
                $appointmentId = $confirmedFromHold;

                // Cập nhật thêm appointment_timeEnd (confirmHold không set)
                DB::table('appointments')
                    ->where('appointment_id', $appointmentId)
                    ->update(['appointment_timeEnd' => $appointmentEndtime]);

            } else {
                // Không có hold → kiểm tra slot & insert mới
                $booked = DB::table('appointments')
                    ->where('schedule_id', $data['schedule_id'])
                    ->where(function ($q) {
                        $q->whereNotIn('status', ['Đã hủy', 'Dời lịch', SlotHoldService::HOLD_STATUS])
                          ->orWhere(function ($q2) {
                              $q2->where('status', SlotHoldService::HOLD_STATUS)
                                 ->where('slot_hold_expire', '>', now());
                          });
                    })
                    ->count();

                if ($booked >= $schedule->max_slot) {
                    throw new Exception('Khung giờ này đã hết chỗ. Vui lòng chọn giờ khác.');
                }

                $appointmentId = DB::table('appointments')->insertGetId([
                    'user_id'             => $userId,
                    'schedule_id'         => $data['schedule_id'],
                    'service_id'          => $data['service_id'] ?? null,
                    'appointment_time'    => $appointmentDatetime,
                    'appointment_timeEnd' => $appointmentEndtime,
                    'queue_number'        => $queueNumber,
                    'status'              => 'Chờ xác nhận',
                    'is_priority'         => $data['is_priority'] ?? false,
                    'priority_type'       => $data['priority_type'] ?? null,
                    'note'                => $data['note'] ?? null,
                    'created_at'          => now(),
                ]);
            }

            // ── Notification ──────────────────────────────────────
            app(NotificationService::class)->createForUser(
                $userId,
                'Đặt lịch hẹn thành công',
                'Lịch khám lúc ' . $data['appointment_time']
                    . ' ngày ' . Carbon::parse($data['work_date'])->format('d/m/Y')
                    . '. Số thứ tự: #' . $queueNumber,
                'appointment_created',
                'appointment',
                $appointmentId
            );

            $this->logAppointmentEvent('Đặt lịch khám', $appointmentId, $userId, [
                'schedule_id'      => $data['schedule_id'],
                'service_id'       => $data['service_id'] ?? null,
                'appointment_time' => $appointmentDatetime,
                'queue_number'     => $queueNumber,
            ]);

            // ── Auto check-in nếu hôm nay ─────────────────────────
            $ticket = null;
            if (Carbon::parse($data['work_date'])->isToday()) {
                $user     = User::find($userId);
                $priority = 'normal';
                if ($user && $user->date_of_birth) {
                    $age      = Carbon::parse($user->date_of_birth)->age;
                    $priority = $age >= 60 ? 'elderly' : 'normal';
                }

                $queueService = app(\App\Services\QueueService::class);
                $ticket       = $queueService->checkin([
                    'schedule_id'    => $data['schedule_id'],
                    'priority'       => $priority,
                    'appointment_id' => $appointmentId,
                    'user_id'        => $userId,
                    'patient_name'   => $user ? $user->full_name : 'Bệnh nhân',
                    'patient_phone'  => $user ? $user->phone : null,
                    'patient_email'  => $user ? $user->email : null,
                    'notes'          => $data['note'] ?? null,
                    'served_by'      => null,
                ]);
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $this->sendAppointmentConfirmationEmail($appointmentId, $userId);

        if ($ticket) {
            return [
                'appointment_id' => $appointmentId,
                'queue_number'   => $ticket->queue_number,
                'message'        => 'Đặt lịch hẹn thành công và đã được đưa vào hàng đợi khám hôm nay! Số thứ tự của bạn là: #' . $ticket->queue_number . '.',
            ];
        }

        return [
            'appointment_id' => $appointmentId,
            'queue_number'   => $queueNumber,
            'message'        => 'Đặt lịch hẹn thành công! Số thứ tự: #' . $queueNumber . '. Chúng tôi sẽ xác nhận sớm.',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    private function checkAppointmentAlreadyBooked(int $userId, int $scheduleId): bool
    {
        return DB::table('appointments')
            ->where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', SlotHoldService::HOLD_STATUS])
            ->exists();
    }

    private function validateSchedule(int $scheduleId, string $workDate): ?object
    {
        $schedule = DB::table('doctorschedules')
            ->where('schedule_id', $scheduleId)
            ->where('status', 'Hoạt động')
            ->first();

        if (!$schedule || $schedule->work_date !== $workDate) {
            return null;
        }

        return $schedule;
    }

    private function calculateQueueNumber(int $scheduleId, string $appointmentTime): int
    {
        return DB::table('appointments')
            ->where('schedule_id', $scheduleId)
            ->whereRaw("DATE_FORMAT(appointment_time, '%H:%i') = ?", [substr($appointmentTime, 0, 5)])
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', SlotHoldService::HOLD_STATUS, 'Đã khám'])
            ->count() + 1;
    }

    private function calculateAppointmentEndTime(string $workDate, string $appointmentTime, int $slotDuration = 15): string
    {
        return Carbon::parse($workDate . ' ' . $appointmentTime . ':00')
            ->addMinutes($slotDuration)
            ->format('Y-m-d H:i:s');
    }

    // ─────────────────────────────────────────────────────────────
    // USER APPOINTMENTS (giữ nguyên từ bản gốc)
    // ─────────────────────────────────────────────────────────────

    public function getUserAppointmentStats(int $userId): object
    {
        return DB::table('appointments')
            ->where('user_id', $userId)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status IN ('Chờ xác nhận','Đã xác nhận') AND appointment_time >= NOW() THEN 1 ELSE 0 END) as upcoming"),
                DB::raw("SUM(CASE WHEN status = 'Đã khám' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status IN ('Đã hủy','Dời lịch','Bác sĩ nghỉ') THEN 1 ELSE 0 END) as cancelled")
            )
            ->first();
    }

    public function getUserAppointments(int $userId, string $status = 'all', string $sort = 'desc'): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Appointment::with(['review'])
            ->join('doctorschedules as ds', 'appointments.schedule_id', '=', 'ds.schedule_id')
            ->join('doctors as d', 'ds.doctor_id', '=', 'd.doctor_id')
            ->join('departments as dep', 'd.department_id', '=', 'dep.department_id')
            ->leftJoin('services as s', 'appointments.service_id', '=', 's.service_id')
            ->leftJoin('payments as p', function ($join) {
                $join->on('appointments.appointment_id', '=', 'p.appointment_id')
                    ->whereIn('p.status', ['Thành công', 'Đã thanh toán']);
            })
            ->leftJoin('reviews as r', 'appointments.appointment_id', '=', 'r.appointment_id')
            ->select(
                'appointments.*',
                'p.status as payment_status',
                'p.payment_id',
                'ds.work_date',
                'ds.start_time',
                'ds.end_time',
                'd.full_name as doctor_name',
                'd.doctor_id',
                'dep.department_name',
                's.service_name',
                'r.review_id',
                'r.rating as review_rating',
                'r.comment as review_comment',
                'r.doctor_reply',
                'r.created_at as review_created_at'
            )
            ->where('appointments.user_id', $userId)
            // Ẩn slot đang giữ khỏi danh sách lịch hẹn của user
            ->where('appointments.status', '!=', SlotHoldService::HOLD_STATUS);

        if ($status === 'upcoming') {
            $query->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận'])
                ->where('ds.work_date', '>=', now()->toDateString());
        } elseif ($status === 'completed') {
            $query->where('appointments.status', 'Đã khám');
        } elseif ($status === 'cancelled') {
            $query->whereIn('appointments.status', ['Đã hủy', 'Dời lịch', 'Bác sĩ nghỉ']);
        }

        return $query
            ->orderBy('ds.work_date', $sort === 'asc' ? 'asc' : 'desc')
            ->orderBy('appointments.appointment_time', $sort === 'asc' ? 'asc' : 'desc')
            ->paginate(8);
    }

    public function getAppointmentForEdit(int $appointmentId, int $userId): ?object
    {
        return DB::table('appointments')
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->join('departments', 'doctors.department_id', '=', 'departments.department_id')
            ->leftJoin('services', 'appointments.service_id', '=', 'services.service_id')
            ->join('users', 'appointments.user_id', '=', 'users.user_id')
            ->where('appointments.appointment_id', $appointmentId)
            ->where('appointments.user_id', $userId)
            ->select(
                'appointments.*',
                'doctorschedules.work_date',
                'doctorschedules.start_time',
                'doctorschedules.end_time',
                'doctors.doctor_id',
                'doctors.full_name as doctor_name',
                'doctors.department_id',
                'departments.department_name',
                'services.service_name',
                'users.full_name as user_full_name',
                'users.phone as user_phone',
                'users.address as user_address',
                'users.email as user_email',
                'users.date_of_birth'
            )
            ->first();
    }

    public function getAvailableSchedulesForReschedule(int $appointmentId, int $doctorId): \Illuminate\Support\Collection
    {
        $appointment = DB::table('appointments')->where('appointment_id', $appointmentId)->first();

        return DB::table('doctorschedules')
            ->leftJoinSub(
                DB::table('appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', SlotHoldService::HOLD_STATUS, 'Bác sĩ nghỉ'])
                    ->groupBy('schedule_id'),
                'bk',
                'bk.schedule_id',
                '=',
                'doctorschedules.schedule_id'
            )
            ->where('doctorschedules.doctor_id', $doctorId)
            ->where('doctorschedules.schedule_id', '!=', $appointment->schedule_id)
            ->whereBetween('doctorschedules.work_date', [
                now()->addDay()->toDateString(),
                now()->addDays(14)->toDateString(),
            ])
            ->where('doctorschedules.status', 'Hoạt động')
            ->whereRaw('COALESCE(bk.booked_count, 0) < doctorschedules.max_slot')
            ->select('doctorschedules.*', DB::raw('COALESCE(bk.booked_count,0) as booked_count'))
            ->orderBy('doctorschedules.work_date')
            ->orderBy('doctorschedules.start_time')
            ->get();
    }

    // ─────────────────────────────────────────────────────────────
    // RESCHEDULE & CANCEL (giữ nguyên từ bản gốc)
    // ─────────────────────────────────────────────────────────────

    public function rescheduleAppointment(int $appointmentId, int $userId, array $data): array
    {
        $appointment = DB::table('appointments')
            ->where('appointment_id', $appointmentId)
            ->where('user_id', $userId)
            ->first();

        if (!$appointment) {
            throw new Exception('Không tìm thấy lịch hẹn.');
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            throw new Exception('Lịch hẹn này không thể dời.');
        }

        if ((int) $data['new_schedule_id'] === (int) $appointment->schedule_id) {
            throw new Exception('Vui lòng chọn lịch khác với lịch hiện tại.');
        }

        $newSchedule = DB::table('doctorschedules')
            ->where('schedule_id', $data['new_schedule_id'])
            ->where('status', 'Hoạt động')
            ->first();

        if (!$newSchedule) {
            throw new Exception('Lịch khám mới không hợp lệ.');
        }

        if (Carbon::parse($newSchedule->work_date)->isPast()) {
            throw new Exception('Ngày dời phải là ngày trong tương lai.');
        }

        $bookedInNew = DB::table('appointments')
            ->where('schedule_id', $data['new_schedule_id'])
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', SlotHoldService::HOLD_STATUS])
            ->count();

        if ($bookedInNew >= $newSchedule->max_slot) {
            throw new Exception('Khung giờ mới đã hết chỗ. Vui lòng chọn giờ khác.');
        }

        $newDatetime = $newSchedule->work_date . ' ' . $data['new_appointment_time'] . ':00';

        DB::beginTransaction();
        try {
            DB::table('appointments')
                ->where('appointment_id', $appointmentId)
                ->update([
                    'schedule_id'      => $data['new_schedule_id'],
                    'appointment_time' => $newDatetime,
                    'queue_number'     => $bookedInNew + 1,
                    'status'           => 'Chờ xác nhận',
                    'cancel_reason'    => $data['reschedule_reason']
                        ? 'Dời lịch: ' . $data['reschedule_reason']
                        : 'Dời sang lịch mới',
                    'rescheduled_from' => $appointment->schedule_id,
                ]);

            app(NotificationService::class)->createForUser(
                $userId,
                'Dời lịch hẹn thành công',
                'Lịch hẹn #' . $appointmentId . ' đã được dời sang '
                    . Carbon::parse($newDatetime)->format('H:i d/m/Y'),
                'appointment_rescheduled',
                'appointment',
                $appointmentId
            );

            $this->logAppointmentEvent('Dời lịch khám', $appointmentId, $userId, [
                'changes' => [
                    'schedule_id'      => ['before' => $appointment->schedule_id, 'after' => $data['new_schedule_id']],
                    'appointment_time' => ['before' => $appointment->appointment_time, 'after' => $newDatetime],
                    'status'           => ['before' => $appointment->status, 'after' => 'Chờ xác nhận'],
                ],
                'reason' => $data['reschedule_reason'] ?? null,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $this->sendAppointmentRescheduleEmail($appointmentId, $userId);

        return ['message' => 'Dời lịch hẹn thành công! Lịch mới đang chờ xác nhận.'];
    }

    public function cancelAppointment(int $appointmentId, int $userId, array $data): array
    {
        $appointment = DB::table('appointments')
            ->where('appointment_id', $appointmentId)
            ->where('user_id', $userId)
            ->first();

        if (!$appointment) {
            throw new Exception('Không tìm thấy lịch hẹn.');
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            throw new Exception('Lịch hẹn này không thể hủy (trạng thái: ' . $appointment->status . ').');
        }

        $schedule  = DB::table('doctorschedules')->where('schedule_id', $appointment->schedule_id)->first();
        $timeError = $this->checkCancelTimeAvailable($schedule);
        if ($timeError) {
            throw new Exception($timeError);
        }

        DB::beginTransaction();
        try {
            DB::table('appointments')
                ->where('appointment_id', $appointmentId)
                ->update([
                    'status'        => 'Đã hủy',
                    'cancel_reason' => $data['cancel_reason'] ?? 'Bệnh nhân tự hủy',
                ]);

            app(NotificationService::class)->createForUser(
                $userId,
                'Hủy lịch hẹn thành công',
                'Lịch hẹn #' . $appointmentId . ' đã được hủy.'
                    . ($data['cancel_reason'] ? ' Lý do: ' . $data['cancel_reason'] : ''),
                'appointment_cancelled',
                'appointment',
                $appointmentId
            );

            $this->logAppointmentEvent('Hủy lịch khám', $appointmentId, $userId, [
                'changes' => [
                    'status' => ['before' => $appointment->status, 'after' => 'Đã hủy'],
                ],
                'cancel_reason' => $data['cancel_reason'] ?? 'Bệnh nhân tự hủy',
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $this->sendAppointmentCancellationEmail($appointmentId, $userId);

        return ['message' => 'Đã hủy lịch hẹn #' . $appointmentId . ' thành công.'];
    }

    /**
     * Tự động dời lịch từ notification của bác sĩ nghỉ.
     * 
     * Quy trình:
     * 1. Xác thực appointment cũ thuộc về user
     * 2. Xác thực appointment cũ có trạng thái 'Bác sĩ nghỉ'
     * 3. Tạo appointment mới
     * 4. Cập nhật appointment cũ thành 'Dời lịch'
     * 5. Gửi email xác nhận
     * 
     * @return array { appointment_id, queue_number, message }
     */
    public function quickRescheduleFromDayOff(int $oldAppointmentId, int $newScheduleId, int $userId): array
    {
        // 1. Xác thực appointment cũ
        $oldAppt = DB::table('appointments')
            ->where('appointment_id', $oldAppointmentId)
            ->where('user_id', $userId)
            ->first();

        if (!$oldAppt) {
            throw new Exception('Không tìm thấy lịch hẹn cũ hoặc bạn không có quyền.');
        }

        if ($oldAppt->status !== 'Bác sĩ nghỉ') {
            throw new Exception('Chỉ có thể dời lịch cho appointment có trạng thái "Bác sĩ nghỉ".');
        }

        // 2. Xác thực schedule mới
        $newSchedule = DB::table('doctorschedules')
            ->where('schedule_id', $newScheduleId)
            ->where('status', '!=', 'blocked')
            ->first();

        if (!$newSchedule) {
            throw new Exception('Lịch mới không tồn tại hoặc không khả dụng.');
        }

        // 3. Kiểm tra slot còn trống
        $booked = DB::table('appointments')
            ->where('schedule_id', $newScheduleId)
            ->where(function ($q) {
                $q->whereNotIn('status', ['Đã hủy', 'Dời lịch', SlotHoldService::HOLD_STATUS])
                  ->orWhere(function ($q2) {
                      $q2->where('status', SlotHoldService::HOLD_STATUS)
                         ->where('slot_hold_expire', '>', now());
                  });
            })
            ->count();

        if ($booked >= $newSchedule->max_slot) {
            throw new Exception('Lịch này đã hết slot. Vui lòng chọn lịch khác.');
        }

        // 4. Tính toán thông tin appointment mới
        $workDate = $newSchedule->work_date;
        // Dùng start_time của schedule làm appointment_time
        $appointmentDatetime = $workDate . ' ' . $newSchedule->start_time;
        $appointmentEndtime = $this->calculateAppointmentEndTime(
            $workDate,
            substr($newSchedule->start_time, 0, 5),
            $newSchedule->slot_duration ?? 15
        );
        $queueNumber = $this->calculateQueueNumber($newScheduleId, substr($newSchedule->start_time, 0, 5));

        $newAppointmentId = null;

        DB::beginTransaction();
        try {
            // 5. Tạo appointment mới
            $newAppointmentId = DB::table('appointments')->insertGetId([
                'user_id'             => $userId,
                'schedule_id'         => $newScheduleId,
                'service_id'          => $oldAppt->service_id,
                'appointment_time'    => $appointmentDatetime,
                'appointment_timeEnd' => $appointmentEndtime,
                'queue_number'        => $queueNumber,
                'status'              => 'Chờ xác nhận',
                'is_priority'         => $oldAppt->is_priority ?? false,
                'priority_type'       => $oldAppt->priority_type ?? null,
                'note'                => ($oldAppt->note ? $oldAppt->note . ' | ' : '') 
                                      . 'Dời lịch từ: ' . $oldAppt->appointment_time,
                'created_at'          => now(),
            ]);

            // 6. Đánh dấu appointment cũ là 'Dời lịch'
            DB::table('appointments')
                ->where('appointment_id', $oldAppointmentId)
                ->update([
                    'status' => 'Dời lịch',
                    'cancel_reason' => 'Bác sĩ nghỉ - tự động dời sang lịch mới #' . $newAppointmentId,
                ]);

            // 7. Tạo notification
            app(NotificationService::class)->createForUser(
                $userId,
                'Dời lịch hẹn thành công',
                'Lịch khám của bạn đã được dời sang ngày ' 
                    . Carbon::parse($workDate)->format('d/m/Y') 
                    . ' lúc ' . substr($newSchedule->start_time, 0, 5)
                    . '. Số thứ tự mới: #' . $queueNumber,
                'appointment_rescheduled',
                'appointment',
                $newAppointmentId
            );

            $this->logAppointmentEvent('Dời lịch tự động (bác sĩ nghỉ)', $newAppointmentId, $userId, [
                'old_appointment_id' => $oldAppointmentId,
                'old_schedule_id'    => $oldAppt->schedule_id,
                'new_schedule_id'    => $newScheduleId,
            ]);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // 8. Gửi email xác nhận
        $this->sendAppointmentConfirmationEmail($newAppointmentId, $userId);

        return [
            'old_appointment_id' => $oldAppointmentId,
            'new_appointment_id' => $newAppointmentId,
            'queue_number'       => $queueNumber,
            'work_date'          => $workDate,
            'appointment_time'   => substr($newSchedule->start_time, 0, 5),
            'message'            => 'Dời lịch hẹn thành công! Lịch mới: ' 
                                  . Carbon::parse($workDate)->format('d/m/Y') 
                                  . ' lúc ' . substr($newSchedule->start_time, 0, 5) 
                                  . ' (số thứ tự: #' . $queueNumber . ')',
        ];
    }

    private function checkCancelTimeAvailable(object $schedule): ?string
    {
        $appointmentTime      = Carbon::parse($schedule->work_date . ' ' . $schedule->start_time);
        $hoursUntilAppointment = $appointmentTime->diffInHours(now(), false);

        if ($hoursUntilAppointment >= 0) {
            return 'Lịch khám này đã qua hoặc đang diễn ra. Không thể hủy lịch.';
        }
        if ($hoursUntilAppointment > -2) {
            return 'Chỉ có thể hủy lịch trước giờ khám ít nhất 2 tiếng.';
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────
    // EMAIL HELPERS (giữ nguyên từ bản gốc)
    // ─────────────────────────────────────────────────────────────

    private function sendAppointmentConfirmationEmail(int $appointmentId, int $userId): void
    {
        try {
            $appointment = DB::table('appointments')
                ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
                ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
                ->join('departments', 'doctors.department_id', '=', 'departments.department_id')
                ->where('appointments.appointment_id', $appointmentId)
                ->select('appointments.*', 'doctors.full_name as doctor_name', 'departments.department_name')
                ->first();

            $user = User::find($userId);
            if ($user && $user->email && $appointment) {
                Mail::to($user->email)->send(new AppointmentConfirmed($user, $appointment));
            }
        } catch (Exception $e) {
            Log::warning('Failed to send appointment confirmation email', [
                'appointment_id' => $appointmentId, 'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendAppointmentRescheduleEmail(int $appointmentId, int $userId): void
    {
        try {
            $appointment = Appointment::with('schedule.doctor.department')->find($appointmentId);
            $user        = User::find($userId);
            if ($user && $user->email && $appointment) {
                Mail::to($user->email)->send(new AppointmentRescheduleMail(
                    patient:      $user,
                    appointment:  $appointment,
                    doctor:       $appointment->schedule->doctor ?? null,
                    reason:       $appointment->cancel_reason ?? '',
                    type:         'leave',
                    alternatives: [],
                ));
            }
        } catch (Exception $e) {
            Log::warning('Failed to send appointment rescheduled email', [
                'appointment_id' => $appointmentId, 'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendAppointmentCancellationEmail(int $appointmentId, int $userId): void
    {
        try {
            $appointment = DB::table('appointments')
                ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
                ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
                ->leftJoin('departments', 'doctors.department_id', '=', 'departments.department_id')
                ->where('appointments.appointment_id', $appointmentId)
                ->select('appointments.*', 'doctors.full_name as doctor_name', 'departments.department_name')
                ->first();

            $user = User::find($userId);
            if ($user && $user->email && $appointment) {
                Mail::to($user->email)->send(new AppointmentCancelled($user, $appointment));
            }
        } catch (Exception $e) {
            Log::warning('Failed to send appointment cancelled email', [
                'appointment_id' => $appointmentId, 'error' => $e->getMessage(),
            ]);
        }
    }

    private function logAppointmentEvent(string $action, int $appointmentId, int $userId, array $metadata = []): void
    {
        $actor       = User::with('role')->find($userId);
        $appointment = DB::table('appointments')
            ->leftJoin('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->leftJoin('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->where('appointments.appointment_id', $appointmentId)
            ->select('appointments.appointment_time', 'appointments.queue_number', 'appointments.status', 'doctors.full_name as doctor_name')
            ->first();

        $time = $appointment?->appointment_time
            ? Carbon::parse($appointment->appointment_time)->format('H:i ngày d/m/Y')
            : 'không rõ thời gian';

        $description = match ($action) {
            'Đặt lịch khám' => ($actor?->full_name ?: 'Bệnh nhân') . ' đã đặt lịch khám với ' . ($appointment?->doctor_name ? 'BS. ' . $appointment->doctor_name : 'bác sĩ') . ' vào ' . $time . '.',
            'Dời lịch khám' => ($actor?->full_name ?: 'Bệnh nhân') . ' đã dời lịch khám #' . $appointmentId . ' sang ' . $time . '.',
            'Hủy lịch khám' => ($actor?->full_name ?: 'Bệnh nhân') . ' đã hủy lịch khám #' . $appointmentId . '.',
            default         => $action . ' #' . $appointmentId,
        };

        ActivityLogService::log(
            $action, $description, 'appointment', $appointmentId,
            array_merge($metadata, [
                'appointment_status' => $appointment?->status,
                'queue_number'       => $appointment?->queue_number,
            ]),
            'success', $actor
        );
    }
}