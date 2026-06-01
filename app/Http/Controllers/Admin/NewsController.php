<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ConcurrentModificationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Mail\NewsPublishedMail;
use App\Models\HospitalNews;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;
use Carbon\Carbon;

class NewsController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    private function assertFreshVersion(?string $expectedVersion, mixed $actualVersion): void
    {
        if (!$expectedVersion || !$actualVersion) {
            return;
        }

        if (Carbon::parse($expectedVersion)->format('Y-m-d H:i:s') !== Carbon::parse($actualVersion)->format('Y-m-d H:i:s')) {
            throw new ConcurrentModificationException('Bài viết đã được người khác thay đổi. Trang sẽ được tải lại để cập nhật dữ liệu mới.');
        }
    }

    public function index()
    {
        $news = HospitalNews::with('author')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        $categories = ['Thông báo', 'Sức khỏe', 'Chương trình', 'Hướng dẫn', 'Khẩn cấp'];
        return view('admin.news.create', compact('categories'));
    }

    public function store(StoreNewsRequest $request)
    {
        $data = $request->validated();
        $data['author_id']    = Auth::id();
        $data['created_at']   = now();
        $data['is_published'] = $request->boolean('is_published');

        if ($data['is_published']) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->storeThumbnail($request);
        }

        try {
            $article = HospitalNews::create($data);
        } catch (Exception $e) {
            Log::error('Create news failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]); /* fixed: log loi DB noi bo, khong lo ra UI */

            return back()
                ->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'])
                ->withInput();
        }

        if ($article->is_published) {
            $this->notifications->createForAll(
                'Bản tin mới của bệnh viện',
                $article->title,
                'hospital_news',
                'news',
                $article->news_id,
                Auth::id(),
                route('news.show', $article->news_id)
            );
        }

        return redirect()->route('admin.news.index')
            ->with('success', 'Đã tạo bài viết thành công.');
    }

    public function edit($id)
    {
        $article    = HospitalNews::findOrFail($id);
        $categories = ['Thông báo', 'Sức khỏe', 'Chương trình', 'Hướng dẫn', 'Khẩn cấp'];
        return view('admin.news.edit', compact('article', 'categories'));
    }

    public function update(StoreNewsRequest $request, $id)
    {
        $data    = $request->validated();

        $oldThumbnail = null;
        $article = null;
        try {
            DB::transaction(function () use ($request, $id, &$article, &$data, &$oldThumbnail) {
                $article = HospitalNews::where('news_id', $id)
                    ->lockForUpdate()
                    ->firstOrFail(); /* fixed: khoa bai viet khi cap nhat de tranh ghi de song song */

                $this->assertFreshVersion($request->input('version'), $article->updated_at);

                $data['is_published'] = $request->boolean('is_published');

                if ($data['is_published'] && !$article->published_at) {
                    $data['published_at'] = now();
                }

                if ($request->hasFile('thumbnail')) {
                    $oldThumbnail = $article->thumbnail;
                    $data['thumbnail'] = $this->storeThumbnail($request);
                }

                $article->update($data);
            });
        } catch (Exception $e) {
            if ($e instanceof ConcurrentModificationException) {
                return redirect()->route('admin.news.index')
                    ->with('warning', $e->getMessage())
                    ->with('reload_page', true); /* fixed: nguoi submit sau duoc bao va reload danh sach */
            }

            Log::error('Update news failed', [
                'news_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.'])
                ->withInput();
        }

        if ($oldThumbnail) {
            $this->deleteThumbnail($oldThumbnail);
        }

        return redirect()->route('admin.news.index')
            ->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy(Request $request, $id)
    {
        $thumbnail = null;
        try {
            DB::transaction(function () use ($request, $id, &$thumbnail) {
                $article = HospitalNews::where('news_id', $id)
                    ->lockForUpdate()
                    ->firstOrFail(); /* fixed: khoa bai viet khi xoa */

                $this->assertFreshVersion($request->input('version'), $article->updated_at);
                $thumbnail = $article->thumbnail;
                $article->delete();
            });
        } catch (Exception $e) {
            if ($e instanceof ConcurrentModificationException) {
                return back()
                    ->with('warning', $e->getMessage())
                    ->with('reload_page', true);
            }

            Log::error('Delete news failed', [
                'news_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['msg' => 'Đã xảy ra lỗi, vui lòng thử lại sau.']);
        }

        if ($thumbnail) {
            $this->deleteThumbnail($thumbnail);
        }

        return redirect()->route('admin.news.index')
            ->with('success', 'Đã xóa bài viết.');
    }

    // Toggle published nhanh không cần vào form
    public function togglePublish(Request $request, $id)
    {
        $article = null;
        $wasPublished = false;

        try {
            DB::transaction(function () use ($request, $id, &$article, &$wasPublished) {
                $article = HospitalNews::where('news_id', $id)
                    ->lockForUpdate()
                    ->firstOrFail(); /* fixed: tranh 2 admin toggle trang thai cung luc */

                $this->assertFreshVersion($request->input('version'), $article?->updated_at);

                $wasPublished = (bool) $article?->is_published;
                $article->is_published = !($article?->is_published ?? false);
                if ($article->is_published && !$article->published_at) {
                    $article->published_at = now();
                }
                $article->save();
            });
        } catch (ConcurrentModificationException $e) {
            return back()
                ->with('warning', $e->getMessage())
                ->with('reload_page', true);
        }

        if ($article && ! $wasPublished && $article->is_published) {
            $this->notifications->createForAll(
                'Bản tin mới của bệnh viện',
                $article->title,
                'hospital_news',
                'news',
                $article->news_id,
                Auth::id(),
                route('news.show', $article->news_id)
            );
        }

        return back()->with('success', 'Đã thay đổi trạng thái bài viết.');
    }

    // Gửi email thông báo — chỉ gửi 1 lần, ghi lại email_sent = 1
    public function sendEmail(Request $request, $id)
    {
        // Lấy danh sách bệnh nhân (role_id = 3 based on User.php)
        $recipients = User::where('role_id', 3)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->reject(fn ($email) => preg_match('/^(a+|test|demo|fake|example)@/i', $email))
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            return back()->with('warning', 'Chưa có bệnh nhân nào có email để gửi thông báo.');
        }

        try {
            $article = DB::transaction(function () use ($request, $id) {
                $article = HospitalNews::where('news_id', $id)
                    ->lockForUpdate()
                    ->firstOrFail(); /* fixed: khoa bai viet khi gui mail de tranh bam gui song song */

                $this->assertFreshVersion($request->input('version'), $article->updated_at);

                if (!$article->is_published) {
                    throw new ConcurrentModificationException('Bài viết vừa chuyển về nháp. Trang sẽ được tải lại để cập nhật trạng thái.');
                }

                if ($article->email_sent) {
                    throw new ConcurrentModificationException('Email đã được người khác gửi trước đó. Trang sẽ được tải lại.');
                }

                $article->update(['email_sent' => 1]); /* fixed: danh dau trong lock de request sau khong gui trung */
                return $article->fresh();
            });
        } catch (ConcurrentModificationException $e) {
            return back()
                ->with('warning', $e->getMessage())
                ->with('reload_page', true);
        }

        $sentCount = 0;
        $failedEmails = [];

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new NewsPublishedMail($article));
                $sentCount++;
            } catch (Exception $e) {
                $failedEmails[] = $email;

                Log::warning('Failed to send news published email', [
                    'news_id' => $article->news_id,
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($sentCount === 0) {
            $article->update(['email_sent' => 0]);

            return back()->with(
                'warning',
                'Không gửi được email nào. Vui lòng kiểm tra danh sách email bệnh nhân và cấu hình SMTP.'
            );
        }

        $message = 'Đã gửi email thông báo thành công cho ' . $sentCount . ' bệnh nhân.';

        if (count($failedEmails) > 0) {
            $message .= ' Bỏ qua ' . count($failedEmails) . ' email lỗi.';
        }

        return back()->with('success', $message);
    }

    private function storeThumbnail(Request $request): string
    {
        $file = $request->file('thumbnail');
        $filename = 'news_' . Str::random(32) . '.' . $file->extension();

        return $file->storePubliclyAs('news', $filename, 'public'); /* fixed: upload qua storage/app/public, khong move truc tiep vao public/ */
    }

    private function deleteThumbnail(?string $thumbnail): void
    {
        if (! $thumbnail) {
            return;
        }

        $relativePath = filter_var($thumbnail, FILTER_VALIDATE_URL)
            ? ltrim(parse_url($thumbnail, PHP_URL_PATH) ?: '', '/')
            : $thumbnail;

        $relativePath = str($relativePath)->after('storage/')->toString();

        if (str_starts_with($relativePath, 'news/')) {
            Storage::disk('public')->delete($relativePath); /* fixed: xoa file qua Storage de tranh path traversal */
        }
    }
}
