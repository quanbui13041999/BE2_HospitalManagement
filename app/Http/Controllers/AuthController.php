<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    // ── ĐĂNG KÝ ──
    public function register(Request $request)
    {
        $request->validate([
            'full_name'     => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:6|confirmed',   // thêm confirmed để check password_confirmation
            'phone'         => 'nullable|string|max:15',
            'address'       => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender'        => 'nullable|in:Nam,Nữ,Khác',
        ], [
            'email.unique'      => 'Email này đã được sử dụng!',
            'password.min'      => 'Mật khẩu phải từ 6 ký tự trở lên.',
            'password.confirmed'=> 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = User::create([
            'full_name'     => $request->full_name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'phone'         => $request->phone,
            'address'       => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender'        => $request->gender,
            'role_id'       => 3,   // User thường
            'status'        => 1,   // Hoạt động
        ]);

        Auth::login($user);

        // BUG CŨ: redirect về /login sau khi đã login → sửa về trang đặt lịch
        return redirect()->route('booking.create')
            ->with('success', 'Đăng ký thành công! Chào mừng ' . $user->full_name);
    }

    // ── ĐĂNG NHẬP ──
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('booking.create'))
                ->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ])->withInput($request->only('email'));

        // BUG CŨ: có 1 dòng return thứ 2 sau return back() → unreachable code, đã xóa
    }

    // ── ĐĂNG XUẤT ──
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}