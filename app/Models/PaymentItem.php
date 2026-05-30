<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    use HasFactory;

    protected $table = 'payment_items';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'total_price' => 'float',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function getTotalPriceAttribute($value)
    {
        return $value ?? $this->subtotal;
    }
}
