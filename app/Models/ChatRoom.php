<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $table = 'chatrooms';
    protected $primaryKey = 'room_id';
    public $timestamps = false;

    protected $fillable = ['user_id', 'doctor_id', 'status', 'created_at', 'closed_at'];

    // Quan hệ: phòng chat thuộc về bệnh nhân
    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Quan hệ: phòng chat có thể được assign cho CSKH/bác sĩ
    public function staff()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    // Quan hệ: danh sách tin nhắn trong phòng
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'room_id', 'room_id')->orderBy('sent_at', 'asc');
    }

    // Lấy tin nhắn mới nhất
    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'room_id', 'room_id')->latestOfMany('sent_at');
    }

    // Đếm tin nhắn chưa đọc
    public function unreadCount()
    {
        return $this->messages()->where('is_read', 0)->where('sender_id', $this->user_id)->count();
    }
}
