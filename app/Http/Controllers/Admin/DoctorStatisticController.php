<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DoctorStatisticService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorStatisticController extends Controller
{
    public function __construct(protected DoctorStatisticService $statisticService) {}

    public function index(Request $request)
    {
        // 1. Sanitize 'month' filter
        $monthInput = $request->input('month');
        if ($monthInput) {
            if (!preg_match('/^\d{4}-\d{2}$/', $monthInput)) {
                $request->merge(['month' => date('Y-m')]);
            } else {
                $yearPart = (int) substr($monthInput, 0, 4);
                if ($yearPart < 2000 || $yearPart > (int)date('Y') + 5) {
                    $request->merge(['month' => date('Y-m')]);
                }
            }
        }

        // 2. Sanitize 'doctor_id' filter
        $doctorId = $request->input('doctor_id', 'all');
        if ($doctorId !== 'all') {
            if (!is_numeric($doctorId) || (int)$doctorId <= 0) {
                $request->merge(['doctor_id' => 'all']);
            } else {
                $exists = DB::table('doctors')->where('doctor_id', (int)$doctorId)->exists();
                if (!$exists) {
                    $request->merge(['doctor_id' => 'all']);
                }
            }
        }

        // Delegate querying to Service
        $data = $this->statisticService->getDashboardData($request);

        return view('admin.doctor_statistics.index', $data);
    }
}

