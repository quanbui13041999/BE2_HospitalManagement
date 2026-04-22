{{-- resources/views/booking/create.blade.php --}}
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
            --bg: #060d18;
            --bg2: #0b1628;
            --surface: rgba(255, 255, 255, .04);
            --border: rgba(255, 255, 255, .09);
            --border2: rgba(255, 255, 255, .15);
            --cyan: #38bdf8;
            --teal: #2dd4bf;
            --green: #34d399;
            --red: #f87171;
            --orange: #fb923c;
            --yellow: #fbbf24;
            --text: #f1f5f9;
            --muted: #94a3b8;
            --dim: #475569;
            --radius: 14px;
            --radius-sm: 9px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: 14px;
        }

        /* ── TOPBAR ── */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(6, 13, 24, .85);
            backdrop-filter: blur(16px);
            border-bottom: .5px solid var(--border);
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: .95rem;
            color: var(--text);
            text-decoration: none;
        }

        .topbar-brand .icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--cyan), var(--green));
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .topbar-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-nav a,
        .topbar-nav button {
            font-family: inherit;
            font-size: .82rem;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            background: none;
            transition: color .15s, background .15s;
        }

        .topbar-nav a:hover,
        .topbar-nav button:hover {
            color: var(--text);
            background: var(--surface);
        }

        .topbar-nav .btn-logout {
            color: var(--red);
            border: .5px solid rgba(248, 113, 113, .2);
        }

        .topbar-nav .btn-logout:hover {
            background: rgba(248, 113, 113, .08);
        }

        /* ── LAYOUT ── */
        .page {
            max-width: 1160px;
            margin: 0 auto;
            padding: 28px 20px 60px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
            align-items: start;
        }

        /* ── PANEL ── */
        .panel {
            background: var(--surface);
            border: .5px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: 18px;
        }

        .panel:last-child {
            margin-bottom: 0;
        }

        .panel-head {
            padding: 14px 20px;
            border-bottom: .5px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-head .icon-wrap {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, var(--cyan), var(--teal));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .panel-head h2 {
            font-size: .88rem;
            font-weight: 700;
            color: var(--text);
        }

        .panel-body {
            padding: 20px;
        }

        /* ── FORM ── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            font-size: .75rem;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .form-label .req {
            color: var(--red);
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            background: rgba(255, 255, 255, .05);
            border: .5px solid var(--border2);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: .85rem;
            color: var(--text);
            outline: none;
            transition: border-color .15s, background .15s;
        }

        .form-control:focus {
            border-color: rgba(56, 189, 248, .5);
            background: rgba(56, 189, 248, .05);
        }

        .form-control::placeholder {
            color: var(--dim);
        }

        .form-control.is-invalid {
            border-color: rgba(248, 113, 113, .6);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 34px;
            cursor: pointer;
        }

        select.form-control option {
            background: #0f2044;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .invalid-msg {
            font-size: .73rem;
            color: var(--red);
            margin-top: 5px;
            display: block;
        }

        /* ── ALERT ── */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            font-size: .82rem;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: .5px solid;
        }

        .alert-success {
            background: rgba(52, 211, 153, .08);
            border-color: rgba(52, 211, 153, .25);
            color: #6ee7b7;
        }

        .alert-error {
            background: rgba(248, 113, 113, .08);
            border-color: rgba(248, 113, 113, .25);
            color: #fca5a5;
        }

        /* ── SECTION LABEL ── */
        .sec-label {
            font-size: .72rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin: 18px 0 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sec-label:first-child {
            margin-top: 0;
        }

        .sec-label .req {
            color: var(--red);
            font-size: .8rem;
        }

        /* ── SLOTS ── */
        .slot-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 4px;
        }

        .slot-btn {
            padding: 7px 13px;
            border: .5px solid var(--border2);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            background: rgba(255, 255, 255, .04);
            color: var(--muted);
            transition: all .15s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .slot-btn .slot-count {
            font-size: .65rem;
            font-weight: 400;
            opacity: .7;
        }

        .slot-btn:hover:not(:disabled):not(.full) {
            border-color: rgba(56, 189, 248, .5);
            color: var(--cyan);
            background: rgba(56, 189, 248, .07);
        }

        .slot-btn.selected {
            background: linear-gradient(135deg, rgba(56, 189, 248, .2), rgba(45, 212, 191, .15));
            border-color: var(--cyan);
            color: var(--cyan);
        }

        .slot-btn.full {
            background: rgba(248, 113, 113, .05);
            border-color: rgba(248, 113, 113, .2);
            color: rgba(248, 113, 113, .5);
            cursor: not-allowed;
        }

        .slot-legend {
            display: flex;
            gap: 16px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .72rem;
            color: var(--dim);
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 3px;
            border: .5px solid;
        }

        .legend-dot.avail {
            background: rgba(255, 255, 255, .04);
            border-color: var(--border2);
        }

        .legend-dot.full-dot {
            background: rgba(248, 113, 113, .05);
            border-color: rgba(248, 113, 113, .2);
        }

        .legend-dot.sel-dot {
            background: rgba(56, 189, 248, .2);
            border-color: var(--cyan);
        }

        .slot-placeholder {
            font-size: .82rem;
            color: var(--dim);
            font-style: italic;
            padding: 4px 0;
        }

        .slot-loading {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .8rem;
            color: var(--muted);
        }

        .mini-spin {
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, .1);
            border-top-color: var(--cyan);
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        /* ── SLOT ERROR ── */
        .slot-error-hint {
            display: none;
            font-size: .73rem;
            color: var(--red);
            margin-top: 8px;
        }

        .slot-error-hint.show {
            display: block;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        /* Doctor card */
        .doctor-card {
            background: var(--surface);
            border: .5px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .doctor-card .card-head {
            padding: 12px 18px;
            border-bottom: .5px solid var(--border);
            font-size: .78rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .doctor-body {
            padding: 18px;
            text-align: center;
        }

        .doc-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0ea5e9, #2dd4bf);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            margin: 0 auto 12px;
            border: 2px solid rgba(56, 189, 248, .3);
            overflow: hidden;
        }

        .doc-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doc-name {
            font-size: .92rem;
            font-weight: 800;
            color: var(--text);
        }

        .doc-bio {
            font-size: .75rem;
            color: var(--muted);
            margin-top: 4px;
        }

        .doc-stars {
            color: #fbbf24;
            font-size: .82rem;
            margin-top: 6px;
        }

        .doc-stars span {
            color: var(--dim);
            font-size: .7rem;
        }

        .doc-meta {
            margin-top: 14px;
            border-top: .5px solid var(--border);
            padding-top: 12px;
        }

        .doc-meta-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: .78rem;
        }

        .doc-meta-row .k {
            color: var(--dim);
        }

        .doc-meta-row .v {
            font-weight: 700;
            color: var(--text);
        }

        .doc-meta-row .v.price {
            color: var(--cyan);
        }

        /* Summary card */
        .summary-card {
            background: var(--surface);
            border: .5px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .summary-card .card-head {
            padding: 12px 18px;
            border-bottom: .5px solid var(--border);
            font-size: .78rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .sum-body {
            padding: 14px 18px;
        }

        .sum-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: .5px solid rgba(255, 255, 255, .05);
            font-size: .8rem;
        }

        .sum-row:last-child {
            border-bottom: none;
        }

        .sum-row .k {
            color: var(--dim);
        }

        .sum-row .v {
            font-weight: 700;
            color: var(--text);
        }

        .sum-row .v.empty {
            color: rgba(255, 255, 255, .15);
            font-weight: 400;
        }

        .sum-row .v.hi {
            color: var(--cyan);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(251, 191, 36, .08);
            border: .5px solid rgba(251, 191, 36, .3);
            color: #fde68a;
            border-radius: 100px;
            padding: 2px 10px;
            font-size: .7rem;
            font-weight: 700;
        }

        .status-dot {
            width: 5px;
            height: 5px;
            background: var(--yellow);
            border-radius: 50%;
        }

        /* ── BUTTONS ── */
        .btn-row {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
            padding-top: 18px;
            border-top: .5px solid var(--border);
        }

        .btn {
            padding: 10px 22px;
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: .85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: none;
            text-decoration: none;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, .06);
            border: .5px solid var(--border2);
            color: var(--muted);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, .1);
            color: var(--text);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), var(--teal));
            color: #060d18;
            box-shadow: 0 4px 20px rgba(56, 189, 248, .25);
        }

        .btn-primary:hover {
            opacity: .9;
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            display: none;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(6, 13, 24, .3);
            border-top-color: #060d18;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 860px) {
            .page {
                grid-template-columns: 1fr;
            }

            .sidebar {
                order: -1;
                flex-direction: row;
                flex-wrap: wrap;
            }

            .doctor-card,
            .summary-card {
                flex: 1;
                min-width: 260px;
            }
        }

        @media (max-width: 520px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .sidebar {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    {{-- TOPBAR --}}
    <nav class="topbar">
        <a href="{{ route('home') }}" class="topbar-brand">
            <div class="icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                </svg>
            </div>
            HospitalBooking
        </a>
        <div class="topbar-nav">
            <a href="{{ route('appointments.index') }}">Lịch hẹn của tôi</a>
            @auth
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="btn-logout">Đăng xuất</button>
            </form>
            @endauth
        </div>
    </nav>

    {{-- PAGE --}}
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

                {{-- ── BƯỚC 1: Chọn khoa & bác sĩ ── --}}
                <div class="panel">
                    <div class="panel-head">
                        <div class="icon-wrap">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        <h2>Chọn Khoa & Bác Sĩ</h2>
                    </div>
                    <div class="panel-body">
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

                {{-- ── BƯỚC 2: Ngày & Khung giờ ── --}}
                <div class="panel">
                    <div class="panel-head">
                        <div class="icon-wrap">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                        </div>
                        <h2>Chọn Ngày & Khung Giờ</h2>
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

                        <div class="slot-wrap" id="slot-wrap">
                            <span class="slot-placeholder">Vui lòng chọn bác sĩ và ngày để xem khung giờ</span>
                        </div>

                        <div class="slot-legend" id="slot-legend" style="display:none">
                            <div class="legend-item">
                                <div class="legend-dot avail"></div>Còn trống
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot full-dot"></div>Đã đầy
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot sel-dot"></div>Đang chọn
                            </div>
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
                    <div style="padding:20px 0;color:var(--dim);text-align:center">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="margin:0 auto 8px;display:block">
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
                    <div class="sum-row"><span class="k">Khoa</span> <span class="v empty" id="sum-dept">—</span></div>
                    <div class="sum-row"><span class="k">Bác sĩ</span> <span class="v empty" id="sum-doctor">—</span></div>
                    <div class="sum-row"><span class="k">Dịch vụ</span> <span class="v empty" id="sum-svc">—</span></div>
                    <div class="sum-row"><span class="k">Ngày</span> <span class="v empty hi" id="sum-date">—</span></div>
                    <div class="sum-row"><span class="k">Giờ</span> <span class="v empty hi" id="sum-time">—</span></div>
                    <div class="sum-row">
                        <span class="k">Trạng thái</span>
                        <span class="status-pill"><span class="status-dot"></span>Chờ xác nhận</span>
                    </div>
                </div>
            </div>

        </div>{{-- /sidebar --}}

    </div>{{-- /page --}}

    <script>
        // ── DATA ──
        const doctorsByDept = JSON.parse('{!! addslashes(json_encode($doctorsByDept)) !!}');
        let scheduleData = JSON.parse('{!! addslashes(json_encode($scheduleData)) !!}');

        const state = {
            deptId: null,
            deptName: '',
            doctor: null,
            date: document.getElementById('work_date').value,
            scheduleId: null,
            time: null,
        };

        // ── DEPT CHANGE ──
        function onDeptChange() {
            const sel = document.getElementById('dept');
            state.deptId = sel.value;
            state.deptName = sel.options[sel.selectedIndex].text;
            state.doctor = null;
            state.scheduleId = null;
            state.time = null;

            const docSel = document.getElementById('doctor');
            docSel.innerHTML = '<option value="">-- Chọn bác sĩ --</option>';
            docSel.disabled = !state.deptId;

            const list = doctorsByDept[state.deptId] || [];
            list.forEach(d => {
                const o = document.createElement('option');
                o.value = d.doctor_id;
                o.textContent = `BS. ${d.full_name}`;
                docSel.appendChild(o);
            });

            renderDoctor(null);
            renderSlots([]);
            updateSummary();
        }

        // ── DOCTOR CHANGE ──
        function onDoctorChange() {
            const val = document.getElementById('doctor').value;
            if (!val) {
                state.doctor = null;
                renderDoctor(null);
                renderSlots([]);
                updateSummary();
                return;
            }

            const list = doctorsByDept[state.deptId] || [];
            state.doctor = list.find(d => String(d.doctor_id) === String(val)) || null;
            state.scheduleId = null;
            state.time = null;
            renderDoctor(state.doctor);
            loadSlots();
            updateSummary();
        }

        // ── DATE CHANGE ──
        function onDateChange() {
            state.date = document.getElementById('work_date').value;
            state.scheduleId = null;
            state.time = null;
            if (state.doctor) loadSlots();
            updateSummary();
        }

        // ── LOAD SLOTS ──
        function loadSlots() {
            if (!state.doctor || !state.date) return;
            const key = `${state.doctor.doctor_id}_${state.date}`;

            if (scheduleData[key] !== undefined) {
                renderSlots(scheduleData[key]);
                return;
            }

            // AJAX fallback
            document.getElementById('slot-wrap').innerHTML =
                '<div class="slot-loading"><div class="mini-spin"></div>Đang tải khung giờ...</div>';
            document.getElementById('slot-legend').style.display = 'none';

            fetch(`{{ route('appointments.schedules') }}?doctor_id=${state.doctor.doctor_id}&work_date=${state.date}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.day_off) {
                        document.getElementById('slot-wrap').innerHTML =
                            '<span class="slot-placeholder">Bác sĩ nghỉ ngày này. Vui lòng chọn ngày khác.</span>';
                        return;
                    }
                    scheduleData[key] = data.schedules || [];
                    renderSlots(scheduleData[key]);
                })
                .catch(() => {
                    document.getElementById('slot-wrap').innerHTML =
                        '<span class="slot-placeholder" style="color:var(--red)">Lỗi tải khung giờ. Vui lòng thử lại.</span>';
                });
        }

        // ── RENDER SLOTS ──
        function renderSlots(schedules) {
            const wrap = document.getElementById('slot-wrap');
            const legend = document.getElementById('slot-legend');
            clearSlotState();

            if (!schedules || !schedules.length) {
                wrap.innerHTML = `<span class="slot-placeholder">${
            state.doctor ? 'Không có lịch khám cho ngày này' : 'Vui lòng chọn bác sĩ và ngày'
        }</span>`;
                legend.style.display = 'none';
                return;
            }

            wrap.innerHTML = schedules.map(s => {
                const booked = parseInt(s.booked_count) || 0;
                const max = parseInt(s.max_slot) || 1;
                const full = booked >= max;
                const time = fmtTime(s.start_time);
                return `<button type="button" class="slot-btn${full ? ' full' : ''}"
                    ${full ? 'disabled' : ''}
                    data-sid="${s.schedule_id}"
                    data-time="${s.start_time}"
                    onclick="selectSlot(this)">
                    ${time}
                    <span class="slot-count">${booked}/${max}</span>
                </button>`;
            }).join('');

            legend.style.display = 'flex';
            updateSummary();
        }

        function clearSlotState() {
            state.scheduleId = null;
            state.time = null;
            document.getElementById('schedule_id').value = '';
            document.getElementById('appointment_time').value = '';
        }

        // ── SELECT SLOT ──
        function selectSlot(el) {
            if (el.disabled || el.classList.contains('full')) return;
            document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
            el.classList.add('selected');
            state.scheduleId = el.dataset.sid;
            state.time = fmtTime(el.dataset.time);
            document.getElementById('schedule_id').value = state.scheduleId;
            document.getElementById('appointment_time').value = state.time;
            document.getElementById('slot-error').classList.remove('show');
            updateSummary();
        }

        function fmtTime(t) {
            return t ? String(t).substring(0, 5) : '';
        }

        // ── RENDER DOCTOR ──
        function renderDoctor(doc) {
            const body = document.getElementById('doctor-body');
            if (!doc) {
                body.innerHTML = `<div style="padding:20px 0;color:var(--dim);text-align:center">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="margin:0 auto 8px;display:block"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <p style="font-size:.78rem">Chưa chọn bác sĩ</p></div>`;
                return;
            }
            const rating = parseFloat(doc.avg_rating) || 0;
            const reviews = parseInt(doc.total_reviews) || 0;
            const stars = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
            const initials = doc.full_name.split(' ').slice(-2).map(w => w[0]).join('').toUpperCase();
            const avatar = doc.avatar_url ?
                `<img src="${doc.avatar_url}" alt="${doc.full_name}">` :
                initials;
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

        // ── UPDATE SUMMARY ──
        function updateSummary() {
            const svcSel = document.getElementById('service_id') ||
                document.querySelector('select[name="service_id"]');
            const svcText = svcSel && svcSel.selectedIndex > 0 ?
                svcSel.options[svcSel.selectedIndex].text : '';

            setSum('sum-dept', state.deptName);
            setSum('sum-doctor', state.doctor ? `BS. ${state.doctor.full_name}` : '');
            setSum('sum-svc', svcText);
            setSum('sum-date', state.date ? state.date.split('-').reverse().join('/') : '');
            setSum('sum-time', state.time || '');
        }

        function setSum(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            if (val) {
                el.textContent = val;
                el.classList.remove('empty');
            } else {
                el.textContent = '—';
                el.classList.add('empty');
            }
        }

        // ── FORM SUBMIT ──
        document.getElementById('booking-form').addEventListener('submit', function(e) {
            if (!state.scheduleId || !state.time) {
                e.preventDefault();
                const errEl = document.getElementById('slot-error');
                errEl.classList.add('show');
                document.getElementById('slot-wrap').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return;
            }
            const btn = document.getElementById('submit-btn');
            const spinner = document.getElementById('spinner');
            const icon = document.getElementById('submit-icon');
            btn.disabled = true;
            spinner.style.display = 'block';
            icon.style.display = 'none';
        });

        // Service change → update summary
        document.querySelector('select[name="service_id"]')
            ?.addEventListener('change', updateSummary);

        // ── INIT (restore old() after validation fail) ──
        (function init() {
            updateSummary();
        })();
    </script>
</body>

</html>