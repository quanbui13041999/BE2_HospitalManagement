<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BhytLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'card_number' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'card_number.required' => 'Vui lòng nhập mã thẻ BHYT.',
        ];
    }
}
