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
                                    <h3 class="mb-0">{{ ($todayStats['count'] ?? 0) - ($todayStats['failed'] ?? 0) }}</h3>
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
                                    <h3 class="mb-0">{{ $pendingCount ?? 0 }}</h3>
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
                                    <th>Hóa đơn</th>
                                    <th>Bệnh nhân</th>
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
                                    <td>#{{ $payment->payment_id }}</td>
                                    <td>{{ $payment->invoice->invoice_number ?? 'N/A' }}</td>
                                    <td>{{ $payment->invoice->patient->full_name ?? 'N/A' }}</td>
                                    <td class="fw-bold">{{ number_format($payment->amount) }} ₫</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $payment->payment_method }}</span>
                                    </td>
                                    <td>
                                        @php
                                        $statusClass = match($payment->status) {
                                        'Thành công' => 'success',
                                        'Chờ xử lý' => 'warning',
                                        'Thất bại' => 'danger',
                                        default => 'secondary'
                                        };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ $payment->status }}</span>
                                    </td>
                                    <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td><code>{{ $payment->transaction_ref ?? '---' }}</code></td>
                                    <td>
                                        @if($payment->invoice_id)
                                        <a href="{{ route('admin.payments.show', $payment->invoice_id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Không có giao dịch nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $payments->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection