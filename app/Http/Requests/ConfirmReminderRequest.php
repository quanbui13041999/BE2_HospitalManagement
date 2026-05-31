<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmReminderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $reminderId = $this->route('reminder');
        $reminder = \App\Models\TreatmentReminder::find($reminderId);
        return $reminder && $reminder->user_id === \Illuminate\Support\Facades\Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // reminder_id is already validated in authorize
        ];
    }
}
