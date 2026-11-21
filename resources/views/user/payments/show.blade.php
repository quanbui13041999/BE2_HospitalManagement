{{-- resources/views/user/payments/show.blade.php --}}
@extends('layouts.user')

@section('title', 'Thanh toán lịch khám')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap');

    * { box-sizing: border-box; }

    .pay-page {
        font-family: 'Be Vietnam Pro', sans-serif;
        background: #f0f4f8;
        min-height: 100vh;
        padding: 2rem 1rem;
    }

    .pay-container {
        max-width: 900px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
        align-items: start;
    }

    /* ---- Card chung ---- */
    .pay-card {
        background: #fff;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }

    .pay-card-title {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 1.25rem;
    }

    /* ---- Thông tin lịch hẹn ---- */
    .appt-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .doctor-avatar {
        width: 56px; height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
        color: #fff;
        font-weight: 700;
    }

    .appt-info h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 .25rem;
    }

    .appt-info p {
        font-size: .85rem;
        color: #64748b;
        margin: 0;
    }

    .appt-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .75rem;
        margin-bottom: 1.5rem;
    }

    .meta-item {
        background: #f8fafc;
        border-radius: 10px;
        padding: .75rem 1rem;
    }

    .meta-label {
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
        margin-bottom: .25rem;
    }

    .meta-value {
        font-size: .9rem;
        font-weight: 600;
        color: #1e293b;
    }

    /* ---- Bảng chi phí ---- */
    .cost-table { width: 100%; }

    .cost-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .6rem 0;
        font-size: .9rem;
        color: #475569;
        border-bottom: 1px dashed #e2e8f0;
    }

    .cost-row:last-child { border-bottom: none; }

    .cost-row.discount { color: #16a34a; }

    .cost-row.total {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        border-top: 2px solid #1e293b;
        border-bottom: none;
        padding-top: 1rem;
        margin-top: .25rem;
    }

    .badge-insurance {
        display: inline-block;
        background: #dcfce7;
        color: #16a34a;
        font-size: .68rem;
        font-weight: 600;
        padding: .15rem .5rem;
        border-radius: 999px;
        margin-left: .5rem;
    }

    /* ---- Phương thức thanh toán ---- */
    .method-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .75rem;
        margin-bottom: 1.5rem;
    }

    .method-option { display: none; }

    .method-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .4rem;
        padding: .9rem .5rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all .2s;
        text-align: center;
    }

    .method-label:hover {
        border-color: #6366f1;
        background: #f5f3ff;
    }

    .method-option:checked + .method-label {
        border-color: #6366f1;
        background: #eef2ff;
        box-shadow: 0 0 0 3px rgba(99,102,241,.15);
    }

    .method-icon {
        width: 36px; height: 36px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
    }

    .method-name {
        font-size: .78rem;
        font-weight: 600;
        color: #334155;
    }

    /* ---- Nút thanh toán ---- */
    .btn-pay {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        font-family: 'Be Vietnam Pro', sans-serif;
        cursor: pointer;
        transition: all .2s;
        letter-spacing: .02em;
    }

    .btn-pay:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(99,102,241,.4);
    }

    .btn-pay:active { transform: translateY(0); }

    /* ---- Đã thanh toán ---- */
    .paid-badge {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: #dcfce7;
        color: #16a34a;
        border: 1.5px solid #86efac;
        padding: .75rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: .9rem;
        margin-bottom: 1rem;
    }

    /* ---- Sidebar summary ---- */
    .summary-amount {
        font-size: 2.2rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -.03em;
    }

    .summary-label {
        font-size: .85rem;
        color: #64748b;
        margin-bottom: .5rem;
    }

    .divider {
        height: 1px;
        background: #f1f5f9;
        margin: 1.25rem 0;
    }

    .secure-badges {
        display: flex;
        flex-direction: column;
        gap: .5rem;
        margin-top: 1.25rem;
    }

    .secure-item {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .8rem;
        color: #64748b;
    }

    .secure-item .icon {
        width: 28px; height: 28px;
        background: #f1f5f9;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem;
        flex-shrink: 0;
    }

    /* ---- Breadcrumb ---- */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .83rem;
        color: #64748b;
        margin-bottom: 1.5rem;
    }

    .breadcrumb a { color: #6366f1; text-decoration: none; }
    .breadcrumb span { color: #cbd5e1; }

    @media (max-width: 720px) {
        .pay-container { grid-template-columns: 1fr; }
        .appt-meta { grid-template-columns: 1fr; }
        .method-grid { grid-template-columns: repeat(3, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="pay-page">
    <div class="pay-container">

        {{-- ======================================================
             CỘT TRÁI: thông tin + chọn phương thức
        ====================================================== --}}
        <div>
            {{-- Breadcrumb --}}
            <div class="breadcrumb">
                <a href="{{ route('appointments.index') }}">Lịch hẹn của tôi</a>
                <span>›</span>
                <span>Thanh toán</span>
            </div>

            {{-- Thông tin lịch hẹn --}}
            <div class="pay-card" style="margin-bottom:1.25rem">
                <div class="pay-card-title">🏥 Thông tin lịch khám</div>

                <div class="appt-header">
                    @if(!empty($appointment->schedule?->doctor?->full_name))
                        <div class="doctor-avatar">
                            {{ strtoupper(substr($appointment->schedule->doctor->full_name, 0, 1)) }}
                        </div>
                        <div class="appt-info">
                            <h2>BS. {{ $appointment->schedule->doctor->full_name }}</h2>
                            <p>{{ $appointment->schedule->doctor->department->department_name ?? '—' }}</p>
                        </div>
                    @else
                        <div class="doctor-avatar" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            DV
                        </div>
                        <div class="appt-info">
                            <h2>Dịch vụ y tế độc lập</h2>
                            <p>Không chỉ định bác sĩ khám</p>
                        </div>
                    @endif
                </div>

                <div class="appt-meta">
                    <div class="meta-item">
                        <div class="meta-label">📅 Ngày khám</div>
                        <div class="meta-value">
                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">🕐 Giờ khám</div>
                        <div class="meta-value">
                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">🔢 Số thứ tự</div>
                        <div class="meta-value">#{{ $appointment->queue_number }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">📋 Trạng thái</div>
                        <div class="meta-value">{{ $appointment->status }}</div>
                    </div>
                    @if($appointment->service)
                    <div class="meta-item" style="grid-column:1/-1">
                        <div class="meta-label">🩺 Dịch vụ</div>
                        <div class="meta-value">{{ $appointment->service->service_name }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Chi phí --}}
            <div class="pay-card" style="margin-bottom:1.25rem">
                <div class="pay-card-title">💳 Chi tiết chi phí</div>

                <div class="cost-table">
                    @if($doctorFee > 0)
                    <div class="cost-row">
                        <span>Phí khám bệnh</span>
                        <span>{{ number_format($doctorFee, 0, ',', '.') }}đ</span>
                    </div>
                    @endif

                    @if($serviceFee > 0)
                    <div class="cost-row">
                        <span>{{ $appointment->service->service_name ?? 'Dịch vụ' }}</span>
                        <span>{{ number_format($serviceFee, 0, ',', '.') }}đ</span>
                    </div>
                    @endif

                    <div class="cost-row">
                        <span>Tạm tính</span>
                        <span>{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                    </div>

                    @if($insuranceDiscount > 0)
                    <div class="cost-row discount">
                        <span>
                            Giảm giá BHYT
                            <span class="badge-insurance">{{ $insurance->discount_pct }}%</span>
                        </span>
                        <span>- {{ number_format($insuranceDiscount, 0, ',', '.') }}đ</span>
                    </div>
                    @endif

                    @if($membershipDiscount > 0)
                    <div class="cost-row discount">
                        <span>
                            Giảm giá thành viên
                            <span class="badge-insurance" style="background:#dbeafe;color:#2563eb">
                                {{ $membership->discount_pct }}%
                            </span>
                        </span>
                        <span>- {{ number_format($membershipDiscount, 0, ',', '.') }}đ</span>
                    </div>
                    @endif

                    <div class="cost-row total">
                        <span>Tổng thanh toán</span>
                        <span style="color:#6366f1">{{ number_format($totalAmount, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>

            {{-- Đã thanh toán / Form chọn PP --}}
            @if($existing && $existing->isPaid())
                <div class="pay-card">
                    <div class="paid-badge">
                        ✅ Lịch khám này đã được thanh toán thành công
                    </div>
                    <p style="font-size:.9rem;color:#64748b;margin:0">
                        Mã giao dịch: <strong>{{ $existing->transaction_ref }}</strong> —
                        {{ $existing->payment_date?->format('H:i d/m/Y') }}
                    </p>
                    <a href="{{ route('user.payments.success', $existing->payment_id) }}"
                       style="display:inline-block;margin-top:1rem;color:#6366f1;font-size:.9rem;font-weight:600">
                        Xem biên lai →
                    </a>
                </div>
            @else
                <div class="pay-card">
                    <div class="pay-card-title">💳 Chọn phương thức thanh toán</div>

                    <form action="{{ route('user.payments.store') }}" method="POST" id="payForm">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $appointment->appointment_id }}">

                        <div class="method-grid">
                            @php
                            $methods = [
                                'QR'      => ['icon' => '📱', 'label' => 'QR Code', 'color' => '#f0fdf4'],
                                'ATM'     => ['icon' => '🏦', 'label' => 'ATM / Internet', 'color' => '#eff6ff'],
                                'MoMo'    => ['icon' => '🟣', 'label' => 'MoMo', 'color' => '#fdf4ff'],
                                'ZaloPay' => ['icon' => '🔵', 'label' => 'ZaloPay', 'color' => '#eff6ff'],
                                'Counter' => ['icon' => '🏥', 'label' => 'Thu ngân', 'color' => '#fff7ed'],
                            ];
                            @endphp

                            @foreach($methods as $value => $m)
                            <div>
                                <input type="radio" name="method" id="method_{{ $value }}"
                                       value="{{ $value }}" class="method-option"
                                       {{ $loop->first ? 'checked' : '' }}>
                                <label for="method_{{ $value }}" class="method-label">
                                    <div class="method-icon" style="background:{{ $m['color'] }}">
                                        {{ $m['icon'] }}
                                    </div>
                                    <div class="method-name">{{ $m['label'] }}</div>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        @if($errors->any())
                            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:.75rem 1rem;font-size:.85rem;color:#dc2626;margin-bottom:1rem">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <button type="submit" class="btn-pay" id="btnPay">
                            Thanh toán {{ number_format($totalAmount, 0, ',', '.') }}đ →
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- ======================================================
             CỘT PHẢI: tóm tắt đơn
        ====================================================== --}}
        <div>
            <div class="pay-card" style="position:sticky;top:2rem">
                <div class="pay-card-title">🧾 Tóm tắt đơn thanh toán</div>

                <div class="summary-label">Tổng cần thanh toán</div>
                <div class="summary-amount">
                    {{ number_format($totalAmount, 0, ',', '.') }}<small style="font-size:1rem;font-weight:500">đ</small>
                </div>

                <div class="divider"></div>

                <div style="font-size:.85rem;color:#475569;line-height:1.7">
                    @if(!empty($appointment->schedule?->doctor?->full_name))
                        <div>🏥 <strong>BS. {{ $appointment->schedule->doctor->full_name }}</strong></div>
                    @else
                        <div>🏥 <strong>Dịch vụ y tế độc lập</strong></div>
                    @endif
                    <div>📅 {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('d/m/Y H:i') }}</div>
                    <div>🔢 STT #{{ $appointment->queue_number }}</div>
                </div>

                @if($insurance)
                <div class="divider"></div>
                <div style="background:#f0fdf4;border-radius:8px;padding:.75rem;font-size:.82rem;color:#15803d">
                    🛡️ BHYT đang áp dụng — giảm {{ $insurance->discount_pct }}%
                </div>
                @endif

                @if($membership)
                <div style="background:#eff6ff;border-radius:8px;padding:.75rem;font-size:.82rem;color:#1d4ed8;margin-top:.5rem">
                    ⭐ Thành viên {{ $membership->tier }} — giảm {{ $membership->discount_pct }}%
                </div>
                @endif

                <div class="divider"></div>

                <div class="secure-badges">
                    <div class="secure-item">
                        <div class="icon">🔒</div>
                        <span>Giao dịch được mã hóa SSL 256-bit</span>
                    </div>
                    <div class="secure-item">
                        <div class="icon">✅</div>
                        <span>Xác nhận tức thì sau thanh toán</span>
                    </div>
                    <div class="secure-item">
                        <div class="icon">📋</div>
                        <span>Biên lai điện tử gửi qua email</span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /pay-container --}}
</div>
@endsection

@push('scripts')
<script>
    // Disable nút sau khi submit để tránh double-click
    document.getElementById('payForm')?.addEventListener('submit', function () {
        const btn = document.getElementById('btnPay');
        btn.disabled = true;
        btn.textContent = 'Đang xử lý...';
    });
</script>
@endpush
