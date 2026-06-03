<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // service_code KHÔNG được phép sửa (khóa nghiệp vụ)
        return [
            'service_name'     => 'required|string|max:150',
            'department_id'    => 'nullable|exists:departments,department_id',
            'description'      => 'nullable|string|max:500',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status'           => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'service_name.required'     => 'Tên dịch vụ là bắt buộc.',
            'service_name.max'          => 'Tên dịch vụ không được vượt quá 150 ký tự.',
            'department_id.exists'      => 'Khoa được chọn không hợp lệ.',
            'description.max'           => 'Mô tả không được vượt quá 500 ký tự.',
            'duration_minutes.required' => 'Thời gian thực hiện là bắt buộc.',
            'duration_minutes.integer'  => 'Thời gian phải là số nguyên.',
            'duration_minutes.min'      => 'Thời gian tối thiểu là 5 phút.',
            'duration_minutes.max'      => 'Thời gian tối đa là 480 phút.',
            'status.required'           => 'Trạng thái là bắt buộc.',
        ];
    }
}
