<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; background:#f4f4f4; padding:20px; margin:0; }
    .box { background:#fff; max-width:580px; margin:auto; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
    .header { background:linear-gradient(135deg,#b71c1c,#e53935); padding:28px 32px; text-align:center; }
    .header h1 { color:#fff; margin:0; font-size:20px; }
    .body { padding:28px 32px; }
    .greeting { font-size:15px; color:#333; margin-bottom:16px; }
    table { width:100%; border-collapse:collapse; margin:16px 0; }
    th { background:#f8f8f8; text-align:left; padding:10px 14px; font-size:13px; color:#555; border:1px solid #e0e0e0; }
    td { padding:10px 14px; font-size:13px; color:#333; border:1px solid #e0e0e0; }
    .btn-wrap { text-align:center; margin:24px 0; }
    .btn { display:inline-block; padding:13px 28px; background:#1565c0; color:#fff; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px; }
    .note { background:#fff8e1; border-left:4px solid #fbc02d; padding:12px 16px; border-radius:4px; font-size:13px; color:#555; margin:16px 0; }
    .footer { background:#f8f8f8; padding:16px 32px; text-align:center; font-size:12px; color:#999; border-top:1px solid #eee; }
</style>
</head>
<body>
<div class="box">

    <div class="header">
        <h1>❌ Xác Nhận Hủy Lịch Khám</h1>
    </div>

    <div class="body">
        <p class="greeting">
            Kính gửi <strong>{{ $user->full_name }}</strong>,<br>
            Lịch hẹn khám bệnh của bạn đã được <strong>hủy thành công</strong>
            vào {{ now()->format('d/m/Y H:i') }}.
        </p>

        <h3 style="color:#333;margin-bottom:8px">📋 Thông Tin Lịch Hẹn Đã Hủy</h3>
        <table>
            <tr><th>Lịch Hẹn ID</th><td>#{{ $appointment->appointment_id }}</td></tr>
            <tr><th>Bác Sĩ</th><td>BS. {{ $appointment->doctor_name }}</td></tr>
            <tr><th>Ngày Khám (cũ)</th>
                <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('d/m/Y') }}</td></tr>
            <tr><th>Giờ Khám (cũ)</th>
                <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td></tr>
            <tr><th>Lý Do Hủy</th>
                <td>{{ $appointment->cancel_reason ?? 'Bệnh nhân tự hủy' }}</td></tr>
        </table>

        <div class="note">
            ⚠️ Slot khám của bạn đã được <strong>giải phóng</strong>.
            Nếu cần tư vấn thêm, vui lòng liên hệ trực tiếp phòng khám.
        </div>

        <div class="btn-wrap">
            <a href="{{ route('appointments.create') }}" class="btn">
                🔄 Đặt Lịch Khám Mới
            </a>
        </div>
    </div>

    <div class="footer">
        Cảm ơn bạn đã sử dụng dịch vụ <strong>HospitalBooking</strong>!<br>
        © {{ date('Y') }} Bệnh Viện HospitalBooking
    </div>

</div>
</body>
</html>