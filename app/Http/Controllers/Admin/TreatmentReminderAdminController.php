<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{TreatmentReminder, User, MedicalRecord};
use App\Services\{TreatmentReminderService, ComplianceReportService};
use App\Http\Requests\Admin\StoreTreatmentReminderRequest;
use Illuminate\Http\Request;

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
    public function show(int $userId)
    {
        $user    = User::where('role_id', 3)->findOrFail($userId);
        $data    = $this->service->getDashboardData($userId);
        $records = MedicalRecord::where('patient_id', $userId) // patient_id in medical_records
            ->with(['prescriptions', 'doctor'])
            ->latest('created_at')
            ->get();

        return view('admin.treatment_reminder.show', compact('user', 'data', 'records'));
    }

    /** Form tạo nhắc nhở thủ công */
    public function create()
    {
        $patients = User::where('role_id', 3)->where('status', 1)->orderBy('full_name')->get();
        $records  = MedicalRecord::with('patient')->latest()->get();
        return view('admin.treatment_reminder.create', compact('patients', 'records'));
    }

    /** Lưu nhắc nhở mới */
    public function store(StoreTreatmentReminderRequest $request)
    {
        TreatmentReminder::create($request->validated());
        return redirect()->route('admin.treatment.index')
            ->with('success', 'Đã tạo nhắc nhở thành công!');
    }

    /** Form sửa nhắc nhở */
    public function edit(int $reminderId)
    {
        $reminder = TreatmentReminder::findOrFail($reminderId);
        return view('admin.treatment_reminder.edit', compact('reminder'));
    }

    /** Cập nhật nhắc nhở */
    public function update(StoreTreatmentReminderRequest $request, int $reminderId)
    {
        $reminder = TreatmentReminder::findOrFail($reminderId);
        $reminder->update($request->validated());
        return back()->with('success', 'Đã cập nhật!');
    }

    /** Xóa nhắc nhở */
    public function destroy(int $reminderId)
    {
        $reminder = TreatmentReminder::findOrFail($reminderId);
        $reminder->delete();
        return back()->with('success', 'Đã xóa nhắc nhở!');
    }

    /**
     * Tự động tạo nhắc nhở từ đơn thuốc của 1 hồ sơ.
     */
    public function generateFromRecord(int $recordId)
    {
        $record = MedicalRecord::findOrFail($recordId);
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
}
