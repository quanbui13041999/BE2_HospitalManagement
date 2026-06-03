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

    public function show($exercise)
    {
        $exercise = $this->findExerciseFromRoute($exercise);

        if (! $exercise || $exercise->status !== 'published') {
            return redirect()
                ->route('rehab.index')
                ->with('warning', 'Không tìm thấy trang bài tập phục hồi.');
        }

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

    private function findExerciseFromRoute($id): ?RehabExercise
    {
        if ($id instanceof RehabExercise) {
            return $id;
        }

        $id = trim((string) $id);

        if (! preg_match('/\A[1-9][0-9]*\z/', $id)) {
            return null;
        }

        return RehabExercise::find((int) $id);
    }
}
