<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Khám Của Tôi – HospitalBooking</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f0f4f8;
            --white: #ffffff;
            --blue-950: #0a1628;
            --blue-900: #0d2248;
            --blue-800: #1a3a6e;
            --blue-700: #1d4ed8;
            --blue-600: #2563eb;
            --blue-500: #3b82f6;
            --blue-400: #60a5fa;
            --blue-100: #dbeafe;
            --blue-50:  #eff6ff;
            --teal:     #0891b2;
            --teal-50:  #ecfeff;
            --green:    #059669;
            --green-50: #ecfdf5;
            --red:      #dc2626;
            --red-50:   #fef2f2;
            --yellow:   #d97706;
            --yellow-50:#fffbeb;
            --gray-700: #374151;
            --gray-500: #6b7280;
            --gray-400: #9ca3af;
            --gray-200: #e5e7eb;
            --gray-100: #f3f4f6;
            --text:     #111827;
            --muted:    #6b7280;
            --radius:   12px;
            --radius-sm: 8px;
            --shadow:   0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
            --shadow-md: 0 4px 16px rgba(37,99,235,.10), 0 1px 4px rgba(0,0,0,.06);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: 14px;
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

        /* ── TOPBARR ── */

        .topbar {
            position: sticky; top: 0; z-index: 100;
            background: var(--blue-900);
            box-shadow: 0 2px 8px rgba(10,22,40,.18);
            padding: 0 28px; height: 62px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .topbar-brand {
            display: flex; align-items: center; gap: 11px;
            font-weight: 800; font-size: 1rem; color: #fff;
            text-decoration: none; letter-spacing: -.01em;
        }
        .topbar-brand .icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(59,130,246,.4);
        }
        .topbar-brand .brand-sub {
            font-size: .65rem; font-weight: 400;
            color: rgba(255,255,255,.55); display: block; margin-top: -1px;
            letter-spacing: .03em; text-transform: uppercase;
        }

        .topbar-center {
            display: flex; align-items: center; gap: 4px;
        }
        .topbar-center a {
            font-size: .82rem; font-weight: 500; color: rgba(255,255,255,.65);
            text-decoration: none; padding: 7px 14px; border-radius: 8px;
            transition: all .15s;
        }
        .topbar-center a:hover, .topbar-center a.active {
            color: #fff; background: rgba(255,255,255,.1);
        }

        .topbar-right {
            display: flex; align-items: center; gap: 10px;
        }
        .topbar-right .user-pill {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.08); border-radius: 24px;
            padding: 5px 14px 5px 8px;
        }
        .user-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 700; color: #fff;
        }
        .user-name { font-size: .8rem; font-weight: 600; color: rgba(255,255,255,.85); }

        .btn-logout {
            font-family: inherit; font-size: .78rem; font-weight: 600;
            color: rgba(255,255,255,.6); background: none;
            border: 1px solid rgba(255,255,255,.2); border-radius: 8px;
            padding: 6px 14px; cursor: pointer; transition: all .15s;
        }
        .btn-logout:hover { color: #fff; border-color: rgba(255,255,255,.45); background: rgba(255,255,255,.08); }

        /* ── BREADCRUMB ── */
        .breadcrumb-bar {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 10px 28px;
            display: flex; align-items: center; gap: 6px;
            font-size: .75rem; color: var(--gray-400);
        }
        .breadcrumb-bar a { color: var(--blue-600); text-decoration: none; transition: color .15s; }
        .breadcrumb-bar a:hover { color: var(--blue-800); }
        .breadcrumb-bar .sep { color: var(--gray-300); }

        /* ── PAGE ── */
        .page {
            max-width: 980px; margin: 0 auto;
            padding: 28px 20px 60px;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
        }
        .page-header-left { display: flex; align-items: center; gap: 14px; }
        .page-header-icon {
            width: 46px; height: 46px; border-radius: 12px;
            background: linear-gradient(135deg, var(--blue-600), var(--teal));
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(37,99,235,.25); flex-shrink: 0;
        }
        .page-header h1 { font-size: 1.15rem; font-weight: 800; color: var(--blue-900); }
        .page-header p { font-size: .78rem; color: var(--muted); margin-top: 2px; }

        .btn-book-new {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 20px; border-radius: var(--radius-sm);
            background: linear-gradient(135deg, var(--blue-600), var(--teal));
            color: #fff; font-family: inherit; font-size: .84rem; font-weight: 700;
            text-decoration: none; border: none; cursor: pointer;
            box-shadow: 0 4px 14px rgba(37,99,235,.3);
            transition: all .15s;
        }
        .btn-book-new:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.35); }

        /* ── ALERT ── */
        .alert-success {
            background: var(--green-50); border: 1px solid #bbf7d0;
            border-radius: var(--radius-sm); padding: 12px 16px;
            font-size: .84rem; color: #065f46; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }

        /* ── STATS ROW ── */
        .stats-row {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;
            margin-bottom: 22px;
        }
        .stat-card {
            background: var(--white); border-radius: var(--radius);
            padding: 16px 18px; border: 1px solid var(--gray-200);
            box-shadow: var(--shadow);
        }
        .stat-card .stat-label { font-size: .7rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
        .stat-card .stat-num { font-size: 1.5rem; font-weight: 800; color: var(--blue-900); margin-top: 4px; }
        .stat-card .stat-num.blue { color: var(--blue-600); }
        .stat-card .stat-num.green { color: var(--green); }
        .stat-card .stat-num.yellow { color: var(--yellow); }
        .stat-card .stat-num.red { color: var(--red); }

        /* ── CARD ── */
        .card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        .card-top {
            padding: 14px 20px;
            border-bottom: 1px solid var(--gray-200);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-top-title { font-size: .85rem; font-weight: 700; color: var(--blue-900); }
        .card-top-sub { font-size: .73rem; color: var(--muted); margin-top: 1px; }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--blue-50); border-bottom: 1px solid var(--blue-100); }
        thead th {
            padding: 12px 18px; font-size: .7rem; font-weight: 700;
            color: var(--blue-700); letter-spacing: .06em; text-transform: uppercase;
            text-align: left; white-space: nowrap;
        }
        tbody tr { border-bottom: 1px solid var(--gray-100); transition: background .15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--blue-50); }
        tbody td { padding: 14px 18px; font-size: .84rem; color: var(--gray-700); vertical-align: middle; }

        .doctor-cell { display: flex; align-items: center; gap: 10px; }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-500), var(--teal));
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 700; color: #fff; flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(37,99,235,.2);
        }
        .doctor-name { font-size: .85rem; color: var(--blue-900); font-weight: 600; }
        .doctor-dept { font-size: .72rem; color: var(--muted); margin-top: 1px; }

        .date-cell .date { font-size: .85rem; color: var(--text); font-weight: 600; }
        .date-cell .time {
            font-size: .72rem; color: var(--muted); margin-top: 2px;
            display: inline-flex; align-items: center; gap: 4px;
        }

        /* ── BADGES ── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px;
            font-size: .72rem; font-weight: 600; white-space: nowrap; border: 1px solid;
        }
        .badge-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
        .badge-pending  { background: var(--yellow-50); color: #92400e; border-color: #fde68a; }
        .badge-pending .badge-dot { background: var(--yellow); }
        .badge-confirmed{ background: var(--green-50); color: #065f46; border-color: #a7f3d0; }
        .badge-confirmed .badge-dot { background: var(--green); }
        .badge-cancelled{ background: var(--red-50); color: #991b1b; border-color: #fecaca; }
        .badge-cancelled .badge-dot { background: var(--red); }
        .badge-done     { background: var(--gray-100); color: var(--gray-500); border-color: var(--gray-200); }
        .badge-done .badge-dot { background: var(--gray-400); }

        /* ── ACTIONS ── */
        .actions { display: flex; align-items: center; gap: 8px; }
        .btn-edit {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 6px 12px;
            background: var(--blue-50); border: 1px solid var(--blue-100);
            border-radius: var(--radius-sm); color: var(--blue-600);
            font-size: .76rem; font-weight: 600; font-family: 'Be Vietnam Pro', sans-serif;
            text-decoration: none; cursor: pointer; transition: all .15s;
        }
        .btn-edit:hover { background: var(--blue-100); border-color: var(--blue-400); color: var(--blue-800); }
        .btn-cancel {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 6px 12px;
            background: var(--red-50); border: 1px solid #fecaca;
            border-radius: var(--radius-sm); color: var(--red);
            font-size: .76rem; font-weight: 600; font-family: 'Be Vietnam Pro', sans-serif;
            cursor: pointer; transition: all .15s;
        }
        .btn-cancel:hover { background: #fee2e2; border-color: #fca5a5; }

        .empty-state {
            padding: 52px 20px; text-align: center; color: var(--gray-400);
        }
        .empty-state svg { width: 48px; height: 48px; margin: 0 auto 14px; display: block; color: var(--blue-200); }
        .empty-state p { font-size: .9rem; color: var(--gray-500); }
        .empty-state a { margin-top: 16px; display: inline-block; }

        /* ── MODAL ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(10,22,40,.55); z-index: 200;
            align-items: center; justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--white); border: 1px solid var(--gray-200);
            border-radius: 16px; padding: 2rem; width: 100%; max-width: 380px;
            text-align: center; box-shadow: 0 20px 60px rgba(10,22,40,.25);
            animation: modalIn .2s ease;
        }
        @keyframes modalIn { from { transform: scale(.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-icon {
            width: 56px; height: 56px;
            background: var(--red-50); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem; border: 1px solid #fecaca;
        }
        .modal h3 { font-size: 1.05rem; font-weight: 700; color: var(--blue-900); margin-bottom: 8px; }
        .modal p { font-size: .84rem; color: var(--muted); margin-bottom: 1.5rem; line-height: 1.6; }
        .modal-btns { display: flex; gap: 10px; }
        .modal-cancel-btn {
            flex: 1; padding: 10px;
            background: var(--gray-100); border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm); color: var(--gray-700);
            font-size: .84rem; font-weight: 600; font-family: 'Be Vietnam Pro', sans-serif;
            cursor: pointer; transition: background .15s;
        }
        .modal-cancel-btn:hover { background: var(--gray-200); }
        .modal-confirm-btn {
            flex: 1; padding: 10px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border: none; border-radius: var(--radius-sm);
            color: #fff; font-size: .84rem; font-weight: 700;
            font-family: 'Be Vietnam Pro', sans-serif; cursor: pointer; transition: opacity .15s;
        }
        .modal-confirm-btn:hover { opacity: .88; }

        /* ── FOOTER ── */
        .footer {
            background: var(--blue-950); color: rgba(255,255,255,.45);
            text-align: center; font-size: .75rem; padding: 22px 20px;
            border-top: 1px solid rgba(255,255,255,.06);
        }
        .footer a { color: var(--blue-400); text-decoration: none; }

        @media (max-width: 760px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
            .topbar-center { display: none; }
            thead th:nth-child(2) { display: none; }
            tbody td:nth-child(2) { display: none; }
        }
        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
            .page { padding: 18px 14px 40px; }
        }
    </style>
