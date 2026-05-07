<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activitylogs';
    protected $primaryKey = 'activity_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ── Relationships ──
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // ── Query Scopes ──
    /**
     * Lọc log của một user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Lọc log theo action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', 'like', "%{$action}%");
    }

    /**
     * Lọc log trong khoảng thời gian
     */
    public function scopeInDateRange($query, string $startDate, string $endDate)
    {
        return $query
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);
    }

    /**
     * Sắp xếp mới nhất
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Sắp xếp cũ nhất
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}
