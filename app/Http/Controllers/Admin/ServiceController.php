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
        $this->serviceService->createService($request->validated());

        return redirect()->route('admin.services.index')
            ->with('success', 'Tạo dịch vụ thành công!');
    }

    // ----------------------------------------------------------------
    // Chi tiết dịch vụ
    // ----------------------------------------------------------------
    public function show(Service $service)
    {
        return view('admin.services.show', $this->serviceService->buildShowData($service));
    }

    // ----------------------------------------------------------------
    // Form sửa dịch vụ
    // ----------------------------------------------------------------
    public function edit(Service $service)
    {
        return view('admin.services.edit', $this->serviceService->buildEditData($service));
    }

    // ----------------------------------------------------------------
    // Cập nhật dịch vụ
    // ----------------------------------------------------------------
    public function update(ServiceUpdateRequest $request, Service $service)
    {
        $this->serviceService->updateService($service, $request->validated());

        return redirect()->route('admin.services.show', $service)
            ->with('success', 'Cập nhật dịch vụ thành công!');
    }

    // ----------------------------------------------------------------
    // Xoá dịch vụ
    // ----------------------------------------------------------------
    public function destroy(Service $service)
    {
        $name = $service->service_name;
        $this->serviceService->deleteService($service);

        return redirect()->route('admin.services.index')
            ->with('success', "Đã xoá dịch vụ \"{$name}\" thành công.");
    }

    // ----------------------------------------------------------------
    // Toggle trạng thái
    // ----------------------------------------------------------------
    public function toggleStatus(Service $service)
    {
        $this->serviceService->toggleStatus($service);
        $msg = $service->fresh()->status ? 'Đã kích hoạt dịch vụ.' : 'Đã vô hiệu hoá dịch vụ.';

        return back()->with('success', $msg);
    }

    // ================================================================
    //  QUẢN LÝ BẢNG GIÁ
    // ================================================================

    public function storePrice(ServicePriceRequest $request, Service $service)
    {
        $this->serviceService->addPrice($service, $request->validated());

        return back()->with('success', 'Đã thêm mức giá mới.');
    }

    public function updatePrice(ServicePriceRequest $request, Service $service, ServicePrice $price)
    {
        abort_if($price->service_id !== $service->service_id, 403);
        $this->serviceService->updatePrice($price, $request->validated());

        return back()->with('success', 'Cập nhật giá thành công.');
    }

    public function destroyPrice(Service $service, ServicePrice $price)
    {
        abort_if($price->service_id !== $service->service_id, 403);
        $this->serviceService->deletePrice($price);

        return back()->with('success', 'Đã xoá mức giá.');
    }
}
