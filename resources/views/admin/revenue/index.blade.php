@extends('layouts.admin')

@section('title', 'Quản lý doanh thu')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body {
        background-color: #f8fafc;
    }
    .page-title {
        color: #1e3a8a;
        font-weight: 700;
        font-size: 1.5rem;
    }
    .page-subtitle {
        color: #64748b;
        font-size: 0.875rem;
    }
    .kpi-card {
        border-radius: 16px;
        padding: 1.5rem;
        background: #fff;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        display: flex;
        align-items: center;
        gap: 1.25rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
    }
    .kpi-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }
    .kpi-icon.blue { background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #0284c7; }
    .kpi-icon.green { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a; }
    .kpi-icon.purple { background: linear-gradient(135deg, #f3e8ff, #e9d5ff); color: #7c3aed; }
    .kpi-icon.orange { background: linear-gradient(135deg, #ffedd5, #fed7aa); color: #ea580c; }
    
    .kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.25rem;
        line-height: 1.2;
    }
    .kpi-label {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0;
        font-weight: 500;
    }
    
    .growth-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.25rem;
    }
    .growth-up { background: #dcfce7; color: #15803d; }
    .growth-down { background: #fee2e2; color: #b91c1c; }

    .chart-card, .table-card {
        background: #fff;
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .card-title-custom {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .table-custom th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }
    .table-custom td {
        padding: 0.875rem 1rem;
        vertical-align: middle;
        color: #334155;
        font-weight: 500;
    }

    @media print {
        #topbar, .no-print, .btn, header, nav, footer, .card.shadow-sm.p-3 {
            display: none !important;
        }
        #sidebar * {
            display: none !important;
        }
        #sidebar, #sidebar .brand, #sidebar .brand i {
            display: block !important;
        }
        #sidebar {
            width: 70px !important;
            border-right: 1px solid #cbd5e1 !important;
            background: #fff !important;
            height: 100vh !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
        }
        #sidebar .brand {
            font-size: 0 !important;
            text-align: center !important;
            padding: 20px 0 !important;
            border: none !important;
        }
        #sidebar .brand i {
            font-size: 28px !important;
            color: #0d6efd !important;
        }
        #main-wrap {
            margin-left: 70px !important;
            padding: 0 15px !important;
        }
        #content {
            padding: 0 !important;
        }
        .row {
            display: flex !important;
            flex-wrap: nowrap !important;
        }
        .col-lg-8 {
            width: 65% !important;
            max-width: 65% !important;
            flex: 0 0 65% !important;
        }
        .col-lg-4 {
            width: 35% !important;
            max-width: 35% !important;
            flex: 0 0 35% !important;
        }
        .col-lg-5 {
            width: 45% !important;
            max-width: 45% !important;
            flex: 0 0 45% !important;
        }
        .col-lg-7 {
            width: 55% !important;
            max-width: 55% !important;
            flex: 0 0 55% !important;
        }
        .chart-card, .table-card, .kpi-card {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            overflow: hidden !important;
            height: auto !important;
        }
        #revenueChart, #methodChart {
            width: 100% !important;
            max-width: 100% !important;
            height: 220px !important;
        }
        /* Custom Page Breaks for print */
        .print-page {
            page-break-before: always !important;
            break-before: page !important;
            padding-top: 30px !important;
        }
        .print-page:first-of-type {
            page-break-before: avoid !important;
            break-before: avoid !important;
            padding-top: 0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <!-- PAGE 1: EXECUTIVE OVERVIEW & KPI CARDS -->
    <div class="print-page">
        <!-- Print Header (Only visible on paper/print) -->
        <div class="d-none d-print-block mb-4 text-center">
            <h3 class="fw-bold mb-1" style="color: #1e3a8a;">PHÒNG KHÁM ĐA KHOA ANTIGRAVITY</h3>
            <p class="text-muted mb-3" style="font-size: 0.9rem;">Địa chỉ: 123 Đường Ba Tháng Hai, Quận 10, TP. Hồ Chí Minh</p>
            <hr style="border-top: 2px double #334155; margin: 15px 0;">
            <h4 class="fw-bold mt-3 text-uppercase text-primary">Báo Cáo Doanh Thu Tổng Hợp</h4>
            <p class="small text-muted mb-1">
                Kỳ báo cáo: 
                @if($month)
                    Tháng {{ $month }} / {{ $year }}
                @else
                    Năm {{ $year }}
                @endif
                @if($selectedMethod)
                    - Phương thức: {{ $selectedMethod }}
                @endif
            </p>
            <p class="small text-muted">Ngày xuất bản: {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <div class="text-muted small mb-1"><i class="bi bi-house-door me-1"></i> / Báo cáo doanh thu</div>
                <h1 class="page-title mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>Quản Lý & Báo Cáo Doanh Thu</h1>
                <p class="page-subtitle">Phân tích hiệu quả kinh doanh, phương thức thanh toán và chuyên khoa khám bệnh</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-light border shadow-sm">
                    <i class="bi bi-printer me-1"></i>In báo cáo
                </button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-receipt me-1"></i>Lịch sử thanh toán
                </a>
            </div>
        </div>

        <!-- Filters (Hidden on print) -->
        <div class="card shadow-sm border-0 p-3 mb-4 no-print bg-white" style="border-radius: 12px;">
            <form method="GET" action="{{ route('admin.revenue.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-bold">Chọn Năm</label>
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        @for($i = date('Y') - 5; $i <= date('Y'); $i++)
                            <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>Năm {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-bold">Chọn Tháng</label>
                    <select name="month" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả các tháng</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold">Phương Thức Thanh Toán</label>
                    <select name="method" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả phương thức</option>
                        @foreach($availableMethods as $availMethod)
                            <option value="{{ $availMethod }}" {{ $selectedMethod == $availMethod ? 'selected' : '' }}>
                                {{ $availMethod }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('admin.revenue.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- KPI Overview Cards -->
        <h5 class="fw-bold mb-3 d-none d-print-block text-secondary" style="font-size: 1.1rem; border-left: 4px solid #0d6efd; padding-left: 10px;">Phần I: Các Chỉ Số Tài Chính Cốt Lõi</h5>
        <div class="row g-4 mb-4">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="kpi-card">
                    <div class="kpi-icon blue"><i class="bi bi-cash-coin"></i></div>
                    <div>
                        <div class="kpi-value text-primary">{{ number_format($totalRevenue, 0, ',', '.') }} đ</div>
                        <div class="kpi-label">Doanh thu kỳ này</div>
                        @if($growthRate >= 0)
                            <span class="growth-badge growth-up">
                                <i class="bi bi-arrow-up-right-circle-fill"></i> +{{ $growthRate }}% so với trước
                            </span>
                        @else
                            <span class="growth-badge growth-down">
                                <i class="bi bi-arrow-down-right-circle-fill"></i> {{ $growthRate }}% so với trước
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="kpi-card">
                    <div class="kpi-icon green"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <div class="kpi-value text-success">{{ number_format($totalTransactions) }}</div>
                        <div class="kpi-label">Số giao dịch thành công</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="kpi-card">
                    <div class="kpi-icon purple"><i class="bi bi-award-fill"></i></div>
                    <div>
                        <div class="kpi-value text-purple" style="font-size: 1.15rem;">
                            {{ $departmentRevenue->first()->department_name ?? 'Chưa có' }}
                        </div>
                        <div class="kpi-label">Chuyên khoa cao nhất</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Narrative summary (Only visible on paper) -->
        <div class="d-none d-print-block mt-5 p-4 border rounded bg-light" style="border-radius: 12px;">
            <h5 class="fw-bold mb-3" style="color: #1e3a8a;"><i class="bi bi-file-earmark-text me-2"></i>Thuyết Minh Tóm Tắt Hoạt Động Doanh Thu</h5>
            <p class="text-dark" style="text-align: justify; line-height: 1.8; font-size: 10.5pt;">
                Dựa trên số liệu tổng hợp trong kỳ báo cáo hoạt động kinh doanh của phòng khám đa khoa Antigravity. Tổng doanh thu viện phí ghi nhận thành công đạt 
                <strong>{{ number_format($totalRevenue, 0, ',', '.') }} đ</strong> thông qua việc xử lý thành công 
                <strong>{{ number_format($totalTransactions) }}</strong> giao dịch thanh toán viện phí và dịch vụ khám chữa bệnh. 
                Trong đó, chuyên khoa lâm sàng có doanh thu đóng góp hiệu quả cao nhất là khoa <strong>{{ $departmentRevenue->first()->department_name ?? '—' }}</strong>. 
                Số liệu này làm cơ sở đánh giá chất lượng hoạt động, chỉ tiêu kinh doanh và làm căn cứ hoạch định kế hoạch nâng cao chất lượng hoạt động chăm sóc sức khỏe bệnh nhân tại cơ sở khám chữa bệnh.
            </p>
            <div class="mt-4 text-end">
                <p class="small text-muted italic">Ký bởi phòng quản lý tài chính và ban giám đốc phòng khám.</p>
            </div>
        </div>
    </div>

    <!-- PAGE 2: CHARTS & TREND ANALYSIS -->
    <div class="print-page">
        <div class="d-none d-print-block mb-4">
            <h5 class="fw-bold text-uppercase text-primary" style="font-size: 1.1rem; border-left: 4px solid #0d6efd; padding-left: 10px;">
                Phần II: Phân Tích Xu Hướng & Cơ Cấu Thanh Toán
            </h5>
            <p class="text-muted small mb-2">Biểu đồ trực quan biểu diễn biến động doanh thu theo thời gian và tỷ trọng các kênh thanh toán.</p>
            <hr style="margin-top: 5px;">
        </div>

        <div class="row">
            <!-- Monthly Line Chart -->
            <div class="col-lg-8 col-md-8 col-sm-12">
                <div class="chart-card">
                    <div class="card-title-custom">
                        <i class="bi bi-graph-up text-primary"></i> Xu hướng doanh thu theo tháng năm {{ $year }}
                    </div>
                    <div style="height: 320px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Doughnut -->
            <div class="col-lg-4 col-md-4 col-sm-12">
                <div class="chart-card">
                    <div class="card-title-custom">
                        <i class="bi bi-pie-chart text-success"></i> Cơ cấu phương thức thanh toán
                    </div>
                    <div style="height: 320px;" class="d-flex justify-content-center align-items-center">
                        <canvas id="methodChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 3: SPECIALTY REVENUE BREAKDOWN -->
    <div class="print-page">
        <div class="d-none d-print-block mb-4">
            <h5 class="fw-bold text-uppercase text-primary" style="font-size: 1.1rem; border-left: 4px solid #0d6efd; padding-left: 10px;">
                Phần III: Hiệu Quả Hoạt Động Theo Chuyên Khoa
            </h5>
            <p class="text-muted small mb-2">Bảng tổng hợp xếp hạng doanh số đóng góp và tỷ trọng doanh thu của các chuyên khoa lâm sàng.</p>
            <hr style="margin-top: 5px;">
        </div>

        <div class="card table-card">
            <div class="card-title-custom">
                <i class="bi bi-hospital text-purple"></i> Doanh thu đóng góp theo chuyên khoa khám bệnh
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Chuyên khoa</th>
                            <th class="text-center" style="width: 30%;">Tỷ lệ đóng góp</th>
                            <th class="text-end" style="width: 30%;">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departmentRevenue as $dep)
                        @php
                            $pct = $totalRevenue > 0 ? round(($dep->total / $totalRevenue) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">{{ $dep->department_name }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-fill" style="height: 8px; border-radius: 4px; overflow: hidden; background: #e2e8f0;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pct }}%; border-radius: 4px;"></div>
                                    </div>
                                    <span class="small fw-bold text-muted" style="min-width: 35px;">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="text-end fw-bold text-success">
                                {{ number_format($dep->total, 0, ',', '.') }} đ
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu chuyên khoa.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PAGE 4: DETAILED LEDGER & SIGNATURES -->
    <div class="print-page">
        <div class="d-none d-print-block mb-4">
            <h5 class="fw-bold text-uppercase text-primary" style="font-size: 1.1rem; border-left: 4px solid #0d6efd; padding-left: 10px;">
                Phần IV: Nhật Ký Giao Dịch Viện Phí Chi Tiết
            </h5>
            <p class="text-muted small mb-2">Bảng tổng hợp chi tiết tối đa 25 giao dịch viện phí phát sinh gần nhất trong kỳ phục vụ kiểm toán.</p>
            <hr style="margin-top: 5px;">
        </div>

        <div class="card table-card">
            <div class="card-title-custom">
                <i class="bi bi-clock-history text-warning"></i> Nhật ký giao dịch phát sinh gần đây
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Mã GD</th>
                            <th>Bệnh nhân</th>
                            <th>Phương thức</th>
                            <th class="text-end">Tổng tiền</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                        <tr>
                            <td class="fw-semibold text-primary" style="font-size: 0.8rem;">
                                {{ $payment->transaction_ref ?: 'COUNTER-' . $payment->payment_id }}
                            </td>
                            <td>{{ $payment->appointment->user->full_name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $payment->method ?: 'Tại quầy' }}</span>
                            </td>
                            <td class="text-end fw-bold">{{ number_format($payment->total_amount, 0, ',', '.') }} đ</td>
                            <td>
                                @if(in_array($payment->status, ['Thành công', 'Đã thanh toán']))
                                    <span class="badge bg-success-subtle text-success">Thành công</span>
                                @elseif($payment->status == 'Thất bại')
                                    <span class="badge bg-danger-subtle text-danger">Thất bại</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">{{ $payment->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có giao dịch nào phù hợp.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Signature Section (Only visible on paper) -->
        <div class="row mt-5 text-center d-none d-print-flex">
            <div class="col-6">
                <p class="fw-bold text-dark">NGƯỜI LẬP BIỂU</p>
                <p class="text-muted small" style="margin-top: 65px;">(Ký, ghi rõ họ tên)</p>
            </div>
            <div class="col-6">
                <p class="fw-bold text-dark">GIÁM ĐỐC PHÒNG KHÁM</p>
                <p class="text-muted small" style="margin-top: 65px;">(Ký, đóng dấu)</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Line Chart with Smooth Gradients for Monthly Trend
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    
    const gradient = ctxRevenue.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.4)');
    gradient.addColorStop(1, 'rgba(13, 110, 253, 0.01)');

    const monthlyData = @json($monthlyData);
    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: monthlyData,
                backgroundColor: gradient,
                borderColor: '#0d6efd',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#0d6efd',
                pointHoverRadius: 7
            }]
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        color: '#64748b',
                        callback: function(value) {
                            return value.toLocaleString() + ' đ';
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b' }
                }
            }
        }
    });

    // 2. Payment Methods Doughnut
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
                backgroundColor: ['#0d6efd', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b'],
                borderWidth: 0
            }]
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 15, color: '#475569' }
                }
            }
        }
    });
});
</script>
@endpush
