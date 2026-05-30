<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealLog extends Model
{
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'user_id',
        'food_id',
        'meal_type',
        'weight_gram',
        'total_calories_intake',
        'logged_date',
    ];

    protected $casts = [
        'logged_date'           => 'date',
        'weight_gram'           => 'integer',
        'total_calories_intake' => 'integer',
    ];

    /**
     * Label tiếng Việt cho từng buổi ăn.
     */
    public const MEAL_LABELS = [
        'breakfast' => 'Bữa sáng',
        'lunch'     => 'Bữa trưa',
        'dinner'    => 'Bữa tối',
        'snack'     => 'Bữa phụ',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'user_id');
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id', 'food_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /** Lấy nhật ký của ngày hôm nay */
    public function scopeToday($query)
    {
        return $query->whereDate('logged_date', today());
    }

    /** Lấy nhật ký theo ngày cụ thể */
    public function scopeOnDate($query, string $date)
    {
        return $query->whereDate('logged_date', $date);
    }

    // ─── Accessors ───────────────────────────────────────────────

    /** Label tiếng Việt cho buổi ăn */
    public function getMealLabelAttribute(): string
    {
        return self::MEAL_LABELS[$this->meal_type] ?? $this->meal_type;
    }
}
