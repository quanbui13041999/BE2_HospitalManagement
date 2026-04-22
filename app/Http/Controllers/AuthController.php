<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin() {
        return view('auth.login');
    }

    public function showRegister() {
        return view('auth.register');
    }

    // XỬ LÝ ĐĂNG KÝ (LẤY ĐỦ THÔNG TIN)
    public function register(Request $request)
    {
        // Validate toàn bộ thông tin
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'required|string|max:15',
            'address' => 'required|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
        ], [
            'email.unique' => 'Email này đã được sử dụng!',
            'password.min' => 'Mật khẩu phải từ 6 ký tự trở lên.',
        ]);

        // Tạo user mới
        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // BẮT BUỘC MÃ HÓA MẬT KHẨU
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'role_id' => 3, // Mặc định là quyền User thường (Ví dụ: 3)
            'status' => 1,  // Mặc định là Hoạt động
        ]);

        // Đăng nhập luôn sau khi đăng ký thành công
        Auth::login($user);

        return redirect('/login')->with('success', 'Đăng ký thành công!');
    }

    // XỬ LÝ ĐĂNG NHẬP
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/booking')->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
        
         return redirect('/booking')->with('success', 'Đăng ký thành công!');
    }

    // XỬ LÝ ĐĂNG XUẤT
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}