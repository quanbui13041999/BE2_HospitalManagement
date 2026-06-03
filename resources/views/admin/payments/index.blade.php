@extends('layouts.admin')

@section('title', 'Quản lý giao dịch thanh toán')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-credit-card"></i> Quản Lý Thanh Toán</h5>
                </div>
                <div class="card-body">
                    <!-- Thống kê nhanh -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-cash-stack"></i> Doanh thu hôm nay</h6>
                                    <h3 class="mb-0">{{ number_format($todayStats['total'] ?? 0) }} ₫</h3>
                                    <small>{{ $todayStats['count'] ?? 0 }} giao dịch</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-check-circle"></i> Thành công</h6>
                                    <h3 class="mb-0">{{ $todayStats['success'] ?? ($todayStats['count'] - $todayStats['failed']) ?? 0 }}</h3>
                                    <small>Tỷ lệ: {{ $todayStats['rate'] ?? 0 }}%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-x-circle"></i> Thất bại</h6>
                                    <h3 class="mb-0">{{ $todayStats['failed'] ?? 0 }}</h3>
                                    <small>giao dịch thất bại</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-hourglass-split"></i> Chờ xử lý</h6>
                                    <h3 class="mb-0">{{ $todayStats['pending'] ?? 0 }}</h3>
                                    <small>đang xử lý</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bộ lọc -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-funnel"></i> Bộ lọc giao dịch
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label>Từ ngày</label>
                                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label>Đến ngày</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label>Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="">Tất cả</option>
                                        @foreach($statuses ?? [] as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Phương thức</label>
                                    <select name="method" class="form-select">
                                        <option value="">Tất cả</option>
                                        @foreach($methods ?? [] as $method)
                                        <option value="{{ $method }}" {{ request('method') == $method ? 'selected' : '' }}>{{ $method }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Lọc
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Danh sách giao dịch -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã GD</th>
                                    <th>Loại giao dịch</th>
                                    <th>Thông tin liên quan</th>
                                    <th>Số tiền</th>
                                    <th>Phương thức</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày thanh toán</th>
                                    <th>Mã tham chiếu</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments ?? [] as $payment)
                                <tr>
                                    <td><strong>{{ $payment->payment_id }}</strong></td>

                                    <!-- Loại giao dịch -->
                                    <td>
                                        @php
                                            $type = 'Khác';
                                            $typeIcon = 'bi-question-circle';
                                            
                                            if($payment->appointment_id) {
                                                $type = 'Đặt lịch khám';
                                                $typeIcon = 'bi-calendar-check';
                                            } elseif($payment->invoice_id) {
                                                $type = 'Hóa đơn dịch vụ';
                                                $typeIcon = 'bi-receipt';
                                            } elseif($payment->membership_id) {
                                                $type = 'Thẻ thành viên';
                                                $typeIcon = 'bi-gem';
                                            } elseif($payment->insurance_id) {
                                                $type = 'BHYT';
                                                $typeIcon = 'bi-shield-check';
                                            }
                                        @endphp
                                        <span class="badge bg-info">
                                            <i class="bi {{ $typeIcon }}"></i> {{ $type }}
                                        </span>
                                    </td>

                                    <!-- Thông tin liên quan -->
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($payment->appointment_id)
                                                <div>
                                                    <i class="bi bi-calendar text-muted"></i>
                                                    <span class="small text-muted">Lịch hẹn:</span>
                                                    <strong>#{{ $payment->appointment_id }}</strong>
                                                </div>
                                            @endif
                                            
                                            @if($payment->invoice_id)
                                                <div>
                                                    <i class="bi bi-receipt text-muted"></i>
                                                    <span class="small text-muted">Hóa đơn:</span>
                                                    <strong>#{{ $payment->invoice_id }}</strong>
                                                </div>
                                            @endif
                                            
                                            @if($payment->membership_id)
                                                <div>
                                                    <i class="bi bi-gem text-muted"></i>
                                                    <span class="small text-muted">Thẻ TV:</span>
                                                    <strong>#{{ $payment->membership_id }}</strong>
                                                </div>
                                            @endif
                                            
                                            @if($payment->insurance_id)
                                                <div>
                                                    <i class="bi bi-shield-check text-muted"></i>
                                                    <span class="small text-muted">BHYT:</span>
                                                    <strong>#{{ $payment->insurance_id }}</strong>
                                                </div>
                                            @endif
                                            
                                            @if(!$payment->appointment_id && !$payment->invoice_id && !$payment->membership_id && !$payment->insurance_id)
                                                <div>
                                                    <i class="bi bi-credit-card text-muted"></i>
                                                    <span class="small text-muted">Thanh toán</span>
                                                    <strong>trực tiếp</strong>
                                                </div>
                                            @endif
                                            
                                            <div>
                                                <i class="bi bi-upc-scan text-muted"></i>
                                                <span class="small text-muted">Mã GD:</span>
                                                <code class="small">{{ $payment->transaction_ref ?? '---' }}</code>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="fw-bold text-primary">{{ number_format($payment->total_amount ?? 0) }} ₫</td>
                                    
                                    <td>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-credit-card"></i> {{ $payment->method ?? 'N/A' }}
                                        </span>
                                    </td>
                                    
                                    <td>
                                        @php
                                        $statusClass = match($payment->status) {
                                            'Thành công', 'Đã thanh toán' => 'success',
                                            'Chờ xử lý', 'Chờ thanh toán', 'Chưa thanh toán' => 'warning',
                                            'Thất bại' => 'danger',
                                            default => 'secondary'
                                        };
                                        
                                        $statusIcon = match($payment->status) {
                                            'Thành công', 'Đã thanh toán' => 'check-circle',
                                            'Chờ xử lý', 'Chờ thanh toán', 'Chưa thanh toán' => 'hourglass-split',
                                            'Thất bại' => 'x-circle',
                                            default => 'question-circle'
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            <i class="bi bi-{{ $statusIcon }}"></i>
                                            {{ $payment->status }}
                                        </span>
                                    </td>
                                    
                                    <td>
                                        @if($payment->payment_date)
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        <code class="small">{{ $payment->transaction_ref ?? '---' }}</code>
                                    </td>
                                    
                                    <td>
                                        <button class="btn btn-sm btn-info" title="Xem chi tiết" onclick="showPaymentDetails({{ $payment->payment_id }})">
                                            <i class="bi bi-eye"></i> Xem
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Không có giao dịch nào</p>
                                        <small class="text-muted">Hãy thử thay đổi bộ lọc hoặc tạo giao dịch mới</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-center mt-3">
                        @if(method_exists($payments, 'links'))
                            {{ $payments->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BEAUTIFUL TRANSACTION DETAILS MODAL -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white border-0 px-4 py-3 position-relative">
                <h5 class="modal-title fw-bold" id="paymentDetailsModalLabel">
                    <i class="bi bi-receipt me-2"></i> Chi Tiết Giao Dịch
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-4" style="background-color: #f8fafc;">
                <!-- Loading State spinner -->
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="text-muted mt-2 small">Đang nạp thông tin giao dịch...</p>
                </div>
                
                <!-- Core Content -->
                <div id="modalContent" class="d-none">
                    
                    <!-- Billing Details Block -->
                    <div class="bg-white rounded-3 p-3 mb-3 border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Mã hóa đơn:</span>
                            <strong class="text-dark" id="detailPaymentId"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Mã tham chiếu:</span>
                            <code class="text-dark" id="detailTransactionRef"></code>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Thời gian:</span>
                            <span class="text-dark small" id="detailDate"></span>
                        </div>
                    </div>

                    <!-- Customer / Appointment details -->
                    <div class="bg-white rounded-3 p-3 mb-3 border">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 14px;">Thông tin khách hàng & Dịch vụ</h6>
                        
                        <div class="mb-2">
                            <span class="text-muted small d-block">Bệnh nhân:</span>
                            <strong class="text-dark" id="detailPatientName"></strong>
                        </div>

                        <div class="mb-2">
                            <span class="text-muted small d-block">Lịch hẹn / Hạng mục:</span>
                            <span class="text-dark fw-bold" id="detailAppointmentType"></span>
                        </div>

                        <div id="detailDoctorSection" class="mb-0">
                            <span class="text-muted small d-block">Thực hiện bởi:</span>
                            <span class="text-dark fw-bold text-primary" id="detailDoctorOrService"></span>
                        </div>
                    </div>

                    <!-- Payment Financial Breakdown -->
                    <div class="bg-white rounded-3 p-3 mb-3 border">
                        <h6 class="fw-bold text-dark mb-3" style="font-size: 14px;">Chi tiết hóa đơn tài chính</h6>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Đơn giá gốc:</span>
                            <span class="text-dark" id="detailSubtotal"></span>
                        </div>

                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span class="small">Bảo hiểm BHYT & Thẻ thành viên:</span>
                            <span id="detailDiscount"></span>
                        </div>

                        <hr class="my-2" style="border-top: 1px dashed #e2e8f0;">

                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-dark">Thực thu:</span>
                            <strong class="text-primary fs-5" id="detailTotalAmount"></strong>
                        </div>
                    </div>

                    <!-- Status block badge -->
                    <div class="d-flex justify-content-between align-items-center bg-white rounded-3 p-3 border">
                        <div>
                            <span class="text-muted small d-block">Phương thức thanh toán:</span>
                            <span class="badge bg-light text-dark border mt-1 px-3 py-1.5" id="detailMethod"></span>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small d-block">Trạng thái hiện tại:</span>
                            <span class="badge rounded-pill fw-bold mt-1 px-3 py-1.5" id="detailStatus"></span>
                        </div>
                    </div>
                </div>

            </div>
            
            <!-- Modal Footer Action buttons -->
            <div class="modal-footer border-0 px-4 py-3 bg-light justify-content-between">
                <button type="button" class="btn btn-outline-secondary fw-bold px-4" data-bs-dismiss="modal">Đóng</button>
                <div id="modalActions" class="d-flex gap-2">
                    <!-- Quick action buttons populate dynamically here based on status -->
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
let detailModal = null;

document.addEventListener('DOMContentLoaded', function() {
    detailModal = new bootstrap.Modal(document.getElementById('paymentDetailsModal'));
});

function showPaymentDetails(paymentId) {
    // Open the modal and display loader
    detailModal.show();
    document.getElementById('modalLoading').classList.remove('d-none');
    document.getElementById('modalContent').classList.add('d-none');
    document.getElementById('modalActions').innerHTML = '';

    // Fetch details via dynamic AJAX endpoint
    fetch(`/admin/payments/${paymentId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(res => {
        if(res.success && res.payment) {
            const p = res.payment;
            
            // Map values to modal elements
            document.getElementById('detailPaymentId').innerText = `#${p.payment_id}`;
            document.getElementById('detailTransactionRef').innerText = p.transaction_ref || '---';
            
            const dateObj = new Date(p.payment_date);
            const dateString = isNaN(dateObj) ? 'Chưa thanh toán' : dateObj.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'}) + ' ' + dateObj.toLocaleDateString('vi-VN');
            document.getElementById('detailDate').innerText = dateString;
            
            document.getElementById('detailPatientName').innerText = p.appointment?.user?.full_name || 'Không rõ bệnh nhân';
            
            // Build type description
            let appType = 'Hóa đơn vãng lai';
            if(p.appointment?.service) {
                appType = 'Đăng ký dịch vụ y tế độc lập';
                document.getElementById('detailDoctorOrService').innerText = p.appointment.service.service_name;
            } else if(p.appointment_id) {
                appType = 'Đặt lịch hẹn lâm sàng';
                document.getElementById('detailDoctorOrService').innerText = p.appointment?.schedule?.doctor?.full_name ? 'Bác sĩ: ' + p.appointment.schedule.doctor.full_name : 'Bác sĩ trực';
            }
            document.getElementById('detailAppointmentType').innerText = appType;
            
            // Format currency
            const fmt = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
            document.getElementById('detailSubtotal').innerText = fmt(p.subtotal || 0);
            document.getElementById('detailDiscount').innerText = `- ${fmt(p.discount_amount || 0)}`;
            document.getElementById('detailTotalAmount').innerText = fmt(p.total_amount || 0);
            
            document.getElementById('detailMethod').innerText = p.method || 'N/A';
            
            // Setup status styles
            const statusBadge = document.getElementById('detailStatus');
            statusBadge.innerText = p.status;
            statusBadge.className = 'badge rounded-pill fw-bold px-3 py-1.5';
            
            if(p.status === 'Thành công' || p.status === 'Đã thanh toán') {
                statusBadge.classList.add('bg-success-subtle', 'text-success', 'border', 'border-success-subtle');
            } else if(p.status === 'Thất bại') {
                statusBadge.classList.add('bg-danger-subtle', 'text-danger', 'border', 'border-danger-subtle');
            } else {
                statusBadge.classList.add('bg-warning-subtle', 'text-warning', 'border', 'border-warning-subtle');
            }

            // Quick actions block for pending payments
            const actionContainer = document.getElementById('modalActions');
            if(p.status !== 'Thành công' && p.status !== 'Đã thanh toán' && p.status !== 'Thất bại') {
                actionContainer.innerHTML = `
                    <button class="btn btn-danger fw-bold px-4" onclick="failPaymentAction(${p.payment_id})">
                        <i class="bi bi-x-circle me-1"></i> Thất bại
                    </button>
                    <button class="btn btn-success fw-bold px-4" onclick="confirmPaymentAction(${p.payment_id}, '${p.transaction_ref || ''}')">
                        <i class="bi bi-check-circle me-1"></i> Xác nhận thu tiền
                    </button>
                `;
            }

            // Hide loader and show content
            document.getElementById('modalLoading').classList.add('d-none');
            document.getElementById('modalContent').classList.remove('d-none');
        } else {
            alert('Lỗi tải dữ liệu giao dịch.');
            detailModal.hide();
        }
    })
    .catch(err => {
        console.error(err);
        alert('Có lỗi xảy ra khi nạp thông tin.');
        detailModal.hide();
    });
}

function confirmPaymentAction(paymentId, ref) {
    if(confirm('Xác nhận bệnh nhân đã thanh toán thành công hóa đơn này?')) {
        const formData = new FormData();
        formData.append('ref', ref);
        formData.append('_token', '{{ csrf_token() }}');

        fetch(`/admin/payments/${paymentId}/confirm`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(res => {
            if(res.success) {
                alert('Xác nhận thanh toán thành công!');
                location.reload();
            } else {
                alert('Có lỗi xảy ra: ' + res.message);
            }
        });
    }
}

function failPaymentAction(paymentId) {
    if(confirm('Bạn có chắc chắn muốn đánh dấu giao dịch này là THẤT BẠI?')) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');

        fetch(`/admin/payments/${paymentId}/fail`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(res => {
            if(res.success) {
                alert('Giao dịch đã được cập nhật là Thất bại.');
                location.reload();
            } else {
                alert('Có lỗi xảy ra.');
            }
        });
    }
}
</script>
@endsection