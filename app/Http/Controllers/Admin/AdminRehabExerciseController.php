<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RehabExerciseRequest;
use App\Models\RehabExercise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminRehabExerciseController extends Controller
{
    /**
     * Dashboard + danh sách bài tập.
     * Route: GET /admin/rehab-exercises
     */
    public function index(): View
    {
        $stats = [
            'total'     => RehabExercise::count(),
            'published' => RehabExercise::published()->count(),
            'draft'     => RehabExercise::draft()->count(),
            'views'     => RehabExercise::sum('view_count'),
        ];

        $exercises = RehabExercise::with('author')
            ->latest()
            ->paginate(10);

        return view('admin.rehab_management', [
            'stats'      => $stats,
            'exercises'  => $exercises,
            'categories' => $this->categoryOptions(),
            'phases'     => $this->phaseOptions(),
        ]);
    }

    /**
     * Form tạo bài tập mới.
     * Route: GET /admin/rehab-exercises/create
     */
    public function create(): View
    {
        return view('admin.rehab_form', [
            'exercise'   => new RehabExercise(),
            'categories' => $this->categoryOptions(),
            'phases'     => $this->phaseOptions(),
            'isEdit'     => false,
        ]);
    }

    /**
     * Lưu bài tập mới vào database.
     * Route: POST /admin/rehab-exercises
     */
    public function store(RehabExerciseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')
                ->store('rehab/thumbnails', 'public');
        }

        RehabExercise::create($data);

        return redirect()
            ->route('admin.rehab.index')
            ->with('success', 'Bài tập đã được tạo thành công!');
    }

    /**
     * Form chỉnh sửa bài tập.
     * Route: GET /admin/rehab-exercises/{exercise}/edit
     */
    public function edit(RehabExercise $exercise): View
    {
        return view('admin.rehab_form', [
            'exercise'   => $exercise,
            'categories' => $this->categoryOptions(),
            'phases'     => $this->phaseOptions(),
            'isEdit'     => true,
        ]);
    }

    /**
     * Cập nhật bài tập.
     * Route: PUT /admin/rehab-exercises/{exercise}
     */
    public function update(RehabExerciseRequest $request, RehabExercise $exercise): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            // Xoá ảnh cũ
            if ($exercise->thumbnail) {
                Storage::disk('public')->delete($exercise->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')
                ->store('rehab/thumbnails', 'public');
        }

        $exercise->update($data);

        return redirect()
            ->route('admin.rehab.index')
            ->with('success', 'Bài tập đã được cập nhật!');
    }

    /**
     * Xoá bài tập.
     * Route: DELETE /admin/rehab-exercises/{exercise}
     */
    public function destroy(RehabExercise $exercise): RedirectResponse
    {
        if ($exercise->thumbnail) {
            Storage::disk('public')->delete($exercise->thumbnail);
        }

        $exercise->delete();

        return redirect()
            ->route('admin.rehab.index')
            ->with('success', 'Bài tập đã được xoá.');
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function categoryOptions(): array
    {
        return [
            'co-xuong-khop'         => '🦴 Cơ – Xương – Khớp',
            'than-kinh-dot-quy'     => '🧠 Thần kinh – Đột quỵ',
            'chan-thuong-the-thao'   => '🏃 Chấn thương Thể thao',
            'ho-hap-tim-mach'       => '🫁 Hô hấp – Tim mạch',
        ];
    }

    private function phaseOptions(): array
    {
        return [
            'cap-tinh' => '⚡ Giai đoạn Cấp tính',
            'phuc-hoi' => '🔄 Giai đoạn Phục hồi',
            'duy-tri'  => '✅ Duy trì',
        ];
    }
}
