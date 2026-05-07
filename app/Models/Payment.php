<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'payment_date' => 'datetime',
    ];

    // Relationship với Appointment
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    // Relationship với PaymentItems
    public function items()
    {
        return $this->hasMany(PaymentItem::class, 'payment_id', 'payment_id');
    }

    // Relationship với Insurance (BHYT)
    public function insurance()
    {
        return $this->belongsTo(InsuranceCard::class, 'insurance_id', 'insurance_id');
    }

    // Relationship với Membership (Thẻ thành viên)
    public function membership()
    {
        return $this->belongsTo(MembershipCard::class, 'membership_id', 'card_id');
    }
}