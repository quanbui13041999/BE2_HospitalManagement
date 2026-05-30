@extends('layouts.admin')

@section('title', 'Chi tiết giao dịch #' . ($payment->payment_id ?? ''))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card"></i> 
                        Chi tiết giao dịch #{{ $payment->payment_id ?? 'N/A' }}
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="bi bi-info-circle"></i> Thông tin giao dịch</strong>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="35%">Mã GD:</th>
                                            <td><strong>#{{ $payment->payment_id }}</strong>}}
                                        </tr>
                                        <tr>
                                            <th>Mã tham chiếu:</th>
                                            <td><code>{{ $payment->transaction_ref ?? '---' }}</code>}}
                                        </tr>
                                        <tr>
                                            <th>Số tiền:</th>
                                            <td class="fw-bold text-primary">{{ number_format($payment->total_amount ?? 0) }} ₫</td>
                                        </tr>
                                        <tr>
                                            <th>Phương thức:</th>
                                            <td>{{ $payment->method ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Trạng thái:</th>
                                            <td>
                                                @php
                                                $statusClass = match($payment->status) {
                                                    'Thành công', 'Đã thanh toán' => 'success',
                                                    'Chờ xử lý', 'Chờ thanh toán', 'Chưa thanh toán' => 'warning',
                                                    'Thất bại' => 'danger',
                                                    default => 'secondary'
                                                };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }}">{{ $payment->status }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Ngày thanh toán:</th>
                                            <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Appointment ID:</th>
                                            <td>{{ $payment->appointment_id ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Subtotal:</th>
                                            <td>{{ number_format($payment->subtotal ?? 0) }} ₫</td>
                                        </tr>
                                        <tr>
                                            <th>Discount:</th>
                                            <td>{{ number_format($payment->discount_amount ?? 0) }} ₫</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="bi bi-person"></i> Thông tin bệnh nhân</strong>
                                </div>
                                <div class="card-body">
                                    @if($payment->appointment && $payment->appointment->user)
                                        <table class="table table-borderless">
                                            <tr><th width="35%">Họ tên:</th><td>{{ $payment->appointment->user->name }}</td></tr>
                                            <tr><th>Email:</th><td>{{ $payment->appointment->user->email ?? 'N/A' }}</td></tr>
                                            <tr><th>Số điện thoại:</th><td>{{ $payment->appointment->user->phone ?? 'N/A' }}</td></tr>
                                            @if($payment->appointment->schedule)
                                                <tr><th>Ngày khám:</th><td>{{ \Carbon\Carbon::parse($payment->appointment->schedule->date)->format('d/m/Y') ?? 'N/A' }}</td></tr>
                                                <tr><th>Bác sĩ:</th><td>{{ $payment->appointment->schedule->doctor->full_name ?? 'N/A' }}</td></tr>
                                            @else
                                                <tr><th>Ngày thực hiện:</th><td>{{ \Carbon\Carbon::parse($payment->appointment->appointment_time)->format('d/m/Y') ?? 'N/A' }}</td></tr>
                                                <tr><th>Phân loại:</th><td><span class="badge bg-success">Dịch vụ độc lập</span></td></tr>
                                            @endif
                                        </table>
                                    @else
                                        <p class="text-muted">Không có thông tin bệnh nhân</p>
                                        <hr>
                                        <small class="text-muted">Appointment ID: {{ $payment->appointment_id }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($payment->items && $payment->items->count() > 0)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="bi bi-list-ul"></i> Chi tiết dịch vụ</strong>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Dịch vụ</th>
                                        <th>Đơn giá</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payment->items as $item)
                                    <tr>
                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ number_format($item->unit_price) }} ₫</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="fw-bold">{{ number_format($item->total_price) }} ₫</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                        <td class="fw-bold text-primary">{{ number_format($payment->total_amount) }} ₫</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif
                    
                    <div class="mt-3">
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection