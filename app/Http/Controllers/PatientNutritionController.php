<?php

namespace App\Http\Controllers;

use App\Models\DiseaseNutritionRule;
use App\Models\Food;
use App\Models\MealLog;
use App\Models\NutritionArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientNutritionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

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
            ->limit(3)
            ->get();

        $shouldEatFoods = collect();
        $shouldAvoidFoods = collect();

        foreach ($latestDiagnoses as $diagnosis) {
            $rules = DiseaseNutritionRule::with('food')
                ->whereHas('food', function ($q) {
                    $q->where('status', 1);
                })
                ->where(function ($q) use ($diagnosis) {
                    $q->where('disease_name', 'LIKE', "%{$diagnosis->diagnosis_name}%");
                    if ($diagnosis->icd_code) {
                        $q->orWhere('icd_code', $diagnosis->icd_code);
                    }
                })
                ->get();

            $shouldEatFoods = $shouldEatFoods->merge($rules->where('recommendation_type', 'should_eat'));
            $shouldAvoidFoods = $shouldAvoidFoods->merge($rules->where('recommendation_type', 'should_avoid'));
        }

        $shouldEatFoods = $shouldEatFoods->filter(fn ($rule) => $rule->food)->unique('food_id');
        $shouldAvoidFoods = $shouldAvoidFoods->filter(fn ($rule) => $rule->food)->unique('food_id');

        $todayLogs = MealLog::with('food')
            ->where('user_id', Auth::id())
            ->whereDate('logged_date', today())
            ->orderBy('created_at')
            ->get();

        $totalCaloriesToday = $todayLogs->sum('total_calories_intake');
        $calorieGoal = 2000;
        $caloriePercent = min(100, round(($totalCaloriesToday / $calorieGoal) * 100));
        $calorieByMeal = $todayLogs->groupBy('meal_type')->map(fn ($logs) => $logs->sum('total_calories_intake'));

        $allFoods = Food::active()->orderBy('food_name')->get(['food_id', 'food_name', 'calories_per_100g']);
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

    public function storeMealLog(Request $request)
    {
        $validated = $request->validate([
            'food_id' => ['required', 'integer', 'min:1', Rule::exists('foods', 'food_id')->where('status', 1)],
            'meal_type' => ['required', Rule::in(['breakfast', 'lunch', 'dinner', 'snack'])],
            'weight_gram' => ['required', 'regex:/\A[0-9]+\z/', 'integer', 'min:1', 'max:5000'],
            'total_calories_intake' => ['prohibited'],
            'logged_date' => ['prohibited'],
            'user_id' => ['prohibited'],
        ], [
            'food_id.required' => 'Vui lòng chọn món ăn.',
            'food_id.exists' => 'Món ăn không hợp lệ hoặc đã bị ẩn.',
            'meal_type.required' => 'Vui lòng chọn buổi ăn.',
            'weight_gram.required' => 'Vui lòng nhập khối lượng.',
            'weight_gram.regex' => 'Khối lượng chỉ được nhập số 0-9, không dùng số full-width hoặc ký tự lạ.',
            'weight_gram.integer' => 'Khối lượng phải là số nguyên.',
            'weight_gram.min' => 'Khối lượng tối thiểu là 1 gram.',
            'weight_gram.max' => 'Khối lượng tối đa là 5000 gram.',
            'prohibited' => 'Không được gửi dữ liệu hệ thống từ trình duyệt.',
        ]);

        $lockKey = 'meal_log_create:' . sha1(Auth::id() . '|' . today()->toDateString() . '|' . $validated['meal_type'] . '|' . $validated['food_id']);

        if (! $this->acquireNutritionLock($lockKey)) {
            return back()->withInput()->with('warning', 'Đang có thao tác ghi nhật ký giống dữ liệu này. Vui lòng tải lại trang.');
        }

        try {
            $result = DB::transaction(function () use ($validated) {
                $food = Food::where('food_id', $validated['food_id'])
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->firstOrFail();

                $exists = MealLog::where('user_id', Auth::id())
                    ->where('food_id', $validated['food_id'])
                    ->where('meal_type', $validated['meal_type'])
                    ->whereDate('logged_date', today())
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    return ['created' => false, 'food' => $food, 'calories' => 0];
                }

                $totalCalories = (int) round($food->calories_per_100g * $validated['weight_gram'] / 100);

                MealLog::create([
                    'user_id' => Auth::id(),
                    'food_id' => $validated['food_id'],
                    'meal_type' => $validated['meal_type'],
                    'weight_gram' => $validated['weight_gram'],
                    'total_calories_intake' => $totalCalories,
                    'logged_date' => today(),
                ]);

                return ['created' => true, 'food' => $food, 'calories' => $totalCalories];
            });
        } finally {
            $this->releaseNutritionLock($lockKey);
        }

        if (! $result['created']) {
            return redirect()->route('patient.nutrition.index')
                ->with('warning', 'Bữa ăn này đã được ghi trước đó. Hệ thống không ghi trùng, vui lòng tải lại trang.');
        }

        return redirect()->route('patient.nutrition.index')
            ->with('success', "Đã ghi nhận vào nhật ký: {$result['food']->food_name} ({$validated['weight_gram']}g = {$result['calories']} kcal)!");
    }

    public function destroyMealLog(int $mealLog)
    {
        $lockKey = 'meal_log_delete:' . $mealLog;

        if (! $this->acquireNutritionLock($lockKey)) {
            return back()->with('warning', 'Đang có thao tác xóa nhật ký này. Vui lòng tải lại trang.');
        }

        try {
            $result = DB::transaction(function () use ($mealLog) {
                $current = MealLog::where('log_id', $mealLog)->lockForUpdate()->first();

                if (! $current) {
                    return 'missing';
                }

                if ($current->user_id !== Auth::id()) {
                    abort(403, 'Bạn không có quyền xóa bản ghi này.');
                }

                $current->delete();

                return 'deleted';
            });
        } finally {
            $this->releaseNutritionLock($lockKey);
        }

        if ($result === 'missing') {
            return redirect()->route('patient.nutrition.index')
                ->with('warning', 'Nhật ký này đã được xóa trước đó. Vui lòng tải lại trang.');
        }

        return redirect()->route('patient.nutrition.index')
            ->with('success', 'Đã xóa bản ghi bữa ăn ra khỏi nhật ký hôm nay.');
    }

    private function acquireNutritionLock(string $lockKey): bool
    {
        $result = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockKey]);

        return (int) ($result->acquired ?? 0) === 1;
    }

    private function releaseNutritionLock(string $lockKey): void
    {
        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockKey]);
    }
}
