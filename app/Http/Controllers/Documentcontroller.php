<?php

namespace App\Http\Controllers;

use App\Models\MedicalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentController extends Controller
{
    private const PER_PAGE = 6;

    private const CATEGORIES = ['xet_nghiem', 'hinh_anh', 'don_thuoc', 'chuyen_vien', 'khac'];

    private const IMAGE_MIMES = ['jpg', 'jpeg', 'png'];

    // ── INDEX ────────────────────────────────────────────────────

    public function index(Request $request): View|RedirectResponse
    {
        if (!$this->currentUserExists()) {
            return $this->redirectWhenUserMissing();
        }

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
        $this->attachSnapshots($documents->getCollection());

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
        $documentCollectionSnapshot = $this->makeCollectionSnapshot(Auth::id());

        return view('documents.index', compact('documents', 'stats', 'documentCollectionSnapshot'));
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
        $conflict = DB::transaction(fn () => $this->detectCollectionConflict($request), 3);

        if ($conflict !== 'ok') {
            return $this->redirectAfterCollectionConflict($conflict);
        }

        $validated = $this->validateStoreRequest($request);

        $result = DB::transaction(function () use ($request, $validated) {
            $conflict = $this->detectCollectionConflict($request);

            if ($conflict !== 'ok') {
                return $conflict;
            }

            $uploadedFile = $request->file('file');

            // Lưu vào folder 'documents' trong disk 'public'
            // Kết quả trả về sẽ là: "documents/tên_file_ngẫu_nhiên.extension"
            $path = $uploadedFile->store('documents', 'public');

            MedicalDocument::create([
                'user_id'     => Auth::id(),
                'record_id'   => 1, // Tạm thời để 1 theo cấu trúc DB của bạn
                'doc_type'    => $validated['category'],
                'doc_name'    => $uploadedFile->getClientOriginalName(),
                'file_path'   => $path,
                'uploaded_at' => now(),
            ]);

            return 'saved';
        }, 3);

        if ($result !== 'saved') {
            return $this->redirectAfterCollectionConflict($result);
        }

        return redirect()->route('documents.index')->with('success', 'Tải lên thành công!');
    }

    // ── SHOW ─────────────────────────────────────────────────────

    public function show(MedicalDocument $document)
    {
        $this->authorizeDocument($document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Tệp không tồn tại.');
        }

        $path = Storage::disk('public')->path($document->file_path);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $document->doc_name . '"',
        ]);
    }

    // ── EDIT ──────────────────────────────────────────────────────

    public function edit(MedicalDocument $document)
    {
        $this->authorizeDocument($document);
        $documentSnapshot = $this->makeDocumentSnapshot($document);

        return view('documents.edit', compact('document', 'documentSnapshot'));
    }

    // ── UPDATE ───────────────────────────────────────────────────

    public function update(Request $request, int $document): RedirectResponse
    {
        $conflict = DB::transaction(fn () => $this->detectDocumentConflict($request, $document), 3);

        if ($conflict !== 'ok') {
            return $this->redirectAfterDocumentConflict($conflict);
        }

        $validated = $this->validateUpdateRequest($request);

        $result = DB::transaction(function () use ($request, $document, $validated) {
            $conflict = $this->detectDocumentConflict($request, $document);

            if ($conflict !== 'ok') {
                return $conflict;
            }

            $current = MedicalDocument::whereKey($document)
                ->lockForUpdate()
                ->first();

            if (!$current) {
                return 'deleted';
            }

            $oldPath = $current->file_path;
            $data = [
                'uploaded_at' => $validated['document_date'] ?? $current->uploaded_at,
                'doc_type' => $validated['category'],
            ];

            if ($request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                $data['doc_name'] = $uploadedFile->getClientOriginalName();
                $data['file_path'] = $uploadedFile->store('documents', 'public');
            }

            $current->update($data);

            if (($data['file_path'] ?? null) && $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            return 'saved';
        }, 3);

        if ($result !== 'saved') {
            return $this->redirectAfterDocumentConflict($result);
        }

        return redirect()->route('documents.index')->with('success', 'Cập nhật tài liệu thành công!');
    }

    // ── DESTROY ──────────────────────────────────────────────────

    public function destroy(Request $request, int $document): RedirectResponse
    {
        $result = DB::transaction(function () use ($request, $document) {
            $conflict = $this->detectDocumentConflict($request, $document);

            if ($conflict !== 'ok') {
                return $conflict;
            }

            $current = MedicalDocument::whereKey($document)
                ->lockForUpdate()
                ->first();

            if (!$current) {
                return 'deleted';
            }

            $path = $current->file_path;
            $current->delete();
            Storage::disk('public')->delete($path);

            return 'saved';
        }, 3);

        if ($result !== 'saved') {
            return $this->redirectAfterDocumentConflict($result);
        }

        return redirect()->route('documents.index')->with('success', 'Đã xoá tài liệu.');
    }

    // ── DOWNLOAD ─────────────────────────────────────────────────

    public function download(MedicalDocument $document)
    {
        $this->authorizeDocument($document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Tệp không tồn tại.');
        }
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->download($document->file_path, $document->doc_name);
    }

    private function authorizeDocument(MedicalDocument $document): void
    {
        $user = Auth::user();
        $isDoctor = in_array($user->role_id ?? 0, [1, 2], true);

        // Bác sĩ/Admin được xem tất cả
        if ($isDoctor) {
            return;
        }

        // Bệnh nhân chỉ xem của mình
        if ($document->user_id !== $user->user_id) {
            abort(403);
        }
    }

    // 👉 Bác sĩ/Admin xem tài liệu của bệnh nhân cụ thể
    public function indexPatient(Request $request, int $patientId): \Illuminate\View\View
    {
        $user = Auth::user();
        $isDoctor = in_array($user->role_id ?? 0, [1, 2], true);

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
        $this->attachSnapshots($documents->getCollection());

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
        $documentCollectionSnapshot = $this->makeCollectionSnapshot($patientId);

        return view('documents.index', compact('documents', 'stats', 'patient', 'documentCollectionSnapshot'));
    }

    private function validateStoreRequest(Request $request): array
    {
        return $request->validate([
            'document_collection_snapshot' => ['required', 'string', 'size:64'],
            'file' => ['required', 'file', 'image', 'mimes:' . implode(',', self::IMAGE_MIMES), 'max:20480'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
        ], $this->validationMessages());
    }

    private function validateUpdateRequest(Request $request): array
    {
        return $request->validate([
            'document_snapshot' => ['required', 'string', 'size:64'],
            'document_date' => ['nullable', 'date'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'file' => ['nullable', 'file', 'image', 'mimes:' . implode(',', self::IMAGE_MIMES), 'max:20480'],
        ], $this->validationMessages());
    }

    private function validationMessages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn ảnh tài liệu.',
            'file.file' => 'Tệp tải lên không hợp lệ.',
            'file.image' => 'Tệp tải lên phải là ảnh.',
            'file.mimes' => 'Chỉ chấp nhận ảnh định dạng JPG, JPEG hoặc PNG.',
            'file.max' => 'Ảnh không được vượt quá 20MB.',
            'category.required' => 'Vui lòng chọn phân loại tài liệu.',
            'category.in' => 'Phân loại tài liệu không hợp lệ.',
            'document_date.date' => 'Ngày tài liệu không hợp lệ.',
        ];
    }

    private function detectCollectionConflict(Request $request): string
    {
        if (!$this->lockCurrentUser()) {
            return 'user_deleted';
        }

        $submittedSnapshot = $request->input('document_collection_snapshot');

        if (!$submittedSnapshot || !hash_equals($this->makeCollectionSnapshot(Auth::id()), $submittedSnapshot)) {
            return 'changed_by_other';
        }

        return 'ok';
    }

    private function detectDocumentConflict(Request $request, int $documentId): string
    {
        if (!$this->lockCurrentUser()) {
            return 'user_deleted';
        }

        $document = MedicalDocument::whereKey($documentId)
            ->lockForUpdate()
            ->first();

        if (!$document) {
            return 'deleted';
        }

        $this->authorizeDocument($document);

        $submittedSnapshot = $request->input('document_snapshot');

        if (!$submittedSnapshot || !hash_equals($this->makeDocumentSnapshot($document), $submittedSnapshot)) {
            return 'changed_by_other';
        }

        return 'ok';
    }

    private function lockCurrentUser(): bool
    {
        return DB::table('users')
            ->where('user_id', Auth::id())
            ->lockForUpdate()
            ->first() !== null;
    }

    private function currentUserExists(): bool
    {
        return DB::table('users')
            ->where('user_id', Auth::id())
            ->exists();
    }

    private function redirectWhenUserMissing(): RedirectResponse
    {
        Auth::logout();

        return redirect()
            ->route('home')
            ->with('error', 'Dữ liệu tài khoản không còn tồn tại. Hệ thống đã chuyển bạn về trang chủ.');
    }

    private function redirectAfterCollectionConflict(string $result): RedirectResponse
    {
        if ($result === 'user_deleted') {
            return $this->redirectWhenUserMissing();
        }

        return redirect()
            ->route('documents.index')
            ->with('warning', 'Kho tài liệu đã được người khác thêm, sửa hoặc xoá trước đó. Hệ thống đã tải lại dữ liệu mới nhất, vui lòng kiểm tra rồi thao tác lại.');
    }

    private function redirectAfterDocumentConflict(string $result): RedirectResponse
    {
        if ($result === 'user_deleted') {
            return $this->redirectWhenUserMissing();
        }

        if ($result === 'deleted') {
            return redirect()
                ->route('documents.index')
                ->with('error', 'Tài liệu không còn tồn tại. Hệ thống đã tải lại danh sách tài liệu mới nhất.');
        }

        return redirect()
            ->route('documents.index')
            ->with('warning', 'Tài liệu đã được người khác cập nhật trước đó. Hệ thống đã tải lại dữ liệu mới nhất, vui lòng kiểm tra rồi thao tác lại.');
    }

    private function attachSnapshots($documents): void
    {
        $documents->each(function (MedicalDocument $document) {
            $document->document_snapshot = $this->makeDocumentSnapshot($document);
        });
    }

    private function makeCollectionSnapshot(int $userId): string
    {
        $documents = MedicalDocument::where('user_id', $userId)
            ->orderBy('doc_id')
            ->get()
            ->map(fn (MedicalDocument $document) => $this->documentSnapshotPayload($document))
            ->values()
            ->all();

        return hash('sha256', json_encode($documents, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    private function makeDocumentSnapshot(MedicalDocument $document): string
    {
        return hash('sha256', json_encode(
            $this->documentSnapshotPayload($document),
            JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        ));
    }

    private function documentSnapshotPayload(MedicalDocument $document): array
    {
        return [
            'doc_id' => $document->doc_id,
            'user_id' => $document->user_id,
            'record_id' => $document->record_id,
            'doc_type' => $document->doc_type,
            'doc_name' => $document->doc_name,
            'file_path' => $document->file_path,
            'uploaded_at' => optional($document->uploaded_at)->format('Y-m-d H:i:s'),
        ];
    }
}
