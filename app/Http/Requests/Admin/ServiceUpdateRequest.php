<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'service_name'     => $this->normalizeText($this->input('service_name'), strip: true),
            'description'      => $this->normalizeText($this->input('description'), strip: true, multiline: true),
            'duration_minutes' => $this->normalizeNumber($this->input('duration_minutes')),
        ]);
    }

    public function rules(): array
    {
        // service_code KHÔNG được phép sửa (khóa nghiệp vụ)
        return [
            'service_name'     => [
                'required',
                'string',
                'max:150',
                function ($attribute, $value, $fail) {
                    if (trim($value) === '') {
                        $fail('Tên dịch vụ không được chỉ chứa khoảng trắng.');
                    }
                },
            ],
            'department_id'    => 'nullable|exists:departments,department_id',
            'description'      => 'nullable|string|max:500',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status'           => 'required|boolean',
            // Optimistic lock token (kiểm tra xung đột cập nhật 2 tab)
            '_lock_version'    => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'service_name.required'     => 'Tên dịch vụ là bắt buộc.',
            'service_name.max'          => 'Tên dịch vụ không được vượt quá 150 ký tự.',
            'department_id.exists'      => 'Khoa được chọn không hợp lệ.',
            'description.max'           => 'Mô tả không được vượt quá 500 ký tự.',
            'duration_minutes.required' => 'Thời gian thực hiện là bắt buộc.',
            'duration_minutes.integer'  => 'Thời gian phải là số nguyên (ví dụ: 30, 60). Không nhập chữ cái hay ký tự đặc biệt.',
            'duration_minutes.min'      => 'Thời gian tối thiểu là 5 phút.',
            'duration_minutes.max'      => 'Thời gian tối đa là 480 phút (8 giờ).',
            'status.required'           => 'Trạng thái là bắt buộc.',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function normalizeText(mixed $value, bool $strip = false, bool $multiline = false): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }
        $value = str_replace("\u{3000}", ' ', $value);
        $value = mb_convert_kana($value, 'n', 'UTF-8');
        if ($strip) {
            $value = strip_tags($value);
        }
        $value = trim($value);
        $value = $multiline
            ? preg_replace("/[ \t]+/u", ' ', $value)
            : preg_replace('/\s+/u', ' ', $value);

        return $value ?? '';
    }

    private function normalizeNumber(mixed $value): mixed
    {
        if (!is_string($value) && !is_numeric($value)) return $value;
        $str = str_replace("\u{3000}", '', (string) $value);
        $str = mb_convert_kana($str, 'n', 'UTF-8');
        return trim($str);
    }
}
