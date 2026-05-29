{{-- resources/views/profile/password.blade.php --}}
@extends('layouts.user')

@section('title', 'Đổi mật khẩu')

@section('content')
<div class="profile-wrapper">

    <div class="page-header">
        <a href="{{ route('profile.show') }}" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
            </svg>
            Quay lại
        </a>
        <h1 class="page-title">Đổi mật khẩu</h1>
        <p class="page-subtitle">Mật khẩu mới phải có ít nhất 8 ký tự và khác mật khẩu hiện tại.</p>
    </div>

    @if(session('warning'))
    <div class="form-alert warning">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
    <div class="form-alert error">{{ session('error') }}</div>
    @endif

    <form action="{{ route('profile.password.update') }}" method="POST" class="form-card" id="passwordForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="profile_snapshot" value="{{ $profileSnapshot }}">

        <div class="form-group">
            <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="required">*</span></label>
            <div class="input-wrap">
                <input type="password" id="current_password" name="current_password"
                    class="form-input @error('current_password') is-error @enderror"
                    placeholder="Nhập mật khẩu hiện tại" autocomplete="current-password">
                <button type="button" class="toggle-pw" onclick="togglePw('current_password', this)" tabindex="-1">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                    </svg>
                </button>
            </div>
            @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="divider"></div>

        <div class="form-group">
            <label for="new_password" class="form-label">Mật khẩu mới <span class="required">*</span></label>
            <div class="input-wrap">
                <input type="password" id="new_password" name="new_password"
                    class="form-input @error('new_password') is-error @enderror"
                    placeholder="Ít nhất 8 ký tự" autocomplete="new-password"
                    minlength="8" maxlength="255"
                    oninput="checkStrength(this.value)">
                <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)" tabindex="-1">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                    </svg>
                </button>
            </div>
            {{-- Strength bar --}}
            <div class="strength-bar-wrap" id="strengthWrap" style="display:none">
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <span class="strength-label" id="strengthLabel"></span>
            </div>
            @error('new_password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label for="new_password_confirmation" class="form-label">Xác nhận mật khẩu mới <span class="required">*</span></label>
            <div class="input-wrap">
                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                    class="form-input"
                    placeholder="Nhập lại mật khẩu mới" autocomplete="new-password">
                <button type="button" class="toggle-pw" onclick="togglePw('new_password_confirmation', this)" tabindex="-1">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('profile.show') }}" class="btn btn-outline">Hủy</a>
            <button type="submit" class="btn btn-primary">Cập nhật mật khẩu</button>
        </div>
    </form>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.style.color = input.type === 'text' ? '#4f46e5' : '#9ca3af';
}

function checkStrength(val) {
    const wrap = document.getElementById('strengthWrap');
    const fill = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'flex';
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        { w: '25%', bg: '#ef4444', text: 'Rất yếu' },
        { w: '50%', bg: '#f97316', text: 'Yếu' },
        { w: '75%', bg: '#eab308', text: 'Trung bình' },
        { w: '100%', bg: '#22c55e', text: 'Mạnh' },
    ];
    const l = levels[score - 1] || levels[0];
    fill.style.width = l.w;
    fill.style.background = l.bg;
    label.textContent = l.text;
    label.style.color = l.bg;
}
</script>

<style>
.profile-wrapper { max-width: 480px; margin: 2rem auto; padding: 0 1rem; }
.page-header { margin-bottom: 1.5rem; }
.back-link { display: inline-flex; align-items: center; gap: .4rem; color: #6b7280;
    text-decoration: none; font-size: .875rem; margin-bottom: .5rem; }
.back-link:hover { color: #4f46e5; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0 0 .25rem; }
.page-subtitle { font-size: .875rem; color: #6b7280; margin: 0; }
.form-alert { padding: .85rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .875rem; font-weight: 600; }
.form-alert.warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.form-alert.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.06); display: flex; flex-direction: column; gap: 1.1rem; }
.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-label { font-size: .875rem; font-weight: 600; color: #374151; }
.required { color: #ef4444; }
.input-wrap { position: relative; }
.form-input { width: 100%; box-sizing: border-box; padding: .6rem .85rem; padding-right: 2.5rem;
    border: 1px solid #d1d5db; border-radius: 8px; font-size: .875rem; color: #111827;
    background: #fff; transition: border .15s, box-shadow .15s; }
.form-input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
.form-input.is-error { border-color: #ef4444; }
.toggle-pw { position: absolute; right: .7rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: #9ca3af; padding: 0; }
.field-error { font-size: .8rem; color: #dc2626; margin: 0; }
.divider { border: none; border-top: 1px solid #f3f4f6; margin: .25rem 0; }
.strength-bar-wrap { display: flex; align-items: center; gap: .6rem; margin-top: .3rem; }
.strength-bar { flex: 1; height: 5px; background: #f3f4f6; border-radius: 99px; overflow: hidden; }
.strength-fill { height: 100%; border-radius: 99px; transition: width .3s, background .3s; }
.strength-label { font-size: .75rem; font-weight: 600; white-space: nowrap; }
.form-actions { display: flex; justify-content: flex-end; gap: .75rem; padding-top: .25rem; }
.btn { display: inline-flex; align-items: center; gap: .4rem; padding: .6rem 1.25rem;
    border-radius: 8px; font-size: .875rem; font-weight: 600; text-decoration: none;
    cursor: pointer; border: none; transition: all .15s; }
.btn-primary { background: #4f46e5; color: #fff; }
.btn-primary:hover { background: #4338ca; }
.btn-outline { background: #fff; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover { background: #f9fafb; }
</style>
@endsection
