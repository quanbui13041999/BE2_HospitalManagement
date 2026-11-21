<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'user_id',
        'notif_type',
        'title',
        'content',
        'message',
        'type',
        'target_type',
        'target_user_id',
        'target_role',
        'ref_id',
        'ref_type',
        'related_type',
        'related_id',
        'sender_id',
        'action_url',
        'is_read',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id', 'user_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'notification_user', 'notification_id', 'user_id')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function reads()
    {
        return $this->hasMany(NotificationUser::class, 'notification_id', 'notification_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function (Builder $query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('target_user_id', $userId)
                ->orWhereHas('reads', fn (Builder $readQuery) => $readQuery->where('user_id', $userId));
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where(function (Builder $query) use ($type) {
            $query->where('notif_type', $type)->orWhere('type', $type);
        });
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $roleName = $user->role?->role_name;
        $roleId = (string) $user->role_id;

        return $query->where(function (Builder $visible) use ($user, $roleName, $roleId) {
            $visible->where('user_id', $user->user_id)
                ->orWhere('target_user_id', $user->user_id)
                ->orWhereHas('reads', fn (Builder $readQuery) => $readQuery->where('user_id', $user->user_id))
                ->orWhere('target_type', 'all')
                ->orWhere(function (Builder $roleQuery) use ($roleName, $roleId) {
                    $roleQuery->where('target_type', 'role')
                        ->where(function (Builder $target) use ($roleName, $roleId) {
                            $target->where('target_role', $roleId);

                            if ($roleName) {
                                $target->orWhere('target_role', $roleName);
                            }
                        });
                });
        });
    }

    public function scopeReadBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $read) use ($user) {
            $read->where(function (Builder $legacy) use ($user) {
                $legacy->where('user_id', $user->user_id)->where('is_read', true);
            })->orWhereHas('reads', function (Builder $readQuery) use ($user) {
                $readQuery->where('user_id', $user->user_id)->whereNotNull('read_at');
            });
        });
    }

    public function scopeUnreadBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $unread) use ($user) {
            $unread->where(function (Builder $legacy) use ($user) {
                $legacy->where(function (Builder $personal) use ($user) {
                    $personal->where('user_id', $user->user_id)
                        ->orWhere('target_user_id', $user->user_id);
                })->where('is_read', false);
            })->orWhere(function (Builder $scoped) use ($user) {
                $scoped->whereNull('user_id')
                    ->where(function (Builder $target) use ($user) {
                        $target->whereNull('target_user_id')
                            ->orWhere('target_user_id', '!=', $user->user_id);
                    })
                    ->whereDoesntHave('reads', fn (Builder $readQuery) => $readQuery->where('user_id', $user->user_id));
            })
                ->orWhereHas('reads', function (Builder $readQuery) use ($user) {
                    $readQuery->where('user_id', $user->user_id)->whereNull('read_at');
                });
        });
    }

    public function markAsReadFor(User $user): void
    {
        NotificationUser::updateOrCreate(
            ['notification_id' => $this->notification_id, 'user_id' => $user->user_id],
            ['read_at' => now()]
        );

        if ((int) $this->user_id === (int) $user->user_id || (int) $this->target_user_id === (int) $user->user_id) {
            $this->forceFill(['is_read' => true])->save();
        }
    }

    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    public function markAsUnread(): bool
    {
        return $this->update(['is_read' => false]);
    }

    public function isReadBy(User $user): bool
    {
        if (((int) $this->user_id === (int) $user->user_id || (int) $this->target_user_id === (int) $user->user_id) && $this->is_read) {
            return true;
        }

        return $this->reads()
            ->where('user_id', $user->user_id)
            ->whereNotNull('read_at')
            ->exists();
    }

    public function displayType(): string
    {
        return $this->type ?: ($this->notif_type ?: 'general');
    }

    public function displayMessage(): string
    {
        return $this->message ?: ($this->content ?: '');
    }

    public function relatedUrl(): ?string
    {
        if ($this->action_url) {
            return $this->action_url;
        }

        $relatedType = $this->related_type ?: $this->ref_type;
        $relatedId = $this->related_id ?: $this->ref_id;

        if (! $relatedType || ! $relatedId) {
            return null;
        }

        return match ($relatedType) {
            'appointment' => Route::has('appointments.index') ? route('appointments.index') : null,
            'payment', 'invoice' => Route::has('user.payments.history') ? route('user.payments.history') : null,
            'news' => Route::has('news.show') ? route('news.show', $relatedId) : null,
            'medical_record' => Route::has('medical-records.show') ? route('medical-records.show', $relatedId) : null,
            default => null,
        };
    }
}
