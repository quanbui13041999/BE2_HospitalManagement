<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'InvoiceItems';
    protected $primaryKey = 'item_id';
    public $timestamps = true;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'service_name',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'total_price' => 'float',
    ];

    // ĐÚNG: BhytRepository.php -> with('items.service')
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }
}