<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DoctorSchedule extends Model
{
    protected $table = 'doctorschedules';
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
        'status',   // active | blocked | full
        'note',
    ];

    protected $casts = [
        'work_date' => 'date',
        'max_slot' => 'integer',
        'slot_duration' => 'integer',
    ];

    const STATUSES = [
        'Hoạt động',
        'Tạm dừng',
        'Đã huỷ',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // DoctorSchedule.doctor_id references Doctors.doctor_id
        return $this->belongsTo(\App\Models\Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class, 'schedule_id', 'schedule_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Chỉ lấy lịch còn hiệu lực (chưa bị block) */
    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', ['active', 'Hoạt động']);
    }

    /** Lịch trong khoảng ngày */
    public function scopeBetweenDates(Builder $q, string $from, string $to): Builder
    {
        return $q->whereBetween('work_date', [$from, $to]);
    }

    /** Lịch của 1 bác sĩ */
    public function scopeForDoctor(Builder $q, int $doctorId): Builder
    {
        return $q->where('doctor_id', $doctorId);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Đây là ca sáng nếu start_time < 12:00
     */
    public function isMorning(): bool
    {
        return $this->start_time < '12:00:00';
    }

    /**
     * Tổng số slot có thể đặt trong 1 ca
     */
    public function totalSlots(): int
    {
        [$sh, $sm] = explode(':', $this->start_time);
        [$eh, $em] = explode(':', $this->end_time);
        $minutes = ($eh * 60 + $em) - ($sh * 60 + $sm);

        return max(0, (int) floor($minutes / $this->slot_duration));
    }

    /**
     * Số slot đã được đặt (pending hoặc confirmed)
     */
    public function getBookedSlotsAttribute(): int
    {
        // Nếu đã load relationship appointments thì dùng collection để tránh query thêm
        if ($this->relationLoaded('appointments')) {
            return $this->appointments->whereIn('status', ['pending', 'confirmed', 'Chờ xác nhận', 'Đã xác nhận'])->count();
        }
        
        return $this->appointments()
            ->whereIn('status', ['pending', 'confirmed', 'Chờ xác nhận', 'Đã xác nhận'])
            ->count();
    }

    /**
     * Số slot còn trống (chưa bị đặt hoặc giữ chỗ)
     */
    public function availableSlots(): int
    {
        return max(0, $this->max_slot - $this->booked_slots);
    }

    /**
     * Lấy tất cả appointment chưa hoàn thành / chưa huỷ của lịch này
     */
    public function activeAppointments()
    {
        return $this->appointments()
            ->with('user')
            ->whereIn('status', ['pending', 'confirmed', 'Chờ xác nhận', 'Đã xác nhận'])
            ->get();
    }
}