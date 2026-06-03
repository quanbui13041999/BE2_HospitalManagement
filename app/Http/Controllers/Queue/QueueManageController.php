<?php
namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Services\QueueService;
use App\Models\{DoctorSchedule, QueueTicket};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class QueueManageController extends Controller
{
    public function __construct(private QueueService $queueService)
    {
    }

    /**
     * Dashboard lễ tân: chọn ca khám để quản lý
     */
    public function index()
    {
        // Lấy các ca khám đang hoạt động hôm nay
        $schedules = DoctorSchedule::with(['doctor', 'doctor.department'])
            ->whereDate('work_date', today())
            ->where('status', 'Hoạt động')
            ->get()
            ->map(function ($s) {
                $s->queue_count = QueueTicket::forSchedule($s->schedule_id)->active()->count();
                return $s;
            });

        return view('queue.manage.index', compact('schedules'));
    }

    /**
     * Trang quản lý hàng đợi của 1 ca cụ thể
     */
    public function show(int $scheduleId)
    {
        $schedule  = DoctorSchedule::with(['doctor', 'doctor.department', 'room'])->findOrFail($scheduleId);
        $snapshot  = $this->queueService->getQueueSnapshot($scheduleId);

        // Lịch sử trong ngày (completed/skipped/cancelled)
        $history = QueueTicket::forSchedule($scheduleId)
            ->whereIn('status', ['completed', 'skipped', 'cancelled'])
            ->orderByDesc('completed_at')
            ->limit(20)
            ->get();

        return view('queue.manage.show', compact('schedule', 'snapshot', 'history'));
    }

    /**
     * Form tìm kiếm bệnh nhân để check-in
     */
    public function searchPatient(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'nullable|string|max:100',
        ]); /* fixed: gioi han input tim kiem, tranh query voi chuoi qua dai/du lieu tho */

        $keyword  = trim($validated['keyword'] ?? '');
        $result   = $keyword ? $this->queueService->findPatient($keyword) : ['found' => false];
        $schedules = DoctorSchedule::with('doctor')
            ->whereDate('work_date', today())
            ->where('status', 'Hoạt động')
            ->get();

        return view('queue.manage.checkin', compact('result', 'keyword', 'schedules'));
    }

    /**
     * Xử lý check-in bệnh nhân
     */
    public function checkin(Request $request)
    {
        $validated = $request->validate([
            'schedule_id'  => 'required|exists:doctorschedules,schedule_id',
            'patient_name' => 'required|string|max:100',
            'priority'     => 'required|in:normal,elderly,disabled,emergency',
            'patient_phone'=> 'nullable|string|max:15',
            'patient_email'=> 'nullable|email|max:100',
            'notes'        => 'nullable|string|max:255',
            'appointment_id' => 'nullable|exists:appointments,appointment_id',
            'user_id'      => 'nullable|exists:users,user_id',
        ]);

        try {
            $ticket = $this->queueService->checkin($validated); /* fixed: chi truyen input da validate, tranh mass assignment/input poisoning */
        } catch (\Throwable $e) {
            Log::error('Queue check-in failed', [
                'schedule_id' => $validated['schedule_id'] ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]); /* fixed: ghi log noi bo va khong lo loi that */

            return back()
                ->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'])
                ->withInput();
        }

        return redirect()
            ->route('queue.manage.show', $validated['schedule_id'])
            ->with('success', "Check-in thành công! Số thứ tự: #{$ticket->queue_number}");
    }

    /**
     * Bỏ qua / hủy 1 ticket
     */
    public function skip(Request $request, int $ticketId)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $this->queueService->skip($ticketId, $validated['reason'] ?? ''); /* fixed: validate ly do skip truoc khi luu */
        } catch (ValidationException $e) {
            return back()
                ->with('warning', $e->validator->errors()->first() ?: 'Hàng đợi đã thay đổi, trang sẽ được tải lại.')
                ->with('reload_page', true); /* fixed: thong bao nguoi thao tac sau va reload snapshot */
        } catch (\Throwable $e) {
            Log::error('Queue ticket skip failed', [
                'ticket_id' => $ticketId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.']);
        }

        return back()->with('success', 'Đã bỏ qua ticket.');
    }

    /**
     * API: Lấy snapshot realtime (polling fallback)
     */
    public function apiSnapshot(int $scheduleId)
    {
        $snapshot = $this->queueService->getQueueSnapshot($scheduleId);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'current' => $snapshot['current'],
            'waiting' => $snapshot['waiting'],
            'stats' => $snapshot['stats'],
            'data' => $snapshot,
        ]); /* fixed: JSON API co cau truc nhat quan */
    }
}
