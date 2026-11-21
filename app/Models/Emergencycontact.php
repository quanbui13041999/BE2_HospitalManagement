<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'priority',
        'name',
        'relationship',
        'phone',
        'email',
        'lab_notifications',
        'recovery_updates',
    ];

    protected $casts = [
        'lab_notifications' => 'boolean',
        'recovery_updates'  => 'boolean',
        'priority'          => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    /** Sắp xếp theo thứ tự ưu tiên */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('priority');
    }

    /** Lọc những liên hệ nhận thông báo xét nghiệm */
    public function scopeLabNotifiable(Builder $query): Builder
    {
        return $query->where('lab_notifications', true);
    }

    /** Lọc những liên hệ nhận cập nhật hồi phục */
    public function scopeRecoveryNotifiable(Builder $query): Builder
    {
        return $query->where('recovery_updates', true);
    }

    // ── Helpers ────────────────────────────────────────────────────

    /** Lấy chữ viết tắt từ họ tên (VD: Nguyễn Văn A → NA) */
    public function getInitialsAttribute(): string
    {
        if (empty($this->name)) {
            return '?';
        }

        $words = explode(' ', trim($this->name));

        return collect($words)
            ->map(fn ($word) => mb_substr($word, 0, 1))
            ->take(2)
            ->join('');
    }

    /** Kiểm tra contact có đủ thông tin cơ bản không */
    public function getIsCompleteAttribute(): bool
    {
        return filled($this->name) && filled($this->phone) && filled($this->relationship);
    }
}