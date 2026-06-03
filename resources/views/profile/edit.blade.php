{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.user')

@section('title', 'Chỉnh sửa thông tin')

@section('content')
<div class="profile-wrapper">
    <div class="page-header">
        <a href="{{ route('profile.show') }}" class="back-link">← Quay lại</a>
        <h1 class="page-title">Chỉnh sửa thông tin cá nhân</h1>
    </div>

    @if(session('warning'))
        <div class="alert-warning-custom">{{ session('warning') }}</div>
    @endif

    @if($user->avatar_url)
        <form id="deleteAvatarForm" action="{{ route('profile.avatar.delete') }}" method="POST" style="display:none">
            @csrf
            @method('DELETE')
            <input type="hidden" name="profile_snapshot" value="{{ $profileSnapshot }}">
        </form>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="profile_snapshot" value="{{ $profileSnapshot }}">

        <div class="form-card">
            <h2 class="card-title">Ảnh đại diện</h2>
            <div class="avatar-upload-area">
                <div class="avatar-preview-wrap">
                    <img src="{{ $user->avatar_url ? asset('storage/' . $user->avatar_url) : asset('images/default-avatar.png') }}"
                         alt="Avatar" class="avatar-preview" id="avatarPreview"
                         onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
                    <label for="avatar" class="avatar-upload-btn" title="Thay đổi ảnh">✎</label>
                    <input type="file" id="avatar" name="avatar"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                           class="hidden-input" onchange="previewAvatar(this)">
                </div>
                <div class="avatar-meta">
                    <p class="avatar-hint">JPG, PNG, WEBP · Tối đa 2MB</p>
                    @if($user->avatar_url)
                        <button type="button" class="btn-text-danger"
                                onclick="if(confirm('Xóa ảnh đại diện?')) document.getElementById('deleteAvatarForm').submit()">
                            Xóa ảnh hiện tại
                        </button>
                    @endif
                </div>
            </div>
            @error('avatar') <p class="field-error">{{ $message }}</p> @enderror
            @error('profile_snapshot') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="form-card">
            <h2 class="card-title">Thông tin cá nhân</h2>
            <div class="form-grid">
                <div class="form-group full">
                    <label for="full_name" class="form-label">Họ và tên <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name"
                           value="{{ old('full_name', $user->full_name) }}"
                           class="form-input @error('full_name') is-error @enderror"
                           maxlength="80" pattern="^[A-Za-zÀ-ỹ\s]+$"
                           placeholder="Nhập họ và tên đầy đủ">
                    @error('full_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $user->email) }}"
                           class="form-input @error('email') is-error @enderror"
                           maxlength="100" placeholder="example@email.com">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone"
                           value="{{ old('phone', $user->phone) }}"
                           class="form-input @error('phone') is-error @enderror"
                           maxlength="13" pattern="^(0|\+84)[0-9]{9,10}$"
                           placeholder="0901234567">
                    @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="date_of_birth" class="form-label">Ngày sinh</label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                           value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                           class="form-input @error('date_of_birth') is-error @enderror"
                           max="{{ now()->subDay()->format('Y-m-d') }}">
                    @error('date_of_birth') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Giới tính</label>
                    <div class="radio-group">
                        @foreach(['Nam', 'Nữ', 'Khác'] as $g)
                            <label class="radio-label">
                                <input type="radio" name="gender" value="{{ $g }}" @checked(old('gender', $user->gender) === $g)>
                                <span class="radio-text">{{ $g }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('gender') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group full">
                    <label for="address" class="form-label">Địa chỉ</label>
                    <input type="text" id="address" name="address"
                           value="{{ old('address', $user->address) }}"
                           class="form-input @error('address') is-error @enderror"
                           maxlength="150" pattern="^[A-Za-zÀ-ỹ0-9\s,.\-/]+$"
                           placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành">
                    @error('address') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('profile.show') }}" class="btn btn-outline">Hủy</a>
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
        </div>
    </form>
</div>

<script>
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowed.includes(file.type) || file.size > 2 * 1024 * 1024) {
        input.value = '';
        alert('Ảnh phải là JPG, PNG hoặc WEBP và không vượt quá 2MB.');
        return;
    }

    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
    reader.readAsDataURL(file);
}
</script>

<style>
.profile-wrapper { max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
.page-header { margin-bottom: 1.5rem; }
.back-link { color: #6b7280; text-decoration: none; font-size: .875rem; margin-bottom: .5rem; display: inline-block; }
.back-link:hover { color: #4f46e5; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0; }
.alert-warning-custom { background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:.75rem; margin-bottom:1rem; color:#c2410c; }
.form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.card-title { font-size: 1rem; font-weight: 700; color: #111827; margin: 0 0 1.2rem; padding-bottom: .7rem; border-bottom: 1px solid #f3f4f6; }
.avatar-upload-area { display: flex; align-items: center; gap: 1.5rem; }
.avatar-preview-wrap { position: relative; display: inline-block; }
.avatar-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #e0e7ff; display: block; }
.avatar-upload-btn { position: absolute; bottom: 0; right: 0; background: #4f46e5; color: #fff; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.hidden-input { display: none; }
.avatar-hint { font-size: .8rem; color: #9ca3af; margin: 0 0 .4rem; }
.btn-text-danger { background: none; border: none; color: #dc2626; font-size: .8rem; cursor: pointer; padding: 0; text-decoration: underline; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width:540px) { .form-grid { grid-template-columns: 1fr; } }
.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-group.full { grid-column: 1 / -1; }
.form-label { font-size: .875rem; font-weight: 600; color: #374151; }
.required { color: #ef4444; }
.form-input { padding: .6rem .85rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: .875rem; color: #111827; background: #fff; width: 100%; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
.form-input.is-error { border-color: #ef4444; }
.field-error { font-size: .8rem; color: #dc2626; margin: 0; }
.radio-group { display: flex; gap: 1rem; flex-wrap: wrap; }
.radio-label { display: flex; align-items: center; gap: .4rem; cursor: pointer; font-size: .875rem; color: #374151; }
.form-actions { display: flex; justify-content: flex-end; gap: .75rem; }
.btn { display: inline-flex; align-items: center; gap: .4rem; padding: .6rem 1.25rem; border-radius: 8px; font-size: .875rem; font-weight: 600; text-decoration: none; cursor: pointer; border: none; }
.btn-primary { background: #4f46e5; color: #fff; }
.btn-primary:hover { background: #4338ca; }
.btn-outline { background: #fff; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover { background: #f9fafb; }
</style>
@endsection
