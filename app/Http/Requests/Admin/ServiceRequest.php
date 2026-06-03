<?php

namespace App\Http\Requests\Admin;

use App\Models\ServicePrice;
use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $priceTypes = implode(',', ServicePrice::PRICE_TYPES);

        return [
            'service_code'            => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9\-\.]+$/',
                'unique:services,service_code',
            ],
            'service_name'            => 'required|string|max:150',
            'department_id'           => 'nullable|exists:departments,department_id',
            'description'             => 'nullable|string|max:500',
            'duration_minutes'        => 'required|integer|min:5|max:480',
            'status'                  => 'required|boolean',
            'prices'                  => 'nullable|array',
            'prices.*.price_type'     => "required_with:prices|in:{$priceTypes}",
            'prices.*.price'          => 'required_with:prices|numeric|min:0',
            'prices.*.effective_date' => 'required_with:prices|date',
            'prices.*.end_date'       => 'nullable|date|after_or_equal:prices.*.effective_date',
        ];
    }

    public function messages(): array
    {
        return [
            'service_code.required'         => 'Mã dịch vụ là bắt buộc.',
            'service_code.max'              => 'Mã dịch vụ không được vượt quá 30 ký tự.',
            'service_code.regex'            => 'Mã dịch vụ chỉ chứa chữ cái, chữ số, dấu gạch ngang và dấu chấm.',
            'service_code.unique'           => 'Mã dịch vụ đã tồn tại trong hệ thống.',
            'service_name.required'         => 'Tên dịch vụ là bắt buộc.',
            'service_name.max'              => 'Tên dịch vụ không được vượt quá 150 ký tự.',
            'department_id.exists'          => 'Khoa được chọn không hợp lệ.',
            'description.max'               => 'Mô tả không được vượt quá 500 ký tự.',
            'duration_minutes.required'     => 'Thời gian thực hiện là bắt buộc.',
            'duration_minutes.integer'      => 'Thời gian phải là số nguyên.',
            'duration_minutes.min'          => 'Thời gian tối thiểu là 5 phút.',
            'duration_minutes.max'          => 'Thời gian tối đa là 480 phút.',
            'status.required'               => 'Trạng thái là bắt buộc.',
            'prices.*.price_type.required_with' => 'Loại giá là bắt buộc.',
            'prices.*.price_type.in'        => 'Loại giá không hợp lệ.',
            'prices.*.price.required_with'  => 'Giá tiền là bắt buộc.',
            'prices.*.price.numeric'        => 'Giá tiền phải là số.',
            'prices.*.price.min'            => 'Giá tiền không được âm.',
            'prices.*.effective_date.required_with' => 'Ngày hiệu lực là bắt buộc.',
            'prices.*.end_date.after_or_equal' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ];
    }
}
