<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; background:#f4f4f4; padding:20px; margin:0; }
    .box { background:#fff; max-width:580px; margin:auto; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
    .header { background:linear-gradient(135deg,#1565c0,#0277bd); padding:28px 32px; text-align:center; }
    .header h1 { color:#fff; margin:0; font-size:20px; }
    .body { padding:28px 32px; }
    .greeting { font-size:15px; color:#333; margin-bottom:16px; }
    table { width:100%; border-collapse:collapse; margin:16px 0; }
    th { background:#f8f8f8; text-align:left; padding:10px 14px; font-size:13px; color:#555; border:1px solid #e0e0e0; width:40%; }
    td { padding:10px 14px; font-size:13px; color:#333; border:1px solid #e0e0e0; }
    .status-badge { display:inline-block; background:#fff8e1; color:#f59e0b; padding:3px 12px; border-radius:100px; font-size:12px; font-weight:bold; border:1px solid #fde68a; }
    .note-box { background:#e3f2fd; border-left:4px solid #1565c0; padding:14px 16px; border-radius:4px; font-size:13px; color:#333; margin:16px 0; }
    .note-box ul { margin:8px 0 0 16px; padding:0; }
    .note-box li { margin-bottom:5px; line-height:1.5; }
    .reason-box { background:#f8f8f8; border:1px solid #e0e0e0; border-radius:6px; padding:12px 16px; font-size:13px; color:#555; margin:12px 0; font-style:italic; }
    .btn-wrap { text-align:center; margin:24px 0; }
    .btn { display:inline-block; padding:13px 28px; background:#1565c0; color:#fff; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px; }
    .footer { background:#f8f8f8; padding:16px 32px; text-align:center; font-size:12px; color:#999; border-top:1px solid #eee; }
    hr { border:none; border-top:1px solid #eee; margin:20px 0; }
</style>
</head>
<body>
<div class="box">

    <div class="header">
        <h1>🔄 Xác Nhận Dời Lịch Khám</h1>
    </div>

    <div class="body">
        <p class="greeting">
            Kính gửi <strong>{{ $user->full_name }}</strong>,<br>
            Lịch hẹn khám bệnh của bạn đã được <strong>dời thành công</strong>
            vào {{ now()->format('d/m/Y H:i') }}.
        </p>

        <h3 style="color:#333;margin-bottom:8px">📋 Thông Tin Lịch Hẹn Mới</h3>
        <table>
            <tr><th>Bác Sĩ</th><td>BS. {{ $appointment->doctor_name }}</td></tr>
            <tr><th>Chuyên Khoa</th><td>{{ $appointment->department_name }}</td></tr>
            <tr><th>Ngày Khám (mới)</th>
                <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('d/m/Y') }}</td></tr>
            <tr><th>Giờ Khám (mới)</th>
                <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td></tr>
            <tr><th>Số Thứ Tự</th><td>#{{ $appointment->queue_number }}</td></tr>
            <tr><th>Trạng Thái</th>
                <td><span class="status-badge">⏳ Chờ xác nhận</span></td></tr>
        </table>

        <h3 style="color:#333;margin-bottom:8px">📝 Lý Do Dời Lịch</h3>
        <div class="reason-box">
            @if($appointment->cancel_reason && str_starts_with($appointment->cancel_reason, 'Dời lịch:'))
                {{ substr($appointment->cancel_reason, 10) }}
            @else
                {{ $appointment->cancel_reason ?? 'Không có ghi chú' }}
            @endif
        </div>

        <hr>

        <div class="note-box">
            <strong>⏰ Lưu Ý Quan Trọng:</strong>
            <ul>
                <li>Vui lòng đến khám đúng giờ đã đặt (tối thiểu <strong>10 phút trước</strong>)</li>
                <li>Nếu cần hủy, vui lòng hủy trước giờ khám <strong>ít nhất 2 tiếng</strong></li>
                <li>Bạn có thể tiếp tục dời lịch khám nếu cần thiết</li>
                <li>Nếu có thắc mắc, vui lòng liên hệ <strong>phòng khám</strong></li>
            </ul>
        </div>

        <div class="btn-wrap">
            <a href="{{ route('appointments.index') }}" class="btn">
                📋 Xem Lịch Hẹn Của Tôi
            </a>
        </div>
    </div>

    <div class="footer">
        Cảm ơn bạn đã tin tưởng <strong>HospitalC</strong>!<br>
        © {{ date('Y') }} Bệnh Viện HospitalC
    </div>

</div>
</body>
</html>
