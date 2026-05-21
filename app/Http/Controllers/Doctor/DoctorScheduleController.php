<?php
// ═══════════════════════════════════════════════════════════════════════════════
// app/Http/Controllers/DoctorScheduleController.php
// ═══════════════════════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDayOffRequest;
use App\Http\Requests\StoreRecurringScheduleRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Services\DayOffService;
use App\Services\RecurringScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller xử lý 2 nhóm chức năng:
 *
 *  A) Recurring Schedule  → prefix /api/v1/schedules/recurring
 *       POST   /preview   — Xem trước lịch (không ghi DB)
 *       POST   /          — Tạo lịch lặp lại
 *       GET    /{doctorId} — Danh sách lịch của bác sĩ (phân trang)
 *       DELETE /{scheduleId} — Xoá 1 slot lịch
 *
 *  B) Day-Off             → prefix /api/v1/schedules/day-off
 *       POST   /          — Đăng ký nghỉ + block slot + gửi email
 *       GET    /{doctorId} — Danh sách ngày nghỉ
 *       DELETE /{scheduleId} — Mở lại lịch đã block
 */
class DoctorScheduleController extends Controller
{
    public function __construct(
        private readonly RecurringScheduleService $recurringService,
        private readonly DayOffService            $dayOffService,
    ) {}

