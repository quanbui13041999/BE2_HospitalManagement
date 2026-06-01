<?php

namespace App\Http\Controllers;

use App\Models\RehabExercise;
use Illuminate\Http\Request;

class RehabExerciseController extends Controller
{
    public function index(Request $request)
    {
        $categories = $this->categoryList();
        $category = $request->query('category');

        if ($category !== null && ! array_key_exists($category, $categories)) {
            $category = null;
        }

        $exercises = RehabExercise::published()
            ->byCategory($category)
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('patient.rehab_exercises', [
            'exercises'      => $exercises,
            'activeCategory' => $category,
            'categories'     => $categories,
        ]);
    }

    public function show(RehabExercise $exercise)
    {
        abort_if($exercise->status !== 'published', 404);

        $exercise->incrementViewCount();

        $related = RehabExercise::published()
            ->where('category', $exercise->category)
            ->where('id', '!=', $exercise->id)
            ->latest()
            ->take(3)
            ->get();

        return view('patient.rehab_exercise_detail', compact('exercise', 'related'));
    }

    private function categoryList(): array
    {
        return [
            null                    => 'Tất cả bài tập',
            'co-xuong-khop'         => 'Cơ - Xương - Khớp',
            'than-kinh-dot-quy'     => 'Thần kinh - Đột quỵ',
            'chan-thuong-the-thao'  => 'Chấn thương Thể thao',
            'ho-hap-tim-mach'       => 'Hô hấp - Tim mạch',
        ];
    }
}
