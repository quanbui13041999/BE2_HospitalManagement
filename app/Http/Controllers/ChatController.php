<?php
namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\GeminiChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected GeminiChatService $gemini;

    public function __construct(GeminiChatService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Lấy hoặc tạo phòng chat cho user đang đăng nhập
     * POST /chat/room
     */
    public function getOrCreateRoom(): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();

        // Tìm phòng chat đang mở của user
        $room = ChatRoom::where('user_id', $userId)
            ->where('status', 'Mở')
            ->first();

        // Nếu chưa có thì tạo mới
        if (!$room) {
            $room = ChatRoom::create([
                'user_id'    => $userId,
                'doctor_id'  => null,
                'status'     => 'Mở',
                'created_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'room_id' => $room->room_id,
        ]);
    }

    /**
     * Lấy tin nhắn trong phòng (dùng cho polling)
     * GET /chat/messages/{roomId}?after_id=xxx
     */
    public function getMessages(Request $request, int $roomId): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();

        // Kiểm tra quyền truy cập phòng
        $room = ChatRoom::where('room_id', $roomId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $query = ChatMessage::where('room_id', $roomId)
            ->with('sender:user_id,full_name,avatar_url,role_id');

        // Lấy tin nhắn sau một ID nhất định (cho polling incremental)
        if ($request->filled('after_id')) {
            $query->where('message_id', '>', $request->integer('after_id'));
        } else {
            $query->latest('sent_at')->take(50);
        }

        $messages = $query->orderBy('sent_at', 'asc')->get()->map(fn($msg) => [
            'message_id'   => $msg->message_id,
            'sender_id'    => $msg->sender_id,
            'sender_name'  => $msg->is_ai ? 'AI Trợ Lý' : ($msg->sender->full_name ?? 'Ẩn danh'),
            'avatar'       => $msg->is_ai ? null : ($msg->sender->avatar_url ?? null),
            'message_text' => $msg->message_text,
            'is_read'      => $msg->is_read,
            'is_ai'        => $msg->is_ai,
            'sent_at'      => $msg->sent_at->format('H:i d/m/Y'),
            'is_mine'      => $msg->sender_id == $userId,
        ]);

        // Đánh dấu đã đọc các tin nhắn của staff/AI gửi đến user
        ChatMessage::where('room_id', $roomId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    /**
     * Gửi tin nhắn từ phía patient
     * POST /chat/send
     */
    public function sendMessage(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'room_id'      => 'required|integer|exists:chatrooms,room_id',
            'message_text' => 'required|string|max:2000',
        ]);

        $userId = Auth::id();
        $roomId = $request->integer('room_id');

        // Kiểm tra quyền sở hữu phòng
        $room = ChatRoom::where('room_id', $roomId)
            ->where('user_id', $userId)
            ->where('status', 'Mở')
            ->firstOrFail();

        // Lưu tin nhắn của patient
        $message = ChatMessage::create([
            'room_id'      => $roomId,
            'sender_id'    => $userId,
            'message_text' => trim($request->message_text),
            'is_read'      => 0,
            'sent_at'      => now(),
            'is_ai'        => 0,
        ]);

        // --- Logic AI: nếu không có staff đang xử lý phòng này ---
        $hasStaff = $room->doctor_id !== null;

        // Kiểm tra tin nhắn cuối cùng của staff trong vòng 5 phút
        $recentStaffReply = ChatMessage::where('room_id', $roomId)
            ->where('sender_id', '!=', $userId)
            ->where('is_ai', 0)
            ->where('sent_at', '>=', now()->subMinutes(5))
            ->exists();

        if (!$hasStaff || !$recentStaffReply) {
            try {
                // Gọi Gemini AI trả lời
                $aiReply = $this->gemini->generateReply($roomId, trim($request->message_text));

                // Lưu tin nhắn AI với sender_id = 0 (system AI user)
                ChatMessage::create([
                    'room_id'      => $roomId,
                    'sender_id'    => 0, // System AI user
                    'message_text' => $aiReply,
                    'is_read'      => 0,
                    'sent_at'      => now()->addSecond(),
                    'is_ai'        => 1,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error generating AI reply: ' . $e->getMessage());
                // Nếu AI gặp lỗi, không tạo message - chỉ log lỗi
                // Staff sẽ thấy tin nhắn từ user và có thể trả lời
            }
        }

        return response()->json(['success' => true, 'message_id' => $message->message_id]);
    }
}
