<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table      = 'payments';
    protected $primaryKey = 'payment_id';

    const CREATED_AT = 'payment_date';
    const UPDATED_AT = null;

    protected $fillable = [
        'appointment_id','insurance_id','membership_id',
        'subtotal','discount_amount','total_amount',
        'method','status','transaction_ref','payment_date','notes',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'payment_date'    => 'datetime',
    ];

    const METHODS   = ['Tiền mặt','Chuyển khoản','VNPay','Momo','BHYT'];
    const STATUSES  = ['Chưa thanh toán','Đã thanh toán','Hoàn tiền'];

    // ── Relations ──
    public function appointment()  { return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id'); }
    public function insurance()    { return $this->belongsTo(InsuranceCard::class, 'insurance_id', 'insurance_id'); }
    public function membership()   { return $this->belongsTo(MembershipCard::class, 'membership_id', 'card_id'); }
    public function items()        { return $this->hasMany(PaymentItem::class, 'payment_id', 'payment_id'); }

    // ── Scopes ──
    public function scopePaid($q)   { return $q->where('status', 'Đã thanh toán'); }
    public function scopeUnpaid($q) { return $q->where('status', 'Chưa thanh toán'); }
}


class PaymentItem extends Model
{
    protected $table      = 'paymentitems';
    protected $primaryKey = 'item_id';
    public $timestamps    = false;

    protected $fillable = [
        'payment_id','item_type','item_name',
        'quantity','unit_price','total_price',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity'    => 'integer',
    ];

    public function payment() { return $this->belongsTo(Payment::class, 'payment_id', 'payment_id'); }
}