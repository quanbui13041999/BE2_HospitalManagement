<?php

// app/Services/User/SlotHoldService.php

namespace App\Services\User;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlotHoldService
{
    public const HOLD_STATUS = 'Giữ slot';
    public const HOLD_DURATION_MINUTES = 5;

    // ── Public API ─────────────────────────────────────────────────

    /**
     * Controller API: Hold a slot for a user
     * Returns a response-ready array
     */
    public function holdSlot(
        int    $userId,
        int    $scheduleId,
        string $appointmentTime
    ): array {
        $hold = $this->createHold(
            userId:         $userId,
            scheduleId:     $scheduleId,
            serviceId:      1,  // Default to first service - frontend should pass this
            appointmentTime: $appointmentTime,
        );

        return [
            'success'           => true,
            'appointment_id'    => $hold->appointment_id,
            'expires_at'        => $hold->slot_hold_expire->toIso8601String(),
            'seconds_remaining' => $hold->slot_hold_expire->diffInSeconds(now()),
            'hold_minutes'      => self::HOLD_DURATION_MINUTES,
            'message'           => 'Khung giờ đã được giữ cho bạn trong ' . self::HOLD_DURATION_MINUTES . ' phút.',
        ];
    }

    /**
     * Tạo hold tạm thời cho một slot.
     *
     * Dùng pessimistic lock (lockForUpdate) để đảm bảo race-condition
     * không xảy ra khi nhiều user cùng chọn slot cuối cùng.
     *
     * @return Appointment  Bản ghi hold vừa tạo
     *
     * @throws \RuntimeException  Khi slot không còn hoặc user đang có hold khác
     */
    public function createHold(
        int    $userId,
        int    $scheduleId,
        int    $serviceId,
        string $appointmentTime
    ): Appointment {

        return DB::transaction(function () use ($userId, $scheduleId, $serviceId, $appointmentTime) {

            // 1. Lock schedule row để chặn concurrent request
            /** @var DoctorSchedule $schedule */
            $schedule = DoctorSchedule::lockForUpdate()->findOrFail($scheduleId);

            // 2. Kiểm tra schedule còn hoạt động
            $this->assertScheduleActive($schedule);

            // 3. Kiểm tra slot còn trống (tính cả các hold chưa expire)
            $occupied = $this->countOccupiedSlots($scheduleId);
            if ($occupied >= $schedule->max_slot) {
                    // Nếu slot đã đầy nhưng một số hold vừa hết hạn (do client countdown lệch),
                    // hãy thử dọn expired holds nhanh để phản ánh “dữ liệu mới nhất”.
                    Appointment::expiredHolds()->delete();

                    $occupied = $this->countOccupiedSlots($scheduleId);
                    if ($occupied >= $schedule->max_slot) {
                        throw new \RuntimeException('Slot này đã đầy, vui lòng chọn khung giờ khác.');
                    }
            }


            // 4. Xóa hold cũ (nếu có) của cùng user trên cùng schedule
            $this->releaseExistingHoldForUser($userId, $scheduleId);

            // 5. Tính số thứ tự hàng đợi
            $queueNumber = $this->nextQueueNumber($scheduleId);

            $workDate = $schedule->work_date instanceof Carbon
                ? $schedule->work_date->toDateString()
                : Carbon::parse($schedule->work_date)->toDateString();

            $appointmentDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $workDate . ' ' . $appointmentTime
            );

            if (!$appointmentDateTime) {
                throw new \RuntimeException('Dữ liệu giờ khám không hợp lệ. Vui lòng chọn lại khung giờ.');
            }

            $appointmentTimeEnd = $appointmentDateTime->copy()->addMinutes($schedule->slot_duration ?: 15);

            // 6. Tạo hold mới
            $hold = Appointment::create([
                'user_id'            => $userId,
                'schedule_id'        => $scheduleId,
                'service_id'         => $serviceId,
                'appointment_time'   => $appointmentDateTime,
                'appointment_timeEnd'=> $appointmentTimeEnd,
                'queue_number'       => $queueNumber,
                'status'             => Appointment::STATUS_HOLD,
                'slot_hold_expire'   => now()->addMinutes(Appointment::HOLD_DURATION_MINUTES),
                'created_at'         => now(),
            ]);

            // Đảm bảo value còn active theo thời gian thực (đề phòng timezone/DB lag)
            // giúp tránh trường hợp vừa giữ xong nhưng isHoldActive() trả false.
            $hold->refresh();


            Log::info('SlotHold: created', [
                'appointment_id' => $hold->appointment_id,
                'user_id'        => $userId,
                'schedule_id'    => $scheduleId,
                'expire_at'      => $hold->slot_hold_expire,
            ]);

            return $hold;
        });
    }

    /**
     * Gia hạn hold thêm HOLD_DURATION_MINUTES phút.
     * Chỉ cho phép nếu hold còn active và thuộc về user.
     *
     * @throws \RuntimeException
     */
    public function extendHold(int $appointmentId, int $userId): Appointment
    {
        return DB::transaction(function () use ($appointmentId, $userId) {

            /** @var Appointment $hold */
            $hold = Appointment::lockForUpdate()->findOrFail($appointmentId);

            $this->assertOwnedBy($hold, $userId);
            $this->assertIsActiveHold($hold);

            $hold->update([
                'slot_hold_expire' => now()->addMinutes(Appointment::HOLD_DURATION_MINUTES),
            ]);

            Log::info('SlotHold: extended', [
                'appointment_id' => $hold->appointment_id,
                'new_expire_at'  => $hold->slot_hold_expire,
            ]);

            return $hold->fresh();
        });
    }

    /**
     * Xác nhận đặt lịch: chuyển hold → Chờ xác nhận.
     *
     * @throws \RuntimeException
     */
    public function confirmHold(
        int    $appointmentId,
        int    $userId,
        ?string $note = null
    ): Appointment {

        return DB::transaction(function () use ($appointmentId, $userId, $note) {

            /** @var Appointment $hold */
            $hold = Appointment::lockForUpdate()->findOrFail($appointmentId);

            $this->assertOwnedBy($hold, $userId);
            $this->assertIsActiveHold($hold);

            $hold->update([
                'status'           => Appointment::STATUS_PENDING,
                'note'             => $note,
                'slot_hold_expire' => null,          // Không còn cần expire
            ]);

            Log::info('SlotHold: confirmed → pending', [
                'appointment_id' => $hold->appointment_id,
                'user_id'        => $userId,
            ]);

            return $hold->fresh(['schedule', 'service', 'user']);
        });
    }

    /**
     * Giải phóng hold thủ công (user bấm hủy).
     *
     * @throws \RuntimeException
     */
    public function releaseHold(int $userId, ?int $scheduleId = null, ?int $appointmentId = null): bool
    {
        // If appointmentId is provided, use the old behavior
        if ($appointmentId !== null) {
            $this->releaseHoldByAppointment($appointmentId, $userId);
            return true;
        }

        // Otherwise find by userId and scheduleId
        $hold = Appointment::where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->where('status', Appointment::STATUS_HOLD)
            ->first();

        if (!$hold) {
            return false;
        }

        $hold->delete();

        Log::info('SlotHold: released manually', [
            'appointment_id' => $hold->appointment_id,
            'user_id'        => $userId,
        ]);

        return true;
    }

    private function releaseHoldByAppointment(int $appointmentId, int $userId): void
    {
        DB::transaction(function () use ($appointmentId, $userId) {

            /** @var Appointment $hold */
            $hold = Appointment::lockForUpdate()->findOrFail($appointmentId);

            $this->assertOwnedBy($hold, $userId);

            // Chỉ xóa khi vẫn ở trạng thái hold
            if ($hold->status !== Appointment::STATUS_HOLD) {
                throw new \RuntimeException('Chỉ có thể hủy bản ghi đang ở trạng thái giữ slot.');
            }

            $hold->delete();

            Log::info('SlotHold: released manually', [
                'appointment_id' => $appointmentId,
                'user_id'        => $userId,
            ]);
        });
    }

    /**
     * Dọn dẹp tất cả hold đã hết hạn.
     * Được gọi bởi scheduled command.
     *
     * @return int  Số bản ghi đã xóa
     */
    public function cleanExpiredHolds(): int
    {
        $count = Appointment::expiredHolds()->delete();

        if ($count > 0) {
            Log::info("SlotHold: cleaned {$count} expired hold(s).");
        }

        return $count;
    }

    public function releaseExpired(): int
    {
        return $this->cleanExpiredHolds();
    }

    /**
     * Lấy thông tin hold hiện tại của user cho một schedule.
     */
    public function getActiveHoldForUser(int $userId, int $scheduleId): ?Appointment
    {
        return Appointment::where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->activeHolds()
            ->first();
    }

    /**
     * Controller API: Get hold status for countdown display
     * Returns a response-ready array
     */
    public function getHoldStatus(int $userId, int $scheduleId): array
    {
        $hold = $this->getActiveHoldForUser($userId, $scheduleId);

        if (!$hold) {
            return [
                'held'              => false,
                'expires_at'        => null,
                'seconds_remaining' => 0,
            ];
        }

        return [
            'held'              => true,
            'appointment_id'    => $hold->appointment_id,
            'expires_at'        => $hold->slot_hold_expire->toIso8601String(),
            'seconds_remaining' => max(0, $hold->slot_hold_expire->diffInSeconds(now())),
        ];
    }

    /**
     * Lấy số slot còn trống của một schedule.
     */
    public function availableSlotCount(int $scheduleId): int
    {
        $schedule = DoctorSchedule::findOrFail($scheduleId);
        $occupied = $this->countOccupiedSlots($scheduleId);

        return max(0, $schedule->max_slot - $occupied);
    }

    // ── Private Helpers ────────────────────────────────────────────

    /**
     * Đếm số slot đang bị chiếm (hold active + pending + confirmed).
     */
    private function countOccupiedSlots(int $scheduleId): int
    {
        return Appointment::where('schedule_id', $scheduleId)
            ->occupyingSlot()
            ->count();
    }

    /**
     * Số thứ tự hàng đợi tiếp theo cho schedule.
     */
    private function nextQueueNumber(int $scheduleId): int
    {
        $max = Appointment::where('schedule_id', $scheduleId)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->max('queue_number');

        return ($max ?? 0) + 1;
    }

    /**
     * Xóa hold cũ của user trên cùng schedule (nếu có).
     * Không throw exception nếu không tìm thấy.
     */
    private function releaseExistingHoldForUser(int $userId, int $scheduleId): void
    {
        $deleted = Appointment::where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->where('status', Appointment::STATUS_HOLD)
            ->delete();

        if ($deleted) {
            Log::info('SlotHold: old hold removed before creating new one', [
                'user_id'     => $userId,
                'schedule_id' => $scheduleId,
            ]);
        }
    }

    // ── Assertion Helpers ──────────────────────────────────────────

    private function assertScheduleActive(DoctorSchedule $schedule): void
    {
        $activeStatuses = [DoctorSchedule::STATUS_ACTIVE, DoctorSchedule::STATUS_ACTIVE_EN];

        if (!in_array($schedule->status, $activeStatuses)) {
            throw new \RuntimeException('Lịch khám này không còn hoạt động.');
        }

        if (Carbon::parse($schedule->work_date)->isPast() &&
            Carbon::parse($schedule->work_date)->isToday() === false) {
            throw new \RuntimeException('Ngày khám đã qua, không thể đặt lịch.');
        }
    }

    private function assertOwnedBy(Appointment $appointment, int $userId): void
    {
        if ((int) $appointment->user_id !== $userId) {
            throw new \RuntimeException('Bạn không có quyền thao tác với slot này.');
        }
    }

    private function assertIsActiveHold(Appointment $hold): void
    {
        if ($hold->status !== Appointment::STATUS_HOLD) {
            throw new \RuntimeException('Slot này không ở trạng thái giữ chỗ.');
        }

        if (!$hold->isHoldActive()) {
            throw new \RuntimeException('Thời gian giữ slot đã hết hạn. Vui lòng chọn lại.');
        }
    }
}