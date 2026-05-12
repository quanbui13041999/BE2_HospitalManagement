<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
