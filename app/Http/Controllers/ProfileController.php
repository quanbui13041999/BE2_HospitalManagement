<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
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
    public function show(): View
    {
        $user = $this->currentUser();

        return view('profile.show', compact('user'));
    }

    public function edit(): View
    {
        $user = $this->currentUser();
        $profileSnapshot = $this->profileSnapshot($user);

        return view('profile.edit', compact('user', 'profileSnapshot'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $result = DB::transaction(function () use ($request): array {
            /** @var User|null $user */
            $user = User::where('user_id', Auth::id())->lockForUpdate()->first();

            if (! $user) {
                return ['saved' => false, 'message' => 'Tài khoản hiện tại đã bị xóa hoặc thay đổi. Vui lòng đăng nhập lại.'];
            }

            if ($request->input('profile_snapshot') !== $this->profileSnapshot($user)) {
                return ['saved' => false, 'message' => 'Hồ sơ đã có người sửa trước đó. Vui lòng tải lại dữ liệu mới nhất rồi cập nhật lại.'];
            }

            $data = $request->safe()->except(['avatar', 'profile_snapshot']);
            $oldAvatar = $user->avatar_url;

            if ($request->hasFile('avatar')) {
                $data['avatar_url'] = $request->file('avatar')->store('avatars', 'public');
            }

            $user->update($data);

            if (($data['avatar_url'] ?? null) && $oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }

            return ['saved' => true, 'message' => 'Cập nhật thông tin thành công!'];
        });

        if (! $result['saved']) {
            return redirect()
                ->route('profile.edit')
                ->with('warning', $result['message']);
        }

        return redirect()
            ->route('profile.show')
            ->with('success', $result['message']);
    }

    public function editPassword(): View
    {
        return view('profile.password');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $result = DB::transaction(function () use ($request): array {
            /** @var User|null $user */
            $user = User::where('user_id', Auth::id())->lockForUpdate()->first();

            if (! $user) {
                return ['saved' => false, 'field' => null, 'message' => 'Tài khoản hiện tại đã bị xóa hoặc thay đổi. Vui lòng đăng nhập lại.'];
            }

            if (! Hash::check($request->current_password, $user->password)) {
                return ['saved' => false, 'field' => 'current_password', 'message' => 'Mật khẩu hiện tại không đúng hoặc đã được đổi trước đó.'];
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return ['saved' => true, 'field' => null, 'message' => 'Đổi mật khẩu thành công!'];
        });

        if (! $result['saved']) {
            $redirect = back()->withInput();

            return $result['field']
                ? $redirect->withErrors([$result['field'] => $result['message']])
                : $redirect->with('warning', $result['message']);
        }

        return redirect()
            ->route('profile.show')
            ->with('success', $result['message']);
    }

    public function deleteAvatar(Request $request): RedirectResponse
    {
        $result = DB::transaction(function () use ($request): array {
            /** @var User|null $user */
            $user = User::where('user_id', Auth::id())->lockForUpdate()->first();

            if (! $user) {
                return ['deleted' => false, 'message' => 'Tài khoản hiện tại đã bị xóa hoặc thay đổi. Vui lòng đăng nhập lại.'];
            }

            if ($request->input('profile_snapshot') !== $this->profileSnapshot($user)) {
                return ['deleted' => false, 'message' => 'Ảnh đại diện hoặc hồ sơ đã có người sửa trước đó. Vui lòng tải lại dữ liệu mới nhất.'];
            }

            if (! $user->avatar_url) {
                return ['deleted' => false, 'message' => 'Ảnh đại diện đã được xóa trước đó. Vui lòng tải lại trang.'];
            }

            $avatar = $user->avatar_url;
            $user->update(['avatar_url' => null]);
            Storage::disk('public')->delete($avatar);

            return ['deleted' => true, 'message' => 'Đã xóa ảnh đại diện.'];
        });

        return back()->with($result['deleted'] ? 'success' : 'warning', $result['message']);
    }

    private function profileSnapshot(User $user): string
    {
        $payload = [
            'user_id' => $user->user_id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'date_of_birth' => $this->formatProfileDate($user->date_of_birth),
            'gender' => $user->gender,
            'avatar_url' => $user->avatar_url,
        ];

        return hash_hmac('sha256', json_encode($payload), (string) config('app.key'));
    }

    private function currentUser(): User
    {
        return User::findOrFail(Auth::id());
    }

    private function formatProfileDate(mixed $date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return $date ? (string) $date : null;
    }
}
