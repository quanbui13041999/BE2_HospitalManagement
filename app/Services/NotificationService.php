<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function createForUser(
        int $userId,
        string $title,
        string $message,
        string $type = 'general',
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?int $senderId = null,
        ?string $actionUrl = null
    ): Notification {
        return DB::transaction(function () use ($userId, $title, $message, $type, $relatedType, $relatedId, $senderId, $actionUrl) {
            $notification = Notification::create([
                'user_id' => $userId,
                'target_type' => 'user',
                'target_user_id' => $userId,
                'notif_type' => $type,
                'type' => $type,
                'title' => $title,
                'content' => $message,
                'message' => $message,
                'ref_type' => $relatedType,
                'ref_id' => $relatedId,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'sender_id' => $senderId,
                'action_url' => $actionUrl,
                'is_read' => false,
            ]);

            NotificationUser::updateOrCreate(
                ['notification_id' => $notification->notification_id, 'user_id' => $userId],
                ['read_at' => null]
            );

            return $notification;
        });
    }

    public function createForUsers(
        array|Collection $userIds,
        string $title,
        string $message,
        string $type = 'general',
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?int $senderId = null,
        ?string $actionUrl = null
    ): Notification {
        $ids = collect($userIds)->filter()->unique()->values();

        return DB::transaction(function () use ($ids, $title, $message, $type, $relatedType, $relatedId, $senderId, $actionUrl) {
            $notification = Notification::create([
                'target_type' => 'users',
                'notif_type' => $type,
                'type' => $type,
                'title' => $title,
                'content' => $message,
                'message' => $message,
                'ref_type' => $relatedType,
                'ref_id' => $relatedId,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'sender_id' => $senderId,
                'action_url' => $actionUrl,
                'is_read' => false,
            ]);

            $this->attachUnreadRecipients($notification, $ids);

            return $notification;
        });
    }

    public function createForRole(
        string|int $role,
        string $title,
        string $message,
        string $type = 'general',
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?int $senderId = null,
        ?string $actionUrl = null
    ): Notification {
        $roleValue = (string) $role;
        $roleModel = is_numeric($role)
            ? Role::find((int) $role)
            : Role::where('role_name', $roleValue)->first();

        $targetRole = $roleModel?->role_name ?: $roleValue;

        return Notification::create([
            'target_type' => 'role',
            'target_role' => $targetRole,
            'notif_type' => $type,
            'type' => $type,
            'title' => $title,
            'content' => $message,
            'message' => $message,
            'ref_type' => $relatedType,
            'ref_id' => $relatedId,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'sender_id' => $senderId,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    public function createForAll(
        string $title,
        string $message,
        string $type = 'general',
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?int $senderId = null,
        ?string $actionUrl = null
    ): Notification {
        return Notification::create([
            'target_type' => 'all',
            'notif_type' => $type,
            'type' => $type,
            'title' => $title,
            'content' => $message,
            'message' => $message,
            'ref_type' => $relatedType,
            'ref_id' => $relatedId,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'sender_id' => $senderId,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    public function visibleQuery(User $user): Builder
    {
        return Notification::query()
            ->with(['sender', 'reads' => fn ($query) => $query->where('user_id', $user->user_id)])
            ->visibleTo($user);
    }

    public function listForUser(User $user, ?string $status = null, ?string $type = null, int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->visibleQuery($user);

        if ($status === 'read') {
            $query->readBy($user);
        } elseif ($status === 'unread') {
            $query->unreadBy($user);
        }

        if ($type) {
            $query->byType($type);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function latestForUser(User $user, int $limit = 8): Collection
    {
        return $this->visibleQuery($user)->latest()->limit($limit)->get();
    }

    public function unreadCount(User $user): int
    {
        return $this->visibleQuery($user)->unreadBy($user)->count();
    }

    public function markAsRead(Notification $notification, User $user): void
    {
        abort_unless($this->canView($notification, $user), 403);
        $notification->markAsReadFor($user);
    }

    public function markAllAsRead(User $user): int
    {
        $notifications = $this->visibleQuery($user)->unreadBy($user)->get();

        foreach ($notifications as $notification) {
            $notification->markAsReadFor($user);
        }

        return $notifications->count();
    }

    public function canView(Notification $notification, User $user): bool
    {
        return Notification::query()
            ->whereKey($notification->getKey())
            ->visibleTo($user)
            ->exists();
    }

    private function attachUnreadRecipients(Notification $notification, Collection $userIds): void
    {
        $now = now();
        $rows = $userIds->map(fn ($userId) => [
            'notification_id' => $notification->notification_id,
            'user_id' => (int) $userId,
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows) {
            NotificationUser::upsert($rows, ['notification_id', 'user_id'], ['read_at', 'updated_at']);
        }
    }
}
