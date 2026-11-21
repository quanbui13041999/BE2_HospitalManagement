<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Throwable;

class PatientSearchController extends Controller
{
    /**
     * Lấy thống kê tổng quan của bệnh viện
     */
    private function getStats()
    {
        return [
            'total_patients' => User::where('role_id', 3)->count(),
            'total_appointments' => Appointment::count(),
            'appointments_today' => Appointment::whereDate('appointment_time', today())->count(),
            'new_patients_month' => User::where('role_id', 3)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Hiển thị trang tìm kiếm bệnh nhân nâng cao
     */
    public function index()
    {
        $stats = $this->getStats();
        return view('admin.patients.search', compact('stats'));
    }

    /**
     * Tìm kiếm bệnh nhân nâng cao (AJAX)
     */
    public function search(Request $request)
    {
        $filters = $request->validate([
            'keyword' => 'nullable|string|max:100',
            'gender' => ['nullable', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'age_from' => 'nullable|integer|min:0|max:130',
            'age_to' => 'nullable|integer|min:0|max:130|gte:age_from',
            'registered_from' => 'nullable|date',
            'registered_to' => 'nullable|date|after_or_equal:registered_from',
            'status' => 'nullable|in:0,1',
            'department_id' => 'nullable|integer|exists:departments,department_id',
            'doctor_id' => 'nullable|integer|exists:doctors,doctor_id',
            'appointment_status' => [
                'nullable',
                Rule::in(['Chờ xác nhận', 'Đã xác nhận', 'Hoàn thành', 'Đã hủy', 'Đã thanh toán', 'Bác sĩ nghỉ']),
            ],
            'appointment_from' => 'nullable|date',
            'appointment_to' => 'nullable|date|after_or_equal:appointment_from',
            'membership_tier' => ['nullable', Rule::in(['Đồng', 'Bạc', 'Vàng', 'Kim Cương', 'Thường'])],
            'has_insurance' => 'nullable|in:0,1',
            'chronic_disease' => 'nullable|string|max:100',
            'allergy' => 'nullable|string|max:100',
            'sort_by' => 'nullable|in:full_name,created_at,date_of_birth,user_id',
            'sort_dir' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]); /* fixed: validate toan bo filter, khong trust query thô */

        $filters = collect($filters)
            ->reject(fn($value) => $value === null || $value === '')
            ->all(); /* fixed: bo qua filter rong tu form, tranh mat danh sach benh nhan khi chon "Tat ca" */

        $query = User::query()
            ->where('role_id', 3) // Chỉ lấy bệnh nhân
            ->with([
                'appointments' => fn($q) => $q->latest()->limit(3)->with('schedule.doctor'),
                'insuranceCards',
                'membershipCard',
                'patientAllergies',
                'patientMedicalHistories',
            ]);

        // -- 1. Từ khóa tổng quát (tìm trong nhiều trường cùng lúc)
        if ($keyword = ($filters['keyword'] ?? null)) {
            $keyword = trim($keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('full_name', 'LIKE', "%{$keyword}%")
                  ->orWhere('email', 'LIKE', "%{$keyword}%")
                  ->orWhere('phone', 'LIKE', "%{$keyword}%")
                  ->orWhere('address', 'LIKE', "%{$keyword}%")
                  ->orWhere('user_id', $keyword); // tìm theo ID
            });
        }

        // -- 2. Lọc theo giới tính
        if ($gender = ($filters['gender'] ?? null)) {
            $query->where('gender', $gender);
        }

        // -- 3. Lọc theo khoảng tuổi
        if ($ageFrom = ($filters['age_from'] ?? null)) {
            $query->whereDate('date_of_birth', '<=', now()->subYears((int)$ageFrom));
        }
        if ($ageTo = ($filters['age_to'] ?? null)) {
            $query->whereDate('date_of_birth', '>=', now()->subYears((int)$ageTo + 1)->addDay());
        }

        // -- 4. Lọc theo ngày đăng ký (bệnh nhân mới/cũ)
        if ($regFrom = ($filters['registered_from'] ?? null)) {
            $query->whereDate('created_at', '>=', $regFrom);
        }
        if ($regTo = ($filters['registered_to'] ?? null)) {
            $query->whereDate('created_at', '<=', $regTo);
        }

        // -- 5. Lọc theo trạng thái tài khoản
        if (array_key_exists('status', $filters)) {
            $query->where('status', $filters['status']);
        }

        // -- 6. Lọc theo khoa khám
        if ($deptId = ($filters['department_id'] ?? null)) {
            $query->whereHas('appointments.schedule.doctor', function ($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
        }

        // -- 7. Lọc theo bác sĩ đã từng khám
        if ($doctorId = ($filters['doctor_id'] ?? null)) {
            $query->whereHas('appointments.schedule', function ($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId);
            });
        }

        // -- 8. Lọc theo trạng thái lịch hẹn
        if ($apptStatus = ($filters['appointment_status'] ?? null)) {
            $query->whereHas('appointments', function ($q) use ($apptStatus) {
                $q->where('status', $apptStatus);
            });
        }

        // -- 9. Lọc theo khoảng thời gian có lịch khám
        if ($apptFrom = ($filters['appointment_from'] ?? null)) {
            $query->whereHas('appointments', function ($q) use ($apptFrom) {
                $q->whereDate('appointment_time', '>=', $apptFrom);
            });
        }
        if ($apptTo = ($filters['appointment_to'] ?? null)) {
            $query->whereHas('appointments', function ($q) use ($apptTo) {
                $q->whereDate('appointment_time', '<=', $apptTo);
            });
        }

        // -- 10. Lọc theo hạng thẻ thành viên
        if ($tier = ($filters['membership_tier'] ?? null)) {
            $query->whereHas('membershipCard', fn($q) => $q->where('tier', $tier));
        }

        // -- 11. Lọc theo bảo hiểm còn hạn
        if (($filters['has_insurance'] ?? null) === '1') {
            $query->whereHas('insuranceCards', fn($q) => $q->where('status', 'Còn hạn'));
        }

        // -- 12. Lọc theo bệnh mãn tính (tìm trong patientmedicalhistory)
        if ($chronic = ($filters['chronic_disease'] ?? null)) {
            $query->whereHas('patientMedicalHistories', function ($q) use ($chronic) {
                $q->where('is_chronic', 1)->where('condition', 'LIKE', "%{$chronic}%");
            });
        }

        // -- 13. Lọc theo dị ứng
        if ($allergy = ($filters['allergy'] ?? null)) {
            $query->whereHas('patientAllergies', function ($q) use ($allergy) {
                $q->where('allergen', 'LIKE', "%{$allergy}%");
            });
        }

        // -- 14. Sắp xếp
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir); /* fixed: sort_by/sort_dir whitelist de tranh SQL injection qua orderBy */

        // -- 15. Phân trang
        $perPage = (int)($filters['per_page'] ?? 12);
        $patients = $query->paginate($perPage)->withQueryString();

        // Tính toán thêm thông tin cho từng bệnh nhân
        $patients->getCollection()->transform(function ($patient) {
            $patient->age = $patient->date_of_birth
                ? Carbon::parse($patient->date_of_birth)->age
                : null;
            $patient->total_appointments = $patient->appointments->count();
            $patient->last_appointment = $patient->appointments->first();
            return $patient;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.patients.partials.patient-card', compact('patients'))->render(),
                'total' => $patients->total(),
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
            ]);
        }

        $stats = $this->getStats();
        return view('admin.patients.search', compact('patients', 'stats'));
    }

    /**
     * Chi tiết hồ sơ bệnh nhân (AJAX modal)
     */
    public function detail(int $id)
    {
        $patient = User::where('role_id', 3)
            ->with([
                'appointments.schedule.doctor.department',
                'insuranceCards',
                'membershipCard',
                'patientAllergies',
                'patientMedicalHistories',
                'medicalRecords.doctor',
            ])
            ->findOrFail($id);

        $patient->age = $patient->date_of_birth
            ? Carbon::parse($patient->date_of_birth)->age
            : null;

        // Thống kê bệnh nhân
        $patient->stats = [
            'total_appointments' => $patient->appointments->count(),
            'completed' => $patient->appointments->where('status', 'Hoàn Thành')->count(),
            'cancelled' => $patient->appointments->where('status', 'Đã hủy')->count(),
            'upcoming' => $patient->appointments->where('status', 'Chờ xác nhận')->count(),
        ];

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.patients.partials.patient-detail', compact('patient'))->render(),
            ]);
        }

        return view('admin.patients.search', compact('patient'));
    }

    /**
     * Tìm kiếm bằng AI (gọi Google Gemini API)
     */
    public function aiSearch(Request $request)
    {
        $request->validate(['query' => 'required|string|max:500']);

        $userQuery = $request->get('query');

        // Chuẩn bị context cho AI
        $systemPrompt = <<<SYSTEM
Bạn là hệ thống phân tích truy vấn tìm kiếm bệnh nhân thông minh cho phần mềm quản lý bệnh viện.

Khi người dùng nhập mô tả bằng ngôn ngữ tự nhiên (tiếng Việt hoặc tiếng Anh), hãy phân tích và trích xuất các tham số tìm kiếm.

Trả về ĐÚNG JSON (không có text thêm), theo cấu trúc:
{
  "keyword": "từ khóa tên/email/phone nếu có",
  "gender": "Nam|Nữ|Khác hoặc null",
  "age_from": số hoặc null,
  "age_to": số hoặc null,
  "appointment_status": "Hoàn Thành|Đã hủy|Chờ xác nhận|Đã Khám|Đã thanh toán hoặc null",
  "has_insurance": "1 hoặc null",
  "membership_tier": "Đồng|Bạc|Vàng|Kim Cương|Thường hoặc null",
  "chronic_disease": "tên bệnh hoặc null",
  "allergy": "tên dị ứng hoặc null",
  "sort_by": "created_at|full_name|date_of_birth hoặc null",
  "sort_dir": "asc|desc",
  "explanation": "Giải thích ngắn gọn bằng tiếng Việt những gì bạn đã hiểu từ câu hỏi"
}

Ví dụ:
- "bệnh nhân nữ trên 50 tuổi bị tiểu đường" → gender: Nữ, age_from: 50, chronic_disease: tiểu đường
- "bệnh nhân có bảo hiểm hạng vàng chưa khám lần nào" → has_insurance: 1, membership_tier: Vàng
- "tìm bệnh nhân tên Lan" → keyword: Lan
SYSTEM;

        try {
            $apiKey = config('services.gemini.api_key');
            if (empty($apiKey)) {
                $filters = $this->fallbackAiFilters($userQuery);

                return response()->json([
                    'success' => true,
                    'filters' => $filters,
                    'explanation' => $filters['explanation'],
                    'fallback' => true,
                    'message' => 'AI tạm thời chưa cấu hình, hệ thống đã dùng bộ phân tích nội bộ.',
                ]);
            }

            $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

            $payload = [
                'system_instruction' => [
                    'parts' => [['text' => $systemPrompt]]
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [['text' => $userQuery]]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 1000,
                    'responseMimeType' => 'application/json'
                ]
            ];

            $caBundle = config('services.gemini.ca_bundle');
            $tlsOptions = $caBundle && is_file($caBundle) ? ['verify' => $caBundle] : [];

            $response = $this->callGeminiWithRetry($apiUrl, $apiKey, $payload, $tlsOptions); /* fixed: retry khi Gemini 503/timeout */

            if ($response->failed()) {
                throw new \Exception('Gemini API request failed: ' . $response->body());
            }

            $content = $response->json('candidates.0.content.parts.0.text', '{}');
            
            // Làm sạch JSON (nếu có markdown code blocks)
            $content = preg_replace('/```json|```/', '', $content);
            $filters = json_decode(trim($content), true);

            if (!$filters || !is_array($filters)) {
                throw new \Exception('AI không thể phân tích câu hỏi hoặc cấu trúc JSON không hợp lệ');
            }

            $filters = $this->normalizeAiFilters($filters, $userQuery); /* fixed: chuan hoa output AI truoc khi day vao form */

            return response()->json([
                'success' => true,
                'filters' => $filters,
                'explanation' => $filters['explanation'] ?? 'Đã phân tích thành công',
            ]);

        } catch (Throwable $e) {
            Log::warning('AI Patient Search fallback used', [
                'error' => preg_replace('/([?&]key=)[^\s&)]+/i', '$1[redacted]', $e->getMessage()) ?? 'Gemini request failed',
            ]); /* fixed: Gemini chap chon thi fallback thay vi tra loi 500 */

            $filters = $this->fallbackAiFilters($userQuery);

            return response()->json([
                'success' => true,
                'filters' => $filters,
                'explanation' => $filters['explanation'],
                'fallback' => true,
                'message' => 'AI đang phản hồi không ổn định, hệ thống đã dùng bộ phân tích nội bộ.',
            ]);
        }
    }

    private function callGeminiWithRetry(string $apiUrl, string $apiKey, array $payload, array $tlsOptions)
    {
        $lastException = null;
        $response = null;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = Http::withQueryParameters(['key' => $apiKey])
                    ->timeout(25)
                    ->connectTimeout(8)
                    ->withOptions($tlsOptions)
                    ->post($apiUrl, $payload);

                if (!$response->serverError() && $response->status() !== 429) {
                    return $response;
                }
            } catch (Throwable $e) {
                $lastException = $e;
            }

            usleep($attempt * 300000);
        }

        if ($response) {
            return $response;
        }

        throw $lastException ?? new \RuntimeException('Gemini request failed');
    }

    private function normalizeAiFilters(array $filters, string $query): array
    {
        $allowedStatus = [
            'Chờ xác nhận' => 'Chờ xác nhận',
            'Đã xác nhận' => 'Đã xác nhận',
            'Hoàn thành' => 'Hoàn thành',
            'Hoàn Thành' => 'Hoàn thành',
            'Đã hủy' => 'Đã hủy',
            'Đã thanh toán' => 'Đã thanh toán',
            'Bác sĩ nghỉ' => 'Bác sĩ nghỉ',
        ];
        $allowedGender = ['Nam' => 'Nam', 'Nữ' => 'Nữ', 'Khác' => 'Khác'];
        $allowedTier = ['Đồng' => 'Đồng', 'Bạc' => 'Bạc', 'Vàng' => 'Vàng', 'Kim Cương' => 'Kim Cương', 'Thường' => 'Thường'];
        $allowedSort = ['created_at', 'full_name', 'date_of_birth', 'user_id'];

        $normalized = [
            'keyword' => $this->shortText($filters['keyword'] ?? null, 100),
            'gender' => $allowedGender[$filters['gender'] ?? ''] ?? null,
            'age_from' => $this->intRange($filters['age_from'] ?? null, 0, 130),
            'age_to' => $this->intRange($filters['age_to'] ?? null, 0, 130),
            'appointment_status' => $allowedStatus[$filters['appointment_status'] ?? ''] ?? null,
            'has_insurance' => (($filters['has_insurance'] ?? null) === '1' || ($filters['has_insurance'] ?? null) === 1) ? '1' : null,
            'membership_tier' => $allowedTier[$filters['membership_tier'] ?? ''] ?? null,
            'chronic_disease' => $this->shortText($filters['chronic_disease'] ?? null, 100),
            'allergy' => $this->shortText($filters['allergy'] ?? null, 100),
            'sort_by' => in_array($filters['sort_by'] ?? null, $allowedSort, true) ? $filters['sort_by'] : 'created_at',
            'sort_dir' => in_array($filters['sort_dir'] ?? null, ['asc', 'desc'], true) ? $filters['sort_dir'] : 'desc',
            'explanation' => $this->shortText($filters['explanation'] ?? null, 500) ?: 'Đã phân tích yêu cầu: ' . $query,
        ];

        if ($normalized['age_from'] !== null && $normalized['age_to'] !== null && $normalized['age_from'] > $normalized['age_to']) {
            [$normalized['age_from'], $normalized['age_to']] = [$normalized['age_to'], $normalized['age_from']];
        }

        return $normalized;
    }

    private function fallbackAiFilters(string $query): array
    {
        $text = mb_strtolower($query, 'UTF-8');
        $ascii = str($text)->ascii()->toString();

        $filters = [
            'keyword' => null,
            'gender' => null,
            'age_from' => null,
            'age_to' => null,
            'appointment_status' => null,
            'has_insurance' => null,
            'membership_tier' => null,
            'chronic_disease' => null,
            'allergy' => null,
            'sort_by' => 'created_at',
            'sort_dir' => 'desc',
            'explanation' => 'Đã phân tích bằng bộ lọc nội bộ vì AI đang phản hồi không ổn định.',
        ];

        if (str_contains($ascii, ' nam ' ) || str_starts_with($ascii, 'nam ') || str_contains($ascii, 'benh nhan nam')) {
            $filters['gender'] = 'Nam';
        }
        if (str_contains($ascii, ' nu ') || str_contains($ascii, 'benh nhan nu')) {
            $filters['gender'] = 'Nữ';
        }

        if (preg_match('/(?:tren|tu)\s+(\d{1,3})\s+tuoi/u', $ascii, $matches)) {
            $filters['age_from'] = $this->intRange($matches[1], 0, 130);
        }
        if (preg_match('/(?:duoi|nho hon)\s+(\d{1,3})\s+tuoi/u', $ascii, $matches)) {
            $filters['age_to'] = $this->intRange($matches[1], 0, 130);
        }

        if (str_contains($ascii, 'hoan thanh') || str_contains($ascii, 'da kham')) {
            $filters['appointment_status'] = 'Hoàn thành';
        } elseif (str_contains($ascii, 'da huy')) {
            $filters['appointment_status'] = 'Đã hủy';
        } elseif (str_contains($ascii, 'cho xac nhan')) {
            $filters['appointment_status'] = 'Chờ xác nhận';
        } elseif (str_contains($ascii, 'da thanh toan')) {
            $filters['appointment_status'] = 'Đã thanh toán';
        }

        if (str_contains($ascii, 'bao hiem') || str_contains($ascii, 'bhyt')) {
            $filters['has_insurance'] = '1';
        }

        foreach (['kim cương' => 'Kim Cương', 'kim cuong' => 'Kim Cương', 'vang' => 'Vàng', 'bac' => 'Bạc', 'dong' => 'Đồng'] as $needle => $tier) {
            if (str_contains($ascii, $needle) || str_contains($text, $needle)) {
                $filters['membership_tier'] = $tier;
                break;
            }
        }

        foreach (['tieu duong' => 'tiểu đường', 'huyet ap' => 'huyết áp', 'tim mach' => 'tim mạch', 'hen suyen' => 'hen suyễn'] as $needle => $condition) {
            if (str_contains($ascii, $needle)) {
                $filters['chronic_disease'] = $condition;
                break;
            }
        }

        if (preg_match('/di ung\s+([a-z0-9\s\-]+)/u', $ascii, $matches)) {
            $filters['allergy'] = $this->shortText(trim($matches[1]), 100);
        }

        if (preg_match('/(?:ten|tên)\s+([\p{L}\s]{2,40})/u', $query, $matches)) {
            $filters['keyword'] = $this->shortText(trim($matches[1]), 100);
        }

        return $filters;
    }

    private function shortText(mixed $value, int $max): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $max, 'UTF-8');
    }

    private function intRange(mixed $value, int $min, int $max): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return max($min, min($max, (int) $value));
    }
}
