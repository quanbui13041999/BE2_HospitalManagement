<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DoctorTimeslotService
{
    /**
     * Get available timeslots for a doctor on a specific date
     * 
     * @param int $doctorId
     * @param string $workDate (format: Y-m-d)
     * @return array ['day_off' => bool, 'slots' => array]
     */
    public function getTimeslots(int $doctorId, string $workDate): array
    {
        // Check if doctor has day off
        if ($this->isDayOff($doctorId, $workDate)) {
            return ['day_off' => true, 'slots' => []];
        }

        // Get all schedules for this doctor on this date
        $schedules = $this->getSchedules($doctorId, $workDate);

        if (empty($schedules)) {
            return ['day_off' => false, 'slots' => []];
        }

        // Get all bookings for these schedules
        $bookings = $this->getBookings($schedules);

        // Generate available slots from schedules
        $slots = $this->generateSlots($schedules, $bookings);

        // Sort slots by time
        usort($slots, fn($a, $b) => strcmp($a['time'], $b['time']));

        return ['day_off' => false, 'slots' => $slots];
    }

    /**
     * Check if doctor has day off on specified date
     */
    private function isDayOff(int $doctorId, string $workDate): bool
    {
        $activeScheduleCount = DB::table('doctorschedules')
            ->where('doctor_id', $doctorId)
            ->where('work_date', $workDate)
            ->whereIn('status', ['active', 'Hoạt động'])
            ->count();

        if ($activeScheduleCount > 0) {
            return false;
        }

        return DB::table('doctorschedules')
            ->where('doctor_id', $doctorId)
            ->where('work_date', $workDate)
            ->where('status', 'blocked')
            ->exists();
    }

    /**
     * Get all active schedules for doctor on specified date
     */
    private function getSchedules(int $doctorId, string $workDate): array
    {
        return DB::table('doctorschedules')
            ->where('doctor_id', $doctorId)
            ->where('work_date', $workDate)
            ->whereIn('status', ['active', 'Hoạt động'])
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
     * Get all bookings for given schedules
     * Groups by schedule_id and appointment_time
     * 
     * @return array Keyed by schedule_id, containing appointment_time => booked_count
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
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->groupBy('schedule_id', 'appointment_time')
            ->get();

        // Transform to nested array: [schedule_id][appointment_time] => booked_count
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
     * Each slot represents an available appointment time
     */
    private function generateSlots(array $schedules, array $bookings): array
    {
        $slots = [];

        foreach ($schedules as $schedule) {
            $scheduleId = (int) $schedule->schedule_id;
            $maxSlot = (int) $schedule->max_slot;
            $duration = (int) $schedule->slot_duration;

            // Parse start and end times
            [$startHour, $startMin] = array_map('intval', explode(':', $schedule->start_time));
            [$endHour, $endMin] = array_map('intval', explode(':', $schedule->end_time));

            $endMinutesTotal = $endHour * 60 + $endMin;
            $currentHour = $startHour;
            $currentMin = $startMin;

            // Generate slots from start to end time
            while ($currentHour * 60 + $currentMin + $duration <= $endMinutesTotal) {
                // Format current time
                $timeStr = sprintf('%02d:%02d', $currentHour, $currentMin);

                // Calculate end time of this slot
                $slotEndMinutesTotal = $currentHour * 60 + $currentMin + $duration;
                $slotEndHour = intdiv($slotEndMinutesTotal, 60);
                $slotEndMin = $slotEndMinutesTotal % 60;
                $endTimeStr = sprintf('%02d:%02d', $slotEndHour, $slotEndMin);

                // Get booking count for this slot
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
}
