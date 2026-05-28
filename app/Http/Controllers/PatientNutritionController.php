<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\MealLog;
use App\Models\NutritionArticle;
use App\Models\DiseaseNutritionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * PatientNutritionController
 * Dashboard Dinh dưỡng cho Bệnh nhân đang đăng nhập.
 * Xử lý đồng thời 4 chức năng:
 * 1. Gợi ý thực đơn theo bệnh (Đã fix lỗi MySQL Strict Mode)
 * 2. Nhật ký bữa ăn hôm nay
 * 3. Tính toán Calo nạp vào
 * 4. Bài viết lời khuyên theo bệnh
 */
class PatientNutritionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─── TRANG CHÍNH: Dashboard Dinh dưỡng ───────────────────────
    public function index()
    {
        $user = Auth::user();

        // ── BƯỚC 1: Lấy chẩn đoán bệnh gần nhất của bệnh nhân ──
        // Đã fix lỗi tương thích với STRICT MODE bằng cách GroupBy và dùng MAX(created_at)
        $latestDiagnoses = DB::table('diagnoses')
            ->join('medical_records', 'diagnoses.record_id', '=', 'medical_records.record_id')
            ->where('medical_records.patient_id', Auth::id())
            ->select(
                'diagnoses.diagnosis_name',
                'diagnoses.icd_code',
                DB::raw('MAX(medical_records.created_at) as latest_created')
            )
            ->groupBy('diagnoses.diagnosis_name', 'diagnoses.icd_code')
            ->orderByDesc('latest_created')
            ->limit(3) // Lấy tối đa 3 bệnh gần nhất
            ->get();

        // ── BƯỚC 2: Gợi ý thực đơn theo bệnh ───────────────────
        $shouldEatFoods   = collect();
        $shouldAvoidFoods = collect();

        foreach ($latestDiagnoses as $diagnosis) {
            $rules = DiseaseNutritionRule::with('food')
                ->where(function ($q) use ($diagnosis) {
                    $q->where('disease_name', 'LIKE', "%{$diagnosis->diagnosis_name}%");
                    if ($diagnosis->icd_code) {
                        $q->orWhere('icd_code', $diagnosis->icd_code);
                    }
                })
                ->get();

            $shouldEatFoods = $shouldEatFoods->merge(
                $rules->where('recommendation_type', 'should_eat')
            );
            $shouldAvoidFoods = $shouldAvoidFoods->merge(
                $rules->where('recommendation_type', 'should_avoid')
            );
        }

        // Loại trùng nếu bệnh nhân có nhiều bệnh cùng trùng quy tắc dinh dưỡng
        $shouldEatFoods   = $shouldEatFoods->unique('food_id');
        $shouldAvoidFoods = $shouldAvoidFoods->unique('food_id');

        // ── BƯỚC 3: Nhật ký ăn uống hôm nay & tổng Calo ─────────
        $todayLogs = MealLog::with('food')
            ->where('user_id', Auth::id())
            ->whereDate('logged_date', today())
            ->orderBy('created_at')
            ->get();

        $totalCaloriesToday = $todayLogs->sum('total_calories_intake');
        $calorieGoal        = 2000; // Mức calo mục tiêu mặc định (kcal/ngày)
        $caloriePercent     = min(100, round(($totalCaloriesToday / $calorieGoal) * 100));

        // Tóm tắt calo theo buổi ăn
        $calorieByMeal = $todayLogs->groupBy('meal_type')->map(fn ($logs) => $logs->sum('total_calories_intake'));

        // ── BƯỚC 4: Danh sách món ăn để bệnh nhân chọn log ────────────
        $allFoods = Food::active()->orderBy('food_name')->get(['food_id', 'food_name', 'calories_per_100g']);

        // ── BƯỚC 5: Bài viết lời khuyên theo bệnh ───────────────
        $diseaseNames = $latestDiagnoses->pluck('diagnosis_name');

        $expertArticles = NutritionArticle::published()
            ->where(function ($q) use ($diseaseNames) {
                foreach ($diseaseNames as $name) {
                    $q->orWhere('target_disease', 'LIKE', "%{$name}%");
                }
            })
            ->latest()
            ->limit(4)
            ->get();

        // Nếu hệ thống chưa tìm thấy bài viết khớp bệnh hoặc bệnh nhân chưa có lịch sử khám bệnh
        if ($expertArticles->isEmpty()) {
            $expertArticles = NutritionArticle::published()->latest()->limit(4)->get();
        }

        return view('nutrition.patient.index', compact(
            'user',
            'latestDiagnoses',
            'shouldEatFoods',
            'shouldAvoidFoods',
            'todayLogs',
            'totalCaloriesToday',
            'calorieGoal',
            'caloriePercent',
            'calorieByMeal',
            'allFoods',
            'expertArticles'
        ));
    }

    // ─── STORE: Bệnh nhân thêm bữa ăn vào nhật ký ───────────────
    public function storeMealLog(Request $request)
    {
        $validated = $request->validate([
            'food_id'     => 'required|exists:foods,food_id',
            'meal_type'   => 'required|in:breakfast,lunch,dinner,snack',
            'weight_gram' => 'required|integer|min:1|max:5000',
        ], [
            'food_id.required'     => 'Vui lòng chọn món ăn.',
            'food_id.exists'       => 'Món ăn không hợp lệ.',
            'meal_type.required'   => 'Vui lòng chọn buổi ăn.',
            'weight_gram.required' => 'Vui lòng nhập khối lượng.',
            'weight_gram.min'      => 'Khối lượng tối thiểu là 1 gram.',
            'weight_gram.max'      => 'Khối lượng tối đa là 5000 gram.',
        ]);

        $food = Food::findOrFail($validated['food_id']);

        // Tính toán lượng calo tự động dựa trên khối lượng (Calo gốc tính trên 100g)
        $totalCalories = (int) round($food->calories_per_100g * $validated['weight_gram'] / 100);

        MealLog::create([
            'user_id'               => Auth::id(),
            'food_id'               => $validated['food_id'],
            'meal_type'             => $validated['meal_type'],
            'weight_gram'           => $validated['weight_gram'],
            'total_calories_intake' => $totalCalories,
            'logged_date'           => today(),
        ]);

        return redirect()->route('patient.nutrition.index')
            ->with('success', "Đã ghi nhận vào nhật ký: {$food->food_name} ({$validated['weight_gram']}g = {$totalCalories} kcal)!");
    }

    // ─── DESTROY: Xóa 1 bản ghi nhật ký ─────────────────────────
    public function destroyMealLog(MealLog $mealLog)
    {
        // Khâu bảo mật: Kiểm tra bệnh nhân chỉ xóa được nhật ký của chính mình
        if ($mealLog->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xóa bản ghi này.');
        }

        $mealLog->delete();

        return redirect()->route('patient.nutrition.index')
            ->with('success', 'Đã xóa bản ghi bữa ăn ra khỏi nhật ký hôm nay.');
    }
}