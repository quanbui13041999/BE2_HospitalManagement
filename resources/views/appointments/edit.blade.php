<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dời Lịch Khám – HospitalBooking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Be Vietnam Pro', sans-serif;
            background: linear-gradient(145deg, #f4f9ff 0%, #eef3fc 100%);
            color: #0b2b42;
            line-height: 1.5;
        }

        :root {
            --primary: #0f52ba;
            --primary-dark: #0a3d8f;
            --primary-soft: #eef4ff;
            --primary-glow: rgba(15, 82, 186, 0.08);
            --accent-teal: #1e8f9b;
            --gray-50: #f9fafc;
            --gray-100: #f2f5f9;
            --gray-200: #e9edf2;
            --gray-300: #dce2e8;
            --gray-400: #9aaebf;
            --gray-600: #4a5c6c;
            --gray-800: #1e2a3a;
            --white: #ffffff;
            --shadow-sm: 0 8px 20px rgba(0, 0, 0, 0.02), 0 2px 6px rgba(0, 20, 50, 0.05);
            --shadow-md: 0 12px 28px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.01);
            --shadow-lg: 0 20px 35px -12px rgba(0, 32, 64, 0.12);
            --radius-card: 24px;
            --radius-panel: 20px;
            --radius-btn: 40px;
            --radius-input: 16px;
        }

        /* Topbar mới – trắng sáng, hiện đại */
        .topbar {
            background: var(--white);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02), 0 1px 0 rgba(0, 0, 0, 0.03);
            padding: 0 32px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 110;
            border-bottom: 1px solid var(--gray-200);
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), #2b6ed7);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 12px -6px rgba(15, 82, 186, 0.3);
        }

        .logo-icon svg {
            width: 22px;
            height: 22px;
        }

        .brand-text {
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--gray-800);
            letter-spacing: -0.3px;
        }

        .brand-sub {
            font-size: 0.7rem;
            font-weight: 500;
            color: var(--gray-400);
            margin-top: 2px;
        }

        .topbar-center {
            display: flex;
            gap: 6px;
            background: var(--gray-100);
            padding: 4px;
            border-radius: 48px;
        }

        .topbar-center a {
            padding: 8px 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-600);
            text-decoration: none;
            border-radius: 40px;
            transition: all 0.2s;
        }

        .topbar-center a:hover, .topbar-center a.active {
            background: var(--white);
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--gray-100);
            padding: 5px 12px 5px 8px;
            border-radius: 48px;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            background: linear-gradient(145deg, var(--primary), #3279dc);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 0.85rem;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--gray-800);
        }

        .btn-logout {
            background: transparent;
            border: 1px solid var(--gray-300);
            border-radius: 32px;
            padding: 7px 18px;
            font-weight: 600;
            font-size: 0.75rem;
            color: var(--gray-600);
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-logout:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
            color: var(--primary-dark);
        }

        /* Breadcrumb */
        .breadcrumb-bar {
            padding: 14px 32px;
            font-size: 0.75rem;
            color: var(--gray-400);
            background: transparent;
            max-width: 1280px;
            margin: 0 auto;
            width: 100%;
        }

        .breadcrumb-bar a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        /* Layout chính */
        .page {
            max-width: 1000px;
            margin: 24px auto 48px;
            padding: 0 28px;
        }

        /* Page title */
        .page-title {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .page-title-icon {
            width: 54px;
            height: 54px;
            background: linear-gradient(145deg, var(--primary), #448af2);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 18px -8px rgba(15, 82, 186, 0.25);
        }

        .page-title h1 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--gray-800);
            letter-spacing: -0.3px;
        }

        .page-title p {
            font-size: 0.8rem;
            color: var(--gray-500);
            margin-top: 4px;
        }

        /* Panels */
        .panel {
            background: var(--white);
            border-radius: var(--radius-panel);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            margin-bottom: 28px;
            transition: transform 0.1s ease;
        }

        .panel-head {
            padding: 18px 28px;
            background: var(--white);
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary);
        }

        .icon-wrap.amber {
            background: #fff3e0;
            color: #d97706;
        }

        .icon-wrap.violet {
            background: #eef4ff;
            color: var(--primary);
        }

        .panel-head h2 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .panel-head p {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: 2px;
        }

        .panel-body {
            padding: 24px 28px;
        }

        /* Info grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px 32px;
        }

        .info-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--gray-400);
            margin-bottom: 6px;
        }

        .info-val {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .info-val.amber {
            color: #c2410c;
        }

        /* Badge status */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 700;
            border: 1px solid;
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .badge-pending {
            background: #fffbeb;
            border-color: #fde68a;
            color: #b45309;
        }

        .badge-pending .badge-dot {
            background: #f59e0b;
        }

        .badge-confirmed {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }

        .badge-confirmed .badge-dot {
            background: #10b981;
        }

        /* Schedule list */
        .schedule-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .schedule-option {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .schedule-option:hover {
            border-color: var(--primary);
            background: var(--primary-soft);
            transform: translateX(3px);
        }

        .schedule-option.selected {
            border-color: var(--primary);
            background: var(--primary-soft);
            box-shadow: 0 4px 12px rgba(15, 82, 186, 0.12);
        }

        .schedule-option input[type="radio"] {
            accent-color: var(--primary);
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            cursor: pointer;
        }

        .sch-date {
            font-weight: 800;
            font-size: 0.9rem;
            color: var(--gray-800);
        }

        .sch-time {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 4px;
        }

        .sch-slot {
            margin-left: auto;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 40px;
            flex-shrink: 0;
        }

        .sch-slot.ok {
            background: #e0f2fe;
            color: #0369a1;
        }

        .sch-slot.tight {
            background: #fef3c7;
            color: #b45309;
        }

        .empty-schedules {
            text-align: center;
            padding: 48px 20px;
            background: var(--gray-50);
            border-radius: 28px;
            color: var(--gray-400);
            font-size: 0.85rem;
        }

        .form-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--gray-500);
            margin-bottom: 8px;
            display: block;
        }

        .form-textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: 20px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            resize: vertical;
            min-height: 90px;
            transition: all 0.2s;
        }

        .form-textarea:focus {
            border-color: var(--primary);
            outline: none;
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(15, 82, 186, 0.08);
        }

        /* Form control inputs */
        .form-control {
            width: 100%;
            padding: 10px 14px;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(15, 82, 186, 0.08);
        }

        /* Buttons */
        .btn-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid var(--gray-200);
        }

        .btn {
            padding: 12px 28px;
            border-radius: 48px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-200);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(105deg, var(--primary), #2065cf);
            color: white;
            box-shadow: 0 4px 12px rgba(15, 82, 186, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px -6px rgba(15, 82, 186, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .alert {
            padding: 14px 20px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .alert-error {
            background: #fef2f2;
            border-left: 4px solid #e5484d;
            color: #b91c1c;
        }

        .alert-info {
            background: #eef4ff;
            border-left: 4px solid var(--primary);
            color: #1e3a8a;
        }

        .footer {
            background: var(--white);
            border-top: 1px solid var(--gray-200);
            text-align: center;
            padding: 28px;
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 40px;
        }

        @media (max-width: 680px) {
            .page { padding: 0 16px; }
            .topbar-center { display: none; }
            .info-grid { grid-template-columns: 1fr; gap: 14px; }
            .btn-row { flex-direction: column-reverse; }
            .btn { width: 100%; justify-content: center; }
            .schedule-option { flex-wrap: wrap; }
            .sch-slot { margin-left: 0; margin-top: 6px; }
        }
    </style>
</head>
<body>

{{-- Topbar hiện đại --}}
<nav class="topbar">
    <a href="{{ route('home') }}" class="topbar-brand">
        <div class="logo-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
            </svg>
        </div>
        <div>
            <div class="brand-text">HospitalBooking</div>
            <div class="brand-sub">Đặt lịch thông minh</div>
        </div>
    </a>
    <div class="topbar-center">
        <a href="{{ route('home') }}">🏠 Trang chủ</a>
        <a href="{{ route('appointments.index') }}">📋 Lịch hẹn</a>
        <a href="{{ route('appointments.create') }}">✨ Đặt lịch mới</a>
    </div>
    <div class="topbar-right">
        @auth
        <div class="user-pill">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <span class="user-name">{{ auth()->user()->name ?? 'Người dùng' }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="btn-logout">Đăng xuất</button>
        </form>
        @endauth
    </div>
</nav>

<div class="breadcrumb-bar">
    <a href="{{ route('home') }}">Trang chủ</a> <span style="margin:0 6px">/</span>
    <a href="{{ route('appointments.index') }}">Lịch hẹn của tôi</a> <span style="margin:0 6px">/</span>
    <span style="color:var(--gray-600); font-weight:500">Dời lịch</span>
</div>

<div class="page">

    <div class="page-title">
        <div class="page-title-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <polyline points="1 4 1 10 7 10" />
                <path d="M3.51 15a9 9 0 1 0 .49-4.95" />
            </svg>
        </div>
        <div>
            <h1>Dời Lịch Khám</h1>
            <p>Thay đổi khung giờ với cùng bác sĩ – nhanh chóng, tiện lợi</p>
        </div>
    </div>

    @if($errors->has('msg'))
    <div class="alert alert-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        {{ $errors->first('msg') }}
    </div>
    @endif

    @if($availableSchedules->isEmpty())
    <div class="alert alert-info">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        Không có lịch trống trong 14 ngày tới của BS. {{ $appointment->doctor_name }}. Vui lòng liên hệ phòng khám để được hỗ trợ.
    </div>
    @endif

    <form action="{{ route('appointments.update', $appointment->appointment_id) }}" method="POST" id="reschedule-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="new_appointment_time" id="new_appointment_time">

        {{-- Panel: Thông tin người dời lịch --}}
        <div class="panel">
            <div class="panel-head">
                <div class="icon-wrap">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div>
                    <h2>Thông tin người dời lịch</h2>
                    <p>Cập nhật thông tin cá nhân nếu cần</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="schedule-list" style="gap: 0;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                        <div>
                            <div class="form-label">Họ và tên</div>
                            <input type="text" name="full_name" class="form-control" 
                                value="{{ old('full_name', auth()->user()->full_name ?? '') }}" placeholder="Họ và tên">
                        </div>
                        <div>
                            <div class="form-label">Số điện thoại</div>
                            <input type="tel" name="phone" class="form-control" 
                                value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="Số điện thoại">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div>
                            <div class="form-label">Ngày sinh</div>
                            <input type="date" name="date_of_birth" class="form-control" 
                                value="{{ old('date_of_birth', auth()->user()->date_of_birth ?? '') }}">
                        </div>
                        <div>
                            <div class="form-label">Giới tính</div>
                            <select name="gender" class="form-control">
                                <option value="">-- Chọn --</option>
                                <option value="Nam" {{ old('gender', auth()->user()->gender ?? '') === 'Nam' ? 'selected' : '' }}>Nam</option>
                                <option value="Nữ" {{ old('gender', auth()->user()->gender ?? '') === 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                <option value="Khác" {{ old('gender', auth()->user()->gender ?? '') === 'Khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel 1: Thông tin lịch hiện tại --}}
        <div class="panel">
            <div class="panel-head">
                <div class="icon-wrap amber">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </div>
                <div>
                    <h2>Lịch hẹn hiện tại</h2>
                    <p>Thông tin lịch khám bạn muốn thay đổi</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Bác sĩ</div>
                        <div class="info-val amber">BS. {{ $appointment->doctor_name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Chuyên khoa</div>
                        <div class="info-val">{{ $appointment->department_name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ngày khám</div>
                        <div class="info-val">{{ \Carbon\Carbon::parse($appointment->work_date)->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Giờ khám</div>
                        <div class="info-val">{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</div>
                    </div>
                    @if($appointment->service_name)
                    <div class="info-item">
                        <div class="info-label">Dịch vụ</div>
                        <div class="info-val">{{ $appointment->service_name }}</div>
                    </div>
                    @endif
                    <div class="info-item">
                        <div class="info-label">Trạng thái</div>
                        @php $bc = $appointment->status === 'Đã xác nhận' ? 'badge-confirmed' : 'badge-pending'; @endphp
                        <span class="badge {{ $bc }}">
                            <span class="badge-dot"></span>{{ $appointment->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel 2: Chọn lịch mới --}}
        <div class="panel">
            <div class="panel-head">
                <div class="icon-wrap violet">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </div>
                <div>
                    <h2>Chọn lịch mới</h2>
                    <p>Khung giờ còn trống của BS. {{ $appointment->doctor_name }} trong 14 ngày tới</p>
                </div>
            </div>
            <div class="panel-body">
                @if($availableSchedules->isEmpty())
                <div class="empty-schedules">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <p style="margin-top: 10px;">Hiện tại chưa có lịch trống</p>
                    <p style="font-size:0.7rem">Vui lòng thử lại sau hoặc liên hệ tổng đài</p>
                </div>
                @else
                <div class="schedule-list">
                    @foreach($availableSchedules as $sch)
                    @php
                        $remaining = $sch->max_slot - $sch->booked_count;
                        $slotCls   = $remaining <= 2 ? 'tight' : 'ok';
                    @endphp
                    <label class="schedule-option" for="sch-{{ $sch->schedule_id }}">
                        <input type="radio"
                               id="sch-{{ $sch->schedule_id }}"
                               name="new_schedule_id"
                               value="{{ $sch->schedule_id }}"
                               data-time="{{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }}"
                               required
                               onchange="onScheduleSelect(this)">
                        <div style="flex:1">
                            <div class="sch-date">
                                {{ \Carbon\Carbon::parse($sch->work_date)->format('d/m/Y') }}
                                &nbsp;·&nbsp;
                                {{ \Carbon\Carbon::parse($sch->work_date)->isoFormat('dddd') }}
                            </div>
                            <div class="sch-time">
                                {{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }}
                                –
                                {{ \Carbon\Carbon::parse($sch->end_time)->format('H:i') }}
                            </div>
                        </div>
                        <span class="sch-slot {{ $slotCls }}">Còn {{ $remaining }} chỗ</span>
                    </label>
                    @endforeach
                </div>
                @error('new_schedule_id')
                <p style="font-size:0.7rem; color:#e5484d; margin-top: 10px;">{{ $message }}</p>
                @enderror
                @endif

                <div style="margin-top: 28px;">
                    <label class="form-label" for="reschedule_reason">
                        Lý do dời lịch <span style="text-transform: none; font-weight: 400;">(tùy chọn)</span>
                    </label>
                    <textarea name="reschedule_reason" id="reschedule_reason"
                        class="form-textarea"
                        placeholder="VD: bận công việc đột xuất, trùng lịch khám khác, sức khỏe chưa ổn…">{{ old('reschedule_reason') }}</textarea>
                </div>

                <div class="btn-row">
                    <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                        Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary" id="submit-btn"
                        {{ $availableSchedules->isEmpty() ? 'disabled' : '' }}>
                        <span class="spinner" id="spinner"></span>
                        <svg id="submit-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="1 4 1 10 7 10" />
                            <path d="M3.51 15a9 9 0 1 0 .49-4.95" />
                        </svg>
                        Xác nhận dời lịch
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<footer class="footer">
    © {{ date('Y') }} HospitalBooking · Nền tảng đặt lịch khám hiện đại · <a href="#" style="color:var(--primary); text-decoration: none;">Chính sách bảo mật</a>
</footer>

<script>
    function onScheduleSelect(radio) {
        document.getElementById('new_appointment_time').value = radio.dataset.time;
        document.querySelectorAll('.schedule-option').forEach(el => el.classList.remove('selected'));
        radio.closest('.schedule-option').classList.add('selected');
    }

    // Tự động chọn nếu chỉ có một lịch
    window.addEventListener('DOMContentLoaded', () => {
        const radios = document.querySelectorAll('input[name="new_schedule_id"]');
        if (radios.length === 1) {
            radios[0].checked = true;
            onScheduleSelect(radios[0]);
        }
        // Nếu có lỗi validation và đã chọn trước đó thì highlight
        const selectedRadio = document.querySelector('input[name="new_schedule_id"]:checked');
        if (selectedRadio) onScheduleSelect(selectedRadio);
    });

    // Xử lý submit loading
    document.getElementById('reschedule-form')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('submit-btn');
        const spinner = document.getElementById('spinner');
        const icon = document.getElementById('submit-icon');
        if (btn.disabled) return;
        btn.disabled = true;
        spinner.style.display = 'inline-block';
        icon.style.display = 'none';
    });
</script>
</body>
</html>