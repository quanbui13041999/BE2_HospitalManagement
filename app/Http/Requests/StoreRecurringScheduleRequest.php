<?php

// ═══════════════════════════════════════════════════════════════════════════════
// app/Http/Requests/StoreRecurringScheduleRequest.php
// ═══════════════════════════════════════════════════════════════════════════════

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'required|integer|exists:Doctors,doctor_id',
            'room_id' => 'nullable|integer',

            // Mảng các ngày trong tuần: 0=CN, 1=T2 … 6=T7
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',

            // Ca sáng
            'morning_enabled' => 'boolean',
            'morning_start' => 'required_if:morning_enabled,true|date_format:H:i',
            'morning_end' => 'required_if:morning_enabled,true|date_format:H:i|after:morning_start',

            // Ca chiều
            'afternoon_enabled' => 'boolean',
            'afternoon_start' => 'required_if:afternoon_enabled,true|date_format:H:i',
            'afternoon_end' => 'required_if:afternoon_enabled,true|date_format:H:i|after:afternoon_start',

            // Slot
            'slot_duration' => 'required|integer|in:15,20,30,45,60',
            'max_slot' => 'required|integer|min:1|max:50',

            // Số tuần áp dụng
            'apply_weeks' => 'required|integer|in:2,4,8,12',
        ];
    }

    public function messages(): array
    {
        return [
            'days_of_week.min' => 'Vui lòng chọn ít nhất 1 ngày làm việc.',
            'morning_start.required_if' => 'Ca sáng cần có giờ bắt đầu.',
            'morning_end.required_if' => 'Ca sáng cần có giờ kết thúc.',
            'morning_end.after' => 'Giờ kết thúc ca sáng phải sau giờ bắt đầu.',
            'afternoon_start.required_if' => 'Ca chiều cần có giờ bắt đầu.',
            'afternoon_end.required_if' => 'Ca chiều cần có giờ kết thúc.',
            'afternoon_end.after' => 'Giờ kết thúc ca chiều phải sau giờ bắt đầu.',
        ];
    }

    /** Ít nhất 1 ca phải được bật */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $morning = $this->boolean('morning_enabled');
            $afternoon = $this->boolean('afternoon_enabled');
            if (! $morning && ! $afternoon) {
                $v->errors()->add('sessions', 'Vui lòng bật ít nhất 1 ca khám (sáng hoặc chiều).');
            }
        });
    }
}
