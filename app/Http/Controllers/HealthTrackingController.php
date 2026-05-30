<?php

namespace App\Http\Controllers;

use App\Http\Requests\HealthTrackingRequest;
use App\Models\HealthTracking;
use App\Services\HealthRiskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthTrackingController extends Controller
{
    public function __construct(private readonly HealthRiskService $riskService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', HealthTracking::class);

        $query = HealthTracking::with('patient')
            ->when(auth()->user()->isPatient(), fn($q) => $q->where('patient_id', auth()->user()->user_id))
            ->when($request->risk_level, fn($q) => $q->where('risk_level', $request->risk_level))
            ->when($request->date_from,  fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,    fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

        $summary = [
            'total' => (clone $query)->count(),
            'normal' => (clone $query)->where('risk_level', 'normal')->count(),
            'warning' => (clone $query)->where('risk_level', 'warning')->count(),
            'danger' => (clone $query)->where('risk_level', 'danger')->count(),
        ];

        $trackings = $query->latest()->paginate(10)->withQueryString();

        return view('health-tracking.index', compact('trackings', 'summary'));
    }

    public function create()
    {
        $this->authorize('create', HealthTracking::class);
        return view('health-tracking.create');
    }

    public function store(HealthTrackingRequest $request)
    {
        $this->authorize('create', HealthTracking::class);

        try {
            $risk    = $this->riskService->analyze($request->validated());
            $tracking = HealthTracking::create($request->validated() + [
                'patient_id'    => auth()->user()->user_id,
                'risk_level'    => $risk['risk_level'],
                'risk_warnings' => $risk['risk_warnings'],
                'version'       => 1,
            ]);

            return redirect()->route('health-tracking.show', $tracking)
                ->with('success', 'Đã lưu nhật ký sức khỏe.')
                ->with('risk_warnings', $risk['risk_warnings']);
        } catch (\Throwable $e) {
            Log::error('HealthTracking store failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Có lỗi khi lưu dữ liệu, vui lòng thử lại.');
        }
    }

    public function show(HealthTracking $healthTracking)
    {
        $this->authorize('view', $healthTracking);
        return view('health-tracking.show', ['tracking' => $healthTracking]);
    }

    public function edit(HealthTracking $healthTracking)
    {
        $this->authorize('update', $healthTracking);
        return view('health-tracking.edit', ['tracking' => $healthTracking]);
    }

    public function update(HealthTrackingRequest $request, HealthTracking $healthTracking)
    {
        $this->authorize('update', $healthTracking);

        try {
            DB::beginTransaction();

            $current = HealthTracking::lockForUpdate()->findOrFail($healthTracking->id);

            // Optimistic locking: version không khớp = người khác đã sửa trước
            if ($current->version !== (int) $request->version) {
                DB::rollBack();
                return back()->withInput()
                    ->with('conflict', true)
                    ->with('conflict_message',
                        'Dữ liệu đã được chỉnh sửa bởi người khác lúc '
                        . $current->updated_at->format('H:i:s d/m/Y')
                        . '. Vui lòng tải lại trang để xem dữ liệu mới nhất trước khi chỉnh sửa.'
                    );
            }

            $risk = $this->riskService->analyze($request->validated());
            $current->update($request->safe()->except('version') + [
                'risk_level'    => $risk['risk_level'],
                'risk_warnings' => $risk['risk_warnings'],
                'version'       => $current->version + 1,
            ]);

            DB::commit();

            return redirect()->route('health-tracking.show', $current)
                ->with('success', 'Cập nhật nhật ký thành công.')
                ->with('risk_warnings', $risk['risk_warnings']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('HealthTracking update failed', ['id' => $healthTracking->id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Có lỗi khi cập nhật, vui lòng thử lại.');
        }
    }

    public function destroy(HealthTracking $healthTracking)
    {
        $this->authorize('delete', $healthTracking);
        $healthTracking->delete();
        return redirect()->route('health-tracking.index')
            ->with('success', 'Đã xóa nhật ký sức khỏe.');
    }

    // API realtime risk check (dùng cho JS trên form)
    public function checkRisk(Request $request)
    {
        $data     = $request->only(['systolic', 'diastolic', 'heart_rate', 'spo2', 'blood_sugar']);
        $warnings = $this->riskService->detectWarnings(array_filter($data, fn($v) => is_numeric($v)));
        return response()->json(['warnings' => $warnings]);
    }
}
