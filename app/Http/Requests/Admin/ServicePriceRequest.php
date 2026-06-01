<?php

namespace App\Http\Requests\Admin;

use App\Models\ServicePrice;
use Illuminate\Foundation\Http\FormRequest;

class ServicePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price_type'     => 'required|in:' . implode(',', ServicePrice::PRICE_TYPES),
            'price'          => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:effective_date',
        ];
    }

    public function messages(): array
    {
        return [
            'price_type.required'          => 'Loại giá là bắt buộc.',
            'price_type.in'                => 'Loại giá không hợp lệ (Thường / BHYT / VIP).',
            'price.required'               => 'Đơn giá là bắt buộc.',
            'price.numeric'                => 'Đơn giá phải là số.',
            'price.min'                    => 'Đơn giá không được âm.',
            'effective_date.required'      => 'Ngày áp dụng là bắt buộc.',
            'effective_date.date'          => 'Ngày áp dụng không hợp lệ.',
            'end_date.date'                => 'Ngày kết thúc không hợp lệ.',
            'end_date.after_or_equal'      => 'Ngày kết thúc phải bằng hoặc sau ngày áp dụng.',
        ];
    }
}
