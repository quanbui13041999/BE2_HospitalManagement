<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Can;
use Illuminate\Validation\ValidationException;

class ReviewsDoctorService
{
  // kiem tra dang nhap
  public function auth()
  {
    return auth()->check();
  }
  /**
   * Summary of messages
   * @return array{appointment_id.exists: string, comment.max: string, doctor_id.exists: string, rating.between: string}
   */
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
  //vallication
  public function rules()
  {
    return [
      'appointment_id' => ['required', 'integer', 'exists:appointments,appointment_id'],
      'doctor_id' => ['required', 'integer', 'exists:doctors,doctor_id'],
      'rating' => ['required', 'integer', 'between:1,5'],
      'comment' => ['nullable', 'string', 'max:1000'],
    ];
  }

  /**
   * kiem tra xem du dieu kiem danh gia hay khong
   * @param int $appointment
   * @param int $userID
   * @return array{appointment: Appointment|\stdClass|null, can: bool|array{can: bool, message: string}|array{can: bool, messege: string}}
   */
  public function canReviews(int $appointment, int $userID)
  {
    $appointment = Appointment::where('appointment_id', $appointment)
      ->where('appointment_id', $appointment)
      ->first();

    if (!$appointment) {
      return ['can' => false, 'message' => 'Lịch hẹn không tồn tại'];
    }

    if ($appointment->status != 'Hoàn Thành') {
      return ['can' => false, 'message' => 'Chỉ có thể đánh giá sau khi hoàn thành khám'];
    }

    $Ready = Review::where('appointment_id', $appointment)
      ->where('user_id', $userID)
      ->exists(); // xac dinh xem cho hang nao ton tai trong truy van hien tai khong

    if ($Ready) {
      return ['can' => false, 'message' => 'Bạn đã đánh giá lịch hẹn này rồi.'];
    }

    return ['can' => true, 'appointment' => $appointment];
  }

  /**
   * tao danh gia
   * @param array $data 
   * @param int $userID
   */
  public function createReviews(array $data, int $userID)
  {
    return DB::transaction(function () use ($data, $userID) {
      $check = $this->canReviews($data['appointment_id'], $userID);

      if (!$check['can']) {
        throw ValidationException::withMessages([
          'appointment_id' => $check['message'],
        ]);
      }

      $review = Review::create([
        'appointment_id' => $data['appointment_id'],
        'user_id' => $userID,
        'doctor_id' => $data['doctor_id'],
        'rating' => $data['rating'],
        'comment' => $data['comment'] ?? null,
      ]);

      // Ghi activity log
      ActivityLog::create([
        'user_id' => $userID,
        'action' => 'Đánh giá bác sĩ #' . $data['doctor_id'],
        'ip_address' => request()->ip(),
      ]);

      return $review;
    });
  }
}
?>