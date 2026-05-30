@extends('layouts.admin')

@section('title', 'Dashboard & Quản lý Bản tin')

@push('styles')
<style>
    /* Premium Theme Color Settings matching welcome page */
    :root {
        --accent: #0A6EBD;
        --accent-light: #E8F3FC;
        --accent-dark: #074B83;
    }
    
    .text-accent { color: var(--accent) !important; }
    .bg-accent-light { background-color: var(--accent-light) !important; }
    .btn-accent {
        background-color: var(--accent) !important;
        border-color: var(--accent) !important;
        color: #fff !important;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .btn-accent:hover {
        background-color: var(--accent-dark) !important;
        border-color: var(--accent-dark) !important;
        transform: translateY(-1px);
    }
    .btn-outline-accent {
        color: var(--accent) !important;
        border-color: var(--accent) !important;
        background-color: transparent !important;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .btn-outline-accent:hover {
        background-color: var(--accent-light) !important;
        color: var(--accent-dark) !important;
    }
    
    .card-kpi {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }
    .card-kpi:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(10, 110, 189, 0.08);
    }
    
    /* Navigation Tabs */
    .dashboard-tabs {
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 24px;
        gap: 8px;
    }
    .dashboard-tabs .nav-link {
        border: none !important;
        color: #64748b;
        font-weight: 600;
        font-size: 15px;
        padding: 12px 20px;
        position: relative;
        transition: all 0.2s;
    }
    .dashboard-tabs .nav-link:hover {
        color: var(--accent);
    }
    .dashboard-tabs .nav-link.active {
        color: var(--accent) !important;
        background: transparent !important;
    }
    .dashboard-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: var(--accent);
        border-radius: 3px 3px 0 0;
    }

    /* Table styles */
    .table-news {
        border-radius: 12px;
        overflow: hidden;
    }
    .table-news thead {
        background-color: #f8fafc;
    }
    
    /* Chart and stats style */
    .chart-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        background: #fff;
    }
    .chart-container {
        position: relative;
        height: 280px;
        width: 100%;
    }
    .time-range-btn {
        font-size: 12px;
        padding: 6px 16px;
        border-radius: 20px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .time-range-btn.active {
        background-color: var(--accent) !important;
        color: #fff !important;
        border-color: var(--accent) !important;
    }
    .fw-600 { font-weight: 600; }
    .fw-500 { font-weight: 500; }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">

    {{-- ══ NOTIFICATION MESSAGES ════════════════════════════════════ --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ══ DASHBOARD NAVIGATION TABS (3 TABS) ════════════════════════ --}}
    <ul class="nav dashboard-tabs" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity-panel" type="button" role="tab">
                <i class="bi bi-activity me-2"></i>Tổng quan hoạt động
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="news-tab" data-bs-toggle="tab" data-bs-target="#news-panel" type="button" role="tab">
                <i class="bi bi-newspaper me-2"></i>Quản lý Bản tin
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats-panel" type="button" role="tab">
                <i class="bi bi-bar-chart-line me-2"></i>Báo cáo & Phân tích
            </button>
        </li>
    </ul>

    {{-- ══ TAB PANELS CONTENT ═══════════════════════════════════════ --}}
    <div class="tab-content" id="dashboardTabsContent">
        
        {{-- ══ TAB 1: OPERATIONAL ACTIVITY OVERVIEW (OLD /ADMIN FEATURES) ══════ --}}
        <div class="tab-pane fade show active" id="activity-panel" role="tabpanel" aria-labelledby="activity-tab">
            
            {{-- Stats Cards Row --}}
            <div class="row g-4 mb-4">
                <!-- KPI 1: Bệnh nhân -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-kpi">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: var(--accent-light);">
                                    <i class="bi bi-people fs-4 text-accent"></i>
                                </div>
                                <span class="badge bg-success-subtle text-success font-medium" style="font-size: 11px;">
                                    +12% tháng này
                                </span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size: 13px; font-weight: 500;">Tổng bệnh nhân</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($stats['total_patients']) }}</h3>
                        </div>
                    </div>
                </div>

                <!-- KPI 2: Lịch hôm nay -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-kpi">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #E8F8EE;">
                                    <i class="bi bi-calendar-check fs-4 text-success"></i>
                                </div>
                                <span class="badge bg-warning-subtle text-warning font-medium" style="font-size: 11px;">
                                    {{ $stats['pending_appointments'] }} Chờ xác nhận
                                </span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size: 13px; font-weight: 500;">Lịch hôm nay</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['appointments_today'] }}</h3>
                        </div>
                    </div>
                </div>

                <!-- KPI 3: Doanh thu -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-kpi">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #FEF3E8;">
                                    <i class="bi bi-wallet2 fs-4" style="color: #D97706;"></i>
                                </div>
                                <span class="badge font-medium text-muted" style="font-size: 10px; background-color: #FEF3E8;">
                                    Hôm nay: {{ number_format($stats['revenue_today']) }}đ
                                </span>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size: 13px; font-weight: 500;">Tổng doanh thu</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($stats['total_revenue']) }}đ</h3>
                        </div>
                    </div>
                </div>

                <!-- KPI 4: Bác sĩ -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-kpi">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #F0EBFE;">
                                    <i class="bi bi-person-badge fs-4" style="color: #7C3AED;"></i>
                                </div>
                            </div>
                            <h6 class="text-muted mb-1" style="font-size: 13px; font-weight: 500;">Tổng bác sĩ</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['total_doctors'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent operational tables row --}}
            <div class="row g-4 mb-4">
                {{-- Lịch hẹn gần đây --}}
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">Lịch hẹn gần đây</h5>
                            <a href="{{ route('admin.rooms.weekly') }}" class="btn btn-sm btn-outline-accent">Xem tất cả</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light-subtle">
                                        <tr class="text-muted" style="font-size: 13px;">
                                            <th class="ps-4">Bệnh nhân</th>
                                            <th>Bác sĩ</th>
                                            <th>Thời gian</th>
                                            <th class="pe-4">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentAppointments as $appt)
                                        <tr style="font-size: 14px;">
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 13px; color: var(--accent); font-weight:600;">
                                                        {{ substr($appt->user->full_name ?? 'P', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $appt->user->full_name ?? 'Ẩn danh' }}</div>
                                                        <div class="text-muted" style="font-size: 11px;">#{{ $appt->appointment_id }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-500 text-dark">BS. {{ $appt->schedule->doctor->full_name ?? '---' }}</div>
                                            </td>
                                            <td>
                                                <div class="text-muted">{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i d/m/Y') }}</div>
                                            </td>
                                            <td class="pe-4">
                                                @php
                                                    $badgeClass = match($appt->status) {
                                                        'Chờ xác nhận' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                                        'Đã xác nhận'  => 'bg-info-subtle text-info border border-info-subtle',
                                                        'Hoàn thành'   => 'bg-success-subtle text-success border border-success-subtle',
                                                        'Đã hủy'       => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                        default        => 'bg-secondary-subtle text-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1.5" style="font-size: 11px;">{{ $appt->status }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">Chưa có lịch hẹn gần đây nào.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bản tin mới nhất --}}
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">Bản tin mới nhất</h5>
                            <button class="btn btn-sm btn-outline-accent" onclick="document.getElementById('news-tab').click()">Quản lý</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse($recentNews as $rNews)
                                <div class="list-group-item border-0 px-4 py-3">
                                    <div class="d-flex gap-3">
                                        @if($rNews->thumbnail)
                                        <img src="{{ $rNews->thumbnail_url }}" class="rounded shadow-sm" style="width: 55px; height: 55px; object-fit: cover;">
                                        @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                                            <i class="bi bi-newspaper text-muted fs-5"></i>
                                        </div>
                                        @endif
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-bold text-dark text-truncate mb-1" style="font-size: 13px;" title="{{ $rNews->title }}">{{ $rNews->title }}</div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">{{ $rNews->category }}</span>
                                                <small class="text-muted" style="font-size: 11px;">{{ $rNews->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5">
                                    <i class="bi bi-journal-x fs-1 text-muted"></i>
                                    <p class="text-muted small mt-2">Chưa có bản tin nào.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Giao dịch gần đây --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">Giao dịch gần đây</h5>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-accent">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr class="text-muted" style="font-size: 13px;">
                                    <th class="ps-4">Mã giao dịch</th>
                                    <th>Bệnh nhân</th>
                                    <th>Số tiền</th>
                                    <th>Phương thức</th>
                                    <th>Trạng thái</th>
                                    <th class="pe-4">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $pay)
                                <tr style="font-size: 14px;">
                                    <td class="ps-4 py-3"><code class="fw-bold text-accent">#{{ $pay->transaction_ref ?? '---' }}</code></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $pay->appointment->user->full_name ?? 'Ẩn danh' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ number_format($pay->total_amount) }}đ</div>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $pay->method }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $pBadge = match($pay->status) {
                                                'Thành công', 'Đã thanh toán' => 'bg-success text-white',
                                                'Chờ xử lý', 'Chờ thanh toán' => 'bg-warning text-dark',
                                                'Thất bại' => 'bg-danger text-white',
                                                default => 'bg-secondary text-white'
                                            };
                                        @endphp
                                        <span class="badge {{ $pBadge }} px-3 py-1.5 rounded-pill" style="font-size: 11px;">{{ $pay->status }}</span>
                                    </td>
                                    <td class="pe-4 text-muted">
                                        {{ \Carbon\Carbon::parse($pay->payment_date)->format('H:i d/m/Y') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Chưa có giao dịch gần đây.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══ TAB 2: NEWS MANAGEMENT PANEL ═════════════════════════════ --}}
        <div class="tab-pane fade" id="news-panel" role="tabpanel" aria-labelledby="news-tab">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">Danh sách Bản tin bệnh viện</h5>
                        <p class="text-muted mb-0 small">Soạn thảo, quản lý bài viết và gửi thông tin y tế cho bệnh nhân.</p>
                    </div>
                    <a href="{{ route('admin.news.create') }}" class="btn btn-accent px-4">
                        <i class="bi bi-plus-lg me-2"></i>Tạo bài viết mới
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-news table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted" style="font-size: 13px; font-weight: 600;">
                                    <th class="ps-4 py-3">ID</th>
                                    <th class="py-3">Bài viết</th>
                                    <th class="py-3">Chuyên mục</th>
                                    <th class="py-3">Trạng thái</th>
                                    <th class="py-3">Email y khoa</th>
                                    <th class="py-3">Ngày đăng</th>
                                    <th class="py-3">Tác giả</th>
                                    <th class="text-end pe-4 py-3">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($news as $item)
                                <tr style="font-size: 14px;">
                                    <td class="ps-4 fw-600 text-muted">#{{ $item->news_id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item->thumbnail)
                                                <img src="{{ $item->thumbnail_url }}" class="rounded shadow-sm" width="50" height="50" style="object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="bi bi-file-image text-muted fs-5"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 250px;" title="{{ $item->title }}">
                                                    {{ $item->title }}
                                                </div>
                                                <span class="text-muted" style="font-size: 11px;">
                                                    <i class="bi bi-clock me-1"></i>Tạo: {{ $item->created_at->format('H:i d/m/Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill" style="font-size: 11px;">
                                            {{ $item->category }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.news.toggle', $item->news_id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            @if($item->is_published)
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" style="font-size: 11px; font-weight:600;">
                                                    <i class="bi bi-check-circle me-1"></i>Đã đăng
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 11px; font-weight:600;">
                                                    <i class="bi bi-pencil-square me-1"></i>Bản nháp
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                    <td>
                                        @if($item->email_sent)
                                            <span class="text-success small fw-bold"><i class="bi bi-envelope-check-fill me-1"></i> Đã gửi</span>
                                        @else
                                            <form action="{{ route('admin.news.sendEmail', $item->news_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-accent rounded-pill px-3" {{ !$item->is_published ? 'disabled' : '' }} style="font-size: 11px;">
                                                    <i class="bi bi-send me-1"></i> Gửi bệnh nhân
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $item->published_at ? $item->published_at->format('d/m/Y') : '---' }}</td>
                                    <td class="fw-500 text-dark">{{ $item->author->full_name ?? 'Admin y tế' }}</td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-2">
                                            <a href="{{ route('admin.news.edit', $item->news_id) }}" class="btn btn-sm btn-light border" title="Chỉnh sửa">
                                                <i class="bi bi-pencil text-primary"></i>
                                            </a>
                                            <form action="{{ route('admin.news.destroy', $item->news_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border" title="Xóa">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-newspaper fs-1 text-muted d-block mb-2"></i>
                                        Chưa có bài viết bản tin nào được tạo.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($news->hasPages())
                    <div class="card-footer bg-white border-0 py-3 px-4">
                        {{ $news->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══ TAB 3: DETAILED CHARTS & STATS PANEL ══════════════════════ --}}
        <div class="tab-pane fade" id="stats-panel" role="tabpanel" aria-labelledby="stats-tab">
            
            {{-- Time range selectors --}}
            <div class="mb-4 d-flex justify-content-between align-items-center bg-white p-3 rounded-4 shadow-sm border-0">
                <span class="fw-bold text-dark"><i class="bi bi-funnel me-2 text-accent"></i>Khoảng thời gian báo cáo</span>
                <div class="flex gap-2">
                    <button class="btn time-range-btn {{ $timeRange === 'week' ? 'active' : '' }}" onclick="setTimeRange('week')">
                        7 ngày qua
                    </button>
                    <button class="btn time-range-btn {{ $timeRange === 'month' ? 'active' : '' }}" onclick="setTimeRange('month')">
                        30 ngày qua
                    </button>
                    <button class="btn time-range-btn {{ $timeRange === 'year' ? 'active' : '' }}" onclick="setTimeRange('year')">
                        1 năm qua
                    </button>
                </div>
            </div>

            <!-- Charts Row 1: Daily appointments and trend -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="card chart-card p-4">
                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 16px;">Biểu đồ lượt khám theo ngày</h5>
                        <div class="chart-container">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card chart-card p-4">
                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 16px;">Xu hướng đặt lịch & Hoàn thành</h5>
                        <div class="chart-container">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2: Distributions (Specialty, Status, Age) -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <div class="card chart-card p-4">
                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 16px;">Phân bố theo khoa phòng</h5>
                        <div class="chart-container">
                            <canvas id="specialtyChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card chart-card p-4">
                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 16px;">Trạng thái lịch hẹn y khoa</h5>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card chart-card p-4">
                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 16px;">Phân bố độ tuổi bệnh nhân</h5>
                        <div class="chart-container">
                            <canvas id="ageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 3: Patient Type and Satisfaction Trend -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="card chart-card p-4">
                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 16px;">Bệnh nhân mới vs Bệnh nhân quay lại</h5>
                        <div class="chart-container">
                            <canvas id="patientTypeChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card chart-card p-4">
                        <h5 class="fw-bold mb-3 text-dark" style="font-size: 16px;">Xu hướng mức độ hài lòng</h5>
                        <div class="chart-container">
                            <canvas id="satisfactionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specialty Wait Time and Doctor Ratings progress indicators -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="card chart-card p-4 h-100">
                        <h5 class="fw-bold mb-4 text-dark" style="font-size: 16px;">Thời gian chờ TB theo Chuyên khoa</h5>
                        <div class="space-y-4">
                            @forelse($waitTimeData['specialties'] as $key => $specialty)
                                @php $maxWait = max(array_merge($waitTimeData['wait_times'], [1])); @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-sm fw-500 text-dark" style="font-size: 14px;">{{ $specialty }}</span>
                                        <span class="text-sm text-accent fw-bold" style="font-size: 14px;">{{ $waitTimeData['wait_times'][$key] }} phút</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 4px; background-color: #f1f5f9;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $maxWait > 0 ? min(($waitTimeData['wait_times'][$key] / $maxWait) * 100, 100) : 0 }}%; background-color: var(--accent); border-radius: 4px;"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center py-4">Chưa có đủ dữ liệu thời gian chờ.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card chart-card p-4 h-100">
                        <h5 class="fw-bold mb-4 text-dark" style="font-size: 16px;">Mức độ hài lòng theo Bác sĩ</h5>
                        <div class="space-y-4">
                            @forelse($satisfactionByDoctor as $doctor)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-sm fw-500 text-dark" style="font-size: 14px;">{{ $doctor['full_name'] }}</span>
                                        <span class="text-sm fw-bold" style="color: #D97706; font-size: 14px;">{{ $doctor['rating'] }} ⭐</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 4px; background-color: #f1f5f9;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($doctor['rating'] / 5) * 100 }}%; border-radius: 4px;"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center py-4">Chưa có dữ liệu đánh giá bác sĩ.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Rating Doctors and Hot Doctor of the Week -->
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card chart-card p-4">
                        <h5 class="fw-bold mb-4 text-dark d-flex align-items-center gap-2" style="font-size: 16px;">
                            <i class="bi bi-award-fill text-warning fs-5"></i>
                            Bảng xếp hạng bác sĩ đánh giá cao
                        </h5>
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead>
                                    <tr class="text-muted border-bottom" style="font-size: 13px;">
                                        <th class="py-2">Thứ hạng</th>
                                        <th class="py-2">Bác sĩ</th>
                                        <th class="py-2">Chuyên khoa</th>
                                        <th class="py-2">Đánh giá</th>
                                        <th class="py-2">Lượt nhận xét</th>
                                        <th class="py-2 text-end">Kinh nghiệm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topDoctors as $index => $doctor)
                                        <tr style="font-size: 14px;" class="border-bottom-0">
                                            <td class="py-3">
                                                <span class="fs-5">
                                                    @if($index === 0) 🥇
                                                    @elseif($index === 1) 🥈
                                                    @elseif($index === 2) 🥉
                                                    @else <span class="badge bg-light text-dark">#{{ $index + 1 }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if(!empty($doctor['avatar_url']))
                                                        <img src="{{ $doctor['avatar_url'] }}" alt="Doctor" class="rounded-circle object-cover shadow-sm" width="38" height="38">
                                                    @else
                                                        <div class="rounded-circle bg-primary-subtle text-accent d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                                                            {{ substr($doctor['full_name'], 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <span class="fw-bold text-dark">{{ $doctor['full_name'] }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 text-muted">{{ $doctor['department_name'] }}</td>
                                            <td class="py-3">
                                                <span class="fw-bold text-dark">{{ $doctor['rating'] }}</span>
                                                <span class="text-warning">⭐</span>
                                            </td>
                                            <td class="py-3 text-muted">{{ $doctor['review_count'] }} nhận xét</td>
                                            <td class="py-3 text-end fw-500 text-dark">{{ $doctor['experience'] }} năm</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Chưa có xếp hạng bác sĩ.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card chart-card p-4 text-center">
                        <h5 class="fw-bold mb-4 text-dark text-start d-flex align-items-center gap-2" style="font-size: 16px;">
                            <i class="bi bi-lightning-charge-fill text-danger fs-5"></i>
                            Bác sĩ Hot nhất tuần
                        </h5>
                        @if(!empty($topDoctorWeek))
                            <div class="py-3">
                                @if(!empty($topDoctorWeek['avatar_url']))
                                    <img src="{{ $topDoctorWeek['avatar_url'] }}" alt="Doctor" class="rounded-circle object-cover shadow mx-auto mb-3" width="90" height="90">
                                @else
                                    <div class="rounded-circle bg-primary-subtle text-accent d-flex align-items-center justify-content-center shadow mx-auto mb-3" style="width: 90px; height: 90px; font-size: 32px; font-weight:700;">
                                        {{ substr($topDoctorWeek['full_name'], 0, 1) }}
                                    </div>
                                @endif
                                <h4 class="fw-bold text-dark mb-1">{{ $topDoctorWeek['full_name'] }}</h4>
                                <p class="text-muted small mb-4">Khoa khám bệnh y tế</p>
                                
                                <div class="bg-accent-light rounded-4 p-3 mb-3 border border-1 border-primary-subtle">
                                    <span class="d-block text-muted small" style="font-weight: 500;">SỐ CA LÀM VIỆC TUẦN NÀY</span>
                                    <h2 class="fw-bold text-accent mb-0 mt-1">{{ $topDoctorWeek['appointment_count'] }}</h2>
                                    <span class="text-accent small fw-bold">lượt đăng ký khám thành công</span>
                                </div>
                            </div>
                        @else
                            <div class="py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                Chưa có đủ lượt đăng ký khám tuần này.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Keep active tab state on redirect or interaction
    document.addEventListener("DOMContentLoaded", function() {
        var hash = window.location.hash;
        if (hash === '#stats-panel' || hash === '#stats') {
            var tabEl = document.querySelector('#stats-tab');
            if (tabEl) {
                var tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
        } else if (hash === '#news-panel' || hash === '#news') {
            var tabEl = document.querySelector('#news-tab');
            if (tabEl) {
                var tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
        } else if (hash === '#activity-panel' || hash === '#activity') {
            var tabEl = document.querySelector('#activity-tab');
            if (tabEl) {
                var tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
        }
    });

    // Time range switcher
    function setTimeRange(range) {
        window.location.href = '{{ route("admin.news.index") }}?time_range=' + range + '#stats';
    }

    // Chart.js Color Configurations matching Welcome page blue accent
    const CHART_COLORS = {
        blue:   '#0A6EBD',
        green:  '#10b981',
        red:    '#ef4444',
        yellow: '#f59e0b',
        purple: '#8b5cf6',
        cyan:   '#06b6d4',
        orange: '#f97316',
    };

    const defaultOptions = (extra = {}) => ({
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    font: {
                        family: "'DM Sans', sans-serif",
                        size: 11
                    }
                }
            }
        },
        ...extra,
    });

    // ── 1. Daily appointments bar chart ──
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailyData['labels']) !!},
            datasets: [
                {
                    label: 'Tổng số ca đặt',
                    data: {!! json_encode(array_map(fn($d) => $d['total'], $dailyData['data'])) !!},
                    backgroundColor: CHART_COLORS.blue,
                    borderRadius: 4,
                },
                {
                    label: 'Đã hoàn thành',
                    data: {!! json_encode(array_map(fn($d) => $d['completed'], $dailyData['data'])) !!},
                    backgroundColor: CHART_COLORS.green,
                    borderRadius: 4,
                },
                {
                    label: 'Đã hủy',
                    data: {!! json_encode(array_map(fn($d) => $d['cancelled'], $dailyData['data'])) !!},
                    backgroundColor: CHART_COLORS.red,
                    borderRadius: 4,
                },
            ],
        },
        options: defaultOptions({ 
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            } 
        }),
    });

    // ── 2. Trend line chart ──
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyData['labels']) !!},
            datasets: [
                {
                    label: 'Lượt đăng ký',
                    data: {!! json_encode(array_map(fn($d) => $d['total'], $dailyData['data'])) !!},
                    borderColor: CHART_COLORS.blue,
                    backgroundColor: 'rgba(10, 110, 189, 0.05)',
                    tension: 0.35,
                    fill: true,
                    borderWidth: 2,
                },
                {
                    label: 'Lượt hoàn thành',
                    data: {!! json_encode(array_map(fn($d) => $d['completed'], $dailyData['data'])) !!},
                    borderColor: CHART_COLORS.green,
                    backgroundColor: 'transparent',
                    tension: 0.35,
                    fill: false,
                    borderWidth: 2,
                },
            ],
        },
        options: defaultOptions({ 
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            } 
        }),
    });

    // ── 3. Specialty pie chart ──
    new Chart(document.getElementById('specialtyChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($specialtyData['labels']) !!},
            datasets: [{
                data: {!! json_encode($specialtyData['data']) !!},
                backgroundColor: [
                    CHART_COLORS.blue,
                    CHART_COLORS.green,
                    CHART_COLORS.yellow,
                    CHART_COLORS.purple,
                    CHART_COLORS.cyan,
                    CHART_COLORS.orange,
                    '#64748b'
                ],
            }],
        },
        options: defaultOptions({ 
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 8 }
                } 
            } 
        }),
    });

    // ── 4. Status doughnut chart ──
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusData['labels']) !!},
            datasets: [{
                data: {!! json_encode($statusData['data']) !!},
                backgroundColor: [
                    CHART_COLORS.orange, // Chờ xác nhận
                    CHART_COLORS.cyan,   // Đã xác nhận
                    CHART_COLORS.blue,   // Đang khám
                    CHART_COLORS.green,  // Hoàn thành
                    CHART_COLORS.red,    // Đã hủy
                ],
                borderWidth: 2,
            }],
        },
        options: defaultOptions({ 
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 8 }
                } 
            },
            cutout: '65%'
        }),
    });

    // ── 5. Age distribution doughnut chart ──
    new Chart(document.getElementById('ageChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($ageData['labels']) !!},
            datasets: [{
                data: {!! json_encode($ageData['data']) !!},
                backgroundColor: [
                    CHART_COLORS.purple,
                    CHART_COLORS.blue,
                    CHART_COLORS.green,
                    CHART_COLORS.yellow,
                    CHART_COLORS.orange,
                ],
                borderWidth: 2,
            }],
        },
        options: defaultOptions({ 
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 8 }
                } 
            },
            cutout: '65%'
        }),
    });

    // ── 6. Patient type trend line chart ──
    new Chart(document.getElementById('patientTypeChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($patientTrendData['labels']) !!},
            datasets: [
                {
                    label: 'Bệnh nhân mới',
                    data: {!! json_encode(array_map(fn($d) => $d['new'], $patientTrendData['data'])) !!},
                    borderColor: CHART_COLORS.green,
                    backgroundColor: 'rgba(16,185,129,0.06)',
                    tension: 0.35,
                    fill: true,
                    borderWidth: 2,
                },
                {
                    label: 'Bệnh nhân quay lại',
                    data: {!! json_encode(array_map(fn($d) => $d['returning'], $patientTrendData['data'])) !!},
                    borderColor: CHART_COLORS.cyan,
                    backgroundColor: 'rgba(6,182,212,0.06)',
                    tension: 0.35,
                    fill: true,
                    borderWidth: 2,
                },
            ],
        },
        options: defaultOptions({ 
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            } 
        }),
    });

    // ── 7. Satisfaction trend line chart ──
    new Chart(document.getElementById('satisfactionChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($satisfactionTrendData['labels']) !!},
            datasets: [{
                label: 'Điểm đánh giá TB',
                data: {!! json_encode($satisfactionTrendData['data']) !!},
                borderColor: CHART_COLORS.yellow,
                backgroundColor: 'rgba(245,158,11,0.08)',
                tension: 0.35,
                fill: true,
                borderWidth: 2.5,
            }],
        },
        options: defaultOptions({
            scales: {
                y: {
                    min: 0,
                    max: 5,
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            },
        }),
    });
</script>
@endpush
