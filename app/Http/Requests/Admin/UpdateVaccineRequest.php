<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVaccineRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'vaccine_name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'manufacturer' => 'nullable|string|max:100',
            'doses_required' => 'required|integer|min:1',
            'status' => 'required|boolean',
        ];
    }
}
