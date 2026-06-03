<?php

namespace App\Http\Controllers;

use App\Models\MedicalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentController extends Controller
{
    private const PER_PAGE = 6;

    // ── INDEX ────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $keyword  = $request->input('search');
        $category = $request->input('category'); // Đây là input từ form lọc
        $period   = $request->input('period');

        $documents = MedicalDocument::where('user_id', Auth::id())
            ->latest('uploaded_at') // Sửa từ created_at sang uploaded_at
            ->search($keyword)
            ->ofCategory($category)
            ->ofPeriod($period)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // Thống kê sidebar cho tài liệu của user hiện tại
        $totalSizeBytes = MedicalDocument::where('user_id', Auth::id())
            ->get()
            ->reduce(fn($carry, $doc) => $carry + (Storage::disk('public')->exists($doc->file_path) ? Storage::disk('public')->size($doc->file_path) : 0), 0);

        $categoryStats = collect(MedicalDocument::categories())
            ->mapWithKeys(fn($cat, $key) => [$key => MedicalDocument::where('user_id', Auth::id())->where('doc_type', $key)->count()])
            ->toArray();

        $stats = [
            'total'         => MedicalDocument::where('user_id', Auth::id())->count(),
            'total_size'    => $this->formatBytes($totalSizeBytes),
            'categoryCounts'=> $categoryStats,
        ];

        $documentsSnapshot = $this->documentsSnapshotForUser((int) Auth::id());

        return view('documents.index', compact('documents', 'stats', 'documentsSnapshot'));
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / 1024 / 1024, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' bytes';
    }

    // ── STORE ────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:20480'],
            'category' => ['required', Rule::in(array_keys(MedicalDocument::categories()))],
            'documents_snapshot' => ['required', 'string', 'size:64'],
        ]);

        $result = DB::transaction(function () use ($request, $validated): array {
            $userId = (int) Auth::id();
            \App\Models\User::where('user_id', $userId)->lockForUpdate()->firstOrFail();

            if (! hash_equals($this->documentsSnapshotForUser($userId), $validated['documents_snapshot'])) {
                return [
                    'saved' => false,
                    'message' => 'Danh sách tài liệu đã được cập nhật trước đó. Vui lòng tải lại trang rồi thêm lại tài liệu.',
                ];
            }

            $uploadedFile = $request->file('file');
            $path = $uploadedFile->store('documents', 'public');

            $document = MedicalDocument::create([
                'user_id'     => $userId,
                'record_id'   => null,
                'doc_type'    => $validated['category'],
                'doc_name'    => Str::limit($uploadedFile->getClientOriginalName(), 200, ''),
                'file_path'   => $path,
                'uploaded_at' => now(),
            ]);

            $document->update([
                'record_id' => null,
                'doc_name' => Str::limit($uploadedFile->getClientOriginalName(), 200, ''),
            ]);

            return ['saved' => true, 'message' => 'Tải lên thành công!'];
        });

        return redirect()
            ->route('documents.index')
            ->with($result['saved'] ? 'success' : 'warning', $result['message']);
    }

    // ── SHOW ─────────────────────────────────────────────────────

    public function show(int $document)
    {
        $document = MedicalDocument::find($document);

        if (! $document) {
            return redirect()->route('documents.index')
                ->with('warning', 'Tai lieu da bi xoa truoc do. Vui long tai lai danh sach.');
        }

        $this->authorizeDocument($document, false);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Tệp không tồn tại.');
        }

        $path = Storage::disk('public')->path($document->file_path);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $document->doc_name . '"',
        ]);
    }

    // ── EDIT ──────────────────────────────────────────────────────

    public function edit(int $document)
    {
        $document = MedicalDocument::find($document);

        if (! $document) {
            return redirect()->route('documents.index')
                ->with('warning', 'Tai lieu da bi xoa truoc do. Vui long tai lai danh sach.');
        }

        $this->authorizeDocument($document);
        $documentSnapshot = $this->documentSnapshot($document);

        return view('documents.edit', compact('document', 'documentSnapshot'));
    }

    // ── UPDATE ───────────────────────────────────────────────────

    public function update(Request $request, int $document): RedirectResponse
    {
        $document = MedicalDocument::where('doc_id', $document)->lockForUpdate()->first();

        if (! $document) {
            return redirect()->route('documents.index')
                ->with('warning', 'Tai lieu da bi xoa truoc do. Vui long tai lai danh sach.');
        }

        $this->authorizeDocument($document);

        $request->validate([

            'document_date' => ['nullable', 'date'],
            'category'      => ['required', Rule::in(array_keys(MedicalDocument::categories()))],
            'document_snapshot' => ['required', 'string', 'size:64'],

        ]);

        if (! hash_equals($this->documentSnapshot($document), $request->input('document_snapshot'))) {
            return redirect()->route('documents.edit', $document)
                ->with('warning', 'Tai lieu da duoc nguoi khac cap nhat truoc do. Vui long tai lai du lieu roi sua lai.');
        }

        $document->update([

            'uploaded_at' => $request->input('document_date'),
            'doc_type'      => $request->input('category'),

        ]);

        return redirect()->route('documents.index')->with('success', 'Cập nhật tài liệu thành công!');
    }

    // ── DESTROY ──────────────────────────────────────────────────

    public function destroy(Request $request, int $document): RedirectResponse
    {
        $request->validate([
            'document_snapshot' => ['required', 'string', 'size:64'],
        ]);

        $document = MedicalDocument::find($document);

        if (! $document) {
            return redirect()->route('documents.index')
                ->with('warning', 'Tai lieu da duoc nguoi khac xoa truoc do. Vui long tai lai danh sach.');
        }

        $this->authorizeDocument($document);

        if (! hash_equals($this->documentSnapshot($document), $request->input('document_snapshot'))) {
            return redirect()->route('documents.index')
                ->with('warning', 'Tai lieu da duoc nguoi khac cap nhat truoc do. Vui long tai lai danh sach.');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Đã xoá tài liệu.');
    }

    // ── DOWNLOAD ─────────────────────────────────────────────────

    public function download(int $document)
    {
        $document = MedicalDocument::find($document);

        if (! $document) {
            return redirect()->route('documents.index')
                ->with('warning', 'Tai lieu da bi xoa truoc do. Vui long tai lai danh sach.');
        }

        $this->authorizeDocument($document, false);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Tệp không tồn tại.');
        }
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->download($document->file_path, $document->doc_name);
    }

   private function authorizeDocument(MedicalDocument $document, bool $forWrite = true): void
{
    $user = Auth::user();
    $isDoctor = in_array($user->role_id ?? 0, [1, 2]);

    // Bác sĩ/Admin được xem tất cả
    if ($isDoctor && ! $forWrite) return;

    // Bệnh nhân chỉ xem của mình
    if ($document->user_id !== $user->user_id) {
        abort(403);
    }
}

