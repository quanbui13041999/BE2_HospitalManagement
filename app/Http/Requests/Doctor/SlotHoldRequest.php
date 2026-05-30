<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * SlotHoldRequest
 *
 * Xác thực dữ liệu đầu vào cho thao tác giữ slot tạm thời.
 * Tách biệt hoàn toàn khỏi Controller — Controller chỉ inject và dùng.
 */
class SlotHoldRequest extends FormRequest
{
    /**
     * Chỉ user đã đăng nhập mới được giữ slot.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'schedule_id'      => 'required|integer|exists:doctorschedules,schedule_id',
            'appointment_time' => ['required', 'string', 'max:5', 'regex:/^[0-2]\d:[0-5]\d$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'schedule_id.required'      => 'Vui lòng chọn khung lịch khám.',
            'schedule_id.exists'        => 'Khung lịch khám không tồn tại.',
            'appointment_time.required' => 'Vui lòng chọn giờ khám.',
            'appointment_time.regex'    => 'Giờ khám không hợp lệ. Vui lòng chọn lại khung giờ theo định dạng HH:MM.',
        ];
    }
}