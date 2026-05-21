<?php
namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Services\TreatmentReminderService;
use App\Http\Requests\ConfirmReminderRequest;
use App\Http\Requests\ToggleInstructionRequest;
use Illuminate\Support\Facades\Auth;

class TreatmentReminderController extends Controller
{
    public function __construct(private TreatmentReminderService $service) {}

    /**
     * Dashboard chính của bệnh nhân.
     */
    public function index()
    {
        $data = $this->service->getDashboardData(Auth::id());
        return view('patient.treatment_reminder.index', $data);
    }

    /**
     * Bệnh nhân xác nhận đã uống thuốc / hoàn thành.
     */
    public function confirm(ConfirmReminderRequest $request, int $reminder)
    {
        $confirmation = $this->service->confirmReminder($reminder, Auth::id());
        return response()->json([
            'success'      => true,
            'confirmed_at' => $confirmation->confirmed_at->format('H:i d/m/Y'),
            'message'      => 'Đã xác nhận thành công!',
        ]);
    }

    /**
     * Toggle checkbox hướng dẫn tại nhà.
     */
    public function toggleInstruction(ToggleInstructionRequest $request)
    {
        $isDone = $this->service->toggleInstruction(
            $request->integer('instruction_id'),
            Auth::id()
        );
        return response()->json(['success' => true, 'is_done' => $isDone]);
    }

    /**
     * Báo cáo tuân thủ.
     */
    public function report()
    {
        $stats = $this->service->getMonthComplianceStats(Auth::id());
        return response()->json($stats);
    }
}
