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
        return [
            'service_name'     => 'required|string|max:150',
            'department_id'    => 'nullable|exists:Departments,department_id',
            'description'      => 'nullable|string|max:500',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status'           => 'required|boolean',
        ];
    }
}
