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

    public function rules(): array
    {
        // Khi UPDATE, không cho phép thay đổi room_code (khóa nghiệp vụ)
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $roomId   = $this->route('room')?->room_id;

        $rules = [
            'room_name'     => 'nullable|string|max:100',
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
            'room_type.in'       => 'Loại phòng không hợp lệ.',
            'status.required'    => 'Trạng thái là bắt buộc.',
            'status.in'          => 'Trạng thái không hợp lệ.',
            'notes.max'          => 'Ghi chú không được vượt quá 500 ký tự.',
        ];
    }
}
