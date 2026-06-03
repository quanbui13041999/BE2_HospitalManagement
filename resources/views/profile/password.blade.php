{{-- resources/views/profile/password.blade.php --}}
@extends('layouts.user')

@section('title', 'Đổi mật khẩu')

@section('content')
<div class="profile-wrapper">
    <div class="page-header">
        <a href="{{ route('profile.show') }}" class="back-link">← Quay lại</a>
        <h1 class="page-title">Đổi mật khẩu</h1>
        <p class="page-subtitle">Mật khẩu mới phải có ít nhất 8 ký tự, chỉ gồm chữ và số, không có khoảng trắng hoặc ký tự đặc biệt, và khác mật khẩu hiện tại.</p>
    </div>

    @if(session('warning'))
        <div class="alert-warning-custom">{{ session('warning') }}</div>
    @endif

    <form action="{{ route('profile.password.update') }}" method="POST" class="form-card" id="passwordForm">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="required">*</span></label>
            <div class="input-wrap">
                <input type="password" id="current_password" name="current_password"
                       class="form-input @error('current_password') is-error @enderror"
                       maxlength="255" placeholder="Nhập mật khẩu hiện tại" autocomplete="current-password">
                <button type="button" class="toggle-pw" onclick="togglePw('current_password', this)" tabindex="-1">👁</button>
            </div>
            @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="divider"></div>

        <div class="form-group">
            <label for="new_password" class="form-label">Mật khẩu mới <span class="required">*</span></label>
            <div class="input-wrap">
                <input type="password" id="new_password" name="new_password"
                       class="form-input @error('new_password') is-error @enderror"
                       minlength="8" maxlength="72" pattern="[A-Za-z0-9]{8,72}"
                       title="Mật khẩu mới chỉ được nhập chữ và số, không nhập khoảng trắng hoặc ký tự đặc biệt."
                       placeholder="Ít nhất 8 ký tự" autocomplete="new-password" oninput="checkStrength(this.value)">
                <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)" tabindex="-1">👁</button>
            </div>
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
                       class="form-input" minlength="8" maxlength="72" pattern="[A-Za-z0-9]{8,72}"
                       title="Xác nhận mật khẩu chỉ được nhập chữ và số, không nhập khoảng trắng hoặc ký tự đặc biệt."
                       placeholder="Nhập lại mật khẩu mới" autocomplete="new-password">
                <button type="button" class="toggle-pw" onclick="togglePw('new_password_confirmation', this)" tabindex="-1">👁</button>
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
    if (/[a-z]/.test(val)) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    const levels = [
        { w: '25%', bg: '#ef4444', text: 'Rất yếu' },
        { w: '50%', bg: '#f97316', text: 'Yếu' },
        { w: '75%', bg: '#eab308', text: 'Trung bình' },
        { w: '100%', bg: '#22c55e', text: 'Mạnh' },
    ];
    const level = levels[Math.max(score - 1, 0)];
    fill.style.width = level.w;
    fill.style.background = level.bg;
    label.textContent = level.text;
    label.style.color = level.bg;
}
</script>

<style>
.profile-wrapper { max-width: 480px; margin: 2rem auto; padding: 0 1rem; }
.page-header { margin-bottom: 1.5rem; }
.back-link { display: inline-block; color: #6b7280; text-decoration: none; font-size: .875rem; margin-bottom: .5rem; }
.back-link:hover { color: #4f46e5; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0 0 .25rem; }
.page-subtitle { font-size: .875rem; color: #6b7280; margin: 0; }
.alert-warning-custom { background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:.75rem; margin-bottom:1rem; color:#c2410c; }
.form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); display: flex; flex-direction: column; gap: 1.1rem; }
.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-label { font-size: .875rem; font-weight: 600; color: #374151; }
.required { color: #ef4444; }
.input-wrap { position: relative; }
.form-input { width: 100%; box-sizing: border-box; padding: .6rem .85rem; padding-right: 2.5rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: .875rem; color: #111827; background: #fff; }
.form-input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
.form-input.is-error { border-color: #ef4444; }
.toggle-pw { position: absolute; right: .7rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #9ca3af; padding: 0; }
.field-error { font-size: .8rem; color: #dc2626; margin: 0; }
.divider { border-top: 1px solid #f3f4f6; margin: .25rem 0; }
.strength-bar-wrap { display: flex; align-items: center; gap: .6rem; margin-top: .3rem; }
.strength-bar { flex: 1; height: 5px; background: #f3f4f6; border-radius: 99px; overflow: hidden; }
.strength-fill { height: 100%; border-radius: 99px; transition: width .3s, background .3s; }
.strength-label { font-size: .75rem; font-weight: 600; white-space: nowrap; }
.form-actions { display: flex; justify-content: flex-end; gap: .75rem; padding-top: .25rem; }
.btn { display: inline-flex; align-items: center; gap: .4rem; padding: .6rem 1.25rem; border-radius: 8px; font-size: .875rem; font-weight: 600; text-decoration: none; cursor: pointer; border: none; }
.btn-primary { background: #4f46e5; color: #fff; }
.btn-outline { background: #fff; color: #374151; border: 1px solid #d1d5db; }
</style>
@endsection
