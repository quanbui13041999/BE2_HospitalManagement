<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'Services';
    protected $primaryKey = 'service_id';
    public $timestamps = false;

    protected $fillable = [
        'service_code',
        'service_name',
        'department_id',
        'description',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function prices()
    {
        return $this->hasMany(ServicePrice::class, 'service_id', 'service_id');
    }

    public function activePrices()
    {
        return $this->hasMany(ServicePrice::class, 'service_id', 'service_id')
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })
            ->where('effective_date', '<=', now()->toDateString());
    }
}
