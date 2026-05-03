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
    
    public function scopeWithReviewStats($q)
    {
        return $q->leftJoinSub(
            static::getReviewStatsQuery(),
            'rv',
            'rv.doctor_id',
            '=',
            'doctors.doctor_id'
        )->addSelect(
            'doctors.*',
            \Illuminate\Support\Facades\DB::raw('COALESCE(rv.avg_rating, 0) as avg_rating'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(rv.total_reviews, 0) as total_reviews')
        );
    }
    
    public static function getReviewStatsQuery()
    {
        return \Illuminate\Support\Facades\DB::table('reviews')
            ->select(
                'doctor_id',
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(rating), 2) as avg_rating'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_reviews')
            )
            ->groupBy('doctor_id');
    }
}
