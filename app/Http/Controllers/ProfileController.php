<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang profile của người dùng đang đăng nhập.
     */
    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa profile.
     */
    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin cá nhân.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = User::find(Auth::id());
        $data = $request->validated();

        // Xử lý upload avatar
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->avatar_url) {
                Storage::disk('public')->delete($user->avatar_url);
            }
            $data['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Hiển thị form đổi mật khẩu.
     */
    public function editPassword(): View
    {
        return view('profile.password');
    }

    /**
     * Xử lý đổi mật khẩu.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = User::find(Auth::id());

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Đổi mật khẩu thành công!');
    }

    /**
     * Xóa avatar hiện tại.
     */
    public function deleteAvatar(): RedirectResponse
    {
        /** @var User $user */
        $user = User::find(Auth::id());

        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
            $user->update(['avatar_url' => null]);
        }

        return back()->with('success', 'Đã xóa ảnh đại diện.');
    }
}