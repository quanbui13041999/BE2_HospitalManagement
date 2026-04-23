<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    protected $table = 'ServicePrices';
    protected $primaryKey = 'price_id';
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'price_type',
        'price',
        'effective_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date'       => 'date',
        'price'          => 'decimal:2',
        'created_at'     => 'datetime',
    ];

    // Danh sách loại giá hợp lệ
    const PRICE_TYPES = ['Thường', 'BHYT', 'VIP', 'Theo yêu cầu'];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    // Scope: còn hiệu lực
    public function scopeActive($query)
    {
        return $query->where('effective_date', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            });
    }
}
