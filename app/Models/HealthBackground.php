<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HealthBackground extends Model
{
    public const BLOOD_GROUPS = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
    public const RH_FACTORS = ['positive', 'negative'];
    public const CHRONIC_DISEASES = [
        'TĂNG HUYẾT ÁP',
        'TUỘT HUYẾT ÁP',
        'ĐÁI THÁO ĐƯỜNG',
        'TIM MẠCH',
        'MỠ MÁU CAO',
        'BỆNH THẬN MẠN',
        'BỆNH PHỔI MẠN TÍNH (COPD)',
        'HEN SUYỄN',
        'VIÊM LOÉT DẠ DÀY',
        'GAN NHIỄM MỠ',
        'VIÊM KHỚP',
        'LOÃNG XƯƠNG',
    ];

    protected $table = 'health_backgrounds';

    protected $fillable = [
        'user_id',
        'blood_group',
        'yeuto_rh',
        'height',
        'weight',
        'bmi',
        'food_allergies',
        'drug_allergies',
        'chronic_diseases',
        'other_chronic_diseases',
    ];

    protected $casts = [
        'height' => 'float',
        'weight' => 'float',
        'bmi' => 'float',
        'chronic_diseases' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function rules(): array
    {
        return [
            'nhommau' => ['nullable', Rule::in(self::BLOOD_GROUPS)],
            'yeuto_rh' => ['nullable', Rule::in(self::RH_FACTORS)],
            'height' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/', 'numeric', 'gt:0', 'max:300'],
            'weight' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/', 'numeric', 'gt:0', 'max:500'],
            'food_allergies' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'drug_allergies' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s,.\-\/()]+$/u'],
            'chronic_diseases' => ['nullable', 'array'],
            'chronic_diseases.*' => ['string', Rule::in(self::CHRONIC_DISEASES)],
            'other_chronic_diseases' => ['nullable', 'string', 'max:500', 'regex:/^[\pL\s,.\-\/()]+$/u'],
            'health_background_id' => ['nullable', 'integer'],
            'health_background_updated_at' => ['nullable', 'date'],
        ];
    }

    public static function messages(): array
    {
        return [
            'height.regex' => 'Chiều cao chỉ được nhập số dương, tối đa 2 chữ số thập phân.',
            'height.gt' => 'Chiều cao phải lớn hơn 0.',
            'height.max' => 'Chiều cao không được lớn hơn 300 cm.',
            'weight.regex' => 'Cân nặng chỉ được nhập số dương, tối đa 2 chữ số thập phân.',
            'weight.gt' => 'Cân nặng phải lớn hơn 0.',
            'weight.max' => 'Cân nặng không được lớn hơn 500 kg.',
            'food_allergies.max' => 'Dị ứng thực phẩm không được vượt quá 100 ký tự.',
            'food_allergies.regex' => 'Dị ứng thực phẩm chỉ được nhập chữ cái và khoảng trắng, không được nhập số hoặc ký tự đặc biệt.',
            'drug_allergies.regex' => 'Dị ứng thuốc chỉ được nhập chữ và dấu phân tách thông dụng.',
            'other_chronic_diseases.regex' => 'Bệnh mãn tính khác chỉ được nhập chữ và dấu phân tách thông dụng.',
            'chronic_diseases.*.in' => 'Bệnh mãn tính đã chọn không hợp lệ.',
        ];
    }

    public static function saveForUser(int $userId, array $validated): array
    {
        return DB::transaction(function () use ($userId, $validated): array {
            User::where('user_id', $userId)->lockForUpdate()->firstOrFail();

            $current = self::where('user_id', $userId)->lockForUpdate()->first();
            $conflict = self::detectWriteConflict($validated, $current);

            if ($conflict) {
                return ['saved' => false, 'message' => $conflict];
            }

            $payload = self::mapFormData($validated, $userId);

            if ($current) {
                $current->fill($payload);
                $current->save();
            } else {
                self::create($payload);
            }

            return ['saved' => true, 'message' => 'Cập nhật thông tin tiền sử sức khỏe thành công!'];
        });
    }

    public static function viewOptions(): array
    {
        return [
            'bloodGroups' => self::BLOOD_GROUPS,
            'rhFactors' => self::RH_FACTORS,
            'chronicDiseaseOptions' => self::CHRONIC_DISEASES,
        ];
    }

    private static function detectWriteConflict(array $data, ?self $current): ?string
    {
        $submittedId = $data['health_background_id'] ?? null;
        $submittedVersion = $data['health_background_updated_at'] ?? null;

        if (! $submittedId && $current) {
            return 'Thông tin tiền sử đã được người khác tạo trước đó. Vui lòng tải lại dữ liệu mới nhất rồi nhập lại.';
        }

        if ($submittedId && ! $current) {
            return 'Thông tin tiền sử hiện tại đã bị xóa hoặc thay đổi trước đó. Vui lòng tải lại trang để cập nhật dữ liệu mới nhất.';
        }

        if ($submittedId && $current && (int) $submittedId !== (int) $current->id) {
            return 'Thông tin tiền sử đã được thay đổi trước đó. Vui lòng tải lại dữ liệu mới nhất.';
        }

        if ($current && $submittedVersion !== optional($current->updated_at)->toDateTimeString()) {
            return 'Thông tin tiền sử đã có người sửa trước đó. Vui lòng tải lại dữ liệu mới nhất trước khi lưu.';
        }

        return null;
    }

    private static function mapFormData(array $data, int $userId): array
    {
        $height = self::nullableFloat($data['height'] ?? null);
        $weight = self::nullableFloat($data['weight'] ?? null);

        return [
            'user_id' => $userId,
            'blood_group' => $data['nhommau'] ?? null,
            'yeuto_rh' => $data['yeuto_rh'] ?? null,
            'height' => $height,
            'weight' => $weight,
            'bmi' => self::calculateBMI($height, $weight),
            'food_allergies' => self::normalizeText($data['food_allergies'] ?? null),
            'drug_allergies' => self::normalizeText($data['drug_allergies'] ?? null),
            'chronic_diseases' => array_values($data['chronic_diseases'] ?? []),
            'other_chronic_diseases' => self::normalizeText($data['other_chronic_diseases'] ?? null),
        ];
    }

    private static function calculateBMI(?float $height, ?float $weight): ?float
    {
        if (! $height || ! $weight) {
            return null;
        }

        $heightInMeters = $height / 100;

        return round($weight / ($heightInMeters * $heightInMeters), 2);
    }

    private static function nullableFloat(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }

    private static function normalizeText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : preg_replace('/\s+/u', ' ', $value);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
