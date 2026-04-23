<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy thông tin user đang đăng nhập để hiển thị (tùy chọn)
        $user = Auth::user();
        
        return view('home', compact('user'));
    }
}