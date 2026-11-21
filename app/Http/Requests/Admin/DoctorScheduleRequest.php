<?php

namespace App\Http\Requests\Admin;

use App\Models\DoctorSchedule;
use Illuminate\Foundation\Http\FormRequest;

class DoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi validate:
     * - Convert số full-width (０→0) trong slot_duration, max_slot
     * - Trim note
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slot_duration' => $this->normalizeNumber($this->input('slot_duration')),
            'max_slot'      => $this->normalizeNumber($this->input('max_slot')),
            'note'          => $this->normalizeText($this->input('note')),
        ]);
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'doctor_id'     => 'required|exists:doctors,doctor_id',
            'room_id'       => 'required|exists:rooms,room_id',
            'work_date'     => 'required|date' . ($isUpdate ? '' : '|after_or_equal:today'),
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:5|max:120',
            'max_slot'      => 'required|integer|min:1|max:100',
            'status'        => 'required|in:' . implode(',', DoctorSchedule::STATUSES),
            'note'          => 'nullable|string|max:255',
            '_lock_version' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required'        => 'Vui lòng chọn bác sĩ.',
            'doctor_id.exists'          => 'Bác sĩ được chọn không tồn tại trong hệ thống.',
            'room_id.required'          => 'Vui lòng chọn phòng khám.',
            'room_id.exists'            => 'Phòng khám được chọn không tồn tại trong hệ thống.',
            'work_date.required'        => 'Ngày làm việc là bắt buộc.',
            'work_date.date'            => 'Ngày làm việc không hợp lệ.',
            'work_date.after_or_equal'  => 'Ngày làm việc phải từ hôm nay trở đi.',
            'start_time.required'       => 'Giờ bắt đầu là bắt buộc.',
            'start_time.date_format'    => 'Giờ bắt đầu phải đúng định dạng HH:MM (ví dụ: 08:00).',
            'end_time.required'         => 'Giờ kết thúc là bắt buộc.',
            'end_time.date_format'      => 'Giờ kết thúc phải đúng định dạng HH:MM (ví dụ: 17:00).',
            'end_time.after'            => 'Giờ kết thúc phải sau giờ bắt đầu.',
            'slot_duration.required'    => 'Thời lượng mỗi slot là bắt buộc.',
            'slot_duration.integer'     => 'Thời lượng slot phải là số nguyên (ví dụ: 15, 30). Không nhập chữ hay ký tự đặc biệt.',
            'slot_duration.min'         => 'Thời lượng slot tối thiểu là 5 phút.',
            'slot_duration.max'         => 'Thời lượng slot tối đa là 120 phút.',
            'max_slot.required'         => 'Số slot tối đa là bắt buộc.',
            'max_slot.integer'          => 'Số slot tối đa phải là số nguyên (ví dụ: 10, 20). Không nhập chữ hay ký tự đặc biệt.',
            'max_slot.min'              => 'Số slot tối thiểu là 1.',
            'max_slot.max'              => 'Số slot tối đa là 100.',
            'status.required'           => 'Trạng thái là bắt buộc.',
            'status.in'                 => 'Trạng thái không hợp lệ. Vui lòng chọn từ danh sách.',
            'note.max'                  => 'Ghi chú không được vượt quá 255 ký tự.',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function normalizeNumber(mixed $value): mixed
    {
        if (!is_string($value) && !is_numeric($value)) return $value;
        $str = str_replace("\u{3000}", '', (string) $value);
        $str = mb_convert_kana($str, 'n', 'UTF-8'); // ０→0, ...
        return trim($str);
    }

    private function normalizeText(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') return $value;
        $value = str_replace("\u{3000}", ' ', $value);
        $value = strip_tags($value);
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}
