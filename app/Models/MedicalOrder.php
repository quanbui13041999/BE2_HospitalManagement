<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalOrder extends Model
{
    protected $primaryKey = 'order_id';
    protected $table = 'medical_orders';
    
    protected $fillable = [
        'record_id',
        'order_type',
        'order_name',
        'description',
        'status',
        'result',
        'result_date'
    ];
    
    // 👉 THÊM PHẦN NÀY - Khai báo các accessor sẽ được tự động thêm vào JSON/Array
    protected $appends = ['has_result', 'status_text', 'status_badge'];
    
    /**
     * Accessor: Kiểm tra xem đã có kết quả hay chưa
     * Sử dụng: $order->has_result
     */
    public function getHasResultAttribute(): bool
    {
        // Cách 1: Kiểm tra theo status
        if (in_array($this->status, ['completed', 'has_result', 'done'])) {
            return true;
        }
        
        // Cách 2: Kiểm tra field result có dữ liệu không
        if (!empty($this->result)) {
            return true;
        }
        
        // Cách 3: Kiểm tra result_date có tồn tại không
        if (!empty($this->result_date)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Accessor: Lấy text trạng thái
     * Sử dụng: $order->status_text
     */
    public function getStatusTextAttribute(): string
    {
        if ($this->has_result) {
            return '✅ Trả kết quả';
        }
        
        // Có thể thêm nhiều trạng thái khác
        if ($this->status === 'pending') {
            return '⏳ Chờ kết quả';
        }
        
        if ($this->status === 'processing') {
            return '🔄 Đang xử lý';
        }
        
        return '📋 Chưa có kết quả';
    }
    
    /**
     * Accessor: Lấy class cho badge (dùng cho CSS)
     * Sử dụng: $order->status_badge
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->has_result) {
            return 'badge-success';
        }
        
        if ($this->status === 'processing') {
            return 'badge-warning';
        }
        
        return 'badge-secondary';
    }
    
    // Nếu bạn muốn thêm method như cũ để tương thích
    public function hasResult(): bool
    {
        return $this->has_result;
    }
}