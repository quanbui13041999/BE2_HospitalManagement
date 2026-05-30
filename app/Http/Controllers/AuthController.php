<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\WelcomeMail;
use App\Services\ActivityLogService;

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
            'password'      => 'required|min:6',
            'phone'         => 'nullable|string|max:15',
            'address'       => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender'        => 'nullable|in:Nam,Nữ,Khác',
        ], [
            'email.unique'      => 'Email này đã được sử dụng!',
            'password.min'      => 'Mật khẩu phải từ 6 ký tự trở lên.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
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
        ActivityLogService::log(
            'Đăng ký tài khoản',
            'Bệnh nhân ' . $user->full_name . ' đã đăng ký tài khoản mới.',
            'user',
            $user->user_id,
            ['email' => $user->email],
            'success',
            $user
        );
        // Gửi mail nhưng KHÔNG để lỗi mail chặn đăng ký

        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            Log::error('Mail lỗi: ' . $e->getMessage());
        }


        // BUG CŨ: redirect về /login sau khi đã login → sửa về trang đặt lịch
        return redirect()->route('appointments.create')
            ->with('success', 'Đăng ký thành công! Chào mừng ' . $user->full_name);
    }

    // ── ĐĂNG NHẬP ──
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $this->normalizeStoredPasswordHash($request->email);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            ActivityLogService::log(
                'Đăng nhập',
                Auth::user()->full_name . ' đã đăng nhập hệ thống.',
                'user',
                Auth::id()
            );
            return redirect()->intended(route('appointments.index'))
                ->with('success', 'Đăng nhập thành công!');
        }

        ActivityLogService::logFailed(
            'Đăng nhập thất bại',
            'Có lượt đăng nhập thất bại với email ' . $request->email . '.',
            'user',
            null,
            ['email' => $request->email]
        );

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ])->withInput($request->only('email'));

        // BUG CŨ: có 1 dòng return thứ 2 sau return back() → unreachable code, đã xóa
    }

    private function normalizeStoredPasswordHash(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user || !is_string($user->password)) {
            return;
        }

        $normalized = trim($user->password);

        if ($normalized === $user->password) {
            return;
        }

        if (password_get_info($normalized)['algoName'] !== 'bcrypt') {
            return;
        }

        $user->forceFill(['password' => $normalized])->save();
    }

    // ── ĐĂNG XUẤT ──
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLogService::log(
                'Đăng xuất',
                $user->full_name . ' đã đăng xuất khỏi hệ thống.',
                'user',
                $user->user_id,
                [],
                'success',
                $user
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
