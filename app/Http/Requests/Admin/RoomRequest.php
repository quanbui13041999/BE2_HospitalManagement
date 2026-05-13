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
        $roomId = $this->route('room')?->room_id;

        return [
            'room_code'     => [
                'required',
                'string',
                'max:20',
                'unique:Rooms,room_code' . ($roomId ? ",{$roomId},room_id" : ''),
            ],
            'room_name'     => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:Departments,department_id',
            'room_type'     => 'required|in:' . implode(',', Room::ROOM_TYPES),
            'status'        => 'required|in:' . implode(',', Room::ROOM_STATUSES),
            'notes'         => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'room_code.unique' => 'Mã phòng đã tồn tại.',
        ];
    }
}
