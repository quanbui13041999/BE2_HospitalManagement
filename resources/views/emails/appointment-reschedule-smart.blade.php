<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lịch hẹn bị thay đổi - Chọn lịch mới</title>
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
      max-width: 640px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(0,0,0,.08);
    }

    /* Header */
    .header {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
      padding: 32px;
      text-align: center;
    }
    .header-title {
      color: #fff;
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .header-sub {
      color: rgba(255,255,255,.9);
      font-size: 15px;
      line-height: 1.5;
    }

    /* Body */
    .body { padding: 32px; }

    /* Alert box */
    .alert-box {
      background: #fff7ed;
      border: 1.5px solid #fed7aa;
      border-radius: 12px;
      padding: 16px 20px;
      margin-bottom: 28px;
    }
    .alert-title {
      color: #c2410c;
      font-weight: 700;
      font-size: 15px;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .info-table {
      width: 100%;
      font-size: 13px;
    }
    .info-row {
      display: flex;
      padding: 8px 0;
      border-bottom: 1px solid #fee2c8;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label {
      color: #92400e;
      font-weight: 600;
      width: 140px;
      flex-shrink: 0;
    }
    .info-value { color: #1c1917; }

    /* Doctor cards with scoring */
    .section-title {
      font-size: 16px;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .section-desc {
      font-size: 13px;
      color: #6b7280;
      margin-bottom: 16px;
    }

    .doctor-card {
      border: 1.5px solid #e5e7eb;
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 12px;
      background: #f9fafb;
      transition: all 0.2s;
    }
    .doctor-card:hover {
      border-color: #2563eb;
      background: #f0f9ff;
      box-shadow: 0 2px 12px rgba(37, 99, 235, 0.08);
    }

    /* Doctor header */
    .doctor-header {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      margin-bottom: 14px;
    }
    .doctor-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, #dbeafe, #bfdbfe);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      flex-shrink: 0;
    }
    .doctor-info { flex: 1; }
    .doctor-name {
      font-weight: 700;
      font-size: 15px;
      color: #111827;
      margin-bottom: 2px;
    }
    .doctor-dept {
      font-size: 12px;
      color: #6b7280;
      margin-bottom: 4px;
    }
    .doctor-exp {
      font-size: 11px;
      color: #9ca3af;
    }

    /* Score badge */
    .score-badge {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #fff;
      padding: 8px 14px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 18px;
      text-align: center;
      min-width: 80px;
      flex-shrink: 0;
    }

    /* Schedule info */
    .schedule-info {
      background: #f3f4f6;
      border-radius: 8px;
      padding: 12px;
      margin-bottom: 12px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      font-size: 13px;
    }
    .schedule-item {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .schedule-label {
      color: #6b7280;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .schedule-value {
      color: #111827;
      font-weight: 600;
    }

    /* Score breakdown */
    .score-breakdown {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      font-size: 12px;
      background: #fff;
      border-radius: 8px;
      padding: 12px;
      border: 1px solid #e5e7eb;
    }
    .breakdown-item {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .breakdown-label {
      color: #6b7280;
      font-size: 11px;
      font-weight: 600;
    }
    .breakdown-value {
      color: #111827;
      font-weight: 600;
    }
    .breakdown-bar {
      background: #e5e7eb;
      height: 4px;
      border-radius: 2px;
      overflow: hidden;
      margin-top: 2px;
    }
    .breakdown-bar-fill {
      background: #2563eb;
      height: 100%;
      border-radius: 2px;
    }

    /* CTA buttons */
    .cta-section {
      margin-top: 24px;
      padding-top: 24px;
      border-top: 1px solid #e5e7eb;
    }
    .cta-title {
      font-size: 14px;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 12px;
    }
    .btn-group {
      display: flex;
      gap: 10px;
      margin-bottom: 12px;
    }
    .btn {
      display: inline-block;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 13px;
      text-align: center;
      border: none;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-primary {
      background: #2563eb;
      color: #fff;
      flex: 1;
    }
    .btn-primary:hover {
      background: #1d4ed8;
    }
    .btn-secondary {
      background: #e5e7eb;
      color: #374151;
      flex: 1;
    }
    .btn-secondary:hover {
      background: #d1d5db;
    }

    /* No alternatives */
    .no-alt {
      background: #f0fdf4;
      border: 1.5px solid #86efac;
      border-radius: 10px;
      padding: 14px 16px;
      font-size: 13px;
      color: #166534;
      text-align: center;
    }

    /* Important note */
    .note-box {
      background: #eff6ff;
      border-left: 3px solid #2563eb;
      border-radius: 0 8px 8px 0;
      padding: 12px 16px;
      font-size: 12px;
      color: #1e40af;
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
    .footer a { color: #6b7280; text-decoration: none; }
    .footer a:hover { text-decoration: underline; }

    /* Responsive */
    @media (max-width: 600px) {
      .schedule-info { grid-template-columns: 1fr; }
      .score-breakdown { grid-template-columns: 1fr; }
      .btn-group { flex-direction: column; }
      .btn { width: 100%; }
    }
  </style>
</head>
<body>
<div class="wrapper">

  <!-- ── Header ─────────────────────────────────────────────────── -->
  <div class="header">
    <div class="header-title">⚠️ Lịch hẹn của bạn đã thay đổi</div>
    <div class="header-sub">Bác sĩ {{ $doctor->full_name }} có lịch nghỉ đột xuất<br>Vui lòng chọn lịch mới phù hợp</div>
  </div>

  <!-- ── Body ──────────────────────────────────────────────────── -->
  <div class="body">

    <p style="font-size:14px; color:#374151; margin-bottom:20px;">
      Kính gửi <strong>{{ $patient->full_name ?? $patient->name ?? 'Quý khách' }}</strong>,<br><br>
      Chúng tôi xin thông báo lịch hẹn của bạn bên dưới đã bị <strong style="color:#dc2626;">huỷ</strong>
      do bác sĩ phụ trách có lịch nghỉ đột xuất. Chúng tôi đã tìm kiếm và xếp hạng những bác sĩ tuyệt vời cùng khoa để gợi ý cho bạn.
    </p>

    <!-- Lịch bị huỷ -->
    <div class="alert-box">
      <div class="alert-title">
        ❌ Lịch hẹn bị huỷ
      </div>
      <div class="info-table">
        <div class="info-row">
          <span class="info-label">👨‍⚕️ Bác sĩ</span>
          <span class="info-value"><strong>{{ $doctor->full_name }}</strong></span>
        </div>
        <div class="info-row">
          <span class="info-label">🏥 Chuyên khoa</span>
          <span class="info-value">{{ $doctor->department->department_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">📅 Ngày hẹn</span>
          <span class="info-value">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('l, d/m/Y') }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">⏰ Giờ hẹn</span>
          <span class="info-value">
            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
            – {{ \Carbon\Carbon::parse($appointment->appointment_time)->addMinutes(30)->format('H:i') }}
          </span>
        </div>
        <div class="info-row">
          <span class="info-label">📝 Lý do</span>
          <span class="info-value">{{ $typeLabel }}{{ $reason ? ' — ' . $reason : '' }}</span>
        </div>
      </div>
    </div>

    <!-- Gợi ý bác sĩ -->
    @if(count($alternatives) > 0)
      <div style="margin-top: 28px;">
        <div class="section-title">
          ⭐ Bác sĩ gợi ý (xếp hạng theo chất lượng)
        </div>
        <div class="section-desc">
          Những bác sĩ dưới đây cùng chuyên khoa với bác sĩ bạn định khám, được xếp hạng dựa trên:
          đánh giá, kinh nghiệm, số lượng lịch trống và phản hồi từ bệnh nhân.
        </div>

        @foreach($alternatives as $index => $alt)
          <div class="doctor-card">
            <!-- Doctor header with score -->
            <div class="doctor-header">
              <div class="doctor-avatar">👨‍⚕️</div>
              <div class="doctor-info">
                <div class="doctor-name">{{ $alt['doctor']->full_name }}</div>
                <div class="doctor-dept">{{ $alt['doctor']->department->department_name ?? 'N/A' }}</div>
                <div class="doctor-exp">
                  {{ $alt['doctor']->experience ?? 0 }} năm kinh nghiệm
                  @if(($alt['doctor']->total_reviews ?? 0) > 0)
                    • {{ $alt['doctor']->total_reviews }} đánh giá
                  @endif
                </div>
              </div>
              <div class="score-badge">
                {{ number_format($alt['score'], 1) }}/100
              </div>
            </div>

            <!-- Schedule information -->
            <div class="schedule-info">
              <div class="schedule-item">
                <span class="schedule-label">📅 Ngày khám</span>
                <span class="schedule-value">{{ \Carbon\Carbon::parse($alt['schedule']->work_date)->format('d/m/Y') }}</span>
              </div>
              <div class="schedule-item">
                <span class="schedule-label">⏰ Giờ khám</span>
                <span class="schedule-value">{{ substr($alt['schedule']->start_time, 0, 5) }}–{{ substr($alt['schedule']->end_time, 0, 5) }}</span>
              </div>
              <div class="schedule-item">
                <span class="schedule-label">💺 Slot trống</span>
                <span class="schedule-value">{{ $alt['available_slots'] }} chỗ trống</span>
              </div>
              <div class="schedule-item">
                <span class="schedule-label">⭐ Đánh giá</span>
                <span class="schedule-value">{{ number_format($alt['doctor']->avg_rating ?? 0, 1) }}/5.0</span>
              </div>
            </div>

            <!-- Score breakdown -->
            @if(isset($alt['score_breakdown']))
              <div class="score-breakdown">
                <div class="breakdown-item">
                  <span class="breakdown-label">
                    {{ $alt['score_breakdown']['available_slots']['label'] }}
                    (40%)
                  </span>
                  <span class="breakdown-value">
                    {{ $alt['score_breakdown']['available_slots']['value'] }}
                    ({{ $alt['score_breakdown']['available_slots']['ratio'] }}% đủ)
                  </span>
                  <div class="breakdown-bar">
                    <div class="breakdown-bar-fill" style="width: {{ $alt['score_breakdown']['available_slots']['ratio'] }}%"></div>
                  </div>
                </div>

                <div class="breakdown-item">
                  <span class="breakdown-label">
                    {{ $alt['score_breakdown']['rating']['label'] }}
                    (35%)
                  </span>
                  <span class="breakdown-value">
                    {{ $alt['score_breakdown']['rating']['value'] }}/{{ $alt['score_breakdown']['rating']['max'] }} ⭐
                  </span>
                  <div class="breakdown-bar">
                    <div class="breakdown-bar-fill" style="width: {{ ($alt['score_breakdown']['rating']['value'] / 5) * 100 }}%"></div>
                  </div>
                </div>

                <div class="breakdown-item">
                  <span class="breakdown-label">
                    {{ $alt['score_breakdown']['experience']['label'] }}
                    (15%)
                  </span>
                  <span class="breakdown-value">
                    {{ $alt['score_breakdown']['experience']['value'] }}
                  </span>
                  <div class="breakdown-bar">
                    <div class="breakdown-bar-fill" style="width: {{ min(($alt['doctor']->experience ?? 0) / 20 * 100, 100) }}%"></div>
                  </div>
                </div>

                <div class="breakdown-item">
                  <span class="breakdown-label">
                    {{ $alt['score_breakdown']['reviews']['label'] }}
                    (10%)
                  </span>
                  <span class="breakdown-value">
                    {{ $alt['score_breakdown']['reviews']['value'] }} reviews
                  </span>
                  <div class="breakdown-bar">
                    <div class="breakdown-bar-fill" style="width: {{ min(($alt['doctor']->total_reviews ?? 0) / 50 * 100, 100) }}%"></div>
                  </div>
                </div>
              </div>
            @endif

            <!-- CTA for this doctor -->
            <div class="cta-section" style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #e5e7eb;">
              <div class="btn-group">
                <a href="{{ route('appointments.reschedule-confirm', [
                    'old_id' => $appointment->appointment_id,
                    'new_schedule_id' => $alt['schedule']->schedule_id,
                    'token' => hash_hmac('sha256', $appointment->appointment_id . '|' . $alt['schedule']->schedule_id, config('app.key'))
                ]) }}" class="btn btn-primary" style="display: inline-block;">
                  ✅ Xác nhận chọn lịch này
                </a>
              </div>
            </div>
          </div>
        @endforeach
    @else
      <div class="no-alt" style="margin-top: 28px;">
        😔 Hiện tại chưa có lịch trống phù hợp của bác sĩ cùng khoa.<br>
        <a href="{{ route('appointments.create') }}" style="color: #166534; font-weight: 600; text-decoration: none;">
          Đặt lịch mới →
        </a>
      </div>
    @endif

    <!-- Important note -->
    <div class="note-box">
      <strong>💡 Lưu ý quan trọng:</strong><br>
      • Nếu bạn đã thanh toán trước, khoản phí sẽ được <strong>hoàn trả tự động</strong> trong 3–5 ngày làm việc<br>
      • Để đặt lịch thành công, vui lòng chọn bác sĩ gợi ý ở trên<br>
      • Nếu không tìm được lịch phù hợp, hãy liên hệ hotline {{ config('app.phone') ?? '1800 xxxx' }}
    </div>
  </div>

  <!-- ── Footer ─────────────────────────────────────────────────── -->
  <div class="footer">
    Email này được gửi tự động từ hệ thống <strong>{{ config('app.name') }}</strong>.<br>
    Vui lòng không reply trực tiếp — sử dụng tính năng liên hệ trên ứng dụng.<br>
    <a href="{{ config('app.url') }}/unsubscribe">Huỷ nhận thông báo</a>
    &nbsp;·&nbsp;
    <a href="{{ config('app.url') }}/privacy">Chính sách bảo mật</a>
  </div>

</div>
</body>
</html>
