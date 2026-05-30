<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\SlotHoldRequest;
use App\Services\User\SlotHoldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SlotHoldController
 *
 * Chỉ điều phối HTTP:
 *  - Inject Request / Service
 *  - Gọi Service
 *  - Trả JSON response
 *
 * Không chứa bất kỳ business logic nào.
 *
 * Routes (thêm vào routes/web.php hoặc routes/api.php):
 *
 *   POST   /api/slot-hold              → hold
 *   DELETE /api/slot-hold              → release
 *   GET    /api/slot-hold/status       → status
 */
class SlotHoldController extends Controller
{
    public function __construct(protected SlotHoldService $slotHoldService)
    {
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/slot-hold
    // Bệnh nhân chọn khung giờ → tạm giữ slot 5 phút
    // ────────────────────────────────────────────────────────────

    /**
     * @bodyParam schedule_id      int    required  ID của doctorschedule
     * @bodyParam appointment_time string required  Giờ khám (HH:MM)
     *
     * @response 200 {
     *   "success": true,
     *   "expires_at": "2026-06-01T09:05:00+07:00",
     *   "seconds_remaining": 300,
     *   "hold_minutes": 5,
     *   "message": "Khung giờ đã được giữ cho bạn trong 5 phút."
     * }
     */
    public function hold(SlotHoldRequest $request): JsonResponse
    {
        try {
            $result = $this->slotHoldService->holdSlot(
                userId:          Auth::id(),
                scheduleId:      (int) $request->validated('schedule_id'),
                appointmentTime: $request->validated('appointment_time'),
            );

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ────────────────────────────────────────────────────────────
    // DELETE /api/slot-hold
    // Bệnh nhân thoát / quay lại → giải phóng slot
    // ────────────────────────────────────────────────────────────

    /**
     * @bodyParam schedule_id int required
     *
     * @response 200 { "success": true, "message": "Đã giải phóng khung giờ." }
     */
    public function release(Request $request): JsonResponse
    {
        $request->validate([
            'schedule_id' => 'required_without:appointment_id|integer|exists:doctorschedules,schedule_id',
            'appointment_id' => 'required_without:schedule_id|integer|exists:appointments,appointment_id',
        ]);

        $released = $this->slotHoldService->releaseHold(
            userId:        Auth::id(),
            scheduleId:    $request->has('schedule_id') ? (int) $request->schedule_id : null,
            appointmentId: $request->has('appointment_id') ? (int) $request->appointment_id : null,
        );

        return response()->json([
            'success' => true,
            'released' => $released,
            'message' => $released
                ? 'Đã giải phóng khung giờ.'
                : 'Không tìm thấy slot đang giữ.',
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/slot-hold/status?schedule_id=xxx
    // Frontend poll để cập nhật countdown
    // ────────────────────────────────────────────────────────────

    /**
     * @queryParam schedule_id int required
     *
     * @response 200 {
     *   "held": true,
     *   "expires_at": "2026-06-01T09:05:00+07:00",
     *   "seconds_remaining": 247
     * }
     */
    public function status(Request $request): JsonResponse
    {
        $request->validate([
            'schedule_id' => 'required|integer|exists:doctorschedules,schedule_id',
        ]);

        $status = $this->slotHoldService->getHoldStatus(
            userId:     Auth::id(),
            scheduleId: (int) $request->schedule_id,
        );

        return response()->json($status);
    }
}