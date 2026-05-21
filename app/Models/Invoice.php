<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    public $timestamps = true;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'bhyt_card_id',
        'issue_date',
        'due_date',
        'subtotal',
        'bhyt_applied',
        'bhyt_coverage',
        'bhyt_amount',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'bhyt_applied' => 'boolean',
        'bhyt_coverage' => 'integer',
        'bhyt_amount' => 'float',
        'total_amount' => 'float',
        'status' => 'string',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    public function items()
    {
        // ĐÚNG: InvoiceItem có service relationship
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'invoice_id');
    }

    public function bhytDiscount()
    {
        // ĐÚNG theo PaymentRepository.php -> with('bhytDiscount')
        return $this->belongsTo(BhytCard::class, 'bhyt_card_id', 'bhyt_card_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'appointment_id', 'appointment_id');
    }
}