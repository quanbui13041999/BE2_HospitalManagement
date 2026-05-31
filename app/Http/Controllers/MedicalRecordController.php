<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Requests\UpdateMedicalRecordRequest;
use App\Models\MedicalRecord;
use App\Models\MedicalAttachment;
use App\Models\MedicalOrder;
use App\Models\Appointment;
use App\Services\MedicalRecordService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MedicalRecordController extends Controller
{
    public function __construct(private MedicalRecordService $service) {}

    // ── LIST ──────────────────────────────────────────────────────

    /**
     * Danh sách hồ sơ – bác sĩ xem của bệnh nhân hoặc của mình
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xem hồ sơ!');
        }

        // Lấy filters từ request
        $filters = [
            'search' => $request->search,
            'visit_type' => $request->visit_type,
            'status' => $request->status,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'sort_by' => $request->get('sort_by', 'exam_date'),
            'sort_order' => $request->get('sort_order', 'desc'),
            'per_page' => $request->get('per_page', 10),
        ];

        // Lấy records dựa trên role
        if ((int) $user->role_id === 3) {
            // Bệnh nhân: xem hồ sơ của mình
            $records = $this->service->getPatientRecords($user->user_id, $filters);
        } elseif ((int) $user->role_id === 2) {
            // Bác sĩ: chỉ xem phiếu khám do chính bác sĩ đó phụ trách.
            $patientId = $request->query('patient_id');
            if ($patientId) {
                $records = $this->service->getPatientRecords((int) $patientId, $filters, (int) $user->user_id);
            } else {
                $records = $this->service->getDoctorRecords($user->user_id, $filters);
            }
        } else {
            // Admin: xem tất cả, nếu có patient_id thì xem tất cả phiếu của bệnh nhân đó.
            $patientId = $request->query('patient_id');
            if ($patientId) {
                $records = $this->service->getPatientRecords((int) $patientId, $filters);
            } else {
                $records = $this->service->getAllRecords($filters);
            }
        }

        // Lấy danh sách loại khám cho dropdown
        $visitTypes = $this->service->getVisitTypes($user->user_id, $user->role_id);

        // Lấy danh sách trạng thái
        $statuses = $this->service->getStatuses();

        // Thống kê (tùy chọn)
        $statistics = $this->service->getStatistics($user->user_id, $user->role_id);

        return view('medical-records.index', compact('records', 'visitTypes', 'statuses', 'statistics'));
    }

    // ── SHOW ──────────────────────────────────────────────────────

    /**
     * Xem chi tiết hồ sơ bệnh án – giao diện chính theo thiết kế
     */
    public function show(int $id): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập!');
        }

        try {
            $record = $this->service->getRecordDetail($id);

            if (! $this->canViewRecord($user, $record)) {
                return redirect()->route('medical-records.index')
                    ->with('error', 'Bạn không có quyền truy cập hồ sơ này.');
            }

            return view('medical-records.show', compact('record'));
        } catch (\Exception $e) {
            return redirect()->route('medical-records.index')
                ->with('warning', 'Hồ sơ bệnh án đã bị xóa hoặc không còn tồn tại. Vui lòng tải lại danh sách.');
        }
    }

    private function canViewRecord($user, MedicalRecord $record): bool
    {
        if ((int) $user->role_id === 1) {
            return true;
        }

        if ((int) $user->role_id === 2) {
            return (int) $record->doctor_id === (int) $user->user_id;
        }

        if ((int) $user->role_id === 3) {
            return (int) $record->patient_id === (int) $user->user_id;
        }

        return false;
    }

    // ── CREATE / STORE ────────────────────────────────────────────

    public function create(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tạo hồ sơ!');
        }

        $appointmentId = $request->query('appointment_id');
        $appointment   = null;
        $record        = null;

        if ($appointmentId) {
            $appointment = Appointment::with([
                'user',
                'service',
                'schedule.doctor',
                'medicalRecord',
            ])->find($appointmentId);

            if (!$appointment) {
                return redirect()->route('medical-records.index')
                    ->with('error', 'Không tìm thấy lịch hẹn để tạo hồ sơ.');
            }

            if ($appointment->status !== 'Hoàn thành') {
                return redirect()->route('medical-records.index', ['patient_id' => $appointment->user_id])
                    ->with('error', 'Lịch hẹn chưa khám xong nên chưa thể tạo hồ sơ bệnh án mới.');
            }

            if ($appointment?->medicalRecord) {
                return redirect()
                    ->route(
                        'medical-records.edit',
                        $appointment->medicalRecord->record_id
                    )
                    ->with('info', 'Hồ sơ khám đã tồn tại cho lịch hẹn này.');
            }
        }

        // ========== THÊM CODE NÀY ==========
        // Lấy danh sách bệnh nhân (role_id = 3)
        $patients = \App\Models\User::where('role_id', 3)->get();

        // Lấy danh sách bác sĩ (role_id = 2)
        $doctors = \App\Models\User::where('role_id', 2)->get();
        // ==================================

        return view('medical-records.create', compact('appointment', 'record', 'patients', 'doctors'));
    }

    public function store(StoreMedicalRecordRequest $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tạo hồ sơ!');
        }

        $data = $request->validated();
        $examMinute = substr((string) ($data['exam_time'] ?? now()->format('H:i')), 0, 5);
        $lockKey = $this->lockKey('medical_record_create', implode('|', [
            $data['patient_id'],
            $data['doctor_id'],
            $data['exam_date'],
            $examMinute,
        ]));

        if (! $this->acquireMedicalRecordLock($lockKey)) {
            return back()->withInput()
                ->with('warning', 'Đang có người tạo phiếu khám cho bệnh nhân này trong cùng khung giờ. Vui lòng tải lại danh sách.');
        }

        try {
            if ($this->recordExistsInMinute((int) $data['patient_id'], (int) $data['doctor_id'], (string) $data['exam_date'], $examMinute)) {
                return back()->withInput()
                    ->with('warning', 'Phiếu khám của bệnh nhân này trong cùng phút đã được người khác tạo trước đó. Vui lòng tải lại danh sách.');
            }

            if (! empty($data['appointment_id']) && MedicalRecord::where('appointment_id', $data['appointment_id'])->exists()) {
                return back()->withInput()
                    ->with('warning', 'Lịch hẹn này đã có hồ sơ bệnh án được tạo trước đó. Vui lòng tải lại danh sách.');
            }

            $record = $this->service->createRecord($data);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->service->uploadAttachment($record, $file);
                }
            }

            return redirect()
                ->route('medical-records.show', $record->record_id)
                ->with('success', 'Hồ sơ bệnh án đã được tạo thành công.');
        } finally {
            $this->releaseMedicalRecordLock($lockKey);
        }
    }

    // ── EDIT / UPDATE ─────────────────────────────────────────────

    public function edit(int $id): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để chỉnh sửa hồ sơ!');
        }

        try {
            $record = $this->service->getRecordDetail($id);

            $isAdmin = ($user->role_id == 1 || $user->role === 'admin');
            if ($record->doctor_id !== $user->user_id && !$isAdmin) {
                return redirect()->route('medical-records.index')
                    ->with('error', 'Bạn không có quyền chỉnh sửa hồ sơ này!');
            }

            $recordSnapshot = $this->recordSnapshot($record);

            return view('medical-records.edit', compact('record', 'recordSnapshot'));
        } catch (\Exception $e) {
            return redirect()->route('medical-records.index')
                ->with('warning', 'Hồ sơ bệnh án đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }
    }

    public function update(UpdateMedicalRecordRequest $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để cập nhật hồ sơ!');
        }

        try {
            $record = MedicalRecord::find($id);

            if (! $record) {
                return redirect()->route('medical-records.index')
                    ->with('warning', 'Hồ sơ bệnh án đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
            }

            $isAdmin = ($user->role_id == 1 || $user->role === 'admin');
            if ($record->doctor_id !== $user->user_id && !$isAdmin) {
                return redirect()->route('medical-records.index')
                    ->with('error', 'Bạn không có quyền cập nhật hồ sơ này!');
            }

            $validated = $request->validated();
            $snapshot = $validated['record_snapshot'];
            unset($validated['record_snapshot']);

            $result = DB::transaction(function () use ($id, $snapshot, $validated) {
                $current = MedicalRecord::lockForUpdate()->find($id);

                if (! $current) {
                    return 'missing';
                }

                if (! hash_equals($this->recordSnapshot($current), $snapshot)) {
                    return 'stale';
                }

                return $this->service->updateRecord($current, $validated);
            });

            if ($result === 'missing') {
                return redirect()->route('medical-records.index')
                    ->with('warning', 'Hồ sơ bệnh án đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
            }

            if ($result === 'stale') {
                return redirect()->route('medical-records.edit', $id)
                    ->with('warning', 'Hồ sơ bệnh án đã được người khác cập nhật trước đó. Hệ thống đã tải lại dữ liệu mới nhất, vui lòng kiểm tra rồi sửa lại.');
            }

            return redirect()
                ->route('medical-records.show', $id)
                ->with('success', 'Cập nhật hồ sơ bệnh án thành công.');
        } catch (\Exception $e) {
            return redirect()
                ->route('medical-records.index')
                ->with('error', 'Có lỗi xảy ra khi cập nhật hồ sơ!');
        }
    }

    // ── DELETE ────────────────────────────────────────────────────

    public function destroy(int $id): RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xóa hồ sơ!');
        }

        try {
            $record = MedicalRecord::find($id);

            if (! $record) {
                return redirect()->route('medical-records.index')
                    ->with('warning', 'Hồ sơ bệnh án đã được người khác xóa trước đó. Vui lòng tải lại danh sách.');
            }

            $isAdmin = ($user->role_id == 1 || $user->role === 'admin');
            if ($record->doctor_id !== $user->user_id && !$isAdmin) {
                return redirect()->route('medical-records.index')
                    ->with('error', 'Bạn không có quyền xóa hồ sơ này!');
            }

            $lockKey = $this->lockKey('medical_record_delete', (string) $id);

            if (! $this->acquireMedicalRecordLock($lockKey)) {
                return redirect()->route('medical-records.index')
                    ->with('warning', 'Đang có người xóa hồ sơ bệnh án này. Vui lòng tải lại danh sách.');
            }

            try {
                $result = DB::transaction(function () use ($id) {
                    $current = MedicalRecord::lockForUpdate()->find($id);

                    if (! $current) {
                        return 'missing';
                    }

                    $current->delete();

                    return 'deleted';
                });
            } finally {
                $this->releaseMedicalRecordLock($lockKey);
            }

            if ($result === 'missing') {
                return redirect()->route('medical-records.index')
                    ->with('warning', 'Hồ sơ bệnh án đã được người khác xóa trước đó. Vui lòng tải lại danh sách.');
            }

            return redirect()
                ->route('medical-records.index')
                ->with('success', 'Hồ sơ bệnh án đã được xóa.');
        } catch (\Exception $e) {
            return redirect()
                ->route('medical-records.index')
                ->with('error', 'Có lỗi xảy ra khi xóa hồ sơ!');
        }
    }

    // ── ORDERS / RESULTS ──────────────────────────────────────────

    /**
     * Cập nhật kết quả xét nghiệm (chỉ Doctor/Admin)
     */
    public function updateOrderResult(Request $request, int $recordId, int $orderId): JsonResponse
    {
        $user = Auth::user();

        // Kiểm tra đăng nhập
        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập!'], 401);
        }

        // Kiểm tra quyền: chỉ Doctor (role_id=2) hoặc Admin (role_id=1)
        $isDoctor = ($user->role_id == 2 || $user->role == 'doctor');
        $isAdmin = ($user->role_id == 1 || $user->role == 'admin');

        if (!$isDoctor && !$isAdmin) {
            return response()->json(['error' => 'Bạn không có quyền cập nhật kết quả!'], 403);
        }

        try {
            $request->validate([
                'result' => ['nullable', 'string', 'max:1000', 'regex:/\A[\pL\pN\s.,;:()\/+\-%]+\z/u'],
            ]);

            $record = MedicalRecord::find($recordId);

            if (! $record) {
                return response()->json(['error' => 'Hồ sơ bệnh án đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.'], 404);
            }

            $order = MedicalOrder::where('record_id', $recordId)->find($orderId);

            if (! $order) {
                return response()->json(['error' => 'Chỉ định đã bị người khác xóa trước đó. Vui lòng tải lại trang.'], 404);
            }

            // Kiểm tra quyền với record (bác sĩ chỉ sửa được của mình)
            if (!$isAdmin && $record->doctor_id !== $user->user_id) {
                return response()->json(['error' => 'Bạn không có quyền sửa kết quả của hồ sơ này!'], 403);
            }

            $order->result_note   = $request->result;
            $order->result_status = 'Có kết quả';
            $order->save();



            return response()->json([
                'success' => true,
                'result_note' => $order->result_note,
                // Bỏ result_date vì không cần và có thể null
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Dữ liệu không hợp lệ!'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xóa kết quả xét nghiệm (chỉ Doctor/Admin)
     */
    public function deleteOrderResult(int $recordId, int $orderId): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập!'], 401);
        }

        $isDoctor = ($user->role_id == 2 || $user->role == 'doctor');
        $isAdmin = ($user->role_id == 1 || $user->role == 'admin');

        if (!$isDoctor && !$isAdmin) {
            return response()->json(['error' => 'Bạn không có quyền xóa kết quả!'], 403);
        }

        try {
            $record = MedicalRecord::find($recordId);

            if (! $record) {
                return response()->json(['error' => 'Hồ sơ bệnh án đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.'], 404);
            }

            $order = MedicalOrder::where('record_id', $recordId)->find($orderId);

            if (! $order) {
                return response()->json(['error' => 'Chỉ định đã bị người khác xóa trước đó. Vui lòng tải lại trang.'], 404);
            }

            if (!$isAdmin && $record->doctor_id !== $user->user_id) {
                return response()->json(['error' => 'Bạn không có quyền xóa kết quả của hồ sơ này!'], 403);
            }

            // ✅ Đúng - khớp với DB
            $order->result_note   = null;
            $order->result_status = null;
            $order->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Có lỗi xảy ra!'], 500);
        }
    }

    // ── ATTACHMENTS ───────────────────────────────────────────────

    /**
     * Upload file đính kèm vào hồ sơ đã tồn tại
     */
    public function uploadAttachment(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập!'], 401);
        }

        // ✅ Kiểm tra quyền đúng cách
        $isAdmin  = in_array($user->role_id ?? 0, [1]) || $user->role === 'admin';
        $isDoctor = in_array($user->role_id ?? 0, [2]) || $user->role === 'doctor';

        if (!$isAdmin && !$isDoctor) {
            return response()->json(['error' => 'Không có quyền upload!'], 403);
        }

        try {
            $request->validate([
                'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
            ]);

            $record = MedicalRecord::find($id);

            if (! $record) {
                return response()->json(['error' => 'Hồ sơ bệnh án đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.'], 404);
            }

            // ✅ Bác sĩ chỉ upload cho hồ sơ của mình, admin upload tất cả
            if (!$isAdmin && $record->doctor_id !== $user->user_id) {
                return response()->json(['error' => 'Bạn không có quyền upload cho hồ sơ này!'], 403);
            }

            $attachment = $this->service->uploadAttachment($record, $request->file('file'));

            return response()->json([
                'success'    => true,
                'attachment' => [
                    'id'        => $attachment->attachment_id,
                    'file_name' => $attachment->file_name,
                    'file_size' => $attachment->file_size_formatted ?? $this->formatFileSize($attachment->file_size),
                    'file_type' => $attachment->file_type,
                    'url'       => route('medical-records.attachments.view', [$record->record_id, $attachment->attachment_id]),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function viewAttachment(int $recordId, int $attachmentId)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xem tập đính kèm!');
        }

        $record = MedicalRecord::find($recordId);

        if (! $record) {
            return redirect()->route('medical-records.index')
                ->with('warning', 'Hồ sơ bệnh án đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        if (! $this->canViewRecord($user, $record)) {
            return redirect()->route('medical-records.index')
                ->with('error', 'Bạn không có quyền xem tập đính kèm của hồ sơ này.');
        }

        $attachment = MedicalAttachment::where('record_id', $recordId)->find($attachmentId);

        if (! $attachment) {
            return redirect()->route('medical-records.show', $recordId)
                ->with('warning', 'Tập đính kèm đã được người khác xóa trước đó. Trang đã được tải lại.');
        }

        if (! Storage::disk('public')->exists($attachment->file_path)) {
            return redirect()->route('medical-records.show', $recordId)
                ->with('warning', 'File đính kèm đã bị xóa khỏi hệ thống. Vui lòng tải lại trang.');
        }

        return response()->file(Storage::disk('public')->path($attachment->file_path), [
            'Content-Disposition' => 'inline; filename="' . addslashes($attachment->file_name) . '"',
        ]);
    }

    public function deleteAttachment(int $recordId, int $attachmentId): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập!'], 401);
        }

        $isAdmin  = in_array($user->role_id ?? 0, [1]) || $user->role === 'admin';
        $isDoctor = in_array($user->role_id ?? 0, [2]) || $user->role === 'doctor';

        if (!$isAdmin && !$isDoctor) {
            return response()->json(['error' => 'Không có quyền!'], 403);
        }

        try {
            $record = MedicalRecord::find($recordId);

            if (! $record) {
                return response()->json(['error' => 'Hồ sơ bệnh án đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.'], 404);
            }

            $attachment = MedicalAttachment::where('record_id', $recordId)->find($attachmentId);

            if (! $attachment) {
                return response()->json(['error' => 'Tập đính kèm đã bị người khác xóa trước đó. Vui lòng tải lại trang.'], 404);
            }

            if (!$isAdmin && $record->doctor_id !== $user->user_id) {
                return response()->json(['error' => 'Không có quyền xóa file này!'], 403);
            }

            $this->service->deleteAttachment($attachment);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // ── PRINT / EXPORT ────────────────────────────────────────────

    /**
     * Trang in hồ sơ bệnh án
     */
    public function print(int $id): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập!');
        }

        try {
            $record = $this->service->getRecordDetail($id);

            if (! $this->canViewRecord($user, $record)) {
                return redirect()->route('medical-records.index')
                    ->with('error', 'Bạn không có quyền truy cập hồ sơ này.');
            }

            return view('medical-records.print', compact('record'));
        } catch (\Exception $e) {
            return redirect()->route('medical-records.index')
                ->with('warning', 'Hồ sơ bệnh án đã bị xóa hoặc không còn tồn tại. Vui lòng tải lại danh sách.');
        }
    }

    // ── HELPER ────────────────────────────────────────────────────

    private function recordExistsInMinute(int $patientId, int $doctorId, string $examDate, string $examMinute): bool
    {
        return MedicalRecord::where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->whereDate('exam_date', $examDate)
            ->whereTime('exam_time', '>=', $examMinute . ':00')
            ->whereTime('exam_time', '<=', $examMinute . ':59')
            ->exists();
    }

    private function recordSnapshot(MedicalRecord $record): string
    {
        return hash_hmac('sha256', implode('|', [
            $record->record_id,
            $record->patient_id,
            $record->doctor_id,
            optional($record->updated_at)->format('Y-m-d H:i:s.u'),
        ]), config('app.key'));
    }

    private function lockKey(string $prefix, string $value): string
    {
        return 'medical:' . sha1($prefix . '|' . $value);
    }

    private function acquireMedicalRecordLock(string $lockKey): bool
    {
        $result = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockKey]);

        return (int) ($result->acquired ?? 0) === 1;
    }

    private function releaseMedicalRecordLock(string $lockKey): void
    {
        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockKey]);
    }

    /**
     * Format file size helper
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
    // Thêm vào cuối class MedicalRecordController
    /**
     * Xuất danh sách ra Excel (tùy chọn)
     */
    public function export(Request $request)
    {
        // Kiểm tra quyền
        $user = Auth::user();
        if (!$user || !in_array($user->role_id, [1, 2])) {
            return redirect()->route('medical-records.index')
                ->with('error', 'Bạn không có quyền xuất dữ liệu!');
        }

        // Lấy dữ liệu theo bộ lọc hiện tại
        $query = MedicalRecord::with(['patient', 'doctor', 'diagnoses']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('record_code', 'like', "%{$search}%")
                    ->orWhere('patient_name', 'like', "%{$search}%")
                    ->orWhere('doctor_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('visit_type')) {
            $query->where('visit_type', $request->visit_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('exam_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('exam_date', '<=', $request->date_to);
        }

        if ($user->role_id === 2) {
            $query->where('doctor_id', $user->user_id);
        } elseif ($user->role_id === 3) {
            $query->where('patient_id', $user->user_id);
        }

        $records = $query->orderBy('exam_date', 'desc')->get();

        // Xuất CSV đơn giản
        $fileName = 'danh-sach-phieu-kham-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Mã phiếu', 'Bệnh nhân', 'Bác sĩ', 'Ngày khám', 'Loại khám', 'Trạng thái']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->record_code,
                    $record->patient_name,
                    $record->doctor_name,
                    $record->exam_date->format('d/m/Y'),
                    $record->visit_type,
                    $record->status ?? 'Chưa xác định'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
