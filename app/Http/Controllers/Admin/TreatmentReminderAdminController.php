<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{TreatmentReminder, User, MedicalRecord, TreatmentHomeInstruction};
use App\Services\{TreatmentReminderService, ComplianceReportService};
use App\Http\Requests\Admin\StoreTreatmentReminderRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreatmentReminderAdminController extends Controller
{
    public function __construct(
        private TreatmentReminderService $service,
        private ComplianceReportService  $reportService
    ) {}

    /** Danh sách bệnh nhân & tổng quan tuân thủ */
    public function index(Request $request)
    {
        $patients = User::where('role_id', 3)
            ->where('status', 1)
            ->withCount(['treatmentReminders as total_reminders',
                         'treatmentConfirmations as confirmed_reminders'])
            ->paginate(15);

        return view('admin.treatment_reminder.index', compact('patients'));
    }

    /** Chi tiết 1 bệnh nhân */
    public function show($userId)
    {
        $userId = $this->validRouteId($userId);

        if (! $userId) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        $user = User::where('role_id', 3)->find($userId);

        if (! $user) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        $data    = $this->service->getDashboardData($userId);
        $records = MedicalRecord::where('patient_id', $userId) // patient_id in medical_records
            ->with(['prescriptions', 'doctor'])
            ->latest('created_at')
            ->get();

        return view('admin.treatment_reminder.show', compact('user', 'data', 'records'));
    }

    /** Form tạo nhắc nhở thủ công */
    public function create(Request $request)
    {
        if ($request->has('user_id')) {
            $userId = $this->validRouteId($request->query('user_id'));

            if (! $userId || ! User::where('role_id', 3)->where('status', 1)->find($userId)) {
                return $this->redirectToTreatmentIndexNotFound();
            }
        }

        $patients = User::where('role_id', 3)->where('status', 1)->orderBy('full_name')->get();
        $records  = MedicalRecord::with('patient')->latest()->get();
        return view('admin.treatment_reminder.create', compact('patients', 'records'));
    }

    /** Lưu nhắc nhở mới */
    public function store(StoreTreatmentReminderRequest $request)
    {
        $data = $request->validated();
        $remindAt = Carbon::parse($data['remind_at']);
        $data['remind_at'] = $remindAt->format('Y-m-d H:i:s');
        $lockKey = $this->reminderCreateLockKey($data['user_id'], $data['reminder_type'], $remindAt);

        if (! $this->acquireReminderLock($lockKey)) {
            return back()
                ->withInput()
                ->with('warning', 'Đang có người khác tạo nhắc nhở cho bệnh nhân ở thời điểm này. Vui lòng tải lại dữ liệu.');
        }

        try {
            $created = DB::transaction(function () use ($data, $remindAt) {
                User::where('user_id', $data['user_id'])
                    ->where('role_id', 3)
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->firstOrFail();

                $alreadyExists = TreatmentReminder::where('user_id', $data['user_id'])
                    ->where('reminder_type', $data['reminder_type'])
                    ->whereBetween('remind_at', [
                        $remindAt->copy()->startOfMinute()->format('Y-m-d H:i:s'),
                        $remindAt->copy()->endOfMinute()->format('Y-m-d H:i:s'),
                    ])
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyExists) {
                    return false;
                }

                TreatmentReminder::create($data);

                return true;
            });
        } finally {
            $this->releaseReminderLock($lockKey);
        }

        if (! $created) {
            return redirect()
                ->route('admin.treatment.show', $data['user_id'])
                ->withInput()
                ->with('warning', 'Nhắc nhở này đã tồn tại cho bệnh nhân trong cùng phút. Hệ thống không tạo trùng, vui lòng tải lại dữ liệu.');
        }
        return redirect()->route('admin.treatment.show', $data['user_id'])
            ->with('success', 'Đã tạo nhắc nhở thành công!');
    }

    /** Form sửa nhắc nhở */
    public function edit($reminderId)
    {
        $reminderId = $this->validRouteId($reminderId);

        if (! $reminderId) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        $reminder = TreatmentReminder::find($reminderId);

        if (! $reminder) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        $reminderSnapshot = $this->reminderSnapshot($reminder);

        return view('admin.treatment_reminder.edit', compact('reminder', 'reminderSnapshot'));
    }

    /** Cập nhật nhắc nhở */
    public function update(StoreTreatmentReminderRequest $request, $reminderId)
    {
        $reminderId = $this->validRouteId($reminderId);

        if (! $reminderId) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        $data = $request->validated();
        $snapshot = $data['reminder_snapshot'];
        unset($data['reminder_snapshot']);
        $data['remind_at'] = Carbon::parse($data['remind_at'])->format('Y-m-d H:i:s');

        $result = DB::transaction(function () use ($reminderId, $data, $snapshot) {
            $reminder = TreatmentReminder::where('reminder_id', $reminderId)
                ->lockForUpdate()
                ->first();

            if (! $reminder) {
                return 'missing';
            }

            if (! hash_equals($this->reminderSnapshot($reminder), $snapshot)) {
                return 'stale';
            }

            $reminder->update($data);

            return ['status' => 'updated', 'user_id' => $reminder->user_id];
        });

        if ($result === 'missing') {
            return $this->redirectToTreatmentIndexNotFound();
        }

        if ($result === 'stale') {
            return redirect()->route('admin.treatment.edit', $reminderId)
                ->with('warning', 'Nhắc nhở đã được người khác cập nhật trước đó. Vui lòng tải lại dữ liệu rồi sửa lại.');
        }

        return redirect()
            ->route('admin.treatment.show', $result['user_id'])
            ->with('success', 'Đã cập nhật nhắc nhở!');
    }

    private function reminderSnapshot(TreatmentReminder $reminder): string
    {
        return hash_hmac('sha256', implode('|', [
            $reminder->reminder_id,
            $reminder->user_id,
            $reminder->record_id,
            $reminder->reminder_type,
            optional($reminder->remind_at)->format('Y-m-d H:i:s'),
            $reminder->message,
            (int) $reminder->is_sent,
        ]), config('app.key'));
    }

    private function reminderCreateLockKey(int $userId, string $type, Carbon $remindAt): string
    {
        return 'treatment_reminder_create:' . implode(':', [
            $userId,
            $type,
            $remindAt->copy()->format('YmdHi'),
        ]);
    }

    private function reminderDeleteLockKey(int $reminderId): string
    {
        return 'treatment_reminder_delete:' . $reminderId;
    }

    private function acquireReminderLock(string $lockKey): bool
    {
        $result = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockKey]);

        return (int) ($result->acquired ?? 0) === 1;
    }

    private function releaseReminderLock(string $lockKey): void
    {
        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockKey]);
    }

    /** Xóa nhắc nhở */
    public function destroy($reminderId)
    {
        $reminderId = $this->validRouteId($reminderId);

        if (! $reminderId) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        $lockKey = $this->reminderDeleteLockKey($reminderId);

        if (! $this->acquireReminderLock($lockKey)) {
            return back()->with('warning', 'Đang có người khác xóa nhắc nhở này. Vui lòng tải lại dữ liệu.');
        }

        try {
            $result = DB::transaction(function () use ($reminderId) {
                $reminder = TreatmentReminder::where('reminder_id', $reminderId)
                    ->lockForUpdate()
                    ->first();

                if (! $reminder) {
                    return ['status' => 'missing', 'user_id' => null];
                }

                $userId = $reminder->user_id;
                $reminder->delete();

                return ['status' => 'deleted', 'user_id' => $userId];
            });
        } finally {
            $this->releaseReminderLock($lockKey);
        }

        if ($result['status'] === 'missing') {
            return $this->redirectToTreatmentIndexNotFound();
        }

        return redirect()
            ->route('admin.treatment.show', $result['user_id'])
            ->with('success', 'Đã xóa nhắc nhở!');
    }

    /**
     * Tự động tạo nhắc nhở từ đơn thuốc của 1 hồ sơ.
     */
    public function generateFromRecord($recordId)
    {
        $recordId = $this->validRouteId($recordId);

        if (! $recordId) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        $record = MedicalRecord::find($recordId);

        if (! $record) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        $count = $this->service->generateFromRecord($record);
        return back()->with('success', "Đã tạo {$count} nhắc nhở từ hồ sơ bệnh án!");
    }

    /** Báo cáo tuân thủ tổng hợp */
    public function complianceReport(Request $request)
    {
        $report = $this->reportService->getOverallReport(
            $request->integer('month', now()->month),
            $request->integer('year', now()->year)
        );
        return view('admin.treatment_reminder.compliance', compact('report'));
    }

    /** ============================================================
     * BỔ SUNG: Form tạo hướng dẫn/bài tập tại nhà cho bệnh nhân
     * ============================================================ */
    public function createInstruction($userId)
    {
        $userId = $this->validRouteId($userId);

        if (! $userId) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        $user = User::where('role_id', 3)->find($userId);

        if (! $user) {
            return $this->redirectToTreatmentIndexNotFound();
        }

        return view('admin.treatment_reminder.create_instruction', compact('user'));
    }

    /** ============================================================
     * BỔ SUNG: Lưu hướng dẫn/bài tập tại nhà mới
     * ============================================================ */
    public function storeInstruction(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|integer',
            'instruction_text' => 'required|string|max:255',
            'detail'           => 'nullable|string',
            'icon'             => 'nullable|string',
        ]);

        TreatmentHomeInstruction::create([
            'user_id'          => $request->user_id,
            'record_id'        => null, // Tạo tự do trực tiếp từ trang hồ sơ tuân thủ nên tạm để null
            'instruction_text' => $request->instruction_text,
            'detail'           => $request->detail,
            'icon'             => $request->icon ?? 'tasks',
            'sort_order'       => 0,
            'is_active'        => 1,
        ]);

        // Điều hướng ngược về trang chi tiết của bệnh nhân vừa tạo theo đúng cấu trúc route mới
        return redirect()->route('admin.treatment.show', $request->user_id)
            ->with('success', 'Đã thêm bài tập tại nhà thành công!');
    }

    private function validRouteId($id): ?int
    {
        $id = trim((string) $id);

        return preg_match('/\A[1-9][0-9]*\z/', $id) ? (int) $id : null;
    }

    private function redirectToTreatmentIndexNotFound()
    {
        return redirect()
            ->route('admin.treatment.index')
            ->with('warning', 'Không tìm thấy trang nhắc nhở điều trị.');
    }
}
