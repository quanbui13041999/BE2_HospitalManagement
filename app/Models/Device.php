<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    public const STATUSES = [
        'active' => 'Hoạt động',
        'broken' => 'Hỏng',
        'maintenance' => 'Bảo trì',
    ];

    protected $fillable = [
        'name',
        'code',
        'device_type_id',
        'status',
        'purchase_date',
        'lock_version',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'lock_version' => 'integer',
    ];

    public function type()
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }
}
