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
            --bg:      #060d18;
            --bg2:     #0b1628;
            --surface: rgba(255,255,255,.04);
            --border:  rgba(255,255,255,.09);
            --border2: rgba(255,255,255,.15);
            --cyan:    #38bdf8;
            --teal:    #2dd4bf;
            --green:   #34d399;
            --red:     #f87171;
            --orange:  #fb923c;
            --yellow:  #fbbf24;
            --violet:  #a78bfa;
            --text:    #f1f5f9;
            --muted:   #94a3b8;
            --dim:     #475569;
            --r:       14px;
            --r-sm:    9px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: var(--bg); color: var(--text);
            min-height: 100vh; font-size: 14px;
        }

        /* TOPBAR */
        .topbar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(6,13,24,.85); backdrop-filter: blur(16px);
            border-bottom: .5px solid var(--border);
            padding: 0 24px; height: 56px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .topbar-brand {
            display: flex; align-items: center; gap: 10px;
            font-weight: 800; font-size: .95rem; color: var(--text);
            text-decoration: none;
        }
        .topbar-brand .icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--cyan), var(--green));
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
        }
        .topbar-nav { display: flex; align-items: center; gap: 8px; }
        .topbar-nav a, .topbar-nav button {
            font-family: inherit; font-size: .82rem; font-weight: 500;
            color: var(--muted); text-decoration: none;
            padding: 6px 14px; border-radius: 8px;
            border: none; cursor: pointer; background: none;
            transition: color .15s, background .15s;
        }
        .topbar-nav a:hover, .topbar-nav button:hover {
            color: var(--text); background: var(--surface);
        }
        .topbar-nav .btn-logout { color: var(--red); border: .5px solid rgba(248,113,113,.2); }
        .topbar-nav .btn-logout:hover { background: rgba(248,113,113,.08); }

        /* BREADCRUMB */
        .breadcrumb-bar {
            background: rgba(255,255,255,.02);
            border-bottom: .5px solid var(--border);
            padding: 10px 24px;
            display: flex; align-items: center; gap: 6px;
            font-size: .75rem; color: var(--dim);
        }
        .breadcrumb-bar a { color: var(--muted); text-decoration: none; transition: color .15s; }
        .breadcrumb-bar a:hover { color: var(--text); }
        .breadcrumb-bar svg { opacity: .5; }

        /* PAGE */
        .page {
            max-width: 800px; margin: 0 auto;
            padding: 28px 20px 60px;
        }

        /* PAGE TITLE */
        .page-title {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 24px;
        }
        .page-title-icon {
            width: 40px; height: 40px; border-radius: 11px;
            background: linear-gradient(135deg, var(--violet), #7c3aed);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .page-title h1 { font-size: 1.1rem; font-weight: 800; }
        .page-title p  { font-size: .78rem; color: var(--muted); margin-top: 2px; }

        /* PANEL */
        .panel {
            background: var(--surface);
            border: .5px solid var(--border);
            border-radius: var(--r);
            overflow: hidden;
            margin-bottom: 18px;
        }
        .panel:last-child { margin-bottom: 0; }
        .panel-head {
            padding: 13px 20px;
            border-bottom: .5px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .panel-head .icon-wrap {
            width: 26px; height: 26px; border-radius: 7px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .panel-head .icon-wrap.amber { background: linear-gradient(135deg, #f59e0b, #f97316); }
        .panel-head .icon-wrap.violet { background: linear-gradient(135deg, var(--violet), #7c3aed); }
        .panel-head h2 { font-size: .85rem; font-weight: 700; }
        .panel-head p  { font-size: .73rem; color: var(--muted); margin-top: 1px; }
        .panel-body { padding: 20px; }

        /* CURRENT APPT */
        .info-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 14px 24px;
        }
        .info-item {}
        .info-label {
            font-size: .68rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; color: var(--dim); margin-bottom: 3px;
        }
        .info-val { font-size: .83rem; font-weight: 600; color: var(--text); }
        .info-val.amber { color: var(--yellow); }

        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 2px 10px; border-radius: 100px;
            font-size: .7rem; font-weight: 700; border: .5px solid;
        }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; }
        .badge-pending  { background: rgba(251,191,36,.08); border-color: rgba(251,191,36,.3); color: #fde68a; }
        .badge-pending .badge-dot { background: var(--yellow); }
        .badge-confirmed{ background: rgba(52,211,153,.08); border-color: rgba(52,211,153,.25); color: #6ee7b7; }
        .badge-confirmed .badge-dot { background: var(--green); }

        /* ALERT */
        .alert {
            padding: 11px 16px; border-radius: var(--r-sm);
            font-size: .81rem; border: .5px solid;
            display: flex; align-items: flex-start; gap: 9px;
            margin-bottom: 20px;
        }
        .alert svg { flex-shrink: 0; margin-top: 1px; }
        .alert-error { background: rgba(248,113,113,.08); border-color: rgba(248,113,113,.25); color: #fca5a5; }
        .alert-info  { background: rgba(56,189,248,.06); border-color: rgba(56,189,248,.2); color: #7dd3fc; }

        /* SCHEDULE OPTIONS */
        .schedule-list { display: flex; flex-direction: column; gap: 8px; }

        .schedule-option {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px;
            border: .5px solid var(--border2);
            border-radius: var(--r-sm);
            cursor: pointer;
            transition: all .15s;
            background: rgba(255,255,255,.03);
        }
        .schedule-option:hover { border-color: rgba(167,139,250,.4); background: rgba(167,139,250,.05); }
        .schedule-option.selected { border-color: var(--violet); background: rgba(167,139,250,.08); }
        .schedule-option input[type="radio"] { accent-color: var(--violet); width: 15px; height: 15px; flex-shrink: 0; }

        .sch-date { font-size: .81rem; font-weight: 700; color: var(--text); }
        .sch-time { font-size: .76rem; color: var(--muted); margin-top: 1px; }
        .sch-slot {
            margin-left: auto; font-size: .7rem; font-weight: 700;
            padding: 2px 9px; border-radius: 100px; border: .5px solid; flex-shrink: 0;
        }
        .sch-slot.ok   { color: #6ee7b7; background: rgba(52,211,153,.08); border-color: rgba(52,211,153,.25); }
        .sch-slot.tight{ color: #fde68a; background: rgba(251,191,36,.08); border-color: rgba(251,191,36,.3); }

        .empty-schedules {
            padding: 28px 16px; text-align: center;
            border: .5px dashed var(--border2); border-radius: var(--r-sm);
            color: var(--dim); font-size: .82rem;
        }
        .empty-schedules svg { display: block; margin: 0 auto 8px; opacity: .3; }

        /* FORM ELEMENTS */
        .form-label {
            font-size: .75rem; font-weight: 600; color: var(--muted);
            text-transform: uppercase; letter-spacing: .04em;
            margin-bottom: 7px; display: block;
        }
        .form-textarea {
            width: 100%; padding: 10px 14px;
            background: rgba(255,255,255,.05);
            border: .5px solid var(--border2);
            border-radius: var(--r-sm);
            font-family: inherit; font-size: .84rem; color: var(--text);
            resize: vertical; min-height: 76px; outline: none;
            transition: border-color .15s, background .15s;
        }
        .form-textarea:focus {
            border-color: rgba(167,139,250,.5);
            background: rgba(167,139,250,.04);
        }
        .form-textarea::placeholder { color: var(--dim); }

        /* BUTTONS */
        .btn-row {
            display: flex; justify-content: space-between; align-items: center;
            gap: 10px; margin-top: 24px; padding-top: 18px;
            border-top: .5px solid var(--border);
        }
        .btn {
            padding: 10px 22px; border-radius: var(--r-sm);
            font-family: inherit; font-size: .84rem; font-weight: 700;
            cursor: pointer; transition: all .15s;
            display: inline-flex; align-items: center; gap: 7px;
            border: none; text-decoration: none;
        }
        .btn-secondary {
            background: rgba(255,255,255,.06);
            border: .5px solid var(--border2); color: var(--muted);
        }
        .btn-secondary:hover { background: rgba(255,255,255,.1); color: var(--text); }

        .btn-violet {
            background: linear-gradient(135deg, var(--violet), #7c3aed);
            color: #fff;
            box-shadow: 0 4px 16px rgba(124,58,237,.3);
        }
        .btn-violet:hover { opacity: .9; transform: translateY(-1px); }
        .btn-violet:active { transform: translateY(0); }
        .btn-violet:disabled { opacity: .4; cursor: not-allowed; transform: none; box-shadow: none; }

        .spinner {
            display: none; width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%; animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 580px) {
            .info-grid { grid-template-columns: 1fr; }
            .btn-row { flex-direction: column-reverse; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

{{-- TOPBAR --}}
<nav class="topbar">
    <a href="{{ route('home') }}" class="topbar-brand">
        <div class="icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
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

{{-- BREADCRUMB --}}
<div class="breadcrumb-bar">
    <a href="{{ route('home') }}">Trang chủ</a>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    <a href="{{ route('appointments.index') }}">Lịch hẹn của tôi</a>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Dời lịch</span>
</div>

<div class="page">

    {{-- Page title --}}
    <div class="page-title">
        <div class="page-title-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/></svg>
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
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
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
                        <div class="info-val">
                            {{ \Carbon\Carbon::parse($appointment->work_date)->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Giờ khám</div>
                        <div class="info-val">
                            {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}
                        </div>
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
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <h2>Chọn lịch mới</h2>
                    <p>Khung giờ còn trống của BS. {{ $appointment->doctor_name }} trong 14 ngày tới</p>
                </div>
            </div>
            <div class="panel-body">

                @if($availableSchedules->isEmpty())
                <div class="empty-schedules">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
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
                        <span style="text-transform:none;font-weight:400;color:var(--dim)">(tùy chọn)</span>
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

<script>
function onScheduleSelect(radio) {
    document.getElementById('new_appointment_time').value = radio.dataset.time;
    document.querySelectorAll('.schedule-option').forEach(el => el.classList.remove('selected'));
    radio.closest('.schedule-option').classList.add('selected');
}

// Auto-select nếu chỉ có 1 lịch
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