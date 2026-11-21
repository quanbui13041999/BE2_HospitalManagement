<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVaccinationRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,user_id',
            'vaccine_id' => 'required|exists:vaccines,vaccine_id',
            'doctor_id' => 'nullable|exists:doctors,doctor_id',
            'dose_number' => 'required|integer|min:1',
            'administered_at' => 'nullable|date',
            'batch_number' => 'nullable|string|max:50',
            'next_dose_date' => 'nullable|date',
            'status' => 'required|string|max:20',
            'notes' => 'nullable|string|max:255',
        ];
    }
}
