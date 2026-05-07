<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Review;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewsDoctorService
{
  // ─────────────────────────────────────────────
  // Auth helper
  // ─────────────────────────────────────────────
  public function auth(): bool
  {
    return auth()->check();
  }

  // ─────────────────────────────────────────────
  // Validation
  // ─────────────────────────────────────────────
  public function rules(): array
  {
    return [
      'appointment_id' => ['required', 'integer', 'exists:appointments,appointment_id'],
      'doctor_id' => ['required', 'integer', 'exists:doctors,doctor_id'],
      'rating' => ['required', 'integer', 'between:1,5'],
      'comment' => ['nullable', 'string', 'max:1000'],
    ];
  }

  public function messages(): array
  {
    return [
      'appointment_id.required' => 'Vui lòng chọn lịch hẹn.',
      'appointment_id.exists' => 'Lịch hẹn không hợp lệ.',
      'doctor_id.required' => 'Vui lòng chọn bác sĩ.',
      'doctor_id.exists' => 'Bác sĩ không hợp lệ.',
      'rating.required' => 'Vui lòng chọn số sao.',
      'rating.between' => 'Đánh giá phải từ 1 đến 5 sao.',
      'comment.max' => 'Nhận xét tối đa 1000 ký tự.',
    ];
  }

  // ─────────────────────────────────────────────
  // Kiểm tra điều kiện đánh giá
  // ─────────────────────────────────────────────

  /**
   * Kiểm tra xem người dùng có thể đánh giá lịch hẹn không.
   * Status hợp lệ: 'Hoàn thành' hoặc 'Đã khám'
   */
  public function canReview(int $appointmentId, int $userId): array
  {
    $appointment = Appointment::where('appointment_id', $appointmentId)->first();

    if (!$appointment) {
      return ['can' => false, 'message' => 'Lịch hẹn không tồn tại.'];
    }

    if ($appointment->user_id !== $userId) {
      return ['can' => false, 'message' => 'Bạn không có quyền đánh giá lịch hẹn này.'];
    }

    // Chấp nhận cả hai trạng thái phổ biến
    $validStatuses = ['Hoàn thành', 'Đã khám', 'Đã Khám', 'Hoàn Thành'];
    if (!in_array($appointment->status, $validStatuses)) {
      return ['can' => false, 'message' => 'Chỉ có thể đánh giá sau khi hoàn thành khám.'];
    }

    $alreadyReviewed = Review::where('appointment_id', $appointmentId)
      ->where('user_id', $userId)
      ->exists();

    if ($alreadyReviewed) {
      return ['can' => false, 'message' => 'Bạn đã đánh giá lịch hẹn này rồi.'];
    }

    return ['can' => true, 'appointment' => $appointment];
  }

  // ─────────────────────────────────────────────
  // Tạo đánh giá
  // ─────────────────────────────────────────────
  public function store(array $data, int $userId): Review
  {
    return DB::transaction(function () use ($data, $userId) {
      $check = $this->canReview($data['appointment_id'], $userId);

      if (!$check['can']) {
        throw ValidationException::withMessages([
          'appointment_id' => [$check['message']],
        ]);
      }

      $review = Review::create([
        'appointment_id' => $data['appointment_id'],
        'user_id' => $userId,
        'doctor_id' => $data['doctor_id'],
        'rating' => $data['rating'],
        'comment' => $data['comment'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      ActivityLog::create([
        'user_id' => $userId,
        'action' => 'Tạo đánh giá bác sĩ #' . $data['doctor_id'],
        'ip_address' => request()->ip(),
      ]);

      return $review;
    });
  }

  // ─────────────────────────────────────────────
  // Chỉnh sửa đánh giá (trong 24 giờ)
  // ─────────────────────────────────────────────
  public function update(int $reviewId, array $data, int $userId, bool $isAdmin = false): Review
  {
    return DB::transaction(function () use ($reviewId, $data, $userId, $isAdmin) {
      $review = Review::findOrFail($reviewId);

      // Chỉ chủ sở hữu hoặc admin mới được sửa
      if (!$isAdmin && $review->user_id !== $userId) {
        throw ValidationException::withMessages([
          'review_id' => ['Bạn không có quyền chỉnh sửa đánh giá này.'],
        ]);
      }

      // User thường chỉ được sửa trong 24 giờ
      if (!$isAdmin && !$review->canEdit($userId)) {
        throw ValidationException::withMessages([
          'review_id' => ['Chỉ có thể chỉnh sửa trong vòng 24 giờ sau khi đăng.'],
        ]);
      }

      $review->update([
        'rating' => $data['rating'],
        'comment' => $data['comment'] ?? null,
        'updated_at' => now(),
      ]);

      ActivityLog::create([
        'user_id' => $userId,
        'action' => 'Cập nhật đánh giá #' . $reviewId,
        'ip_address' => request()->ip(),
      ]);

      return $review->fresh();
    });
  }

  // ─────────────────────────────────────────────
  // Xóa đánh giá
  // ─────────────────────────────────────────────
  public function delete(int $reviewId, int $userId, bool $isAdmin = false): void
  {
    DB::transaction(function () use ($reviewId, $userId, $isAdmin) {
      $review = Review::findOrFail($reviewId);

      if (!$isAdmin && $review->user_id !== $userId) {
        throw ValidationException::withMessages([
          'review_id' => ['Bạn không có quyền xóa đánh giá này.'],
        ]);
      }

      ActivityLog::create([
        'user_id' => $userId,
        'action' => 'Xóa đánh giá #' . $reviewId . ' (bác sĩ #' . $review->doctor_id . ')',
        'ip_address' => request()->ip(),
      ]);

      $review->delete();
    });
  }

  // ─────────────────────────────────────────────
  // Trả lời bình luận (bác sĩ / admin)
  // ─────────────────────────────────────────────

  /**
   * @param int    $reviewId
   * @param string $reply      Nội dung phản hồi (null = xóa reply)
   * @param int    $actorId    ID người thực hiện
   * @param bool   $isAdmin
   * @param int|null $doctorUserId  user_id của bác sĩ (nếu kiểm tra quyền)
   */
  public function reply(
    int $reviewId,
    ?string $reply,
    int $actorId,
    bool $isAdmin = false,
    ?int $doctorUserId = null
  ): Review {
    return DB::transaction(function () use ($reviewId, $reply, $actorId, $isAdmin, $doctorUserId) {
      $review = Review::findOrFail($reviewId);

      // Quyền: admin hoặc bác sĩ liên quan
      $isRelatedDoctor = $doctorUserId && ($review->doctor_id === $doctorUserId || $actorId === $doctorUserId);

      if (!$isAdmin && !$isRelatedDoctor) {
        throw ValidationException::withMessages([
          'review_id' => ['Bạn không có quyền trả lời đánh giá này.'],
        ]);
      }

      $review->update([
        'doctor_reply' => $reply,
        'doctor_reply_updated_at' => $reply ? now() : null,
      ]);

      ActivityLog::create([
        'user_id' => $actorId,
        'action' => ($reply ? 'Trả lời' : 'Xóa phản hồi') . ' đánh giá #' . $reviewId,
        'ip_address' => request()->ip(),
      ]);

      return $review->fresh();
    });
  }

  // ─────────────────────────────────────────────
  // Lấy đánh giá theo review_id (kèm quan hệ)
  // ─────────────────────────────────────────────
  public function findWithRelations(int $reviewId): Review
  {
    return Review::with(['user', 'doctor', 'appointment'])->findOrFail($reviewId);
  }
}