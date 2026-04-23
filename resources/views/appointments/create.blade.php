<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đặt Lịch Khám – HospitalBooking | Trải nghiệm đặt khám hiện đại</title>
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

        

        /* Giao diện chính: xanh dương tinh tế + trắng tinh khôi */
        :root {
            --primary: #0f52ba;
            --primary-dark: #0a3d8f;
            --primary-soft: #eef4ff;
            --primary-glow: rgba(15, 82, 186, 0.08);
            --accent-teal: #1e8f9b;
            --accent-teal-light: #cff4f0;
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

        /* Typography */
        h1, h2, h3 {
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        /* Topbar – sang trọng, hiện đại */
        .topbar {
            background: var(--white);
            backdrop-filter: blur(0px);
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
            letter-spacing: 0.2px;
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

        /* Breadcrumb thanh lịch */
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
            max-width: 1320px;
            margin: 24px auto 48px;
            padding: 0 28px;
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 32px;
        }

        /* Panel cards đẹp */
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
            background: var(--primary-soft);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .panel-head h2 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .panel-body {
            padding: 24px 28px;
        }

        /* Form elements */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--gray-500);
            letter-spacing: 0.03em;
            margin-bottom: 8px;
            display: block;
        }

        .req {
            color: #e5484d;
        }

        .form-control, select.form-control, textarea.form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-input);
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            transition: all 0.2s;
            outline: none;
        }

        .form-control:focus, select:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(15, 82, 186, 0.08);
        }

        /* Slot buttons hiện đại */
        .slot-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 12px 0 16px;
        }

        .slot-btn {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 60px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            min-width: 88px;
            color: var(--gray-700);
        }

        .slot-count {
            font-size: 0.65rem;
            font-weight: 500;
            color: var(--gray-500);
        }

        .slot-btn:hover:not(:disabled):not(.full) {
            background: var(--primary-soft);
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(15, 82, 186, 0.15);
        }

        .slot-btn.selected {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 8px 18px rgba(15, 82, 186, 0.3);
        }

        .slot-btn.selected .slot-count {
            color: rgba(255, 255, 255, 0.8);
        }

        .slot-btn.full {
            background: #fef2f2;
            border-color: #ffc9c9;
            color: #e5484d;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .slot-legend {
            display: flex;
            gap: 20px;
            margin: 12px 0 4px;
        }

        .legend-item {
            font-size: 0.7rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 20px;
            background: var(--gray-200);
            border: 1px solid var(--gray-300);
        }

        .legend-dot.full-dot { background: #fef2f2; border-color: #ffb4b4; }
        .legend-dot.sel-dot { background: var(--primary); border: none; }

        /* Sidebar card tinh tế */
        .doctor-card, .summary-card {
            background: var(--white);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .card-head {
            padding: 16px 24px;
            background: var(--gray-50);
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--primary);
            border-bottom: 1px solid var(--gray-200);
        }

        .doctor-body {
            padding: 24px;
            text-align: center;
        }

        .doc-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(145deg, var(--primary), #448af2);
            border-radius: 50%;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.6rem;
            color: white;
            box-shadow: 0 12px 18px -8px rgba(15, 82, 186, 0.3);
        }

        .doc-name {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--gray-800);
        }

        .doc-bio {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 6px;
        }

        .doc-stars {
            color: #f5b042;
            margin: 8px 0;
            font-size: 0.8rem;
        }

        .doc-meta-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.8rem;
            border-bottom: 1px dashed var(--gray-100);
        }

        .sum-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .status-pill {
            background: #fff6e0;
            border-radius: 100px;
            padding: 4px 12px;
            font-size: 0.7rem;
            font-weight: 700;
            color: #c47b2e;
        }

        .btn-row {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 32px;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 48px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
            background: linear-gradient(105deg, #0f52ba, #2a6fd8);
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

        @media (max-width: 860px) {
            .page {
                grid-template-columns: 1fr;
            }
            .topbar-center {
                display: none;
            }
        }
    </style>
</head>

<body>

    {{-- TOPBAR LUXURY --}}
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
            <a href="{{ route('appointments.create') }}" class="active">✨ Đặt lịch mới</a>
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
        <a href="{{ route('appointments.index') }}">Lịch hẹn</a> <span style="margin:0 6px">/</span>
        <span style="color:var(--gray-600); font-weight:500">Đặt lịch khám</span>
    </div>

    <div class="page">

        {{-- Cột chính --}}
        <div class="main-col">
            @if(session('success'))
            <div class="alert" style="background:#e8f3ef; border-left:4px solid #0f52ba; padding:14px 20px; border-radius:18px; margin-bottom:24px;">
                ✔️ {{ session('success') }}
            </div>
            @endif
            @if($errors->has('msg'))
            <div class="alert" style="background:#fee9e9; border-left:4px solid #e5484d; padding:14px 20px; border-radius:18px; margin-bottom:24px;">
                ⚠️ {{ $errors->first('msg') }}
            </div>
            @endif

            <form id="booking-form" action="{{ route('appointments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="schedule_id" id="schedule_id">
                <input type="hidden" name="appointment_time" id="appointment_time">

                {{-- Bước 1: Khoa & bác sĩ --}}
                <div class="panel">
                    <div class="panel-head">
                        <div class="icon-wrap">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </div>
                        <h2>Bước 1 — Chuyên khoa & Bác sĩ</h2>
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
                                <select name="service_id" class="form-control" id="service_id_select">
                                    <option value="">-- Không chọn --</option>
                                    @foreach($services as $svc)
                                    <option value="{{ $svc->service_id }}" {{ old('service_id') == $svc->service_id ? 'selected' : '' }}>
                                        {{ $svc->service_name }} @if($svc->price) – {{ number_format($svc->price, 0, ',', '.') }}₫ @endif
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

                {{-- Bước 2: Ngày giờ --}}
                <div class="panel">
                    <div class="panel-head">
                        <div class="icon-wrap">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                        </div>
                        <h2>Bước 2 — Chọn ngày & khung giờ</h2>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="form-label">Ngày Khám <span class="req">*</span></label>
                            <input type="date" id="work_date" name="work_date"
                                class="form-control @error('work_date') is-invalid @enderror"
                                min="{{ date('Y-m-d') }}" value="{{ old('work_date', date('Y-m-d')) }}"
                                oninput="onDateChange()" style="max-width:260px">
                            @error('work_date')<span class="invalid-msg" style="color:#e5484d;font-size:0.7rem">{{ $message }}</span>@enderror
                        </div>

                        <div class="sec-label" style="font-size:0.7rem; font-weight:700; margin: 16px 0 8px; color: var(--gray-600);">
                            Khung Giờ <span class="req">*</span>
                        </div>
                        <div class="slot-wrap" id="slot-wrap">
                            <span class="slot-placeholder" style="color:var(--gray-400);">Vui lòng chọn bác sĩ và ngày để xem khung giờ</span>
                        </div>
                        <div class="slot-legend" id="slot-legend" style="display:none"></div>
                        <span class="slot-error-hint" id="slot-error" style="color:#e5484d; font-size:0.7rem; display:block; margin-top:12px;"></span>

                        <div class="form-group" style="margin-top:20px">
                            <label class="form-label">Ghi Chú / Triệu Chứng</label>
                            <textarea name="note" class="form-control" placeholder="VD: đau ngực, khó thở, tái khám...">{{ old('note') }}</textarea>
                        </div>

                        <div class="btn-row">
                            <a href="{{ route('home') }}" class="btn btn-secondary">← Quay lại</a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <span class="spinner" id="spinner" style="display:none; width:14px;height:14px; border:2px solid white; border-top-color:transparent; border-radius:50%; animation:spin .6s linear;"></span>
                                <svg id="submit-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M5 13l4 4L19 7" />
                                </svg>
                                Xác Nhận Đặt Lịch
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Sidebar tinh tế --}}
        <div class="sidebar">
            <div class="doctor-card">
                <div class="card-head">👨‍⚕️ Bác Sĩ Được Chọn</div>
                <div class="doctor-body" id="doctor-body">
                    <div style="padding:20px 0;color:var(--gray-400);text-align:center">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></svg>
                        <p style="margin-top:8px">Chưa chọn bác sĩ</p>
                    </div>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-head">📋 Tóm Tắt Đặt Lịch</div>
                <div class="sum-body" style="padding:18px 22px;">
                    <div class="sum-row"><span class="k">Khoa</span> <span class="v empty" id="sum-dept" style="font-weight:500">—</span></div>
                    <div class="sum-row"><span class="k">Bác sĩ</span> <span class="v empty" id="sum-doctor">—</span></div>
                    <div class="sum-row"><span class="k">Dịch vụ</span> <span class="v empty" id="sum-svc">—</span></div>
                    <div class="sum-row"><span class="k">Ngày</span> <span class="v hi" id="sum-date" style="color:var(--primary);font-weight:700">—</span></div>
                    <div class="sum-row"><span class="k">Giờ</span> <span class="v hi" id="sum-time" style="color:var(--primary);font-weight:700">—</span></div>
                    <div class="sum-row"><span class="k">Trạng thái</span><span class="status-pill">⏳ Chờ xác nhận</span></div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        © {{ date('Y') }} HospitalBooking · Nền tảng đặt lịch khám hiện đại · <a href="#" style="color:var(--primary); text-decoration:none">Chính sách bảo mật</a>
    </footer>

    <script>
        const doctorsByDept = JSON.parse('{!! addslashes(json_encode($doctorsByDept)) !!}');
        let scheduleData = JSON.parse('{!! addslashes(json_encode($scheduleData)) !!}');

        let state = {
            deptId: null, deptName: '', doctor: null, date: document.getElementById('work_date').value,
            scheduleId: null, time: null
        };

        function onDeptChange() {
            let sel = document.getElementById('dept');
            state.deptId = sel.value;
            state.deptName = sel.options[sel.selectedIndex]?.text || '';
            state.doctor = null; state.scheduleId = null; state.time = null;
            let docSel = document.getElementById('doctor');
            docSel.innerHTML = '<option value="">-- Chọn bác sĩ --</option>';
            docSel.disabled = !state.deptId;
            let list = doctorsByDept[state.deptId] || [];
            list.forEach(d => { let o = document.createElement('option'); o.value = d.doctor_id; o.textContent = `BS. ${d.full_name}`; docSel.appendChild(o); });
            renderDoctor(null); renderSlots([]); updateSummary();
        }

        function onDoctorChange() {
            let val = document.getElementById('doctor').value;
            if (!val) { state.doctor = null; renderDoctor(null); renderSlots([]); updateSummary(); return; }
            let list = doctorsByDept[state.deptId] || [];
            state.doctor = list.find(d => String(d.doctor_id) === String(val)) || null;
            state.scheduleId = null; state.time = null;
            renderDoctor(state.doctor); loadSlots(); updateSummary();
        }

        function onDateChange() { state.date = document.getElementById('work_date').value; state.scheduleId = null; state.time = null; if(state.doctor) loadSlots(); updateSummary(); }

        function loadSlots() {
            if (!state.doctor || !state.date) return;
            let key = `${state.doctor.doctor_id}_${state.date}`;
            if (scheduleData[key]) { renderSlots(scheduleData[key]); return; }
            document.getElementById('slot-wrap').innerHTML = '<div class="slot-loading" style="display:flex;gap:8px"><div class="mini-spin" style="width:16px;height:16px;border:2px solid #ccc; border-top-color:var(--primary); border-radius:50%; animation:spin .6s linear;"></div>Đang tải khung giờ...</div>';
            document.getElementById('slot-legend').style.display = 'none';
            fetch(`{{ route('appointments.schedules') }}?doctor_id=${state.doctor.doctor_id}&work_date=${state.date}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
                .then(r => r.json()).then(data => {
                    if (data.day_off) { document.getElementById('slot-wrap').innerHTML = '<span class="slot-placeholder">🚫 Bác sĩ nghỉ ngày này. Vui lòng chọn ngày khác.</span>'; return; }
                    scheduleData[key] = data.schedules || []; renderSlots(scheduleData[key]);
                }).catch(() => { document.getElementById('slot-wrap').innerHTML = '<span class="slot-placeholder" style="color:#e5484d">⚠️ Lỗi tải khung giờ. Vui lòng thử lại.</span>'; });
        }

        function renderSlots(schedules) {
            let wrap = document.getElementById('slot-wrap'); let legendDiv = document.getElementById('slot-legend');
            clearSlotState();
            if (!schedules || !schedules.length) { wrap.innerHTML = `<span class="slot-placeholder">${state.doctor ? 'Không có lịch khám cho ngày này' : 'Vui lòng chọn bác sĩ và ngày'}</span>`; legendDiv.style.display = 'none'; return; }
            wrap.innerHTML = schedules.map(s => { let booked = parseInt(s.booked_count)||0, max = parseInt(s.max_slot)||1, full = booked>=max; let time = s.start_time.substring(0,5); return `<button type="button" class="slot-btn${full ? ' full' : ''}" ${full ? 'disabled' : ''} data-sid="${s.schedule_id}" data-time="${s.start_time}" onclick="selectSlot(this)">${time}<span class="slot-count">${booked}/${max}</span></button>`; }).join('');
            legendDiv.innerHTML = `<div class="legend-item"><div class="legend-dot"></div>Còn trống</div><div class="legend-item"><div class="legend-dot full-dot"></div>Đã đầy</div><div class="legend-item"><div class="legend-dot sel-dot"></div>Đang chọn</div>`;
            legendDiv.style.display = 'flex';
        }

        function clearSlotState() { state.scheduleId = null; state.time = null; document.getElementById('schedule_id').value = ''; document.getElementById('appointment_time').value = ''; document.getElementById('slot-error').innerText = ''; }
        function selectSlot(el) { if(el.disabled || el.classList.contains('full')) return; document.querySelectorAll('.slot-btn').forEach(b=>b.classList.remove('selected')); el.classList.add('selected'); state.scheduleId = el.dataset.sid; state.time = el.dataset.time.substring(0,5); document.getElementById('schedule_id').value = state.scheduleId; document.getElementById('appointment_time').value = state.time; document.getElementById('slot-error').innerText = ''; updateSummary(); }
        function renderDoctor(doc) { let body = document.getElementById('doctor-body'); if(!doc) { body.innerHTML = `<div style="padding:20px 0;color:var(--gray-400);text-align:center"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><p>Chưa chọn bác sĩ</p></div>`; return; } let rating = parseFloat(doc.avg_rating)||0, reviews = parseInt(doc.total_reviews)||0; let stars = '★'.repeat(Math.round(rating))+'☆'.repeat(5-Math.round(rating)); let initials = doc.full_name.split(' ').slice(-2).map(w=>w[0]).join('').toUpperCase(); let avatar = doc.avatar_url ? `<img src="${doc.avatar_url}" style="width:100%;height:100%;border-radius:50%;object-fit:cover">` : initials; let price = parseInt(doc.price)||0; body.innerHTML = `<div class="doc-avatar">${avatar}</div><div class="doc-name">BS. ${doc.full_name}</div><div class="doc-bio">${doc.bio||''} ${doc.experience?`• ${doc.experience} năm kinh nghiệm`:''}</div><div class="doc-stars">${stars} <span style="color:var(--gray-400)">(${reviews.toLocaleString('vi-VN')})</span></div><div class="doc-meta"><div class="doc-meta-row"><span>💰 Phí khám</span><span style="font-weight:800;color:var(--primary)">${price.toLocaleString('vi-VN')} ₫</span></div></div>`; }
        function updateSummary() { let svcSel = document.getElementById('service_id_select'); let svcText = (svcSel && svcSel.selectedIndex>0) ? svcSel.options[svcSel.selectedIndex].text : ''; setSum('sum-dept', state.deptName); setSum('sum-doctor', state.doctor ? `BS. ${state.doctor.full_name}` : ''); setSum('sum-svc', svcText); setSum('sum-date', state.date ? state.date.split('-').reverse().join('/') : ''); setSum('sum-time', state.time || ''); }
        function setSum(id, val) { let el = document.getElementById(id); if(!el) return; if(val && val!=='—') { el.textContent = val; el.classList.remove('empty'); } else { el.textContent = '—'; el.classList.add('empty'); } }
        document.getElementById('booking-form').addEventListener('submit', function(e) { if(!state.scheduleId || !state.time) { e.preventDefault(); document.getElementById('slot-error').innerText = '⚠️ Vui lòng chọn khung giờ trước khi đặt lịch.'; document.getElementById('slot-wrap').scrollIntoView({behavior:'smooth'}); return; } let btn = document.getElementById('submit-btn'); let spinner = document.getElementById('spinner'); let icon = document.getElementById('submit-icon'); btn.disabled = true; spinner.style.display = 'inline-block'; icon.style.display = 'none'; });
        document.getElementById('service_id_select')?.addEventListener('change', updateSummary);
        (function init() { updateSummary(); })();
        window.onDeptChange = onDeptChange; window.onDoctorChange = onDoctorChange; window.onDateChange = onDateChange; window.selectSlot = selectSlot;
    </script>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        .invalid-msg { display: block; margin-top: 4px; }
        .slot-error-hint:empty { display: none; }
    </style>
</body>
</html>