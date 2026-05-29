<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang profile của người dùng đang đăng nhập.
     */
    public function show(): View|RedirectResponse
    {
        /** @var User $user */
        $user = $this->currentUser();

        if (!$user) {
            return $this->redirectWhenUserMissing();
        }

        return view('profile.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa profile.
     */
    public function edit(): View|RedirectResponse
    {
        /** @var User $user */
        $user = $this->currentUser();

        if (!$user) {
            return $this->redirectWhenUserMissing();
        }

        $profileSnapshot = $this->makeSnapshot($user);

        return view('profile.edit', compact('user', 'profileSnapshot'));
    }

    /**
     * Cập nhật thông tin cá nhân.
     */
    public function update(Request $request): RedirectResponse
    {
        $conflict = DB::transaction(fn () => $this->detectWriteConflict($request), 3);

        if ($conflict !== 'ok') {
            return $this->redirectAfterWriteConflict($conflict, 'profile.edit');
        }

        $this->normalizeProfileInput($request);

        $data = $request->validate(
            UpdateProfileRequest::rulesFor(Auth::id()),
            UpdateProfileRequest::messagesFor()
        );
        unset($data['profile_snapshot'], $data['avatar']);

        $result = DB::transaction(function () use ($request, $data) {
            $conflict = $this->detectWriteConflict($request);

            if ($conflict !== 'ok') {
                return $conflict;
            }

            $user = User::where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if (!$user) {
                return 'deleted';
            }

            $oldAvatar = $user->avatar_url;

            if ($request->hasFile('avatar')) {
                $data['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
            }

            $user->update($data);

            if (($data['avatar_url'] ?? null) && $oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }

            return 'saved';
        }, 3);

        if ($result !== 'saved') {
            return $this->redirectAfterWriteConflict($result, 'profile.edit');
        }

        return redirect()
            ->route('profile.show')
            ->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Hiển thị form đổi mật khẩu.
     */
    public function editPassword(): View|RedirectResponse
    {
        $user = $this->currentUser();

        if (!$user) {
            return $this->redirectWhenUserMissing();
        }

        $profileSnapshot = $this->makeSnapshot($user);

        return view('profile.password', compact('profileSnapshot'));
    }

    /**
     * Xử lý đổi mật khẩu.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $conflict = DB::transaction(fn () => $this->detectWriteConflict($request), 3);

        if ($conflict !== 'ok') {
            return $this->redirectAfterWriteConflict($conflict, 'profile.password.edit');
        }

        $request->validate(
            UpdatePasswordRequest::rulesFor(),
            UpdatePasswordRequest::messagesFor()
        );

        $result = DB::transaction(function () use ($request) {
            $conflict = $this->detectWriteConflict($request);

            if ($conflict !== 'ok') {
                return $conflict;
            }

            $user = User::where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if (!$user) {
                return 'deleted';
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return 'wrong_password';
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return 'saved';
        }, 3);

        if ($result === 'wrong_password') {
            return back()
                ->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])
                ->withInput();
        }

        if ($result !== 'saved') {
            return $this->redirectAfterWriteConflict($result, 'profile.password.edit');
        }

        return redirect()
            ->route('profile.show')
            ->with('success', 'Đổi mật khẩu thành công!');
    }

    /**
     * Xóa avatar hiện tại.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $result = DB::transaction(function () use ($request) {
            $conflict = $this->detectWriteConflict($request);

            if ($conflict !== 'ok') {
                return $conflict;
            }

            $user = User::where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if (!$user) {
                return 'deleted';
            }

            if ($user->avatar_url) {
                $avatar = $user->avatar_url;
                $user->update(['avatar_url' => null]);
                Storage::disk('public')->delete($avatar);
            }

            return 'saved';
        }, 3);

        if ($result !== 'saved') {
            return $this->redirectAfterWriteConflict($result, 'profile.edit');
        }

        return back()->with('success', 'Đã xóa ảnh đại diện.');
    }

    private function currentUser(): ?User
    {
        return User::find(Auth::id());
    }

    private function redirectWhenUserMissing(): RedirectResponse
    {
        Auth::logout();

        return redirect()
            ->route('home')
            ->with('error', 'Dữ liệu hồ sơ cá nhân không còn tồn tại. Hệ thống đã chuyển bạn về trang chủ.');
    }

    private function redirectAfterWriteConflict(string $result, string $route): RedirectResponse
    {
        if ($result === 'deleted') {
            return $this->redirectWhenUserMissing();
        }

        return redirect()
            ->route($route)
            ->with('warning', 'Hồ sơ đã được người khác cập nhật trước đó. Hệ thống đã tải lại dữ liệu mới nhất, vui lòng kiểm tra rồi lưu lại nếu cần.');
    }

    private function normalizeProfileInput(Request $request): void
    {
        $request->merge([
            'full_name' => trim((string) $request->input('full_name', '')),
            'email' => trim((string) $request->input('email', '')),
            'phone' => $request->filled('phone') ? trim((string) $request->input('phone')) : null,
            'address' => $request->filled('address') ? trim((string) $request->input('address')) : null,
        ]);
    }

    private function detectWriteConflict(Request $request): string
    {
        $user = User::where('user_id', Auth::id())
            ->lockForUpdate()
            ->first();

        if (!$user) {
            return 'deleted';
        }

        if (!$this->hasSameSnapshot($user, $request->input('profile_snapshot'))) {
            return 'changed_by_other';
        }

        return 'ok';
    }

    private function hasSameSnapshot(User $user, ?string $submittedSnapshot): bool
    {
        if (!$submittedSnapshot) {
            return false;
        }

        return hash_equals($this->makeSnapshot($user), $submittedSnapshot);
    }

    private function makeSnapshot(User $user): string
    {
        return hash('sha256', json_encode([
            'user_id' => $user->user_id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'date_of_birth' => optional($user->date_of_birth)->format('Y-m-d'),
            'gender' => $user->gender,
            'avatar_url' => $user->avatar_url,
            'password' => $user->password,
            'status' => $user->status,
        ], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }
}
