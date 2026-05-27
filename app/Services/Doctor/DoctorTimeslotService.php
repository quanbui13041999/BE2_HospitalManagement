<?php

namespace App\Services\Doctor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DoctorTimeslotService
{
    // Thêm constants (trong cùng file service hoặc trong model)
    private const STATUS_ACTIVE = 'active';
    private const STATUS_ACTIVE_VI = 'Hoạt động';  // Vietnamese variant
    private const STATUS_BLOCKED = 'blocked';
    
    private const APPOINTMENT_CANCELLED = ['Đã hủy', 'Dời lịch'];
    private const APPOINTMENT_HOLD_SLOT = 'Giữ slot';
    
    /**
     * Get available timeslots for a doctor on a specific date
     */
    public function getTimeslots(int $doctorId, string $workDate): array
    {
        // Check if doctor has day off - ĐÃ SỬA LỖI
        if ($this->isDayOff($doctorId, $workDate)) {
            return ['day_off' => true, 'slots' => []];
        }

        // Get all schedules for this doctor on this date
        $schedules = $this->getSchedules($doctorId, $workDate);

        if (empty($schedules)) {
            return ['day_off' => false, 'slots' => []];
        }

        // Get all bookings for these schedules - ĐÃ SỬA LỖI GIỮ SLOT
        $bookings = $this->getBookings($schedules);

        // Generate available slots from schedules
        $slots = $this->generateSlots($schedules, $bookings);

        // Sort slots by time
        usort($slots, fn($a, $b) => strcmp($a['time'], $b['time']));

        return ['day_off' => false, 'slots' => $slots];
    }

    /**
     * Check if doctor has day off on specified date
     * 
     * Returns true (day off) if:
     * 1. Doctor registered day off (DoctorDayOff record exists)
     * 2. Doctor has schedules but ALL are blocked (intentional closure)
     */
    private function isDayOff(int $doctorId, string $workDate): bool
    {
        // 1. Check DoctorDayOff table first - explicit day off registration
        $isDayOffRegistered = DB::table('doctordaysoff')
            ->where('doctor_id', $doctorId)
            ->where('off_date', $workDate)
            ->exists();
        
        if ($isDayOffRegistered) {
            return true;  // Doctor explicitly registered day off
        }
        
        // 2. Check if there are any active schedules (both English & Vietnamese status values)
        $hasActiveSchedule = DB::table('doctorschedules')
            ->where('doctor_id', $doctorId)
            ->where('work_date', $workDate)
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_ACTIVE_VI])
            ->exists();
        
        if ($hasActiveSchedule) {
            return false;  // Has active schedule = NOT a day off
        }
        
        // 3. Check if there are blocked schedules (all schedules blocked = intentional closure)
        $hasBlockedSchedule = DB::table('doctorschedules')
            ->where('doctor_id', $doctorId)
            ->where('work_date', $workDate)
            ->where('status', self::STATUS_BLOCKED)
            ->exists();
        
        if ($hasBlockedSchedule) {
            return true;  // All schedules blocked = day off
        }
        
        // 4. No schedules at all = NOT a day off (schedules just not created yet)
        return false;
    }

    /**
     * Get all active schedules for doctor on specified date
     */
    private function getSchedules(int $doctorId, string $workDate): array
    {
        return DB::table('doctorschedules')
            ->where('doctor_id', $doctorId)
            ->where('work_date', $workDate)
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_ACTIVE_VI])
            ->select(
                'schedule_id',
                'start_time',
                'end_time',
                'slot_duration',
                'max_slot'
            )
            ->orderBy('start_time')
            ->get()
            ->toArray();
    }

    /**
     * Get all bookings for given schedules - ĐÃ SỬA LỖI GIỮ SLOT
     */
    private function getBookings(array $schedules): array
    {
        $scheduleIds = array_column($schedules, 'schedule_id');

        if (empty($scheduleIds)) {
            return [];
        }

        $bookings = DB::table('appointments')
            ->select(
                'schedule_id',
                'appointment_time',
                DB::raw('COUNT(*) as booked_count')
            )
            ->whereIn('schedule_id', $scheduleIds)
            ->whereNotIn('status', self::APPOINTMENT_CANCELLED)
            ->where(function($query) {
                // Chỉ tính slot 'Giữ slot' nếu chưa hết hạn
                $query->where('status', '!=', self::APPOINTMENT_HOLD_SLOT)
                      ->orWhere(function($q) {
                          $q->where('status', self::APPOINTMENT_HOLD_SLOT)
                            ->where('slot_hold_expire', '>', now());
                      });
            })
            ->groupBy('schedule_id', 'appointment_time')
            ->get();

        // Transform to nested array
        $bookingMap = [];
        foreach ($bookings as $booking) {
            if (!isset($bookingMap[$booking->schedule_id])) {
                $bookingMap[$booking->schedule_id] = [];
            }
            $bookingMap[$booking->schedule_id][$booking->appointment_time] = (int) $booking->booked_count;
        }

        return $bookingMap;
    }

    /**
     * Generate individual time slots from schedules
     */
    private function generateSlots(array $schedules, array $bookings): array
    {
        $slots = [];

        foreach ($schedules as $schedule) {
            $scheduleId = (int) $schedule->schedule_id;
            $maxSlot = (int) $schedule->max_slot;
            $duration = (int) $schedule->slot_duration;

            // Kiểm tra duration hợp lệ
            if ($duration <= 0) {
                continue;
            }

            // Parse start and end times
            [$startHour, $startMin] = array_map('intval', explode(':', $schedule->start_time));
            [$endHour, $endMin] = array_map('intval', explode(':', $schedule->end_time));

            $endMinutesTotal = $endHour * 60 + $endMin;
            $currentHour = $startHour;
            $currentMin = $startMin;

            // Generate slots
            while ($currentHour * 60 + $currentMin + $duration <= $endMinutesTotal) {
                $timeStr = sprintf('%02d:%02d', $currentHour, $currentMin);
                
                $slotEndMinutesTotal = $currentHour * 60 + $currentMin + $duration;
                $slotEndHour = intdiv($slotEndMinutesTotal, 60);
                $slotEndMin = $slotEndMinutesTotal % 60;
                $endTimeStr = sprintf('%02d:%02d', $slotEndHour, $slotEndMin);

                $booked = $bookings[$scheduleId][$timeStr] ?? 0;
                $isBooked = ($booked >= $maxSlot);

                $slots[] = [
                    'schedule_id' => $scheduleId,
                    'time' => $timeStr,
                    'end_time' => $endTimeStr,
                    'is_booked' => $isBooked,
                    'max_slot' => $maxSlot,
                    'booked' => $booked,
                ];

                // Increment to next slot
                $currentMin += $duration;
                if ($currentMin >= 60) {
                    $currentHour += intdiv($currentMin, 60);
                    $currentMin = $currentMin % 60;
                }
            }
        }

        return $slots;
    }
    
    // TÙY CHỌN: Thêm cache nếu cần (chỉ thêm nếu thấy chậm)
    public function getTimeslotsWithCache(int $doctorId, string $workDate): array
    {
        $cacheKey = "timeslots_{$doctorId}_{$workDate}";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($doctorId, $workDate) {
            return $this->getTimeslots($doctorId, $workDate);
        });
    }
    
    public function clearCache(int $doctorId, string $workDate): void
    {
        Cache::forget("timeslots_{$doctorId}_{$workDate}");
    }
}