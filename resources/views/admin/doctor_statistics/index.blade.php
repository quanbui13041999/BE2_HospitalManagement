@extends('layouts.admin')

@section('title', 'Thống kê bác sĩ')

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
    .filter-card {
        background: transparent;
        border: none;
    }
    .kpi-card {
        border-radius: 12px;
        padding: 1.5rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        gap: 1rem;
        height: 100%;
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .kpi-icon.blue { background: #e0f2fe; color: #0284c7; }
    .kpi-icon.green { background: #dcfce7; color: #16a34a; }
    .kpi-icon.orange { background: #ffedd5; color: #ea580c; }
    .kpi-icon.red { background: #fee2e2; color: #dc2626; }
    
    .kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.25rem;
        line-height: 1;
    }
    .kpi-label {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0;
    }
    
    .chart-card, .table-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        padding: 1.5rem;
        height: 100%;
    }
    .card-title-custom {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1.5rem;
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
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 1rem;
    }
    .table-custom td {
        padding: 1rem 0;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-weight: 500;
    }
    .table-custom tr:last-child td {
        border-bottom: none;
    }
    
    .mom-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        text-align: center;
    }
    .mom-box {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
        flex: 1;
    }
    .mom-box.highlight {
        background: #f0fdf4;
    }
    .mom-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }
    .mom-value.highlight {
        color: #16a34a;
    }
    .mom-label {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .badge-growth {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        background: #dcfce7;
        color: #16a34a;
        font-weight: 600;
    }
    .badge-drop {
        background: #fee2e2;
        color: #dc2626;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-muted small mb-1"><i class="bi bi-house-door me-1"></i> / Thống kê bác sĩ</div>
            <h1 class="page-title mb-1"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Thống Kê Theo Bác Sĩ</h1>
            <p class="page-subtitle">Phân tích hiệu suất khám bệnh và doanh thu theo từng bác sĩ</p>
        </div>
        <div class="d-none d-md-flex align-items-center text-muted">
            <i class="bi bi-calendar3 me-2"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d/m/Y') }}
        </div>
    </div>

    <!-- Filters -->
    <form action="{{ route('admin.doctor-statistics.index') }}" method="GET" class="row g-3 mb-4 filter-card">
        <div class="col-md-4">
            <select name="doctor_id" class="form-select form-select-lg" style="border-radius: 8px;">
                <option value="all">Tất cả bác sĩ</option>
                @foreach($doctors as $doc)
                    <option value="{{ $doc->doctor_id }}" {{ $selectedDoctorId == $doc->doctor_id ? 'selected' : '' }}>
                        {{ $doc->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="month" name="month" class="form-control form-select-lg" value="{{ $selectedMonthStr }}" style="border-radius: 8px;">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100 h-100" style="border-radius: 8px; font-weight: 600;">
                <i class="bi bi-bar-chart-line me-2"></i> Xem báo cáo
            </button>
        </div>
    </form>

    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon blue"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="kpi-value">{{ number_format($totalAppointments) }}</div>
                    <div class="kpi-label">Lượt khám tháng này</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon green"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="kpi-value">{{ number_format($totalRevenue / 1000000, 1) }}M</div>
                    <div class="kpi-label">Doanh thu tạo ra</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon orange"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="kpi-value">24 phút</div>
                    <div class="kpi-label">TG khám trung bình</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon red"><i class="bi bi-x-circle-fill"></i></div>
                <div>
                    <div class="kpi-value">{{ $cancelRate }}%</div>
                    <div class="kpi-label">Tỷ lệ hủy lịch</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Chart -->
        <div class="col-lg-7">
            <div class="chart-card">
                <div class="card-title-custom">
                    <i class="bi bi-bar-chart-fill text-primary"></i> Lượt khám theo ngày - {{ $selectedDate->format('m/Y') }}
                </div>
                <div style="height: 350px;">
                    <canvas id="appointmentsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables & MoM -->
        <div class="col-lg-5 d-flex flex-column gap-4">
            <!-- Table -->
            <div class="table-card flex-grow-1">
                <div class="card-title-custom">
                    <i class="bi bi-people-fill text-primary"></i> So sánh bác sĩ - Tháng {{ $selectedDate->format('m') }}
                </div>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Bác sĩ</th>
                                <th class="text-center">Lượt khám</th>
                                <th class="text-center">Hủy</th>
                                <th class="text-end">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($doctorStats->take(5) as $doc)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $doc->full_name }}</div>
                                    <div class="small text-muted">{{ $doc->department_name ?? 'Đa khoa' }}</div>
                                </td>
                                <td class="text-center fw-bold">{{ $doc->total_appointments }}</td>
                                <td class="text-center text-danger">{{ $doc->cancel_rate }}%</td>
                                <td class="text-end fw-bold">{{ number_format($doc->revenue / 1000000, 1) }}M</td>
                            </tr>
                            @endforeach
                            @if($doctorStats->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Chưa có dữ liệu</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MoM Comparison -->
            <div class="mom-card">
                <div class="card-title-custom text-start mb-3">
                    <i class="bi bi-arrow-left-right text-primary"></i> So sánh tháng trước
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="mom-box">
                        <div class="mom-label mb-1">Tháng {{ $previousDate->format('m') }}</div>
                        <div class="mom-value">{{ number_format($previousTotalAppointments) }}</div>
                        <div class="mom-label">lượt khám</div>
                    </div>
                    <div class="text-muted"><i class="bi bi-arrow-right"></i></div>
                    <div class="mom-box highlight position-relative">
                        <div class="mom-label mb-1">Tháng {{ $selectedDate->format('m') }}</div>
                        <div class="mom-value highlight">{{ number_format($totalAppointments) }}</div>
                        @if($momGrowth >= 0)
                            <span class="badge-growth position-absolute bottom-0 start-50 translate-middle-x mb-2">
                                <i class="bi bi-caret-up-fill"></i> {{ $momGrowth }}%
                            </span>
                        @else
                            <span class="badge-growth badge-drop position-absolute bottom-0 start-50 translate-middle-x mb-2">
                                <i class="bi bi-caret-down-fill"></i> {{ abs($momGrowth) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('appointmentsChart').getContext('2d');
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, '#3b82f6'); // blue-500
    gradient.addColorStop(1, '#93c5fd'); // blue-300

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailyLabels) !!},
            datasets: [{
                label: 'Lượt khám',
                data: {!! json_encode($dailyData) !!},
                backgroundColor: gradient,
                borderRadius: 4,
                borderSkipped: false,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 10,
                    titleFont: { size: 13 },
                    bodyFont: { size: 14, weight: 'bold' },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' lượt khám';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: { color: '#64748b', stepSize: 5 }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { 
                        color: '#64748b',
                        maxRotation: 45,
                        minRotation: 0,
                        callback: function(value, index) {
                            // Only show labels for some days to avoid crowding
                            const label = this.getLabelForValue(value);
                            return index % Math.ceil({!! count($dailyLabels) !!}/7) === 0 ? label.replace('Ngày ', '') : '';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
