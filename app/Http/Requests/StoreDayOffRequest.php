<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDayOffRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'doctor_id'  => 'required|integer|exists:doctors,doctor_id',
            'type'       => 'required|in:sick,leave,conference',
            'date'       => 'required|date|after_or_equal:today',
            'end_date'   => 'nullable|date|after_or_equal:date',
            // all = cả ngày | morning | afternoon
            'session'    => 'required|in:all,morning,afternoon',
            'reason'     => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'date.after_or_equal' => 'Không thể đăng ký nghỉ cho ngày đã qua.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
        ];
    }
}
?>