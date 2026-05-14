<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipCard extends Model
{
    protected $table = 'membershipcards'; 
    protected $primaryKey = 'card_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'card_number', 'tier', 'points'
    ];

    // 👉 Accessor: tier tự động
    public function getTierAttribute()
    {
        $points = $this->points ?? 0;

        if ($points >= 10000000) return 'Vàng';
        if ($points >= 5000000) return 'Bạc';
        return 'Đồng';
    }

    // 👉 Progress %
    public function getProgressPercentAttribute()
    {
        $points = $this->points ?? 0;

        if ($points >= 10000000) return 75;
        if ($points >= 5000000) return 50;
        return 25;
    }

    // 👉 Số tiền còn thiếu
    public function getRemainingAttribute()
    {
        return max(0, 25000000 - ($this->points ?? 0));
    }

    // 👉 Tổng chi tiêu (format sẵn luôn)
    public function getTotalSpentAttribute()
    {
        return number_format(($this->points ?? 0) / 1000000, 1);
    }
}