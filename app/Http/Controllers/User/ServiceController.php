<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Department;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Danh sách dịch vụ cho user (bệnh nhân)
     */
    public function index(Request $request)
    {
        // Khởi tạo query chỉ lấy dịch vụ đang hoạt động
        $query = Service::with(['department', 'activePrices'])
            ->where('status', 1);

        // Tìm kiếm theo từ khóa
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('service_name', 'like', "%{$search}%")
                  ->orWhere('service_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Lọc theo khoa
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Lấy danh sách dịch vụ
        $services = $query->orderBy('service_name')
            ->paginate(12)
            ->withQueryString();

        // Lấy danh sách khoa
        $departments = Department::where('status', 1)
            ->orderBy('department_name')
            ->get();

        $priceTypes = ['Thường', 'BHYT', 'VIP'];
        $priceType = $request->price_type;

        return view('user.services.index', compact(
            'services', 
            'departments', 
            'priceTypes',
            'priceType'
        ));
    }

    /**
     * Chi tiết dịch vụ
     */
    public function show($id)
    {
        $service = Service::with([
            'department', 
            'activePrices',
            'prices' => function($query) {
                $query->orderBy('effective_date', 'desc');
            }
        ])->where('status', 1)
          ->findOrFail($id);

        // Lấy dịch vụ cùng khoa
        $relatedServices = Service::with('activePrices')
            ->where('department_id', $service->department_id)
            ->where('service_id', '!=', $service->service_id)
            ->where('status', 1)
            ->limit(4)
            ->get();

        return view('user.services.show', compact('service', 'relatedServices'));
    }

    /**
     * API: Lấy giá theo loại
     */
    public function getPrice($id, $priceType)
    {
        $service = Service::findOrFail($id);
        $price = $service->activePrices()
            ->where('price_type', $priceType)
            ->first();

        return response()->json([
            'success' => true,
            'price' => $price ? number_format($price->price, 0, ',', '.') : 'Chưa có giá',
            'price_raw' => $price ? $price->price : 0
        ]);
    }
}