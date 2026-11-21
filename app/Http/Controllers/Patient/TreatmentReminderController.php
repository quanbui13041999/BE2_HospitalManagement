<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmReminderRequest;
use App\Http\Requests\ToggleInstructionRequest;
use App\Services\TreatmentReminderService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class TreatmentReminderController extends Controller
{
    public function __construct(private TreatmentReminderService $service) {}

    public function index()
    {
        $data = $this->service->getDashboardData(Auth::id());

        return view('patient.treatment_reminder.index', $data);
    }

    public function confirm(ConfirmReminderRequest $request, int $reminder)
    {
        try {
            $confirmation = $this->service->confirmReminder($reminder, Auth::id());

            return response()->json([
                'success'      => true,
                'confirmed_at' => $confirmation->confirmed_at->format('H:i d/m/Y'),
                'message'      => 'Đã xác nhận thành công!',
            ]);
        } catch (ConflictHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'reload'  => true,
            ], 409);
        }
    }

    public function toggleInstruction(ToggleInstructionRequest $request)
    {
        try {
            $isDone = $this->service->toggleInstruction(
                $request->integer('instruction_id'),
                Auth::id(),
                $request->boolean('expected_state')
            );

            return response()->json(['success' => true, 'is_done' => $isDone]);
        } catch (ConflictHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'reload'  => true,
            ], 409);
        }
    }

    public function report()
    {
        $stats = $this->service->getMonthComplianceStats(Auth::id());

        return response()->json($stats);
    }
}
