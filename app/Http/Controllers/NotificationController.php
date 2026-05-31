<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:all,read,unread',
            'type' => 'nullable|string|max:80',
        ]);

        $user = Auth::user();
        $status = $request->input('status', 'all');
        $type = $request->input('type');

        $notifications = $this->notifications->listForUser(
            $user,
            $status === 'all' ? null : $status,
            $type
        );

        $types = $this->notifications->visibleQuery($user)
            ->selectRaw('COALESCE(type, notif_type) as notification_type')
            ->whereRaw('COALESCE(type, notif_type) IS NOT NULL')
            ->distinct()
            ->orderBy('notification_type')
            ->pluck('notification_type');

        $layout = $this->layoutFor($user);

        return view('notifications.index', compact('notifications', 'status', 'type', 'types', 'layout'));
    }

    public function show(Notification $notification)
    {
        $user = Auth::user();
        abort_unless(
            $this->notifications->visibleQuery($user)->whereKey($notification->getKey())->exists(),
            403
        ); /* fixed: chi cho xem thong bao thuoc pham vi user */

        $this->notifications->markAsRead($notification, $user);

        $notification->load('sender');
        $layout = $this->layoutFor($user);

        return view('notifications.show', compact('notification', 'layout'));
    }

    private function layoutFor($user): string
    {
        $roleName = mb_strtolower((string) ($user->role?->role_name ?? ''), 'UTF-8');

        if ((method_exists($user, 'isAdmin') && $user->isAdmin()) || $user->role_id === 1 || $roleName === 'admin') {
            return 'layouts.admin';
        }

        return 'layouts.user';
    }

    public function dropdown()
    {
        $user = Auth::user();
        $items = $this->notifications->latestForUser($user, 8)->map(function (Notification $notification) use ($user) {
            return [
                'id' => $notification->notification_id,
                'title' => $notification->title,
                'message' => str($notification->displayMessage())->limit(90)->toString(),
                'type' => $notification->displayType(),
                'is_read' => $notification->isReadBy($user),
                'created_at' => optional($notification->created_at)->diffForHumans(),
                'url' => route('notifications.show', $notification),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'unread_count' => $this->notifications->unreadCount($user),
            'items' => $items,
            'all_url' => route('notifications.index'),
            'data' => [
                'unread_count' => $this->notifications->unreadCount($user),
                'items' => $items,
                'all_url' => route('notifications.index'),
            ],
        ]);
    }

    public function unreadCount()
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'unread_count' => $this->notifications->unreadCount(Auth::user()),
            'data' => ['unread_count' => $this->notifications->unreadCount(Auth::user())],
        ]);
    }

    public function markRead(Notification $notification)
    {
        $user = Auth::user();
        abort_unless(
            $this->notifications->visibleQuery($user)->whereKey($notification->getKey())->exists(),
            403
        ); /* fixed: ngan user mark-read thong bao khong thuoc ve minh */

        $this->notifications->markAsRead($notification, $user);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'unread_count' => $this->notifications->unreadCount(Auth::user()),
            'data' => ['unread_count' => $this->notifications->unreadCount(Auth::user())],
        ]);
    }

    public function markAllRead()
    {
        $count = $this->notifications->markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'marked_count' => $count,
            'unread_count' => 0,
            'data' => ['marked_count' => $count, 'unread_count' => 0],
        ]);
    }
}
