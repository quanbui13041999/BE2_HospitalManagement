@extends('layouts.admin')

@section('title', 'Quét mã QR thanh toán')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h5 class="mb-0"><i class="bi bi-qr-code"></i> Quét mã QR để thanh toán</h5>
                </div>
                <div class="card-body text-center">
                    <!-- QR Code -->
                    <div class="qr-container mb-4">
                        {!! QrCode::size(300)->generate(session('qr_content', 'Thanh toan hoa don')) !!}
                    </div>

                    <!-- Thông tin -->
                    <div class="alert alert-info">
                        <p class="mb-1"><i class="bi bi-clock"></i> Mã QR có hiệu lực trong <strong>15 phút</strong></p>
                        <p class="mb-0"><i class="bi bi-phone"></i> Sử dụng ứng dụng ngân hàng hoặc ví điện tử để quét mã</p>
                    </div>

                    <div class="card bg-light mt-3">
                        <div class="card-body">
                            <h6>Mã tham chiếu: <code class="fw-bold">{{ session('ref', 'N/A') }}</code></h6>
                            <p class="text-muted small mb-0">Vui lòng giữ mã này để đối chiếu khi cần</p>
                        </div>
                    </div>

                    <!-- Polling status -->
                    <div class="mt-4" id="paymentStatus">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang chờ thanh toán...</span>
                        </div>
                        <p class="mt-2">Đang chờ xác nhận thanh toán...</p>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                        <button class="btn btn-warning" onclick="checkStatus()">
                            <i class="bi bi-arrow-repeat"></i> Kiểm tra trạng thái
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let paymentId = {{ $paymentId }};
let checkInterval;

function checkStatus() {
    fetch('{{ url("/admin/payments") }}/' + paymentId + '/status', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'Thành công') {
            clearInterval(checkInterval);
            document.getElementById('paymentStatus').innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> Thanh toán thành công!
                </div>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-success">
                    <i class="bi bi-download"></i> Xem hóa đơn
                </a>
            `;
        } else if (data.status === 'Thất bại') {
            clearInterval(checkInterval);
            document.getElementById('paymentStatus').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle-fill"></i> Thanh toán thất bại. Vui lòng thử lại!
                </div>
            `;
        }
    });
}

// Polling mỗi 3 giây
checkInterval = setInterval(checkStatus, 3000);

// Stop polling sau 15 phút
setTimeout(() => {
    clearInterval(checkInterval);
    if (document.getElementById('paymentStatus').innerHTML.includes('spinner-border')) {
        document.getElementById('paymentStatus').innerHTML = `
            <div class="alert alert-warning">
                <i class="bi bi-clock-history"></i> Hết thời gian chờ. Vui lòng tạo giao dịch mới.
            </div>
        `;
    }
}, 900000);
</script>
@endsection