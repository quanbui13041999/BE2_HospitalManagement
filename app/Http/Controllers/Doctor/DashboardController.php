<?php
// app/Http/Controllers/Doctor/DashboardController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use App\Http\Requests\Doctor\UploadAvatarRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\Doctor\DoctorDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

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
        $request->validate([
            'page'      => 'nullable|integer|min:1',
            'per_page'  => 'nullable|integer|min:1|max:100',
            'doctor_id' => 'nullable|integer|min:1',
        ]);

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

        $request->validate([
            'page'      => 'nullable|integer|min:1',
            'per_page'  => 'nullable|integer|min:1|max:100',
            'search'    => 'nullable|string|max:255',
            'status'    => 'nullable|string|in:0,1',
        ]);

        try {
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
                    'version'         => $d->version ?? 1,
                    'avg_rating'      => $d->avg_rating,
                    'total_reviews'   => $d->total_reviews,
                ]),
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'total'        => $paginated->total(),
                ],
            ]);
        } catch (QueryException $e) {
            \Log::error('doctorsList DB error', ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            return response()->json(['success' => false, 'message' => 'Không thể tải danh sách bác sĩ hiện tại. Vui lòng thử lại sau.'], 503);
        }
    }

    /**
     * GET /doctor/dashboard/doctors/{id}
     */
    public function getDoctor(int $id): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'ID bác sĩ không hợp lệ.'], 404);
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

    /**
     * POST /doctor/dashboard/doctors
     */
    public function storeDoctor(StoreDoctorRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (!$validated['user_id'] && !($validated['email'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp User ID hoặc Email để tạo bác sĩ.',
                'status'  => 422,
            ], 422);
        }

        $doctor = null;
        $createdUser = null;
        $plainPassword = null;

        try {
            DB::transaction(function () use ($validated, &$doctor, &$createdUser, &$plainPassword) {
                $userId = $validated['user_id'];
                $email = $validated['email'] ?? null;

                if (!$userId && $email) {
                    $plainPassword = !empty($validated['password'])
                        ? $validated['password']
                        : \Illuminate\Support\Str::random(12);

                    $createdUser = \App\Models\User::create([
                        'full_name'  => $validated['full_name'],
                        'email'      => $email,
                        'password'   => Hash::make($plainPassword),
                        'avatar_url' => $validated['avatar_url'] ?? null,
                        'status'     => $validated['status'] ?? 1,
                        'is_admin'   => 0,
                        'role_id'    => 2,
                    ]);

                    $userId = $createdUser->user_id;
                }

                $doctor = Doctor::create([
                    'user_id'       => $userId,
                    'full_name'     => $validated['full_name'],
                    'department_id' => $validated['department_id'],
                    'experience'    => $validated['experience'],
                    'price'         => $validated['price'],
                    'avatar_url'    => $validated['avatar_url'],
                    'bio'           => $validated['bio'],
                    'status'        => $validated['status'],
                    'version'       => 1,
                ]);
            });

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi: không thể tạo bác sĩ.',
                ], 500);
            }

            $response = [
                'success' => true,
                'message' => 'Đã thêm bác sĩ thành công.',
                'doctor'  => [
                    'doctor_id'     => $doctor->doctor_id,
                    'full_name'     => $doctor->full_name,
                    'department_id' => $doctor->department_id,
                    'version'       => $doctor->version ?? 1,
                ],
            ];

            if ($createdUser) {
                $response['created_user'] = [
                    'email'          => $createdUser->email,
                    'user_id'        => $createdUser->user_id,
                    'plain_password' => $plainPassword,
                ];
            }

            return response()->json($response, 201);
        } catch (QueryException $e) {
            \Log::error('QueryException creating doctor: ' . $e->getMessage());
            
            // Handle unique constraint violations
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'Duplicate')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email hoặc User ID đã tồn tại trong hệ thống.'
                ], 409);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error creating doctor: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm bác sĩ: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST /doctor/dashboard/doctors (storeDoctor) might exist in your project.
    // In case it is already implemented elsewhere, leave it out here.

    /**
     * PUT /doctor/dashboard/doctors/{id}
     */
    public function updateDoctor(UpdateDoctorRequest $request, int $id): JsonResponse
    {
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'ID bác sĩ không hợp lệ.'], 404);
        }

        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Bác sĩ không tồn tại.'], 404);
        }

        $validated = $request->validated();
        $oldVersion = $doctor->version ?? 1;

        if ($oldVersion !== $validated['version']) {
            return response()->json([
                'success' => false,
                'message' => 'Bản ghi đã bị thay đổi bởi người khác. Vui lòng tải lại và thử lại.',
            ], 409);
        }

        try {
            DB::transaction(function () use ($doctor, $validated, $oldVersion) {
                $data = $validated;
                unset($data['version']);

                if (empty($data['avatar_url'])) {
                    $data['avatar_url'] = $doctor->avatar_url;
                }

                $newVersion = $oldVersion + 1;

                $updated = Doctor::where('doctor_id', $doctor->doctor_id)
                    ->where('version', $oldVersion)
                    ->update(array_merge($data, ['version' => $newVersion]));

                if ($updated === 0) {
                    throw new \RuntimeException('CONFLICT');
                }

                if (!empty($validated['user_id'])) {
                    $user = \App\Models\User::find($validated['user_id']);
                    if ($user) {
                        $user->update([
                            'full_name'  => $validated['full_name'],
                            'avatar_url' => !empty($validated['avatar_url']) ? $validated['avatar_url'] : $user->avatar_url,
                            'status'     => $validated['status'],
                        ] + (!empty($validated['email']) ? ['email' => $validated['email']] : [])
                          + (!empty($validated['password']) ? ['password' => Hash::make($validated['password'])] : []));
                    }
                }
            });

            // Refresh doctor from database to get the updated version
            $doctor->refresh();
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'CONFLICT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bản ghi đã bị thay đổi bởi người khác. Vui lòng tải lại và thử lại.',
                ], 409);
            }
            throw $e;
        } catch (QueryException $e) {
            \Log::error('QueryException updating doctor: ' . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'Duplicate')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email hoặc User ID đã tồn tại. Vui lòng chọn giá trị khác.',
                ], 409);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error updating doctor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật thông tin bác sĩ.',
            'doctor'  => [
                'doctor_id' => $doctor->doctor_id,
                'full_name' => $doctor->full_name,
                'version' => $doctor->version,
            ],
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

        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'ID bác sĩ không hợp lệ.'], 404);
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

        try {
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
                // Atomic delete with version check
                $updated = Doctor::where('doctor_id', $doctor->doctor_id)
                    ->where('version', $oldVersion)
                    ->delete();

                if ($updated === 0) {
                    throw new \RuntimeException('CONFLICT');
                }
            });

            return response()->json(['success' => true, 'message' => 'Đã xóa bác sĩ thành công.']);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'CONFLICT') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bản ghi đã bị xóa bởi người khác hoặc phiên bản không khớp. Vui lòng tải lại.',
                ], 409);
            }
            throw $e;
        } catch (QueryException $e) {
            \Log::error('QueryException deleting doctor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cơ sở dữ liệu: Không thể xóa bác sĩ. Có thể bác sĩ được tham chiếu bởi dữ liệu khác.'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error deleting doctor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /doctor/dashboard/doctors/upload-avatar
     */
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $file = $request->file('avatar');
            
            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy tệp ảnh.',
                ], 400);
            }

            $path = $file->store('images/doctors', 'public');

            return response()->json([
                'success' => true,
                'message' => 'Tải ảnh lên thành công.',
                'path'    => $path,
                'url'     => '/storage/' . $path,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error uploading avatar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải ảnh: ' . $e->getMessage(),
            ], 500);
        }
    }
}

