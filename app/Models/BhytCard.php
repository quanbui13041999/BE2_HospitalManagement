<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model ánh xạ tới bảng insurancecards trong database.
 * Dùng chung với InsuranceCard - chỉ cần 1 trong 2.
 */
class BhytCard extends Model
{
    protected $table      = 'insurancecards';   // ← trỏ đúng bảng thực tế
    protected $primaryKey = 'insurance_id';
    public    $timestamps = false;

    protected $fillable = [
        'user_id',
        'card_number',
        'provider',
        'issued_date',
        'expiry_date',
        'discount_pct',
        'status',
    ];

    protected $casts = [
        'issued_date'  => 'date',
        'expiry_date'  => 'date',
        'discount_pct' => 'float',
    ];

    /**
     * Relationship đúng: insurancecards.user_id → users.user_id
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}