<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AppointmentQueueService
{
    /**
     * Get queue information for a specific appointment time
     * 
     * @param int $scheduleId
     * @param string|null $appointmentTime
     * @param int|null $currentAppointmentId
     * @return array 
     */
    public function getQueueInfo(int $scheduleId, ?string $appointmentTime = null, ?int $currentAppointmentId = null): array
    {
        // Get schedule information
        $schedule = $this->getSchedule($scheduleId);

        if (!$schedule) {
            return [
                'success' => false,
                'message' => 'Lịch khám không tồn tại'
            ];
        }

        // Get all appointments for this specific time slot
        $appointments = $this->getAppointmentsInQueue($scheduleId, $appointmentTime);

        // If no appointment_id provided, use total count 
        if ($currentAppointmentId === null) {
            $queueNumber = $appointments->count() + 1;
            $peopleAhead = max(0, $appointments->count());
        } else {
            // Calculate exact queue position: count appointments created BEFORE current appointment in same time slot
            $appointmentsBeforeCurrent = $appointments->filter(function($apt) use ($currentAppointmentId) {
                return $apt->appointment_id < $currentAppointmentId;
            });
            $peopleAhead = $appointmentsBeforeCurrent->count();
            $queueNumber = $peopleAhead + 1;
        }

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
     * Get all appointments in queue for this schedule and specific appointment time
     * If appointmentTime is null, returns all appointments for the schedule
     * Ordered by queue number
     * 
     * @param int $scheduleId
     * @param string|null $appointmentTime 
     */
    private function getAppointmentsInQueue(int $scheduleId, ?string $appointmentTime = null)
    {
        $query = DB::table('appointments')
            ->where('schedule_id', $scheduleId)
            ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận']);

        // Filter by specific appointment time if provided
        if ($appointmentTime !== null) {
            $timeToMatch = strlen($appointmentTime) > 5 
                ? substr($appointmentTime, 0, 5) 
                : $appointmentTime;          
            
            $query->whereRaw("DATE_FORMAT(appointment_time, '%H:%i') = ?", [$timeToMatch]);
        }

        return $query
            ->orderBy('appointment_id', 'asc')
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
     * Recalculates queue positions based on appointment_id order (creation order)
     */
    private function getQueueDetails($appointments, int $currentQueueNumber): array
    {
        $queueDetails = [];

        // Sort by appointment_id to get creation order 
        $sortedAppointments = $appointments->sortBy('appointment_id');
        
        // Get only appointments before current queue number 
        $appointmentsAhead = $sortedAppointments
            ->take($currentQueueNumber - 1)  
            ->take(5);                       

        foreach ($appointmentsAhead as $index => $appointment) {
            $user = $this->getUser($appointment->user_id);

            if ($user) {
                $recalculatedQueueNumber = $index + 1;
                $queueDetails[] = [
                    'queue_number' => $recalculatedQueueNumber,
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
