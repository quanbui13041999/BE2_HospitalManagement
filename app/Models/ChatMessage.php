<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chatmessages';
    protected $primaryKey = 'message_id';
    public $timestamps = false;

    protected $fillable = ['room_id', 'sender_id', 'message_text', 'is_read', 'sent_at', 'is_ai'];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
        'is_ai'   => 'boolean',
    ];

    // Quan hệ: tin nhắn thuộc phòng chat
    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'room_id', 'room_id');
    }

    // Quan hệ: người gửi
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
}
