<?php

namespace App\Http\Controllers;

use App\Models\NutritionArticle;
use App\Models\DiseaseNutritionRule;
use App\Models\Food;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * AdminNutritionController
 * Quản lý Bài viết, Quy tắc gợi ý thực đơn & Thực phẩm dành cho Admin & Bác sĩ.
 * Route group: middleware(['auth', 'role:1,2'])
 */
class AdminNutritionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:1,2']);
    }

    // =========================================================================
    // 1. PHẦN QUẢN LÝ BÀI VIẾT DINH DƯỠNG (NUTRITION ARTICLES)
    // =========================================================================

    public function index()
    {
        $articles = NutritionArticle::with('doctor')
            ->latest()
            ->paginate(10);

        return view('nutrition.admin.index', compact('articles'));
    }

    public function create()
    {
        $doctors = Doctor::where('status', 1)->get(['doctor_id', 'full_name']);
        return view('nutrition.admin.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id'      => 'nullable|exists:doctors,doctor_id',
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'target_disease' => 'nullable|string|max:200',
            'status'         => 'required|in:0,1',
        ], [
            'title.required'   => 'Vui lòng nhập tiêu đề bài viết.',
            'content.required' => 'Vui lòng nhập nội dung bài viết.',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        // Xử lý trùng slug
        $baseSlug = $validated['slug'];
        $count = 1;
        while (NutritionArticle::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $count++;
        }

        NutritionArticle::create($validated);

        return redirect()->route('admin.nutrition.index')
            ->with('success', 'Bài viết đã được tạo thành công!');
    }

    public function edit(NutritionArticle $article)
    {
        $doctors = Doctor::where('status', 1)->get(['doctor_id', 'full_name']);
        return view('nutrition.admin.edit', compact('article', 'doctors'));
    }

    public function update(Request $request, NutritionArticle $article)
    {
        $validated = $request->validate([
            'doctor_id'      => 'nullable|exists:doctors,doctor_id',
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'target_disease' => 'nullable|string|max:200',
            'status'         => 'required|in:0,1',
        ]);

        // Chỉ tạo lại slug nếu title thay đổi
        if ($article->title !== $validated['title']) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $count = 1;
            while (NutritionArticle::where('slug', $slug)->where('article_id', '!=', $article->article_id)->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }
            $validated['slug'] = $slug;
        }

        $article->update($validated);

        return redirect()->route('admin.nutrition.index')
            ->with('success', 'Bài viết đã được cập nhật!');
    }

    public function destroy(NutritionArticle $article)
    {
        $article->delete();
        return redirect()->route('admin.nutrition.index')
            ->with('success', 'Bài viết đã được xóa.');
    }

    // =========================================================================
    // 2. PHẦN QUẢN LÝ QUY TẮC DINH DƯỠNG (DISEASE NUTRITION RULES)
    // =========================================================================

    public function rulesIndex()
    {
        $rules = DiseaseNutritionRule::with('food')
            ->latest()
            ->paginate(15);

        return view('nutrition.admin.rules.index', compact('rules'));
    }

    public function rulesCreate()
    {
        $foods = Food::active()->orderBy('food_name')->get();
        return view('nutrition.admin.rules.create', compact('foods'));
    }

    public function rulesStore(Request $request)
    {
        $validated = $request->validate([
            'disease_name'        => 'required|string|max:200',
            'icd_code'            => 'nullable|string|max:20',
            'food_id'             => 'required|exists:foods,food_id',
            'recommendation_type' => 'required|in:should_eat,should_avoid',
            'reason'              => 'nullable|string',
        ], [
            'disease_name.required'        => 'Vui lòng nhập tên bệnh lý.',
            'food_id.required'             => 'Vui lòng chọn thực phẩm.',
            'recommendation_type.required' => 'Vui lòng chọn loại gợi ý.',
        ]);

        // Kiểm tra trùng quy tắc (tránh lỗi Duplicate entry ở Database unique index)
        $exists = DiseaseNutritionRule::where('disease_name', $validated['disease_name'])
            ->where('food_id', $validated['food_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['food_id' => 'Quy tắc dinh dưỡng cho bệnh này và thực phẩm này đã tồn tại!']);
        }

        DiseaseNutritionRule::create($validated);

        return redirect()->route('admin.nutrition.rules.index')
            ->with('success', 'Quy tắc dinh dưỡng đã được thêm thành công!');
    }

    public function rulesEdit(DiseaseNutritionRule $rule)
    {
        $foods = Food::active()->orderBy('food_name')->get();
        return view('nutrition.admin.rules.edit', compact('rule', 'foods'));
    }

    public function rulesUpdate(Request $request, DiseaseNutritionRule $rule)
    {
        $validated = $request->validate([
            'disease_name'        => 'required|string|max:200',
            'icd_code'            => 'nullable|string|max:20',
            'food_id'             => 'required|exists:foods,food_id',
            'recommendation_type' => 'required|in:should_eat,should_avoid',
            'reason'              => 'nullable|string',
        ], [
            'disease_name.required'        => 'Vui lòng nhập tên bệnh lý.',
            'food_id.required'             => 'Vui lòng chọn thực phẩm.',
            'recommendation_type.required' => 'Vui lòng chọn loại gợi ý.',
        ]);

        // Kiểm tra trùng quy tắc với các bản ghi khác
        $exists = DiseaseNutritionRule::where('disease_name', $validated['disease_name'])
            ->where('food_id', $validated['food_id'])
            ->where('rule_id', '!=', $rule->rule_id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['food_id' => 'Quy tắc dinh dưỡng cho bệnh này và thực phẩm này đã tồn tại!']);
        }

        $rule->update($validated);

        return redirect()->route('admin.nutrition.rules.index')
            ->with('success', 'Quy tắc dinh dưỡng đã được cập nhật thành công!');
    }

    public function rulesDestroy(DiseaseNutritionRule $rule)
    {
        $rule->delete();
        return redirect()->route('admin.nutrition.rules.index')
            ->with('success', 'Đã xóa quy tắc dinh dưỡng.');
    }

    // =========================================================================
    // 3. PHẦN QUẢN LÝ THỰC PHẨM & CALO (FOODS DATABASE)
    // =========================================================================

    public function foodsIndex()
    {
        $foods = Food::latest()->paginate(15);
        return view('nutrition.admin.foods.index', compact('foods'));
    }

    public function foodsCreate()
    {
        return view('nutrition.admin.foods.create');
    }

    public function foodsStore(Request $request)
    {
        $validated = $request->validate([
            'food_name'         => 'required|string|max:150|unique:foods,food_name',
            'calories_per_100g' => 'required|integer|min:0|max:5000',
            'description'       => 'nullable|string',
            'status'            => 'required|in:0,1',
        ], [
            'food_name.required'          => 'Vui lòng nhập tên món ăn/thực phẩm.',
            'food_name.unique'            => 'Tên món ăn/thực phẩm này đã tồn tại trong cơ sở dữ liệu.',
            'calories_per_100g.required'  => 'Vui lòng nhập lượng Calo.',
            'calories_per_100g.integer'   => 'Lượng Calo phải là một số nguyên.',
            'calories_per_100g.min'       => 'Lượng Calo không thể nhỏ hơn 0.',
        ]);

        Food::create($validated);

        return redirect()->route('admin.nutrition.foods.index')
            ->with('success', 'Món ăn mới đã được thêm vào cơ sở dữ liệu!');
    }

    public function foodsEdit(Food $food)
    {
        return view('nutrition.admin.foods.edit', compact('food'));
    }

    public function foodsUpdate(Request $request, Food $food)
    {
        $validated = $request->validate([
            'food_name'         => 'required|string|max:150|unique:foods,food_name,' . $food->food_id . ',food_id',
            'calories_per_100g' => 'required|integer|min:0|max:5000',
            'description'       => 'nullable|string',
            'status'            => 'required|in:0,1',
        ], [
            'food_name.required'          => 'Vui lòng nhập tên món ăn/thực phẩm.',
            'food_name.unique'            => 'Tên món ăn/thực phẩm này đã tồn tại trong cơ sở dữ liệu.',
            'calories_per_100g.required'  => 'Vui lòng nhập lượng Calo.',
            'calories_per_100g.integer'   => 'Lượng Calo phải là một số nguyên.',
            'calories_per_100g.min'       => 'Lượng Calo không thể nhỏ hơn 0.',
        ]);

        $food->update($validated);

        return redirect()->route('admin.nutrition.foods.index')
            ->with('success', 'Thông tin món ăn đã được cập nhật thành công!');
    }

    public function foodsDestroy(Food $food)
    {
        $food->delete();
        return redirect()->route('admin.nutrition.foods.index')
            ->with('success', 'Đã xóa món ăn khỏi cơ sở dữ liệu.');
    }
}
