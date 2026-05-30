<?php

namespace App\Http\Controllers;

use App\Services\AppointmentService;
use App\Services\Doctor\DoctorSuggestionService;
use App\Services\Doctor\DoctorTimeslotService;
use App\Services\Doctor\AppointmentQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AppointmentController
 * 
 * Xử lý HTTP requests và delegating business logic tới AppointmentService
 * Tuân theo MVC pattern: Controller chỉ xử lý HTTP, Service xử lý logic
 */
class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
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
            'appointment_time' => 'required|string|max:10',
            'note' => 'nullable|string|max:255',
            'is_priority' => 'nullable',
            'priority_type' => 'nullable|string|max:255',
        ], [
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
        } catch (\Exception $e) {
            return back()
                ->withErrors(['msg' => $e->getMessage()])
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
        $status = $request->input('status', 'all');
        $sort = $request->input('sort', 'desc');

        $counts = $this->appointmentService->getUserAppointmentStats($userId);
        $appointments = $this->appointmentService->getUserAppointments($userId, $status, $sort);

        return view('appointments.index', compact('appointments', 'counts', 'status'));
    }

    // ================================================================
    // 3. FORM DỜI LỊCH — GET /lich-hen/{id}/doi
    // ================================================================
    public function edit($id)
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
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => $e->getMessage()]);
        }

        return view('appointments.edit', compact('appointment', 'availableSchedules'));
    }

    public function doctorOff($id)
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
    public function update(Request $request, $id)
    {
        if ($redirect = $this->redirectIfNotPatientAppointmentFlow()) {
            return $redirect;
        }

        $request->validate([
            'new_schedule_id' => 'required|integer|exists:doctorschedules,schedule_id',
            'new_appointment_time' => 'required|string|max:10',
            'reschedule_reason' => 'nullable|string|max:255',
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
                    'ip_address' => $request->ip(),
                ]
            );

            return redirect()->route('appointments.index')
                ->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['msg' => $e->getMessage()])
                ->withInput();
        }
    }

    // ================================================================
    // 4. HỦY LỊCH HẸN — POST /lich-hen/{id}/huy
    // ================================================================
    public function cancel(Request $request, $id)
    {
        if ($redirect = $this->redirectIfNotPatientAppointmentFlow()) {
            return $redirect;
        }

        $request->validate([
            'cancel_reason' => 'nullable|string|max:255',
        ]);

        try {
            $result = $this->appointmentService->cancelAppointment(
                $id,
                Auth::id(),
                [
                    'cancel_reason' => $request->cancel_reason,
                    'ip_address' => $request->ip(),
                ]
            );

            return redirect()->route('appointments.index')
                ->with('success', $result['message']);
        } catch (\Exception $e) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => $e->getMessage()]);
        }
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
            'appointment_time' => 'nullable|string',
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

    private function redirectIfNotPatientAppointmentFlow()
    {
        $user = Auth::user();

        if (!$user || $user->isPatient() || $user->isAdmin()) {
            return null;
        }

        if ($user->isDoctor()) {
            return redirect()->route('doctor.dashboard')
                ->with('error', 'Tài khoản bác sĩ không dùng chức năng đặt lịch của bệnh nhân.');
        }

        abort(403);
    }

    private function jsonIfNotPatientAppointmentFlow()
    {
        $user = Auth::user();

        if (!$user || $user->isPatient() || $user->isAdmin()) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Tài khoản bác sĩ không dùng chức năng đặt lịch của bệnh nhân.',
        ], 403);
    }
}
