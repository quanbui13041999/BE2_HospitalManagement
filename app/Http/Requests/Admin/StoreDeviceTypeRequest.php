<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceTypeRequest extends FormRequest
{
    protected const NAME_REGEX = '/^[\pL\pM .,\(\)\/+\-]+$/u';
    protected const DESCRIPTION_REGEX = '/^[\pL\pM .,;:\(\)\/+\-\r\n]+$/u';
    protected const HAS_LETTER_REGEX = '/[\pL]/u';
    protected const EDGE_SPACE_REGEX = '/^\s|\s$/u';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                'regex:' . self::NAME_REGEX,
                'regex:' . self::HAS_LETTER_REGEX,
                'not_regex:' . self::EDGE_SPACE_REGEX,
                Rule::unique('device_types', 'name'),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                'regex:' . self::DESCRIPTION_REGEX,
                'regex:' . self::HAS_LETTER_REGEX,
                'not_regex:' . self::EDGE_SPACE_REGEX,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên danh mục thiết bị.',
            'name.max' => 'Tên danh mục tối đa 120 ký tự.',
            'name.regex' => 'Tên danh mục phải có chữ và không được chứa số hoặc ký tự lạ.',
            'name.not_regex' => 'Tên danh mục không được có khoảng trắng ở đầu hoặc cuối.',
            'name.unique' => 'Tên danh mục thiết bị đã tồn tại.',
            'description.max' => 'Mô tả tối đa 1000 ký tự.',
            'description.regex' => 'Mô tả phải có chữ và không được chứa số hoặc ký tự lạ.',
            'description.not_regex' => 'Mô tả không được có khoảng trắng ở đầu hoặc cuối.',
        ];
    }
}
