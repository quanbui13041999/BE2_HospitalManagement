<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thông báo thay đổi lịch hẹn</title>
  <style>
    /* Reset & base */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: #f3f4f6;
      color: #111827;
      padding: 32px 16px;
    }

    /* Wrapper */
    .wrapper {
      max-width: 560px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(0,0,0,.08);
    }

    /* Header */
    .header {
      background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
      padding: 28px 32px;
      text-align: center;
    }
    .logo-row {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 16px;
    }
    .logo-icon {
      width: 36px; height: 36px;
      background: rgba(255,255,255,.25);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
    }
    .logo-text { color: #fff; font-size: 20px; font-weight: 700; }
    .header-title {
      color: #fff;
      font-size: 22px;
      font-weight: 700;
      line-height: 1.3;
    }
    .header-sub {
      color: rgba(255,255,255,.8);
      font-size: 13px;
      margin-top: 6px;
    }

    /* Body */
    .body { padding: 32px; }

    /* Alert box — lịch bị huỷ */
    .alert-box {
      background: #fff7ed;
      border: 1.5px solid #fed7aa;
      border-radius: 12px;
      padding: 16px 20px;
      margin-bottom: 24px;
    }
    .alert-title {
      color: #c2410c;
      font-weight: 700;
      font-size: 15px;
      margin-bottom: 10px;
      display: flex; align-items: center; gap: 6px;
    }
    .info-row {
      display: flex;
      font-size: 13px;
      padding: 4px 0;
      border-bottom: 1px solid #fee2c8;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label {
      color: #92400e;
      font-weight: 600;
      min-width: 110px;
    }
    .info-value { color: #1c1917; }

    /* Alternatives */
    .section-title {
      font-size: 15px;
      font-weight: 700;
      color: #1e3a5f;
      margin-bottom: 12px;
      display: flex; align-items: center; gap-6px;
    }
    .alt-card {
      border: 1.5px solid #e5e7eb;
      border-radius: 12px;
      padding: 14px 16px;
      margin-bottom: 10px;
      display: flex;
      align-items: flex-start;
      gap: 14px;
    }
    .alt-avatar {
      width: 42px; height: 42px;
      border-radius: 50%;
      background: #dbeafe;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      flex-shrink: 0;
    }
    .alt-info { flex: 1; }
    .alt-name {
      font-weight: 700;
      font-size: 14px;
      color: #111827;
      margin-bottom: 2px;
    }
    .alt-dept {
      font-size: 12px;
      color: #6b7280;
      margin-bottom: 6px;
    }
    .alt-meta {
      font-size: 12px;
      color: #374151;
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }
    .badge {
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
      border-radius: 99px;
      padding: 2px 10px;
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
    }
    .badge-green {
      background: #f0fdf4;
      color: #15803d;
      border-color: #bbf7d0;
    }

    /* CTA button */
    .cta-wrap { text-align: center; margin: 28px 0 8px; }
    .cta-btn {
      display: inline-block;
      background: #2563eb;
      color: #fff !important;
      font-weight: 700;
      font-size: 15px;
      padding: 14px 36px;
      border-radius: 12px;
      text-decoration: none;
      letter-spacing: .3px;
    }

    /* No alternatives */
    .no-alt {
      background: #f9fafb;
      border: 1px dashed #d1d5db;
      border-radius: 10px;
      padding: 14px 16px;
      font-size: 13px;
      color: #6b7280;
      text-align: center;
    }

    /* Note */
    .note {
      background: #f0fdf4;
      border-left: 3px solid #16a34a;
      border-radius: 0 8px 8px 0;
      padding: 12px 16px;
      font-size: 12px;
      color: #166534;
      margin-top: 24px;
      line-height: 1.6;
    }

    /* Footer */
    .footer {
      background: #f9fafb;
      border-top: 1px solid #e5e7eb;
      padding: 20px 32px;
      text-align: center;
      font-size: 11px;
      color: #9ca3af;
      line-height: 1.7;
    }
    .footer a { color: #6b7280; }
  </style>
</head>
<body>
<div class="wrapper">

  <!-- ── Header ─────────────────────────────────────────────────── -->
  <div class="header">
    <div class="logo-row">
      <div class="logo-icon">
        <svg width="20" height="20" fill="none" stroke="#fff" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <span class="logo-text">MediBook</span>
    </div>
    <div class="header-title">⚠️ Lịch hẹn của bạn đã thay đổi</div>
    <div class="header-sub">Vui lòng đọc thông tin bên dưới và chọn lịch mới phù hợp</div>
  </div>

  <!-- ── Body ──────────────────────────────────────────────────── -->
  <div class="body">

    <p style="font-size:14px; color:#374151; margin-bottom:20px;">
      Xin chào <strong>{{ $patient->name }}</strong>,<br><br>
      Chúng tôi xin thông báo lịch hẹn của bạn bên dưới đã bị <strong style="color:#dc2626;">huỷ</strong>
      do bác sĩ phụ trách có lịch nghỉ đột xuất. Chúng tôi thành thật xin lỗi vì sự bất tiện này.
    </p>

    <!-- Lịch bị huỷ -->
    <div class="alert-box">
      <div class="alert-title">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        Lịch hẹn bị huỷ
      </div>

      <div class="info-row">
        <span class="info-label">👨‍⚕️ Bác sĩ</span>
        <span class="info-value">{{ $doctor->full_name }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">📅 Ngày hẹn</span>
        <span class="info-value">{{ $appointment->appointment_time->format('l, d/m/Y') }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">⏰ Giờ hẹn</span>
        <span class="info-value">
          {{ $appointment->appointment_time->format('H:i') }}
          – {{ $appointment->appointment_timeEnd->format('H:i') }}
        </span>
      </div>
      <div class="info-row">
        <span class="info-label">📋 Số thứ tự</span>
        <span class="info-value">{{ $appointment->queue_number }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">📝 Lý do nghỉ</span>
        <span class="info-value">
          {{ $typeLabel }}@if($reason) — {{ $reason }}@endif
        </span>
      </div>
    </div>

    <!-- Gợi ý lịch thay thế -->
    @if(count($alternatives) > 0)
      <div class="section-title">
        💡 Gợi ý lịch thay thế từ bác sĩ cùng khoa
      </div>

      @foreach($alternatives as $alt)
        <div class="alt-card">
          <div class="alt-avatar">👨‍⚕️</div>
          <div class="alt-info">
            <div class="alt-name">{{ $alt['doctor']->full_name }}</div>
            <div class="alt-dept">{{ $alt['doctor']->department->department_name ?? '' }}</div>
            <div class="alt-meta">
              <span class="badge">
                📅 {{ \Carbon\Carbon::parse($alt['schedule']->work_date)->format('d/m/Y') }}
              </span>
              <span class="badge">
                ⏰ {{ substr($alt['schedule']->start_time, 0, 5) }}–{{ substr($alt['schedule']->end_time, 0, 5) }}
              </span>
              <span class="badge badge-green">
                ✅ Còn {{ $alt['available_slots'] }} slot trống
              </span>
            </div>
          </div>
        </div>
      @endforeach
    @else
      <div class="no-alt">
        😔 Hiện tại chưa có lịch trống phù hợp của bác sĩ cùng khoa.<br>
        Vui lòng truy cập MediBook để chọn lịch khác.
      </div>
    @endif

    <!-- CTA -->
    <div class="cta-wrap">
      <a href="{{ $bookingUrl }}" class="cta-btn">
        📆 Đặt lịch mới ngay
      </a>
    </div>

    <!-- Note -->
    <div class="note">
      <strong>Lưu ý:</strong> Nếu bạn đã thanh toán trước, khoản phí sẽ được hoàn trả
      tự động trong <strong>3–5 ngày làm việc</strong>. Mọi thắc mắc vui lòng liên hệ
      hotline <strong>1800 xxxx</strong> hoặc reply email này.
    </div>
  </div>

  <!-- ── Footer ─────────────────────────────────────────────────── -->
  <div class="footer">
    Email này được gửi tự động từ hệ thống <strong>MediBook</strong>.<br>
    Vui lòng không reply trực tiếp — sử dụng tính năng liên hệ trên ứng dụng.<br>
    <a href="{{ config('app.url') }}/unsubscribe">Huỷ nhận thông báo</a>
    &nbsp;·&nbsp;
    <a href="{{ config('app.url') }}/privacy">Chính sách bảo mật</a>
  </div>

</div>
</body>
</html>
