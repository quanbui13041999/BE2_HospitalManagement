<?php
// app/Models/Appointment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table      = 'Appointments';
    protected $primaryKey = 'appointment_id';
    public    $timestamps = true;

    protected $fillable = [
        'user_id', 'schedule_id', 'service_id',
        'appointment_time', 'queue_number',
        'status', 'note', 'cancel_reason',
        'slot_hold_expire', 'rescheduled_from',
        'created_at',
    ];

    protected $casts = [
        'appointment_time' => 'datetime',
        'slot_hold_expire' => 'datetime',
        'created_at'       => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function schedule()
    {
        return $this->belongsTo(DoctorSchedule::class, 'schedule_id', 'schedule_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    public function rescheduledFrom()
    {
        return $this->belongsTo(Appointment::class, 'rescheduled_from', 'appointment_id');
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id', 'appointment_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'appointment_id', 'appointment_id');
    }

    public function checkIn()
    {
        return $this->hasOne(CheckIn::class, 'appointment_id', 'appointment_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'appointment_id', 'appointment_id');
    }

    // ── Query Scopes ───────────────────────────────────────────────
    /**
     * Lọc appointment của một user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Lọc appointment sắp tới (Chờ xác nhận, Đã xác nhận và trong tương lai)
     */
    public function scopeUpcoming($query)
    {
        return $query
            ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận'])
            ->where('appointment_time', '>=', now());
    }

    /**
     * Lọc appointment đã hoàn thành
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'Đã khám');
    }

    /**
     * Lọc appointment đã hủy hoặc dời
     */
    public function scopeCancelled($query)
    {
        return $query->whereIn('status', ['Đã hủy', 'Dời lịch']);
    }

    /**
     * Lọc appointment theo trạng thái
     */
    public function scopeByStatus($query, array $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * Sắp xếp theo ngày
     */
    public function scopeOrderByDate($query, string $direction = 'desc')
    {
        return $query->orderBy('appointment_time', $direction);
    }

    /**
     * Lọc appointment theo ngày làm việc
     */
    public function scopeOnDate($query, string $date)
    {
        return $query->whereDate('appointment_time', $date);
    }

    /**
     * Lọc appointment cho một bác sĩ
     */
    public function scopeForDoctor($query, int $doctorId)
    {
        return $query
            ->join('doctor_schedules', 'appointments.schedule_id', '=', 'doctor_schedules.schedule_id')
            ->where('doctor_schedules.doctor_id', $doctorId);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public function getAppointmentTimeEndAttribute()
    {
        if (!$this->schedule) return $this->appointment_time;
        $start = \Carbon\Carbon::parse($this->appointment_time);
        return $start->copy()->addMinutes($this->schedule->slot_duration);
    }

    public function canCancel(): bool
    {
        if (!in_array($this->status, ['Chờ xác nhận', 'Đã xác nhận'])) return false;
        $schedule = $this->schedule;
        if (!$schedule) return false;
        $time = \Carbon\Carbon::parse($schedule->work_date . ' ' . $schedule->start_time);
        return $time->diffInHours(now(), false) <= -2;
    }

    public function canReschedule(): bool
    {
        return $this->canCancel();
    }

    public function canReview(): bool
    {
        return $this->status === 'Hoàn thành' && !$this->review;
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'Chờ xác nhận' => 'warning',
            'Đã xác nhận'  => 'info',
            'Đang khám'    => 'primary',
            'Hoàn thành'   => 'success',
            'Đã hủy'       => 'danger',
            'Dời lịch'     => 'secondary',
            'Giữ slot'     => 'light',
            default        => 'secondary',
        };
    }

    public function statusIcon(): string
    {
        return match($this->status) {
            'Chờ xác nhận' => '⏳',
            'Đã xác nhận'  => '✅',
            'Đang khám'    => '🩺',
            'Hoàn thành'   => '🎉',
            'Đã hủy'       => '❌',
            'Dời lịch'     => '🔄',
            default        => '📋',
        };
    }
}
