<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DoctorStatisticService;

class DoctorStatisticController extends Controller
{
    public function __construct(protected DoctorStatisticService $statisticService) {}

    public function index(\Illuminate\Http\Request $request)
    {
        // Delegate querying to Service
        $data = $this->statisticService->getDashboardData($request);

        return view('admin.doctor_statistics.index', $data);
    }
}
