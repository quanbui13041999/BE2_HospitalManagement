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
                'success' => true,
                'service' => $service,
                'departments' => $this->serviceService->buildEditData($service)['departments'],
            ]);
        }
        return view('admin.services.edit', $this->serviceService->buildEditData($service));
    }

    // ----------------------------------------------------------------
    // Cập nhật dịch vụ
    // ----------------------------------------------------------------
    public function update(ServiceUpdateRequest $request, Service $service)
    {
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
}
