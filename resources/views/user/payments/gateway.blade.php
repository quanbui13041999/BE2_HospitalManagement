{{-- resources/views/user/payments/gateway.blade.php --}}
@extends('layouts.user')

@section('title', 'Cổng thanh toán')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap');

    .gw-page {
        font-family: 'Be Vietnam Pro', sans-serif;
        background: #f0f4f8;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .gw-card {
        background: #fff;
        border-radius: 20px;
        max-width: 480px;
        width: 100%;
        overflow: hidden;
        box-shadow: 0 16px 48px rgba(0,0,0,.1);
    }

    .gw-header {
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .gw-logo {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
    }

    .gw-header-info h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #fff;
        margin: 0 0 .2rem;
    }

    .gw-header-info p {
        font-size: .82rem;
        color: rgba(255,255,255,.75);
        margin: 0;
    }

    .gw-body { padding: 2rem; }

    .amount-display {
        text-align: center;
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: 12px;
        margin-bottom: 1.75rem;
    }

    .amount-label { font-size: .8rem; color: #94a3b8; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
    .amount-value { font-size: 2.2rem; font-weight: 800; color: #1e293b; margin: .25rem 0; letter-spacing: -.03em; }
    .amount-ref { font-size: .78rem; color: #94a3b8; }

    .form-group { margin-bottom: 1.25rem; }

    .form-label {
        display: block;
        font-size: .82rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: .4rem;
    }

    .form-input {
        width: 100%;
        padding: .75rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: .95rem;
        color: #1e293b;
        outline: none;
        transition: border-color .2s;
    }

    .form-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }

    .card-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

    .btn-pay {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 12px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        cursor: pointer;
        transition: all .2s;
        margin-top: .25rem;
    }

    .btn-pay:hover { transform: translateY(-1px); }

    .secure-note {
        text-align: center;
        font-size: .78rem;
        color: #94a3b8;
        margin-top: 1rem;
    }

    .demo-notice {
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        border-radius: 10px;
        padding: .75rem 1rem;
        font-size: .82rem;
        color: #92400e;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: .5rem;
    }
</style>
@endpush

@section('content')
<div class="gw-page">
    <div class="gw-card">

        @php
        $configs = [
            'ATM'     => ['icon' => '🏦', 'name' => 'Ngân hàng / ATM', 'color1' => '#1d4ed8', 'color2' => '#1e40af', 'btnColor' => '#1d4ed8'],
            'MoMo'    => ['icon' => '💜', 'name' => 'Ví MoMo',          'color1' => '#9d174d', 'color2' => '#831843', 'btnColor' => '#ae2d68'],
            'ZaloPay' => ['icon' => '💙', 'name' => 'ZaloPay',          'color1' => '#0369a1', 'color2' => '#075985', 'btnColor' => '#0ea5e9'],
        ];
        $cfg = $configs[$method] ?? $configs['ATM'];
        @endphp

        {{-- Header --}}
        <div class="gw-header" style="background: linear-gradient(135deg, {{ $cfg['color1'] }}, {{ $cfg['color2'] }})">
            <div class="gw-logo" style="background:rgba(255,255,255,.2)">{{ $cfg['icon'] }}</div>
            <div class="gw-header-info">
                <h2>{{ $cfg['name'] }}</h2>
                <p>Bệnh viện Đặt Khám Online</p>
            </div>
        </div>

        <div class="gw-body">
            {{-- Demo notice --}}
            <div class="demo-notice">
                ⚠️ <span>Đây là môi trường <strong>demo</strong>. Nhấn "Xác nhận thanh toán" để mô phỏng giao dịch thành công.</span>
            </div>

            <div class="amount-display">
                <div class="amount-label">Số tiền thanh toán</div>
                <div class="amount-value">{{ number_format($totalAmount, 0, ',', '.') }}đ</div>
                <div class="amount-ref">Mã GD: {{ $ref }}</div>
            </div>

            @if($method === 'ATM')
            {{-- Form ATM --}}
            <div class="form-group">
                <label class="form-label">Số thẻ / tài khoản</label>
                <input type="text" class="form-input" placeholder="0000 0000 0000 0000" maxlength="19"
                       id="cardNum" oninput="formatCard(this)">
            </div>
            <div class="card-row">
                <div class="form-group">
                    <label class="form-label">Ngày hết hạn</label>
                    <input type="text" class="form-input" placeholder="MM/YY" maxlength="5">
                </div>
                <div class="form-group">
                    <label class="form-label">Mã OTP</label>
                    <input type="text" class="form-input" placeholder="6 số" maxlength="6">
                </div>
            </div>
            @elseif($method === 'MoMo')
            <div class="form-group">
                <label class="form-label">Số điện thoại MoMo</label>
                <input type="tel" class="form-input" placeholder="09x xxx xxxx" maxlength="11">
            </div>
            <div class="form-group">
                <label class="form-label">Mã OTP</label>
                <input type="text" class="form-input" placeholder="Mã OTP gửi về điện thoại">
            </div>
            @elseif($method === 'ZaloPay')
            <div class="form-group">
                <label class="form-label">Số điện thoại ZaloPay</label>
                <input type="tel" class="form-input" placeholder="09x xxx xxxx" maxlength="11">
            </div>
            <div class="form-group">
                <label class="form-label">Mã PIN ZaloPay</label>
                <input type="password" class="form-input" placeholder="6 chữ số" maxlength="6">
            </div>
            @endif

            <form id="confirmForm" action="{{ route('user.payments.confirm', $payment->payment_id) }}" method="POST">
                @csrf
                <input type="hidden" name="ref" value="{{ $ref }}">
                <button type="submit" class="btn-pay" style="background:{{ $cfg['btnColor'] }}">
                    Xác nhận thanh toán {{ number_format($totalAmount, 0, ',', '.') }}đ
                </button>
            </form>

            <div class="secure-note">🔒 Được bảo mật bởi SSL 256-bit</div>

            <div style="text-align:center;margin-top:1rem">
                <a href="{{ route('user.payments.fail', $payment->payment_id) }}"
                   style="font-size:.82rem;color:#94a3b8;text-decoration:none"
                   onclick="return confirm('Hủy giao dịch này?')">
                   Hủy giao dịch
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function formatCard(input) {
        let v = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let m = v.match(/.{1,4}/g);
        input.value = m ? m.join(' ') : '';
    }

    document.getElementById('confirmForm').addEventListener('submit', function (e) {
        const btn = this.querySelector('button');
        btn.disabled = true;
        btn.textContent = 'Đang xử lý...';
    });
</script>
@endpush
