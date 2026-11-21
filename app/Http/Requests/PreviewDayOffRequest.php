<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewDayOffRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'doctor_id'  => 'required|integer|exists:doctors,doctor_id',
            'date'       => 'required|date|after_or_equal:today',
            'end_date'   => 'nullable|date|after_or_equal:date',
            'session'    => 'required|in:all,morning,afternoon',
            'type'       => 'nullable|in:sick,leave,conference', // Optional for preview
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
