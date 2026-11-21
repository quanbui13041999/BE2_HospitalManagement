<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVaccineRequest;
use App\Http\Requests\Admin\UpdateVaccineRequest;
use App\Models\Vaccine;
use App\Services\Admin\VaccineService;

class VaccineController extends Controller
{
    public function __construct(protected VaccineService $vaccineService) {}

    public function index()
    {
        $vaccines = $this->vaccineService->getPaginated(10);

        return view('admin.vaccines.index', compact('vaccines'));
    }

    public function create()
    {
        return view('admin.vaccines.create');
    }

    public function store(StoreVaccineRequest $request)
    {
        $this->vaccineService->createVaccine($request->validated());

        return redirect()->route('admin.vaccines.index')->with('success', 'Thêm vắc xin thành công.');
    }

    public function edit(Vaccine $vaccine)
    {
        return view('admin.vaccines.edit', compact('vaccine'));
    }

    public function update(UpdateVaccineRequest $request, Vaccine $vaccine)
    {
        $this->vaccineService->updateVaccine($vaccine, $request->validated());

        return redirect()->route('admin.vaccines.index')->with('success', 'Cập nhật vắc xin thành công.');
    }

    public function destroy(Vaccine $vaccine)
    {
        try {
            $this->vaccineService->deleteVaccine($vaccine);

            return redirect()->route('admin.vaccines.index')->with('success', 'Xóa vắc xin thành công.');
        } catch (\Exception $e) {
            return redirect()->route('admin.vaccines.index')->with('error', 'Không thể xóa vắc xin này vì đã có dữ liệu liên quan.');
        }
    }
}
