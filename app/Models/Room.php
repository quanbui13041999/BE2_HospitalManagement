<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'Rooms';
    protected $primaryKey = 'room_id';
    public $timestamps = false;

    protected $fillable = [
        'room_code',
        'room_name',
        'department_id',
        'room_type',
        'status',
        'notes',
    ];

    const ROOM_TYPES   = ['Khám', 'Thủ thuật', 'Siêu âm', 'Xét nghiệm'];
    const ROOM_STATUSES = ['Đang sử dụng', 'Trống', 'Bảo trì', 'Vệ sinh'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'room_id', 'room_id');
    }

    public function todaySchedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'room_id', 'room_id')
            ->whereDate('work_date', today());
    }
}
