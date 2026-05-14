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
        $serviceId = $this->route('service')?->service_id;
        $priceTypes = implode(',', ServicePrice::PRICE_TYPES);

        return [
            'service_code'            => [
                'required',
                'string',
                'max:30',
                'unique:Services,service_code' . ($serviceId ? ",{$serviceId},service_id" : ''),
            ],
            'service_name'            => 'required|string|max:150',
            'department_id'           => 'nullable|exists:Departments,department_id',
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
            'service_code.unique' => 'Mã dịch vụ đã tồn tại.',
        ];
    }
}
