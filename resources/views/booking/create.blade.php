<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch Khám Bệnh</title>
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
            max-width: 560px;
            backdrop-filter: blur(20px);
        }

        .logo {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #38bdf8, #34d399);
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

        .step-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .step-num {
            width: 22px;
            height: 22px;
            background: linear-gradient(135deg, #38bdf8, #34d399);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            color: #0c1a2e;
            flex-shrink: 0;
        }

        .step-label span {
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .field {
            margin-bottom: 18px;
        }

        .field select,
        .field textarea {
            background: rgba(255, 255, 255, 0.06);
            border: 0.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            color: #f1f5f9;
            font-family: 'Be Vietnam Pro', sans-serif;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            width: 100%;
            appearance: none;
        }

        .field select:focus,
        .field textarea:focus {
            border-color: rgba(56, 189, 248, 0.6);
            background: rgba(56, 189, 248, 0.06);
        }

        .field select option {
            background: #0f3460;
            color: #f1f5f9;
        }

        .field textarea {
            resize: vertical;
            min-height: 90px;
        }

        .field textarea::placeholder {
            color: #475569;
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

        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-success {
            background: rgba(52, 211, 153, 0.1);
            border: 0.5px solid rgba(52, 211, 153, 0.3);
            color: #6ee7b7;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 0.5px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-icon {
            flex-shrink: 0;
            margin-top: 1px;
        }

        .divider {
            height: 0.5px;
            background: rgba(255, 255, 255, 0.08);
            margin: 20px 0;
        }

        .btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #38bdf8, #34d399);
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

        .btn:hover {
            opacity: 0.9;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .note-hint {
            font-size: 12px;
            color: #475569;
            margin-top: 6px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 1.75rem 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
        </svg>
    </div>

    <h2>Đặt Lịch Khám Bệnh</h2>
    <p class="sub">Chọn bác sĩ, dịch vụ và thời gian phù hợp với bạn.</p>

    @if(session('success'))
        <div class="alert alert-success">
            <svg class="alert-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <svg class="alert-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('booking.store') }}" method="POST">
        @csrf

        <div class="field">
            <div class="step-label">
                <div class="step-num">1</div>
                <span>Chọn Bác sĩ và Thời gian</span>
            </div>
            <div class="sel-wrap">
                <select name="schedule_id" required>
                    <option value="">-- Vui lòng chọn lịch khám --</option>
                    @foreach($schedules as $schedule)
                        <option value="{{ $schedule->schedule_id }}" {{ old('schedule_id') == $schedule->schedule_id ? 'selected' : '' }}>
                            {{ $schedule->work_date }} | {{ $schedule->start_time }} — BS. {{ $schedule->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <div class="step-label">
                <div class="step-num">2</div>
                <span>Chọn Dịch vụ</span>
            </div>
            <div class="sel-wrap">
                <select name="service_id" required>
                    <option value="">-- Vui lòng chọn dịch vụ --</option>
                    @foreach($services as $service)
                        <option value="{{ $service->service_id }}" {{ old('service_id') == $service->service_id ? 'selected' : '' }}>
                            {{ $service->service_name }} ({{ $service->duration_minutes }} phút)
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="divider"></div>

        <div class="field">
            <div class="step-label">
                <div class="step-num">3</div>
                <span>Ghi chú triệu chứng</span>
            </div>
            <textarea name="note" placeholder="Mô tả triệu chứng để bác sĩ chuẩn bị tốt hơn...">{{ old('note') }}</textarea>
            <p class="note-hint">Không bắt buộc — nhưng sẽ giúp bác sĩ chuẩn bị tốt hơn.</p>
        </div>

        <button type="submit" class="btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Xác nhận Đặt Lịch
        </button>
    </form>
</div>

</body>
</html>