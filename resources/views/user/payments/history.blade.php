{{-- resources/views/user/payments/history.blade.php --}}
@extends('layouts.user')

@section('title', 'Lịch sử thanh toán')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap');

    .hist-page {
        font-family: 'Be Vietnam Pro', sans-serif;
        background: #f0f4f8;
        min-height: 100vh;
        padding: 2rem 1rem;
    }

    .hist-container { max-width: 800px; margin: 0 auto; }

    .page-header {
        margin-bottom: 1.5rem;
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 .25rem;
    }

    .page-header p { font-size: .88rem; color: #64748b; margin: 0; }

    .payment-item {
        background: #fff;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.05);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: box-shadow .2s;
    }

    .payment-item:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }

    .pay-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .pay-main { flex: 1; min-width: 0; }

    .pay-doctor {
        font-size: .95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: .2rem;
    }

    .pay-date {
        font-size: .8rem;
        color: #94a3b8;
    }

    .pay-right { text-align: right; }

    .pay-amount {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: .25rem;
    }

    .pay-status {
        display: inline-block;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }

    .status-paid    { background: #dcfce7; color: #16a34a; }
    .status-pending { background: #fef9c3; color: #92400e; }
    .status-failed  { background: #fee2e2; color: #dc2626; }
    .status-refund  { background: #dbeafe; color: #1d4ed8; }

    .method-icons {
        'QR': '📱', 'ATM': '🏦', 'MoMo': '💜', 'ZaloPay': '💙', 'Counter': '🏥'
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #94a3b8;
    }

    .empty-state .big-icon { font-size: 3.5rem; margin-bottom: 1rem; }
    .empty-state p { font-size: .9rem; }
</style>
@endpush

@section('content')
<div class="hist-page">
    <div class="hist-container">

        <div class="page-header">
            <h1>💳 Lịch sử thanh toán</h1>
            <p>Tất cả giao dịch thanh toán lịch khám của bạn</p>
        </div>

        @if($payments->isEmpty())
            <div class="empty-state">
                <div class="big-icon">📭</div>
                <p>Bạn chưa có giao dịch thanh toán nào.</p>
                <a href="{{ route('user.appointments.index') }}"
                   style="color:#6366f1;font-weight:600;text-decoration:none">
                   Xem lịch hẹn →
                </a>
            </div>
        @else
            @php
            $methodIcons = [
                'QR' => '📱', 'ATM' => '🏦', 'MoMo' => '💜',
                'ZaloPay' => '💙', 'Counter' => '🏥',
            ];
            $methodColors = [
                'QR' => '#f0fdf4', 'ATM' => '#eff6ff', 'MoMo' => '#fdf4ff',
                'ZaloPay' => '#eff6ff', 'Counter' => '#fff7ed',
            ];
            @endphp

            @foreach($payments as $p)
            @php
            $statusClass = match($p->status) {
                'Đã thanh toán'   => 'status-paid',
                'Chờ thanh toán',
                'Chưa thanh toán' => 'status-pending',
                'Thất bại'        => 'status-failed',
                'Hoàn tiền'       => 'status-refund',
                default           => 'status-pending',
            };
            @endphp
            <div class="payment-item">
                <div class="pay-icon" style="background: {{ $methodColors[$p->method] ?? '#f1f5f9' }}">
                    {{ $methodIcons[$p->method] ?? '💳' }}
                </div>
                <div class="pay-main">
                    <div class="pay-doctor">
                        BS. {{ $p->appointment?->schedule?->doctor?->full_name ?? '—' }}
                    </div>
                    <div class="pay-date">
                        @if($p->appointment?->appointment_time)
                            Khám {{ \Carbon\Carbon::parse($p->appointment->appointment_time)->format('d/m/Y H:i') }}
                        @endif
                        · {{ $p->method }}
                        · <span style="font-family:monospace;font-size:.75rem">{{ $p->transaction_ref }}</span>
                    </div>
                </div>
                <div class="pay-right">
                    <div class="pay-amount">{{ number_format($p->total_amount, 0, ',', '.') }}đ</div>
                    <span class="pay-status {{ $statusClass }}">{{ $p->status }}</span>
                    @if($p->isPaid())
                    <div style="margin-top:.35rem">
                        <a href="{{ route('user.payments.success', $p->payment_id) }}"
                           style="font-size:.75rem;color:#6366f1;text-decoration:none;font-weight:600">
                           Biên lai →
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            <div style="margin-top:1.5rem">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
