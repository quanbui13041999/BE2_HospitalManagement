<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div style="max-width: 600px; margin: 50px auto; font-family: sans-serif;">
    <h2>Đặt Lịch Khám Bệnh</h2>

    @if(session('success'))
        <div style="color: green; padding: 10px; border: 1px solid green; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 15px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('booking.store') }}" method="POST">
        @csrf

        <div style="margin-bottom: 15px;">
            <label><b>1. Chọn Bác sĩ và Thời gian:</b></label><br>
            <select name="schedule_id" required style="width: 100%; padding: 8px;">
                <option value="">-- Vui lòng chọn lịch khám --</option>
                @foreach($schedules as $schedule)
                    <option value="{{ $schedule->schedule_id }}">
                        Ngày: {{ $schedule->work_date }} | Giờ: {{ $schedule->start_time }} - Bác sĩ: {{ $schedule->full_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label><b>2. Chọn Dịch vụ:</b></label><br>
            <select name="service_id" required style="width: 100%; padding: 8px;">
                <option value="">-- Vui lòng chọn dịch vụ --</option>
                @foreach($services as $service)
                    <option value="{{ $service->service_id }}">
                        {{ $service->service_name }} (Thời gian: {{ $service->duration_minutes }} phút)
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label><b>3. Ghi chú triệu chứng (nếu có):</b></label><br>
            <textarea name="note" rows="3" style="width: 100%; padding: 8px;" placeholder="Bác sĩ ơi em bị đau đầu..."></textarea>
        </div>

        <button type="submit" style="padding: 10px 20px; background: blue; color: white; border: none; cursor: pointer;">
            Xác nhận Đặt Lịch
        </button>
    </form>
</div>
</body>
</html>