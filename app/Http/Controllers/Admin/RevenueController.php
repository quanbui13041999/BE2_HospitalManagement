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
        if ($year < 2000 || $year > (int)date('Y') + 2) {
            $year = (int)date('Y');
        }

        $monthInput = $request->input('month');
        $month = null;
        if ($monthInput !== null) {
            $monthVal = (int)$monthInput;
            if ($monthVal >= 1 && $monthVal <= 12) {
                $month = $monthVal;
            }
        }
        $method = $request->input('method');
        if ($method) {
            $method = preg_replace('/[^a-zA-Z0-9\s\-\p{L}]/u', '', $method);
            $method = trim($method) ?: null;
        } else {
            $method = null;
        }

        // Delegate data fetching to Service
        $data = $this->revenueService->getDashboardStatistics($year, $month, $method);

        return view('admin.revenue.index', $data);
    }
}
