<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueTicket extends Model
{
    protected $primaryKey = 'ticket_id';
    
    protected $fillable = [
        'appointment_id', 'schedule_id', 'user_id',
        'patient_name', 'patient_phone', 'patient_email',
        'queue_date', 'queue_number', 'priority', 'status',
        'priority_sort', 'checkin_time', 'called_at',
        'started_at', 'completed_at', 'est_wait_minutes',
        'notes', 'served_by',
    ];

    protected $casts = [
        'checkin_time' => 'datetime',
        'called_at'    => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'queue_date'   => 'date',
    ];

    // Priority sort mapping
    const PRIORITY_SORT = [
        'emergency' => 1,
        'disabled'  => 2,
        'elderly'   => 3,
        'normal'    => 4,
    ];

    const PRIORITY_LABELS = [
        'normal'    => ['label' => 'Thường',    'color' => 'gray',   'icon' => '👤'],
        'elderly'   => ['label' => 'Cao tuổi',  'color' => 'blue',   'icon' => '👴'],
        'disabled'  => ['label' => 'Khuyết tật','color' => 'purple', 'icon' => '♿'],
        'emergency' => ['label' => 'Cấp cứu',   'color' => 'red',    'icon' => '🚨'],
    ];

    const STATUS_LABELS = [
        'waiting'     => ['label' => 'Đang chờ',    'color' => 'yellow'],
        'calling'     => ['label' => 'Đang gọi',    'color' => 'orange'],
        'in_progress' => ['label' => 'Đang khám',   'color' => 'green'],
        'completed'   => ['label' => 'Hoàn thành',  'color' => 'gray'],
        'skipped'     => ['label' => 'Bỏ qua',      'color' => 'red'],
        'cancelled'   => ['label' => 'Đã hủy',      'color' => 'red'],
    ];

    // Relationships
    public function schedule()    { return $this->belongsTo(DoctorSchedule::class, 'schedule_id'); }
    public function appointment() { return $this->belongsTo(Appointment::class, 'appointment_id'); }
    public function user()        { return $this->belongsTo(User::class, 'user_id'); }
    public function servedBy()    { return $this->belongsTo(User::class, 'served_by', 'user_id'); }

    // Scopes
    public function scopeWaiting($q)     { return $q->where('status', 'waiting'); }
    public function scopeActive($q)      { return $q->whereIn('status', ['waiting', 'calling', 'in_progress']); }
    public function scopeForSchedule($q, $scheduleId) {
        return $q->where('schedule_id', $scheduleId)->whereDate('queue_date', today());
    }
    public function scopeOrdered($q) {
        return $q->orderBy('priority_sort')->orderBy('queue_number');
    }

    // Helpers
    public function getPriorityLabelAttribute()  { return self::PRIORITY_LABELS[$this->priority]['label'] ?? 'Thường'; }
    public function getPriorityColorAttribute()  { return self::PRIORITY_LABELS[$this->priority]['color'] ?? 'gray'; }
    public function getPriorityIconAttribute()   { return self::PRIORITY_LABELS[$this->priority]['icon'] ?? '👤'; }
    public function getStatusLabelAttribute()    { return self::STATUS_LABELS[$this->status]['label'] ?? 'Đang chờ'; }
    public function getStatusColorAttribute()    { return self::STATUS_LABELS[$this->status]['color'] ?? 'yellow'; }
    public function getWaitMinutesAttribute() {
        // Tính thời gian chờ thực tế từ checkin_time
        return $this->checkin_time ? now()->diffInMinutes($this->checkin_time) : 0;
    }
}
