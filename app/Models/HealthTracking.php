<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthTracking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id', 'systolic', 'diastolic', 'heart_rate',
        'spo2', 'weight', 'blood_sugar', 'symptoms',
        'risk_level', 'risk_warnings', 'version',
    ];

    protected $casts = [
        'weight'        => 'decimal:2',
        'risk_warnings' => 'array',
        'version'       => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // Accessor tiện dụng cho badge màu
    public function getRiskBadgeAttribute(): array
    {
        return match ($this->risk_level) {
            'danger'  => ['class' => 'danger',  'label' => 'Nguy hiểm'],
            'warning' => ['class' => 'warning', 'label' => 'Cảnh báo'],
            default   => ['class' => 'success', 'label' => 'Bình thường'],
        };
    }
}
