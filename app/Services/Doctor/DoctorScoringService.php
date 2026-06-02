<?php
// ═══════════════════════════════════════════════════════════════════════════════
// app/Services/Doctor/DoctorScoringService.php
// ═══════════════════════════════════════════════════════════════════════════════
namespace App\Services\Doctor;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Dùng để tính điểm bác sĩ khi gợi ý thay thế.
 * 
 * Scoring weights (total: 100):
 *  - 40% available slots ratio (doctors with more slots available)
 *  - 35% average rating (avg_rating / 5 * 35)
 *  - 15% experience (capped at 20 years)
 *  - 10% number of reviews (capped at 50 reviews)
 */
class DoctorScoringService
{
    /**
     * Tìm các bác sĩ thay thế phù hợp, sắp xếp theo điểm số.
     *
     * @param  Collection  $doctors  Danh sách bác sĩ cùng khoa
     * @param  Carbon  $originalTime  Thời điểm hẹn cũ (để tìm lịch gần)
     * @param  int  $daysAhead  Số ngày tìm kiếm phía trước (mặc định 7)
     * @param  int  $limit  Số lượng gợi ý tối đa (mặc định 5)
     * @return Collection  Bác sĩ đã xếp hạng, mỗi item chứa doctor, schedule, available_slots, score
     */
    public function findScoredAlternatives(
        Collection $doctors,
        Carbon $originalTime,
        int $daysAhead = 7,
        int $limit = 5
    ): Collection {
        $searchFrom = $originalTime->copy()->startOfDay();
        $searchTo   = $searchFrom->copy()->addDays($daysAhead);

        $alternatives = [];

        foreach ($doctors as $doctor) {
            // Tìm schedule có slot trống gần ngày bị huỷ
            $schedule = DoctorSchedule::forDoctor($doctor->doctor_id)
                ->active()
                ->betweenDates($searchFrom->toDateString(), $searchTo->toDateString())
                ->get()
                ->filter(fn ($s) => $s->availableSlots() > 0)
                ->sortBy('work_date')
                ->first();

            if (!$schedule) {
                continue;
            }

            // Tính điểm cho bác sĩ này
            $score = $this->calculateScore($doctor, $schedule);

            $alternatives[] = (object) [
                'doctor'           => $doctor,
                'schedule'         => $schedule,
                'available_slots'  => $schedule->availableSlots(),
                'score'            => $score,
                'score_breakdown'  => $this->getScoreBreakdown($doctor, $schedule),
            ];
        }

        // Sắp xếp theo điểm cao nhất
        usort($alternatives, fn ($a, $b) => $b->score <=> $a->score);

        // Trả về top N gợi ý
        return collect(array_slice($alternatives, 0, $limit));
    }

    /**
     * Tính điểm cho 1 bác sĩ dựa trên công thức scoring.
     *
     * Scores: 
     *  - Available slots ratio: 40 points (tối đa)
     *  - Average rating: 35 points (7 * 5 stars)
     *  - Experience: 15 points (capped at 20 years)
     *  - Review count: 10 points (capped at 50 reviews)
     * Total: 100 points
     */
    private function calculateScore(Doctor $doctor, DoctorSchedule $schedule): float
    {
        // 1. Available slots ratio (40%)
        $totalSlots = $schedule->max_slot;
        $slotsAvailable = $schedule->availableSlots();
        $slotsRatio = $totalSlots > 0 ? ($slotsAvailable / $totalSlots) : 0;
        $slotsScore = $slotsRatio * 40;

        // 2. Average rating (35%)
        $avgRating = $doctor->avg_rating ?? 0;
        $ratingScore = ($avgRating / 5) * 35;

        // 3. Experience (15%, capped at 20 years)
        $experience = min($doctor->experience ?? 0, 20);
        $experienceScore = ($experience / 20) * 15;

        // 4. Number of reviews (10%, capped at 50 reviews)
        $reviewCount = min($doctor->total_reviews ?? 0, 50);
        $reviewScore = ($reviewCount / 50) * 10;

        return $slotsScore + $ratingScore + $experienceScore + $reviewScore;
    }

    /**
     * Trả về chi tiết từng thành phần của điểm để hiển thị trên email.
     */
    private function getScoreBreakdown(Doctor $doctor, DoctorSchedule $schedule): array
    {
        $totalSlots = $schedule->max_slot;
        $slotsAvailable = $schedule->availableSlots();
        $slotsRatio = $totalSlots > 0 ? ($slotsAvailable / $totalSlots) : 0;
        $slotsScore = $slotsRatio * 40;

        $avgRating = $doctor->avg_rating ?? 0;
        $ratingScore = ($avgRating / 5) * 35;

        $experience = min($doctor->experience ?? 0, 20);
        $experienceScore = ($experience / 20) * 15;

        $reviewCount = min($doctor->total_reviews ?? 0, 50);
        $reviewScore = ($reviewCount / 50) * 10;

        return [
            'available_slots' => [
                'label'     => 'Slot trống',
                'value'     => "{$slotsAvailable}/{$totalSlots}",
                'ratio'     => round($slotsRatio * 100, 1),
                'weight'    => '40%',
                'score'     => round($slotsScore, 2),
            ],
            'rating' => [
                'label'     => 'Đánh giá',
                'value'     => round($avgRating, 1),
                'max'       => 5,
                'count'     => $doctor->total_reviews ?? 0,
                'weight'    => '35%',
                'score'     => round($ratingScore, 2),
            ],
            'experience' => [
                'label'     => 'Kinh nghiệm',
                'value'     => ($doctor->experience ?? 0) . ' năm',
                'weight'    => '15%',
                'score'     => round($experienceScore, 2),
            ],
            'reviews' => [
                'label'     => 'Số reviews',
                'value'     => $doctor->total_reviews ?? 0,
                'weight'    => '10%',
                'score'     => round($reviewScore, 2),
            ],
        ];
    }

    /**
     * Tính điểm mà không cần schedule object (nếu chỉ cần so sánh bác sĩ chung).
     * Dùng giá trị slot trống trung bình.
     */
    public function calculateDoctorScore(Doctor $doctor, float $averageSlotsRatio = 0.5): float
    {
        $slotsScore = $averageSlotsRatio * 40;
        
        $avgRating = $doctor->avg_rating ?? 0;
        $ratingScore = ($avgRating / 5) * 35;

        $experience = min($doctor->experience ?? 0, 20);
        $experienceScore = ($experience / 20) * 15;

        $reviewCount = min($doctor->total_reviews ?? 0, 50);
        $reviewScore = ($reviewCount / 50) * 10;

        return $slotsScore + $ratingScore + $experienceScore + $reviewScore;
    }
}
