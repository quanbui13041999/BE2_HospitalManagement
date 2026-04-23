<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $table = 'DoctorSchedules';
    protected $primaryKey = 'schedule_id';
    public $timestamps = false;

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
    ];

    const STATUSES = ['Hoạt động', 'Tạm dừng', 'Đã huỷ'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'schedule_id', 'schedule_id');
    }

    // Số slot đã đặt (không tính huỷ / dời)
    public function getBookedSlotsAttribute(): int
    {
        return $this->appointments()
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->count();
    }

    // Số slot còn trống
    public function getRemainingSlotAttribute(): int
    {
        return max(0, $this->max_slot - $this->booked_slots);
    }
}
