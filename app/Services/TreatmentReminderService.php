<?php
namespace App\Services;

use App\Models\{TreatmentReminder, Prescription, MedicalRecord, TreatmentConfirmation, InstructionDailyCheck, Appointment, TreatmentHomeInstruction};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class TreatmentReminderService
{
    /**
     * Lấy dữ liệu dashboard cho bệnh nhân.
     * Trả về: reminders hôm nay, instructions hôm nay, stats 7 ngày.
     */
    public function getDashboardData(int $userId): array
    {
        $todayReminders = TreatmentReminder::forUser($userId)
            ->today()
            ->orderBy('remind_at')
            ->with('confirmation')
            ->get();

        $instructions = TreatmentHomeInstruction::where('user_id', $userId)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        $weeklyStats = $this->getWeeklyComplianceStats($userId);
        $monthStats  = $this->getMonthComplianceStats($userId);

        // Ngày tái khám tiếp theo
        $nextAppointment = Appointment::where('user_id', $userId)
            ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận'])
            ->where('appointment_time', '>=', now())
            ->orderBy('appointment_time')
            ->first();

        return compact('todayReminders', 'instructions', 'weeklyStats', 'monthStats', 'nextAppointment');
    }

    /**
     * Xác nhận bệnh nhân đã uống thuốc / hoàn thành.
     */
    public function confirmReminder(int $reminderId, int $userId): TreatmentConfirmation
    {
        return DB::transaction(function () use ($reminderId, $userId) {
            $reminder = TreatmentReminder::where('reminder_id', $reminderId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $reminder->remind_at || ! $reminder->remind_at->isSameDay(today())) {
                throw new ConflictHttpException('Chỉ được xác nhận nhắc nhở trong ngày hôm nay. Vui lòng tải lại trang.');
            }

            $confirmed = TreatmentConfirmation::where('reminder_id', $reminderId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if ($confirmed) {
                throw new ConflictHttpException('Nhắc nhở này đã được xác nhận trước đó. Vui lòng tải lại trang.');
            }

            return TreatmentConfirmation::create([
                'reminder_id'   => $reminderId,
                'user_id'       => $userId,
                'confirmed_at'  => now(),
                'confirm_type'  => $reminder->reminder_type === 'medicine' ? 'medicine' : 'instruction',
            ]);
        });
    }

    /**
     * Toggle checkbox hướng dẫn điều trị.
     */
    public function toggleInstruction(int $instructionId, int $userId, bool $expectedState): bool
    {
        return DB::transaction(function () use ($instructionId, $userId, $expectedState) {
            TreatmentHomeInstruction::where('id', $instructionId)
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->lockForUpdate()
                ->firstOrFail();

            $check = InstructionDailyCheck::where('instruction_id', $instructionId)
                ->where('user_id', $userId)
                ->whereDate('checked_date', today())
                ->lockForUpdate()
                ->first();

            $currentState = (bool) ($check?->is_done ?? false);

            if ($currentState !== $expectedState) {
                throw new ConflictHttpException('Hướng dẫn này đã được cập nhật trước đó. Vui lòng tải lại trang.');
            }

            $newState = ! $expectedState;

            $check ??= new InstructionDailyCheck([
                'instruction_id' => $instructionId,
                'user_id'        => $userId,
                'checked_date'   => today()->toDateString(),
            ]);

            $check->is_done    = $newState;
            $check->checked_at = $newState ? now() : null;
            $check->save();

            return $check->is_done;
        });
    }

    /**
     * Tự động sinh TreatmentReminder từ Prescriptions của 1 MedicalRecord.
     */
    public function generateFromRecord(MedicalRecord $record): int
    {
        $prescriptions = $record->prescriptions;
        $userId        = $record->patient_id; // MedicalRecord uses patient_id for User
        $startDate     = now()->toDateString();
        $count         = 0;

        $timeMappings = [
            'sáng'   => '06:00:00',
            'trưa'   => '12:00:00',
            'chiều'  => '15:00:00',
            'tối'    => '18:00:00',
            'trước khi ngủ' => '21:00:00',
        ];

        foreach ($prescriptions as $rx) {
            $instructions = strtolower($rx->instructions ?? '');
            $times        = [];

            foreach ($timeMappings as $keyword => $time) {
                if (str_contains($instructions, $keyword)) {
                    $times[] = $time;
                }
            }

            if (empty($times)) {
                $times = ['08:00:00']; // mặc định sáng nếu không rõ
            }

            $durationDays = $rx->duration_days ?? 30;

            for ($day = 0; $day < $durationDays; $day++) {
                $date = Carbon::parse($startDate)->addDays($day)->toDateString();
                foreach ($times as $time) {
                    TreatmentReminder::firstOrCreate([
                        'user_id'       => $userId,
                        'record_id'     => $record->record_id,
                        'reminder_type' => 'medicine',
                        'remind_at'     => "{$date} {$time}",
                    ], [
                        'message'  => "{$rx->drug_name} {$rx->dosage} — {$rx->instructions}",
                        'is_sent'  => 0,
                    ]);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Thống kê tuân thủ 7 ngày gần nhất.
     */
    public function getWeeklyComplianceStats(int $userId): array
    {
        $days   = [];
        $labels = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

        for ($i = 6; $i >= 0; $i--) {
            $date  = now()->subDays($i);
            $label = $labels[$date->dayOfWeek];

            $total     = TreatmentReminder::forUser($userId)->whereDate('remind_at', $date)->count();
            $confirmed = TreatmentConfirmation::where('user_id', $userId)
                ->whereDate('confirmed_at', $date)->count();

            $days[$label] = [
                'total'     => $total,
                'confirmed' => $confirmed,
                'rate'      => $total > 0 ? round($confirmed / $total * 100) : 0,
                'compliant' => $total > 0 && $confirmed >= $total,
            ];
        }

        return $days;
    }

    /**
     * Thống kê tuân thủ tháng hiện tại.
     */
    public function getMonthComplianceStats(int $userId): array
    {
        $month = now()->month;
        $year  = now()->year;

        $total = TreatmentReminder::forUser($userId)
            ->whereMonth('remind_at', $month)->whereYear('remind_at', $year)->count();

        $confirmed = TreatmentConfirmation::where('user_id', $userId)
            ->whereMonth('confirmed_at', $month)->whereYear('confirmed_at', $year)->count();

        $remindersToday = TreatmentReminder::forUser($userId)->today()->count();
        $confirmedToday = TreatmentConfirmation::where('user_id', $userId)
            ->whereDate('confirmed_at', today())->count();

        $daysLeft = now()->daysInMonth - now()->day;

        return [
            'compliance_rate'    => $total > 0 ? round($confirmed / $total * 100) : 0,
            'reminders_today'    => $remindersToday,
            'completed_today'    => $confirmedToday,
            'days_left_in_month' => $daysLeft,
            'medicine_rate'      => $this->getRateByType($userId, 'medicine', $month, $year),
            'exercise_rate'      => $this->getRateByType($userId, 'instruction', $month, $year),
        ];
    }

    private function getRateByType(int $userId, string $type, int $month, int $year): int
    {
        $total = TreatmentReminder::forUser($userId)->where('reminder_type', $type)
            ->whereMonth('remind_at', $month)->whereYear('remind_at', $year)->count();
        $conf  = TreatmentConfirmation::where('user_id', $userId)->where('confirm_type', $type)
            ->whereMonth('confirmed_at', $month)->whereYear('confirmed_at', $year)->count();
        return $total > 0 ? round($conf / $total * 100) : 0;
    }
}
