<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RehabExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'phase',
        'thumbnail',
        'status',
        'view_count',
        'duration_minutes',
        'created_by',
    ];

    protected $casts = [
        'view_count'       => 'integer',
        'duration_minutes' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeByCategory(Builder $query, ?string $category): Builder
    {
        return $category ? $query->where('category', $category) : $query;
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Nhãn tiếng Việt của nhóm bệnh lý.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'co-xuong-khop'         => '🦴 Cơ – Xương – Khớp',
            'than-kinh-dot-quy'     => '🧠 Thần kinh – Đột quỵ',
            'chan-thuong-the-thao'   => '🏃 Chấn thương Thể thao',
            'ho-hap-tim-mach'       => '🫁 Hô hấp – Tim mạch',
            default                 => 'Khác',
        };
    }

    /**
     * Nhãn tiếng Việt của giai đoạn điều trị.
     */
    public function getPhaseLabelAttribute(): string
    {
        return match ($this->phase) {
            'cap-tinh' => 'Giai đoạn Cấp tính',
            'phuc-hoi' => 'Giai đoạn Phục hồi',
            'duy-tri'  => 'Duy trì',
            default    => 'Khác',
        };
    }

    /**
     * Nhãn trạng thái xuất bản.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'published' ? 'Công khai' : 'Lưu nháp';
    }

    /**
     * URL thumbnail hoặc ảnh placeholder nếu chưa có.
     */
    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : asset('images/exercise-placeholder.png');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Tăng lượt xem (dùng atomic increment để tránh race condition).
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
