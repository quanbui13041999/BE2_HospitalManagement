<?php

namespace App\Services\Doctor;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SlotHoldService
 *
 * Xử lý toàn bộ business logic cho tính năng Giữ slot tạm thời:
 *
 *  1. holdSlot()          — Kiểm tra & tạo bản ghi giữ slot (status = 'Giữ slot')
 *  2. confirmHold()       — Chuyển Giữ slot → Chờ xác nhận khi đặt lịch thành công
 *  3. releaseHold()       — Bệnh nhân chủ động huỷ / rời trang
 *  4. releaseExpired()    — Cron job tự động giải phóng slot hết hạn
 *  5. getHoldStatus()     — Trả trạng thái hold hiện tại cho frontend countdown
 *  6. isSlotAvailable()   — Kiểm tra slot còn trống không (dùng nội bộ & public)
 *
 * Constants:
 *   HOLD_MINUTES — thời gian giữ slot (mặc định 5 phút)
 *   HOLD_STATUS  — giá trị status trong bảng appointments
 */
class SlotHoldService
{
    /**
     * Thời gian giữ slot (phút).
     * Có thể override qua config('appointment.slot_hold_minutes').
     */
    public const HOLD_MINUTES = 5;
    public const HOLD_STATUS  = 'Giữ slot';

    // ─────────────────────────────────────────────────────────────
    // 1. HOLD SLOT
    // ─────────────────────────────────────────────────────────────

