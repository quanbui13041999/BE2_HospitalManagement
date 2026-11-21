<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseNutritionRule extends Model
{
    protected $primaryKey = 'rule_id';

    protected $fillable = [
        'disease_name',
        'icd_code',
        'food_id',
        'recommendation_type',
        'reason',
    ];

    // ─── Relationships ───────────────────────────────────────────

    /** Mỗi quy tắc thuộc về 1 món ăn */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id', 'food_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /** Lọc các thực phẩm nên ăn */
    public function scopeShouldEat($query)
    {
        return $query->where('recommendation_type', 'should_eat');
    }

    /** Lọc các thực phẩm nên tránh */
    public function scopeShouldAvoid($query)
    {
        return $query->where('recommendation_type', 'should_avoid');
    }

    /**
     * Lọc quy tắc theo tên bệnh hoặc mã ICD.
     * Dùng để map với bảng diagnoses.
     */
    public function scopeForDisease($query, string $diseaseName, ?string $icdCode = null)
    {
        return $query->where(function ($q) use ($diseaseName, $icdCode) {
            $q->where('disease_name', 'LIKE', "%{$diseaseName}%");
            if ($icdCode) {
                $q->orWhere('icd_code', $icdCode);
            }
        });
    }
}
