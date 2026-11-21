@extends('layouts.admin')

@section('title', 'Thanh toán hóa đơn')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Thanh Toán Hóa Đơn</h5>
                </div>
                <div class="card-body">
                    <!-- Thông tin hóa đơn -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Thông tin bệnh nhân</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Họ tên:</th>
                                    <td>{{ $invoice->patient->full_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Số điện thoại:</th>
                                    <td>{{ $invoice->patient->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $invoice->patient->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Địa chỉ:</th>
                                    <td>{{ $invoice->patient->address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày sinh:</th>
                                    <td>{{ $invoice->patient->dob ? \Carbon\Carbon::parse($invoice->patient->dob)->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Thông tin hóa đơn</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Số hóa đơn:</th>
                                    <td class="fw-bold">#{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <th>Ngày lập:</th>
                                    <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Hạn thanh toán:</th>
                                    <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Trạng thái:</th>
                                    <td>
                                        <span class="badge bg-{{ $invoice->status === 'Đã thanh toán' ? 'success' : 'warning' }}">
                                            {{ $invoice->status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Chi tiết dịch vụ -->
                    <h6>Chi tiết dịch vụ</h6>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Dịch vụ</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->service_name }}</td>
                                    <td>{{ number_format($item->unit_price) }} ₫</td>
                                    <td>{{ number_format($item->total_price) }} ₫</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @if($invoice->bhyt_applied)
                                <tr class="table-info">
                                    <td colspan="2"><strong>BHYT chi trả ({{ $invoice->bhyt_coverage }}%)</strong></td>
                                    <td><strong class="text-success">- {{ number_format($invoice->bhyt_amount) }} ₫</strong></td>
                                </tr>
                            @endif
                            <tr class="table-warning">
                                <td colspan="2"><strong>Tổng cộng</strong></td>
                                <td><strong class="text-danger">{{ number_format($invoice->total_amount) }} ₫</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Form thanh toán -->
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-credit-card"></i> Chọn phương thức thanh toán</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.payments.store') }}" method="POST" id="paymentForm">
                                @csrf
                                <input type="hidden" name="invoice_id" value="{{ $invoice->invoice_id }}">
                                <input type="hidden" name="amount" value="{{ $invoice->total_amount }}">
                                
                                <div class="row">
                                    @foreach($paymentMethods ?? ['QR', 'ATM', 'MoMo', 'ZaloPay', 'Counter'] as $method)
                                        <div class="col-md-2 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" 
                                                       id="method_{{ $method }}" value="{{ $method }}" required>
                                                <label class="form-check-label" for="method_{{ $method }}">
                                                    {{ $method }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle"></i> 
                                    Số tiền cần thanh toán: <strong class="text-danger">{{ number_format($invoice->total_amount) }} ₫</strong>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-check-circle"></i> Tiến hành thanh toán
                                    </button>
                                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-lg">
                                        <i class="bi bi-arrow-left"></i> Quay lại
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection