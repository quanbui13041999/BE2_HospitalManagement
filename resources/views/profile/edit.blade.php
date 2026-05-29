{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.user')

@section('title', 'Chỉnh sửa thông tin')

@section('content')
<div class="profile-wrapper">

    <div class="page-header">
        <a href="{{ route('profile.show') }}" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
            </svg>
            Quay lại
        </a>
        <h1 class="page-title">Chỉnh sửa thông tin cá nhân</h1>
    </div>

    @if(session('success'))
    <div class="form-alert success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
    <div class="form-alert warning">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
    <div class="form-alert error">{{ session('error') }}</div>
    @endif

    {{-- ===== FORM XÓA AVATAR — đặt NGOÀI form chính ===== --}}
    @if($user->avatar_url)
    <form id="deleteAvatarForm" action="{{ route('profile.avatar.delete') }}" method="POST" style="display:none">
        @csrf
        @method('DELETE')
        <input type="hidden" name="profile_snapshot" value="{{ $profileSnapshot }}">
    </form>
    @endif

    {{-- ===== FORM CHÍNH ===== --}}
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="profile_snapshot" value="{{ $profileSnapshot }}">

        {{-- Avatar upload --}}
        <div class="form-card">
            <h2 class="card-title">Ảnh đại diện</h2>
            <div class="avatar-upload-area">
                <div class="avatar-preview-wrap">
                    <img src="{{ $user->avatar_url ? asset('storage/' . $user->avatar_url) : asset('images/default-avatar.png') }}" alt="Avatar" class="avatar-preview" id="avatarPreview">
                    <label for="avatar" class="avatar-upload-btn" title="Thay đổi ảnh">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                            <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0z"/>
                        </svg>
                    </label>
                    <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="hidden-input" onchange="previewAvatar(this)">
                </div>
                <div class="avatar-meta">
                    <p class="avatar-hint">JPG, PNG, WEBP · Tối đa 2MB</p>
                    @if($user->avatar_url)
                        {{-- Nút này submit form xóa avatar ở bên ngoài --}}
                        <button type="button" class="btn-text-danger"
                            onclick="if(confirm('Xóa ảnh đại diện?')) document.getElementById('deleteAvatarForm').submit()">
                            Xóa ảnh hiện tại
                        </button>
                    @endif
                </div>
            </div>
            <p class="field-error" id="avatarClientError" style="display:none"></p>
            @error('avatar') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        {{-- Personal info --}}
        <div class="form-card">
            <h2 class="card-title">Thông tin cá nhân</h2>
            <div class="form-grid">

                <div class="form-group full">
                    <label for="full_name" class="form-label">Họ và tên <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name"
                        value="{{ old('full_name', $user->full_name) }}"
                        class="form-input @error('full_name') is-error @enderror"
                        maxlength="100" pattern="[A-Za-zÀ-ỹ\s.'-]+" title="Họ và tên chỉ được nhập chữ cái, khoảng trắng, dấu chấm, dấu gạch nối hoặc dấu nháy."
                        placeholder="Nhập họ và tên đầy đủ">
                    @error('full_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                        value="{{ old('email', $user->email) }}"
                        class="form-input @error('email') is-error @enderror"
                        maxlength="100"
                        placeholder="example@email.com">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        class="form-input @error('phone') is-error @enderror"
                        inputmode="numeric" minlength="10" maxlength="10" pattern="0[0-9]{9}" title="Số điện thoại phải đúng 10 chữ số và bắt đầu bằng số 0."
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
                            <input type="radio" name="gender" value="{{ $g }}"
                                {{ old('gender', $user->gender) === $g ? 'checked' : '' }}>
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
                        maxlength="255" pattern="[A-Za-zÀ-ỹ0-9\s,.\-/]+" title="Địa chỉ chỉ được nhập chữ, số, khoảng trắng và các dấu phân cách thông dụng."
                        placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành">
                    @error('address') <p class="field-error">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Submit --}}
        <div class="form-actions">
            <a href="{{ route('profile.show') }}" class="btn btn-outline">Hủy</a>
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1H2z"/>
                </svg>
                Lưu thay đổi
            </button>
        </div>
    </form>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const error = document.getElementById('avatarClientError');
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        const maxSize = 2 * 1024 * 1024;

        error.style.display = 'none';
        error.textContent = '';

        if (!allowedTypes.includes(file.type)) {
            input.value = '';
            error.textContent = 'Chỉ được chọn ảnh JPG, JPEG, PNG hoặc WEBP.';
            error.style.display = 'block';
            return;
        }

        if (file.size > maxSize) {
            input.value = '';
            error.textContent = 'Ảnh không được vượt quá 2MB.';
            error.style.display = 'block';
            return;
        }

        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(file);
    }
}
</script>

<style>
.profile-wrapper { max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
.page-header { margin-bottom: 1.5rem; }
.back-link { display: inline-flex; align-items: center; gap: .4rem; color: #6b7280;
    text-decoration: none; font-size: .875rem; margin-bottom: .5rem; }
.back-link:hover { color: #4f46e5; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0; }
.form-alert { padding: .85rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .875rem; font-weight: 600; }
.form-alert.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.form-alert.warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.form-alert.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

.form-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
    padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.card-title { font-size: 1rem; font-weight: 700; color: #111827; margin: 0 0 1.2rem;
    padding-bottom: .7rem; border-bottom: 1px solid #f3f4f6; }

/* Avatar */
.avatar-upload-area { display: flex; align-items: center; gap: 1.5rem; }
.avatar-preview-wrap { position: relative; display: inline-block; }
.avatar-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
    border: 3px solid #e0e7ff; display: block; }
.avatar-upload-btn { position: absolute; bottom: 0; right: 0; background: #4f46e5; color: #fff;
    width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; cursor: pointer; }
.avatar-upload-btn:hover { background: #4338ca; }
.hidden-input { display: none; }
.avatar-hint { font-size: .8rem; color: #9ca3af; margin: 0 0 .4rem; }
.btn-text-danger { background: none; border: none; color: #dc2626; font-size: .8rem;
    cursor: pointer; padding: 0; text-decoration: underline; }
.btn-text-danger:hover { color: #b91c1c; }

/* Form */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width:540px) { .form-grid { grid-template-columns: 1fr; } }
.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-group.full { grid-column: 1 / -1; }
.form-label { font-size: .875rem; font-weight: 600; color: #374151; }
.required { color: #ef4444; }
.form-input { padding: .6rem .85rem; border: 1px solid #d1d5db; border-radius: 8px;
    font-size: .875rem; color: #111827; background: #fff; transition: border .15s, box-shadow .15s;
    width: 100%; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
.form-input.is-error { border-color: #ef4444; }
.field-error { font-size: .8rem; color: #dc2626; margin: 0; }

/* Radio */
.radio-group { display: flex; gap: 1rem; flex-wrap: wrap; }
.radio-label { display: flex; align-items: center; gap: .4rem; cursor: pointer;
    font-size: .875rem; color: #374151; }

/* Actions */
.form-actions { display: flex; justify-content: flex-end; gap: .75rem; }
.btn { display: inline-flex; align-items: center; gap: .4rem; padding: .6rem 1.25rem;
    border-radius: 8px; font-size: .875rem; font-weight: 600; text-decoration: none;
    cursor: pointer; border: none; transition: all .15s; }
.btn-primary { background: #4f46e5; color: #fff; }
.btn-primary:hover { background: #4338ca; }
.btn-outline { background: #fff; color: #374151; border: 1px solid #d1d5db; }
.btn-outline:hover { background: #f9fafb; }
</style>
@endsection
