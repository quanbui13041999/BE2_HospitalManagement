<?php

namespace App\Http\Controllers;

use App\Models\HospitalNews;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $category   = $request->query('category');
        $categories = ['Thông báo', 'Sức khỏe', 'Chương trình', 'Hướng dẫn', 'Khẩn cấp'];

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
