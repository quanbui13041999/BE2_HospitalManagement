@extends('layouts.admin')

@section('title', 'Báo Cáo Hàng Đợi')

@section('content')
<div class="container-fluid">
    {{-- ══ HEADER ════════════════════════════════════════════════ --}}
    <div class="mb-5">
        <a href="{{ route('admin.queue.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i>Quay Lại
        </a>
        <h1 class="fw-black text-gray-900 text-3xl tracking-tight mb-1">
            <i class="bi bi-bar-chart text-primary me-2"></i>Báo Cáo Hàng Đợi Hàng Ngày
        </h1>
        <p class="text-muted mb-0">Thống kê chi tiết về hoạt động hàng đợi tất cả các phòng khám</p>
    </div>

    {{-- ══ FILTER ════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" class="d-flex gap-3 align-items-end flex-wrap">
                <div>
                    <label class="form-label small fw-bold text-muted mb-2">Chọn Ngày</label>
                    <input type="date" name="date" class="form-control rounded-3" 
                           value="{{ $date }}" 
                           max="{{ today() }}">
                </div>
                <button type="submit" class="btn btn-primary rounded-3">
                    <i class="bi bi-search me-1"></i>Xem Báo Cáo
                </button>
                <a href="{{ route('admin.queue.report', ['date' => today()]) }}" class="btn btn-outline-secondary rounded-3">
                    <i class="bi bi-arrow-clockwise me-1"></i>Hôm Nay
                </a>
            </form>
        </div>
    </div>

    {{-- ══ OVERVIEW STATS ════════════════════════════════════════ --}}
    @php
        $totalTickets = collect($reportData)->sum('total_tickets');
        $totalCompleted = collect($reportData)->sum('completed');
        $totalSkipped = collect($reportData)->sum('skipped');
        $totalCancelled = collect($reportData)->sum('cancelled');
        $avgWaitTime = collect($reportData)->filter(fn($r) => $r['avg_wait_time'])->avg('avg_wait_time');
    @endphp
    
    <div class="row g-4 mb-5">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-list-check fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">Tổng Bệnh Nhân</h6>
                    <h3 class="mb-0 fw-bold text-primary">{{ $totalTickets }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">Hoàn Thành</h6>
                    <h3 class="mb-0 fw-bold text-success">{{ $totalCompleted }}</h3>
                    <small class="text-muted">{{ $totalTickets > 0 ? round(($totalCompleted / $totalTickets) * 100) : 0 }}%</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-exclamation-circle fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">Bỏ Qua</h6>
                    <h3 class="mb-0 fw-bold text-warning">{{ $totalSkipped }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">Trung Bình Chờ</h6>
                    <h3 class="mb-0 fw-bold text-info">{{ round($avgWaitTime ?? 0) }} phút</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ DETAIL TABLE ══════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 py-3 px-4">
            <h5 class="mb-0 fw-bold">📊 Chi Tiết Theo Ca Khám</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Bác Sĩ / Ca</th>
                        <th class="text-center">Tổng</th>
                        <th class="text-center">Hoàn Thành</th>
                        <th class="text-center">Bỏ Qua</th>
                        <th class="text-center">Hủy</th>
                        <th class="text-center">Trung Bình Chờ</th>
                        <th class="text-center">Số Lớn Nhất</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $report)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-gray-800">BS. {{ $report['schedule']->doctor->full_name }}</div>
                            <small class="text-muted">
                                <i class="bi bi-door-open me-1"></i>{{ $report['schedule']->room->room_code ?? '—' }} 
                                • {{ \Carbon\Carbon::parse($report['schedule']->start_time)->format('H:i') }}
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary-subtle text-primary fw-bold">{{ $report['total_tickets'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success fw-bold">{{ $report['completed'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning-subtle text-warning fw-bold">{{ $report['skipped'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-subtle text-danger fw-bold">{{ $report['cancelled'] }}</span>
                        </td>
                        <td class="text-center">
                            <strong class="text-info">{{ round($report['avg_wait_time'] ?? 0) }}</strong> phút
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary">#{{ $report['max_queue_number'] }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.queue.show', $report['schedule']->schedule_id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-arrow-right-short me-1"></i>Chi Tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <p class="mb-2">Không có dữ liệu hàng đợi cho ngày này.</p>
                            <small>Hãy chọn ngày khác hoặc đảm bảo có ca khám đang hoạt động.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══ SUMMARY ════════════════════════════════════════════════ --}}
    <div class="mt-5 p-4 bg-light rounded-4">
        <h5 class="fw-bold text-gray-800 mb-3">📈 Tóm Tắt Báo Cáo</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Ngày báo cáo:</span>
                    <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d/m/Y') }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tổng ca khám:</span>
                    <strong>{{ count($reportData) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Tổng bệnh nhân:</span>
                    <strong>{{ $totalTickets }}</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tỉ lệ hoàn thành:</span>
                    <strong class="text-success">{{ $totalTickets > 0 ? round(($totalCompleted / $totalTickets) * 100) : 0 }}%</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Thời chờ trung bình:</span>
                    <strong class="text-info">{{ round($avgWaitTime ?? 0) }} phút</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Tỉ lệ bỏ qua:</span>
                    <strong class="text-warning">{{ $totalTickets > 0 ? round(($totalSkipped / $totalTickets) * 100) : 0 }}%</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
