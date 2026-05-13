<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DoctorStatisticService;

class DoctorStatisticController extends Controller
{
    public function __construct(protected DoctorStatisticService $statisticService) {}

    public function index()
    {
        // Delegate querying to Service
        $statistics = $this->statisticService->getDoctorStatistics();

        return view('admin.doctor_statistics.index', compact('statistics'));
    }
}
