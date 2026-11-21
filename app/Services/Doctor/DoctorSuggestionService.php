<?php

namespace App\Services\Doctor;

use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class DoctorSuggestionService
{
    /**
     * Suggest top 3 doctors based on scoring algorithm
     * 
     * Scoring weights (total: 100):
     * - 40% available slots ratio (doctors with more slots available)
     * - 35% average rating (avg_rating / 5 * 35)
     * - 15% experience (capped at 20 years)
     * - 10% number of reviews (capped at 50 reviews)
     */
    public function suggestTopDoctors(int $departmentId, string $workDate, int $limit = 3): array
    {
        // Get all active doctors in department with review stats
        $doctors = Doctor::byDepartment($departmentId)
            ->active()
            ->withReviewStats()
            ->get();

        if ($doctors->isEmpty()) {
            return [];
        }

        $doctorIds = $doctors->pluck('doctor_id')->toArray();

        // Get available slots for each doctor
        $scheduleStats = $this->getScheduleStats($doctorIds, $workDate);

        // Calculate scores
        $scored = [];
        foreach ($doctors as $doctor) {
            // Get slot stats for this doctor
            $stats = $scheduleStats->get($doctor->doctor_id);
            $totalSlots = $stats ? (int) $stats->total_slots : 0;
            $bookedCount = $stats ? (int) $stats->booked_count : 0;
            $availableSlots = max(0, $totalSlots - $bookedCount);

            // Skip doctors with no schedule or full schedules
            if ($totalSlots === 0 || $availableSlots === 0) {
                continue;
            }

            // Calculate composite score
            $score = $this->calculateScore(
                $availableSlots,
                $totalSlots,
                (float) $doctor->avg_rating,
                (int) $doctor->total_reviews,
                (int) $doctor->experience
            );

            $scored[] = [
                'doctor_id' => $doctor->doctor_id,
                'full_name' => $doctor->full_name,
                'experience' => $doctor->experience,
                'price' => $doctor->price,
                'avatar_url' => $doctor->avatar_url,
                'bio' => $doctor->bio,
                'avg_rating' => $doctor->avg_rating,
                'total_reviews' => $doctor->total_reviews,
                'available_slots' => $availableSlots,
                'total_slots' => $totalSlots,
                'score' => round($score, 2),
            ];
        }

        // Sort by score (highest first) and get top doctors
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($scored, 0, $limit);
    }

    /**
     * Get schedule statistics for doctors on specified date
     * Returns total slots and booked count per doctor
     */
    private function getScheduleStats($doctorIds, $workDate)
    {
        return DB::table('doctorschedules')
            ->leftJoinSub(
                DB::table('appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
                    ->groupBy('schedule_id'),
                'bk',
                'bk.schedule_id',
                '=',
                'doctorschedules.schedule_id'
            )
            ->whereIn('doctorschedules.doctor_id', $doctorIds)
            ->where('doctorschedules.work_date', $workDate)
            ->whereIn('doctorschedules.status', ['active', 'Hoạt động'])
            ->select(
                'doctorschedules.doctor_id',
                DB::raw('SUM(doctorschedules.max_slot) as total_slots'),
                DB::raw('SUM(COALESCE(bk.booked_count, 0)) as booked_count')
            )
            ->groupBy('doctorschedules.doctor_id')
            ->get()
            ->keyBy('doctor_id');
    }

    /**
     * Calculate composite score for doctor recommendation
     * 
     * Components:
     * - Slot Score (40%): (available / total) * 40
     * - Rating Score (35%): (avg_rating / 5) * 35
     * - Experience Score (15%): (min(exp, 20) / 20) * 15
     * - Review Score (10%): (min(reviews, 50) / 50) * 10
     */
    private function calculateScore(
        int $availableSlots,
        int $totalSlots,
        float $avgRating,
        int $totalReviews,
        int $experience
    ): float {
        $slotScore = ($availableSlots / $totalSlots) * 40;
        $ratingScore = ($avgRating / 5.0) * 35;
        $experienceScore = (min($experience, 20) / 20) * 15;
        $reviewScore = (min($totalReviews, 50) / 50) * 10;

        return $slotScore + $ratingScore + $experienceScore + $reviewScore;
    }
}
