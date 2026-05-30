{{-- resources/views/user/payments/qr.blade.php --}}
@extends('layouts.user')

@section('title', 'Quét mã QR thanh toán')

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
        background: linear-gradient(135deg, #0369a1, #075985);
    }

    .gw-logo {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        background: rgba(255,255,255,.2);
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

    .gw-body { padding: 2rem; text-align: center; }

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

    .qr-container {
        display: inline-block;
        padding: 1rem;
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        margin-bottom: 1.5rem;
    }

    .qr-image {
        width: 250px;
        height: 250px;
    }

    .instruction {
        font-size: .95rem;
        color: #475569;
        margin-bottom: 1.5rem;
    }

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
        background: #10b981;
    }

    .btn-pay:hover { transform: translateY(-1px); }

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
        text-align: left;
    }
</style>
@endpush

@section('content')
<div class="gw-page">
    <div class="gw-card">

        {{-- Header --}}
        <div class="gw-header">
            <div class="gw-logo">📱</div>
            <div class="gw-header-info">
                <h2>Quét mã QR (VietQR)</h2>
                <p>Bệnh viện Đặt Khám Online</p>
            </div>
        </div>

        <div class="gw-body">
            {{-- Demo/Real notice --}}
            @if($isRealMode)
                <div class="demo-notice" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; display: block; text-align: left;">
                    🚀 <span style="font-weight: 700;">Cổng thanh toán thực tế (ACB Bank) đã hoạt động!</span>
                    <p style="margin: .35rem 0 0 0; font-size: .8rem; opacity: .9; line-height: 1.4;">
                        Mã VietQR bên dưới liên kết trực tiếp với tài khoản **ACB** của bạn. Hãy mở app ngân hàng quét mã để thanh toán. Màn hình này sẽ tự động đóng và chuyển tiếp ngay khi ngân hàng nhận được tiền!
                    </p>
                </div>
            @else
                <div class="demo-notice">
                    ⚠️ <span>Đây là môi trường <strong>demo</strong>. Mã QR này dùng API để hiển thị. Nhấn "Giả lập đã quét QR thành công" để mô phỏng quét QR xong.</span>
                </div>
            @endif

            <div class="amount-display">
                <div class="amount-label">Số tiền cần thanh toán</div>
                <div class="amount-value">{{ number_format($totalAmount, 0, ',', '.') }}đ</div>
                <div class="amount-ref">Mã GD: {{ $payment->transaction_ref }}</div>
            </div>

            <div class="qr-container">
                {{-- Dùng API tạo QR ảnh để tránh lỗi QrCode not found --}}
                <img src="https://quickchart.io/qr?text={{ urlencode($qrContent) }}&size=300" alt="Mã QR thanh toán" class="qr-image">
            </div>

            <p class="instruction">
                Sử dụng App ngân hàng hoặc ví điện tử (MoMo, ZaloPay, Viettel Money...) hỗ trợ VietQR để quét mã.
            </p>

            @if($isRealMode)
                @if($checkoutUrl)
                    <div style="margin-bottom: 1rem;">
                        <a href="{{ $checkoutUrl }}" target="_blank" class="btn-pay" style="display: block; text-decoration: none; background: #2563eb; text-align: center; line-height: 1.5;">
                            ➡️ Mở trang thanh toán cổng PayOS
                        </a>
                    </div>
                @endif
            @else
                <form id="confirmForm" action="{{ route('user.payments.confirm', $payment->payment_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="ref" value="{{ $payment->transaction_ref }}">
                    <button type="submit" class="btn-pay">
                        ✓ Giả lập đã quét QR thành công
                    </button>
                </form>
            @endif

            <div style="text-align:center;margin-top:1.5rem">
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
    const confirmForm = document.getElementById('confirmForm');
    if (confirmForm) {
        confirmForm.addEventListener('submit', function (e) {
            const btn = this.querySelector('button');
            btn.disabled = true;
            btn.textContent = 'Đang xử lý...';
        });
    }

    // Tự động quét đối soát giao dịch thực tế từ tài khoản ACB Bank
    const paymentId = {{ $payment->payment_id }};
    const checkStatusUrl = "{{ route('user.payments.check', $payment->payment_id) }}";

    function startPolling() {
        const intervalId = setInterval(async () => {
            try {
                const response = await fetch(checkStatusUrl);
                if (response.ok) {
                    const data = await response.json();
                    if (data.is_paid && data.redirect_url) {
                        clearInterval(intervalId);
                        
                        // Cập nhật giao diện nút bấm thành công
                        const btn = document.querySelector('.btn-pay');
                        if (btn) {
                            btn.disabled = true;
                            btn.style.backgroundColor = '#10b981';
                            btn.textContent = '✓ Ngân hàng đã nhận tiền! Đang chuyển hướng...';
                        }
                        
                        // Chuyển hướng tự động sau 1.2 giây
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1200);
                    }
                }
            } catch (error) {
                console.error('Lỗi khi kiểm tra đối soát:', error);
            }
        }, 3000); // 3 giây kiểm tra biến động 1 lần
    }

    // Kích hoạt ngay khi trang tải xong
    startPolling();
</script>
@endpush
