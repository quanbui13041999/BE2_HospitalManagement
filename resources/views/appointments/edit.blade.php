{{-- resources/views/appointments/edit.blade.php --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dời Lịch Khám – HospitalBooking</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --blue-100: #dbeafe;
            --blue-50:  #eff6ff;
            --teal:     #0891b2;
            --violet:   #7c3aed;
            --violet-50:#f5f3ff;
            --violet-100:#ede9fe;
            --amber:    #d97706;
            --amber-50: #fffbeb;
            --green:    #059669;
            --green-50: #ecfdf5;
            --red:      #dc2626;
            --red-50:   #fef2f2;
            --gray-700: #374151;
            --gray-500: #6b7280;
            --gray-400: #9ca3af;
            --gray-200: #e5e7eb;
            --gray-100: #f3f4f6;
            --text:     #111827;
            --muted:    #6b7280;
            --r:        12px;
            --r-sm:     8px;
            --shadow:   0 1px 3px rgba(0,0,0,.08);
            --shadow-md:0 4px 16px rgba(37,99,235,.10);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--bg); color: var(--text);
            min-height: 100vh; font-size: 14px;
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
        .topbar-center a:hover { color: #fff; background: rgba(255,255,255,.1); }
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

        /* ── PAGE ── */
        .page { max-width: 820px; margin: 0 auto; padding: 28px 20px 60px; }

        /* ── PAGE TITLE ── */
        .page-title { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; }
        .page-title-icon {
            width: 46px; height: 46px; border-radius: 12px;
            background: linear-gradient(135deg, var(--violet), #6d28d9);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(124,58,237,.25); flex-shrink: 0;
        }
        .page-title h1 { font-size: 1.15rem; font-weight: 800; color: var(--blue-900); }
        .page-title p  { font-size: .78rem; color: var(--muted); margin-top: 2px; }

        /* ── PANEL ── */
        .panel {
            background: var(--white); border: 1px solid var(--gray-200);
            border-radius: var(--r); overflow: hidden;
            box-shadow: var(--shadow-md); margin-bottom: 18px;
        }
        .panel:last-child { margin-bottom: 0; }
        .panel-head {
            padding: 14px 20px; border-bottom: 1px solid var(--gray-200);
            display: flex; align-items: center; gap: 12px;
            background: var(--blue-50);
        }
        .panel-head .icon-wrap {
            width: 30px; height: 30px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .panel-head .icon-wrap.amber { background: linear-gradient(135deg, #f59e0b, #f97316); }
        .panel-head .icon-wrap.violet { background: linear-gradient(135deg, var(--violet), #6d28d9); }
        .panel-head h2 { font-size: .88rem; font-weight: 700; color: var(--blue-900); }
        .panel-head p  { font-size: .73rem; color: var(--muted); margin-top: 1px; }
        .panel-body { padding: 22px; }

        /* ── INFO GRID ── */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 28px; }
        
        .info-label {
            font-size: .68rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; color: var(--gray-400); margin-bottom: 4px;
        }
        .info-val { font-size: .88rem; font-weight: 600; color: var(--text); }
        .info-val.amber { color: var(--amber); }

        /* ── BADGES ── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 100px;
            font-size: .72rem; font-weight: 600; border: 1px solid;
        }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; }
        .badge-pending   { background: var(--amber-50); border-color: #fde68a; color: #92400e; }
        .badge-pending .badge-dot { background: var(--amber); }
        .badge-confirmed { background: var(--green-50); border-color: #a7f3d0; color: #065f46; }
        .badge-confirmed .badge-dot { background: var(--green); }

        /* ── ALERT ── */
        .alert {
            padding: 12px 16px; border-radius: var(--r-sm);
            font-size: .82rem; border: 1px solid;
            display: flex; align-items: flex-start; gap: 9px; margin-bottom: 20px;
        }
        .alert svg { flex-shrink: 0; margin-top: 1px; }
        .alert-error { background: var(--red-50); border-color: #fecaca; color: #991b1b; }
        .alert-info  { background: var(--blue-50); border-color: var(--blue-100); color: var(--blue-700); }

        /* ── SCHEDULE LIST ── */
        .schedule-list { display: flex; flex-direction: column; gap: 8px; }
        .schedule-option {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 16px;
            border: 1px solid var(--gray-200); border-radius: var(--r-sm);
            cursor: pointer; transition: all .15s;
            background: var(--gray-100);
        }
        .schedule-option:hover { border-color: var(--violet); background: var(--violet-50); }
        .schedule-option.selected { border-color: var(--violet); background: var(--violet-100); box-shadow: 0 0 0 3px rgba(124,58,237,.08); }
        .schedule-option input[type="radio"] { accent-color: var(--violet); width: 16px; height: 16px; flex-shrink: 0; }
        .sch-date { font-size: .84rem; font-weight: 700; color: var(--blue-900); }
        .sch-time { font-size: .76rem; color: var(--muted); margin-top: 2px; }
        .sch-slot {
            margin-left: auto; font-size: .7rem; font-weight: 700;
            padding: 3px 10px; border-radius: 100px; border: 1px solid; flex-shrink: 0;
        }
        .sch-slot.ok    { color: #065f46; background: var(--green-50); border-color: #a7f3d0; }
        .sch-slot.tight { color: #92400e; background: var(--amber-50); border-color: #fde68a; }

        .empty-schedules {
            padding: 32px 16px; text-align: center;
            border: 1px dashed var(--gray-200); border-radius: var(--r-sm);
            color: var(--muted); font-size: .84rem; background: var(--gray-100);
        }
        .empty-schedules svg { display: block; margin: 0 auto 10px; opacity: .4; }

        /* ── FORM ELEMENTS ── */
        .form-label {
            font-size: .75rem; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: .04em;
            margin-bottom: 7px; display: block;
        }
        .form-textarea {
            width: 100%; padding: 10px 14px;
            background: var(--gray-100); border: 1px solid var(--gray-200);
            border-radius: var(--r-sm); font-family: inherit;
            font-size: .84rem; color: var(--text);
            resize: vertical; min-height: 80px; outline: none;
            transition: border-color .15s, background .15s;
        }
        .form-textarea:focus { border-color: var(--violet); background: var(--violet-50); }
        .form-textarea::placeholder { color: var(--gray-400); }

        /* ── BUTTONS ── */
        .btn-row {
            display: flex; justify-content: space-between; align-items: center;
            gap: 10px; margin-top: 24px; padding-top: 18px;
            border-top: 1px solid var(--gray-200);
        }
        .btn {
            padding: 10px 22px; border-radius: var(--r-sm);
            font-family: inherit; font-size: .84rem; font-weight: 700;
            cursor: pointer; transition: all .15s;
            display: inline-flex; align-items: center; gap: 7px;
            border: none; text-decoration: none;
        }
        .btn-secondary {
            background: var(--gray-100); border: 1px solid var(--gray-200); color: var(--gray-700);
        }
        .btn-secondary:hover { background: var(--gray-200); color: var(--text); }
        .btn-violet {
            background: linear-gradient(135deg, var(--violet), #6d28d9);
            color: #fff; box-shadow: 0 4px 14px rgba(124,58,237,.3);
        }
        .btn-violet:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(124,58,237,.35); }
        .btn-violet:active { transform: translateY(0); }
        .btn-violet:disabled { opacity: .4; cursor: not-allowed; transform: none; box-shadow: none; }

        .spinner {
            display: none; width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,.35); border-top-color: #fff;
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

        @media (max-width: 580px) {
            .info-grid { grid-template-columns: 1fr; }
            .btn-row { flex-direction: column-reverse; }
            .btn { width: 100%; justify-content: center; }
            .topbar-center { display: none; }
        }
    </style>
</head>
<body>

{{-- ── TOPBAR ── --}}
<nav class="topbar">
    <a href="{{ route('home') }}" class="topbar-brand">
        <div class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        <div>
            HospitalBooking
            <span class="brand-sub">Hệ thống đặt lịch khám</span>
        </div>
    </a>

    <div class="topbar-center">
        <a href="{{ route('home') }}">Trang chủ</a>
        <a href="{{ route('appointments.index') }}">Lịch hẹn</a>
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
    <span>›</span>
    <a href="{{ route('appointments.index') }}">Lịch hẹn của tôi</a>
    <span>›</span>
    <span style="color:var(--gray-600)">Dời lịch</span>
</div>

<div class="page">

    {{-- Page title --}}
    <div class="page-title">
        <div class="page-title-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/></svg>
        </div>
        <div>
            <h1>Dời Lịch Khám</h1>
            <p>Chọn khung giờ mới phù hợp với cùng bác sĩ</p>
        </div>
    </div>

    {{-- Error --}}
    @if($errors->has('msg'))
    <div class="alert alert-error">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ $errors->first('msg') }}
    </div>
    @endif

    @if($availableSchedules->isEmpty())
    <div class="alert alert-info">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Không có lịch trống trong 14 ngày tới của BS. {{ $appointment->doctor_name }}. Vui lòng liên hệ phòng khám.
    </div>
    @endif

    <form action="{{ route('appointments.update', $appointment->appointment_id) }}" method="POST" id="reschedule-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="new_appointment_time" id="new_appointment_time">

        {{-- Panel 1: Lịch hiện tại --}}
        <div class="panel">
            <div class="panel-head">
                <div class="icon-wrap amber">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
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
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <h2>Chọn lịch mới</h2>
                    <p>Khung giờ còn trống của BS. {{ $appointment->doctor_name }} trong 14 ngày tới</p>
                </div>
            </div>
            <div class="panel-body">

                @if($availableSchedules->isEmpty())
                <div class="empty-schedules">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Không có lịch trống nào trong 14 ngày tới.
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
                <p style="font-size:.73rem;color:var(--red);margin-top:8px">{{ $message }}</p>
                @enderror
                @endif

                {{-- Lý do --}}
                <div style="margin-top:20px">
                    <label class="form-label" for="reschedule_reason">
                        Lý do dời lịch
                        <span style="text-transform:none;font-weight:400;color:var(--gray-400)">(tùy chọn)</span>
                    </label>
                    <textarea name="reschedule_reason" id="reschedule_reason"
                        class="form-textarea"
                        placeholder="VD: bận công việc đột xuất, trùng lịch khác...">{{ old('reschedule_reason') }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="btn-row">
                    <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        Quay lại
                    </a>
                    <button type="submit" class="btn btn-violet" id="submit-btn"
                        {{ $availableSchedules->isEmpty() ? 'disabled' : '' }}>
                        <span class="spinner" id="spinner"></span>
                        <svg id="submit-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/></svg>
                        Xác nhận dời lịch
                    </button>
                </div>

            </div>
        </div>

    </form>
</div>

{{-- ── FOOTER ── --}}
<footer class="footer">
    © {{ date('Y') }} HospitalBooking &nbsp;·&nbsp; <a href="#">Chính sách bảo mật</a> &nbsp;·&nbsp; <a href="#">Hỗ trợ</a>
</footer>

<script>
function onScheduleSelect(radio) {
    document.getElementById('new_appointment_time').value = radio.dataset.time;
    document.querySelectorAll('.schedule-option').forEach(el => el.classList.remove('selected'));
    radio.closest('.schedule-option').classList.add('selected');
}

// Auto-select nếu chỉ có 1 lịch trống
window.addEventListener('DOMContentLoaded', () => {
    const radios = document.querySelectorAll('input[name="new_schedule_id"]');
    if (radios.length === 1) { radios[0].checked = true; onScheduleSelect(radios[0]); }
});

document.getElementById('reschedule-form')?.addEventListener('submit', function(e) {
    const btn     = document.getElementById('submit-btn');
    const spinner = document.getElementById('spinner');
    const icon    = document.getElementById('submit-icon');
    btn.disabled          = true;
    spinner.style.display = 'block';
    icon.style.display    = 'none';
});
</script>
</body>
</html>