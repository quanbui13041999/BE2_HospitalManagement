<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipCard extends Model
{
    // Cấu hình tên bảng trong Database ứng với Model này
    protected $table      = 'membershipcards';
    
    // Cấu hình khóa chính của bảng là cột 'id'
    protected $primaryKey = 'card_id';
    
    // Cho phép tự động cập nhật 2 cột mốc thời gian: created_at (ngày tạo) và updated_at (ngày sửa)
    public    $timestamps = false;

    // Định nghĩa các cột được phép thêm/sửa dữ liệu hàng loạt thông qua Mass Assignment (như Create, Update)
    protected $fillable = [
        'user_id', 'card_number', 'tier',
        'total_spent', 'points', 'discount_pct',
        'issue_date', 'expiry_date', 'status',
    ];

    // Ép kiểu dữ liệu (Casts): Đảm bảo khi lấy dữ liệu lên từ DB, các biến luôn đúng kiểu số mong muốn
    protected $casts = [
        'total_spent' => 'float',   // Ép kiểu tổng chi tiêu về số thực (decimal trong DB -> float)
        'points'      => 'integer', // Ép kiểu điểm số tích lũy về số nguyên (int)
        'discount_pct' => 'decimal:2',
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'status'      => 'boolean',
    ];

    /**
     * Hàm booted(): Đăng ký các sự kiện (Model Events) tự động của Eloquent
     */
    protected static function booted(): void
    {
        // Sự kiện static::saving xảy ra NGAY TRƯỚC KHI bản ghi được tạo mới (create) hoặc cập nhật (update) vào DB
        static::saving(function ($card) {
            // Lấy số tiền tổng chi tiêu hiện tại và ép về kiểu float
            $spent = (float) $card->total_spent;

            // TỰ ĐỘNG XẾP HẠNG THẺ (TIER) DỰA TRÊN TỔNG CHI TIÊU
            // Sử dụng match(true) quét từ trên xuống, thỏa mốc nào sẽ gán chữ tương ứng vào cột 'tier'
            $card->tier = match (true) {
                $spent >= 25_000_000 => 'Kim Cương', // Chi tiêu từ 25 triệu trở lên
                $spent >= 10_000_000 => 'Vàng',      // Chi tiêu từ 10 triệu đến dưới 25 triệu
                $spent >= 5_000_000  => 'Bạc',       // Chi tiêu từ 5 triệu đến dưới 10 triệu
                default              => 'Đồng',      // Dưới 5 triệu mặc định là hạng Đồng
            };

            // TỰ ĐỘNG TÍNH LẠI ĐIỂM SỐ (POINTS): Quy đổi 1,000 đ = 1 điểm
            // Chỉ tính lại khi: Cột tổng chi tiêu bị thay đổi (isDirty) HOẶC khi thẻ mới tạo đang có điểm bằng 0
            if ($card->isDirty('total_spent') || (int) $card->getRawOriginal('points') === 0) {
                // floor() dùng để làm tròn xuống số nguyên (bỏ phần tiền lẻ dưới 1,000đ)
                $card->points = (int) floor($spent / 1000);
            }
        });
    }

    /**
     * Accessor: Tự động tính phần trăm (%) tiến trình của thanh Progress Bar ngoài UI
     * Cách gọi ngoài Blade: {{ $membership->progress_percent }}
     */
    public function getProgressPercentAttribute(): int
    {
        // getRawOriginal giúp lấy giá trị số thô trong DB để tính, tránh bị lặp vòng lặp vô hạn
        $spent = (float) $this->getRawOriginal('total_spent');

        // Phân bổ thanh tiến trình thành 3 khúc đều nhau (Mỗi khúc chiếm khoảng 33%): Đồng -> Bạc -> Vàng -> K.Cương
        return match (true) {
            $spent >= 25_000_000 => 100, // Đạt Kim Cương thì thanh tiến trình đầy 100%
            
            // Khúc từ Vàng lên Kim Cương (Chiếm từ mốc 66% đến 100% trên thanh UI)
            $spent >= 10_000_000 => (int) (($spent - 10_000_000) / 15_000_000 * 34) + 66,
            
            // Khúc từ Bạc lên Vàng (Chiếm từ mốc 33% đến 66% trên thanh UI)
            $spent >= 5_000_000  => (int) (($spent - 5_000_000)  / 5_000_000  * 33) + 33,
            
            // Khúc từ Đồng lên Bạc (Chiếm từ mốc 0% đến 33% trên thanh UI)
            default              => (int) ($spent / 5_000_000 * 33),
        };
    }

    /**
     * Accessor: Tính số tiền còn thiếu để đạt được hạng thẻ KẾ TIẾP
     * Cách gọi ngoài Blade: {{ $membership->remaining_to_next_tier }}
     */
    public function getRemainingToNextTierAttribute(): int
    {
        $spent = (float) $this->getRawOriginal('total_spent');

        return match (true) {
            $spent >= 25_000_000 => 0,                      // Đạt cấp tối đa, không cần bù thêm tiền
            $spent >= 10_000_000 => 25_000_000 - (int) $spent, // Đang ở Vàng -> Cần thêm tiền để chạm mốc Kim Cương (25tr)
            $spent >= 5_000_000  => 10_000_000 - (int) $spent, // Đang ở Bạc -> Cần thêm tiền để chạm mốc Vàng (10tr)
            default              =>  5_000_000 - (int) $spent, // Đang ở Đồng -> Cần thêm tiền để chạm mốc Bạc (5tr)
        };
    }

    /**
     * Accessor: Xác định tên của Hạng Thẻ Tiếp Theo để hiển thị làm mục tiêu phấn đấu trên UI
     * Cách gọi ngoài Blade: {{ $membership->next_tier }}
     */
    public function getNextTierAttribute(): string
    {
        $spent = (float) $this->getRawOriginal('total_spent');

        return match (true) {
            $spent >= 25_000_000 => 'Kim Cương', // Kịch trần, giữ nguyên tên Kim Cương
            $spent >= 10_000_000 => 'Kim Cương', // Đang ở Vàng thì mục tiêu tiếp theo là Kim Cương
            $spent >= 5_000_000  => 'Vàng',      // Đang ở Bạc thì mục tiêu tiếp theo là Vàng
            default              => 'Bạc',       // Đang ở Đồng thì mục tiêu tiếp theo là Bạc
        };
    }
}