</head>
<body>

{{-- ── TOPBAR ── --}}
<nav class="topbar">
    <a href="{{ route('home') }}" class="topbar-brand">
        <div class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <div>
            HospitalBooking
            <span class="brand-sub">Hệ thống đặt lịch khám</span>
        </div>
    </a>

    <div class="topbar-center">
        <a href="{{ route('home') }}">Trang chủ</a>
        <a href="{{ route('appointments.index') }}" class="active">Lịch hẹn</a>
        <a href="{{ route('appointments.create') }}">Đặt lịch mới</a>
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

{{-- ── BREADCRUMB ── --}}
<div class="breadcrumb-bar">
    <a href="{{ route('home') }}">Trang chủ</a>
    <span class="sep">›</span>
    <span style="color:var(--gray-600)">Lịch hẹn của tôi</span>
</div>

{{-- ── PAGE ── --}}
<div class="page">

    {{-- Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div>
                <h1>Lịch Khám Của Tôi</h1>
                <p>Quản lý và theo dõi các lịch hẹn khám bệnh</p>
            </div>
        </div>
        <a href="{{ route('appointments.create') }}" class="btn-book-new">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Đặt lịch mới
        </a>
    </div>

    @if(session('success'))
    <div class="alert-success">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    @php
        $total     = $appointments->count();
        $pending   = $appointments->where('status','Chờ xác nhận')->count();
        $confirmed = $appointments->where('status','Đã xác nhận')->count();
        $done      = $appointments->where('status','Hoàn thành')->count();
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
            <div class="stat-num" style="color:var(--gray-500)">{{ $done }}</div>
        </div>
    </div>

    {{-- Table card --}}
    <div class="card">
        <div class="card-top">
            <div>
                <div class="card-top-title">Danh sách lịch hẹn</div>
                <div class="card-top-sub">Hiển thị {{ $total }} lịch hẹn của bạn</div>
            </div>
        </div>
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
                    <td>
                        <div class="doctor-cell">
                            <div class="avatar">
                                {{ strtoupper(substr($item->doctor_name, -2)) }}
                            </div>
                            <div>
                                <div class="doctor-name">BS. {{ $item->doctor_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $item->service_name }}</td>
                    <td>
                        <div class="date-cell">
                            <div class="date">{{ $item->work_date }}</div>
                            <div class="time">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $item->start_time }}
                            </div>
                        </div>
                    </td>
                    <td>
                        @php
                            $statusMap = [
                                'Chờ xác nhận' => 'badge-pending',
                                'Đã xác nhận'  => 'badge-confirmed',
                                'Đã hủy'       => 'badge-cancelled',
                                'Hoàn thành'   => 'badge-done',
                            ];
                            $badgeClass = $statusMap[$item->status] ?? 'badge-pending';
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            <span class="badge-dot"></span>
                            {{ $item->status }}
                        </span>
                    </td>
                    <td>
                        @if($item->status != 'Đã hủy' && $item->status != 'Hoàn thành')
                            <div class="actions">
                                <a href="{{ route('appointments.edit', $item->appointment_id) }}" class="btn-edit">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    Dời lịch
                                </a>
                                <button type="button" onclick="openModal(this)" data-action="{{ route('appointments.cancel', $item->appointment_id) }}" class="btn-cancel">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                    Hủy
                                </button>
                            </div>
                        @else
                            <span style="font-size:.78rem;color:var(--gray-400)">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <p>Bạn chưa có lịch khám nào.</p>
                            <a href="{{ route('appointments.create') }}" class="btn-book-new" style="margin-top:14px">Đặt lịch ngay</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── FOOTER ── --}}
