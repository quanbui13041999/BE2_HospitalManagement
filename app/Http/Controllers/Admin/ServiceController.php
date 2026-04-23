<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Service;
use App\Models\ServicePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    // ----------------------------------------------------------------
    // Danh sách dịch vụ + tab Bảng giá + Lịch sử
    // ----------------------------------------------------------------
    public function index(Request $request)
    {
        // Tab hiện tại
        $tab = $request->get('tab', 'services'); // services | prices | history

        // ── Tab 1: Danh sách dịch vụ ──────────────────────────────
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

        $services    = $query->orderBy('service_code')->paginate(20)->withQueryString();
        $departments = Department::where('status', 1)->orderBy('department_name')->get();

        // ── Tab 2: Bảng giá 3 cột ─────────────────────────────────
        // Lấy tất cả dịch vụ active + giá hiện hành nhóm theo loại
        $pricesByType = [];
        foreach (['Thường', 'BHYT', 'VIP'] as $type) {
            $pricesByType[$type] = DB::table('ServicePrices as sp')
                ->join('Services as s', 's.service_id', '=', 'sp.service_id')
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

        // ── Tab 3: Lịch sử thay đổi giá ──────────────────────────
        $priceHistory = DB::table('ServicePriceLogs as l')
            ->join('Services as s', 's.service_id', '=', 'l.service_id')
            ->join('Users as u', 'u.user_id', '=', 'l.changed_by')
            ->select(
                'l.*',
                's.service_name',
                's.service_code',
                'u.full_name as changed_by_name'
            )
            ->orderByDesc('l.changed_at')
            ->paginate(30, ['*'], 'history_page')
            ->withQueryString();

        $priceTypes = ServicePrice::PRICE_TYPES;

        return view('admin.services.index', compact(
            'services', 'departments', 'pricesByType',
            'priceHistory', 'priceTypes', 'tab'
        ));
    }

    // ----------------------------------------------------------------
    // Form tạo dịch vụ mới
    // ----------------------------------------------------------------
    public function create()
    {
        $departments = Department::where('status', 1)->orderBy('department_name')->get();
        $priceTypes  = ServicePrice::PRICE_TYPES;
        return view('admin.services.create', compact('departments', 'priceTypes'));
    }

    // ----------------------------------------------------------------
    // Lưu dịch vụ mới
    // ----------------------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'service_code'     => 'required|string|max:30|unique:Services,service_code',
            'service_name'     => 'required|string|max:150',
            'department_id'    => 'nullable|exists:Departments,department_id',
            'description'      => 'nullable|string|max:500',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status'           => 'required|boolean',
            'prices'           => 'nullable|array',
            'prices.*.price_type'     => 'required_with:prices|in:' . implode(',', ServicePrice::PRICE_TYPES),
            'prices.*.price'          => 'required_with:prices|numeric|min:0',
            'prices.*.effective_date' => 'required_with:prices|date',
            'prices.*.end_date'       => 'nullable|date|after_or_equal:prices.*.effective_date',
        ], [
            'service_code.unique' => 'Mã dịch vụ đã tồn tại.',
        ]);

        DB::transaction(function () use ($request) {
            $service = Service::create($request->only([
                'service_code', 'service_name', 'department_id',
                'description', 'duration_minutes', 'status',
            ]));

            if ($request->filled('prices')) {
                foreach ($request->prices as $p) {
                    if (empty($p['price'])) continue;
                    $price = $service->prices()->create([
                        'price_type'     => $p['price_type'],
                        'price'          => $p['price'],
                        'effective_date' => $p['effective_date'],
                        'end_date'       => $p['end_date'] ?? null,
                        'created_by'     => Auth::id(),
                        'created_at'     => now(),
                    ]);
                    // Ghi log khởi tạo giá
                    $this->logPriceChange(
                        serviceId: $service->service_id,
                        priceType: $p['price_type'],
                        oldPrice:  null,
                        newPrice:  $p['price'],
                        reason:    'Khởi tạo dịch vụ'
                    );
                }
            }
        });

        return redirect()->route('admin.services.index')
            ->with('success', 'Tạo dịch vụ thành công!');
    }

    // ----------------------------------------------------------------
    // Chi tiết dịch vụ
    // ----------------------------------------------------------------
    public function show(Service $service)
    {
        $service->load(['department', 'prices.createdBy']);
        $priceTypes = ServicePrice::PRICE_TYPES;
        return view('admin.services.show', compact('service', 'priceTypes'));
    }

    // ----------------------------------------------------------------
    // Form sửa dịch vụ
    // ----------------------------------------------------------------
    public function edit(Service $service)
    {
        $service->load(['department', 'prices']);
        $departments = Department::where('status', 1)->orderBy('department_name')->get();
        $priceTypes  = ServicePrice::PRICE_TYPES;
        return view('admin.services.edit', compact('service', 'departments', 'priceTypes'));
    }

    // ----------------------------------------------------------------
    // Cập nhật dịch vụ
    // ----------------------------------------------------------------
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'service_code'     => 'required|string|max:30|unique:Services,service_code,' . $service->service_id . ',service_id',
            'service_name'     => 'required|string|max:150',
            'department_id'    => 'nullable|exists:Departments,department_id',
            'description'      => 'nullable|string|max:500',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status'           => 'required|boolean',
        ]);

        $service->update($request->only([
            'service_code', 'service_name', 'department_id',
            'description', 'duration_minutes', 'status',
        ]));

        return redirect()->route('admin.services.show', $service)
            ->with('success', 'Cập nhật dịch vụ thành công!');
    }

    // ----------------------------------------------------------------
    // Toggle trạng thái (Hoạt động ↔ Tạm ngưng)
    // ----------------------------------------------------------------
    public function toggleStatus(Service $service)
    {
        $service->update(['status' => !$service->status]);
        $msg = $service->status ? 'Đã kích hoạt dịch vụ.' : 'Đã vô hiệu hoá dịch vụ.';
        return back()->with('success', $msg);
    }

    // ================================================================
    //  QUẢN LÝ BẢNG GIÁ
    // ================================================================

    // Thêm mức giá mới
    public function storePrice(Request $request, Service $service)
    {
        $request->validate([
            'price_type'     => 'required|in:' . implode(',', ServicePrice::PRICE_TYPES),
            'price'          => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:effective_date',
        ]);

        $service->prices()->create([
            'price_type'     => $request->price_type,
            'price'          => $request->price,
            'effective_date' => $request->effective_date,
            'end_date'       => $request->end_date,
            'created_by'     => Auth::id(),
            'created_at'     => now(),
        ]);

        // Ghi log
        $this->logPriceChange(
            serviceId: $service->service_id,
            priceType: $request->price_type,
            oldPrice:  null,
            newPrice:  $request->price,
            reason:    $request->get('reason', 'Thêm mức giá mới')
        );

        return back()->with('success', 'Đã thêm mức giá mới.');
    }

    // Cập nhật mức giá + ghi log lịch sử
    public function updatePrice(Request $request, Service $service, ServicePrice $price)
    {
        abort_if($price->service_id !== $service->service_id, 403);

        $request->validate([
            'price_type'     => 'required|in:' . implode(',', ServicePrice::PRICE_TYPES),
            'price'          => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:effective_date',
        ]);

        $oldPrice = $price->price;
        $oldType  = $price->price_type;

        $price->update($request->only(['price_type', 'price', 'effective_date', 'end_date']));

        // Ghi log chỉ khi giá thực sự thay đổi
        if ((float) $oldPrice !== (float) $request->price || $oldType !== $request->price_type) {
            $this->logPriceChange(
                serviceId: $service->service_id,
                priceType: $request->price_type,
                oldPrice:  $oldPrice,
                newPrice:  $request->price,
                reason:    $request->get('reason', 'Cập nhật giá')
            );
        }

        return back()->with('success', 'Cập nhật giá thành công.');
    }

    // Xoá mức giá
    public function destroyPrice(Service $service, ServicePrice $price)
    {
        abort_if($price->service_id !== $service->service_id, 403);
        $price->delete();
        return back()->with('success', 'Đã xoá mức giá.');
    }

    // ----------------------------------------------------------------
    // Helper: ghi log lịch sử thay đổi giá
    // ----------------------------------------------------------------
    private function logPriceChange(
        int    $serviceId,
        string $priceType,
        ?float $oldPrice,
        float  $newPrice,
        string $reason = ''
    ): void {
        // Bảng ServicePriceLogs cần được tạo trong migration:
        // service_id, price_type, old_price, new_price, changed_by, changed_at, reason
        DB::table('ServicePriceLogs')->insert([
            'service_id'  => $serviceId,
            'price_type'  => $priceType,
            'old_price'   => $oldPrice,
            'new_price'   => $newPrice,
            'changed_by'  => Auth::id(),
            'changed_at'  => now(),
            'reason'      => $reason,
        ]);
    }
}
