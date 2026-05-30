<?php
// app/Models/Review.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    
    public $timestamps = false;
    
    protected $fillable = [
        'appointment_id', 'user_id', 'doctor_id',
        'rating', 'comment', 'doctor_reply',
        'created_at', 'updated_at', 'doctor_reply_updated_at'
    ];
    
    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'doctor_reply_updated_at' => 'datetime',
    ];
    
    // Relationships
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }
    
    // Kiểm tra có thể chỉnh sửa không (trong 24 giờ)
    public function canEdit($userId)
    {
        if (!$this->created_at) return false;
        
        $hoursSinceCreated = $this->created_at->diffInHours(now());
        return $this->user_id === $userId && $hoursSinceCreated <= 24;
    }
    
    // Kiểm tra có thể xóa không
    public function canDelete($userId, $isAdmin = false)
    {
        return $isAdmin || $this->user_id === $userId;
    }
    
    // Kiểm tra có thể trả lời không (chỉ bác sĩ liên quan hoặc admin)
    public function canReply($userId, $isAdmin = false)
    {
        return $isAdmin || $this->doctor_id === $userId;
    }
    
    // Scope lấy reviews theo doctor
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }
    
    // Scope lấy reviews theo user
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    // Accessor: lấy rating dạng sao
    public function getStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}