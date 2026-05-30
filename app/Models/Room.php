<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $primaryKey = 'room_id';
    public $timestamps = true;

    const ROOM_TYPES = [
        'Khám bệnh',
        'Cấp cứu',
        'Điều trị',
        'Phòng mổ',
        'Xét nghiệm',
        'Chẩn đoán hình ảnh',
        'Vật lý trị liệu',
        'Hành chính',
    ];

    const ROOM_STATUSES = [
        'Hoạt động',
        'Trống',
        'Bảo trì',
        'Vệ sinh',
    ];

    protected $fillable = [
        'room_code',
        'room_name',
        'room_type',
        'department_id',
        'status',
        'notes',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    // ĐÚNG: RoomService.php -> with('doctor.department')
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'room_id', 'room_id');
    }
}