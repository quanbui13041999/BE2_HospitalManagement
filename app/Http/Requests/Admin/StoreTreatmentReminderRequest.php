<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTreatmentReminderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'       => 'required|exists:users,user_id',
            'record_id'     => 'nullable|exists:medical_records,record_id',
            'reminder_type' => 'required|in:medicine,instruction',
            'remind_at'     => 'required|date|after:now',
            'message'       => 'required|string|max:500',
        ];
    }
}
