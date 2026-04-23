<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = 'Doctors';
    protected $primaryKey = 'doctor_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'full_name', 'department_id',
        'experience', 'price', 'avatar_url', 'bio', 'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id', 'doctor_id');
    }
}
