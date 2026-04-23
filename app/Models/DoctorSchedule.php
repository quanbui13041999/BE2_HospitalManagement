<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $table      = 'doctorschedules';
    protected $primaryKey = 'schedule_id';
    public $timestamps    = false;

    protected $fillable = [
        'doctor_id','room_id','work_date','start_time',
        'end_time','slot_duration','max_slot','status','note',
    ];

    protected $casts = [
        'work_date'     => 'date',
        'slot_duration' => 'integer',
        'max_slot'      => 'integer',
    ];

    // ── Relations ──
    public function doctor()       { return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id'); }
    public function room()         { return $this->belongsTo(Room::class, 'room_id', 'room_id'); }
    public function appointments() { return $this->hasMany(Appointment::class, 'schedule_id', 'schedule_id'); }

    // ── Helpers ──
    public function getBookedCountAttribute()
    {
        return $this->appointments()
            ->whereNotIn('status', ['Đã hủy','Dời lịch','Giữ slot'])
            ->count();
    }

    public function getRemainingSlotAttribute()
    {
        return max(0, $this->max_slot - $this->booked_count);
    }

    public function isFull(): bool
    {
        return $this->booked_count >= $this->max_slot;
    }

    // Sinh danh sách khung giờ
    public function generateTimeSlots(): array
    {
        $slots = [];
        [$h, $m] = explode(':', $this->start_time);
        [$eh, $em] = explode(':', $this->end_time);
        $endMins = (int)$eh * 60 + (int)$em;
        $h = (int)$h; $m = (int)$m;

        while ($h * 60 + $m + $this->slot_duration <= $endMins) {
            $slots[] = sprintf('%02d:%02d', $h, $m);
            $m += $this->slot_duration;
            if ($m >= 60) { $h++; $m -= 60; }
        }
        return $slots;
    }

    // ── Scopes ──
    public function scopeActive($q)            { return $q->where('doctorschedules.status', 'Hoạt động'); }
    public function scopeByDoctor($q, $id)     { return $q->where('doctor_id', $id); }
    public function scopeByDate($q, $date)     { return $q->where('work_date', $date); }
    public function scopeUpcoming($q)          { return $q->where('work_date', '>=', now()->toDateString()); }
}


class DoctorDayOff extends Model
{
    protected $table      = 'doctordaysoff';
    protected $primaryKey = 'day_off_id';
    public $timestamps    = false;

    protected $fillable = ['doctor_id','off_date','reason','created_at'];
    protected $casts    = ['off_date' => 'date', 'created_at' => 'datetime'];

    public function doctor() { return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id'); }
}