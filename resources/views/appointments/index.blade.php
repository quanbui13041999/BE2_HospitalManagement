<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lịch Khám Của Tôi – HospitalC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        /* (toàn bộ CSS giữ nguyên như file của bạn) */
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

        .topbar-center a:hover,
        .topbar-center a.active {
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

        .breadcrumb-bar .sep {
            color: var(--gray-300);
            margin: 0 6px;
        }

        .page {
            max-width: 1280px;
            margin: 24px auto 48px;
            padding: 0 28px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .page-header-icon {
            width: 54px;
            height: 54px;
            background: linear-gradient(145deg, var(--primary), #448af2);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 18px -8px rgba(15, 82, 186, 0.25);
        }

        .page-header h1 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--gray-800);
            letter-spacing: -0.3px;
        }

        .page-header p {
            font-size: 0.8rem;
            color: var(--gray-500);
            margin-top: 4px;
        }

        .btn-book-new {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(105deg, var(--primary), #2065cf);
            color: white;
            padding: 10px 24px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(15, 82, 186, 0.3);
            transition: all 0.2s;
        }

        .btn-book-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px -6px rgba(15, 82, 186, 0.4);
        }

        .alert-success {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            border-radius: 20px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            font-size: 0.85rem;
            color: #065f46;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 24px;
            padding: 18px 22px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-md);
            transition: transform 0.1s ease;
        }

        .stat-card .stat-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--gray-400);
        }

        .stat-card .stat-num {
            font-size: 1.8rem;
            font-weight: 800;
            margin-top: 8px;
        }

        .stat-card .stat-num.blue {
            color: var(--primary);
        }

        .stat-card .stat-num.green {
            color: #10b981;
        }

        .stat-card .stat-num.yellow {
            color: #f59e0b;
        }

        .stat-card .stat-num.red {
            color: #ef4444;
        }

        .card {
            background: var(--white);
            border-radius: 28px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .card-top {
            padding: 18px 24px;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-top-title {
            font-weight: 800;
            font-size: 0.95rem;
            color: var(--gray-800);
        }

        .card-top-sub {
            font-size: 0.7rem;
            color: var(--gray-400);
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }

        thead th {
            padding: 16px 20px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--gray-500);
            text-align: left;
        }

        tbody tr {
            border-bottom: 1px solid var(--gray-100);
            transition: background 0.15s;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: var(--primary-soft);
        }

        tbody td {
            padding: 16px 20px;
            font-size: 0.85rem;
            color: var(--gray-700);
            vertical-align: middle;
        }

        .doctor-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(145deg, var(--primary), #448af2);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 8px rgba(15, 82, 186, 0.2);
        }

        .doctor-name {
            font-weight: 700;
            color: var(--gray-800);
        }

        .date-cell .date {
            font-weight: 700;
            color: var(--gray-800);
        }

        .date-cell .time {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: 4px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 700;
            border: 1px solid;
            white-space: nowrap;
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

        .badge-cancelled {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }

        .badge-cancelled .badge-dot {
            background: #dc2626;
        }

        .badge-done {
            background: var(--gray-100);
            border-color: var(--gray-200);
            color: var(--gray-600);
        }

        .badge-done .badge-dot {
            background: var(--gray-400);
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-edit,
        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-edit {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--gray-200);
        }

        .btn-edit:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .btn-cancel:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            transform: translateY(-1px);
        }

        .btn-pay {
            background: #065f46;
            color: white;
            border-color: #065f46;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 10px rgba(6, 95, 70, 0.2);
        }

        .btn-pay:hover {
            background: #047857;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(6, 95, 70, 0.3);
            color: white;
        }

        .payment-status {
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 4px;
            display: block;
        }

        .payment-unpaid {
            color: #dc2626;
        }

        .payment-paid {
            color: #059669;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: var(--gray-400);
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            color: var(--gray-300);
        }

        .empty-state p {
            font-size: 0.9rem;
            color: var(--gray-500);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 22, 40, 0.6);
            backdrop-filter: blur(6px);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: var(--white);
            border-radius: 32px;
            width: 100%;
            max-width: 440px;
            padding: 28px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalIn 0.2s ease;
        }

        @keyframes modalIn {
            from {
                transform: scale(0.96);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal-icon {
            width: 64px;
            height: 64px;
            background: #fef2f2;
            border-radius: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }

        .modal h3 {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--gray-800);
            margin-bottom: 8px;
        }

        .modal p {
            font-size: 0.85rem;
            color: var(--gray-500);
            margin-bottom: 20px;
        }

        .modal textarea {
            width: 100%;
            padding: 12px 14px;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: 20px;
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            resize: vertical;
            min-height: 80px;
            margin-bottom: 20px;
            outline: none;
            transition: all 0.2s;
        }

        .modal textarea:focus {
            border-color: var(--primary);
            background: var(--white);
        }

        .modal-btns {
            display: flex;
            gap: 12px;
        }

        .modal-cancel-btn,
        .modal-confirm-btn {
            flex: 1;
            padding: 12px;
            border-radius: 60px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .modal-cancel-btn {
            background: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-200);
        }

        .modal-cancel-btn:hover {
            background: var(--gray-200);
        }

        .modal-confirm-btn {
            background: linear-gradient(105deg, #dc2626, #b91c1c);
            color: white;
        }

        .modal-confirm-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .footer {
            background: var(--white);
            border-top: 1px solid var(--gray-200);
            text-align: center;
            padding: 28px;
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 48px;
        }

        .footer a {
            color: var(--primary);
            text-decoration: none;
        }

        .review-summary {
            font-size: 0.75rem;
            flex: 1;
            min-width: 150px;
            padding: 8px 12px;
            background: var(--gray-50);
            border-radius: 12px;
            border: 1px solid var(--gray-200);
        }

        .review-summary-stars {
            color: #f59e0b;
            margin-bottom: 4px;
        }

        .review-summary-comment {
            color: var(--gray-600);
            margin-top: 2px;
            display: block;
        }

        .review-summary-reply {
            color: var(--gray-400);
            margin-top: 2px;
            font-style: italic;
            display: block;
        }

        @media (max-width: 860px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }

            .topbar-center {
                display: none;
            }

            .page {
                padding: 0 16px;
            }

            thead th:nth-child(2) {
                display: none;
            }

            tbody td:nth-child(2) {
                display: none;
            }
        }

        @media (max-width: 580px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .actions {
                flex-direction: column;
                gap: 6px;
            }

            .btn-edit,
            .btn-cancel {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    {{-- TOPBAR --}}
    <nav class="topbar">
        <a href="{{ route('home') }}" class="topbar-brand">
            <div class="logo-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
            </div>
            <div>
                <div class="brand-text">HospitalC</div>
                <div class="brand-sub">Đặt lịch thông minh</div>
            </div>
        </a>
        <div class="topbar-center">
            <a href="{{ route('home') }}">🏠 Trang chủ</a>
            <a href="{{ route('appointments.index') }}" class="active">📋 Lịch hẹn</a>
            <a href="{{ route('appointments.create') }}">✨ Đặt lịch mới</a>
            <a href="{{ route('news.index') }}">📰 Bản tin</a>
            @auth
                @if(auth()->user()->isPatient())
                    <a href="{{ route('medical_history.index') }}">📄 Hồ sơ bệnh án</a>
                @elseif(auth()->user()->isDoctor())
                    <a href="{{ route('doctor.dashboard') }}">🩺 Dashboard bác sĩ</a>
                @endif
            @endauth
        </div>
        <div class="topbar-right" style="display:flex;align-items:center;gap:10px;">
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

    {{-- BREADCRUMB --}}
    <div class="breadcrumb-bar">
        <a href="{{ route('home') }}">Trang chủ</a>
        <span class="sep">›</span>
        <span style="color:var(--gray-600);font-weight:500;">Lịch hẹn của tôi</span>
    </div>

    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                <div class="page-header-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </div>
                <div>
                    <h1>Lịch Khám Của Tôi</h1>
                    <p>Quản lý và theo dõi các lịch hẹn khám bệnh</p>
                </div>
            </div>
            <a href="{{ route('appointments.create') }}" class="btn-book-new">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Đặt lịch mới
            </a>
        </div>

        @if(session('success'))
        <div class="alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            {{ session('success') }}
            @if(session('appointment_id'))
                <a href="{{ route('user.payments.show', session('appointment_id')) }}" class="btn-pay" style="margin-left: auto; padding: 8px 20px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <rect x="2" y="5" width="20" height="14" rx="2" />
                        <line x1="2" y1="10" x2="22" y2="10" />
                    </svg>
                    Thanh toán ngay
                </a>
            @endif
        </div>
        @endif

        @php
        $total = $appointments->count();
        $pending = $appointments->where('status', 'Chờ xác nhận')->count();
        $confirmed = $appointments->where('status', 'Đã xác nhận')->count();
        $done = $appointments->where('status', 'Hoàn thành')->count();
        @endphp

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">Tổng lịch hẹn</div>
                <div class="stat-num blue">{{ $total }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Chờ xác nhận</div>
                <div class="stat-num yellow">{{ $pending }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Đã xác nhận</div>
                <div class="stat-num green">{{ $confirmed }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Hoàn thành</div>
                <div class="stat-num" style="color:var(--gray-500);">{{ $done }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-top">
                <div>
                    <div class="card-top-title">Danh sách lịch hẹn</div>
                    <div class="card-top-sub">Hiển thị {{ $total }} lịch hẹn của bạn</div>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Bác sĩ</th>
                            <th>Dịch vụ</th>
                            <th>Ngày khám</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $item)
                        <tr>
                            {{-- CỘT BÁC SĨ --}}
                            <td>
                                <div class="doctor-cell">
                                    @php
                                    $words = explode(' ', trim($item->doctor_name));
                                    $initials = count($words) >= 2
                                    ? strtoupper(mb_substr($words[count($words)-2],0,1).mb_substr($words[count($words)-1],0,1))
                                    : strtoupper(mb_substr($item->doctor_name,0,2));
                                    @endphp
                                    <div class="avatar">{{ $initials }}</div>
                                    <div>
                                        <div class="doctor-name">BS. {{ $item->doctor_name }}</div>
                                        <div style="font-size:.7rem;color:var(--gray-400)">{{ $item->department_name }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- CỘT DỊCH VỤ --}}
                            <td>{{ $item->service_name ?? '—' }}</td>

                            {{-- CỘT NGÀY KHÁM --}}
                            <td>
                                <div class="date-cell">
                                    <div class="date">{{ \Carbon\Carbon::parse($item->work_date)->format('d/m/Y') }}</div>
                                    <div class="time">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}
                                    </div>
                                </div>
                            </td>

                            {{-- CỘT TRẠNG THÁI --}}
                            <td>
                                @php
                                $statusMap = [
                                'Chờ xác nhận' => 'badge-pending',
                                'Đã xác nhận' => 'badge-confirmed',
                                'Đã hủy' => 'badge-cancelled',
                                'Dời lịch' => 'badge-cancelled',
                                'Đã thanh toán' => 'badge-confirmed',
                                'Đã khám' => 'badge-done',
                                'Hoàn thành' => 'badge-done',
                                ];
                                $badgeClass = $statusMap[$item->status] ?? 'badge-pending';
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    <span class="badge-dot"></span>
                                    {{ $item->status }}
                                </span>
                                @if(!in_array($item->status, ['Đã hủy', 'Dời lịch', 'Bác sĩ nghỉ']))
                                    @if($item->payment_status)
                                        <span class="payment-status payment-paid">✓ Đã thanh toán</span>
                                    @else
                                        <span class="payment-status payment-unpaid">✗ Chưa thanh toán</span>
                                    @endif
                                @endif
                            </td>

                            {{-- CỘT THAO TÁC --}}
                            <td>
                                @php
                                    $appointmentTime = \Carbon\Carbon::parse($item->appointment_time);
                                    $canCancelAppointment = in_array($item->status, ['Chờ xác nhận', 'Đã xác nhận', 'Đã thanh toán'])
                                        && now()->lt($appointmentTime)
                                        && now()->lte($appointmentTime->copy()->subHour());
                                    $canManageAppointment = in_array($item->status, ['Chờ xác nhận', 'Đã xác nhận', 'Đã thanh toán']);
                                @endphp

                                @if($canManageAppointment)
                                {{-- Dời / Huỷ --}}
                                 <div class="actions">
                                    @if(empty($item->payment_status))
                                        <a href="{{ route('user.payments.show', $item->appointment_id) }}" class="btn-pay">
                                            💳 Thanh toán
                                        </a>
                                    @endif
                                    @if(in_array($item->status, ['Chờ xác nhận', 'Đã xác nhận']))
                                        <a href="{{ route('appointments.edit', $item->appointment_id) }}" class="btn-edit">
                                            📅 Dời lịch
                                        </a>
                                    @endif
                                    @if($canCancelAppointment)
                                        <button type="button" class="btn-cancel"
                                            onclick="openModal(this)"
                                            data-action="{{ route('appointments.cancel', $item->appointment_id) }}">
                                            ✕ Huỷ
                                        </button>
                                    @else
                                        <span class="payment-status payment-unpaid">Không thể hủy trong vòng 1 giờ trước giờ khám</span>
                                    @endif
                                </div>

                                @elseif(in_array($item->status, ['Đã khám', 'Đã Khám', 'Hoàn thành', 'Hoàn Thành']))
                                @php
                                // ← FIX: dùng ?? null để tránh Undefined property
                                $rawReviewDate = $item->review_created_at ?? null;
                                $reviewCreatedAt = $rawReviewDate ? \Carbon\Carbon::parse($rawReviewDate) : null;
                                $canEdit = !empty($rawReviewDate)
                                && $reviewCreatedAt && $reviewCreatedAt->diffInHours(now()) <= 24;
                                    $isAdmin=auth()->user()->role === 'admin';
                                    $isDoctor = auth()->user()->role === 'doctor';
                                    @endphp

                                    <div class="actions" style="flex-wrap:wrap">
                                        @if(!empty($item->review_id))
                                        {{-- Đã có đánh giá: hiển thị tóm tắt --}}
                                        <div class="review-summary">
                                            <div class="review-summary-stars">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span style="color:{{ $i <= ($item->review_rating ?? 0) ? '#f59e0b' : '#d1d5db' }}">★</span>
                                                    @endfor
                                            </div>
                                            @if($item->review_comment ?? null)
                                            <div class="review-summary-comment">
                                                {{ Str::limit($item->review_comment, 40) }}
                                            </div>
                                            @endif
                                            @if($item->doctor_reply ?? null)
                                            <div class="review-summary-reply">
                                                💬 {{ Str::limit($item->doctor_reply, 30) }}
                                            </div>
                                            @endif
                                        </div>

                                        {{-- Sửa đánh giá: trong 24h hoặc admin --}}
                                        @if($canEdit || $isAdmin)
                                        @php
                                        $reviewEditData = [
                                        'appointmentId' => $item->appointment_id,
                                        'doctorId' => $item->doctor_id,
                                        'doctorName' => $item->doctor_name,
                                        'deptName' => $item->department_name,
                                        'workDate' => \Carbon\Carbon::parse($item->work_date)->format('d/m/Y'),
                                        'avatarUrl' => '',
                                        'storeUrl' => route('reviews.store'),
                                        'existing' => [
                                        'reviewId' => $item->review_id,
                                        'rating' => $item->review_rating ?? 0,
                                        'comment' => $item->review_comment ?? '',
                                        'updateUrl' => route('reviews.update', $item->review_id ?? 0),
                                        ],
                                        ];
                                        @endphp
                                        <button type="button" class="btn-edit"
                                            data-review='@json($reviewEditData)'
                                            onclick="openReviewModal(JSON.parse(this.dataset.review))">
                                            ✏️ Sửa
                                        </button>
                                        @endif

                                        {{-- Xoá đánh giá --}}
                                        @if($canEdit || $isAdmin)
                                        <button type="button" class="btn-cancel"
                                            data-destroy-url="{{ route('reviews.destroy', $item->review_id ?? 0) }}"
                                            onclick="openDeleteReviewModal(this.dataset.destroyUrl)">
                                            🗑 Xóa
                                        </button>
                                        @endif

                                        {{-- Trả lời: doctor hoặc admin --}}
                                        @if($isAdmin || $isDoctor)
                                        @php
                                        $replyData = [
                                        'replyUrl' => route('reviews.reply', $item->review_id ?? 0),
                                        'stars' => $item->review_rating ?? 0,
                                        'comment' => $item->review_comment ?? '',
                                        'userName' => auth()->user()->full_name ?? auth()->user()->name ?? '',
                                        'existingReply' => $item->doctor_reply ?? '',
                                        ];
                                        @endphp
                                        <button type="button" class="btn-edit"
                                            style="background:#f0fdf4;color:#16a34a;border-color:#bbf7d0"
                                            data-reply='@json($replyData)'
                                            onclick="openReplyModal(JSON.parse(this.dataset.reply))">
                                            💬 {{ ($item->doctor_reply ?? null) ? 'Sửa phản hồi' : 'Trả lời' }}
                                        </button>
                                        @endif

                                        @else
                                        {{-- Chưa đánh giá --}}
                                        @php
                                        $newReviewData = [
                                        'appointmentId' => $item->appointment_id,
                                        'doctorId' => $item->doctor_id,
                                        'doctorName' => $item->doctor_name,
                                        'deptName' => $item->department_name,
                                        'workDate' => \Carbon\Carbon::parse($item->work_date)->format('d/m/Y'),
                                        'avatarUrl' => '',
                                        'storeUrl' => route('reviews.store'),
                                        'existing' => null,
                                        ];
                                        @endphp
                                        <button type="button" class="btn-edit btn-review"
                                            data-review='@json($newReviewData)'
                                            onclick="openReviewModal(JSON.parse(this.dataset.review))">
                                            ⭐ Đánh giá
                                        </button>
                                        @endif
                                    </div>

                                    @else
                                    <span style="font-size:.75rem;color:var(--gray-400)">—</span>
                                    @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    <p>Bạn chưa có lịch khám nào.</p>
                                    <a href="{{ route('appointments.create') }}" class="btn-book-new" style="margin-top:16px;display:inline-flex;">Đặt lịch ngay</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        © {{ date('Y') }} HospitalC · Nền tảng đặt lịch khám hiện đại ·
        <a href="#">Chính sách bảo mật</a> &nbsp;·&nbsp; <a href="#">Hỗ trợ</a>
    </footer>

    {{-- Modal huỷ lịch --}}
    <div class="modal-overlay" id="cancelModal">
        <div class="modal">
            <div class="modal-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
            </div>
            <h3>Xác nhận hủy lịch</h3>
            <p>Bạn có chắc muốn hủy lịch khám này không? Hành động này không thể hoàn tác.</p>
            <form id="cancelForm" method="POST">
                @csrf
                <textarea name="cancel_reason" placeholder="Nhập lý do hủy (tùy chọn)"></textarea>
                <div class="modal-btns">
                    <button type="button" class="modal-cancel-btn" onclick="closeModal()">Không, giữ lại</button>
                    <button type="submit" class="modal-confirm-btn">Xác nhận hủy</button>
                </div>
            </form>
        </div>
    </div>

    @include('appointments.reviews')

    <script>
        function openModal(button) {
            document.getElementById('cancelForm').action = button.getAttribute('data-action');
            document.getElementById('cancelModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('cancelModal').classList.remove('active');
        }
        document.getElementById('cancelModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>

</html>
