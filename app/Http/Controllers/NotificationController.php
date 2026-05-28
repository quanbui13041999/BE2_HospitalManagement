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

        $layout = $user->role_id === 1 ? 'layouts.admin' : 'layouts.user';

        return view('notifications.index', compact('notifications', 'status', 'type', 'types', 'layout'));
    }

    public function show(Notification $notification)
    {
        $user = Auth::user();
        $this->notifications->markAsRead($notification, $user);

        $notification->load('sender');
        $layout = $user->role_id === 1 ? 'layouts.admin' : 'layouts.user';

        return view('notifications.show', compact('notification', 'layout'));
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
            'unread_count' => $this->notifications->unreadCount($user),
            'items' => $items,
            'all_url' => route('notifications.index'),
        ]);
    }

    public function unreadCount()
    {
        return response()->json([
            'unread_count' => $this->notifications->unreadCount(Auth::user()),
        ]);
    }

    public function markRead(Notification $notification)
    {
        $this->notifications->markAsRead($notification, Auth::user());

        return response()->json([
            'success' => true,
            'unread_count' => $this->notifications->unreadCount(Auth::user()),
        ]);
    }

    public function markAllRead()
    {
        $count = $this->notifications->markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'marked_count' => $count,
            'unread_count' => 0,
        ]);
    }
}