private function documentSnapshot(MedicalDocument $document): string
{
    return hash_hmac('sha256', implode('|', [
        $document->doc_id,
        $document->user_id,
        $document->record_id,
        $document->doc_type,
        $document->doc_name,
        $document->file_path,
        optional($document->uploaded_at)->format('Y-m-d H:i:s'),
    ]), (string) config('app.key'));
}

private function documentsSnapshotForUser(int $userId): string
{
    $payload = MedicalDocument::where('user_id', $userId)
        ->orderBy('doc_id')
        ->get(['doc_id', 'user_id', 'record_id', 'doc_type', 'doc_name', 'file_path', 'uploaded_at'])
        ->map(fn (MedicalDocument $document) => implode('|', [
            $document->doc_id,
            $document->user_id,
            $document->record_id,
            $document->doc_type,
            $document->doc_name,
            $document->file_path,
            optional($document->uploaded_at)->format('Y-m-d H:i:s'),
        ]))
        ->implode('||');

    return hash_hmac('sha256', $payload, (string) config('app.key'));
}
    // 👉 Bác sĩ/Admin xem tài liệu của bệnh nhân cụ thể
public function indexPatient(Request $request, int $patientId): \Illuminate\View\View
{
    $user = Auth::user();
    $isDoctor = in_array($user->role_id ?? 0, [1, 2]);

    if (!$isDoctor) {
        abort(403, 'Không có quyền xem tài liệu này');
    }

    $keyword  = $request->input('search');
    $category = $request->input('category');
    $period   = $request->input('period');

    $documents = MedicalDocument::where('user_id', $patientId)
        ->latest('uploaded_at')
        ->search($keyword)
        ->ofCategory($category)
        ->ofPeriod($period)
        ->paginate(self::PER_PAGE)
        ->withQueryString();

    $stats = [
        'total' => MedicalDocument::where('user_id', $patientId)->count(),
        'total_size' => '—',
        'categoryCounts' => collect(MedicalDocument::categories())
            ->mapWithKeys(fn($cat, $key) => [
                $key => MedicalDocument::where('user_id', $patientId)
                            ->where('doc_type', $key)->count()
            ])->toArray(),
    ];

    $patient = \App\Models\User::findOrFail($patientId);

    $documentsSnapshot = $this->documentsSnapshotForUser((int) Auth::id());

    return view('documents.index', compact('documents', 'stats', 'patient', 'documentsSnapshot'));
}
}
