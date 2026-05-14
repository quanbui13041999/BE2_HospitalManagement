<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BhytCard extends Model
{
    use HasFactory;

    protected $table = 'insurancecards';
    protected $primaryKey = 'insurance_id';
    public $timestamps = true;

    protected $fillable = [
        'patient_id',
        'card_number',
        'issue_date',
        'expiry_date',
        'coverage_rate',
        'status',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'discount_pct' => 'integer',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Accessors for compatibility with existing code
    public function getPatientIdAttribute()
    {
        return $this->user_id;
    }

    public function getCoverageRateAttribute()
    {
        return $this->discount_pct;
    }

    public function getIssueDateAttribute()
    {
        return $this->issued_date;
    }
}