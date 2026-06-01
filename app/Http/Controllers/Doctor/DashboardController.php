<?php
// app/Http/Controllers/Doctor/DashboardController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\Doctor\DoctorDashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __construct(private DoctorDashboardService $service)
    {
        // Chỉ cho phép user đã đăng nhập và có role doctor hoặc admin
        $this->middleware('role:1,2');
    }

    // ═══════════════════════════════════════════════════════════════
    //  PAGE RENDER
    // ═══════════════════════════════════════════════════════════════

    /**
     * Hiển thị trang dashboard
     */
    public function index(): \Illuminate\View\View
    {
        $user    = Auth::user();
        $isAdmin = $user->isAdmin();

        // Bác sĩ: lấy doctor record của chính mình
        $currentDoctor = $isAdmin ? null : $user->doctor;

        if (!$isAdmin && !$currentDoctor) {
            abort(403, 'Tài khoản này chưa được liên kết với hồ sơ bác sĩ.');
        }

        $doctorId = $currentDoctor?->doctor_id;

        // Admin thấy danh sách tất cả bác sĩ để lọc
        $doctors = $isAdmin ? $this->service->getDoctorsList() : collect([$currentDoctor]);

        $stats = $this->service->getStats($doctorId ?? 0, $isAdmin);

        $departments = \App\Models\Department::select('department_id', 'department_name as name')->orderBy('department_name')->get();

        return view('doctor.dashboard', compact('isAdmin', 'currentDoctor', 'doctors', 'stats', 'departments'));
    }

    // ═══════════════════════════════════════════════════════════════
    //  AJAX – APPOINTMENTS
    // ═══════════════════════════════════════════════════════════════

    /**
     * GET /doctor/dashboard/appointments/today
     * Query param: doctor_id (admin only)
     */
    public function todayAppointments(Request $request): JsonResponse
    {
        try {
            [$doctorId, $isAdmin] = $this->resolveContext($request);

            $appointments = $this->service->getTodayAppointments($doctorId, $isAdmin, $request->integer('doctor_id') ?: null);

            return response()->json([
                'success' => true,
                'data'    => $appointments->map(fn($a) => $this->formatAppointment($a)),
            ]);
        } catch (\Exception $e) {
            \Log::error('todayAppointments error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /doctor/dashboard/appointments/upcoming
     */
    public function upcomingAppointments(Request $request): JsonResponse
    {
        [$doctorId, $isAdmin] = $this->resolveContext($request);

        $appointments = $this->service->getUpcomingAppointments($doctorId, $isAdmin, $request->integer('doctor_id') ?: null);

        return response()->json([
            'success' => true,
            'data'    => $appointments->map(fn($a) => $this->formatAppointment($a)),
        ]);
    }

    /**
     * GET /doctor/dashboard/stats
     */
    public function stats(Request $request): JsonResponse
    {
        [$doctorId, $isAdmin] = $this->resolveContext($request);

        $stats = $this->service->getStats(
            $doctorId,
            $isAdmin,
            $request->integer('doctor_id') ?: null
        );

        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * PATCH /doctor/dashboard/appointments/{id}/complete
     */
    public function completeAppointment(int $id): JsonResponse
    {
        [$doctorId, $isAdmin] = $this->resolveContext();

        $result = $this->service->completeAppointment($id, $doctorId, $isAdmin);

        return response()->json($result, $result['success'] ? 200 : 403);
    }

    /**
     * PATCH /doctor/dashboard/appointments/{id}/cancel
     */
    public function cancelAppointment(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        [$doctorId, $isAdmin] = $this->resolveContext();

        $result = $this->service->cancelAppointment(
            $id,
            $request->input('reason', ''),
            $doctorId,
            $isAdmin
        );

        return response()->json($result, $result['success'] ? 200 : 403);
    }

    // ═══════════════════════════════════════════════════════════════
    //  AJAX – REVIEWS
    // ═══════════════════════════════════════════════════════════════

    /**
     * GET /doctor/dashboard/reviews
     */
    public function reviews(Request $request): JsonResponse
    {
        [$doctorId, $isAdmin] = $this->resolveContext($request);

        $paginated = $this->service->getReviews(
            $doctorId,
            $isAdmin,
            $request->integer('doctor_id') ?: null,
            $request->integer('per_page', 10)
        );

        return response()->json([
            'success' => true,
            'data'    => $paginated->map(fn($r) => $this->formatReview($r)),
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    /**
     * POST /doctor/dashboard/reviews/{id}/reply
     */
    public function replyReview(Request $request, int $id): JsonResponse
    {
        $request->validate(['reply' => 'required|string|max:1000']);

        [$doctorId, $isAdmin] = $this->resolveContext();

        $result = $this->service->replyToReview(
            $id,
            $request->input('reply'),
            $doctorId,
            $isAdmin
        );

        return response()->json($result, $result['success'] ? 200 : 403);
    }

    /**
     * DELETE /doctor/dashboard/reviews/{id}/reply   (admin only)
     */
    public function deleteReply(int $id): JsonResponse
    {
        [, $isAdmin] = $this->resolveContext();

        $result = $this->service->deleteReply($id, $isAdmin);

        return response()->json($result, $result['success'] ? 200 : 403);
    }

    // ═══════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Trả về [doctorId, isAdmin] từ user đang đăng nhập
     */
    private function resolveContext(?Request $request = null): array
    {
        $user    = Auth::user();
        $isAdmin = $user->isAdmin();
        $doctorId = $isAdmin ? 0 : ($user->doctor?->doctor_id ?? 0);

        return [$doctorId, $isAdmin];
    }

    private function formatAppointment($a): array
    {
        return [
            'id'              => $a->appointment_id,
            'patient_name'    => $a->patient_name,
            'patient_phone'   => $a->patient_phone,
            'service_name'    => $a->service_name ?? 'Khám tổng quát',
            'doctor_name'     => $a->doctor_name,
            'appointment_time'=> $a->appointment_time?->format('Y-m-d H:i'),
            'queue_number'    => $a->queue_number,
            'status'          => $a->status,
            'note'            => $a->note,
            'slot_duration'   => $a->slot_duration ?? 30,
        ];
    }

    private function formatReview($r): array
    {
        return [
            'id'                      => $r->review_id,
            'appointment_id'          => $r->appointment_id,
            'patient_name'            => $r->user?->full_name ?? 'Ẩn danh',
            'patient_avatar'          => $r->user?->avatar_url,
            'doctor_name'             => $r->doctor?->full_name,
            'rating'                  => $r->rating,
            'comment'                 => $r->comment,
            'doctor_reply'            => $r->doctor_reply,
            'doctor_reply_updated_at' => $r->doctor_reply_updated_at?->format('Y-m-d H:i'),
            'created_at'              => $r->created_at?->format('Y-m-d H:i'),
            'can_edit_reply'          => true, // frontend tự check theo role
        ];
    }

// ════════════════════════════════════════════════════════════════
//  DOCTORS CRUD  (admin only)
// ════════════════════════════════════════════════════════════════

/**
 * GET /doctor/dashboard/doctors/list
 */
public function doctorsList(Request $request): JsonResponse
{
    if (!Auth::user()->isAdmin()) {
        return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
    }

    $perPage = $request->integer('per_page', 10);
    $search  = $request->input('search', '');
    $status  = $request->input('status', '');

    $query = \App\Models\Doctor::with('department:department_id,department_name')
        ->withReviewStats()
        ->when($search, fn($q) => $q->where('doctors.full_name', 'like', "%{$search}%")
            ->orWhereHas('department', fn($dq) => $dq->where('department_name', 'like', "%{$search}%")))
        ->when($status !== '', fn($q) => $q->where('doctors.status', $status))
        ->orderBy('doctors.full_name');

    $paginated = $query->paginate($perPage);

    return response()->json([
        'success' => true,
        'data'    => $paginated->map(fn($d) => [
            'doctor_id'       => $d->doctor_id,
            'user_id'         => $d->user_id,
            'full_name'       => $d->full_name,
            'department_id'   => $d->department_id,
            'department_name' => $d->department?->department_name,
            'experience'      => $d->experience,
            'price'           => $d->price,
            'avatar_url'      => $d->avatar_url,
            'bio'             => $d->bio,
            'status'          => $d->status,
            'avg_rating'      => $d->avg_rating,
            'total_reviews'   => $d->total_reviews,
        ]),
        'meta' => [
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'total'        => $paginated->total(),
        ],
    ]);
}

/**
 * POST /doctor/dashboard/doctors
 */
public function storeDoctor(Request $request): JsonResponse
{
    if (!Auth::user()->isAdmin()) {
        return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
    }

    $validated = $request->validate([
        'full_name'     => 'required|string|max:100',
        'user_id'       => 'nullable|integer|exists:users,user_id',
        'email'         => 'nullable|email|unique:users,email',
        'password'      => 'nullable|string|min:6',
        'department_id' => 'required|integer|exists:departments,department_id',
        'experience'    => 'nullable|integer|min:0|max:60',
        'price'         => 'nullable|numeric|min:0',
        'avatar_url'    => 'nullable|string|max:255',
        'bio'           => 'nullable|string|max:2000',
        'status'        => 'required|in:0,1',
    ]);

    // If admin didn't provide user_id, create a user account automatically for this doctor
    $plainPassword = null;
    if (empty($validated['user_id'])) {
        // If admin supplied an email, create user with that email (and provided password or generated one)
        if (!empty($validated['email'])) {
            $plainPassword = $validated['password'] ?? bin2hex(random_bytes(4));
            $user = \App\Models\User::create([
                'full_name' => $validated['full_name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($plainPassword),
                'role_id'   => 2,
                'status'    => 1,
            ]);
            $validated['user_id'] = $user->user_id;
        } else {
            // fallback: generate a placeholder user
            $timestamp = time();
            $rand = substr(md5(uniqid('', true)), 0, 6);
            $email = "doctor+{$timestamp}{$rand}@example.local";
            $plainPassword = bin2hex(random_bytes(4));
            $user = \App\Models\User::create([
                'full_name' => $validated['full_name'],
                'email'     => $email,
                'password'  => Hash::make($plainPassword),
                'role_id'   => 2,
                'status'    => 1,
            ]);
            $validated['user_id'] = $user->user_id;
        }
    }

    // ensure user_id is unique in doctors table
    if (\App\Models\Doctor::where('user_id', $validated['user_id'])->exists()) {
        return response()->json(['success' => false, 'message' => 'Tài khoản người dùng đã được liên kết với bác sĩ khác.'], 422);
    }

    $doctor = \App\Models\Doctor::create($validated);
    $doctor->load('department:department_id,department_name');

    return response()->json([
        'success' => true,
        'message' => 'Đã thêm bác sĩ thành công.',
        'doctor'  => ['doctor_id' => $doctor->doctor_id, 'full_name' => $doctor->full_name],
        'created_user' => isset($user) ? ['user_id' => $user->user_id, 'email' => $user->email, 'plain_password' => $plainPassword] : null,
    ], 201);
}

/**
 * PUT /doctor/dashboard/doctors/{id}
 */
public function updateDoctor(Request $request, int $id): JsonResponse
{
    if (!Auth::user()->isAdmin()) {
        return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
    }

    $doctor = \App\Models\Doctor::find($id);
    if (!$doctor) {
        return response()->json(['success' => false, 'message' => 'Bác sĩ không tồn tại.'], 404);
    }

    $validated = $request->validate([
        'full_name'     => 'required|string|max:100',
        'user_id'       => [
            'required','integer','exists:users,user_id',
            Rule::unique('doctors','user_id')->ignore($doctor->doctor_id, 'doctor_id'),
        ],
        'department_id' => 'required|integer|exists:departments,department_id',
        'experience'    => 'nullable|integer|min:0|max:60',
        'price'         => 'nullable|numeric|min:0',
        'avatar_url'    => 'nullable|string|max:255',
        'bio'           => 'nullable|string|max:2000',
        'status'        => 'required|in:0,1',
    ]);

    DB::transaction(function () use ($doctor, $validated) {
        $doctor->update($validated);

        // Sync user data with doctor data
        if ($doctor->user_id) {
            $user = \App\Models\User::find($doctor->user_id);
            if ($user) {
                $user->update([
                    'full_name'  => $validated['full_name'],
                    'avatar_url' => $validated['avatar_url'] ?? $user->avatar_url,
                    'status'     => $validated['status'],
                ]);
            }
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Đã cập nhật thông tin bác sĩ.',
        'doctor'  => ['doctor_id' => $doctor->doctor_id, 'full_name' => $doctor->full_name],
    ]);
}

/**
 * DELETE /doctor/dashboard/doctors/{id}
 */
public function destroyDoctor(int $id): JsonResponse
{
    if (!Auth::user()->isAdmin()) {
        return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
    }

    $doctor = \App\Models\Doctor::find($id);
    if (!$doctor) {
        return response()->json(['success' => false, 'message' => 'Bác sĩ không tồn tại.'], 404);
    }

    // Kiểm tra còn lịch hẹn active không
    $hasActive = Appointment::whereHas('schedule', fn($q) => $q->where('doctor_id', $id))
        ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám','Đã thanh toán'])
        ->exists();

    if ($hasActive) {
        return response()->json([
            'success' => false,
            'message' => 'Không thể xóa: bác sĩ còn lịch hẹn chưa hoàn thành.',
        ], 422);
    }

    $doctor->update(['status' => 0]); // soft-disable trước khi xóa nếu cần
    $doctor->delete();

    return response()->json(['success' => true, 'message' => 'Đã xóa bác sĩ thành công.']);
}

    /**
     * POST /doctor/dashboard/upload-avatar
     * Upload avatar image for doctor (admin only)
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'avatar' => 'required|image|max:5120', // max 5MB
        ]);

        $file = $request->file('avatar');
        $path = $file->store('images/doctors', 'public');

        return response()->json(['success' => true, 'message' => 'Uploaded', 'path' => $path, 'url' => '/storage/' . $path]);
    }

}

