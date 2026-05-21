<?php
// app/Services/DoctorDashboardService.php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Review;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class DoctorDashboardService
{
    // ═══════════════════════════════════════════════════════════════
    //  STATS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Tổng hợp số liệu thống kê cho dashboard header
     *
     * @param int      $doctorId  Doctor đang đăng nhập (0 nếu admin không chọn)
     * @param bool     $isAdmin
     * @param int|null $targetDoctorId  Bác sĩ admin đang xem (null = tất cả)
     */
    public function getStats(int $doctorId, bool $isAdmin, ?int $targetDoctorId = null): array
    {
        $effectiveDoctorId = $isAdmin ? $targetDoctorId : $doctorId;

        $appointmentQuery = $this->baseAppointmentQuery($effectiveDoctorId);
        $reviewQuery      = $this->baseReviewQuery($effectiveDoctorId);

        $today    = Carbon::today();
        $todayCount = (clone $appointmentQuery)
            ->whereDate('appointments.appointment_time', $today)
            ->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám'])
            ->count();

        $upcomingCount = (clone $appointmentQuery)
            ->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận'])
            ->where('appointments.appointment_time', '>', now())
            ->count();

        $completedCount = (clone $appointmentQuery)
            ->where('appointments.status', 'Hoàn thành')
            ->whereMonth('appointments.appointment_time', $today->month)
            ->count();

        $avgRating = (clone $reviewQuery)->avg('rating') ?? 0;
        $totalReviews = (clone $reviewQuery)->count();
        $pendingReplies = (clone $reviewQuery)
            ->whereNull('doctor_reply')
            ->count();

        return [
            'today'           => $todayCount,
            'upcoming'        => $upcomingCount,
            'completed_month' => $completedCount,
            'avg_rating'      => round($avgRating, 1),
            'total_reviews'   => $totalReviews,
            'pending_replies' => $pendingReplies,
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    //  APPOINTMENTS
    // ═══════════════════════════════════════════════════════════════

    public function getTodayAppointments(int $doctorId, bool $isAdmin, ?int $targetDoctorId = null): \Illuminate\Support\Collection
    {
        $effectiveId = $isAdmin ? $targetDoctorId : $doctorId;

        return $this->baseAppointmentQuery($effectiveId)
            ->whereDate('appointments.appointment_time', Carbon::today())
            ->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám'])
            ->orderBy('appointments.queue_number')
            ->select([
                'appointments.*',
                'users.full_name as patient_name',
                'users.phone as patient_phone',
                'services.service_name as service_name',
                'doctors.full_name as doctor_name',
                'doctorschedules.slot_duration',
            ])
            ->get();
    }

    public function getUpcomingAppointments(int $doctorId, bool $isAdmin, ?int $targetDoctorId = null): \Illuminate\Support\Collection
    {
        $effectiveId = $isAdmin ? $targetDoctorId : $doctorId;

        return $this->baseAppointmentQuery($effectiveId)
            ->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận'])
            ->where('appointments.appointment_time', '>', now())
            ->orderBy('appointments.appointment_time', 'asc')
            ->select([
                'appointments.*',
                'users.full_name as patient_name',
                'users.phone as patient_phone',
                'services.service_name as service_name',
                'doctors.full_name as doctor_name',
            ])
            ->get();
    }

    /**
     * Đánh dấu hoàn thành lịch hẹn
     * Bác sĩ chỉ được cập nhật lịch hẹn của mình
     */
    public function completeAppointment(int $appointmentId, int $doctorId, bool $isAdmin): array
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return ['success' => false, 'message' => 'Lịch hẹn không tồn tại.'];
        }

        // Kiểm tra quyền
        if (!$isAdmin) {
            $schedule = $appointment->schedule;
            if (!$schedule || $schedule->doctor_id !== $doctorId) {
                return ['success' => false, 'message' => 'Bạn không có quyền cập nhật lịch hẹn này.'];
            }
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám'])) {
            return ['success' => false, 'message' => "Không thể hoàn thành lịch hẹn với trạng thái: {$appointment->status}"];
        }

        $appointment->update(['status' => 'Hoàn thành']);

        return ['success' => true, 'message' => 'Đã đánh dấu hoàn thành lịch hẹn.'];
    }

    /**
     * Hủy lịch hẹn
     */
    public function cancelAppointment(int $appointmentId, string $reason, int $doctorId, bool $isAdmin): array
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return ['success' => false, 'message' => 'Lịch hẹn không tồn tại.'];
        }

        if (!$isAdmin) {
            $schedule = $appointment->schedule;
            if (!$schedule || $schedule->doctor_id !== $doctorId) {
                return ['success' => false, 'message' => 'Bạn không có quyền hủy lịch hẹn này.'];
            }
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám'])) {
            return ['success' => false, 'message' => "Không thể hủy lịch hẹn với trạng thái: {$appointment->status}"];
        }

        $appointment->update([
            'status'        => 'Đã hủy',
            'cancel_reason' => $reason ?: 'Bác sĩ hủy',
        ]);

        // TODO: gửi email thông báo cho bệnh nhân
        // event(new AppointmentCancelledByDoctor($appointment));

        return ['success' => true, 'message' => 'Đã hủy lịch hẹn thành công.'];
    }

    // ═══════════════════════════════════════════════════════════════
    //  REVIEWS
    // ═══════════════════════════════════════════════════════════════

    public function getReviews(int $doctorId, bool $isAdmin, ?int $targetDoctorId = null, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        $effectiveId = $isAdmin ? $targetDoctorId : $doctorId;

        return $this->baseReviewQuery($effectiveId)
            ->with(['user:user_id,full_name,avatar_url', 'doctor:doctor_id,full_name'])
            ->orderBy('reviews.created_at', 'desc')
            ->select('reviews.*')
            ->paginate($perPage);
    }

    /**
     * Bác sĩ trả lời đánh giá của bệnh nhân
     */
    public function replyToReview(int $reviewId, string $reply, int $doctorId, bool $isAdmin): array
    {
        $review = Review::find($reviewId);

        if (!$review) {
            return ['success' => false, 'message' => 'Đánh giá không tồn tại.'];
        }

        // Bác sĩ chỉ được trả lời review thuộc về mình
        if (!$isAdmin && $review->doctor_id !== $doctorId) {
            return ['success' => false, 'message' => 'Bạn không có quyền trả lời đánh giá này.'];
        }

        if (empty(trim($reply))) {
            return ['success' => false, 'message' => 'Nội dung trả lời không được để trống.'];
        }

        $review->update([
            'doctor_reply'            => trim($reply),
            'doctor_reply_updated_at' => now(),
        ]);

        return ['success' => true, 'message' => 'Đã gửi phản hồi thành công.'];
    }

    /**
     * Xóa phản hồi của bác sĩ (chỉ admin)
     */
    public function deleteReply(int $reviewId, bool $isAdmin): array
    {
        if (!$isAdmin) {
            return ['success' => false, 'message' => 'Chỉ admin mới có quyền xóa phản hồi.'];
        }

        $review = Review::find($reviewId);
        if (!$review) {
            return ['success' => false, 'message' => 'Đánh giá không tồn tại.'];
        }

        $review->update([
            'doctor_reply'            => null,
            'doctor_reply_updated_at' => null,
        ]);

        return ['success' => true, 'message' => 'Đã xóa phản hồi.'];
    }

    // ═══════════════════════════════════════════════════════════════
    //  DOCTORS LIST (admin only)
    // ═══════════════════════════════════════════════════════════════

    public function getDoctorsList(): \Illuminate\Support\Collection
    {
        return Doctor::active()
            ->with('department:department_id,department_name')
            ->select('doctor_id', 'full_name', 'department_id', 'avatar_url')
            ->orderBy('full_name')
            ->get();
    }

    // ═══════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Query gốc cho appointments, tự động join doctor qua schedule
     */
    private function baseAppointmentQuery(?int $doctorId): Builder
    {
        $query = Appointment::query()
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->join('users', 'appointments.user_id', '=', 'users.user_id')
            ->leftJoin('services', 'appointments.service_id', '=', 'services.service_id');

        if ($doctorId) {
            $query->where('doctorschedules.doctor_id', $doctorId);
        }

        return $query;
    }

    /**
     * Query gốc cho reviews
     */
    private function baseReviewQuery(?int $doctorId): Builder
    {
        $query = Review::query();

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        return $query;
    }
}