{{-- resources/views/profile/show.blade.php --}}
@extends('layouts.user')

@section('title', 'Thông tin cá nhân')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
<style>
    /* 1. Thiết lập màu mặc định cho cả 2 nút (Nền trắng, viền xanh, chữ xanh) */
    .hero-actions .btn-primary-custom, 
    .hero-actions .btn-secondary-custom {
        background-color: #ffffff !important; /* BẮT BUỘC: Nền màu trắng mặc định */
        color: #007bff !important;            /* Chữ màu xanh dương mặc định */
        border: 2px solid #007bff !important; /* Viền màu xanh dương mặc định (tùy chọn) */
        display: inline-flex;
        align-items: center;
        gap: 6px;                             /* Khoảng cách giữa icon và chữ */
        text-decoration: none;                /* Bỏ gạch chân */
        padding: 8px 16px;                    /* Thêm padding để nút trông đẹp hơn (tùy chỉnh) */
        border-radius: 4px;                   /* Bo góc nút (tùy chỉnh) */
        transition: all 0.2s ease-in-out;      /* Hiệu ứng chuyển đổi mượt mà cho tất cả thuộc tính */
        font-weight: 500;                     /* Làm chữ đậm một chút (tùy chỉnh) */
    }

    /* Đảm bảo icon SVG dùng chung màu xanh dương của chữ */
    .hero-actions .btn-primary-custom svg, 
    .hero-actions .btn-secondary-custom svg {
        color: #007bff !important;
        fill: currentColor !important;         /* Icon dùng màu của `color` */
        transition: all 0.2s ease-in-out;
    }

    /* 2. Khi HOVER: Chuyển sang nền xanh dương, viền xanh dương, chữ trắng */
    .hero-actions .btn-primary-custom:hover, 
    .hero-actions .btn-secondary-custom:hover {
        background-color: #007bff !important; /* BẮT BUỘC: Nền màu xanh dương khi hover */
        color: #ffffff !important;            /* Chữ màu trắng khi hover */
        border-color: #007bff !important;      /* Viền giữ nguyên màu xanh (hoặc đổi thành xanh đậm hơn) */
        text-decoration: none;                 /* Đảm bảo không gạch chân khi hover */
    }

    /* Đảm bảo icon SVG cũng chuyển sang màu trắng khi hover nền xanh */
    .hero-actions .btn-primary-custom:hover svg, 
    .hero-actions .btn-secondary-custom:hover svg {
        color: #ffffff !important;
        fill: currentColor !important;         /* Icon chuyển màu trắng */
    }
</style>
@endpush

@section('content')
<div class="profile-container">

    {{-- Flash message --}}
    @if(session('success'))
    <div class="profile-alert success" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('warning'))
    <div class="profile-alert" role="alert" style="background:#fff7ed;border:1px solid #fed7aa;color:#c2410c;">
        <span>{{ session('warning') }}</span>
    </div>
    @endif

    {{-- Hero Section --}}
    <section class="profile-card hero-card">
        <div class="hero-left">

            <div class="avatar-shell">
                <img src="{{ $user->avatar_url ? asset('storage/' . $user->avatar_url) : asset('images/default-avatar.png') }}"
                    alt="Ảnh đại diện của {{ $user->full_name }}"
                    class="avatar-image">
                <span class="status-dot {{ $user->status ? 'online' : 'offline' }}"
                    title="{{ $user->status ? 'Đang hoạt động' : 'Không hoạt động' }}"></span>
            </div>

            <div class="hero-info">
                <h1>{{ $user->full_name }}</h1>
                <p>{{ $user->email }}</p>

                <span class="role-chip role-{{ $user->role_id }}">
                    @switch($user->role_id)
                    @case(1)
                    ✦ ADmin
                    @break
                    @case(2)
                    ◈ Bác sĩ
                    @break
                     @case(3)
                    ◈ Bệnh nhân
                    @break
                     @case(4)
                    ◈ Lễ Tân
                    @break
                    @default
                    ○ Dược sĩ
                    @endswitch
                </span>
            </div>

        </div>

        <div class="hero-actions">
    <a href="{{ route('profile.edit') }}" class="btn-primary-custom">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10z" />
        </svg>
        Chỉnh sửa hồ sơ
    </a>

    <a href="{{ route('profile.password.edit') }}" class="btn-secondary-custom">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
        </svg>
        Đổi mật khẩu
    </a>
</div>
    </section>

    {{-- Main Grid --}}
    <section class="profile-grid">

        {{-- Personal Information --}}
        <div class="profile-card info-card">
            <h2>Thông tin cá nhân </h2>

            <div class="info-group">

                <div class="info-item">
                    <label>Họ và tên</label>
                    <span>{{ $user->full_name }}</span>
                </div>

                <div class="info-item">
                    <label>Email</label>
                    <span>{{ $user->email }}</span>
                </div>

                <div class="info-item">
                    <label>Số điện thoại</label>
                    <span>{{ $user->phone ?? '—' }}</span>
                </div>

                <div class="info-item">
                    <label>Giới tính</label>
                    <span>{{ $user->gender ?? '—' }}</span>
                </div>

                <div class="info-item">
                    <label>Ngày sinh</label>
                    <span>
                        {{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : '—' }}
                    </span>
                </div>

                <div class="info-item">
                    <label>Địa chỉ</label>
                    <span>{{ $user->address ?? '—' }}</span>
                </div>

            </div>
        </div>

        {{-- Account Utilities --}}
        <div class="profile-card quick-links-card">
            <h2>Tiện ích tài khoản</h2>

            <div class="quick-links">

                <a href="{{ route('health.store') }}" class="quick-link-item">
                    <div class="link-icon">📋</div>
                    <div>
                        <strong>Tiền sử & dị ứng</strong>
                        <small>Xem hồ sơ sức khỏe cá nhân</small>
                    </div>
                </a>

                <a href="{{ route('membership.show') }}" class="quick-link-item">
                    <div class="link-icon">💳</div>
                    <div>
                        <strong>Thẻ thành viên</strong>
                        <small>Quản lý quyền lợi thành viên</small>
                    </div>
                </a>
                <a href="{{ route('emergency-contacts.index') }}" class="quick-link-item">
                    <div class="link-icon">💳</div>
                    <div>
                        <strong>Liên hệ khẩn câp</strong>
                        <small>Hỗ trợ mạng lưới và liên hệ</small>
                    </div>
                </a>
                 <a href="{{ route('health-tracking.index') }}" class="quick-link-item">
                    <div class="link-icon">📓</div>
                    <div>
                        <strong>
                            {{ auth()->user()->isPatient() ? 'Nhật kí sức khỏe' : 'Nhật kí sức khỏe bệnh nhân' }}
                        </strong>
                        
                    </div>
                </a>
                <a href="{{ route('documents.index') }}" class="quick-link-item">
                    <div class="link-icon">📁</div>
                    <div>
                        <strong>Kho tài liệu y tế</strong>
                        <small>Lưu trữ và truy cập tài liệu sức khỏe</small>
                    </div>
                </a>
            </div>
        </div>

    </section>

</div>
@endsection
