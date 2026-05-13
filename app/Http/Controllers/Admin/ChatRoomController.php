<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatRoomController extends Controller
{
    public function __construct()
    {
        // Middleware đã được khai báo tại route web.php
    }

    /**
     * Trang chatroom quản lý – danh sách tất cả phòng chat
     * GET /admin/chatroom
     */
    public function index(): \Illuminate\View\View
    {
        $rooms = ChatRoom::with([
                'patient:user_id,full_name,email,avatar_url',
                'staff:user_id,full_name',
                'latestMessage',
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.chatroom.index', compact('rooms'));
    }

    /**
     * Lấy danh sách phòng dạng JSON (dùng cho polling sidebar admin)
     * GET /admin/chatroom/list
     */
    public function listJson(): \Illuminate\Http\JsonResponse
    {
        $rooms = ChatRoom::with([
                'patient:user_id,full_name,avatar_url',
                'latestMessage',
            ])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('is_read', 0)->whereColumn('sender_id', 'chatrooms.user_id');
            }])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($room) => [
                'room_id'         => $room->room_id,
                'patient_name'    => $room->patient->full_name ?? 'Ẩn danh',
                'patient_avatar'  => $room->patient->avatar_url,
                'status'          => $room->status,
                'unread_count'    => $room->unread_count,
                'last_message'    => $room->latestMessage?->message_text,
                'last_time'       => $room->latestMessage?->sent_at
                                        ? \Carbon\Carbon::parse($room->latestMessage->sent_at)->format('H:i d/m')
                                        : null,
            ]);

        return response()->json(['success' => true, 'rooms' => $rooms]);
    }

    /**
     * Lấy tin nhắn của 1 phòng (admin xem + trả lời)
     * GET /admin/chatroom/{roomId}/messages?after_id=xxx
     */
    public function getMessages(Request $request, int $roomId): \Illuminate\Http\JsonResponse
    {
        $room = ChatRoom::findOrFail($roomId);

        $query = ChatMessage::where('room_id', $roomId)
            ->with('sender:user_id,full_name,avatar_url,role_id');

        if ($request->filled('after_id')) {
            $query->where('message_id', '>', $request->integer('after_id'));
        } else {
            $query->latest('sent_at')->take(50);
        }

        $userId = $room->user_id; // ID của bệnh nhân

        $messages = $query->orderBy('sent_at', 'asc')->get()->map(fn($msg) => [
            'message_id'   => $msg->message_id,
            'sender_id'    => $msg->sender_id,
            'sender_name'  => $msg->is_ai ? 'AI Trợ Lý' : ($msg->sender->full_name ?? 'Ẩn danh'),
            'avatar'       => $msg->sender->avatar_url ?? null,
            'message_text' => $msg->message_text,
            'is_read'      => $msg->is_read,
            'is_ai'        => $msg->is_ai,
            'sent_at'      => \Carbon\Carbon::parse($msg->sent_at)->format('H:i d/m/Y'),
            'is_patient'   => $msg->sender_id == $userId,
        ]);

        // Đánh dấu đã đọc tin nhắn của bệnh nhân
        ChatMessage::where('room_id', $roomId)
            ->where('sender_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json([
            'success'  => true,
            'messages' => $messages,
            'room'     => [
                'room_id'      => $room->room_id,
                'patient_name' => $room->patient->full_name ?? 'Ẩn danh',
                'status'       => $room->status,
                'doctor_id'    => $room->doctor_id,
            ]
        ]);
    }

    /**
     * Admin/CSKH gửi tin nhắn trả lời bệnh nhân
     * POST /admin/chatroom/{roomId}/send
     */
    public function sendMessage(Request $request, int $roomId): \Illuminate\Http\JsonResponse
    {
        $request->validate(['message_text' => 'required|string|max:2000']);

        $room = ChatRoom::findOrFail($roomId);

        // Assign phòng cho staff nếu chưa có. Cột doctor_id đang lưu user_id của CSKH/admin.
        if (!$room->doctor_id) {
            $room->update(['doctor_id' => Auth::id()]);
        }

        $message = ChatMessage::create([
            'room_id'      => $roomId,
            'sender_id'    => Auth::id(),
            'message_text' => trim($request->message_text),
            'is_read'      => 0,
            'sent_at'      => now(),
            'is_ai'        => 0,
        ]);

        return response()->json(['success' => true, 'message_id' => $message->message_id]);
    }

    /**
     * Đóng phòng chat
     * POST /admin/chatroom/{roomId}/close
     */
    public function closeRoom(int $roomId): \Illuminate\Http\JsonResponse
    {
        $room = ChatRoom::findOrFail($roomId);
        $room->update(['status' => 'Đóng', 'closed_at' => now()]);

        return response()->json(['success' => true]);
    }
}
