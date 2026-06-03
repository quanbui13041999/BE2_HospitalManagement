<?php

namespace App\Http\Controllers;

use App\Models\HospitalNews;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $categories = ['Thông báo', 'Sức khỏe', 'Chương trình', 'Hướng dẫn', 'Khẩn cấp'];
        $validated = $request->validate([
            'category' => ['nullable', Rule::in($categories)],
            'page' => 'nullable|integer|min:1|max:1000',
        ]); /* fixed: validate category tu query string truoc khi loc */

        $category = $validated['category'] ?? null;

        $news = HospitalNews::published()
            ->ofCategory($category)
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        // Bài khẩn cấp hiển thị nổi bật riêng
        $urgent = HospitalNews::published()
            ->where('category', 'Khẩn cấp')
            ->latest('published_at')
            ->first();

        return view('news.index', compact('news', 'categories', 'category', 'urgent'));
    }

    public function show($id)
    {
        $article = HospitalNews::published()->findOrFail($id);
        return view('news.show', compact('article'));
    }
}
