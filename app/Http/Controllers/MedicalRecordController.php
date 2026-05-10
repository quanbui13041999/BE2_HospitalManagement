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

        if ($user->role_id === 3) {
            $records = $this->service->getPatientRecords($user->user_id);
        } else {
            $patientId = $request->query('patient_id');
            if ($patientId) {
                $records = $this->service->getPatientRecords((int) $patientId);
            } else {
                $records = $this->service->getDoctorRecords($user->user_id);
            }
        }

        return view('medical-records.index', compact('records'));
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
                ->with('error', 'Không tìm thấy hồ sơ bệnh án!');
        }
    }

    private function canViewRecord($user, MedicalRecord $record): bool
    {
        if ($user->role_id === 1) {
            return true;
        }

        if ($user->role_id === 2) {
            return $record->doctor_id === $user->user_id;
        }

        if ($user->role_id === 3) {
            return $record->patient_id === $user->user_id;
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
            ])->find($appointmentId);

            if ($appointment?->medicalRecord) {
                return redirect()
                    ->route(
                        'medical-records.show',
                        $appointment->medicalRecord->record_id
                    )
                    ->with('info', 'Hồ sơ khám đã tồn tại cho lịch hẹn này.');
            }
        }

        return view('medical-records.create', compact('appointment', 'record'));
    }

    public function store(StoreMedicalRecordRequest $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tạo hồ sơ!');
        }

        $data = array_merge(
            $request->validated(),
            ['appointment_id' => $request->input('appointment_id')]
        );

        $record = $this->service->createRecord($data);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->service->uploadAttachment($record, $file);
            }
        }

        return redirect()
            ->route('medical-records.show', $record->record_id)
            ->with('success', 'Hồ sơ bệnh án đã được tạo thành công.');
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

            if ($record->doctor_id !== $user->user_id && $user->role !== 'admin') {
                return redirect()->route('medical-records.index')
                    ->with('error', 'Bạn không có quyền chỉnh sửa hồ sơ này!');
            }

            return view('medical-records.edit', compact('record'));
        } catch (\Exception $e) {
            return redirect()->route('medical-records.index')
                ->with('error', 'Không tìm thấy hồ sơ bệnh án!');
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
            $record = MedicalRecord::findOrFail($id);

            if ($record->doctor_id !== $user->user_id && $user->role !== 'admin') {
                return redirect()->route('medical-records.index')
                    ->with('error', 'Bạn không có quyền cập nhật hồ sơ này!');
            }

            $this->service->updateRecord($record, $request->validated());

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
            $record = MedicalRecord::findOrFail($id);

            if ($record->doctor_id !== $user->user_id && $user->role !== 'admin') {
                return redirect()->route('medical-records.index')
                    ->with('error', 'Bạn không có quyền xóa hồ sơ này!');
            }

            $record->delete();

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
                'result' => 'nullable|string|max:1000',
            ]);

            $order = MedicalOrder::where('record_id', $recordId)->findOrFail($orderId);
            
            // Kiểm tra quyền với record (bác sĩ chỉ sửa được của mình)
            $record = MedicalRecord::findOrFail($recordId);
            if (!$isAdmin && $record->doctor_id !== $user->user_id) {
                return response()->json(['error' => 'Bạn không có quyền sửa kết quả của hồ sơ này!'], 403);
            }

            $order->result_note   = $request->result;
$order->result_status = 'Có kết quả';
            $order->save();

            return response()->json([
                'success' => true,
                'result' => $order->result,
                'result_date' => $order->result_date->format('d/m/Y H:i')
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
            $order = MedicalOrder::where('record_id', $recordId)->findOrFail($orderId);
            
            $record = MedicalRecord::findOrFail($recordId);
            if (!$isAdmin && $record->doctor_id !== $user->user_id) {
                return response()->json(['error' => 'Bạn không có quyền xóa kết quả của hồ sơ này!'], 403);
            }

            $order->result = null;
            $order->result_date = null;
            $order->status = 'pending';
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

        try {
            $request->validate([
                'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            ]);

            $record = MedicalRecord::findOrFail($id);

            if ($record->doctor_id !== $user->user_id && $user->role !== 'admin') {
                return response()->json(['error' => 'Bạn không có quyền upload file cho hồ sơ này!'], 403);
            }

            $attachment = $this->service->uploadAttachment($record, $request->file('file'));

            return response()->json([
                'success'    => true,
                'attachment' => [
                    'id'        => $attachment->attachment_id,
                    'file_name' => $attachment->file_name,
                    'file_size' => $attachment->file_size_formatted ?? $this->formatFileSize($attachment->file_size),
                    'file_type' => $attachment->file_type,
                    'url'       => asset('storage/' . $attachment->file_path),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Xóa file đính kèm
     */
    public function deleteAttachment(int $recordId, int $attachmentId): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập!'], 401);
        }

        try {
            $attachment = MedicalAttachment::where('record_id', $recordId)->findOrFail($attachmentId);

            $record = MedicalRecord::findOrFail($recordId);
            if ($record->doctor_id !== $user->user_id && $user->role !== 'admin') {
                return response()->json(['error' => 'Bạn không có quyền xóa file này!'], 403);
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
                ->with('error', 'Không tìm thấy hồ sơ bệnh án!');
        }
    }

    // ── HELPER ────────────────────────────────────────────────────

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
}