    /**
     * Kiểm tra quyền truy cập cho doctor_id.
     * Admin: tất cả
     * Doctor: chỉ doctor_id của mình
     */
    private function canAccessDoctor(int $doctorId): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->isAdmin) {
            return true;
        }

        if ($user->isDoctor) {
            return $user->doctor?->doctor_id === $doctorId;
        }

        return false;
    }

    // =========================================================================
    // A. RECURRING SCHEDULE
    // =========================================================================

    /**
     * POST /api/v1/schedules/recurring/preview
     *
     * Trả thông tin xem trước lịch tuần (số ngày, tổng slot, ngày áp dụng đến)
     * mà KHÔNG ghi vào DB — dùng cho UI preview real-time.
     */
    public function recurringPreview(StoreRecurringScheduleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $doctorId = $data['doctor_id'];

        if (!$this->canAccessDoctor($doctorId)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem lịch cho bác sĩ này.',
            ], 403);
        }

        $preview = $this->recurringService->preview($data);

        return response()->json([
            'success' => true,
            'data'    => $preview,
        ]);
    }

    /**
     * POST /api/v1/schedules/recurring
     *
     * Tạo lịch làm việc lặp lại cho bác sĩ trong khoảng apply_weeks tuần.
     * Bỏ qua các ngày đã có lịch tồn tại (idempotent).
     *
     * Response: { created, skipped, apply_until }
     */
    public function storeRecurring(StoreRecurringScheduleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $doctorId = $data['doctor_id'];

        if (!$this->canAccessDoctor($doctorId)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền tạo lịch cho bác sĩ này.',
            ], 403);
        }

        $result = $this->recurringService->generate($data);

        return response()->json([
            'success' => true,
            'message' => "Đã tạo {$result['created']} lịch, bỏ qua {$result['skipped']} lịch đã tồn tại.",
            'data'    => [
                'created' => $result['created'],
                'skipped' => $result['skipped'],
            ],
        ], 201);
    }

    /**
     * GET /api/v1/schedules/recurring/{doctorId}
     *
     * Lấy danh sách lịch active của bác sĩ từ hôm nay trở đi.
     * Query params:
     *   - from (date) — mặc định hôm nay
     *   - to   (date) — mặc định +4 tuần
     *   - per_page (int) — mặc định 20
     */
    public function indexRecurring(Request $request, int $doctorId): JsonResponse
    {
        if (!$this->canAccessDoctor($doctorId)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem lịch của bác sĩ này.',
            ], 403);
        }

        $from    = $request->input('from', now()->toDateString());
        $to      = $request->input('to', now()->addWeeks(4)->toDateString());
        $perPage = $request->integer('per_page', 20);

        $schedules = DoctorSchedule::forDoctor($doctorId)
            ->active()
            ->betweenDates($from, $to)
            ->orderBy('work_date')
            ->orderBy('start_time')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $schedules,
        ]);
    }

    /**
     * DELETE /api/v1/schedules/recurring/{scheduleId}
     *
     * Xoá 1 slot lịch. Từ chối nếu đã có appointment đang pending/confirmed.
     */
    public function destroyRecurring(int $scheduleId): JsonResponse
    {
        $schedule = DoctorSchedule::findOrFail($scheduleId);

        if (!$this->canAccessDoctor($schedule->doctor_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xoá lịch của bác sĩ này.',
            ], 403);
        }

        // Kiểm tra còn appointment không
        $hasActive = $schedule->activeAppointments()->isNotEmpty();
        if ($hasActive) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xoá lịch đang có bệnh nhân đặt. Hãy dùng chức năng đăng ký nghỉ thay thế.',
            ], 422);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xoá lịch thành công.',
        ]);
    }

    // =========================================================================
    // B. DAY-OFF (BLOCK LỊCH + EMAIL)
    // =========================================================================

    /**
     * POST /api/v1/schedules/day-off
     *
     * Đăng ký ngày nghỉ cho bác sĩ:
     *  1. Block các schedule tương ứng (status = 'blocked')
     *  2. Huỷ appointment bị ảnh hưởng
     *  3. Gửi email gợi ý lịch mới cho bệnh nhân (queue job)
     *
     * Request body: { doctor_id, type, date, end_date?, session, reason? }
     *
     * Response: { blocked_schedules, affected_appointments, emails_sent }
     */
    public function storeDayOff(StoreDayOffRequest $request): JsonResponse
    {
        $data = $request->validated();
        $doctorId = $data['doctor_id'];

        if (!$this->canAccessDoctor($doctorId)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền đăng ký nghỉ cho bác sĩ này.',
            ], 403);
        }

        $result = $this->dayOffService->process($data);

        $msg = "Đã block {$result['blocked_schedules']} ca khám.";
        if ($result['affected_appointments'] > 0) {
            $msg .= " Đã gửi email thông báo + gợi ý lịch mới cho {$result['emails_sent']} bệnh nhân.";
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data'    => $result,
        ], 201);
    }

    /**
     * GET /api/v1/schedules/day-off/{doctorId}
     *
     * Danh sách ngày nghỉ sắp tới của bác sĩ (work_date >= today, status = blocked).
     * Gom theo ngày để tiện hiển thị trên UI.
     */
    public function indexDayOff(int $doctorId): JsonResponse
    {
        if (!$this->canAccessDoctor($doctorId)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem ngày nghỉ của bác sĩ này.',
            ], 403);
        }

        // Kiểm tra bác sĩ tồn tại
        Doctor::findOrFail($doctorId);

        $list = $this->dayOffService->listDayOffs($doctorId);

        return response()->json([
            'success' => true,
            'data'    => $list,
            'total'   => $list->count(),
        ]);
    }

    /**
     * DELETE /api/v1/schedules/day-off/{scheduleId}
     *
     * Mở lại (cancel) ngày nghỉ — đặt schedule về status = 'active'.
     * Bệnh nhân sẽ nhận email thông báo bác sĩ đã trở lại nếu cần
     * (phần này để làm ở notification layer sau).
     */
    public function destroyDayOff(int $scheduleId): JsonResponse
    {
        $schedule = DoctorSchedule::findOrFail($scheduleId);

        if (!$this->canAccessDoctor($schedule->doctor_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền mở lại lịch của bác sĩ này.',
            ], 403);
        }

        $this->dayOffService->cancel($scheduleId);

        return response()->json([
            'success' => true,
            'message' => 'Đã mở lại lịch. Bệnh nhân có thể đặt lịch bình thường.',
        ]);
    }

    // =========================================================================
    // C. UTILITY
    // =========================================================================

    /**
     * GET /api/v1/schedules/doctors
     *
     * Danh sách bác sĩ để dropdown chọn trên UI.
     * Admin: tất cả bác sĩ
     * Doctor: chỉ mình
     */
    public function listDoctors(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem danh sách bác sĩ.',
            ], 403);
        }

        if ($user->isAdmin) {
            $doctors = Doctor::with('department')
                ->where('status', 1)
                ->orderBy('full_name')
                ->get(['doctor_id', 'full_name', 'department_id', 'avatar_url']);
        } elseif ($user->isDoctor && $user->doctor) {
            $doctors = collect([$user->doctor->load('department')]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem danh sách bác sĩ.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $doctors,
        ]);
    }
}