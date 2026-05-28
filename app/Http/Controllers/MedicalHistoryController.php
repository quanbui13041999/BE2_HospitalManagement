<?php

namespace App\Http\Controllers;

use App\Services\MedicalRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MedicalHistoryController extends Controller
{
    public function __construct(private MedicalRecordService $service) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để xem lịch sử khám.');
        }

        if (($user->role_id ?? 0) !== 3) {
            return redirect()->route('home')
                ->with('error', 'Trang lịch sử khám chỉ dành cho bệnh nhân.');
        }

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

        $records = $this->service->getPatientRecords($user->user_id, $filters);
        $visitTypes = $this->service->getVisitTypes($user->user_id, $user->role_id);
        $statuses = $this->service->getStatuses();

        return view('medical_history.index', compact('records', 'visitTypes', 'statuses'));
    }
}
