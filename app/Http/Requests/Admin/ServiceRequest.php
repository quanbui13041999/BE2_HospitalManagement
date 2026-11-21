<?php

namespace App\Http\Requests\Admin;

use App\Models\ServicePrice;
use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa dữ liệu đầu vào trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'service_code'     => $this->normalizeCode($this->input('service_code')),
            'service_name'     => $this->normalizeText($this->input('service_name'), strip: true),
            'description'      => $this->normalizeText($this->input('description'), strip: true, multiline: true),
            'duration_minutes' => $this->normalizeNumber($this->input('duration_minutes')),
        ]);
    }

    public function rules(): array
    {
        $priceTypes = implode(',', ServicePrice::PRICE_TYPES);

        return [
            'service_code'            => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9\-\.]+$/',
                'unique:services,service_code',
            ],
            'service_name'            => [
                'required',
                'string',
                'max:150',
                function ($attribute, $value, $fail) {
                    if (trim($value) === '') {
                        $fail('Tên dịch vụ không được chỉ chứa khoảng trắng.');
                    }
                },
            ],
            'department_id'           => 'nullable|exists:departments,department_id',
            'description'             => 'nullable|string|max:500',
            'duration_minutes'        => 'required|integer|min:5|max:480',
            'status'                  => 'required|boolean',
            'prices'                  => 'nullable|array',
            'prices.*.price_type'     => "required_with:prices|in:{$priceTypes}",
            'prices.*.price'          => 'required_with:prices|numeric|min:0|max:999999999',
            'prices.*.effective_date' => 'required_with:prices|date',
            'prices.*.end_date'       => 'nullable|date|after_or_equal:prices.*.effective_date',
        ];
    }

    public function messages(): array
    {
        return [
            'service_code.required'         => 'Mã dịch vụ là bắt buộc.',
            'service_code.max'              => 'Mã dịch vụ không được vượt quá 30 ký tự.',
            'service_code.regex'            => 'Mã dịch vụ chỉ chứa chữ cái, chữ số, dấu gạch ngang và dấu chấm (không có khoảng trắng hay ký tự đặc biệt).',
            'service_code.unique'           => 'Mã dịch vụ đã tồn tại trong hệ thống, vui lòng dùng mã khác.',
            'service_name.required'         => 'Tên dịch vụ là bắt buộc.',
            'service_name.max'              => 'Tên dịch vụ không được vượt quá 150 ký tự.',
            'department_id.exists'          => 'Khoa được chọn không hợp lệ.',
            'description.max'               => 'Mô tả không được vượt quá 500 ký tự.',
            'duration_minutes.required'     => 'Thời gian thực hiện là bắt buộc.',
            'duration_minutes.integer'      => 'Thời gian phải là số nguyên (ví dụ: 30, 60). Không nhập chữ cái hay ký tự đặc biệt.',
            'duration_minutes.min'          => 'Thời gian tối thiểu là 5 phút.',
            'duration_minutes.max'          => 'Thời gian tối đa là 480 phút (8 giờ).',
            'status.required'               => 'Trạng thái là bắt buộc.',
            'prices.*.price_type.required_with' => 'Loại giá là bắt buộc khi thêm mức giá.',
            'prices.*.price_type.in'        => 'Loại giá không hợp lệ. Chỉ chấp nhận: Thường, BHYT, VIP.',
            'prices.*.price.required_with'  => 'Giá tiền là bắt buộc khi thêm mức giá.',
            'prices.*.price.numeric'        => 'Giá tiền phải là số hợp lệ (không nhập chữ hay ký tự đặc biệt).',
            'prices.*.price.min'            => 'Giá tiền không được âm.',
            'prices.*.price.max'            => 'Giá tiền không được vượt quá 999,999,999 đ.',
            'prices.*.effective_date.required_with' => 'Ngày hiệu lực là bắt buộc khi thêm mức giá.',
            'prices.*.end_date.after_or_equal' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function normalizeText(mixed $value, bool $strip = false, bool $multiline = false): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }
        $value = str_replace("\u{3000}", ' ', $value);           // full-width space
        $value = mb_convert_kana($value, 'n', 'UTF-8');           // full-width digits
        if ($strip) {
            $value = strip_tags($value);
        }
        $value = trim($value);
        $value = $multiline
            ? preg_replace("/[ \t]+/u", ' ', $value)
            : preg_replace('/\s+/u', ' ', $value);

        return $value ?? '';
    }

    private function normalizeCode(mixed $value): mixed
    {
        if (!is_string($value)) return $value;
        return strtoupper(trim(str_replace("\u{3000}", ' ', $value)));
    }

    private function normalizeNumber(mixed $value): mixed
    {
        if (!is_string($value) && !is_numeric($value)) return $value;
        $str = str_replace("\u{3000}", '', (string) $value);
        $str = mb_convert_kana($str, 'n', 'UTF-8');  // ０→0
        return trim($str);
    }
}
