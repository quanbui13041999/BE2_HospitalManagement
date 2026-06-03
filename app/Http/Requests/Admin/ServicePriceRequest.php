<?php

namespace App\Http\Requests\Admin;

use App\Models\ServicePrice;
use Illuminate\Foundation\Http\FormRequest;

class ServicePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi validate:
     * - Convert số full-width (０→0) cho price
     * - Trim các trường date
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'price'          => $this->normalizeNumber($this->input('price')),
            'effective_date' => $this->normalizeDate($this->input('effective_date')),
            'end_date'       => $this->normalizeDate($this->input('end_date')),
        ]);
    }

    public function rules(): array
    {
        return [
            'price_type'     => 'required|in:' . implode(',', ServicePrice::PRICE_TYPES),
            'price'          => 'required|numeric|min:0|max:999999999',
            'effective_date' => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:effective_date',
        ];
    }

    public function messages(): array
    {
        return [
            'price_type.required'          => 'Loại giá là bắt buộc.',
            'price_type.in'                => 'Loại giá không hợp lệ. Chỉ chấp nhận: Thường, BHYT, VIP.',
            'price.required'               => 'Đơn giá là bắt buộc.',
            'price.numeric'                => 'Đơn giá phải là số hợp lệ (không nhập chữ hay ký tự đặc biệt).',
            'price.min'                    => 'Đơn giá không được âm.',
            'price.max'                    => 'Đơn giá không được vượt quá 999,999,999 đ.',
            'effective_date.required'      => 'Ngày áp dụng là bắt buộc.',
            'effective_date.date'          => 'Ngày áp dụng không hợp lệ (định dạng: YYYY-MM-DD).',
            'end_date.date'                => 'Ngày kết thúc không hợp lệ (định dạng: YYYY-MM-DD).',
            'end_date.after_or_equal'      => 'Ngày kết thúc phải bằng hoặc sau ngày áp dụng.',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function normalizeNumber(mixed $value): mixed
    {
        if (!is_string($value) && !is_numeric($value)) return $value;
        $str = str_replace("\u{3000}", '', (string) $value);
        $str = mb_convert_kana($str, 'n', 'UTF-8'); // ０→0
        return trim($str);
    }

    private function normalizeDate(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') return $value;
        return trim(str_replace("\u{3000}", '', $value));
    }
}
