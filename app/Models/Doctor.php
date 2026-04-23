<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table      = 'doctors';
    protected $primaryKey = 'doctor_id';
    public $timestamps    = false;

    protected $fillable = [
        'user_id',
        'full_name',
        'department_id',
        'experience',
        'price',
        'avatar_url',
        'bio',
        'status',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'status'     => 'boolean',
        'experience' => 'integer',
    ];

    // ── Relations ──
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id', 'doctor_id');
    }
    public function daysOff()
    {
        return $this->hasMany(DoctorDayOff::class, 'doctor_id', 'doctor_id');
    }
    public function reviews()
    {
        return $this->hasMany(Review::class, 'doctor_id', 'doctor_id');
    }
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_id', 'doctor_id');
    }
    public function vaccinationRecords()
    {
        return $this->hasMany(VaccinationRecord::class, 'doctor_id', 'doctor_id');
    }
    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class, 'doctor_id', 'doctor_id');
    }

    // ── Accessors ──
    public function getAvgRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    // ── Scopes ──
    public function scopeActive($q)
    {
        return $q->where('doctors.status', 1);
    }
    public function scopeByDepartment($q, $id)
    {
        return $q->where('department_id', $id);
    }
}
