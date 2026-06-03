<?php

namespace App\Http\Controllers;

use App\Http\Requests\HealthTrackingRequest;
use App\Models\HealthTracking;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Services\HealthRiskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $query = HealthTracking::with('patient')
            ->when($this->isPatientUser($user), fn($q) => $q->where('patient_id', $user->user_id))
            ->when($this->isDoctorUser($user), function ($q) use ($user) {
                $q->whereIn('patient_id', MedicalRecord::query()
                    ->select('patient_id')
                    ->where('doctor_id', $user->user_id)
                    ->whereNotNull('patient_id')
                    ->distinct());
            })
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

        $validated = $request->validated();
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $patientId = $user->user_id;
        $createMinute = now()->format('Y-m-d H:i');
        $lockKey = $this->lockKey('health_tracking_create', $patientId . '|' . $createMinute);

        if (! $this->acquireHealthLock($lockKey)) {
            return back()->withInput()
                ->with('warning', 'Đang có người lưu nhật ký trong cùng phút này. Vui lòng tải lại trang rồi kiểm tra lại danh sách.');
        }

        try {
            $tracking = DB::transaction(function () use ($validated, $patientId, $createMinute) {
                if ($this->hasTrackingInMinute($patientId, $createMinute)) {
                    return null;
                }

                $risk = $this->riskService->analyze($validated);

                return HealthTracking::create($validated + [
                    'patient_id'    => $patientId,
                    'risk_level'    => $risk['risk_level'],
                    'risk_warnings' => $risk['risk_warnings'],
                    'version'       => 1,
                ]);
            });

            if (! $tracking) {
                return back()->withInput()
                    ->with('warning', 'Bệnh nhân này đã có nhật ký sức khỏe được lưu trong cùng phút. Vui lòng tải lại danh sách.');
            }

            return redirect()->route('health-tracking.show', $tracking)
                ->with('success', 'Đã lưu nhật ký sức khỏe.')
                ->with('risk_warnings', $tracking->risk_warnings ?? []);
        } catch (\Throwable $e) {
            Log::error('HealthTracking store failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Có lỗi khi lưu dữ liệu, vui lòng thử lại.');
        } finally {
            $this->releaseHealthLock($lockKey);
        }
    }

    public function show(int $healthTracking)
    {
        $healthTracking = HealthTracking::find($healthTracking);

        if (! $healthTracking) {
            return redirect()->route('health-tracking.index')
                ->with('warning', 'Nhật ký sức khỏe đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        $this->authorize('view', $healthTracking);
        return view('health-tracking.show', ['tracking' => $healthTracking]);
    }

    public function edit(int $healthTracking)
    {
        $healthTracking = HealthTracking::find($healthTracking);

        if (! $healthTracking) {
            return redirect()->route('health-tracking.index')
                ->with('warning', 'Nhật ký sức khỏe đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        $this->authorize('update', $healthTracking);
        return view('health-tracking.edit', ['tracking' => $healthTracking]);
    }

    public function update(HealthTrackingRequest $request, int $healthTracking)
    {
        $existing = HealthTracking::find($healthTracking);

        if (! $existing) {
            return redirect()->route('health-tracking.index')
                ->with('warning', 'Nhật ký sức khỏe đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        $this->authorize('update', $existing);

        try {
            DB::beginTransaction();

            $current = HealthTracking::lockForUpdate()->find($healthTracking);

            if (! $current) {
                DB::rollBack();

                return redirect()->route('health-tracking.index')
                    ->with('warning', 'Nhật ký sức khỏe đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
            }

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
            Log::error('HealthTracking update failed', ['id' => $healthTracking, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Có lỗi khi cập nhật, vui lòng thử lại.');
        }
    }

    public function destroy(int $healthTracking)
    {
        $existing = HealthTracking::find($healthTracking);

        if (! $existing) {
            return redirect()->route('health-tracking.index')
                ->with('warning', 'Nhật ký sức khỏe đã được người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        $this->authorize('delete', $existing);

        $lockKey = $this->lockKey('health_tracking_delete', (string) $healthTracking);

        if (! $this->acquireHealthLock($lockKey)) {
            return redirect()->route('health-tracking.index')
                ->with('warning', 'Đang có người xóa nhật ký này. Vui lòng tải lại danh sách.');
        }

        try {
            DB::beginTransaction();

            $current = HealthTracking::lockForUpdate()->find($healthTracking);

            if (! $current) {
                DB::rollBack();

                return redirect()->route('health-tracking.index')
                    ->with('warning', 'Nhật ký sức khỏe đã được người khác xóa trước đó. Vui lòng tải lại danh sách.');
            }

            $this->authorize('delete', $current);
            $current->delete();

            DB::commit();

            return redirect()->route('health-tracking.index')
                ->with('success', 'Đã xóa nhật ký sức khỏe.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('HealthTracking delete failed', ['id' => $healthTracking, 'error' => $e->getMessage()]);

            return redirect()->route('health-tracking.index')
                ->with('error', 'Có lỗi khi xóa nhật ký, vui lòng thử lại.');
        } finally {
            $this->releaseHealthLock($lockKey);
        }
    }

    // API realtime risk check (dùng cho JS trên form)
    public function checkRisk(Request $request)
    {
        $data     = $request->only(['systolic', 'diastolic', 'heart_rate', 'spo2', 'blood_sugar']);
        $warnings = $this->riskService->detectWarnings(array_filter($data, fn($v) => is_numeric($v)));
        return response()->json(['warnings' => $warnings]);
    }

    private function hasTrackingInMinute(int $patientId, string $minute): bool
    {
        $from = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $minute)->startOfMinute();
        $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $minute)->endOfMinute();

        return HealthTracking::where('patient_id', $patientId)
            ->whereBetween('created_at', [$from, $to])
            ->exists();
    }

    private function lockKey(string $prefix, string $value): string
    {
        return 'health:' . sha1($prefix . '|' . $value);
    }

    private function acquireHealthLock(string $lockKey): bool
    {
        $result = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockKey]);

        return (int) ($result->acquired ?? 0) === 1;
    }

    private function releaseHealthLock(string $lockKey): void
    {
        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockKey]);
    }

    private function isPatientUser(?User $user): bool
    {
        return $user?->isPatient() ?? false;
    }

    private function isDoctorUser(?User $user): bool
    {
        return $user?->isDoctor() ?? false;
    }
}
