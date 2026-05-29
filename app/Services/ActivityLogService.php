<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'remember_token',
        '_token',
        'api_key',
        'secret',
    ];

    /**
     * Ghi nhật ký hoạt động. Lỗi ghi log không được làm hỏng nghiệp vụ chính.
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?string $subjectType = null,
        int|string|null $subjectId = null,
        array $metadata = [],
        ?string $status = 'success',
        ?User $actor = null
    ): ?ActivityLog {
        try {
            $actor ??= Auth::user();
            $request = request();

            return ActivityLog::create([
                'user_id' => $actor?->user_id,
                'actor_name' => $actor?->full_name,
                'actor_email' => $actor?->email,
                'role_name' => self::roleName($actor),
                'action' => mb_substr($action, 0, 255),
                'subject_type' => $subjectType ? mb_substr($subjectType, 0, 80) : null,
                'subject_id' => is_numeric($subjectId) ? (int) $subjectId : null,
                'description' => $description,
                'metadata' => self::sanitizeMetadata($metadata),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'status' => $status ?: 'success',
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Không thể ghi activity log', [
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public static function logFailed(
        string $action,
        ?string $description = null,
        ?string $subjectType = null,
        int|string|null $subjectId = null,
        array $metadata = [],
        ?User $actor = null
    ): ?ActivityLog {
        return self::log($action, $description, $subjectType, $subjectId, $metadata, 'failed', $actor);
    }

    public static function roleName(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return $user->role?->role_name
            ?? match ((int) $user->role_id) {
                1 => 'Admin',
                2 => 'Bác sĩ',
                3 => 'Bệnh nhân',
                4 => 'Lễ tân',
                5 => 'Dược sĩ',
                default => 'Người dùng',
            };
    }

    public static function summarizeChanges(array $before, array $after, array $allowedKeys): array
    {
        $before = Arr::only($before, $allowedKeys);
        $after = Arr::only($after, $allowedKeys);
        $changes = [];

        foreach ($allowedKeys as $key) {
            $old = $before[$key] ?? null;
            $new = $after[$key] ?? null;

            if ((string) $old !== (string) $new) {
                $changes[$key] = [
                    'before' => $old,
                    'after' => $new,
                ];
            }
        }

        return $changes;
    }

    private static function sanitizeMetadata(array $metadata): array
    {
        return self::sanitizeArray($metadata);
    }

    private static function sanitizeArray(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, self::SENSITIVE_KEYS, true)) {
                $clean[$key] = '[filtered]';
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = self::sanitizeArray($value);
                continue;
            }

            if (is_string($value) && mb_strlen($value) > 1000) {
                $clean[$key] = mb_substr($value, 0, 1000) . '...';
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }
}
