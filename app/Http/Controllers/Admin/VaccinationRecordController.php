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
        // Get upcoming schedules
        $upcomingSchedules = VaccinationRecord::with(['user', 'vaccine'])
            ->where('status', 'Chưa tiêm')
            ->whereNotNull('next_dose_date')
            ->orderBy('next_dose_date', 'asc')
            ->get();

        // Get selected patient
        $selectedPatientId = $request->patient_id;
        if (!$selectedPatientId && $upcomingSchedules->count() > 0) {
            $selectedPatientId = $upcomingSchedules->first()->user_id;
        }

        $selectedPatient = null;
        $patientRecords = collect();
        if ($selectedPatientId) {
            $selectedPatient = User::find($selectedPatientId);
            $patientRecords = VaccinationRecord::with(['vaccine', 'doctor'])
                ->where('user_id', $selectedPatientId)
                ->orderBy('administered_at', 'desc')
                ->orderBy('next_dose_date', 'asc')
                ->get();
        }

        $vaccines = Vaccine::where('status', 1)->get();
        
        // Search patients
        $search = $request->input('search');
        $patientsQuery = User::where('role_id', 3);
        if ($search) {
            $patientsQuery->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }
        $patients = $patientsQuery->limit(20)->get();

        return view('admin.vaccination_records.index', compact(
            'upcomingSchedules',
            'selectedPatient',
            'patientRecords',
            'vaccines',
            'patients',
            'search'
        ));
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
