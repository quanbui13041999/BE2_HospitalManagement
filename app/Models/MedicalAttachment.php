<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalAttachment extends Model
{
    protected $primaryKey = 'attachment_id';
    protected $table = 'medical_attachments';
    
    protected $fillable = [
        'record_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'attachment_category'
    ];
    
    // Thêm method này để kiểm tra file PDF
    public function isPdf()
    {
        // Cách 1: Kiểm tra dựa trên file_type (nếu bạn đã lưu MIME type)
        if ($this->file_type) {
            return $this->file_type === 'application/pdf';
        }
        
        // Cách 2: Kiểm tra dựa trên extension của file_name
        $extension = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        return $extension === 'pdf';
    }
    
    // Optional: Thêm accessor cho file_size_formatted (nếu chưa có)
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}