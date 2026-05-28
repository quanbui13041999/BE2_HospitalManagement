<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NutritionArticle extends Model
{
    protected $primaryKey = 'article_id';

    protected $fillable = [
        'doctor_id',
        'title',
        'slug',
        'content',
        'target_disease',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────

    /** Bài viết thuộc về 1 bác sĩ (nullable - admin cũng viết được) */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Doctor::class, 'doctor_id', 'doctor_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /** Chỉ lấy bài đã xuất bản */
    public function scopePublished($query)
    {
        return $query->where('status', 1);
    }

    /** Lọc bài viết theo tên bệnh */
    public function scopeForDisease($query, string $diseaseName)
    {
        return $query->where('target_disease', 'LIKE', "%{$diseaseName}%");
    }

    // ─── Hooks ───────────────────────────────────────────────────

    protected static function booted(): void
    {
        /** Tự động tạo slug từ title nếu không truyền vào */
        static::creating(function (NutritionArticle $article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 1 ? 'Xuất bản' : 'Nháp';
    }

    /** Tóm tắt nội dung (strip HTML, lấy 150 ký tự đầu) */
    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 150);
    }
}
