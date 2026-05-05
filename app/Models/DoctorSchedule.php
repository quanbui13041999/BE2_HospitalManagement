<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $table = 'DoctorSchedules';
    protected $primaryKey = 'schedule_id';
    
    // ⚠️ TẮT timestamps vì bảng KHÔNG có created_at, updated_at
    public $timestamps = false;

    // Constants
    const STATUSES = ['Hoạt động', 'Tạm dừng', 'Đã huỷ'];

    // Fillable properties
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

    // Casts
    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'slot_duration' => 'integer',
        'max_slot' => 'integer',
        'booked_slots' => 'integer',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================
    
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

    // ============================================================
    // ACCESSORS & MUTATORS
    // ============================================================
    
    /**
     * Số slot đã đặt (không tính huỷ / dời lịch)
     */
    public function getBookedSlotsAttribute(): int
    {
        return $this->appointments()
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->count();
    }

    /**
     * Số slot còn trống
     */
    public function getRemainingSlotAttribute(): int
    {
        return max(0, $this->max_slot - $this->booked_slots);
    }

    /**
     * Kiểm tra xem còn slot trống không
     */
    public function hasAvailableSlot(): bool
    {
        return $this->remaining_slot > 0 && $this->status === 'Hoạt động';
    }

    /**
     * Lấy phần trăm đã đặt
     */
    public function getBookedPercentageAttribute(): float
    {
        if ($this->max_slot <= 0) {
            return 0;
        }
        return round(($this->booked_slots / $this->max_slot) * 100, 2);
    }

    // ============================================================
    // SCOPES
    // ============================================================
    
    /**
     * Scope lấy các ca đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Hoạt động');
    }

    /**
     * Scope lấy các ca trong ngày
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('work_date', $date);
    }

    /**
     * Scope lấy các ca theo bác sĩ
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope lấy các ca theo phòng
     */
    public function scopeForRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Scope lấy các ca trong khoảng thời gian
     */
    public function scopeInTimeRange($query, $startTime, $endTime)
    {
        return $query->where(function ($q) use ($startTime, $endTime) {
            $q->where('start_time', '<', $endTime)
              ->where('end_time', '>', $startTime);
        });
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================
    
    /**
     * Kiểm tra xem có thể đặt lịch được không
     */
    public function isBookable(): bool
    {
        return $this->status === 'Hoạt động' && $this->remaining_slot > 0;
    }
}