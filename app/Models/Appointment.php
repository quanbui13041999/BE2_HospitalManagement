<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    // Chỉ định đúng tên bảng trong database của bạn
    protected $table = 'Appointments'; 
    
    // Chỉ định khóa chính
    protected $primaryKey = 'appointment_id';

    // Bảng không có cột updated_at
    const UPDATED_AT = null;

    // Các cột được phép insert dữ liệu
    protected $fillable = [
        'user_id',
        'schedule_id',
        'service_id',
        'note',
        'status'
    ];
}