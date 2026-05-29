<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activitylogs';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'actor_name',
        'actor_email',
        'role_name',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'status',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relationships ──
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getActorDisplayAttribute(): string
    {
        return $this->actor_name ?: ($this->user?->full_name ?: 'Người dùng đã xóa');
    }

    public function getActorEmailDisplayAttribute(): ?string
    {
        return $this->actor_email ?: $this->user?->email;
    }

    public function getRoleDisplayAttribute(): string
    {
        return $this->role_name ?: ($this->user?->role?->role_name ?: 'Không rõ');
    }

    public function getSubjectTypeDisplayAttribute(): ?string
    {
        return $this->subject_type ?: $this->inferSubject()['type'];
    }

    public function getSubjectIdDisplayAttribute(): ?int
    {
        return $this->subject_id ?: $this->inferSubject()['id'];
    }

    public function getSubjectDisplayAttribute(): string
    {
        $labels = [
            'appointment' => 'Lịch khám',
            'patient' => 'Bệnh nhân',
            'doctor' => 'Bác sĩ',
            'medical_record' => 'Hồ sơ bệnh án',
            'payment' => 'Thanh toán',
            'service' => 'Dịch vụ',
            'medicine' => 'Thuốc',
            'room' => 'Phòng khám',
            'user' => 'Người dùng',
            'queue' => 'Hàng đợi',
            'review' => 'Đánh giá',
        ];

        $type = $this->subject_type_display;
        $id = $this->subject_id_display;

        if (!$type) {
            return '-';
        }

        return ($labels[$type] ?? $type) . ($id ? ' #' . $id : '');
    }

    public function getDescriptionDisplayAttribute(): string
    {
        return $this->description ?: ($this->action ?: 'Không có nội dung.');
    }

    public function getUserAgentDisplayAttribute(): string
    {
        return $this->user_agent ?: 'Không ghi nhận (log cũ).';
    }

    private function inferSubject(): array
    {
        $action = (string) $this->action;

        $patterns = [
            'appointment' => [
                '/(?:Đặt|Hủy|Dời|Xác nhận|Thanh toán).*lịch (?:hẹn|khám)\s*#?(\d+)/iu',
                '/lịch (?:hẹn|khám)\s*#?(\d+)/iu',
            ],
            'medical_record' => [
                '/hồ sơ bệnh án\s*#?(\d+)/iu',
            ],
            'payment' => [
                '/(?:thanh toán|giao dịch)\s*#?(\d+)/iu',
            ],
            'review' => [
                '/đánh giá\s*#?(\d+)/iu',
            ],
            'doctor' => [
                '/bác sĩ\s*#?(\d+)/iu',
            ],
            'service' => [
                '/dịch vụ\s*#?(\d+)/iu',
            ],
            'room' => [
                '/phòng(?: khám)?\s*#?(\d+)/iu',
            ],
            'user' => [
                '/người dùng\s*#?(\d+)/iu',
            ],
        ];

        foreach ($patterns as $type => $typePatterns) {
            foreach ($typePatterns as $pattern) {
                if (preg_match($pattern, $action, $matches)) {
                    return ['type' => $type, 'id' => isset($matches[1]) ? (int) $matches[1] : null];
                }
            }
        }

        return ['type' => null, 'id' => null];
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
