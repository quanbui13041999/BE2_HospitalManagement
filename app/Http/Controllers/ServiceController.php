<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(protected ServiceRepository $repo) {}

    /**
     * Hiển thị danh sách dịch vụ công khai (trang chủ).
     */
    public function index(Request $request)
    {
        $data = [
            'services'    => $this->repo->filteredPublicServices($request),
            'departments' => $this->repo->activeDepartments(),
        ];

        return view('services.index', $data);
    }

    /**
     * Hiển thị chi tiết dịch vụ.
     */
    public function show(Request $request, int $id)
    {
        $service = Service::with(['department', 'activePrices'])->find($id);

        abort_if(!$service || !$service->status, 404, 'Dịch vụ không tồn tại hoặc không khả dụng.');

        $related = $this->repo->relatedServices($service);

        return view('services.show', [
            'service' => $service,
            'related' => $related,
        ]);
    }

    /**
     * Lấy giá dịch vụ theo loại (API).
     */
    public function getPrice(int $id, string $priceType)
    {
        $service = Service::find($id);

        abort_if(!$service, 404, 'Dịch vụ không tồn tại.');

        $price = $service->activePrices()
            ->where('price_type', $priceType)
            ->first();

        if (!$price) {
            return response()->json(['error' => 'Giá không khả dụng.'], 404);
        }

        return response()->json([
            'price_id'    => $price->price_id,
            'service_id'  => $service->service_id,
            'price_type'  => $priceType,
            'price'       => $price->price,
            'effective'   => $price->effective_date->toDateString(),
        ]);
    }
}
