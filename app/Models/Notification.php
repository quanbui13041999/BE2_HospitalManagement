<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'notif_type',
        'title',
        'content',
        'ref_id',
        'ref_type',
        'is_read',
        'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    // ── Relationships ──
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // ── Query Scopes ──
    /**
     * Lọc thông báo chưa đọc
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Lọc thông báo đã đọc
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Lọc thông báo của một user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Lọc theo loại thông báo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('notif_type', $type);
    }

    /**
     * Sắp xếp mới nhất
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ── Helpers ──
    /**
     * Đánh dấu là đã đọc
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    /**
     * Đánh dấu là chưa đọc
     */
    public function markAsUnread(): bool
    {
        return $this->update(['is_read' => false]);
    }
}
