
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đặt Lịch Khám – HospitalBooking</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #f0f4f8;
            --white:    #ffffff;
            --blue-950: #0a1628;
            --blue-900: #0d2248;
            --blue-800: #1a3a6e;
            --blue-700: #1d4ed8;
            --blue-600: #2563eb;
            --blue-500: #3b82f6;
            --blue-400: #60a5fa;
            --blue-200: #bfdbfe;
            --blue-100: #dbeafe;
            --blue-50:  #eff6ff;
            --teal:     #0891b2;
            --teal-500: #06b6d4;
            --teal-50:  #ecfeff;
            --green:    #059669;
            --green-50: #ecfdf5;
            --green-100:#d1fae5;
            --green-600:#059669;
            --red:      #dc2626;
            --red-50:   #fef2f2;
            --yellow:   #d97706;
            --yellow-50:#fffbeb;
            --amber-50: #fffbeb;
            --amber-400:#fbbf24;
            --purple-50:#faf5ff;
            --purple-600:#9333ea;
            --gray-700: #374151;
            --gray-500: #6b7280;
            --gray-400: #9ca3af;
            --gray-300: #d1d5db;
            --gray-200: #e5e7eb;
            --gray-100: #f3f4f6;
            --text:     #111827;
            --muted:    #6b7280;
            --radius:   12px;
            --radius-sm: 8px;
            --shadow:   0 1px 3px rgba(0,0,0,.08);
            --shadow-md: 0 4px 16px rgba(37,99,235,.10);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: 14px;
        }

        /* ── TOPBAR ── */
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
        .topbar-center { display: flex; align-items: center; gap: 4px; }
        .topbar-center a {
            font-size: .82rem; font-weight: 500; color: rgba(255,255,255,.65);
            text-decoration: none; padding: 7px 14px; border-radius: 8px; transition: all .15s;
        }
        .topbar-center a:hover, .topbar-center a.active { color: #fff; background: rgba(255,255,255,.1); }
        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .user-pill {
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
            background: var(--white); border-bottom: 1px solid var(--gray-200);
            padding: 10px 28px;
            display: flex; align-items: center; gap: 6px;
            font-size: .75rem; color: var(--gray-400);
        }
        .breadcrumb-bar a { color: var(--blue-600); text-decoration: none; transition: color .15s; }
        .breadcrumb-bar a:hover { color: var(--blue-800); }

        /* ── LAYOUT ── */
        .page {
            max-width: 1180px; margin: 0 auto;
            padding: 28px 20px 60px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px; align-items: start;
        }

        /* ── PANEL ── */
        .panel {
            background: var(--white); border: 1px solid var(--gray-200);
            border-radius: var(--radius); overflow: hidden;
            box-shadow: var(--shadow-md); margin-bottom: 18px;
        }
        .panel:last-child { margin-bottom: 0; }
        .panel-head {
            padding: 14px 20px; border-bottom: 1px solid var(--gray-200);
            display: flex; align-items: center; gap: 12px;
            background: var(--blue-50);
        }
        .panel-head .icon-wrap {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, var(--blue-600), var(--teal));
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; box-shadow: 0 2px 6px rgba(37,99,235,.25);
        }
        .panel-head h2 {
            font-size: .9rem; font-weight: 700; color: var(--blue-900);
        }
        .panel-body { padding: 22px; }

        /* ── FORM ── */
        .form-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }
        .form-group { margin-bottom: 14px; }
        .form-group:last-child { margin-bottom: 0; }
        .form-label {
            display: block; font-size: .75rem; font-weight: 700;
            color: var(--muted); margin-bottom: 7px;
            text-transform: uppercase; letter-spacing: .04em;
        }
        .form-label .req { color: var(--red); }

        .form-control {
            width: 100%; padding: 10px 14px;
            background: var(--gray-100); border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-family: inherit; font-size: .85rem; color: var(--text);
            outline: none; transition: border-color .15s, background .15s, box-shadow .15s;
        }
        .form-control:focus {
            border-color: var(--blue-400); background: var(--white);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .form-control::placeholder { color: var(--gray-400); }
        .form-control.is-invalid { border-color: var(--red); }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center;
            padding-right: 34px; cursor: pointer;
        }
        select.form-control option { background: var(--white); }
        textarea.form-control { resize: vertical; min-height: 80px; }

        .invalid-msg { font-size: .73rem; color: var(--red); margin-top: 5px; display: block; }

        /* ── ALERT ── */
        .alert {
            padding: 12px 16px; border-radius: var(--radius-sm);
            font-size: .82rem; margin-bottom: 18px;
            display: flex; align-items: flex-start;
            gap: 10px; border: 1px solid;
        }
        .alert-success { background: var(--green-50); border-color: #a7f3d0; color: #065f46; }
        .alert-error   { background: var(--red-50); border-color: #fecaca; color: #991b1b; }

        /* ── SECTION LABEL ── */
        .sec-label {
            font-size: .72rem; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: .05em;
            margin: 18px 0 10px;
            display: flex; align-items: center; gap: 6px;
        }
        .sec-label:first-child { margin-top: 0; }
        .sec-label .req { color: var(--red); font-size: .8rem; }

        /* ========================================================
           FEATURE 1 — GỢI Ý BÁC SĨ
        ======================================================== */
        .suggest-section {
            display: none; /* ẩn cho đến khi có data */
            margin-bottom: 16px;
        }
        .suggest-header {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 10px;
        }
        .suggest-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: linear-gradient(135deg, #f59e0b, #f97316);
            color: #fff; font-size: .68rem; font-weight: 800;
            padding: 3px 9px; border-radius: 100px;
            text-transform: uppercase; letter-spacing: .04em;
        }
        .suggest-title {
            font-size: .78rem; font-weight: 700; color: var(--blue-900);
        }
        .suggest-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;
        }
        @media (max-width: 620px) { .suggest-grid { grid-template-columns: 1fr; } }

        .suggest-card {
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            padding: 14px 12px;
            cursor: pointer;
            transition: all .18s;
            position: relative;
            text-align: center;
        }
        .suggest-card:hover {
            border-color: var(--blue-400);
            box-shadow: 0 4px 14px rgba(37,99,235,.14);
            transform: translateY(-1px);
        }
        .suggest-card.selected-sug {
            border-color: var(--blue-600);
            background: var(--blue-50);
            box-shadow: 0 4px 16px rgba(37,99,235,.2);
        }
        .sug-rank {
            position: absolute; top: 8px; left: 8px;
            width: 18px; height: 18px; border-radius: 50%;
            font-size: .6rem; font-weight: 800; color: #fff;
            display: flex; align-items: center; justify-content: center;
        }
        .sug-rank.rank-1 { background: #f59e0b; }
        .sug-rank.rank-2 { background: #9ca3af; }
        .sug-rank.rank-3 { background: #cd7c5b; }

        .sug-avatar {
            width: 44px; height: 44px; border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-500), var(--teal-500));
            display: flex; align-items: center; justify-content: center;
            font-size: .9rem; font-weight: 800; color: #fff;
            margin: 0 auto 8px; overflow: hidden;
            border: 2px solid var(--blue-100);
        }
        .sug-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .sug-name { font-size: .82rem; font-weight: 800; color: var(--blue-900); }
        .sug-stars { color: #f59e0b; font-size: .76rem; margin: 3px 0; }
        .sug-meta {
            display: flex; justify-content: center; gap: 8px;
            margin-top: 6px; flex-wrap: wrap;
        }
        .sug-tag {
            font-size: .64rem; font-weight: 700; padding: 2px 7px; border-radius: 100px;
        }
        .sug-tag.slot  { background: var(--green-100); color: var(--green-600); }
        .sug-tag.exp   { background: var(--blue-100);  color: var(--blue-700); }
        .sug-tag.price { background: var(--purple-50); color: var(--purple-600); }

        .sug-cta {
            display: none; margin-top: 8px;
            font-size: .7rem; font-weight: 700; color: var(--blue-600);
        }
        .suggest-card.selected-sug .sug-cta { display: block; }
        .suggest-card.selected-sug .sug-stars { color: var(--blue-600); }

        .suggest-loading {
            display: flex; align-items: center; gap: 8px;
            font-size: .8rem; color: var(--muted); padding: 8px 0;
        }

        /* ========================================================
           FEATURE 2 — DYNAMIC TIME SLOTS
        ======================================================== */
        .slot-wrap { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
        .slot-btn {
            padding: 9px 14px;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
            font-family: inherit; font-size: .8rem; font-weight: 700;
            cursor: pointer; background: var(--gray-100); color: var(--gray-700);
            transition: all .15s;
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            min-width: 72px;
        }
        .slot-btn .slot-time-end {
            font-size: .64rem; font-weight: 400; color: var(--gray-400);
        }
        .slot-btn .slot-dur {
            font-size: .6rem; font-weight: 400; opacity: .65;
        }
        .slot-btn:hover:not(:disabled):not(.booked) {
            border-color: var(--blue-400); color: var(--blue-600);
            background: var(--blue-50); box-shadow: 0 0 0 3px rgba(37,99,235,.08);
        }
        .slot-btn.selected {
            background: var(--blue-600); border-color: var(--blue-600); color: #fff;
            box-shadow: 0 4px 12px rgba(37,99,235,.3);
        }
        .slot-btn.selected .slot-time-end { color: rgba(255,255,255,.7); }
        .slot-btn.booked {
            background: var(--red-50); border-color: #fecaca; color: #fca5a5;
            cursor: not-allowed;
        }
        .slot-btn.booked .slot-time-end { color: #fca5a5; }

        /* Session separator */
        .slot-session {
            width: 100%; margin: 6px 0 2px;
            font-size: .67rem; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: .05em;
            display: flex; align-items: center; gap: 6px;
        }
        .slot-session::after {
            content: ''; flex: 1; height: 1px; background: var(--gray-200);
        }

        .slot-legend { display: flex; gap: 16px; margin-top: 10px; flex-wrap: wrap; }
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: .72rem; color: var(--muted); }
        .legend-dot { width: 10px; height: 10px; border-radius: 3px; border: 1px solid; }
        .legend-dot.avail   { background: var(--gray-100); border-color: var(--gray-300); }
        .legend-dot.full-dot{ background: var(--red-50); border-color: #fecaca; }
        .legend-dot.sel-dot { background: var(--blue-600); border-color: var(--blue-600); }

        .slot-placeholder { font-size: .82rem; color: var(--gray-400); font-style: italic; padding: 4px 0; }
        .slot-loading { display: flex; align-items: center; gap: 8px; font-size: .8rem; color: var(--muted); }
        .mini-spin {
            width: 14px; height: 14px;
            border: 2px solid var(--gray-200); border-top-color: var(--blue-500);
            border-radius: 50%; animation: spin .6s linear infinite;
        }
        .slot-error-hint { display: none; font-size: .73rem; color: var(--red); margin-top: 8px; }
        .slot-error-hint.show { display: block; }

        /* slot stats bar */
        .slot-stats {
            display: none; margin-bottom: 10px;
            font-size: .74rem; color: var(--muted);
            display: flex; gap: 14px; flex-wrap: wrap; align-items: center;
        }
        .slot-stat-item { display: flex; align-items: center; gap: 5px; }
        .slot-stat-dot { width: 8px; height: 8px; border-radius: 2px; }

        /* ── SIDEBAR ── */
        .sidebar { display: flex; flex-direction: column; gap: 18px; }

        /* Doctor card */
        .doctor-card {
            background: var(--white); border: 1px solid var(--gray-200);
            border-radius: var(--radius); overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        .doctor-card .card-head {
            padding: 13px 18px; border-bottom: 1px solid var(--gray-200);
            background: var(--blue-50);
            font-size: .75rem; font-weight: 700; color: var(--blue-700);
            text-transform: uppercase; letter-spacing: .04em;
        }
        .doctor-body { padding: 20px; text-align: center; }

        .doc-avatar {
            width: 68px; height: 68px; border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-500), var(--teal-500));
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; font-weight: 800; color: #fff;
            margin: 0 auto 12px; overflow: hidden;
            border: 3px solid var(--blue-100);
            box-shadow: 0 4px 12px rgba(37,99,235,.2);
        }
        .doc-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .doc-name { font-size: .95rem; font-weight: 800; color: var(--blue-900); }
        .doc-bio  { font-size: .76rem; color: var(--muted); margin-top: 4px; line-height: 1.5; }
        .doc-stars { color: #f59e0b; font-size: .84rem; margin-top: 6px; }
        .doc-stars span { color: var(--gray-400); font-size: .72rem; }
        .doc-meta { margin-top: 14px; border-top: 1px solid var(--gray-200); padding-top: 12px; }
        .doc-meta-row {
            display: flex; justify-content: space-between;
            padding: 5px 0; font-size: .8rem;
        }
        .doc-meta-row .k { color: var(--muted); }
        .doc-meta-row .v { font-weight: 700; color: var(--text); }
        .doc-meta-row .v.price { color: var(--blue-600); }

        /* Summary card */
        .summary-card {
            background: var(--white); border: 1px solid var(--gray-200);
            border-radius: var(--radius); overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        .summary-card .card-head {
            padding: 13px 18px; border-bottom: 1px solid var(--gray-200);
            background: var(--blue-50);
            font-size: .75rem; font-weight: 700; color: var(--blue-700);
            text-transform: uppercase; letter-spacing: .04em;
        }
        .sum-body { padding: 14px 18px; }
        .sum-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 8px 0; border-bottom: 1px solid var(--gray-100);
            font-size: .82rem;
        }
        .sum-row:last-child { border-bottom: none; }
        .sum-row .k { color: var(--muted); }
        .sum-row .v { font-weight: 700; color: var(--text); }
        .sum-row .v.empty { color: var(--gray-300); font-weight: 400; }
        .sum-row .v.hi { color: var(--blue-600); }

        .status-pill {
            display: inline-flex; align-items: center; gap: 5px;
            background: var(--yellow-50); border: 1px solid #fde68a;
            color: #92400e; border-radius: 100px;
            padding: 3px 10px; font-size: .7rem; font-weight: 700;
        }
        .status-dot { width: 5px; height: 5px; background: var(--yellow); border-radius: 50%; }

        /* ── BUTTONS ── */
        .btn-row {
            display: flex; justify-content: flex-end; gap: 10px;
            margin-top: 22px; padding-top: 18px;
            border-top: 1px solid var(--gray-200);
        }
        .btn {
            padding: 10px 22px; border-radius: var(--radius-sm);
            font-family: inherit; font-size: .85rem; font-weight: 700;
            cursor: pointer; transition: all .15s;
            display: inline-flex; align-items: center; gap: 7px;
            border: none; text-decoration: none;
        }
        .btn-secondary {
            background: var(--gray-100); border: 1px solid var(--gray-200); color: var(--gray-700);
        }
        .btn-secondary:hover { background: var(--gray-200); color: var(--text); }
        .btn-primary {
            background: linear-gradient(135deg, var(--blue-600), var(--teal));
            color: #fff; box-shadow: 0 4px 14px rgba(37,99,235,.3);
        }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,.35); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

        .spinner {
            display: none; width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
            border-radius: 50%; animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── FOOTER ── */
        .footer {
            background: var(--blue-950); color: rgba(255,255,255,.45);
            text-align: center; font-size: .75rem; padding: 22px 20px;
            border-top: 1px solid rgba(255,255,255,.06);
        }
        .footer a { color: var(--blue-400); text-decoration: none; }

        /* ── RESPONSIVE ── */
        @media (max-width: 860px) {
            .page { grid-template-columns: 1fr; }
            .sidebar { order: -1; flex-direction: row; flex-wrap: wrap; }
            .doctor-card, .summary-card { flex: 1; min-width: 260px; }
            .topbar-center { display: none; }
        }
        @media (max-width: 520px) {
            .form-row { grid-template-columns: 1fr; }
            .sidebar { flex-direction: column; }
            .suggest-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>

<body>

    {{-- ── TOPBAR ── --}}
    <nav class="topbar">
        <a href="{{ route('home') }}" class="topbar-brand">
            <div class="icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
            </div>
            <div>
                HospitalBooking
                <span class="brand-sub">Hệ thống đặt lịch khám</span>
            </div>
        </a>

        <div class="topbar-center">
            <a href="{{ route('home') }}">Trang chủ</a>
            <a href="{{ route('appointments.index') }}">Lịch hẹn</a>
            <a href="{{ route('appointments.create') }}" class="active">Đặt lịch mới</a>
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
        <span>›</span>
        <a href="{{ route('appointments.index') }}">Lịch hẹn</a>
        <span>›</span>
        <span style="color:var(--gray-600)">Đặt lịch khám</span>
    </div>

    {{-- ── PAGE ── --}}
    <div class="page">

        {{-- ── MAIN FORM ── --}}
        <div class="main-col">

            {{-- Alerts --}}
            @if(session('success'))
            <div class="alert alert-success">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 15 4 10" />
                </svg>
                {{ session('success') }}
            </div>
            @endif

            @if($errors->has('msg'))
            <div class="alert alert-error">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                {{ $errors->first('msg') }}
            </div>
            @endif

            <form id="booking-form" action="{{ route('appointments.store') }}" method="POST">
                @csrf

                {{-- Hidden fields — filled by JS --}}
                <input type="hidden" name="schedule_id" id="schedule_id">
                <input type="hidden" name="appointment_time" id="appointment_time">

                {{-- ══════════════════════════════════════════
                     BƯỚC 1: Chọn khoa & bác sĩ
                ══════════════════════════════════════════ --}}
                <div class="panel">
                    <div class="panel-head">
                        <div class="icon-wrap">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        <h2>Bước 1 — Chọn Khoa &amp; Bác Sĩ</h2>
                    </div>
                    <div class="panel-body">

                        {{-- ── FEATURE 1: GỢI Ý BÁC SĨ ── --}}
                        <div id="suggest-section" class="suggest-section">
                            <div class="suggest-header">
                                <span class="suggest-badge">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    Gợi ý
                                </span>
                                <span class="suggest-title">Bác sĩ phù hợp nhất hôm nay</span>
                            </div>

                            <div id="suggest-grid" class="suggest-grid">
                                {{-- rendered by JS --}}
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Chuyên Khoa <span class="req">*</span></label>
                                <select class="form-control" id="dept" onchange="onDeptChange()">
                                    <option value="">-- Chọn khoa --</option>
                                    @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Bác Sĩ <span class="req">*</span></label>
                                <select class="form-control" id="doctor" onchange="onDoctorChange()" disabled>
                                    <option value="">-- Chọn bác sĩ --</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Dịch Vụ</label>
                                <select name="service_id" class="form-control">
                                    <option value="">-- Không chọn --</option>
                                    @foreach($services as $svc)
                                    <option value="{{ $svc->service_id }}"
                                        {{ old('service_id') == $svc->service_id ? 'selected' : '' }}>
                                        {{ $svc->service_name }}
                                        @if($svc->price) – {{ number_format($svc->price, 0, ',', '.') }}₫ @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hình Thức Khám</label>
                                <select name="visit_type" class="form-control">
                                    <option value="Khám trực tiếp" selected>Khám trực tiếp</option>
                                    <option value="Khám online">Khám online</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ══════════════════════════════════════════
                     BƯỚC 2: Ngày & DYNAMIC TIME SLOTS
                ══════════════════════════════════════════ --}}
                <div class="panel">
                    <div class="panel-head">
                        <div class="icon-wrap">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                        </div>
                        <h2>Bước 2 — Chọn Ngày &amp; Khung Giờ</h2>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="form-label">Ngày Khám <span class="req">*</span></label>
                            <input type="date" id="work_date" name="work_date"
                                class="form-control @error('work_date') is-invalid @enderror"
                                min="{{ date('Y-m-d') }}"
                                value="{{ old('work_date', date('Y-m-d')) }}"
                                oninput="onDateChange()" style="max-width:220px">
                            @error('work_date')<span class="invalid-msg">{{ $message }}</span>@enderror
                        </div>

                        <div class="sec-label">
                            Khung Giờ <span class="req">*</span>
                        </div>

                        {{-- ── FEATURE 2: slot stats bar ── --}}
                        <div id="slot-stats" style="display:none; margin-bottom:10px; font-size:.74rem; color:var(--muted); display:none; gap:14px; flex-wrap:wrap; align-items:center">
                            <span>
                                <strong id="stat-total" style="color:var(--text)">0</strong> khung giờ
                            </span>
                            <span style="display:flex;align-items:center;gap:4px">
                                <span style="width:8px;height:8px;background:var(--green-100);border-radius:2px;display:inline-block"></span>
                                <strong id="stat-avail" style="color:var(--green-600)">0</strong> trống
                            </span>
                            <span style="display:flex;align-items:center;gap:4px">
                                <span style="width:8px;height:8px;background:var(--red-50);border-radius:2px;border:1px solid #fecaca;display:inline-block"></span>
                                <strong id="stat-booked" style="color:var(--red)">0</strong> đã đặt
                            </span>
                        </div>

                        <div class="slot-wrap" id="slot-wrap">
                            <span class="slot-placeholder">Vui lòng chọn bác sĩ và ngày để xem khung giờ</span>
                        </div>

                        <div class="slot-legend" id="slot-legend" style="display:none">
                            <div class="legend-item"><div class="legend-dot avail"></div>Còn trống</div>
                            <div class="legend-item"><div class="legend-dot full-dot"></div>Đã đặt</div>
                            <div class="legend-item"><div class="legend-dot sel-dot"></div>Đang chọn</div>
                        </div>

                        <span class="slot-error-hint" id="slot-error">Vui lòng chọn khung giờ trước khi đặt lịch.</span>

                        <div class="form-group" style="margin-top:18px">
                            <label class="form-label">Ghi Chú / Triệu Chứng</label>
                            <textarea name="note" class="form-control"
                                placeholder="VD: đau ngực, khó thở, tái khám sau điều trị...">{{ old('note') }}</textarea>
                        </div>

                        <div class="btn-row">
                            <a href="{{ route('home') }}" class="btn btn-secondary">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                                Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <span class="spinner" id="spinner"></span>
                                <svg id="submit-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                Xác Nhận Đặt Lịch
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>{{-- /main-col --}}

        {{-- ── SIDEBAR ── --}}
        <div class="sidebar">

            {{-- Doctor profile --}}
            <div class="doctor-card">
                <div class="card-head">Bác Sĩ Được Chọn</div>
                <div class="doctor-body" id="doctor-body">
                    <div style="padding:20px 0;color:var(--gray-400);text-align:center">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="margin:0 auto 8px;display:block;color:var(--blue-200)">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <p style="font-size:.78rem">Chưa chọn bác sĩ</p>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="summary-card">
                <div class="card-head">Tóm Tắt Đặt Lịch</div>
                <div class="sum-body">
                    <div class="sum-row"><span class="k">Khoa</span>    <span class="v empty" id="sum-dept">—</span></div>
                    <div class="sum-row"><span class="k">Bác sĩ</span> <span class="v empty" id="sum-doctor">—</span></div>
                    <div class="sum-row"><span class="k">Dịch vụ</span><span class="v empty" id="sum-svc">—</span></div>
                    <div class="sum-row"><span class="k">Ngày</span>   <span class="v empty hi" id="sum-date">—</span></div>
                    <div class="sum-row"><span class="k">Giờ</span>    <span class="v empty hi" id="sum-time">—</span></div>
                    <div class="sum-row">
                        <span class="k">Trạng thái</span>
                        <span class="status-pill"><span class="status-dot"></span>Chờ xác nhận</span>
                    </div>
                </div>
            </div>

        </div>{{-- /sidebar --}}

    </div>{{-- /page --}}

    {{-- ── FOOTER ── --}}
    <footer class="footer">
        © {{ date('Y') }} HospitalBooking &nbsp;·&nbsp; <a href="#">Chính sách bảo mật</a> &nbsp;·&nbsp; <a href="#">Hỗ trợ</a>
    </footer>

    <script>
    // ════════════════════════════════════════════════════════════
    // BASE DATA (preloaded từ server — 14 ngày đầu)
    // ════════════════════════════════════════════════════════════
    const doctorsByDept = JSON.parse('{!! addslashes(json_encode($doctorsByDept)) !!}');

    // Route URLs (truyền từ Blade → JS an toàn)
    const ROUTE_SUGGEST    = '{{ route("appointments.suggest") }}';
    const ROUTE_TIMESLOTS  = '{{ route("appointments.timeslots") }}';

    // ════════════════════════════════════════════════════════════
    // STATE
    // ════════════════════════════════════════════════════════════
    const state = {
        deptId:      null,
        deptName:    '',
        doctor:      null,
        date:        document.getElementById('work_date').value,
        scheduleId:  null,
        time:        null,
        timeEnd:     null,
    };

    // Cache cho dynamic slots (tránh gọi AJAX lặp lại)
    const slotCache = {};
    // Cache cho suggestion
    const suggestCache = {};

    // ════════════════════════════════════════════════════════════
    // DEPT CHANGE
    // ════════════════════════════════════════════════════════════
    function onDeptChange() {
        const sel = document.getElementById('dept');
        state.deptId   = sel.value;
        state.deptName = sel.options[sel.selectedIndex].text;
        state.doctor   = null;
        clearSlotState();

        // Cập nhật doctor select
        const docSel = document.getElementById('doctor');
        docSel.innerHTML = '<option value="">-- Chọn bác sĩ --</option>';
        docSel.disabled = !state.deptId;

        const list = doctorsByDept[state.deptId] || [];
        list.forEach(d => {
            const o = document.createElement('option');
            o.value       = d.doctor_id;
            o.textContent = `BS. ${d.full_name}`;
            docSel.appendChild(o);
        });

        renderDoctor(null);
        renderDynamicSlots([]);
        updateSummary();

        // ── FEATURE 1: tải gợi ý khi đã có khoa + ngày ──
        if (state.deptId && state.date) {
            loadSuggestions();
        } else {
            hideSuggestions();
        }
    }

    // ════════════════════════════════════════════════════════════
    // DOCTOR CHANGE
    // ════════════════════════════════════════════════════════════
    function onDoctorChange() {
        const val = document.getElementById('doctor').value;
        if (!val) {
            state.doctor = null;
            renderDoctor(null);
            renderDynamicSlots([]);
            updateSummary();
            return;
        }

        const list = doctorsByDept[state.deptId] || [];
        state.doctor = list.find(d => String(d.doctor_id) === String(val)) || null;
        clearSlotState();

        renderDoctor(state.doctor);
        loadDynamicSlots();  // ← FEATURE 2
        updateSummary();

        // Highlight suggestion card tương ứng
        highlightSuggestionCard(val);
    }

    // ════════════════════════════════════════════════════════════
    // DATE CHANGE
    // ════════════════════════════════════════════════════════════
    function onDateChange() {
        state.date = document.getElementById('work_date').value;
        clearSlotState();

        if (state.doctor) {
            loadDynamicSlots();  // ← FEATURE 2
        }

        // Tải lại gợi ý khi đổi ngày (có thể bác sĩ khác trống)
        if (state.deptId && state.date) {
            loadSuggestions();
        }

        updateSummary();
    }

    // ════════════════════════════════════════════════════════════
    // FEATURE 1 — GỢI Ý BÁC SĨ TỰ ĐỘNG
    // ════════════════════════════════════════════════════════════
    function loadSuggestions() {
        if (!state.deptId || !state.date) { hideSuggestions(); return; }

        const cacheKey = `${state.deptId}_${state.date}`;
        if (suggestCache[cacheKey] !== undefined) {
            renderSuggestions(suggestCache[cacheKey]);
            return;
        }

        // Hiển thị skeleton loading
        const section = document.getElementById('suggest-section');
        const grid    = document.getElementById('suggest-grid');
        section.style.display = 'block';
        grid.innerHTML = `<div class="suggest-loading" style="grid-column:1/-1">
            <div class="mini-spin"></div> Đang tìm bác sĩ phù hợp...
        </div>`;

        fetch(`${ROUTE_SUGGEST}?department_id=${state.deptId}&work_date=${state.date}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            suggestCache[cacheKey] = data.suggested || [];
            renderSuggestions(suggestCache[cacheKey]);
        })
        .catch(() => {
            hideSuggestions();
        });
    }

    function renderSuggestions(doctors) {
        const section = document.getElementById('suggest-section');
        const grid    = document.getElementById('suggest-grid');

        if (!doctors || doctors.length === 0) {
            hideSuggestions();
            return;
        }

        section.style.display = 'block';

        const rankColors = ['rank-1', 'rank-2', 'rank-3'];
        const rankLabels = ['#1', '#2', '#3'];

        grid.innerHTML = doctors.map((doc, i) => {
            const rating   = parseFloat(doc.avg_rating) || 0;
            const reviews  = parseInt(doc.total_reviews) || 0;
            const avail    = parseInt(doc.available_slots) || 0;
            const exp      = parseInt(doc.experience) || 0;
            const price    = parseInt(doc.price) || 0;
            const initials = doc.full_name.split(' ').slice(-2).map(w => w[0]).join('').toUpperCase();
            const avatar   = doc.avatar_url
                ? `<img src="${doc.avatar_url}" alt="${doc.full_name}">`
                : initials;
            const stars    = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));

            return `<div class="suggest-card" id="sug-card-${doc.doctor_id}"
                        onclick="selectSuggestedDoctor(${doc.doctor_id})">
                <div class="sug-rank ${rankColors[i]}">${rankLabels[i]}</div>
                <div class="sug-avatar">${avatar}</div>
                <div class="sug-name">BS. ${doc.full_name}</div>
                <div class="sug-stars">${stars}
                    <span style="color:var(--gray-400);font-size:.64rem"> (${reviews})</span>
                </div>
                <div class="sug-meta">
                    <span class="sug-tag slot">${avail} chỗ trống</span>
                    ${exp ? `<span class="sug-tag exp">${exp} năm</span>` : ''}
                    ${price ? `<span class="sug-tag price">${(price/1000).toFixed(0)}k₫</span>` : ''}
                </div>
                <div class="sug-cta">✓ Đã chọn bác sĩ này</div>
            </div>`;
        }).join('');
    }

    function hideSuggestions() {
        document.getElementById('suggest-section').style.display = 'none';
    }

    // Click vào suggestion card → tự động chọn bác sĩ
    function selectSuggestedDoctor(doctorId) {
        const docSel = document.getElementById('doctor');
        if (docSel.disabled) return; // chưa chọn khoa

        const val = String(doctorId);
        if (!docSel.querySelector(`option[value="${val}"]`)) return;

        docSel.value = val;
        onDoctorChange(); // kích hoạt flow bình thường
    }

    function highlightSuggestionCard(doctorId) {
        document.querySelectorAll('.suggest-card').forEach(c => c.classList.remove('selected-sug'));
        const card = document.getElementById(`sug-card-${doctorId}`);
        if (card) card.classList.add('selected-sug');
    }

    // ════════════════════════════════════════════════════════════
    // FEATURE 2 — DYNAMIC TIME SLOTS
    // ════════════════════════════════════════════════════════════
    function loadDynamicSlots() {
        if (!state.doctor || !state.date) return;

        const cacheKey = `${state.doctor.doctor_id}_${state.date}`;

        if (slotCache[cacheKey] !== undefined) {
            renderDynamicSlots(slotCache[cacheKey]);
            return;
        }

        // Hiển thị loading
        document.getElementById('slot-wrap').innerHTML =
            '<div class="slot-loading"><div class="mini-spin"></div>Đang tải khung giờ...</div>';
        document.getElementById('slot-legend').style.display = 'none';
        document.getElementById('slot-stats').style.display  = 'none';

        fetch(`${ROUTE_TIMESLOTS}?doctor_id=${state.doctor.doctor_id}&work_date=${state.date}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.day_off) {
                document.getElementById('slot-wrap').innerHTML =
                    '<span class="slot-placeholder">🚫 Bác sĩ nghỉ ngày này. Vui lòng chọn ngày khác.</span>';
                slotCache[cacheKey] = null;
                return;
            }
            slotCache[cacheKey] = data.slots || [];
            renderDynamicSlots(slotCache[cacheKey]);
        })
        .catch(() => {
            document.getElementById('slot-wrap').innerHTML =
                '<span class="slot-placeholder" style="color:var(--red)">Lỗi tải khung giờ. Vui lòng thử lại.</span>';
        });
    }

    // Phân loại buổi theo giờ
    function getSession(timeStr) {
        const h = parseInt(timeStr.split(':')[0]);
        if (h < 12) return 'Buổi sáng';
        if (h < 17) return 'Buổi chiều';
        return 'Buổi tối';
    }

    function renderDynamicSlots(slots) {
        const wrap   = document.getElementById('slot-wrap');
        const legend = document.getElementById('slot-legend');
        const stats  = document.getElementById('slot-stats');
        clearSlotState();

        if (!slots || slots.length === 0) {
            wrap.innerHTML = `<span class="slot-placeholder">${
                state.doctor ? 'Không có lịch khám cho ngày này' : 'Vui lòng chọn bác sĩ và ngày'
            }</span>`;
            legend.style.display = 'none';
            stats.style.display  = 'none';
            return;
        }

        // Thống kê
        const totalSlots  = slots.length;
        const bookedCount = slots.filter(s => s.is_booked).length;
        const availCount  = totalSlots - bookedCount;

        document.getElementById('stat-total').textContent  = totalSlots;
        document.getElementById('stat-avail').textContent  = availCount;
        document.getElementById('stat-booked').textContent = bookedCount;
        stats.style.display = 'flex';

        // Nhóm theo buổi (sáng/chiều/tối)
        const groups = {};
        slots.forEach(s => {
            const session = getSession(s.time);
            if (!groups[session]) groups[session] = [];
            groups[session].push(s);
        });

        const sessionOrder = ['Buổi sáng', 'Buổi chiều', 'Buổi tối'];
        let html = '';

        sessionOrder.forEach(session => {
            if (!groups[session]) return;
            html += `<div class="slot-session">${session}</div>`;
            groups[session].forEach(s => {
                const cls = s.is_booked ? 'booked' : '';
                html += `<button type="button"
                    class="slot-btn ${cls}"
                    ${s.is_booked ? 'disabled' : ''}
                    data-sid="${s.schedule_id}"
                    data-time="${s.time}"
                    data-end="${s.end_time || ''}"
                    onclick="selectDynamicSlot(this)">
                    ${s.time}
                    <span class="slot-time-end">${s.end_time ? '→ ' + s.end_time : ''}</span>
                </button>`;
            });
        });

        wrap.innerHTML = html;
        legend.style.display = 'flex';
        updateSummary();
    }

    // ── Chọn slot ──
    function selectDynamicSlot(el) {
        if (el.disabled || el.classList.contains('booked')) return;

        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');

        state.scheduleId = el.dataset.sid;
        state.time       = el.dataset.time;
        state.timeEnd    = el.dataset.end || '';

        document.getElementById('schedule_id').value      = state.scheduleId;
        document.getElementById('appointment_time').value = state.time;
        document.getElementById('slot-error').classList.remove('show');
        updateSummary();
    }

    function clearSlotState() {
        state.scheduleId = null;
        state.time       = null;
        state.timeEnd    = null;
        document.getElementById('schedule_id').value      = '';
        document.getElementById('appointment_time').value = '';
    }

    // ════════════════════════════════════════════════════════════
    // RENDER DOCTOR (sidebar)
    // ════════════════════════════════════════════════════════════
    function renderDoctor(doc) {
        const body = document.getElementById('doctor-body');
        if (!doc) {
            body.innerHTML = `<div style="padding:20px 0;color:var(--gray-400);text-align:center">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"
                    style="margin:0 auto 8px;display:block;color:var(--blue-200)">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <p style="font-size:.78rem">Chưa chọn bác sĩ</p></div>`;
            return;
        }
        const rating   = parseFloat(doc.avg_rating) || 0;
        const reviews  = parseInt(doc.total_reviews) || 0;
        const stars    = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
        const initials = doc.full_name.split(' ').slice(-2).map(w => w[0]).join('').toUpperCase();
        const avatar   = doc.avatar_url
            ? `<img src="${doc.avatar_url}" alt="${doc.full_name}">`
            : initials;
        const price = parseInt(doc.price) || 0;

        body.innerHTML = `
            <div class="doc-avatar">${avatar}</div>
            <div class="doc-name">Dr. ${doc.full_name}</div>
            <div class="doc-bio">${doc.bio || ''} · ${doc.experience ? doc.experience + ' năm KN' : ''}</div>
            <div class="doc-stars">${stars} <span>(${reviews.toLocaleString('vi-VN')})</span></div>
            <div class="doc-meta">
                <div class="doc-meta-row"><span class="k">Đánh giá</span><span class="v">${rating.toFixed(1)}/5.0</span></div>
                <div class="doc-meta-row"><span class="k">Phí khám</span><span class="v price">${price.toLocaleString('vi-VN')} ₫</span></div>
            </div>`;
    }

    // ════════════════════════════════════════════════════════════
    // UPDATE SUMMARY (sidebar)
    // ════════════════════════════════════════════════════════════
    function updateSummary() {
        const svcSel  = document.querySelector('select[name="service_id"]');
        const svcText = svcSel && svcSel.selectedIndex > 0
            ? svcSel.options[svcSel.selectedIndex].text : '';

        const timeDisplay = state.time
            ? (state.timeEnd ? `${state.time} – ${state.timeEnd}` : state.time)
            : '';

        setSum('sum-dept',   state.deptName);
        setSum('sum-doctor', state.doctor ? `BS. ${state.doctor.full_name}` : '');
        setSum('sum-svc',    svcText);
        setSum('sum-date',   state.date ? state.date.split('-').reverse().join('/') : '');
        setSum('sum-time',   timeDisplay);
    }

    function setSum(id, val) {
        const el = document.getElementById(id);
        if (!el) return;
        if (val) { el.textContent = val; el.classList.remove('empty'); }
        else      { el.textContent = '—'; el.classList.add('empty'); }
    }

    // ════════════════════════════════════════════════════════════
    // FORM SUBMIT
    // ════════════════════════════════════════════════════════════
    document.getElementById('booking-form').addEventListener('submit', function(e) {
        if (!state.scheduleId || !state.time) {
            e.preventDefault();
            document.getElementById('slot-error').classList.add('show');
            document.getElementById('slot-wrap').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        const btn     = document.getElementById('submit-btn');
        const spinner = document.getElementById('spinner');
        const icon    = document.getElementById('submit-icon');
        btn.disabled         = true;
        spinner.style.display = 'block';
        icon.style.display   = 'none';
    });

    // Service change → cập nhật summary
    document.querySelector('select[name="service_id"]')
        ?.addEventListener('change', updateSummary);

    // ── INIT ──
    (function init() {
        updateSummary();
    })();
    </script>
</body>
</html>