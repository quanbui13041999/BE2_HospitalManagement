<?php

namespace App\Http\Controllers;

use App\Exceptions\ConcurrentModificationException;
use App\Services\AppointmentService;
use App\Services\AppointmentReminderService;
use App\Services\Doctor\DoctorSuggestionService;
use App\Services\Doctor\DoctorTimeslotService;
use App\Services\Doctor\AppointmentQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * AppointmentController
 * 
 * Xử lý HTTP requests và delegating business logic tới AppointmentService
 * Tuân theo MVC pattern: Controller chỉ xử lý HTTP, Service xử lý logic
 */
class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;
    protected AppointmentReminderService $appointmentReminderService;

    public function __construct(AppointmentService $appointmentService, AppointmentReminderService $appointmentReminderService)
    {
        $this->appointmentService = $appointmentService;
        $this->appointmentReminderService = $appointmentReminderService;
    }

    // ================================================================
    // 1. FORM ĐẶT LỊCH — GET /dat-lich
    // ================================================================
    public function create()
    {
        if ($redirect = $this->redirectIfNotPatientAppointmentFlow()) {
            return $redirect;
        }

        $data = $this->appointmentService->getCreateFormData();
        return view('appointments.create', $data);
    }

    // ================================================================
    // 1b. AJAX SCHEDULES — GET /api/schedules
    // ================================================================
    public function getSchedules(Request $request)
    {
        if ($response = $this->jsonIfNotPatientAppointmentFlow()) {
            return $response;
        }

        $request->validate([
            'doctor_id' => 'required|integer|exists:doctors,doctor_id',
            'work_date' => 'required|date|after_or_equal:today',
        ]);

        $schedules = $this->appointmentService->getSchedulesForDoctor(
            $request->doctor_id,
            $request->work_date
        );

        return response()->json(['schedules' => $schedules]);
    }

    // ================================================================
    // 1c. LƯU ĐẶT LỊCH — POST /dat-lich
    // ================================================================
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để đặt lịch hẹn.');
        }

        if ($redirect = $this->redirectIfNotPatientAppointmentFlow()) {
            return $redirect;
        }

        $request->validate([
            'schedule_id' => 'required|integer|exists:doctorschedules,schedule_id',
            'service_id' => 'nullable|integer|exists:services,service_id',
            'work_date' => 'required|date|after_or_equal:today',
            'appointment_time' => ['required', 'string', 'max:10', 'regex:/\A\d{2}:\d{2}\z/'],
            'visit_type' => ['nullable', Rule::in(['Khám trực tiếp', 'Khám online'])],
            'note' => ['nullable', 'string', 'max:255', 'not_regex:/\A[\s\x{3000}]*\z/u'],
            'is_priority' => 'nullable',
            'priority_type' => ['nullable', 'string', 'max:255', Rule::in(['Trẻ em dưới 6 tuổi', 'Người già trên 80 tuổi', 'Phụ nữ có thai', 'Người khuyết tật', 'Cấp cứu'])],
        ], [ /* fixed: validate select visit_type, khong nhan option gia tu DevTools */
            'schedule_id.required' => 'Vui lòng chọn khung giờ khám.',
            'schedule_id.exists' => 'Khung giờ không hợp lệ.',
            'work_date.after_or_equal' => 'Ngày khám phải từ hôm nay trở đi.',
            'appointment_time.required' => 'Vui lòng chọn giờ khám.',
        ]);

        try {
            $result = $this->appointmentService->createAppointment(
                Auth::id(),
                [
                    'schedule_id' => $request->schedule_id,
                    'service_id' => $request->service_id,
                    'work_date' => $request->work_date,
                    'appointment_time' => $request->appointment_time,
                    'note' => $request->note,
                    'is_priority' => $request->has('is_priority') ? true : false,
                    'priority_type' => $request->priority_type,
                    'ip_address' => $request->ip(),
                ]
            );

            return redirect()->route('appointments.index')
                ->with('success', $result['message'])
                ->with('appointment_id', $result['appointment_id']);
        } catch (ConcurrentModificationException $e) {
            return back()
                ->with('warning', $e->getMessage())
                ->with('reload_page', true)
                ->withInput(); /* fixed: slot vua thay doi do nguoi khac dat truoc thi bao va reload */
        } catch (\Exception $e) {
            Log::error('Create appointment failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]); /* fixed: log loi noi bo, khong tra stack/message that ra user */

            return back()
                ->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'])
                ->withInput();
        }
    }

    // ================================================================
    // 2. DANH SÁCH LỊCH HẸN — GET /lich-hen
    // ================================================================
    public function index(Request $request)
    {
        if ($redirect = $this->redirectIfNotPatientAppointmentFlow()) {
            return $redirect;
        }

        $userId = Auth::id();
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['all', 'upcoming', 'completed', 'cancelled'])],
            'sort' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => 'nullable|integer|min:1|max:1000',
        ]); /* fixed: chan URL page/status/sort bi sua thanh gia tri khong hop le */

        $status = $validated['status'] ?? 'all';
        $sort = $validated['sort'] ?? 'desc';

        $counts = $this->appointmentService->getUserAppointmentStats($userId);
        $appointments = $this->appointmentService->getUserAppointments($userId, $status, $sort);

        return view('appointments.index', compact('appointments', 'counts', 'status'));
    }

    // ================================================================
    // 3. FORM DỜI LỊCH — GET /lich-hen/{id}/doi
    // ================================================================
    public function edit(int $id)
    {
        if ($redirect = $this->redirectIfNotPatientAppointmentFlow()) {
            return $redirect;
        }

        $userId = Auth::id();
        
        $appointment = $this->appointmentService->getAppointmentForEdit($id, $userId);
        if (!$appointment) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Không tìm thấy lịch hẹn.']);
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận', 'Bác sĩ nghỉ'])) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Lịch hẹn này không thể dời (trạng thái: ' . $appointment->status . ').']);
        }

        // Check time availability
        try {
            $availableSchedules = $this->appointmentService->getAvailableSchedulesForReschedule($id, $appointment->doctor_id);
        } catch (\Exception $e) {
            Log::error('Load reschedule options failed', [
                'appointment_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]); /* fixed: an loi he thong khoi response */

            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.']);
        }

        return view('appointments.edit', compact('appointment', 'availableSchedules'));
    }

    public function doctorOff(int $id)
    {
        if ($redirect = $this->redirectIfNotPatientAppointmentFlow()) {
            return $redirect;
        }

        $userId = Auth::id();

        $appointment = $this->appointmentService->getAppointmentForEdit($id, $userId);
        if (!$appointment) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Không tìm thấy lịch hẹn.']);
        }

        if ($appointment->status !== 'Bác sĩ nghỉ') {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Lịch hẹn này không bị ảnh hưởng bởi bác sĩ nghỉ.']);
        }

        return view('appointments.doctor-off', compact('appointment'));
    }

    // ================================================================
    // 3b. XỬ LÝ DỜI LỊCH — PUT /lich-hen/{id}/doi
    // ================================================================
    public function update(Request $request, int $id)
    {
        if ($redirect = $this->redirectIfNotPatientAppointmentFlow()) {
            return $redirect;
        }

        $request->validate([
            'new_schedule_id' => 'required|integer|exists:doctorschedules,schedule_id',
            'new_appointment_time' => ['required', 'string', 'max:10', 'regex:/\A\d{2}:\d{2}\z/'],
            'reschedule_reason' => ['nullable', 'string', 'max:255', 'not_regex:/\A[\s\x{3000}]*\z/u'],
            'version' => 'required|string|size:40',
        ], [
            'new_schedule_id.required' => 'Vui lòng chọn khung giờ mới.',
            'new_schedule_id.exists' => 'Khung giờ không hợp lệ.',
        ]);

        try {
            $result = $this->appointmentService->rescheduleAppointment(
                $id,
                Auth::id(),
                [
                    'new_schedule_id' => $request->new_schedule_id,
                    'new_appointment_time' => $request->new_appointment_time,
                    'reschedule_reason' => $request->reschedule_reason,
                    'version' => $request->version,
                    'ip_address' => $request->ip(),
                ]
            );

            return redirect()->route('appointments.index')
                ->with('success', $result['message']);
        } catch (ConcurrentModificationException $e) {
            return redirect()->route('appointments.index')
                ->with('warning', $e->getMessage())
                ->with('reload_page', true); /* fixed: bao nguoi submit sau va tai lai danh sach */
        } catch (\Exception $e) {
            Log::error('Reschedule appointment failed', [
                'appointment_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]); /* fixed: log loi noi bo, tra thong bao chung */

            return back()
                ->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'])
                ->withInput();
        }
    }

    // ================================================================
    // 4. HỦY LỊCH HẸN — POST /lich-hen/{id}/huy
    // ================================================================
    public function cancel(Request $request, int $id)
    {
        if ($redirect = $this->redirectIfNotPatientAppointmentFlow()) {
            return $redirect;
        }

        $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:255', 'not_regex:/\A[\s\x{3000}]*\z/u'],
            'version' => 'required|string|size:40',
        ]);

        try {
            $result = $this->appointmentService->cancelAppointment(
                $id,
                Auth::id(),
                [
                    'cancel_reason' => $request->cancel_reason,
                    'version' => $request->version,
                    'ip_address' => $request->ip(),
                ]
            );

            return redirect()->route('appointments.index')
                ->with('success', $result['message']);
        } catch (ConcurrentModificationException $e) {
            return redirect()->route('appointments.index')
                ->with('warning', $e->getMessage())
                ->with('reload_page', true); /* fixed: neu lich da bi doi/huy truoc do thi reload */
        } catch (\Exception $e) {
            Log::error('Cancel appointment failed', [
                'appointment_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]); /* fixed: khong lo ly do loi noi bo qua UI */

            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.']);
        }
    }

    public function sendEmailReminders()
    {
        $stats = $this->appointmentReminderService->sendPendingReminders();

        return response()->json([
            'success' => true,
            'message' => 'Appointment reminder job executed.',
            'data' => $stats,
        ]);
    }

    /**
     * Gợi ý bác sĩ tự động
     * 
     * Thuật toán scoring (100):
     * - 40% tỉ lệ slot còn trống
     * - 35% đánh giá trung bình
     * - 15% năm kinh nghiệm
     * - 10% số lượt đánh giá
     */
    public function suggest(Request $request, DoctorSuggestionService $suggestionService)
    {
        if ($response = $this->jsonIfNotPatientAppointmentFlow()) {
            return $response;
        }

        $request->validate([
            'department_id' => 'required|integer|exists:departments,department_id',
            'work_date' => 'required|date|after_or_equal:today',
        ]);

        $suggested = $suggestionService->suggestTopDoctors(
            (int) $request->department_id,
            $request->work_date
        );

        return response()->json(['suggested' => $suggested]);
    }

    /**
     * Lấy khung giờ khám của bác sĩ
     */
    public function timeslots(Request $request, DoctorTimeslotService $timeslotService)
    {
        if ($response = $this->jsonIfNotPatientAppointmentFlow()) {
            return $response;
        }

        $request->validate([
            'doctor_id' => 'required|integer|exists:doctors,doctor_id',
            'work_date' => 'required|date|after_or_equal:today',
        ]);

        $result = $timeslotService->getTimeslots(
            (int) $request->doctor_id,
            $request->work_date
        );

        return response()->json($result);
    }

    // ================================================================
    // 5. ƯỚC LƯỢNG THỜI GIAN CHỜ — GET /api/appointments/queue-info
    // ================================================================
    /**
     * Lấy thông tin hàng đợi và ước lượng thời gian chờ
     * 
     * Response:
     * {
     *   "success": true,
     *   "queue_number": 3,
     *   "people_ahead": 2,
     *   "estimated_wait_minutes": 30,
     *   "schedule_info": { ... },
     *   "queue_details": [ ... ]
     * }
     */
    public function getQueueInfo(Request $request, AppointmentQueueService $queueService)
    {
        if ($response = $this->jsonIfNotPatientAppointmentFlow()) {
            return $response;
        }

        $request->validate([
            'schedule_id' => 'required|integer|exists:doctorschedules,schedule_id',
            'appointment_time' => ['nullable', 'string', 'max:10', 'regex:/\A\d{2}:\d{2}\z/'],
            'appointment_id' => 'nullable|integer|exists:appointments,appointment_id',
        ]);

        $queueInfo = $queueService->getQueueInfo(
            (int) $request->schedule_id,
            $request->appointment_time,
            $request->appointment_id ? (int) $request->appointment_id : null
        );

        if (!$queueInfo['success']) {
            return response()->json($queueInfo, 404);
        }

        return response()->json($queueInfo);
    }

    // ================================================================
    // QUICK RESCHEDULE FROM DAY-OFF
    // ================================================================
    /**
     * GET /dat-lich/xac-nhan-doi-lich?old_id=X&new_schedule_id=Y&token=...
     * 
     * Xác nhận dời lịch từ email notification.
     * User click nút "Xác nhận chọn lịch này" trong email → redirect tới endpoint này
     * → endpoint xác thực token → tự động tạo appointment mới → redirect tới trang xác nhận
     */
    public function confirmRescheduleFromEmail(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để dời lịch');
        }

        $oldAppointmentId = $request->integer('old_id');
        $newScheduleId = $request->integer('new_schedule_id');
        $token = (string) $request->string('token');

        // Xác thực token
        $expectedToken = hash_hmac('sha256', $oldAppointmentId . '|' . $newScheduleId, config('app.key'));
        if (!hash_equals($token, $expectedToken)) {
            return redirect()->route('appointments.index')->with('error', 'Link không hợp lệ hoặc đã hết hạn');
        }

        try {
            $result = $this->appointmentService->quickRescheduleFromDayOff(
                $oldAppointmentId,
                $newScheduleId,
                $user->user_id
            );

            return redirect()->route('appointments.index')->with('success', $result['message']);

        } catch (\Exception $e) {
            return redirect()->route('appointments.index')->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * POST /api/v1/appointments/reschedule-confirm
     * 
     * API endpoint cho quick reschedule (backup nếu email không thể submit form).
     * Yêu cầu authentication.
     */
    public function quickRescheduleFromDayOff(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để dời lịch.',
            ], 401);
        }

        $request->validate([
            'old_appointment_id' => 'required|integer|exists:appointments,appointment_id',
            'new_schedule_id'    => 'required|integer|exists:doctorschedules,schedule_id',
        ]);

        try {
            $result = $this->appointmentService->quickRescheduleFromDayOff(
                $request->integer('old_appointment_id'),
                $request->integer('new_schedule_id'),
                $user->user_id
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data'    => $result,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function redirectIfNotPatientAppointmentFlow()
    {
        $user = Auth::user();

        if (!$user || in_array((int) $user->role_id, [1, 3], true)) {
            return null;
        }

        if ((int) $user->role_id === 2) {
            return redirect()->route('doctor.dashboard')
                ->with('error', 'Tài khoản bác sĩ không dùng chức năng đặt lịch của bệnh nhân.');
        }

        abort(403);
    }

    private function jsonIfNotPatientAppointmentFlow()
    {
        $user = Auth::user();

        if (!$user || in_array((int) $user->role_id, [1, 3], true)) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Tài khoản bác sĩ không dùng chức năng đặt lịch của bệnh nhân.',
        ], 403);
    }
}
