<?php

namespace App\Services\Admin;

use App\Models\Service;
use App\Models\ServicePrice;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;

class ServiceService
{
    public function __construct(protected ServiceRepository $repo) {}

    // ----------------------------------------------------------------
    // Data builders cho view
    // ----------------------------------------------------------------

    public function buildIndexData(Request $request): array
    {
        return [
            'services'     => $this->repo->filteredServices($request),
            'departments'  => $this->repo->activeDepartments(),
            'pricesByType' => $this->repo->pricesByType(),
            'priceHistory' => collect(),
            'priceTypes'   => ServicePrice::PRICE_TYPES,
            'tab'          => $request->get('tab', 'services'),
        ];
    }

    public function buildCreateData(): array
    {
        return [
            'departments' => $this->repo->activeDepartments(),
            'priceTypes'  => ServicePrice::PRICE_TYPES,
        ];
    }

    public function buildEditData(Service $service): array
    {
        $service->load(['department', 'prices']);
        return [
            'service'     => $service,
            'departments' => $this->repo->activeDepartments(),
            'priceTypes'  => ServicePrice::PRICE_TYPES,
        ];
    }

    public function buildShowData(Service $service): array
    {
        $service->load(['department', 'prices.createdBy']);
        return [
            'service'    => $service,
            'priceTypes' => ServicePrice::PRICE_TYPES,
        ];
    }

    public function buildPublicIndexData(Request $request): array
    {
        return [
            'services'    => $this->repo->filteredPublicServices($request),
            'departments' => $this->repo->activeDepartments(),
            'priceTypes'  => ['Thường', 'BHYT', 'VIP'],
            'priceType'   => $request->price_type,
        ];
    }

    // ----------------------------------------------------------------
    // CRUD
    // ----------------------------------------------------------------

    public function createService(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            $service = Service::create([
                'service_code'     => $data['service_code'],
                'service_name'     => $data['service_name'],
                'department_id'    => $data['department_id'] ?? null,
                'description'      => $data['description'] ?? null,
                'duration_minutes' => $data['duration_minutes'],
                'status'           => $data['status'],
            ]);

            if (!empty($data['prices'])) {
                foreach ($data['prices'] as $p) {
                    if (empty($p['price'])) continue;
                    $service->prices()->create([
                        'price_type'     => $p['price_type'],
                        'price'          => $p['price'],
                        'effective_date' => $p['effective_date'],
                        'end_date'       => $p['end_date'] ?? null,
                        'created_by'     => Auth::id(),
                        'created_at'     => now(),
                    ]);
                }
            }

            ActivityLogService::log(
                'Admin thêm dịch vụ',
                'Admin ' . (Auth::user()?->full_name ?: '') . ' đã thêm dịch vụ ' . $service->service_name . '.',
                'service',
                $service->service_id,
                [
                    'service_code' => $service->service_code,
                    'department_id' => $service->department_id,
                    'price_count' => count($data['prices'] ?? []),
                ]
            );

            return $service;
        });
    }

    public function updateService(Service $service, array $data): Service
    {
        $before = $service->only(['service_name', 'department_id', 'description', 'duration_minutes', 'status']);

        // service_code không được cập nhật (theo thiết kế gốc)
        $service->update([
            'service_name'     => $data['service_name'],
            'department_id'    => $data['department_id'] ?? null,
            'description'      => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'status'           => $data['status'],
        ]);

        ActivityLogService::log(
            'Admin sửa dịch vụ',
            'Admin ' . (Auth::user()?->full_name ?: '') . ' đã cập nhật dịch vụ ' . $service->service_name . '.',
            'service',
            $service->service_id,
            [
                'service_code' => $service->service_code,
                'changes' => ActivityLogService::summarizeChanges(
                    $before,
                    $service->fresh()->only(['service_name', 'department_id', 'description', 'duration_minutes', 'status']),
                    ['service_name', 'department_id', 'description', 'duration_minutes', 'status']
                ),
            ]
        );

        return $service;
    }

    public function deleteService(Service $service): void
    {
        $snapshot = $service->only(['service_id', 'service_code', 'service_name', 'department_id']);

        DB::transaction(function () use ($service) {
            $service->prices()->delete();
            $service->delete();
        });

        ActivityLogService::log(
            'Admin xóa dịch vụ',
            'Admin ' . (Auth::user()?->full_name ?: '') . ' đã xóa dịch vụ ' . ($snapshot['service_name'] ?? '') . '.',
            'service',
            $snapshot['service_id'] ?? null,
            ['deleted_service' => $snapshot]
        );
    }

    public function toggleStatus(Service $service): Service
    {
        $before = $service->status;
        $service->update(['status' => !$service->status]);

        ActivityLogService::log(
            'Admin sửa dịch vụ',
            'Admin ' . (Auth::user()?->full_name ?: '') . ' đã ' . ($service->status ? 'kích hoạt' : 'vô hiệu hóa') . ' dịch vụ ' . $service->service_name . '.',
            'service',
            $service->service_id,
            [
                'changes' => [
                    'status' => [
                        'before' => $before,
                        'after' => $service->status,
                    ],
                ],
            ]
        );

        return $service;
    }

    // ----------------------------------------------------------------
    // Bảng giá
    // ----------------------------------------------------------------

    public function addPrice(Service $service, array $data): ServicePrice
    {
        return $service->prices()->create([
            'price_type'     => $data['price_type'],
            'price'          => $data['price'],
            'effective_date' => $data['effective_date'],
            'end_date'       => $data['end_date'] ?? null,
            'created_by'     => Auth::id(),
            'created_at'     => now(),
        ]);
    }

    public function updatePrice(ServicePrice $price, array $data): ServicePrice
    {
        $price->update([
            'price_type'     => $data['price_type'],
            'price'          => $data['price'],
            'effective_date' => $data['effective_date'],
            'end_date'       => $data['end_date'] ?? null,
        ]);
        return $price;
    }

    public function deletePrice(ServicePrice $price): void
    {
        $price->delete();
    }

    public function getActivePrice(Service $service, string $priceType): ?ServicePrice
    {
        return $service->activePrices()->where('price_type', $priceType)->first();
    }
}
