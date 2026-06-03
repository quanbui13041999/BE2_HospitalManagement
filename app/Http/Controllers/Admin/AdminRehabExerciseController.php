<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RehabExerciseRequest;
use App\Models\RehabExercise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminRehabExerciseController extends Controller
{
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

    public function create(): View
    {
        return view('admin.rehab_form', [
            'exercise'      => new RehabExercise(),
            'categories'    => $this->categoryOptions(),
            'phases'        => $this->phaseOptions(),
            'isEdit'        => false,
            'rehabSnapshot' => null,
        ]);
    }

    public function store(RehabExerciseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $lockKey = $this->rehabCreateLockKey($data['category'], $data['phase'], $data['title']);

        if (! $this->acquireRehabLock($lockKey)) {
            return back()
                ->withInput()
                ->with('warning', 'Đang có người khác tạo bài tập giống dữ liệu này. Vui lòng tải lại danh sách.');
        }

        try {
            $created = DB::transaction(function () use ($request, $data) {
                $alreadyExists = RehabExercise::where('category', $data['category'])
                    ->where('phase', $data['phase'])
                    ->whereRaw('LOWER(TRIM(title)) = ?', [$this->normalizeForCompare($data['title'])])
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyExists) {
                    return false;
                }

                if ($request->hasFile('thumbnail')) {
                    $data['thumbnail'] = $request->file('thumbnail')
                        ->store('rehab/thumbnails', 'public');
                }

                RehabExercise::create($data);

                return true;
            });
        } finally {
            $this->releaseRehabLock($lockKey);
        }

        if (! $created) {
            return redirect()
                ->route('admin.rehab.index')
                ->with('warning', 'Bài tập này đã được người khác tạo trước đó. Hệ thống không tạo trùng.');
        }

        return redirect()
            ->route('admin.rehab.index')
            ->with('success', 'Bài tập đã được tạo thành công!');
    }

    public function edit(RehabExercise $exercise): View
    {
        return view('admin.rehab_form', [
            'exercise'      => $exercise,
            'categories'    => $this->categoryOptions(),
            'phases'        => $this->phaseOptions(),
            'isEdit'        => true,
            'rehabSnapshot' => $this->rehabSnapshot($exercise),
        ]);
    }

    public function update(RehabExerciseRequest $request, int $exercise): RedirectResponse
    {
        $data = $request->validated();
        $snapshot = $data['rehab_snapshot'];
        unset($data['rehab_snapshot']);
        $oldThumbnail = null;

        $result = DB::transaction(function () use ($request, $exercise, $data, $snapshot, &$oldThumbnail) {
            $current = RehabExercise::where('id', $exercise)
                ->lockForUpdate()
                ->first();

            if (! $current) {
                return 'missing';
            }

            if (! hash_equals($this->rehabSnapshot($current), $snapshot)) {
                return 'stale';
            }

            if ($request->hasFile('thumbnail')) {
                $oldThumbnail = $current->thumbnail;
                $data['thumbnail'] = $request->file('thumbnail')
                    ->store('rehab/thumbnails', 'public');
            }

            $current->update($data);

            return 'updated';
        });

        if ($result === 'missing') {
            return redirect()
                ->route('admin.rehab.index')
                ->with('warning', 'Bài tập đã bị người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        if ($result === 'stale') {
            return redirect()
                ->route('admin.rehab.edit', $exercise)
                ->with('warning', 'Bài tập đã được người khác cập nhật trước đó. Vui lòng tải lại dữ liệu rồi sửa lại.');
        }

        if ($oldThumbnail) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        return redirect()
            ->route('admin.rehab.index')
            ->with('success', 'Bài tập đã được cập nhật!');
    }

    public function destroy(int $exercise): RedirectResponse
    {
        $lockKey = $this->rehabDeleteLockKey($exercise);

        if (! $this->acquireRehabLock($lockKey)) {
            return back()->with('warning', 'Đang có người khác xóa bài tập này. Vui lòng tải lại dữ liệu.');
        }

        $thumbnail = null;

        try {
            $result = DB::transaction(function () use ($exercise, &$thumbnail) {
                $current = RehabExercise::where('id', $exercise)
                    ->lockForUpdate()
                    ->first();

                if (! $current) {
                    return 'missing';
                }

                $thumbnail = $current->thumbnail;
                $current->delete();

                return 'deleted';
            });
        } finally {
            $this->releaseRehabLock($lockKey);
        }

        if ($result === 'missing') {
            return redirect()
                ->route('admin.rehab.index')
                ->with('warning', 'Bài tập đã được người khác xóa trước đó. Vui lòng tải lại danh sách.');
        }

        if ($thumbnail) {
            Storage::disk('public')->delete($thumbnail);
        }

        return redirect()
            ->route('admin.rehab.index')
            ->with('success', 'Bài tập đã được xoá.');
    }

    private function categoryOptions(): array
    {
        return [
            'co-xuong-khop'       => 'Cơ - Xương - Khớp',
            'than-kinh-dot-quy'   => 'Thần kinh - Đột quỵ',
            'chan-thuong-the-thao'=> 'Chấn thương Thể thao',
            'ho-hap-tim-mach'     => 'Hô hấp - Tim mạch',
        ];
    }

    private function phaseOptions(): array
    {
        return [
            'cap-tinh' => 'Giai đoạn Cấp tính',
            'phuc-hoi' => 'Giai đoạn Phục hồi',
            'duy-tri'  => 'Duy trì',
        ];
    }

    private function rehabSnapshot(RehabExercise $exercise): string
    {
        return hash_hmac('sha256', implode('|', [
            $exercise->id,
            $exercise->title,
            $exercise->content,
            $exercise->category,
            $exercise->phase,
            $exercise->thumbnail,
            $exercise->status,
            $exercise->duration_minutes,
            optional($exercise->updated_at)->format('Y-m-d H:i:s'),
        ]), config('app.key'));
    }

    private function rehabCreateLockKey(string $category, string $phase, string $title): string
    {
        return 'rehab_create:' . sha1(implode('|', [
            $category,
            $phase,
            $this->normalizeForCompare($title),
        ]));
    }

    private function rehabDeleteLockKey(int $exerciseId): string
    {
        return 'rehab_exercise_delete:' . $exerciseId;
    }

    private function acquireRehabLock(string $lockKey): bool
    {
        $result = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockKey]);

        return (int) ($result->acquired ?? 0) === 1;
    }

    private function releaseRehabLock(string $lockKey): void
    {
        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockKey]);
    }

    private function normalizeForCompare(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($value)), 'UTF-8');
    }
}
