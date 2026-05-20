<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác nhận đăng ký nghỉ</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: #f3f4f6;
      color: #111827;
      padding: 32px 16px;
    }
    .wrapper {
      max-width: 560px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(0,0,0,.08);
    }
    .header {
      background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
      padding: 28px 32px;
      text-align: center;
    }
    .header-title { color: #fff; font-size: 22px; font-weight: 700; line-height: 1.3; }
    .body { padding: 32px; }
    .section { margin-bottom: 24px; }
    .section-title { font-size: 15px; font-weight: 700; color: #1e3a5f; margin-bottom: 12px; }
    .info-box {
      background: #f0fdf4;
      border-left: 4px solid #16a34a;
      padding: 16px;
      border-radius: 8px;
      margin-bottom: 16px;
    }
    .info-item { display: flex; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
    .info-item:last-child { border-bottom: none; }
    .info-label { font-weight: 600; min-width: 120px; color: #374151; }
    .info-value { color: #111827; }
    .footer {
      background: #f9fafb;
      border-top: 1px solid #e5e7eb;
      padding: 20px 32px;
      text-align: center;
      font-size: 12px;
      color: #9ca3af;
      line-height: 1.7;
    }
  </style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-title">✅ Xác Nhận Đăng Ký Nghỉ</div>
  </div>
  
  <div class="body">
    <p style="font-size:14px; color:#374151; margin-bottom:20px;">
      Bác sĩ <strong>{{ $doctor->full_name }}</strong>,<br><br>
      Yêu cầu nghỉ của bạn đã được ghi nhận và xử lý. Các ca khám phù hợp đã được khoá lại,
      và bệnh nhân bị ảnh hưởng đã được thông báo bằng email.
    </p>

    <div class="section">
      <div class="section-title">📋 Chi tiết yêu cầu</div>
      <div class="info-box">
        <div class="info-item">
          <span class="info-label">Loại nghỉ:</span>
          <span class="info-value">{{ $type === 'sick' ? 'Bệnh / đột xuất' : ($type === 'leave' ? 'Nghỉ phép' : ($type === 'conference' ? 'Hội nghị / đào tạo' : $type)) }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Ngày:</span>
          <span class="info-value">{{ $date }}@if($end_date !== $date) đến {{ $end_date }}@endif</span>
        </div>
        <div class="info-item">
          <span class="info-label">Buổi:</span>
          <span class="info-value">{{ $session === 'all' ? 'Cả ngày' : ($session === 'morning' ? 'Sáng' : 'Chiều') }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Lý do:</span>
          <span class="info-value">{{ $reason }}</span>
        </div>
      </div>
    </div>

    <div class="section">
      <div class="section-title">⚠️ Tác động</div>
      <p style="font-size:13px; color:#374151; line-height:1.6; margin-bottom:12px;">
        <strong>{{ $blocked_schedules }}</strong> ca khám đã bị khóa<br>
        <strong>{{ $affected_appointments }}</strong> bệnh nhân bị ảnh hưởng<br>
        ✉️ Email thông báo bác sĩ nghỉ và gợi ý lịch mới đã được gửi đến bệnh nhân
      </p>
    </div>
  </div>

  <div class="footer">
    Email này được gửi tự động từ hệ thống {{ config('app.name') }}.<br>
    Mọi thắc mắc vui lòng liên hệ ban quản lý bệnh viện.
  </div>
</div>
</body>
</html>
