<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorDayOff extends Model
{
    protected $table = 'DoctorDaysOff';
    protected $primaryKey = 'day_off_id';
    public $timestamps = false;

    protected $fillable = [
        'doctor_id', 'off_date', 'reason',
    ];

    protected $casts = [
        'off_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }
}
