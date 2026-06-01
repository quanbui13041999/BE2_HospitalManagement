<?php

namespace App\Http\Controllers;

use App\Models\DiseaseNutritionRule;
use App\Models\Doctor;
use App\Models\Food;
use App\Models\NutritionArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminNutritionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:1,2']);
    }

    public function index()
    {
        $articles = NutritionArticle::with('doctor')->latest()->paginate(10);

        return view('nutrition.admin.index', compact('articles'));
    }

    public function create()
    {
        $doctors = Doctor::where('status', 1)->get(['doctor_id', 'full_name']);

        return view('nutrition.admin.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateArticle($request);
        $lockKey = $this->lockKey('nutrition_article_create', $validated['title']);

        if (! $this->acquireLock($lockKey)) {
            return back()->withInput()->with('warning', 'Đang có người khác tạo bài viết tương tự. Vui lòng tải lại trang.');
        }

        try {
            $created = DB::transaction(function () use ($validated) {
                $exists = NutritionArticle::whereRaw('LOWER(TRIM(title)) = ?', [$this->normalizeForCompare($validated['title'])])
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    return false;
                }

                $validated['slug'] = $this->uniqueArticleSlug($validated['title']);
                NutritionArticle::create($validated);

                return true;
            });
        } finally {
            $this->releaseLock($lockKey);
        }

        if (! $created) {
            return back()
                ->withInput()
                ->with('warning', 'Bài viết này đã được người khác thêm trước đó. Hệ thống không lưu trùng, vui lòng tải lại trang.');
        }

        return redirect()->route('admin.nutrition.index')
            ->with('success', 'Bài viết đã được tạo thành công!');
    }

    public function edit(int $article)
    {
        $article = NutritionArticle::find($article);

        if (! $article) {
            return redirect()->route('admin.nutrition.index')
                ->with('warning', 'Bài viết đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        $doctors = Doctor::where('status', 1)->get(['doctor_id', 'full_name']);
        $articleSnapshot = $this->articleSnapshot($article);

        return view('nutrition.admin.edit', compact('article', 'doctors', 'articleSnapshot'));
    }

    public function update(Request $request, int $article)
    {
        $validated = $this->validateArticle($request, true);
        $snapshot = $validated['article_snapshot'];
        unset($validated['article_snapshot']);

        $result = DB::transaction(function () use ($article, $validated, $snapshot) {
            $current = NutritionArticle::where('article_id', $article)->lockForUpdate()->first();

            if (! $current) {
                return 'missing';
            }

            if (! hash_equals($this->articleSnapshot($current), $snapshot)) {
                return 'stale';
            }

            if ($current->title !== $validated['title']) {
                $validated['slug'] = $this->uniqueArticleSlug($validated['title'], $current->article_id);
            }

            $current->update($validated);

            return 'updated';
        });

        return $this->redirectAfterWrite($result, 'admin.nutrition.index', 'Bài viết đã được cập nhật!');
    }

    public function destroy(int $article)
    {
        $lockKey = $this->lockKey('nutrition_article_delete', (string) $article);

        if (! $this->acquireLock($lockKey)) {
            return back()->with('warning', 'Đang có người khác xóa bài viết này. Vui lòng tải lại dữ liệu.');
        }

        try {
            $result = DB::transaction(function () use ($article) {
                $current = NutritionArticle::where('article_id', $article)->lockForUpdate()->first();

                if (! $current) {
                    return 'missing';
                }

                $current->delete();

                return 'deleted';
            });
        } finally {
            $this->releaseLock($lockKey);
        }

        return $this->redirectAfterDelete($result, 'admin.nutrition.index', 'Bài viết đã được xóa.');
    }

    public function rulesIndex()
    {
        $rules = DiseaseNutritionRule::with('food')->latest()->paginate(15);

        return view('nutrition.admin.rules.index', compact('rules'));
    }

    public function rulesCreate()
    {
        $foods = Food::active()->orderBy('food_name')->get();

        return view('nutrition.admin.rules.create', compact('foods'));
    }

    public function rulesStore(Request $request)
    {
        $validated = $this->validateRule($request);
        $lockKey = $this->lockKey('nutrition_rule_create', implode('|', [
            $validated['disease_name'],
            $validated['food_id'],
            $validated['recommendation_type'],
        ]));

        if (! $this->acquireLock($lockKey)) {
            return back()->withInput()->with('warning', 'Đang có người khác tạo quy tắc giống dữ liệu này. Vui lòng tải lại trang.');
        }

        try {
            $created = DB::transaction(function () use ($validated) {
                $exists = DiseaseNutritionRule::whereRaw('LOWER(TRIM(disease_name)) = ?', [$this->normalizeForCompare($validated['disease_name'])])
                    ->where('food_id', $validated['food_id'])
                    ->where('recommendation_type', $validated['recommendation_type'])
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    return false;
                }

                DiseaseNutritionRule::create($validated);

                return true;
            });
        } finally {
            $this->releaseLock($lockKey);
        }

        if (! $created) {
            return back()
                ->withInput()
                ->with('warning', 'Quy tắc này đã được người khác thêm trước đó. Hệ thống không lưu trùng, vui lòng tải lại trang.');
        }

        return redirect()->route('admin.nutrition.rules.index')
            ->with('success', 'Quy tắc dinh dưỡng đã được thêm thành công!');
    }

    public function rulesEdit(int $rule)
    {
        $rule = DiseaseNutritionRule::find($rule);

        if (! $rule) {
            return redirect()->route('admin.nutrition.rules.index')
                ->with('warning', 'Quy tắc dinh dưỡng đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        $foods = Food::active()->orderBy('food_name')->get();
        $ruleSnapshot = $this->ruleSnapshot($rule);

        return view('nutrition.admin.rules.edit', compact('rule', 'foods', 'ruleSnapshot'));
    }

    public function rulesUpdate(Request $request, int $rule)
    {
        $validated = $this->validateRule($request, true);
        $snapshot = $validated['rule_snapshot'];
        unset($validated['rule_snapshot']);

        $result = DB::transaction(function () use ($rule, $validated, $snapshot) {
            $current = DiseaseNutritionRule::where('rule_id', $rule)->lockForUpdate()->first();

            if (! $current) {
                return 'missing';
            }

            if (! hash_equals($this->ruleSnapshot($current), $snapshot)) {
                return 'stale';
            }

            $exists = DiseaseNutritionRule::whereRaw('LOWER(TRIM(disease_name)) = ?', [$this->normalizeForCompare($validated['disease_name'])])
                ->where('food_id', $validated['food_id'])
                ->where('recommendation_type', $validated['recommendation_type'])
                ->where('rule_id', '!=', $current->rule_id)
                ->exists();

            if ($exists) {
                return 'duplicate';
            }

            $current->update($validated);

            return 'updated';
        });

        if ($result === 'duplicate') {
            return back()->withInput()->withErrors(['food_id' => 'Quy tắc này đã tồn tại.']);
        }

        return $this->redirectAfterWrite($result, 'admin.nutrition.rules.index', 'Quy tắc dinh dưỡng đã được cập nhật thành công!');
    }

    public function rulesDestroy(int $rule)
    {
        $lockKey = $this->lockKey('nutrition_rule_delete', (string) $rule);

        if (! $this->acquireLock($lockKey)) {
            return back()->with('warning', 'Đang có người khác xóa quy tắc này. Vui lòng tải lại dữ liệu.');
        }

        try {
            $result = DB::transaction(function () use ($rule) {
                $current = DiseaseNutritionRule::where('rule_id', $rule)->lockForUpdate()->first();

                if (! $current) {
                    return 'missing';
                }

                $current->delete();

                return 'deleted';
            });
        } finally {
            $this->releaseLock($lockKey);
        }

        return $this->redirectAfterDelete($result, 'admin.nutrition.rules.index', 'Đã xóa quy tắc dinh dưỡng.');
    }

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
        $validated = $this->validateFood($request);
        $lockKey = $this->lockKey('nutrition_food_create', $validated['food_name']);

        if (! $this->acquireLock($lockKey)) {
            return back()->withInput()->with('warning', 'Đang có người khác tạo thực phẩm này. Vui lòng tải lại trang.');
        }

        try {
            $created = DB::transaction(function () use ($validated) {
                $exists = Food::whereRaw('LOWER(TRIM(food_name)) = ?', [$this->normalizeForCompare($validated['food_name'])])
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    return false;
                }

                Food::create($validated);

                return true;
            });
        } finally {
            $this->releaseLock($lockKey);
        }

        if (! $created) {
            return back()
                ->withInput()
                ->with('warning', 'Thực phẩm này đã được người khác thêm trước đó. Hệ thống không lưu trùng, vui lòng tải lại trang.');
        }

        return redirect()->route('admin.nutrition.foods.index')
            ->with('success', 'Món ăn mới đã được thêm vào cơ sở dữ liệu!');
    }

    public function foodsEdit(int $food)
    {
        $food = Food::find($food);

        if (! $food) {
            return redirect()->route('admin.nutrition.foods.index')
                ->with('warning', 'Thực phẩm đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        $foodSnapshot = $this->foodSnapshot($food);

        return view('nutrition.admin.foods.edit', compact('food', 'foodSnapshot'));
    }

    public function foodsUpdate(Request $request, int $food)
    {
        $validated = $this->validateFood($request, true);
        $snapshot = $validated['food_snapshot'];
        unset($validated['food_snapshot']);

        $result = DB::transaction(function () use ($food, $validated, $snapshot) {
            $current = Food::where('food_id', $food)->lockForUpdate()->first();

            if (! $current) {
                return 'missing';
            }

            if (! hash_equals($this->foodSnapshot($current), $snapshot)) {
                return 'stale';
            }

            $exists = Food::whereRaw('LOWER(TRIM(food_name)) = ?', [$this->normalizeForCompare($validated['food_name'])])
                ->where('food_id', '!=', $current->food_id)
                ->exists();

            if ($exists) {
                return 'duplicate';
            }

            $current->update($validated);

            return 'updated';
        });

        if ($result === 'duplicate') {
            return back()->withInput()->withErrors(['food_name' => 'Tên thực phẩm này đã tồn tại.']);
        }

        return $this->redirectAfterWrite($result, 'admin.nutrition.foods.index', 'Thông tin món ăn đã được cập nhật thành công!');
    }

    public function foodsDestroy(int $food)
    {
        $lockKey = $this->lockKey('nutrition_food_delete', (string) $food);

        if (! $this->acquireLock($lockKey)) {
            return back()->with('warning', 'Đang có người khác xóa thực phẩm này. Vui lòng tải lại dữ liệu.');
        }

        try {
            $result = DB::transaction(function () use ($food) {
                $current = Food::where('food_id', $food)->lockForUpdate()->first();

                if (! $current) {
                    return 'missing';
                }

                $current->delete();

                return 'deleted';
            });
        } finally {
            $this->releaseLock($lockKey);
        }

        return $this->redirectAfterDelete($result, 'admin.nutrition.foods.index', 'Đã xóa món ăn khỏi cơ sở dữ liệu.');
    }

    private function validateArticle(Request $request, bool $isUpdate = false): array
    {
        $this->mergeClean($request, ['title', 'content', 'target_disease']);

        return $request->validate([
            'doctor_id' => ['nullable', 'integer', 'min:1', Rule::exists('doctors', 'doctor_id')->where('status', 1)],
            'title' => ['required', 'string', 'min:3', 'max:150', 'regex:/\A[\pL\s]+\z/u'],
            'content' => ['required', 'string', 'min:10', 'max:5000', 'regex:/\A[\pL\s]+\z/u'],
            'target_disease' => ['nullable', 'string', 'max:120', 'regex:/\A[\pL\s]+\z/u'],
            'status' => ['required', Rule::in(['0', '1', 0, 1])],
            'article_snapshot' => $isUpdate ? ['required', 'string', 'size:64'] : ['prohibited'],
            'article_id' => ['prohibited'],
            'slug' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ], $this->messages());
    }

    private function validateRule(Request $request, bool $isUpdate = false): array
    {
        $this->mergeClean($request, ['disease_name', 'icd_code', 'reason']);
        $request->merge(['icd_code' => $request->filled('icd_code') ? strtoupper($request->input('icd_code')) : null]);

        return $request->validate([
            'disease_name' => ['required', 'string', 'min:3', 'max:120', 'regex:/\A[\pL\s]+\z/u'],
            'icd_code' => ['nullable', 'string', 'max:10', 'regex:/\A[A-Z][0-9]{1,2}(\.[0-9A-Z]{1,2})?\z/'],
            'food_id' => ['required', 'integer', 'min:1', Rule::exists('foods', 'food_id')],
            'recommendation_type' => ['required', Rule::in(['should_eat', 'should_avoid'])],
            'reason' => ['nullable', 'string', 'max:500', 'regex:/\A[\pL\s]+\z/u'],
            'rule_snapshot' => $isUpdate ? ['required', 'string', 'size:64'] : ['prohibited'],
            'rule_id' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ], $this->messages());
    }

    private function validateFood(Request $request, bool $isUpdate = false): array
    {
        $this->mergeClean($request, ['food_name', 'description']);

        return $request->validate([
            'food_name' => ['required', 'string', 'min:2', 'max:80', 'regex:/\A[\pL\s]+\z/u'],
            'calories_per_100g' => ['required', 'integer', 'min:0', 'max:5000'],
            'description' => ['nullable', 'string', 'max:300', 'regex:/\A[\pL\s]+\z/u'],
            'status' => ['required', Rule::in(['0', '1', 0, 1])],
            'food_snapshot' => $isUpdate ? ['required', 'string', 'size:64'] : ['prohibited'],
            'food_id' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ], $this->messages());
    }

    private function mergeClean(Request $request, array $fields): void
    {
        $data = [];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = html_entity_decode(strip_tags((string) $request->input($field)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $data[$field] = preg_replace('/\s+/u', ' ', trim($value));
            }
        }

        $request->merge($data);
    }

    private function messages(): array
    {
        return [
            'required' => 'Trường này không được để trống.',
            'integer' => 'Trường này phải là số nguyên.',
            'min' => 'Dữ liệu nhập chưa đạt giới hạn tối thiểu.',
            'max' => 'Dữ liệu nhập vượt quá giới hạn cho phép.',
            'in' => 'Giá trị được chọn không hợp lệ.',
            'exists' => 'Dữ liệu được chọn không tồn tại.',
            'prohibited' => 'Không được gửi dữ liệu này từ trình duyệt.',
            'regex' => 'Dữ liệu nhập sai định dạng.',
            'title.regex' => 'Tiêu đề chỉ được nhập chữ và khoảng trắng, không nhập số hoặc ký tự đặc biệt.',
            'content.regex' => 'Nội dung chỉ được nhập chữ và khoảng trắng, không nhập số hoặc ký tự đặc biệt.',
            'target_disease.regex' => 'Tên bệnh chỉ được nhập chữ và khoảng trắng.',
            'disease_name.regex' => 'Tên bệnh lý chỉ được nhập chữ và khoảng trắng.',
            'icd_code.regex' => 'Mã ICD không đúng định dạng, ví dụ E11 hoặc I10.',
            'reason.regex' => 'Lý do chỉ được nhập chữ và khoảng trắng.',
            'food_name.regex' => 'Tên thực phẩm chỉ được nhập chữ và khoảng trắng.',
            'description.regex' => 'Mô tả chỉ được nhập chữ và khoảng trắng.',
            'article_snapshot.required' => 'Dữ liệu đã cũ, vui lòng tải lại trang trước khi lưu.',
            'rule_snapshot.required' => 'Dữ liệu đã cũ, vui lòng tải lại trang trước khi lưu.',
            'food_snapshot.required' => 'Dữ liệu đã cũ, vui lòng tải lại trang trước khi lưu.',
        ];
    }

    private function uniqueArticleSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $count = 1;

        while (NutritionArticle::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('article_id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }

        return $slug;
    }

    private function articleSnapshot(NutritionArticle $article): string
    {
        return hash_hmac('sha256', implode('|', [
            $article->article_id,
            $article->doctor_id,
            $article->title,
            $article->content,
            $article->target_disease,
            $article->status,
            optional($article->updated_at)->format('Y-m-d H:i:s'),
        ]), config('app.key'));
    }

    private function ruleSnapshot(DiseaseNutritionRule $rule): string
    {
        return hash_hmac('sha256', implode('|', [
            $rule->rule_id,
            $rule->disease_name,
            $rule->icd_code,
            $rule->food_id,
            $rule->recommendation_type,
            $rule->reason,
            optional($rule->updated_at)->format('Y-m-d H:i:s'),
        ]), config('app.key'));
    }

    private function foodSnapshot(Food $food): string
    {
        return hash_hmac('sha256', implode('|', [
            $food->food_id,
            $food->food_name,
            $food->calories_per_100g,
            $food->description,
            $food->status,
            optional($food->updated_at)->format('Y-m-d H:i:s'),
        ]), config('app.key'));
    }

    private function redirectAfterWrite(string $result, string $route, string $success)
    {
        if ($result === 'missing') {
            return redirect()->route($route)
                ->with('warning', 'Dữ liệu đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        if ($result === 'stale') {
            return redirect()->route($route)
                ->with('warning', 'Dữ liệu đã được người khác cập nhật trước đó. Vui lòng tải lại rồi sửa lại.');
        }

        return redirect()->route($route)->with('success', $success);
    }

    private function redirectAfterDelete(string $result, string $route, string $success)
    {
        if ($result === 'missing') {
            return redirect()->route($route)
                ->with('warning', 'Dữ liệu đã được người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        return redirect()->route($route)->with('success', $success);
    }

    private function lockKey(string $prefix, string $value): string
    {
        return 'nutri:' . sha1($prefix . '|' . $this->normalizeForCompare($value));
    }

    private function acquireLock(string $lockKey): bool
    {
        $result = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockKey]);

        return (int) ($result->acquired ?? 0) === 1;
    }

    private function releaseLock(string $lockKey): void
    {
        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockKey]);
    }

    private function normalizeForCompare(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($value)), 'UTF-8');
    }
}
