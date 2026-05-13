<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Mail\NewsPublishedMail;
use App\Models\HospitalNews;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Exception;

class NewsController extends Controller
{
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
            $data['thumbnail'] = $request->file('thumbnail')->store('news', 'public');
        }

        $article = HospitalNews::create($data);

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
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('news', 'public');
        }

        $article->update($data);

        return redirect()->route('admin.news.index')
            ->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy($id)
    {
        $article = HospitalNews::findOrFail($id);
        if ($article->thumbnail) {
            Storage::disk('public')->delete($article->thumbnail);
        }
        $article->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'Đã xóa bài viết.');
    }

    // Toggle published nhanh không cần vào form
    public function togglePublish($id)
    {
        $article = HospitalNews::findOrFail($id);
        $article->is_published = !$article->is_published;
        if ($article->is_published && !$article->published_at) {
            $article->published_at = now();
        }
        $article->save();

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
            ->pluck('email');

        if ($recipients->isEmpty()) {
            return back()->with('warning', 'Chưa có bệnh nhân nào có email để gửi thông báo.');
        }

        try {
            foreach ($recipients as $email) {
                Mail::to($email)->send(new NewsPublishedMail($article));
            }
        } catch (Exception $e) {
            Log::warning('Failed to send news published email', [
                'news_id' => $article->news_id,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'warning',
                'Không gửi được email. Vui lòng kiểm tra cấu hình SMTP/MAIL_PASSWORD trong file .env.'
            );
        }

        $article->update(['email_sent' => 1]);

        return back()->with('success', 'Đã gửi email thông báo thành công cho ' . $recipients->count() . ' bệnh nhân.');
    }
}