<footer class="footer">
    © {{ date('Y') }} HospitalBooking &nbsp;·&nbsp; <a href="#">Chính sách bảo mật</a> &nbsp;·&nbsp; <a href="#">Hỗ trợ</a>
</footer>

{{-- Modal xác nhận hủy --}}
<div class="modal-overlay" id="cancelModal">
    <div class="modal">
        <div class="modal-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h3>Xác nhận hủy lịch</h3>
        <p>Bạn có chắc muốn hủy lịch khám này không? Hành động này không thể hoàn tác.</p>
        <form id="cancelForm" method="POST" style="width:100%;text-align:left">
            @csrf
            <div style="margin-bottom:1rem">
                <label style="font-size:.75rem;color:var(--muted);font-weight:600;display:block;margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.04em">Lý do hủy (tùy chọn)</label>
                <textarea name="cancel_reason" style="width:100%;padding:10px 12px;background:var(--gray-100);border:1px solid var(--gray-200);border-radius:var(--radius-sm);font-family:'Be Vietnam Pro',sans-serif;font-size:.84rem;color:var(--text);outline:none;resize:vertical;min-height:64px;transition:border-color .15s" placeholder="Nhập lý do hủy (tuỳ chọn)" onfocus="this.style.borderColor='var(--blue-400)'" onblur="this.style.borderColor='var(--gray-200)'"></textarea>
            </div>
            <div class="modal-btns">
                <button type="button" class="modal-cancel-btn" onclick="closeModal()">Không, giữ lại</button>
                <button type="submit" class="modal-confirm-btn">Xác nhận hủy</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(button) {
        const action = button.getAttribute('data-action');
        document.getElementById('cancelForm').action = action;
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