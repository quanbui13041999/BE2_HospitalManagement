<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalNews extends Model
{
    protected $table = 'HospitalNews';
    protected $primaryKey = 'news_id';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'content',
        'category',
        'thumbnail',
        'author_id',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'created_at'   => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }
}
