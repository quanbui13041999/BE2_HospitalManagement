<?php
// app/Services/DoctorDashboardService.php

namespace App\Services\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Review;
use App\Models\DoctorSchedule;
use App\Models\QueueTicket;
use App\Services\QueueService;
use App\Services\MedicalRecordService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class DoctorDashboardService
{
    public function __construct(
        private MedicalRecordService $medicalRecordService,
        private QueueService $queueService
    ) {}

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
            ->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận', 'Đã thanh toán', 'Đang khám'])
            ->count();

        $upcomingCount = (clone $appointmentQuery)
            ->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận','Đã thanh toán'])
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
            ->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận', 'Đã thanh toán', 'Đang khám', 'Hoàn thành'])
            ->orderBy('appointments.queue_number')
            ->select([
                'appointments.*',
                'users.full_name as patient_name',
                'users.phone as patient_phone',
                'services.service_name as service_name',
                'doctors.full_name as doctor_name',
                'doctorschedules.slot_duration',
            ])
            ->get()
            ->load('medicalRecord');
    }

    public function getUpcomingAppointments(int $doctorId, bool $isAdmin, ?int $targetDoctorId = null): \Illuminate\Support\Collection
    {
        $effectiveId = $isAdmin ? $targetDoctorId : $doctorId;

        return $this->baseAppointmentQuery($effectiveId)
            ->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận', 'Đã thanh toán'])
            ->where('appointments.appointment_time', '>', now())
            ->orderBy('appointments.appointment_time', 'asc')
            ->select([
                'appointments.*',
                'users.full_name as patient_name',
                'users.phone as patient_phone',
                'services.service_name as service_name',
                'doctors.full_name as doctor_name',
            ])
            ->get()
            ->load('medicalRecord');
    }

    /**
     * Đánh dấu hoàn thành lịch hẹn
     * Bác sĩ chỉ được cập nhật lịch hẹn của mình
     */
    public function completeAppointment(int $appointmentId, int $doctorId, bool $isAdmin): array
    {
        $appointment = Appointment::lockForUpdate()->find($appointmentId);

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

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận', 'Đã thanh toán', 'Đang khám'])) {
            return ['success' => false, 'message' => "Không thể hoàn thành lịch hẹn với trạng thái: {$appointment->status}"];
        }

        $queueTicket = QueueTicket::where('appointment_id', $appointment->appointment_id)
            ->whereDate('queue_date', today())
            ->latest('ticket_id')
            ->first();

        if ($queueTicket && $queueTicket->status !== 'in_progress') {
            return ['success' => false, 'message' => 'Chỉ được hoàn thành khi bệnh nhân đang ở trạng thái Đang khám trong hàng đợi.'];
        }

        if ($queueTicket) {
            $queueTicket = $this->queueService->complete($queueTicket->ticket_id);
            $appointment = $queueTicket->appointment()->with(['user', 'service', 'schedule.doctor', 'medicalRecord'])->first();

            $record = $appointment?->medicalRecord;

            return [
                'success' => true,
                'message' => 'Đã hoàn thành ca khám và cập nhật hàng đợi.',
                'record_id' => $record?->record_id,
                'record_url' => $record ? route('medical-records.show', $record->record_id) : null,
                'record_edit_url' => $record ? route('medical-records.edit', $record->record_id) : null,
            ];
        }

        $appointment->loadMissing(['user', 'service', 'schedule.doctor', 'medicalRecord']);

        try {
            $currentVersion = $appointment->version ?? 1;
            $updated = Appointment::where('appointment_id', $appointment->appointment_id)
                ->where('version', $currentVersion)
                ->update([
                    'status' => 'Hoàn thành',
                    'version' => $currentVersion + 1,
                ]);

            if ($updated === 0) {
                return ['success' => false, 'message' => 'Lịch hẹn đã bị thay đổi bởi người khác. Vui lòng tải lại.'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Lịch hẹn đã bị thay đổi bởi người khác. Vui lòng tải lại.'];
        }

        $record = $this->medicalRecordService->createBlankRecordFromAppointment($appointment->fresh([
            'user',
            'service',
            'schedule.doctor',
            'medicalRecord',
        ]));

        return [
            'success' => true,
            'message' => 'Đã hoàn thành lịch hẹn và tạo hồ sơ bệnh án để bác sĩ nhập.',
            'record_id' => $record->record_id,
            'record_url' => route('medical-records.show', $record->record_id),
            'record_edit_url' => route('medical-records.edit', $record->record_id),
        ];
    }

    /**
     * Hủy lịch hẹn
     */
    public function cancelAppointment(int $appointmentId, string $reason, int $doctorId, bool $isAdmin): array
    {
        $appointment = Appointment::lockForUpdate()->find($appointmentId);

        if (!$appointment) {
            return ['success' => false, 'message' => 'Lịch hẹn không tồn tại.'];
        }

        if (!$isAdmin) {
            $schedule = $appointment->schedule;
            if (!$schedule || $schedule->doctor_id !== $doctorId) {
                return ['success' => false, 'message' => 'Bạn không có quyền hủy lịch hẹn này.'];
            }
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận', 'Đã thanh toán', 'Đang khám'])) {
            return ['success' => false, 'message' => "Không thể hủy lịch hẹn với trạng thái: {$appointment->status}"];
        }

        try {
            DB::transaction(function () use ($appointment, $reason) {
                $currentVersion = $appointment->version ?? 1;
                Appointment::where('appointment_id', $appointment->appointment_id)
                    ->where('version', $currentVersion)
                    ->update([
                        'status'        => 'Đã hủy',
                        'cancel_reason' => $reason ?: 'Bác sĩ hủy',
                        'version'       => $currentVersion + 1
                    ]);
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Lịch hẹn đã bị thay đổi bởi người khác. Vui lòng tải lại.'];
        }

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
        $review = Review::lockForUpdate()->find($reviewId);

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

        try {
            DB::transaction(function () use ($review, $reply) {
                $currentVersion = $review->version ?? 1;
                Review::where('review_id', $review->review_id)
                    ->where('version', $currentVersion)
                    ->update([
                        'doctor_reply'            => trim($reply),
                        'doctor_reply_updated_at' => now(),
                        'version'                 => $currentVersion + 1
                    ]);
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Đánh giá đã bị thay đổi bởi người khác. Vui lòng tải lại.'];
        }

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

        $review = Review::lockForUpdate()->find($reviewId);
        if (!$review) {
            return ['success' => false, 'message' => 'Đánh giá không tồn tại.'];
        }

        try {
            DB::transaction(function () use ($review) {
                $currentVersion = $review->version ?? 1;
                Review::where('review_id', $review->review_id)
                    ->where('version', $currentVersion)
                    ->update([
                        'doctor_reply'            => null,
                        'doctor_reply_updated_at' => null,
                        'version'                 => $currentVersion + 1
                    ]);
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Đánh giá đã bị thay đổi bởi người khác. Vui lòng tải lại.'];
        }

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
