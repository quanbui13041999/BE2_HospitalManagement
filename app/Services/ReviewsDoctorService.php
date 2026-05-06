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
        'appointment_id.exists' => 'Lịch hẹn không hợp lệ.',
        'doctor_id.exists'      => 'Bác sĩ không hợp lệ.',
        'rating.between'        => 'Đánh giá phải từ 1 đến 5 sao.',
        'comment.max'           => 'Nhận xét tối đa 1000 ký tự.',
    ];
}
  //vallication
  public function rules()
  {
    return [
      'appointment_id' => ['required', 'integer', 'exists:appointments,appointment_id'],
      'doctor_id' => ['required', 'integer', 'exists:appointment_id'],
      'ranting' => ['required', 'integer', 'between:1,5'],
      'comment' => ['nullable', 'string', 'max:1000']
    ];
  }

  public function canReviews(int $appointment, int $userID) {
    $appointment = Appointment::where('appointment_id',$appointment)
    ->where('appointment_id',$appointment)
    ->first();

    if ($appointment) {
      return ['can' => false, 'messege' => 'Lịch hẹn không tồn tại'];
    }

    if($appointment->status != 'Hoàn Thành') {
      return ['can' => false, 'messege' => 'Chỉ có thể đánh giá sau khi hoàn thành khám'];
    }

    $Ready = Review::where('appointment_id',$appointment)
    ->where('user_id',$userID)
    ->exists(); // xac dinh xem cho hang nao ton tai trong truy van hien tai khong
  }
  public function createReviews(array $data, int $userID) {
    return DB::transaction(function() use ($data,$userID) {
    
    // kiem tra lich hen thuoc user hien tai
    $appointment = Appointment::where('appointment_id',$data['appointment_id'])
    ->where('user_id',$userID)
    ->whereIn('status',['Đã Khám','Hoàn Thành'])
    ->firstOrFail();
    });
  }
}
?>