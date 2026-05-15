<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipCard extends Model
{
    protected $table = 'membershipcards';
    // SỬA LẠI: Trong ảnh DB của bạn khóa chính là 'id' chứ không phải 'card_id'
    protected $primaryKey = 'id';

    // SỬA LẠI: Bật lại true nếu bạn muốn dùng created_at và updated_at như trong ảnh DB
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'card_number',
        'tier',
        'total_spent',
        'points',
        'expiry_date'
    ];
    protected static function booted()
    {
        // Trước khi lưu (cả tạo mới hoặc cập nhật)
        static::saving(function ($membershipCard) {
            // Tự động tính hạng dựa trên số điểm hiện tại và gán vào cột 'tier' để lưu vào DB
            $total_spent = $membershipCard->total_spent ?? 0;

            if ($total_spent >= 25000000) {
                $membershipCard->tier = 'Kim Cương';
            } elseif ($total_spent >= 10000000) {
                $membershipCard->tier = 'Vàng';
            } elseif ($total_spent >= 5000000) {
                $membershipCard->tier = 'Bạc';
            } else {
                $membershipCard->tier = 'Đồng';
            }
        });
    }
    // 👉 Accessor: Tự động tính hạng dựa trên điểm (Không dùng giá trị cột 'tier' trong DB)
    public function getTierAttribute()
    {
        $total_spent = $this->total_spent ?? 0;
        if ($total_spent >= 25000000) return 'Kim Cương'; // 25 triệu
        if ($total_spent >= 10000000) return 'Vàng';      // 10 triệu
        if ($total_spent >= 5000000)  return 'Bạc';       // 5 triệu
        return 'Đồng';
    }

    // 👉 Tiến trình % dựa trên các mốc
   public function getProgressPercentAttribute()
{
    $total_spent = $this->attributes['total_spent'] ?? 0;
    if ($total_spent >= 25000000) return 100;
    if ($total_spent >= 10000000) return 75;
    if ($total_spent >= 5000000)  return 50;
    if ($total_spent > 0)         return 25;
    return 0;
}

    // 👉 Số điểm còn thiếu để lên hạng tối đa (Kim cương)
    public function getRemainingAttribute()
    {
        return max(0, 25000000 - ($this->points ?? 0));
    }

   // THÊM VÀO: Tự động quy đổi total_spent → points (1.000đ = 1 điểm)
public function getPointsAttribute()
{
    return (int) (($this->attributes['total_spent'] ?? 0) / 1000);
}
}
