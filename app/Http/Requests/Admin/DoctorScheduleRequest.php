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

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'doctor_id'     => 'required|exists:Doctors,doctor_id',
            'room_id'       => 'required|exists:Rooms,room_id',
            'work_date'     => 'required|date' . ($isUpdate ? '' : '|after_or_equal:today'),
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:5|max:120',
            'max_slot'      => 'required|integer|min:1|max:100',
            'status'        => 'required|in:' . implode(',', DoctorSchedule::STATUSES),
            'note'          => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'work_date.after_or_equal' => 'Ngày làm việc phải từ hôm nay trở đi.',
            'end_time.after'           => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ];
    }
}
