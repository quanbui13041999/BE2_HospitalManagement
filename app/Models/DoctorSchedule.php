<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $table = 'doctor_schedules';
    protected $primaryKey = 'schedule_id';
    public $timestamps = true;

    const STATUSES = ['Scheduled', 'In Progress', 'Completed', 'Cancelled', 'Full'];

    protected $fillable = [
        'doctor_id',
        'room_id',
        'work_date',
        'start_time',
        'end_time',
        'max_slot',
        'booked_slots',
        'status',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'max_slot' => 'integer',
        'booked_slots' => 'integer',
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