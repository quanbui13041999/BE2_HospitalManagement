<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Mail\NewsPublishedMail;
use App\Models\HospitalNews;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class NewsController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

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

        $article = HospitalNews::create($data);

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
        $article = HospitalNews::findOrFail($id);
        $data    = $request->validated();
        $data['is_published'] = $request->boolean('is_published');

        if ($data['is_published'] && !$article->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('thumbnail')) {
            $this->deleteThumbnail($article->thumbnail);
            $data['thumbnail'] = $this->storeThumbnail($request);
        }

        $article->update($data);

        return redirect()->route('admin.news.index')
            ->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy($id)
    {
        $article = HospitalNews::findOrFail($id);
        if ($article->thumbnail) {
            $this->deleteThumbnail($article->thumbnail);
        }
        $article->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'Đã xóa bài viết.');
    }

    // Toggle published nhanh không cần vào form
    public function togglePublish($id)
    {
        $article = HospitalNews::findOrFail($id);
        $wasPublished = (bool) $article->is_published;
        $article->is_published = !$article->is_published;
        if ($article->is_published && !$article->published_at) {
            $article->published_at = now();
        }
        $article->save();

        if (! $wasPublished && $article->is_published) {
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
    public function sendEmail($id)
    {
        $article = HospitalNews::findOrFail($id);

        if (!$article->is_published) {
            return back()->with('warning', 'Chỉ có thể gửi email cho bài viết đã đăng.');
        }

        if ($article->email_sent) {
            return back()->with('warning', 'Email đã được gửi trước đó, không gửi lại.');
        }

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
            return back()->with(
                'warning',
                'Không gửi được email nào. Vui lòng kiểm tra danh sách email bệnh nhân và cấu hình SMTP.'
            );
        }

        $article->update(['email_sent' => 1]);

        $message = 'Đã gửi email thông báo thành công cho ' . $sentCount . ' bệnh nhân.';

        if (count($failedEmails) > 0) {
            $message .= ' Bỏ qua ' . count($failedEmails) . ' email lỗi.';
        }

        return back()->with('success', $message);
    }

    private function storeThumbnail(Request $request): string
    {
        $file = $request->file('thumbnail');
        $directory = public_path('uploads/news');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = uniqid('news_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return asset('uploads/news/' . $filename);
    }

    private function deleteThumbnail(?string $thumbnail): void
    {
        if (! $thumbnail) {
            return;
        }

        $relativePath = $thumbnail;

        if (filter_var($thumbnail, FILTER_VALIDATE_URL)) {
            $relativePath = ltrim(parse_url($thumbnail, PHP_URL_PATH) ?: '', '/');
        }

        if (! str_starts_with($relativePath, 'uploads/news/')) {
            return;
        }

        $path = public_path($relativePath);

        if (is_file($path)) {
            unlink($path);
        }
    }
}
