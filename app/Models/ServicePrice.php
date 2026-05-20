<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    use HasFactory;

    protected $table = 'serviceprices';
    protected $primaryKey = 'price_id';
    public $timestamps = false;  // ĐÚNG: không có updated_at

    const PRICE_TYPES = ['Thường', 'BHYT', 'VIP'];

    protected $fillable = [
        'service_id',
        'price_type',
        'price',
        'effective_date',
        'end_date',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'price' => 'float',
        'effective_date' => 'date',
        'end_date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    // ĐÚNG: ServiceService.php -> with('createdBy')
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}