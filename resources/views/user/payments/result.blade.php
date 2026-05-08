{{-- resources/views/user/payments/result.blade.php --}}
@extends('layouts.user')

@section('title', $success ? 'Thanh toán thành công' : 'Thanh toán thất bại')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap');

    .result-page {
        font-family: 'Be Vietnam Pro', sans-serif;
        background: #f0f4f8;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .result-card {
        background: #fff;
        border-radius: 24px;
        padding: 3rem 2.5rem;
        max-width: 500px;
        width: 100%;
        text-align: center;
        box-shadow: 0 16px 48px rgba(0, 0, 0, .1);
    }

    .result-icon {
        font-size: 4.5rem;
        margin-bottom: 1rem;
        display: block;
        animation: bounceIn .5s cubic-bezier(.34, 1.56, .64, 1);
    }

    @keyframes bounceIn {
        from {
            transform: scale(0);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .result-title {
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: .5rem;
    }

    .result-sub {
        font-size: .9rem;
        color: #64748b;
        margin-bottom: 2rem;
    }

    /* Receipt */
    .receipt {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: left;
        margin-bottom: 2rem;
        position: relative;
    }

    .receipt::before {
        content: 'BIÊN LAI';
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: #6366f1;
        color: #fff;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .12em;
        padding: .2rem .75rem;
        border-radius: 999px;
    }

    .receipt-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .55rem 0;
        font-size: .88rem;
        border-bottom: 1px dashed #e2e8f0;
    }

    .receipt-row:last-child {
        border-bottom: none;
    }

    .receipt-label {
        color: #64748b;
    }

    .receipt-value {
        font-weight: 600;
        color: #1e293b;
        text-align: right;
    }

    .receipt-total {
        font-size: 1.1rem;
        font-weight: 800;
        color: #6366f1 !important;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .75rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
    }

    .chip-success {
        background: #dcfce7;
        color: #16a34a;
    }

    .chip-failed {
        background: #fee2e2;
        color: #dc2626;
    }

    /* Action buttons */
    .action-btns {
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    .btn-primary {
        display: block;
        padding: .9rem;
        border-radius: 12px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: .95rem;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #fff;
        transition: all .2s;
        border: none;
        cursor: pointer;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, .35);
    }

    .btn-outline {
        display: block;
        padding: .9rem;
        border-radius: 12px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: .95rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        border: 1.5px solid #e2e8f0;
        color: #475569;
        transition: all .2s;
    }

    .btn-outline:hover {
        border-color: #6366f1;
        color: #6366f1;
    }

    /* Print button */
    .btn-print {
        background: none;
        border: none;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: .82rem;
        color: #94a3b8;
        cursor: pointer;
        margin-top: .5rem;
        text-decoration: underline;
    }

    /* Confetti (success) */
    .confetti-wrap {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .confetti-piece {
        position: absolute;
        width: 8px;
        height: 8px;
        border-radius: 2px;
        animation: confettiFall linear forwards;
    }

    @keyframes confettiFall {
        0% {
            transform: translateY(-20px) rotate(0deg);
            opacity: 1;
        }

        100% {
            transform: translateY(100vh) rotate(720deg);
            opacity: 0;
        }
    }

    @media print {

        .action-btns,
        .confetti-wrap {
            display: none !important;
        }

        .result-page {
            background: white;
        }

        .result-card {
            box-shadow: none;
            padding: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="result-page">

    @if($success)
    <div class="confetti-wrap" id="confettiWrap"></div>
    @endif

    <div class="result-card" style="position:relative;z-index:1">
        @if($success)
        <span class="result-icon">🎉</span>
        <div class="result-title" style="color:#16a34a">Thanh toán thành công!</div>
        <div class="result-sub">Cảm ơn bạn. Lịch khám đã được xác nhận.</div>
        @else
        <span class="result-icon">😞</span>
        <div class="result-title" style="color:#dc2626">Thanh toán thất bại</div>
        <div class="result-sub">Giao dịch không thành công. Vui lòng thử lại.</div>
        @endif

        {{-- Biên lai --}}
        <div class="receipt" id="receiptBlock">
            <div class="receipt-row">
                <span class="receipt-label">Mã giao dịch</span>
                <span class="receipt-value" style="font-family:monospace;font-size:.82rem">
                    {{ $payment->transaction_ref }}
                </span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Thời gian</span>
                <span class="receipt-value">
                    {{ $payment->payment_date?->format('H:i - d/m/Y') ?? now()->format('H:i - d/m/Y') }}
                </span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Bác sĩ</span>
                <span class="receipt-value">
                    BS. {{ $payment->appointment?->schedule?->doctor?->full_name ?? '—' }}
                </span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Ngày khám</span>
                <span class="receipt-value">
                    @if($payment->appointment?->appointment_time)
                    {{ \Carbon\Carbon::parse($payment->appointment->appointment_time)->format('d/m/Y H:i') }}
                    @else —
                    @endif
                </span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Phương thức</span>
                <span class="receipt-value">{{ $payment->method }}</span>
            </div>
            @if($payment->discount_amount > 0)
            <div class="receipt-row">
                <span class="receipt-label">Giảm giá</span>
                <span class="receipt-value" style="color:#16a34a">
                    - {{ number_format($payment->discount_amount, 0, ',', '.') }}đ
                </span>
            </div>
            @endif
            <div class="receipt-row">
                <span class="receipt-label">Trạng thái</span>
                <span class="receipt-value">
                    <span class="status-chip {{ $success ? 'chip-success' : 'chip-failed' }}">
                        {{ $success ? '✅ Thành công' : '❌ Thất bại' }}
                    </span>
                </span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Tổng thanh toán</span>
                <span class="receipt-value receipt-total">
                    {{ number_format($payment->total_amount, 0, ',', '.') }}đ
                </span>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="action-btns">
            @if($success)
            <a href="/lich-hen" class="btn-primary"> Xem lịch hẹn của tôi →
            </a>
            <a href="{{ route('user.payments.history') }}" class="btn-outline">
                Lịch sử thanh toán
            </a>
            <button class="btn-print" onclick="window.print()">🖨️ In biên lai</button>
            @else
            <a href="{{ route('user.payments.show', $payment->appointment_id) }}" class="btn-primary">
                Thử thanh toán lại →
            </a>
            <a href="/lich-hen" class="btn-outline"> </a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($success)
<script>
    // Confetti animation
    const colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6'];
    const wrap = document.getElementById('confettiWrap');

    for (let i = 0; i < 60; i++) {
        const p = document.createElement('div');
        p.className = 'confetti-piece';
        p.style.cssText = `
            left: ${Math.random() * 100}%;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            width: ${Math.random() * 10 + 6}px;
            height: ${Math.random() * 10 + 6}px;
            border-radius: ${Math.random() > .5 ? '50%' : '2px'};
            animation-duration: ${Math.random() * 3 + 2}s;
            animation-delay: ${Math.random() * 1.5}s;
        `;
        wrap.appendChild(p);
    }

    // Xóa confetti sau 5 giây
    setTimeout(() => {
        if (wrap) wrap.remove();
    }, 6000);
</script>
@endif
@endpush