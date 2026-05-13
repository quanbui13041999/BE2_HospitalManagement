<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VaccinationRecord;
use App\Models\Vaccine;
use App\Models\User;
use App\Models\Doctor;
use App\Services\Admin\VaccinationRecordService;
use App\Http\Requests\Admin\StoreVaccinationRecordRequest;
use App\Http\Requests\Admin\UpdateVaccinationRecordRequest;
use Illuminate\Http\Request;

class VaccinationRecordController extends Controller
{
    public function __construct(protected VaccinationRecordService $recordService) {}

    public function index(Request $request)
    {
        $records = $this->recordService->getPaginatedRecords($request->status, 15);
        return view('admin.vaccination_records.index', compact('records'));
    }

    public function create()
    {
        $patients = User::where('role_id', 3)->get();
        $vaccines = Vaccine::where('status', 1)->get();
        $doctors = Doctor::where('status', 1)->get();

        return view('admin.vaccination_records.create', compact('patients', 'vaccines', 'doctors'));
    }

    public function store(StoreVaccinationRecordRequest $request)
    {
        $this->recordService->createRecord($request->validated());

        return redirect()->route('admin.vaccination-records.index')->with('success', 'Thêm hồ sơ tiêm chủng thành công.');
    }

    public function edit(VaccinationRecord $vaccination_record)
    {
        $patients = User::where('role_id', 3)->get();
        $vaccines = Vaccine::where('status', 1)->get();
        $doctors = Doctor::where('status', 1)->get();

        return view('admin.vaccination_records.edit', compact('vaccination_record', 'patients', 'vaccines', 'doctors'));
    }

    public function update(UpdateVaccinationRecordRequest $request, VaccinationRecord $vaccination_record)
    {
        $this->recordService->updateRecord($vaccination_record, $request->validated());

        return redirect()->route('admin.vaccination-records.index')->with('success', 'Cập nhật hồ sơ tiêm chủng thành công.');
    }

    public function destroy(VaccinationRecord $vaccination_record)
    {
        $this->recordService->deleteRecord($vaccination_record);
        return redirect()->route('admin.vaccination-records.index')->with('success', 'Xóa hồ sơ tiêm chủng thành công.');
    }
}
