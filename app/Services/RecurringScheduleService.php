<?php
// ═══════════════════════════════════════════════════════════════════════════════
// app/Services/RecurringScheduleService.php
// ═══════════════════════════════════════════════════════════════════════════════
namespace App\Services;

use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecurringScheduleService
{
    /**
     * Tạo lịch lặp lại cho bác sĩ.
     *
     * Ý tưởng:
     *  - Duyệt từng ngày trong khoảng [hôm nay, hôm nay + apply_weeks * 7]
     *  - Nếu ngày đó trùng với 1 trong các days_of_week → tạo schedule (ca sáng + ca chiều nếu được bật)
     *  - Nếu đã tồn tại schedule cùng ngày + ca + bác sĩ → bỏ qua (tránh trùng)
     *
     * @param  array  $data  Dữ liệu đã qua validation từ StoreRecurringScheduleRequest
     * @return array  { created: int, skipped: int, schedules: Collection }
     */
    public function generate(array $data): array
    {
        $doctorId  = $data['doctor_id'];
        $roomId    = $data['room_id'] ?? null;
        $days      = $data['days_of_week'];           // e.g. [1,2,3,4,5]
        $duration  = $data['slot_duration'];
        $maxSlot   = $data['max_slot'];
        $weeks     = $data['apply_weeks'];

        // Xây danh sách các ca được bật
        $sessions = $this->buildSessions($data);

        if (empty($sessions)) {
            throw new \InvalidArgumentException('Ít nhất 1 ca phải được bật.');
        }

        $start   = Carbon::today();
        $end     = Carbon::today()->addWeeks($weeks);
        $created = 0;
        $skipped = 0;
        $newSchedules = collect();

        DB::transaction(function () use (
            $doctorId, $roomId, $days, $duration, $maxSlot,
            $start, $end, $sessions, &$created, &$skipped, &$newSchedules
        ) {
            // Lấy sẵn danh sách (work_date, start_time) đã có để check trùng
            $existing = DoctorSchedule::forDoctor($doctorId)
                ->betweenDates($start->toDateString(), $end->toDateString())
                ->whereIn('status', ['active', 'blocked'])
                ->get(['work_date', 'start_time'])
                ->map(fn ($s) => $s->work_date->toDateString() . '|' . $s->start_time)
                ->flip(); // flip → dùng làm lookup O(1)

            $current = $start->copy();
            while ($current->lte($end)) {
                // dayOfWeek: 0=CN, 1=T2 … 6=T7 (Carbon chuẩn ISO)
                if (in_array($current->dayOfWeek, $days)) {
                    foreach ($sessions as $session) {
                        $key = $current->toDateString() . '|' . $session['start'];

                        if ($existing->has($key)) {
                            $skipped++;
                        } else {
                            $schedule = DoctorSchedule::create([
                                'doctor_id'     => $doctorId,
                                'room_id'       => $roomId,
                                'work_date'     => $current->toDateString(),
                                'start_time'    => $session['start'],
                                'end_time'      => $session['end'],
                                'slot_duration' => $duration,
                                'max_slot'      => $maxSlot,
                                'status'        => 'active',
                                'note'          => $session['label'], // "Sáng" | "Chiều"
                            ]);
                            $newSchedules->push($schedule);
                            $created++;
                        }
                    }
                }
                $current->addDay();
            }
        });

        return [
            'created'   => $created,
            'skipped'   => $skipped,
            'schedules' => $newSchedules,
        ];
    }

    /**
     * Xem trước (preview) — không ghi DB, chỉ tính số lượng và ngày áp dụng.
     *
     * @return array { total_days: int, total_slots: int, apply_until: string, sessions: array }
     */
    public function preview(array $data): array
    {
        $days     = $data['days_of_week'];
        $weeks    = $data['apply_weeks'];
        $duration = $data['slot_duration'];
        $maxSlot  = $data['max_slot'];
        $sessions = $this->buildSessions($data);

        $applyUntil = Carbon::today()->addWeeks($weeks);
        $totalDays  = 0;

        $current = Carbon::today();
        while ($current->lte($applyUntil)) {
            if (in_array($current->dayOfWeek, $days)) {
                $totalDays++;
            }
            $current->addDay();
        }

        $slotsPerDay = array_sum(array_map(
            fn ($s) => $this->calcSlots($s['start'], $s['end'], $duration),
            $sessions
        ));

        return [
            'apply_until' => $applyUntil->format('d/m/Y'),
            'total_days'  => $totalDays,
            'total_slots' => $totalDays * $slotsPerDay * $maxSlot, // tổng chỗ bệnh nhân
            'slot_count'  => $totalDays * $slotsPerDay,            // tổng slot
            'sessions'    => $sessions,
        ];
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /** Xây mảng các ca (session) được bật từ $data */
    private function buildSessions(array $data): array
    {
        $sessions = [];
        if (!empty($data['morning_enabled'])) {
            $sessions[] = [
                'label' => 'Sáng',
                'start' => $data['morning_start'],
                'end'   => $data['morning_end'],
            ];
        }
        if (!empty($data['afternoon_enabled'])) {
            $sessions[] = [
                'label' => 'Chiều',
                'start' => $data['afternoon_start'],
                'end'   => $data['afternoon_end'],
            ];
        }
        return $sessions;
    }

    /** Tính số slot giữa start–end với bước duration phút */
    private function calcSlots(string $start, string $end, int $duration): int
    {
        [$sh, $sm] = explode(':', $start);
        [$eh, $em] = explode(':', $end);
        $minutes = ($eh * 60 + $em) - ($sh * 60 + $sm);
        return max(0, (int) floor($minutes / $duration));
    }
}