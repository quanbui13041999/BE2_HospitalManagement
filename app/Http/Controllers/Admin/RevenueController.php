<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RevenueService;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function __construct(protected RevenueService $revenueService) {}

    public function index(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));

        // Delegate data fetching to Service
        $data = $this->revenueService->getDashboardStatistics($year);

        return view('admin.revenue.index', $data);
    }
}
