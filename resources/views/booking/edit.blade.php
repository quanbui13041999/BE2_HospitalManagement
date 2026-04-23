<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dời Lịch Khám</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0c1a2e 0%, #0f3460 50%, #0c1a2e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            font-family: 'Be Vietnam Pro', sans-serif;
        }

        .card {
            background: rgba(255, 255, 255, 0.04);
            border: 0.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
            backdrop-filter: blur(20px);
        }

        .logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #f59e0b, #f97316);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .logo svg {
            width: 24px;
            height: 24px;
        }

        h2 {
            font-size: 22px;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 4px;
        }

        .sub {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 2rem;
        }

        .current-box {
            background: rgba(245, 158, 11, 0.08);
            border: 0.5px solid rgba(245, 158, 11, 0.25);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }

        .current-box .label {
            font-size: 11px;
            font-weight: 500;
            color: #f59e0b;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .current-box .info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #cbd5e1;
            margin-bottom: 4px;
        }

        .current-box .info-row:last-child {
            margin-bottom: 0;
        }

        .current-box svg {
            width: 14px;
            height: 14px;
            color: #f59e0b;
            flex-shrink: 0;
        }

        .field {
            margin-bottom: 20px;
        }

        .field-label {
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }

        .sel-wrap {
            position: relative;
        }

        .sel-wrap::after {
            content: '';
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 5px solid #94a3b8;
            pointer-events: none;
        }

        .field select {
            background: rgba(255, 255, 255, 0.06);
            border: 0.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            color: #f1f5f9;
            font-family: 'Be Vietnam Pro', sans-serif;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            width: 100%;
            appearance: none;
        }

        .field select:focus {
            border-color: rgba(245, 158, 11, 0.5);
            background: rgba(245, 158, 11, 0.06);
        }

        .field select option {
            background: #0f3460;
            color: #f1f5f9;
        }

        .divider {
            height: 0.5px;
            background: rgba(255, 255, 255, 0.08);
            margin: 20px 0;
        }

        .btn-row {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-submit {
            flex: 1;
            padding: 12px;
            background: linear-gradient(135deg, #f59e0b, #f97316);
            border: none;
            border-radius: 10px;
            color: #0c1a2e;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Be Vietnam Pro', sans-serif;
            cursor: pointer;
            letter-spacing: 0.01em;
            transition: opacity 0.2s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover { opacity: 0.9; }
        .btn-submit:active { transform: scale(0.98); }

        .btn-back {
            padding: 12px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 0.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            font-family: 'Be Vietnam Pro', sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s, color 0.2s;
            white-space: nowrap;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.09);
            color: #f1f5f9;
        }

        @media (max-width: 480px) {
            .card { padding: 1.75rem 1.5rem; }
            .btn-row { flex-direction: column-reverse; }
            .btn-back { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
            <path d="M17 14l-4 4-2-2"/>
        </svg>
    </div>

    <h2>Dời Lịch Khám</h2>
    <p class="sub">Chọn thời gian mới phù hợp để cập nhật lịch hẹn.</p>

    <div class="current-box">
        <div class="label">Lịch hiện tại</div>
        <div class="info-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            {{ $appointment->work_date ?? 'N/A' }} lúc {{ $appointment->start_time ?? 'N/A' }}
        </div>
        <div class="info-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            BS. {{ $appointment->full_name ?? 'N/A' }}
        </div>
    </div>

    <form action="{{ route('booking.update', $appointment->appointment_id) }}" method="post">
        @csrf
        

        <div class="field">
            <span class="field-label">Thời gian mới</span>
            <div class="sel-wrap">
                <select name="schedule_id" required>
                    @foreach($schedules as $s)
                        <option value="{{ $s->schedule_id }}" {{ $s->schedule_id == $appointment->schedule_id ? 'selected' : '' }}>
                            {{ $s->work_date }} | {{ $s->start_time }} — BS. {{ $s->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="divider"></div>

        <div class="btn-row">
            <a href="{{ route('booking.index') }}" class="btn-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Quay lại
            </a>
            <button type="submit" class="btn-submit">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Cập nhật lịch
            </button>
        </div>
    </form>
</div>

</body>
</html>