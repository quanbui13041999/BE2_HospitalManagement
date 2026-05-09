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
                                    <th>Thông tin giao dịch</th>
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
                                    <td><strong>{{ $payment->payment_id }}</strong>}}

                                    <!-- THAY THẾ: Hiển thị thông tin giao dịch thay vì tên bệnh nhân -->
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <!-- Appointment ID -->
                                            <div>
                                                <i class="bi bi-hash text-muted"></i>
                                                <span class="small text-muted">Appointment ID:</span>
                                                <strong>{{ $payment->appointment_id ?? 'N/A' }}</strong>
                                            </div>
                                            
                                            <!-- Mã hóa đơn (nếu có) -->
                                            @if($payment->invoice_id)
                                            <div>
                                                <i class="bi bi-receipt text-muted"></i>
                                                <span class="small text-muted">Invoice ID:</span>
                                                <strong>{{ $payment->invoice_id }}</strong>
                                            </div>
                                            @endif
                                            
                                            <!-- Mã giao dịch -->
                                            <div>
                                                <i class="bi bi-upc-scan text-muted"></i>
                                                <span class="small text-muted">Mã GD:</span>
                                                <code class="small">{{ $payment->transaction_ref ?? '---' }}</code>
                                            </div>
                                            
                                            <!-- Ngày tạo -->
                                            <div>
                                                <i class="bi bi-clock text-muted"></i>
                                                <span class="small text-muted">Tạo lúc:</span>
                                                <span class="small">
                                                    {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y H:i') : 'N/A' }}
                                                </span>
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
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.payments.show', $payment->payment_id) }}" 
                                               class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($payment->status == 'Chờ xử lý' || $payment->status == 'Chờ thanh toán')
                                            <button type="button" 
                                                    class="btn btn-sm btn-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#confirmModal{{ $payment->payment_id }}"
                                                    title="Xác nhận thành công">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#failModal{{ $payment->payment_id }}"
                                                    title="Xác nhận thất bại">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            @endif
                                        </div>

                                        <!-- Modal Confirm -->
                                        <div class="modal fade" id="confirmModal{{ $payment->payment_id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-check-circle"></i> Xác nhận thanh toán
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Xác nhận thanh toán thành công cho giao dịch:</p>
                                                        <ul>
                                                            <li><strong>Mã GD:</strong> {{ $payment->payment_id }}</li>
                                                            <li><strong>Số tiền:</strong> <span class="text-primary">{{ number_format($payment->total_amount) }} ₫</span></li>
                                                            <li><strong>Phương thức:</strong> {{ $payment->method }}</li>
                                                        </ul>
                                                        <p class="text-warning mb-0">⚠️ Hành động này không thể hoàn tác!</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                        <form action="{{ route('admin.payments.confirm', $payment->payment_id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="bi bi-check-lg"></i> Xác nhận thành công
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Fail -->
                                        <div class="modal fade" id="failModal{{ $payment->payment_id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-x-circle"></i> Xác nhận thất bại
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Xác nhận thanh toán <strong class="text-danger">THẤT BẠI</strong> cho giao dịch:</p>
                                                        <ul>
                                                            <li><strong>Mã GD:</strong> {{ $payment->payment_id }}</li>
                                                            <li><strong>Số tiền:</strong> {{ number_format($payment->total_amount) }} ₫</li>
                                                        </ul>
                                                        <p class="text-danger mb-0">⚠️ Hành động này không thể hoàn tác!</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                        <form action="{{ route('admin.payments.fail', $payment->payment_id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="bi bi-x-lg"></i> Xác nhận thất bại
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
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
@endsection