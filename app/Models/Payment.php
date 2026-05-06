<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $timestamps = false;  // ĐÚNG: không có updated_at

    protected $fillable = [
        'invoice_id',
        'payment_method',
        'amount',
        'status',
        'paid_at',
        'transaction_ref',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function invoice()
    {
        // ĐÚNG: PaymentRepository.php -> with('invoice.patient')
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    // ĐÚNG theo PaymentService.php -> $payment->invoice?->update
}