<?php

namespace App\Http\Requests;

use App\Models\TreatmentReminder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ConfirmReminderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $reminderId = $this->route('reminder');

        return is_numeric($reminderId)
            && TreatmentReminder::where('reminder_id', (int) $reminderId)
                ->where('user_id', Auth::id())
                ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'note' => ['prohibited'],
            'message' => ['prohibited'],
            'confirmed_at' => ['prohibited'],
            'confirm_type' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.prohibited' => 'Không được gửi thêm ghi chú từ trình duyệt.',
            'message.prohibited' => 'Không được tự ý sửa nội dung nhắc nhở.',
            'confirmed_at.prohibited' => 'Thời gian xác nhận do hệ thống tự ghi nhận.',
            'confirm_type.prohibited' => 'Loại xác nhận do hệ thống tự xác định.',
        ];
    }
}
