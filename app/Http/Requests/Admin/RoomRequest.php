<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa dữ liệu đầu vào trước khi validate:
     * - Trim khoảng trắng đầu/cuối
     * - Xử lý khoảng trắng full-width (U+3000 = 　)
     * - Loại bỏ HTML tags (chống paste mã HTML)
     * - Chuẩn hóa số full-width (０１２...) về ASCII
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'room_code' => $this->normalizeText($this->input('room_code'), strip: true),
            'room_name' => $this->normalizeText($this->input('room_name'), strip: true),
            'notes'     => $this->normalizeText($this->input('notes'), strip: true, multiline: true),
        ]);
    }

    public function rules(): array
    {
        // Khi UPDATE, không cho phép thay đổi room_code (khóa nghiệp vụ)
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $roomId   = $this->route('room')?->room_id;

        $rules = [
            'room_name'     => [
                'nullable',
                'string',
                'max:100',
                // Kiểm tra không được chỉ có khoảng trắng
                function ($attribute, $value, $fail) {
                    if ($value !== null && trim($value) === '') {
                        $fail('Tên phòng không được chỉ chứa khoảng trắng.');
                    }
                },
            ],
            'department_id' => 'nullable|exists:departments,department_id',
            'room_type'     => 'required|in:' . implode(',', Room::ROOM_TYPES),
            'status'        => 'required|in:' . implode(',', Room::ROOM_STATUSES),
            'notes'         => 'nullable|string|max:500',
        ];

        if (!$isUpdate) {
            $rules['room_code'] = [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9\-\.]+$/',
                'unique:rooms,room_code',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'room_code.required' => 'Mã phòng là bắt buộc.',
            'room_code.max'      => 'Mã phòng không được vượt quá 20 ký tự.',
            'room_code.regex'    => 'Mã phòng chỉ được chứa chữ cái, chữ số, dấu gạch ngang và dấu chấm.',
            'room_code.unique'   => 'Mã phòng đã tồn tại trong hệ thống.',
            'room_name.max'      => 'Tên phòng không được vượt quá 100 ký tự.',
            'department_id.exists'=> 'Khoa được chọn không hợp lệ.',
            'room_type.required' => 'Loại phòng là bắt buộc.',
            'room_type.in'       => 'Loại phòng không hợp lệ. Vui lòng chọn từ danh sách.',
            'status.required'    => 'Trạng thái là bắt buộc.',
            'status.in'          => 'Trạng thái không hợp lệ. Vui lòng chọn từ danh sách.',
            'notes.max'          => 'Ghi chú không được vượt quá 500 ký tự.',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Chuẩn hóa text:
     * - Thay khoảng trắng full-width (U+3000) thành space thường
     * - Trim khoảng trắng đầu/cuối
     * - Strip HTML tags nếu strip=true
     * - Chuẩn hóa khoảng trắng thừa ở giữa
     * - Convert số full-width về ASCII
     */
    private function normalizeText(mixed $value, bool $strip = false, bool $multiline = false): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        // 1. Convert khoảng trắng full-width (　= U+3000) → space thường
        $value = str_replace("\u{3000}", ' ', $value);

        // 2. Convert số full-width về ASCII (０→0, １→1, ...)
        $value = mb_convert_kana($value, 'n', 'UTF-8');

        // 3. Strip HTML tags (loại bỏ paste HTML từ vnexpress...)
        if ($strip) {
            $stripped = strip_tags($value);
            // Nếu sau khi strip nội dung thay đổi nhiều → có HTML
            $value = $stripped;
        }

        // 4. Trim đầu/cuối
        $value = trim($value);

        // 5. Chuẩn hóa khoảng trắng thừa
        if ($multiline) {
            $value = preg_replace("/[ \t]+/u", ' ', $value);
        } else {
            $value = preg_replace('/\s+/u', ' ', $value);
        }

        return $value ?? '';
    }
}
