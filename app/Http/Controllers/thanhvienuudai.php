<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
class thanhvienuudai extends Controller
{
    public function member()
{
    // Lấy thông tin người dùng đang đăng nhập (object)
    $user = Auth::user();
    
    // Nếu chưa đăng nhập, nên redirect về trang login để tránh lỗi null
    if (!$user) {
        return redirect()->route('login');
    }

    // Truyền biến $user sang view 'thethanhvien'
    return view('thethanhvien', compact('user'));
}
}
