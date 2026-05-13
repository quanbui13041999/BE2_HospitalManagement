<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InsuranceCard extends Model
{
    protected $table      = 'insurancecards';
    protected $primaryKey = 'insurance_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id','card_number','provider',
        'issued_date','expiry_date','discount_pct','status',
    ];

    protected $casts = [
        'issued_date'  => 'date',
        'expiry_date'  => 'date',
        'discount_pct' => 'decimal:2',
        'created_at'   => 'datetime',
    ];

    public function user()     { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function payments() { return $this->hasMany(Payment::class, 'insurance_id', 'insurance_id'); }

    public function isValid(): bool
    {
        return $this->status === 'Còn hạn'
            && (!$this->expiry_date || $this->expiry_date->isFuture());
    }
}


class MembershipCard extends Model
{
    protected $table      = 'membershipcards';
    protected $primaryKey = 'card_id';
    public $timestamps    = false;

    protected $fillable = [
        'user_id','card_number','tier','points',
        'discount_pct','issue_date','expiry_date','status',
    ];

    protected $casts = [
        'issue_date'   => 'date',
        'expiry_date'  => 'date',
        'discount_pct' => 'decimal:2',
        'points'       => 'integer',
        'status'       => 'boolean',
    ];

    const TIERS = ['Thường','Bạc','Vàng','Kim cương'];

    public function user()     { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
    public function payments() { return $this->hasMany(Payment::class, 'membership_id', 'card_id'); }
}


class PatientAllergy extends Model
{
    protected $table      = 'patientallergies';
    protected $primaryKey = 'allergy_id';
    public $timestamps    = false;

    protected $fillable = ['user_id','allergen','reaction','severity','noted_date','notes'];
    protected $casts    = ['noted_date' => 'date'];

    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
}


class PatientMedicalHistory extends Model
{
    protected $table      = 'patientmedicalhistory';
    protected $primaryKey = 'history_id';
    public $timestamps    = false;

    protected $fillable = [
        'user_id','condition','diagnosed_at',
        'treated_at','is_chronic','notes',
    ];

    protected $casts = [
        'diagnosed_at' => 'date',
        'is_chronic'   => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
}