    /**
     * Tạm giữ một khung giờ cho người dùng.
     *
     * Luồng:
     *  a) Kiểm tra slot không bị đặt / đang hold hợp lệ.
     *  b) Giải phóng hold cũ của chính user này (nếu đang hold slot khác).
     *  c) Kiểm tra xem user đã có hold / booking cho schedule này chưa.
     *  d) Insert bản ghi status='Giữ slot' + slot_hold_expire.
     *
     * @param  int    $userId
     * @param  int    $scheduleId
     * @param  string $appointmentTime  HH:MM
     * @return array  { success, expires_at, seconds_remaining, message }
     * @throws Exception
     */
    public function holdSlot(int $userId, int $scheduleId, string $appointmentTime): array
    {
        // Validate schedule tồn tại & đang hoạt động
        $schedule = $this->getActiveSchedule($scheduleId);
        if (!$schedule) {
            throw new Exception('Khung lịch khám không tồn tại hoặc đã ngưng hoạt động.');
        }

        // Validate work_date không trong quá khứ
        if (Carbon::parse($schedule->work_date)->isPast() &&
            !Carbon::parse($schedule->work_date)->isToday()) {
            throw new Exception('Ngày khám đã qua. Vui lòng chọn ngày khác.');
        }

        DB::beginTransaction();
        try {
            // --- Giải phóng hold cũ của user (nếu có, ở slot khác) ---
            $this->releaseUserActiveHolds($userId, $scheduleId);

            // --- Kiểm tra user đã có booking thật cho schedule này ---
            $alreadyBooked = DB::table('appointments')
                ->where('user_id', $userId)
                ->where('schedule_id', $scheduleId)
                ->whereNotIn('status', ['Đã hủy', 'Dời lịch', self::HOLD_STATUS])
                ->exists();

            if ($alreadyBooked) {
                throw new Exception('Bạn đã đặt lịch cho khung giờ này rồi.');
            }

            // --- Kiểm tra user đang hold chính xác slot này ---
            $existingHold = $this->getUserHoldForSchedule($userId, $scheduleId);
            if ($existingHold && Carbon::parse($existingHold->slot_hold_expire)->isFuture()) {
                // Gia hạn thêm 5 phút kể từ bây giờ (UX tốt hơn là báo lỗi)
                $newExpire = now()->addMinutes(self::HOLD_MINUTES);
                DB::table('appointments')
                    ->where('appointment_id', $existingHold->appointment_id)
                    ->update([
                        'slot_hold_expire'  => $newExpire,
                        'appointment_time'  => $schedule->work_date . ' ' . $appointmentTime . ':00',
                    ]);

                DB::commit();
                return $this->buildHoldResponse($newExpire);
            }

            // --- Kiểm tra slot còn chỗ không ---
            $available = $this->isSlotAvailable($scheduleId, $schedule->max_slot);
            if (!$available) {
                throw new Exception('Khung giờ này đã hết chỗ. Vui lòng chọn giờ khác.');
            }

            // --- Tạo bản ghi Giữ slot ---
            $expire    = now()->addMinutes(self::HOLD_MINUTES);
            $datetime  = $schedule->work_date . ' ' . $appointmentTime . ':00';

            DB::table('appointments')->insert([
                'user_id'          => $userId,
                'schedule_id'      => $scheduleId,
                'service_id'       => null,
                'appointment_time' => $datetime,
                'queue_number'     => null,
                'status'           => self::HOLD_STATUS,
                'note'             => null,
                'cancel_reason'    => null,
                'slot_hold_expire' => $expire,
                'rescheduled_from' => null,
                'created_at'       => now(),
            ]);

            DB::commit();
            return $this->buildHoldResponse($expire);

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 2. CONFIRM HOLD → chuyển thành lịch thật
    // ─────────────────────────────────────────────────────────────

    /**
     * Xác nhận hold, cập nhật thành 'Chờ xác nhận'.
     * Gọi từ AppointmentService::createAppointment() trong cùng transaction.
     *
     * @param  int    $userId
     * @param  int    $scheduleId
     * @param  array  $data   ['appointment_time', 'queue_number', 'service_id', ...]
     * @return int    appointment_id
     * @throws Exception
     */
    public function confirmHold(int $userId, int $scheduleId, array $data): int
    {
        $hold = $this->getUserHoldForSchedule($userId, $scheduleId);

        if (!$hold) {
            // Không có hold → tạo mới không qua hold (fallback)
            return 0;
        }

        // Kiểm tra hold chưa hết hạn
        if (Carbon::parse($hold->slot_hold_expire)->isPast()) {
            throw new Exception(
                'Thời gian giữ slot đã hết (' . self::HOLD_MINUTES . ' phút). ' .
                'Vui lòng chọn lại khung giờ.'
            );
        }

        // Cập nhật bản ghi hold thành lịch chính thức
        DB::table('appointments')
            ->where('appointment_id', $hold->appointment_id)
            ->update([
                'service_id'       => $data['service_id'] ?? null,
                'appointment_time' => $data['appointment_time'],
                'queue_number'     => $data['queue_number'],
                'status'           => 'Chờ xác nhận',
                'is_priority'      => $data['is_priority'] ?? false,
                'priority_type'    => $data['priority_type'] ?? null,
                'note'             => $data['note'] ?? null,
                'slot_hold_expire' => null,   // ← xoá expire
                'cancel_reason'    => null,
            ]);

        return $hold->appointment_id;
    }

    // ─────────────────────────────────────────────────────────────
    // 3. RELEASE HOLD (bệnh nhân chủ động)
    // ─────────────────────────────────────────────────────────────

    /**
     * Bệnh nhân huỷ hold (đóng tab, bấm "Quay lại").
     *
     * @param  int $userId
     * @param  int $scheduleId
     * @return bool
     */
    public function releaseHold(int $userId, int $scheduleId): bool
    {
        $deleted = DB::table('appointments')
            ->where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->where('status', self::HOLD_STATUS)
            ->delete();

        return $deleted > 0;
    }

    // ─────────────────────────────────────────────────────────────
    // 4. RELEASE EXPIRED (cron / command)
    // ─────────────────────────────────────────────────────────────

    /**
     * Giải phóng tất cả slot hold đã hết hạn.
     * Gọi từ Artisan Command hoặc Scheduler.
     *
     * @return int số bản ghi đã xoá
     */
    public function releaseExpired(): int
    {
        $count = DB::table('appointments')
            ->where('status', self::HOLD_STATUS)
            ->where('slot_hold_expire', '<', now())
            ->delete();

        if ($count > 0) {
            Log::info("[SlotHold] Released {$count} expired slot hold(s).");
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────
    // 5. GET HOLD STATUS (frontend countdown)
    // ─────────────────────────────────────────────────────────────

    /**
     * Trả trạng thái hold hiện tại để frontend hiển thị countdown.
     *
     * @param  int    $userId
     * @param  int    $scheduleId
     * @return array  { held, expires_at|null, seconds_remaining }
     */
    public function getHoldStatus(int $userId, int $scheduleId): array
    {
        $hold = $this->getUserHoldForSchedule($userId, $scheduleId);

        if (!$hold || Carbon::parse($hold->slot_hold_expire)->isPast()) {
            return [
                'held'              => false,
                'expires_at'        => null,
                'seconds_remaining' => 0,
            ];
        }

        $expire = Carbon::parse($hold->slot_hold_expire);
        return [
            'held'              => true,
            'expires_at'        => $expire->toIso8601String(),
            'seconds_remaining' => max(0, (int) now()->diffInSeconds($expire, false)),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // 6. IS SLOT AVAILABLE (public helper)
    // ─────────────────────────────────────────────────────────────

    /**
     * Kiểm tra slot còn chỗ không.
     * Đếm tất cả booking hợp lệ (bao gồm cả 'Giữ slot' chưa hết hạn).
     *
     * @param  int $scheduleId
     * @param  int $maxSlot
     * @return bool
     */
    public function isSlotAvailable(int $scheduleId, int $maxSlot): bool
    {
        $occupied = DB::table('appointments')
            ->where('schedule_id', $scheduleId)
            ->where(function ($q) {
                $q->whereNotIn('status', ['Đã hủy', 'Dời lịch', self::HOLD_STATUS])
                  ->orWhere(function ($q2) {
                      // Hold chưa hết hạn cũng chiếm chỗ
                      $q2->where('status', self::HOLD_STATUS)
                         ->where('slot_hold_expire', '>', now());
                  });
            })
            ->count();

        return $occupied < $maxSlot;
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Lấy schedule đang hoạt động.
     */
    private function getActiveSchedule(int $scheduleId): ?object
    {
        return DB::table('doctorschedules')
            ->where('schedule_id', $scheduleId)
            ->where('status', 'Hoạt động')
            ->first();
    }

    /**
     * Lấy bản ghi hold hiện tại của user cho schedule.
     */
    private function getUserHoldForSchedule(int $userId, int $scheduleId): ?object
    {
        return DB::table('appointments')
            ->where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->where('status', self::HOLD_STATUS)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Giải phóng tất cả hold đang active của user,
     * trừ scheduleId hiện tại (nếu truyền vào).
     */
    private function releaseUserActiveHolds(int $userId, ?int $exceptScheduleId = null): void
    {
        $query = DB::table('appointments')
            ->where('user_id', $userId)
            ->where('status', self::HOLD_STATUS);

        if ($exceptScheduleId !== null) {
            $query->where('schedule_id', '!=', $exceptScheduleId);
        }

        $query->delete();
    }

    /**
     * Build response array thống nhất cho holdSlot / gia hạn.
     */
    private function buildHoldResponse(Carbon $expire): array
    {
        return [
            'success'           => true,
            'expires_at'        => $expire->toIso8601String(),
            'seconds_remaining' => self::HOLD_MINUTES * 60,
            'hold_minutes'      => self::HOLD_MINUTES,
            'message'           => "Khung giờ đã được giữ cho bạn trong " . self::HOLD_MINUTES . " phút.",
        ];
    }
}