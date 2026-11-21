<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Food extends Model
{
    use HasFactory;

    // 1. Khắc phục lỗi không tìm thấy bảng 'food'
    protected $table = 'foods';

    // 2. Đã sửa từ 'foods_id' thành 'food_id' cho đồng nhất với database và relationship
    protected $primaryKey = 'food_id';

    // Nếu khóa chính của bạn tăng tự động (Auto-incrementing INT) thì thêm dòng này cho chuẩn cast dữ liệu
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'food_name',
        'calories_per_100g',
        'description',
        'status',
    ];

    protected $casts = [
        'calories_per_100g' => 'integer',
        'status'            => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────

    /** Một món ăn có thể xuất hiện trong nhiều quy tắc dinh dưỡng */
    public function nutritionRules(): HasMany
    {
        // Khóa chính bên bảng foods hiện tại đã là food_id
        return $this->hasMany(DiseaseNutritionRule::class, 'food_id', 'food_id');
    }

    /** Một món ăn có thể xuất hiện trong nhiều bản ghi nhật ký ăn uống */
    public function mealLogs(): HasMany
    {
        return $this->hasMany(MealLog::class, 'food_id', 'food_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /** Chỉ lấy các món ăn đang active (status = 1) */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}