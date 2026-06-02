<?php
// app/Http/Controllers/Doctor/DashboardController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\DoctorDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private DoctorDashboardService $service)
    {
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

        $currentDoctor = $isAdmin ? null : $user->doctor;

        if (!$isAdmin && !$currentDoctor) {
            abort(403, 'Tài khoản này chưa được liên kết với hồ sơ bác sĩ.');
        }

        $doctorId = $currentDoctor?->doctor_id;

        $doctors = $isAdmin ? $this->service->getDoctorsList() : collect([$currentDoctor]);
        $stats   = $this->service->getStats($doctorId ?? 0, $isAdmin);

        $departments = \App\Models\Department::select('department_id', 'department_name as name')
            ->orderBy('department_name')
            ->get();

        return view('doctor.dashboard', compact('isAdmin', 'currentDoctor', 'doctors', 'stats', 'departments'));
    }

    // ═══════════════════════════════════════════════════════════════
    //  AJAX – APPOINTMENTS
    // ═══════════════════════════════════════════════════════════════

    /**
     * GET /doctor/dashboard/appointments/today
     */
    public function todayAppointments(Request $request): JsonResponse
    {
        try {
            [$doctorId, $isAdmin] = $this->resolveContext($request);

            $appointments = $this->service->getTodayAppointments(
                $doctorId,
                $isAdmin,
                $request->integer('doctor_id') ?: null
            );

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

        $appointments = $this->service->getUpcomingAppointments(
            $doctorId,
            $isAdmin,
            $request->integer('doctor_id') ?: null
        );

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
            'id'               => $a->appointment_id,
            'patient_name'     => $a->patient_name,
            'patient_phone'    => $a->patient_phone,
            'service_name'     => $a->service_name ?? 'Khám tổng quát',
            'doctor_name'      => $a->doctor_name,
            'appointment_time' => $a->appointment_time?->format('Y-m-d H:i'),
            'queue_number'     => $a->queue_number,
            'status'           => $a->status,
            'note'             => $a->note,
            'slot_duration'    => $a->slot_duration ?? 30,
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
            'can_edit_reply'          => true,
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    //  DOCTORS CRUD  (admin only)
    // ═══════════════════════════════════════════════════════════════

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

        $query = Doctor::with('department:department_id,department_name')
            ->withReviewStats()
            ->when($search, fn($q) => $q
                ->where('doctors.full_name', 'like', "%{$search}%")
                ->orWhereHas('department', fn($dq) => $dq->where('department_name', 'like', "%{$search}%"))
            )
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
     * GET /doctor/dashboard/doctors/{id}
     */
    public function getDoctor(int $id): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        $doctor = Doctor::with('department:department_id,department_name')->find($id);
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Bác sĩ không tồn tại.'], 404);
        }

        return response()->json([
            'success' => true,
            'doctor'  => [
                'doctor_id'     => $doctor->doctor_id,
                'user_id'       => $doctor->user_id,
                'full_name'     => $doctor->full_name,
                'department_id' => $doctor->department_id,
                'experience'    => $doctor->experience,
                'price'         => $doctor->price,
                'avatar_url'    => $doctor->avatar_url,
                'bio'           => $doctor->bio,
                'status'        => $doctor->status,
                'version'       => $doctor->version ?? 1,
            ],
        ]);
    }

    // POST /doctor/dashboard/doctors (storeDoctor) might exist in your project.
    // In case it is already implemented elsewhere, leave it out here.

    /**
     * PUT /doctor/dashboard/doctors/{id}
     */
    public function updateDoctor(Request $request, int $id): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Bác sĩ không tồn tại.'], 404);
        }

        $validated = $request->validate([
            'full_name'     => 'required|string|max:100',
            'user_id'       => [
                'nullable',
                'integer',
                'exists:users,user_id',
                Rule::unique('doctors', 'user_id')->ignore($doctor->doctor_id, 'doctor_id'),
            ],
            'email'         => 'nullable|email|unique:users,email,' . ($doctor->user_id ?? 'NULL') . ',user_id',
            'password'      => 'nullable|string|min:6',
            'department_id' => 'required|integer|exists:departments,department_id',
            'experience'    => 'nullable|integer|min:0|max:60',
            'price'         => 'nullable|numeric|min:0',
            'avatar_url'    => 'nullable|string|max:255',
            'bio'           => 'nullable|string|max:2000',
            'status'        => 'required|in:0,1',
            'version'       => 'required|integer|min:1',
        ]);

        $oldVersion = $doctor->version ?? 1;
        if ($oldVersion !== $validated['version']) {
            return response()->json([
                'success' => false,
                'message' => 'Bản ghi đã bị thay đổi bởi người khác. Vui lòng tải lại và thử lại.',
            ], 409);
        }

        DB::transaction(function () use ($doctor, $validated, $oldVersion) {
            // optimistic locking update (atomic): only update if version matches oldVersion
            $data = $validated;
            unset($data['version']);
            $newVersion = $oldVersion + 1;

            $updated = Doctor::where('doctor_id', $doctor->doctor_id)
                ->where('version', $oldVersion)
                ->update(array_merge($data, ['version' => $newVersion]));

            if ($updated === 0) {
                throw new \RuntimeException('CONFLICT');
            }

            // Sync user (best-effort; concurrency conflicts here are less likely)
            if (!empty($validated['user_id'])) {
                $user = \App\Models\User::find($validated['user_id']);
                if ($user) {
                    $user->update([
                        'full_name'  => $validated['full_name'],
                        'avatar_url' => $validated['avatar_url'] ?? $user->avatar_url,
                        'status'     => $validated['status'],
                    ] + (!empty($validated['email']) ? ['email' => $validated['email']] : [])
                      + (!empty($validated['password']) ? ['password' => Hash::make($validated['password'])] : []));
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
    public function destroyDoctor(Request $request, int $id): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'version' => 'required|integer|min:1',
        ]);

        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Bác sĩ không tồn tại.'], 404);
        }

        $oldVersion = $doctor->version ?? 1;
        if ($oldVersion !== (int)$validated['version']) {
            return response()->json([
                'success' => false,
                'message' => 'Bản ghi đã bị thay đổi bởi người khác. Vui lòng tải lại và thử lại.',
            ], 409);
        }

        $hasActive = Appointment::whereHas('schedule', fn($q) => $q->where('doctor_id', $id))
            ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám', 'Đã thanh toán'])
            ->exists();

        if ($hasActive) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa: bác sĩ còn lịch hẹn chưa hoàn thành.',
            ], 422);
        }

        DB::transaction(function () use ($doctor, $oldVersion) {
            // atomic delete/disable with version check
            $updated = Doctor::where('doctor_id', $doctor->doctor_id)
                ->where('version', $oldVersion)
                ->update(['status' => 0]);

            if ($updated === 0) {
                throw new \RuntimeException('CONFLICT');
            }

            $doctor->delete();
        });

        return response()->json(['success' => true, 'message' => 'Đã xóa bác sĩ thành công.']);
    }

    /**
     * POST /doctor/dashboard/doctors/upload-avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        $validated = $request->validate([
            'avatar' => 'required|image|max:5120',
        ]);

        $file = $request->file('avatar');
        $path = $file->store('images/doctors', 'public');

        return response()->json([
            'success' => true,
            'message' => 'Uploaded',
            'path'    => $path,
            'url'     => '/storage/' . $path,
        ]);
    }
}

