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
            'card_number' => 'required|string|min:10|max:20|regex:/^[A-Z]{2}[0-9]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'card_number.required' => 'Vui lòng nhập mã thẻ BHYT.',
            'card_number.regex'    => 'Mã thẻ BHYT không đúng định dạng (vd: HC4230145678910).',
            'card_number.min'      => 'Mã thẻ BHYT phải có ít nhất 10 ký tự.',
        ];
    }
}
