<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thẻ Thành Viên - 4PM CLINIC</title>
    <link rel="stylesheet" href="{{ asset('css/membershipcards.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">
        @if($user)
        <div class="top-section">
            <div class="membership-card">
                <div class="card-header">
                    <span>4PM CLINIC</span>
                    <i class="fas fa-star {{ ($membership->tier == 'Vàng') ? 'yellow-star' : '' }}"></i>
                </div>
                <div class="card-title">Thẻ {{ $membership->tier }}</div>
                <div class="card-number">{{ $membership->card_number }}</div>
                <div class="card-user">
                    <strong>{{ $user->full_name ?? $user->name }}</strong><br>
                    <span>Thành viên từ {{ $user->created_at->format('m/Y') }}</span>
                </div>
                <div class="card-stats">
                    <div class="stat-item">
                        <small>ĐIỂM TÍCH LŨY</small>
                       <div class="stat-value">{{ number_format($membership->points ?? 0) }} đ</div>
                    </div>
                    <div class="stat-item text-right">
                        <small>TỔNG CHI TIÊU</small>
                    <p>{{ number_format(($membership->points ?? 0) / 1000000, 1) }}M đ</p>
                    </div>
                </div>
            </div>

            <div class="upgrade-progress">
                <h3><i class="fas fa-medal blue-icon"></i> TIẾN TRÌNH THĂNG HẠNG</h3>
                <div class="rank-steps">
                    <div class="step {{ $membership->tier == 'Đồng' ? 'active' : '' }}">
                        <i class="fas fa-medal bronze"></i><span>Đồng</span>
                    </div>
                    <div class="step {{ $membership->tier == 'Bạc' ? 'active' : '' }}">
                        <i class="fas fa-medal silver"></i><span>Bạc</span>
                    </div>
                    <div class="step {{ $membership->tier == 'Vàng' ? 'active' : '' }}">
                        <i class="fas fa-star gold"></i><span>Vàng</span>
                    </div>
                    <div class="step {{ $membership->tier == 'Kim Cương' ? 'active' : '' }}">
                        <i class="fas fa-gem diamond"></i><span>K.Cương</span>
                    </div>
                </div>

                <div class="progress-bar-container">
                 <div class="progress-bar" style="width: {{ $progressPercent }}%;"></div>
                </div>
                <p class="progress-text">Còn thiếu <strong>{{ number_format($remaining) }} đ</strong> để đạt hạng Kim Cương</p>

                <div class="extra-stats-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px;">
                    <div class="extra-item" style="background: #f8f9fa; padding: 10px; border-radius: 8px;">
                        <i class="fas fa-calendar-check" style="color: #4a90e2;"></i> Lần khám: <strong>{{ $extraData['visit_count'] }} lần</strong>
                    </div>
                    <div class="extra-item" style="background: #f8f9fa; padding: 10px; border-radius: 8px;">
                        <i class="fas fa-hourglass-start" style="color: #f39c12;"></i> Chờ duyệt: <strong>{{ number_format($extraData['pending_points']) }} đ</strong>
                    </div>
                    <div class="extra-item" style="background: #f8f9fa; padding: 10px; border-radius: 8px;">
                        <i class="fas fa-tags" style="color: #e74c3c;"></i> Voucher còn: <strong>{{ $extraData['voucher_count'] }} mã</strong>
                    </div>
                    <div class="extra-item" style="background: #f8f9fa; padding: 10px; border-radius: 8px;">
                        <i class="fas fa-hand-holding-usd" style="color: #2ecc71;"></i> Tiết kiệm: <strong>{{ $extraData['saved_money'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</body>

</html>