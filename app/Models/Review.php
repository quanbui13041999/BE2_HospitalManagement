<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table      = 'reviews';
    protected $primaryKey = 'review_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'appointment_id','user_id','doctor_id',
        'rating','comment','doctor_reply',
    ];

    protected $casts = [
        'rating'     => 'integer',
        'created_at' => 'datetime',
    ];

    public function appointment() { return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id'); }
    public function user()        { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function doctor()      { return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id'); }
}


class Notification extends Model
{
    protected $table      = 'notifications';
    protected $primaryKey = 'notification_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id','notif_type','title','content',
        'ref_id','ref_type','is_read',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }

    public function scopeUnread($q) { return $q->where('is_read', false); }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}


class ActivityLog extends Model
{
    protected $table      = 'activitylogs';
    protected $primaryKey = 'log_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = ['user_id','action','ip_address'];
    protected $casts    = ['created_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
}


class TreatmentReminder extends Model
{
    protected $table      = 'treatmentreminders';
    protected $primaryKey = 'reminder_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id','record_id','reminder_type',
        'remind_at','message','is_sent',
    ];

    protected $casts = [
        'remind_at'  => 'datetime',
        'is_sent'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()          { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function medicalRecord() { return $this->belongsTo(MedicalRecord::class, 'record_id', 'record_id'); }

    public function scopePending($q) { return $q->where('is_sent', false)->where('remind_at', '<=', now()); }
}