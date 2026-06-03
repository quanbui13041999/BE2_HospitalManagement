<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServicePriceRequest;
use App\Http\Requests\Admin\ServiceRequest;
use App\Http\Requests\Admin\ServiceUpdateRequest;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Services\Admin\ServiceService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(protected ServiceService $serviceService) {}

    // ----------------------------------------------------------------
    // Danh sách dịch vụ + tab Bảng giá
    // ----------------------------------------------------------------
    public function index(Request $request)
    {
        // Validate page param: phải là số nguyên dương
        $page = $request->query('page');
        if ($page !== null && (!ctype_digit((string) $page) || (int) $page < 1)) {
            return redirect()->route('admin.services.index', array_merge(
                $request->except('page'),
                ['page' => 1]
            ))->with('error', 'Tham số trang không hợp lệ, đã chuyển về trang 1.');
        }

        return view('admin.services.index', $this->serviceService->buildIndexData($request));
    }

    // ----------------------------------------------------------------
    // Form tạo dịch vụ mới
    // ----------------------------------------------------------------
    public function create()
    {
        return view('admin.services.create', $this->serviceService->buildCreateData());
    }

    // ----------------------------------------------------------------
    // Lưu dịch vụ mới
    // ----------------------------------------------------------------
    public function store(ServiceRequest $request)
    {
        $service = $this->serviceService->createService($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo dịch vụ thành công!',
                'service' => $service,
            ]);
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Tạo dịch vụ thành công!');
    }

    // ----------------------------------------------------------------
    // Chi tiết dịch vụ
    // ----------------------------------------------------------------
    public function show(Service $service)
    {
        if (request()->ajax() || request()->wantsJson()) {
            $service->load(['department', 'prices.createdBy']);
            return response()->json([
                'success' => true,
                'service' => $service,
                'priceTypes' => ServicePrice::PRICE_TYPES,
            ]);
        }
        return view('admin.services.show', $this->serviceService->buildShowData($service));
    }

    // ----------------------------------------------------------------
    // Form sửa dịch vụ
    // ----------------------------------------------------------------
    public function edit(Service $service)
    {
        if (request()->ajax() || request()->wantsJson()) {
            $service->load(['department']);
            return response()->json([
                'success'              => true,
                'service'              => $service,
                'departments'          => $this->serviceService->buildEditData($service)['departments'],
                // Optimistic lock token: frontend dùng giá trị này để phát hiện xung đột
                'updated_at_timestamp' => $service->updated_at?->timestamp,
            ]);
        }
        return view('admin.services.edit', $this->serviceService->buildEditData($service));
    }

    // ----------------------------------------------------------------
    // Cập nhật dịch vụ
    // ----------------------------------------------------------------
    public function update(ServiceUpdateRequest $request, Service $service)
    {
        // Optimistic locking: kiểm tra xung đột cập nhật 2 tab
        $lockVersion = $request->input('_lock_version');
        if ($service->updated_at !== null) {
            $dbTimestamp = (string) $service->updated_at->timestamp;
            if ($lockVersion === null || $lockVersion === '') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã phiên làm việc bị thiếu. Vui lòng tải lại trang.',
                    ], 409);
                }
                return redirect()->route('admin.services.edit', $service)
                    ->with('error', 'Mã phiên làm việc bị thiếu. Vui lòng tải lại trang.');
            }
            if ($lockVersion !== $dbTimestamp) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dữ liệu dịch vụ đã được người khác cập nhật. Vui lòng tải lại trang trước khi cập nhật.',
                    ], 409);
                }
                return redirect()->route('admin.services.edit', $service)
                    ->with('error', 'Dịch vụ đã được người khác cập nhật trước đó. Vui lòng tải lại trang trước khi tiếp tục chỉnh sửa.');
            }
        }

        $this->serviceService->updateService($service, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật dịch vụ thành công!',
                'service' => $service->fresh(['department']),
            ]);
        }

        return redirect()->route('admin.services.show', $service)
            ->with('success', 'Cập nhật dịch vụ thành công!');
    }

    // ----------------------------------------------------------------
    // Xoá dịch vụ
    // ----------------------------------------------------------------
    public function destroy(Service $service)
    {
        try {
            $name = $service->service_name;
            $this->serviceService->deleteService($service);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã xoá dịch vụ \"{$name}\" thành công.",
                ]);
            }

            return redirect()->route('admin.services.index')
                ->with('success', "Đã xoá dịch vụ \"{$name}\" thành công.");
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }

            return redirect()->route('admin.services.index')
                ->with('error', $e->getMessage());
        }
    }

    // ----------------------------------------------------------------
    // Toggle trạng thái
    // ----------------------------------------------------------------
    public function toggleStatus(Service $service)
    {
        $this->serviceService->toggleStatus($service);
        $status = $service->fresh()->status;
        $msg = $status ? 'Đã kích hoạt dịch vụ.' : 'Đã vô hiệu hoá dịch vụ.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'status' => $status,
            ]);
        }

        return back()->with('success', $msg);
    }

    // ================================================================
    //  QUẢN LÝ BẢNG GIÁ
    // ================================================================

    public function storePrice(ServicePriceRequest $request, Service $service)
    {
        $price = $this->serviceService->addPrice($service, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm mức giá mới.',
                'price' => $price->load('createdBy'),
            ]);
        }

        return back()->with('success', 'Đã thêm mức giá mới.');
    }

    public function updatePrice(ServicePriceRequest $request, Service $service, ServicePrice $price)
    {
        abort_if($price->service_id !== $service->service_id, 403);

        // Optimistic locking: kiểm tra dữ liệu có bị thay đổi ở tab khác không
        $lockVersion = $request->input('_lock_version');
        if ($price->updated_at !== null) {
            $dbTimestamp = (string) $price->updated_at->timestamp;
            if ($lockVersion === null || $lockVersion === '') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã phiên làm việc bị thiếu. Vui lòng tải lại trang.',
                    ], 409);
                }
                return back()->with('error', 'Mã phiên làm việc bị thiếu. Vui lòng tải lại trang.');
            }
            if ($lockVersion !== $dbTimestamp) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mức giá đã được người khác cập nhật. Vui lòng tải lại trang.',
                    ], 409);
                }
                return back()->with('error', 'Mức giá đã được người khác cập nhật trước đó. Vui lòng tải lại trang.');
            }
        }

        $this->serviceService->updatePrice($price, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật giá thành công.',
                'price' => $price->fresh('createdBy'),
            ]);
        }

        return back()->with('success', 'Cập nhật giá thành công.');
    }

    public function destroyPrice(Service $service, ServicePrice $price)
    {
        abort_if($price->service_id !== $service->service_id, 403);
        $this->serviceService->deletePrice($price);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xoá mức giá.',
            ]);
        }

        return back()->with('success', 'Đã xoá mức giá.');
    }

    /**
     * JSON endpoint cho realtime polling – admin index.
     */
    public function servicesData(Request $request)
    {
        $services = $this->serviceService->buildIndexData($request);
        return response()->json([
            'stats' => [
                'total'    => $services['services']->total(),
                'active'   => $services['services']->getCollection()->where('status', true)->count(),
                'inactive' => $services['services']->getCollection()->where('status', false)->count(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * JSON endpoint công khai – cho trang /dich-vu polling realtime.
     * Chỉ trả về dịch vụ đang hoạt động, đủ thông tin cho frontend cập nhật card.
     */
    public function publicServicesData(Request $request)
    {
        $services = $this->serviceService->buildPublicIndexData($request)['services'];

        $list = $services->getCollection()->map(function ($s) {
            $priceNormal = $s->activePrices->firstWhere('price_type', 'Thường');
            $lowestPrice = $s->activePrices->min('price');
            return [
                'service_id'       => $s->service_id,
                'service_code'     => $s->service_code,
                'service_name'     => $s->service_name,
                'description'      => $s->description,
                'duration_minutes' => $s->duration_minutes,
                'department'       => $s->department?->department_name,
                'price_normal'     => $priceNormal?->price,
                'lowest_price'     => $lowestPrice,
                'show_url'         => route('user.services.show', $s->service_id),
                'book_url'         => route('appointments.create') . '?service_id=' . $s->service_id,
            ];
        });

        return response()->json([
            'total'     => $services->total(),
            'services'  => $list,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
