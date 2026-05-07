<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $table = 'doctorschedules';
    protected $primaryKey = 'schedule_id';
    public $timestamps = false;

    const STATUSES = ['Hoạt động', 'Tạm dừng', 'Đã huỷ'];

    protected $fillable = [
        'doctor_id',
        'room_id',
        'work_date',
        'start_time',
        'end_time',
        'slot_duration',
        'max_slot',
        'status',
        'note',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'max_slot' => 'integer',
        'slot_duration' => 'integer',
    ];

    public function doctor()
    {
        // ĐÚNG: RoomService.php -> $item->doctor->full_name
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    public function room()
    {
        // ĐÚNG: RoomService.php -> $item->room->room_code
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }
}   