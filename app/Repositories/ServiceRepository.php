<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceRepository
{
    public function filteredServices(Request $request)
    {
        $query = Service::with(['department', 'activePrices']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('service_name', 'like', "%{$request->search}%")
                  ->orWhere('service_code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active' ? 1 : 0);
        }

        return $query->orderBy('service_code')->paginate(20)->withQueryString();
    }

    public function pricesByType(): array
    {
        $result = [];
        foreach (['Thường', 'BHYT', 'VIP'] as $type) {
            $result[$type] = DB::table('serviceprices as sp')
                ->join('services as s', 's.service_id', '=', 'sp.service_id')
                ->where('sp.price_type', $type)
                ->where('s.status', 1)
                ->where('sp.effective_date', '<=', now()->toDateString())
                ->where(function ($q) {
                    $q->whereNull('sp.end_date')
                      ->orWhere('sp.end_date', '>=', now()->toDateString());
                })
                ->select('s.service_name', 'sp.price', 'sp.price_id')
                ->orderBy('s.service_code')
                ->get();
        }
        return $result;
    }

    public function activeDepartments()
    {
        return Department::where('status', 1)->orderBy('department_name')->get();
    }

    public function filteredPublicServices(Request $request)
    {
        $query = Service::with(['department', 'activePrices'])->where('status', 1);

        // 1. Tìm kiếm từ khóa nâng cao
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('service_name', 'like', "%{$search}%")
                  ->orWhere('service_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Lọc theo chuyên khoa
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 3. Lọc theo phân khúc giá (BHYT / VIP)
        if ($request->filled('price_tier')) {
            $tier = $request->price_tier;
            $query->whereHas('activePrices', function ($q) use ($tier) {
                $q->where('price_type', 'like', "%{$tier}%");
            });
        }

        // 4. Lọc theo thời lượng dịch vụ
        if ($request->filled('duration_range')) {
            $range = $request->duration_range;
            if ($range === 'fast') {
                $query->where('duration_minutes', '<', 30);
            } elseif ($range === 'medium') {
                $query->whereBetween('duration_minutes', [30, 60]);
            } elseif ($range === 'long') {
                $query->where('duration_minutes', '>', 60);
            }
        }

        // 5. Sắp xếp chuyên nghiệp
        if ($request->filled('sort_by')) {
            $sortBy = $request->sort_by;
            if ($sortBy === 'price_asc' || $sortBy === 'price_desc') {
                $subquery = DB::table('serviceprices')
                    ->select('service_id', DB::raw('MIN(price) as min_price'))
                    ->where('effective_date', '<=', now()->toDateString())
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now()->toDateString());
                    })
                    ->groupBy('service_id');
                
                $query->joinSub($subquery, 'price_sub', function($join) {
                    $join->on('services.service_id', '=', 'price_sub.service_id');
                })->orderBy('price_sub.min_price', $sortBy === 'price_asc' ? 'asc' : 'desc');
            } elseif ($sortBy === 'name_desc') {
                $query->orderBy('service_name', 'desc');
            } elseif ($sortBy === 'duration_asc') {
                $query->orderBy('duration_minutes', 'asc');
            } else {
                $query->orderBy('service_name', 'asc');
            }
        } else {
            $query->orderBy('service_name', 'asc');
        }

        return $query->paginate(12)->withQueryString();
    }

    public function relatedServices(Service $service, int $limit = 4)
    {
        return Service::with('activePrices')
            ->where('department_id', $service->department_id)
            ->where('service_id', '!=', $service->service_id)
            ->where('status', 1)
            ->limit($limit)
            ->get();
    }

    public function priceHistory()
    {
        return DB::table('serviceprices as sp')
            ->join('services as s', 's.service_id', '=', 'sp.service_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'sp.created_by')
            ->select([
                'sp.created_at as changed_at',
                's.service_name',
                's.service_code',
                'u.full_name as changed_by_name',
                'sp.price_type',
                DB::raw('NULL as old_price'),
                'sp.price as new_price',
                DB::raw('CONCAT("Áp dụng từ ", DATE_FORMAT(sp.effective_date, "%d/%m/%Y")) as reason'),
            ])
            ->orderBy('sp.created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
    }
}

