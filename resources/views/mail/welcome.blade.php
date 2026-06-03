<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .box { background: #fff; max-width: 500px; margin: auto; padding: 32px; border-radius: 10px; }
        h1 { color: #1565c0; font-size: 20px; }
        p { color: #444; line-height: 1.6; }
        .btn { display: inline-block; margin-top: 20px; padding: 12px 24px;
               background: #1565c0; color: #fff; border-radius: 8px; text-decoration: none; }
        .footer { margin-top: 28px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="box">
    <h1>🏥 Chào mừng đến HospitalC!</h1>
    <p>Xin chào <strong>{{ $user->full_name }}</strong>,</p>
    <p>Tài khoản của bạn đã được tạo thành công. Bạn có thể đặt lịch khám bệnh ngay bây giờ.</p>
    <a href="{{ url('/dat-lich') }}" class="btn">Đặt lịch ngay</a>
    <div class="footer">
        Email: {{ $user->email }}<br>
        Trân trọng, <strong>HospitalC</strong>
    </div>
</div>
</body>
</html>
