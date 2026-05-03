<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AppointmentQueueService
{
    /**
     * Get queue information for a schedule
     * 
     * @param int $scheduleId
     * @return array Queue info including queue number, people ahead, wait time, and queue details
     */
    public function getQueueInfo(int $scheduleId): array
    {
        // Get schedule information
        $schedule = $this->getSchedule($scheduleId);

        if (!$schedule) {
            return [
                'success' => false,
                'message' => 'Lịch khám không tồn tại'
            ];
        }

        // Get all appointments in queue
        $appointments = $this->getAppointmentsInQueue($scheduleId);

        // Calculate queue metrics
        $queueNumber = $appointments->count() + 1;
        $peopleAhead = max(0, $appointments->count());
        $estimatedWaitMinutes = $this->calculateEstimatedWaitTime($peopleAhead, $schedule->slot_duration);

        // Get queue details for people ahead
        $queueDetails = $this->getQueueDetails($appointments, $queueNumber);

        return [
            'success' => true,
            'queue_number' => $queueNumber,
            'people_ahead' => $peopleAhead,
            'estimated_wait_minutes' => $estimatedWaitMinutes,
            'schedule_info' => [
                'start_time' => $schedule->start_time,
                'slot_duration' => $schedule->slot_duration,
                'max_slot' => $schedule->max_slot,
            ],
            'queue_details' => $queueDetails,
        ];
    }

    /**
     * Get schedule information
     */
    private function getSchedule(int $scheduleId)
    {
        return DB::table('doctorschedules')
            ->where('schedule_id', $scheduleId)
            ->where('status', 'Hoạt động')
            ->select(
                'schedule_id',
                'doctor_id',
                'work_date',
                'start_time',
                'end_time',
                'slot_duration',
                'max_slot'
            )
            ->first();
    }

    /**
     * Get all appointments in queue for this schedule
     * Ordered by queue number
     */
    private function getAppointmentsInQueue(int $scheduleId)
    {
        return DB::table('appointments')
            ->where('schedule_id', $scheduleId)
            ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận', 'Đã khám'])
            ->orderBy('queue_number', 'asc')
            ->select(
                'appointment_id',
                'queue_number',
                'status',
                'user_id',
                'appointment_time'
            )
            ->get();
    }

    /**
     * Calculate estimated wait time
     * Formula: number of people ahead * average service time per slot
     */
    private function calculateEstimatedWaitTime(int $peopleAhead, ?int $slotDuration): int
    {
        return $peopleAhead * ($slotDuration ?? 15);
    }

    /**
     * Get details of people ahead in queue (max 5 people)
     * Includes abbreviated names
     */
    private function getQueueDetails($appointments, int $currentQueueNumber): array
    {
        $queueDetails = [];

        // Filter appointments ahead of current queue number and take first 5
        $appointmentsAhead = $appointments
            ->filter(fn($a) => $a->queue_number < $currentQueueNumber)
            ->take(5);

        foreach ($appointmentsAhead as $appointment) {
            $user = $this->getUser($appointment->user_id);

            if ($user) {
                $queueDetails[] = [
                    'queue_number' => $appointment->queue_number,
                    'status' => $appointment->status,
                    'abbreviated_name' => $this->abbreviateName($user->full_name),
                ];
            }
        }

        return $queueDetails;
    }

    /**
     * Get user information by ID
     */
    private function getUser(int $userId)
    {
        return DB::table('users')
            ->where('user_id', $userId)
            ->select('user_id', 'full_name')
            ->first();
    }

    /**
     * Abbreviate user's full name
     * Example: "Nguyễn Văn A" -> "N.V.A."
     * 
     * @param string $fullName
     * @return string Abbreviated name
     */
    private function abbreviateName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));

        if (count($parts) > 1) {
            // Multiple parts: use first letter of each part
            return implode('.', array_map(fn($part) => substr($part, 0, 1), $parts)) . '.';
        } else {
            // Single part: use first 3 letters
            return substr($fullName, 0, 3) . '.';
        }
    }
}
