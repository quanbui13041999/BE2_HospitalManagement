<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecord extends Model
{
    protected $primaryKey = 'record_id';
    protected $table = 'medical_records';
    
    // Tắt timestamps nếu bảng không có created_at, updated_at
    public $timestamps = true; // Hoặc false nếu không có

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
    'status',        // Thêm dòng này
    'status_note',   // Thêm dòng này
];

    protected $casts = [
        'exam_date' => 'date',
    ];

    public const VISIT_TYPE_NEW = 'Khám mới';
    public const VISIT_TYPE_FOLLOW_UP = 'Tái khám';
    public const VISIT_TYPE_EMERGENCY = 'Cấp cứu';

    private const VISIT_TYPE_ALIASES = [
        'khám mới' => self::VISIT_TYPE_NEW,
        'kham moi' => self::VISIT_TYPE_NEW,
        'khám moi' => self::VISIT_TYPE_NEW,
        'kham mới' => self::VISIT_TYPE_NEW,
        'tái khám' => self::VISIT_TYPE_FOLLOW_UP,
        'tai kham' => self::VISIT_TYPE_FOLLOW_UP,
        'tái kham' => self::VISIT_TYPE_FOLLOW_UP,
        'tai khám' => self::VISIT_TYPE_FOLLOW_UP,
        'cấp cứu' => self::VISIT_TYPE_EMERGENCY,
        'cap cuu' => self::VISIT_TYPE_EMERGENCY,
        'cấp cuu' => self::VISIT_TYPE_EMERGENCY,
        'cap cứu' => self::VISIT_TYPE_EMERGENCY,
    ];

    public static function canonicalVisitType(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = mb_strtolower(preg_replace('/\s+/u', ' ', trim($value)), 'UTF-8');

        return self::VISIT_TYPE_ALIASES[$normalized] ?? null;
    }

    public static function visitTypeVariants(?string $value): array
    {
        $canonical = self::canonicalVisitType($value) ?? $value;

        return array_values(array_unique(array_filter([
            $canonical,
            match ($canonical) {
                self::VISIT_TYPE_NEW => 'Kham moi',
                self::VISIT_TYPE_FOLLOW_UP => 'Tai kham',
                self::VISIT_TYPE_EMERGENCY => 'Cap cuu',
                default => null,
            },
        ])));
    }

    public function getVisitTypeLabelAttribute(): ?string
    {
        return self::canonicalVisitType($this->attributes['visit_type'] ?? null)
            ?? ($this->attributes['visit_type'] ?? null);
    }

    public function setVisitTypeAttribute(?string $value): void
    {
        $this->attributes['visit_type'] = self::canonicalVisitType($value) ?? $value;
    }

    // ── Status Constants ──────────────────────────────────────────
    const STATUS_PENDING = 'pending';
    const STATUS_EXAMINING = 'examining';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PRESCRIBED = 'prescribed';
    const STATUS_FOLLOW_UP = 'follow_up';
    const STATUS_EMERGENCY = 'emergency';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationship với bệnh nhân (User)
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'user_id');
    }
    
    /**
     * Relationship với bác sĩ (User)
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }
    
    /**
     * Relationship với bệnh nhân (cách khác)
     */
    public function patientInfo()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

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

    // ── Accessors for Status ─────────────────────────────────────
    
    /**
     * Lấy label của status
     */
    public function getStatusLabelAttribute(): string
    {
        $statuses = [
            self::STATUS_PENDING => 'Chờ khám',
            self::STATUS_EXAMINING => 'Đang khám',
            self::STATUS_COMPLETED => 'Đã khám xong',
            self::STATUS_PRESCRIBED => 'Đã kê đơn',
            self::STATUS_FOLLOW_UP => 'Cần tái khám',
            self::STATUS_EMERGENCY => 'Cấp cứu',
            self::STATUS_CANCELLED => 'Đã hủy',
        ];
        
        return $statuses[$this->status] ?? 'Chưa xác định';
    }
    
    /**
     * Lấy icon cho status
     */
    public function getStatusIconAttribute(): string
    {
        $icons = [
            self::STATUS_PENDING => '⏳',
            self::STATUS_EXAMINING => '🔍',
            self::STATUS_COMPLETED => '✅',
            self::STATUS_PRESCRIBED => '💊',
            self::STATUS_FOLLOW_UP => '🔄',
            self::STATUS_EMERGENCY => '🚨',
            self::STATUS_CANCELLED => '❌',
        ];
        
        return $icons[$this->status] ?? '📋';
    }
    
    /**
     * Lấy màu chữ cho status
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING => '#856404',
            self::STATUS_EXAMINING => '#0c5460',
            self::STATUS_COMPLETED => '#155724',
            self::STATUS_PRESCRIBED => '#0c5460',
            self::STATUS_FOLLOW_UP => '#856404',
            self::STATUS_EMERGENCY => '#721c24',
            self::STATUS_CANCELLED => '#383d41',
        ];
        
        return $colors[$this->status] ?? '#6c757d';
    }
    
    /**
     * Lấy background color cho status
     */
    public function getStatusBgAttribute(): string
    {
        $bgColors = [
            self::STATUS_PENDING => '#fff3cd',
            self::STATUS_EXAMINING => '#d1ecf1',
            self::STATUS_COMPLETED => '#d4edda',
            self::STATUS_PRESCRIBED => '#d1ecf1',
            self::STATUS_FOLLOW_UP => '#fff3cd',
            self::STATUS_EMERGENCY => '#f8d7da',
            self::STATUS_CANCELLED => '#e2e3e5',
        ];
        
        return $bgColors[$this->status] ?? '#f8f9fa';
    }
    
    /**
     * Lấy badge HTML cho status
     */
    public function getStatusBadgeAttribute(): string
    {
        return sprintf(
            '<span style="background:%s; color:%s; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:500; display:inline-flex; align-items:center; gap:4px;">
                <span>%s</span> <span>%s</span>
            </span>',
            $this->status_bg,
            $this->status_color,
            $this->status_icon,
            $this->status_label
        );
    }

    // ── Scopes for filtering ─────────────────────────────────────
    
    /**
     * Scope lọc theo status
     */
    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope lọc theo loại khám
     */
    public function scopeOfVisitType($query, $visitType)
    {
        return $query->where('visit_type', $visitType);
    }
    
    /**
     * Scope tìm kiếm
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('record_code', 'like', "%{$search}%")
              ->orWhere('patient_name', 'like', "%{$search}%")
              ->orWhere('doctor_name', 'like', "%{$search}%");
        });
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
