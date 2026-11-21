<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        request()->validate([
            'page' => 'nullable|integer|min:1|max:1000',
        ]); /* fixed: chan URL page=abc/page qua lon */

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
    public function listJson(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if (!$this->expectsJsonRequest($request)) {
            return redirect()
                ->route('admin.chatroom.index')
                ->with('warning', 'Đường dẫn dữ liệu chat không hợp lệ. Trang đã được tải lại.');
        } /* fixed: khong hien JSON thô khi user go truc tiep URL API */

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

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'rooms' => $rooms,
            'data' => ['rooms' => $rooms],
        ]); /* fixed: JSON API co cau truc nhat quan va giu tuong thich key cu */
    }

    /**
     * Lấy tin nhắn của 1 phòng (admin xem + trả lời)
     * GET /admin/chatroom/{roomId}/messages?after_id=xxx
     */
    public function getMessages(Request $request, int $roomId): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if (!$this->expectsJsonRequest($request)) {
            return redirect()
                ->route('admin.chatroom.index')
                ->with('warning', 'Đường dẫn dữ liệu chat không hợp lệ. Trang đã được tải lại.');
        } /* fixed: doi id tren URL API messages thi quay ve man chat thay vi lo JSON/404 */

        $request->validate([
            'after_id' => 'nullable|integer|min:0',
        ]); /* fixed: validate query polling */

        $room = ChatRoom::find($roomId);
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Phòng chat không tồn tại hoặc đã bị xóa. Vui lòng tải lại danh sách.',
                'data' => null,
            ], 404);
        }

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
            'message'  => 'OK',
            'messages' => $messages,
            'room'     => [
                'room_id'      => $room->room_id,
                'patient_name' => $room->patient->full_name ?? 'Ẩn danh',
                'status'       => $room->status,
                'doctor_id'    => $room->doctor_id,
            ],
            'data' => [
                'messages' => $messages,
                'room' => [
                    'room_id'      => $room->room_id,
                    'patient_name' => $room->patient->full_name ?? 'Ẩn danh',
                    'status'       => $room->status,
                    'doctor_id'    => $room->doctor_id,
                ],
            ],
        ]);
    }

    /**
     * Admin/CSKH gửi tin nhắn trả lời bệnh nhân
     * POST /admin/chatroom/{roomId}/send
     */
    public function sendMessage(Request $request, int $roomId): \Illuminate\Http\JsonResponse
    {
        $request->validate(['message_text' => ['required', 'string', 'max:2000', 'not_regex:/\A[\s\x{3000}]*\z/u']]);

        $room = ChatRoom::where('room_id', $roomId)
            ->where('status', 'Mở')
            ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Phòng chat không còn mở hoặc đã bị xóa. Vui lòng tải lại danh sách.',
                'data' => null,
            ], 409); /* fixed: gui vao phong da dong/xoa phai bao UI */
        }

        try {
            return DB::transaction(function () use ($request, $room, $roomId) {
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

                return response()->json([
                    'success' => true,
                    'message' => 'OK',
                    'message_id' => $message->message_id,
                    'data' => ['message_id' => $message->message_id],
                ]); /* fixed: transaction cho assign + message */
            });
        } catch (\Throwable $e) {
            Log::error('Admin chat send failed', [
                'room_id' => $roomId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi, vui lòng thử lại sau.',
                'data' => null,
            ], 500);
        }
    }

    /**
     * Đóng phòng chat
     * POST /admin/chatroom/{roomId}/close
     */
    public function closeRoom(int $roomId): \Illuminate\Http\JsonResponse
    {
        $room = ChatRoom::find($roomId);
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Phòng chat không tồn tại hoặc đã bị xóa. Vui lòng tải lại danh sách.',
                'data' => null,
            ], 404);
        }

        if ($room->status !== 'Mở') {
            return response()->json([
                'success' => false,
                'message' => 'Phòng chat đã được người khác đóng trước đó. Vui lòng tải lại danh sách.',
                'data' => null,
            ], 409); /* fixed: dong phong o tab thu 2 phai bao loi */
        }

        $room->update(['status' => 'Đóng', 'closed_at' => now()]);

        return response()->json(['success' => true, 'message' => 'OK', 'data' => null]);
    }

    /**
     * Xóa hoàn toàn phòng chat và tin nhắn liên quan
     * DELETE /admin/chatroom/{roomId}
     */
    public function deleteRoom(int $roomId): \Illuminate\Http\JsonResponse
    {
        $deleted = DB::transaction(function () use ($roomId) {
            $room = ChatRoom::where('room_id', $roomId)->lockForUpdate()->first();
            if (!$room) {
                return false;
            }

            // Xóa tất cả tin nhắn trong phòng trước (nếu database không dùng Cascade delete)
            ChatMessage::where('room_id', $roomId)->delete();
            $room->delete();
            return true;
        }); /* fixed: xoa phong va tin nhan cung transaction */

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Phòng chat không tồn tại hoặc đã bị xóa. Vui lòng tải lại danh sách.',
                'data' => null,
            ], 404); /* fixed: xoa lan 2 bao loi ro */
        }

        return response()->json(['success' => true, 'message' => 'OK', 'data' => null]);
    }

    /**
     * Xóa một tin nhắn bất kỳ (Admin)
     * DELETE /admin/chatroom/messages/{messageId}
     */
    public function deleteMessage(int $messageId): \Illuminate\Http\JsonResponse
    {
        $message = ChatMessage::find($messageId);
        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Tin nhắn không tồn tại hoặc đã bị xóa. Vui lòng tải lại cuộc trò chuyện.',
                'data' => null,
            ], 404);
        }

        $message->delete();

        return response()->json(['success' => true, 'message' => 'OK', 'data' => null]);
    }

    private function expectsJsonRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }
}
