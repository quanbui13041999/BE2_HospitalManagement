<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecord extends Model
{
    protected $primaryKey = 'record_id';

    protected $fillable = [
        'record_code',
        'patient_id',
        'patient_name',
        'patient_code',
        'doctor_id',
        'doctor_name',
        'appointment_id',
        'exam_date',
        'exam_time',
        'visit_type',
        'chief_complaint',
    ];

    protected $casts = [
        'exam_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function vitalSigns(): HasOne
    {
        return $this->hasOne(VitalSigns::class, 'record_id', 'record_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class, 'record_id', 'record_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'record_id', 'record_id');
    }

    public function medicalOrders(): HasMany
    {
        return $this->hasMany(MedicalOrder::class, 'record_id', 'record_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MedicalAttachment::class, 'record_id', 'record_id');
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(RecordAllergy::class, 'record_id', 'record_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    // ── Helpers ────────────────────────────────────────────────────

    public function getAllergyWarning(): ?string
    {
        $allergies = $this->allergies->pluck('allergen')->implode(', ');
        return $allergies ?: null;
    }

    public static function generateRecordCode(): string
    {
        $prefix = 'CK-' . now()->format('Y');
        $last = static::where('record_code', 'like', $prefix . '-%')
            ->orderByDesc('record_id')
            ->first();
        $seq = $last ? ((int) substr($last->record_code, -4)) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
