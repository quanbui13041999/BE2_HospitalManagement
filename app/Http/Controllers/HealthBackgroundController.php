<?php

namespace App\Http\Controllers;

use App\Models\HealthBackground;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HealthBackgroundController extends Controller
{
    private const BLOOD_GROUPS = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

    private const RH_FACTORS = ['positive', 'negative'];

    private const CHRONIC_DISEASES = [
        'TĂNG HUYẾT ÁP',
        'TUỘT HUYẾT ÁP',
        'ĐÁI THÁO ĐƯỜNG',
        'TIM MẠCH',
        'MỠ MÁU CAO',
        'BỆNH THẬN MÃN',
        'BỆNH PHỔI MÃN TÍNH (COPD)',
        'HEN SUYỄN',
        'VIÊM LOÉT DẠ DÀY',
        'GAN NHIỄM MỠ',
        'VIÊM KHỚP',
        'LOÃNG XƯƠNG',
    ];

    public function index()
    {
        $healthData = HealthBackground::where('user_id', Auth::id())->first();
        $healthSnapshot = $this->makeSnapshot($healthData);

        return view('health_background.index', compact('healthData', 'healthSnapshot'));
    }

    public function store(Request $request)
    {
        $preflightResult = DB::transaction(function () use ($request) {
            return $this->detectWriteConflict($request);
        }, 3);

        if ($preflightResult !== 'ok') {
            return $this->redirectAfterWriteConflict($preflightResult);
        }

        $validated = $this->validateRequest($request);
        $validated = $this->normalizeTextFields($validated);
        $validated['user_id'] = Auth::id();
        $validated['bmi'] = $this->calculateBMI($validated['height'] ?? null, $validated['weight'] ?? null);

        $result = DB::transaction(function () use ($request, $validated) {
            $conflictResult = $this->detectWriteConflict($request);

            if ($conflictResult !== 'ok') {
                return $conflictResult;
            }

            $data = $this->mapData($validated);
            $current = HealthBackground::where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if ($current) {
                $current->fill($data);
                $current->save();
            } else {
                HealthBackground::create($data);
            }

            return 'saved';
        }, 3);

        if ($result !== 'saved') {
            return $this->redirectAfterWriteConflict($result);
        }

        return redirect()
            ->route('health.index')
            ->with('success', 'Cập nhật thành công!');
    }

    private function redirectAfterWriteConflict(string $result)
    {
        return match ($result) {
            'deleted' => redirect()
                ->route('Home.trangchu')
                ->with('error', 'Dữ liệu tiền sử sức khỏe không còn tồn tại. Hệ thống đã chuyển bạn về trang chủ, vui lòng mở lại chức năng để khai báo mới.'),
            'created_by_other' => redirect()
                ->route('health.index')
                ->with('warning', 'Dữ liệu đã được người khác thêm trước đó. Hệ thống đã tải lại dữ liệu mới nhất, vui lòng kiểm tra rồi lưu lại nếu cần.'),
            'changed_by_other' => redirect()
                ->route('health.index')
                ->with('warning', 'Dữ liệu đã được người khác cập nhật trước đó. Hệ thống đã tải lại dữ liệu mới nhất, vui lòng kiểm tra rồi lưu lại nếu cần.'),
            default => redirect()
                ->route('health.index')
                ->with('warning', 'Dữ liệu đã thay đổi. Hệ thống đã tải lại dữ liệu mới nhất, vui lòng kiểm tra rồi lưu lại nếu cần.'),
        };
    }

    private function validateRequest(Request $request): array
    {
        $positiveDecimalRule = ['nullable', 'regex:/^\d+(\.\d{1,2})?$/', 'numeric', 'gt:0'];
        $safeTextRule = ['nullable', 'string', 'max:1000', 'regex:/^(?=.*\pL)[\pL\s,.\-()\/]+$/u'];

        return $request->validate([
            'health_background_id' => ['nullable', 'integer'],
            'health_background_updated_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'health_background_snapshot' => ['nullable', 'string', 'size:64'],
            'nhommau' => ['required', Rule::in(self::BLOOD_GROUPS)],
            'yeuto_rh' => ['required', Rule::in(self::RH_FACTORS)],
            'height' => [...$positiveDecimalRule, 'max:300'],
            'weight' => [...$positiveDecimalRule, 'max:500'],
            'food_allergies' => $safeTextRule,
            'drug_allergies' => $safeTextRule,
            'chronic_diseases' => ['nullable', 'array'],
            'chronic_diseases.*' => ['string', Rule::in(self::CHRONIC_DISEASES)],
            'other_chronic_diseases' => $safeTextRule,
        ], [
            'height.numeric' => 'Chiều cao phải là số.',
            'height.regex' => 'Chiều cao chỉ được nhập số dương, tối đa 2 chữ số thập phân.',
            'height.gt' => 'Chiều cao phải là số dương.',
            'height.max' => 'Chiều cao không được lớn hơn 300 cm.',
            'weight.numeric' => 'Cân nặng phải là số.',
            'weight.regex' => 'Cân nặng chỉ được nhập số dương, tối đa 2 chữ số thập phân.',
            'weight.gt' => 'Cân nặng phải là số dương.',
            'weight.max' => 'Cân nặng không được lớn hơn 500 kg.',
            'food_allergies.regex' => 'Dị ứng thực phẩm chỉ được nhập chữ cái, khoảng trắng và dấu phân cách thông dụng; không nhập số hoặc ký tự lạ.',
            'drug_allergies.regex' => 'Dị ứng thuốc chỉ được nhập chữ cái, khoảng trắng và dấu phân cách thông dụng; không nhập số hoặc ký tự lạ.',
            'other_chronic_diseases.regex' => 'Bệnh mãn tính khác chỉ được nhập chữ cái, khoảng trắng và dấu phân cách thông dụng; không nhập số hoặc ký tự lạ.',
            'nhommau.in' => 'Nhóm máu không hợp lệ.',
            'yeuto_rh.in' => 'Yếu tố Rh không hợp lệ.',
            'chronic_diseases.*.in' => 'Bệnh mãn tính không hợp lệ.',
        ]);
    }

    private function detectWriteConflict(Request $request): string
    {
        $userId = Auth::id();

        DB::table('users')
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();

        $current = HealthBackground::where('user_id', $userId)
            ->lockForUpdate()
            ->first();

        $submittedId = $request->filled('health_background_id')
            ? (int) $request->input('health_background_id')
            : null;

        if ($submittedId !== null && !$current) {
            return 'deleted';
        }

        if ($submittedId === null && $current) {
            return 'created_by_other';
        }

        if ($submittedId !== null && (int) $current->getKey() !== $submittedId) {
            return 'changed_by_other';
        }

        if ($current && !$this->hasSameVersion($current, $request->input('health_background_updated_at'))) {
            return 'changed_by_other';
        }

        if ($current && !$this->hasSameSnapshot($current, $request->input('health_background_snapshot'))) {
            return 'changed_by_other';
        }

        return 'ok';
    }

    private function normalizeTextFields(array $data): array
    {
        foreach (['food_allergies', 'drug_allergies', 'other_chronic_diseases'] as $field) {
            $data[$field] = isset($data[$field]) && trim((string) $data[$field]) !== ''
                ? trim((string) $data[$field])
                : null;
        }

        $data['chronic_diseases'] = $data['chronic_diseases'] ?? [];

        return $data;
    }

    private function calculateBMI($height, $weight): float
    {
        if ($height > 0 && $weight > 0) {
            $heightInMeters = $height / 100;

            return round($weight / ($heightInMeters * $heightInMeters), 2);
        }

        return 0;
    }

    private function hasSameVersion(HealthBackground $healthBackground, ?string $submittedUpdatedAt): bool
    {
        if (!$submittedUpdatedAt || !$healthBackground->updated_at) {
            return false;
        }

        return $healthBackground->updated_at->format('Y-m-d H:i:s') === $submittedUpdatedAt;
    }

    private function hasSameSnapshot(HealthBackground $healthBackground, ?string $submittedSnapshot): bool
    {
        if (!$submittedSnapshot) {
            return false;
        }

        return hash_equals($this->makeSnapshot($healthBackground), $submittedSnapshot);
    }

    private function makeSnapshot(?HealthBackground $healthBackground): ?string
    {
        if (!$healthBackground) {
            return null;
        }

        $chronicDiseases = $healthBackground->chronic_diseases ?? [];
        sort($chronicDiseases);

        return hash('sha256', json_encode([
            'id' => $healthBackground->getKey(),
            'user_id' => $healthBackground->user_id,
            'blood_group' => $healthBackground->blood_group,
            'yeuto_rh' => $healthBackground->yeuto_rh,
            'height' => $healthBackground->height,
            'weight' => $healthBackground->weight,
            'bmi' => $healthBackground->bmi,
            'food_allergies' => $healthBackground->food_allergies,
            'drug_allergies' => $healthBackground->drug_allergies,
            'chronic_diseases' => $chronicDiseases,
            'other_chronic_diseases' => $healthBackground->other_chronic_diseases,
            'updated_at' => optional($healthBackground->updated_at)->format('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    private function mapData(array $data): array
    {
        return [
            'user_id' => $data['user_id'],
            'blood_group' => $data['nhommau'],
            'yeuto_rh' => $data['yeuto_rh'],
            'height' => $data['height'] ?? null,
            'weight' => $data['weight'] ?? null,
            'bmi' => $data['bmi'],
            'food_allergies' => $data['food_allergies'],
            'drug_allergies' => $data['drug_allergies'],
            'chronic_diseases' => $data['chronic_diseases'] ?? [],
            'other_chronic_diseases' => $data['other_chronic_diseases'],
        ];
    }

    public function showPatient(int $patientId)
    {
        $user = Auth::user();
        $isDoctor = in_array($user->role_id ?? 0, [1, 2], true);

        if (!$isDoctor) {
            abort(403, 'Không có quyền xem hồ sơ này');
        }

        $healthData = HealthBackground::where('user_id', $patientId)->first();
        $healthSnapshot = $this->makeSnapshot($healthData);
        $patient = User::findOrFail($patientId);

        return view('health_background.index', compact('healthData', 'healthSnapshot', 'patient'));
    }
}
