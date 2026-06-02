<?php

namespace App\Http\Requests\Admin;

use App\Models\Device;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceRequest extends FormRequest
{
    protected const NAME_REGEX = '/^[\pL\pM .,\(\)\/+\-]+$/u';
    protected const CODE_REGEX = '/^[A-Za-z0-9._-]+$/';
    protected const HAS_LETTER_REGEX = '/[\pL]/u';
    protected const EDGE_SPACE_REGEX = '/^\s|\s$/u';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper((string) $this->input('code')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                'regex:' . self::NAME_REGEX,
                'regex:' . self::HAS_LETTER_REGEX,
                'not_regex:' . self::EDGE_SPACE_REGEX,
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:' . self::CODE_REGEX,
                Rule::unique('devices', 'code')->ignore($this->route('device')),
            ],
            'device_type_id' => ['required', 'integer', Rule::exists('device_types', 'id')],
            'status' => ['required', Rule::in(array_keys(Device::STATUSES))],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'lock_version' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên thiết bị.',
            'name.max' => 'Tên thiết bị tối đa 150 ký tự.',
            'name.regex' => 'Tên thiết bị phải có chữ và không được chứa số hoặc ký tự lạ.',
            'name.not_regex' => 'Tên thiết bị không được có khoảng trắng ở đầu hoặc cuối.',
            'code.required' => 'Vui lòng nhập mã thiết bị.',
            'code.max' => 'Mã thiết bị tối đa 50 ký tự.',
            'code.regex' => 'Mã thiết bị chỉ được gồm chữ không dấu, số, dấu chấm, gạch ngang và gạch dưới.',
            'code.unique' => 'Mã thiết bị đã tồn tại.',
            'device_type_id.required' => 'Vui lòng chọn danh mục thiết bị.',
            'device_type_id.exists' => 'Danh mục thiết bị không hợp lệ hoặc vừa bị xóa.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái thiết bị không hợp lệ.',
            'purchase_date.date' => 'Ngày mua không hợp lệ.',
            'purchase_date.before_or_equal' => 'Ngày mua không được lớn hơn hôm nay.',
            'lock_version.required' => 'Thiếu phiên bản dữ liệu. Trang sẽ được tải lại để cập nhật.',
        ];
    }
}
