<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TreatmentReminder extends Model
{
    protected $table = 'treatmentreminders';
    protected $primaryKey = 'reminder_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'record_id', 'reminder_type', 'remind_at', 'message', 'is_sent',
    ];

    protected $casts = [
        'remind_at'  => 'datetime',
        'is_sent'    => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user()        { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function medicalRecord(){ return $this->belongsTo(MedicalRecord::class, 'record_id', 'record_id'); }
    public function confirmation() { return $this->hasOne(TreatmentConfirmation::class, 'reminder_id', 'reminder_id'); }

    // Scopes
    public function scopeToday($q)  { return $q->whereDate('remind_at', today()); }
    public function scopeForUser($q, $userId) { return $q->where('user_id', $userId); }
    public function scopeMedicine($q) { return $q->where('reminder_type', 'medicine'); }
    public function scopeInstruction($q){ return $q->where('reminder_type', 'instruction'); }
    public function scopePending($q) { return $q->where('is_sent', false)->where('remind_at', '<=', now()); }

    // Helpers
    public function isConfirmed(): bool  { return $this->confirmation()->exists(); }
    public function isDangerous(): bool  { return Str::contains($this->message, 'NGUY HIỂM') || Str::contains($this->message, 'dị ứng'); }
    public function getTimeLabelAttribute(): string { return $this->remind_at->format('H:i'); }
}
