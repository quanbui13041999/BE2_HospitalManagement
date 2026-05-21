<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalNews extends Model
{
    protected $table = 'hospitalnews';
    protected $primaryKey = 'news_id';
    public $timestamps = false; // chỉ có created_at thủ công

    protected $fillable = [
        'title', 'content', 'category', 'thumbnail',
        'author_id', 'is_published', 'email_sent', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at'   => 'datetime',
        'is_published' => 'boolean',
        'email_sent'   => 'boolean',
    ];

    // Scope: chỉ lấy bài đã đăng
    public function scopePublished($query)
    {
        return $query->where('is_published', 1)->whereNotNull('published_at');
    }

    // Scope: lọc theo category
    public function scopeOfCategory($query, $category)
    {
        return $category ? $query->where('category', $category) : $query;
    }

    // Lấy mô tả ngắn
    public function getExcerptAttribute(): string
    {
        return mb_substr(strip_tags($this->content), 0, 150) . '...';
    }

    // URL thumbnail với fallback
    public function getThumbnailUrlAttribute(): string
    {
        if (! $this->thumbnail) {
            return asset('images/news-default.jpg');
        }

        if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
            return $this->thumbnail;
        }

        if (str_starts_with($this->thumbnail, 'uploads/news/')) {
            return asset($this->thumbnail);
        }

        return asset('storage/' . $this->thumbnail);
    }

    public function author()
    {
        // Reference user_id as it is the PK in the users table
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }
}
