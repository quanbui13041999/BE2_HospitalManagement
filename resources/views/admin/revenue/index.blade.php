@extends('layouts.admin')

@section('title', 'Quản lý doanh thu')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-wallet2 me-2 text-primary"></i>Quản lý doanh thu</h4>
            <p class="text-muted small mb-0">Thống kê doanh thu phòng khám năm {{ $year }}</p>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('admin.revenue.index') }}" class="d-flex align-items-center gap-2">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @for($i = date('Y') - 5; $i <= date('Y'); $i++)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>Năm {{ $i }}</option>
                    @endfor
                </select>
            </form>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-receipt me-1"></i>Lịch sử thanh toán
            </a>
        </div>
    </div>

    <!-- Cards Overview -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-white-50 mb-1 text-uppercase fw-bold">Tổng doanh thu</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalRevenue, 0, ',', '.') }} đ</h2>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-cash-coin"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-white-50 mb-1 text-uppercase fw-bold">Tổng hóa đơn thành công</h6>
                        <h2 class="mb-0 fw-bold">{{ number_format($totalTransactions) }}</h2>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white pt-3 pb-2 border-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-line text-primary me-2"></i>Biểu đồ doanh thu năm {{ $year }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white pt-3 pb-2 border-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart text-success me-2"></i>Phương thức thanh toán</h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="methodChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-warning me-2"></i>Giao dịch gần đây</h6>
            <a href="{{ route('admin.payments.index') }}" class="small text-decoration-none">Xem tất cả</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã GD</th>
                        <th>Bệnh nhân</th>
                        <th>Phương thức</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày thanh toán</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                    <tr>
                        <td class="fw-semibold text-primary">{{ $payment->transaction_ref }}</td>
                        <td>{{ $payment->appointment->user->full_name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $payment->method ?? 'Counter' }}</span>
                        </td>
                        <td class="fw-bold">{{ number_format($payment->total_amount, 0, ',', '.') }} đ</td>
                        <td>
                            @if(in_array($payment->status, ['Thành công', 'Đã thanh toán']))
                                <span class="badge bg-success-subtle text-success">Thành công</span>
                            @elseif($payment->status == 'Thất bại')
                                <span class="badge bg-danger-subtle text-danger">Thất bại</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">{{ $payment->status }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y H:i') : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có giao dịch nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Biểu đồ cột Doanh thu theo tháng
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        const monthlyData = @json($monthlyData);
        new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: monthlyData,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' đ';
                            }
                        }
                    }
                }
            }
        });

        // Biểu đồ tròn Phương thức thanh toán
        const ctxMethod = document.getElementById('methodChart').getContext('2d');
        const methodsData = @json($methods);
        const methodLabels = Object.keys(methodsData);
        const methodValues = Object.values(methodsData);
        
        new Chart(ctxMethod, {
            type: 'doughnut',
            data: {
                labels: methodLabels.length > 0 ? methodLabels : ['Chưa có dữ liệu'],
                datasets: [{
                    data: methodValues.length > 0 ? methodValues : [1],
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    });
</script>
@endpush
