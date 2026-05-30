<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
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
        'duration_minutes' => 'integer',
        'status' => 'boolean',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    // ĐÚNG: ServiceRepository.php -> with('activePrices')
    public function activePrices()
    {
        return $this->hasMany(ServicePrice::class, 'service_id', 'service_id')
            ->where('effective_date', '<=', now()->toDateString())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            });
    }

    public function latestPrice()
    {
        return $this->hasOne(ServicePrice::class, 'service_id', 'service_id')
            ->where('effective_date', '<=', now()->toDateString())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->orderBy('effective_date', 'desc');
    }

    public function prices()
    {
        return $this->hasMany(ServicePrice::class, 'service_id', 'service_id');
    }
}