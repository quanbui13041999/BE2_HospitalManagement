<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceTypeRequest extends FormRequest
{
    protected const NAME_REGEX = '/^[\pL\pM]+(?: [\pL\pM]+)*$/u';
    protected const DESCRIPTION_REGEX = '/^[\pL\pM]+(?: [\pL\pM]+)*$/u';
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
                Rule::unique('device_types', 'name')->ignore($this->route('device_type')),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                'regex:' . self::DESCRIPTION_REGEX,
                'regex:' . self::HAS_LETTER_REGEX,
                'not_regex:' . self::EDGE_SPACE_REGEX,
            ],
            'lock_version' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên danh mục thiết bị.',
            'name.max' => 'Tên danh mục tối đa 120 ký tự.',
            'name.regex' => 'Tên danh mục chỉ được nhập chữ và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ.',
            'name.not_regex' => 'Tên danh mục không được có khoảng trắng ở đầu hoặc cuối.',
            'name.unique' => 'Tên danh mục thiết bị đã tồn tại.',
            'description.max' => 'Mô tả tối đa 1000 ký tự.',
            'description.regex' => 'Mô tả chỉ được nhập chữ và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ.',
            'description.not_regex' => 'Mô tả không được có khoảng trắng ở đầu hoặc cuối.',
            'lock_version.required' => 'Thiếu phiên bản dữ liệu. Trang sẽ được tải lại để cập nhật.',
        ];
    }
}
