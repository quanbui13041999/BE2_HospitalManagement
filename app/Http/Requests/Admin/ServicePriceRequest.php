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
}
