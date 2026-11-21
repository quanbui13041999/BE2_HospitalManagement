@extends('layouts.admin')

@section('title', 'Quản Lý Hàng Đợi')

@section('content')
<style>
    .badge-priority {
        background-color: var(--badge-bg-color, #6b7280);
        color: white;
    }
</style>
<div class="container-fluid">
    {{-- ══ HEADER ════════════════════════════════════════════════ --}}
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h1 class="fw-black text-gray-900 text-3xl tracking-tight mb-2">
                    <i class="bi bi-collection-play-fill text-primary me-2"></i>
                    @if($userRole === 1)
                        Quản Lý Hàng Đợi - Admin (Tất Cả Chức Năng)
                    @elseif($userRole === 2)
                        Hàng Đợi Khám Bệnh - Bác Sĩ
                    @elseif($userRole === 4)
                        Quản Lý Check-in - Lễ Tân
                    @elseif($userRole === 5)
                        Thống Kê Hàng Đợi - Dược Sĩ
                    @else
                        Hàng Đợi Khám Bệnh
                    @endif
                </h1>
                <p class="text-muted mb-0">Hôm nay: {{ now()->translatedFormat('l, d/m/Y') }} • Theo dõi tổng quan hàng đợi tại tất cả phòng khám</p>
            </div>
            <span class="badge bg-primary px-4 py-3 rounded-pill fw-bold text-white">
                👤 {{ ucfirst($userRoleName) }}
            </span>
        </div>
    </div>

    {{-- ══ STATISTICS CARDS ══════════════════════════════════════ --}}
    <div class="row g-4 mb-5">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">Đang Chờ</h6>
                    <h3 class="mb-0 fw-bold text-warning">{{ $totalStats['total_waiting'] }}</h3>
                    <small class="text-muted">bệnh nhân</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-play-circle fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">Đang Khám</h6>
                    <h3 class="mb-0 fw-bold text-info">{{ $totalStats['total_in_progress'] }}</h3>
                    <small class="text-muted">bệnh nhân</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">Đã Hoàn Thành</h6>
                    <h3 class="mb-0 fw-bold text-success">{{ $totalStats['total_completed'] }}</h3>
                    <small class="text-muted">bệnh nhân</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-calendar3 fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">Tổng Ngày Hôm Nay</h6>
                    <h3 class="mb-0 fw-bold text-primary">{{ $totalStats['total_today'] }}</h3>
                    <small class="text-muted">bệnh nhân</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ CA KHÁM HÔM NAY ════════════════════════════════════════ --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-gray-800 text-base">📋 Ca Khám Đang Hoạt Động Hôm Nay</h4>
                @if($userRole === 1 || $userRole === 4)
                <a href="{{ route('queue.manage.checkin') }}" class="btn btn-sm btn-primary rounded-3">
                    <i class="bi bi-person-plus-fill me-1"></i>Check-in Bệnh Nhân
                </a>
                @endif
            </div>
        </div>

        @forelse($schedules as $schedule)
        <div class="col-12 col-lg-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 hover-shadow transition-all duration-300">
                <div class="card-header bg-gradient bg-primary text-white border-0 py-3 px-4 rounded-top">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="mb-1 fw-bold text-sm">
                                <i class="bi bi-stethoscope me-1"></i>BS. {{ $schedule->doctor->full_name }}
                            </h6>
                            <small class="text-blue-100">{{ $schedule->doctor->department->department_name ?? 'Khoa Khám' }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3 pb-3 border-bottom border-gray-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted font-semibold uppercase">Phòng</small>
                            <span class="badge bg-secondary rounded-pill">{{ $schedule->room->room_code ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted font-semibold uppercase">Giờ Khám</small>
                            <span class="badge bg-light text-dark">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</span>
                        </div>
                    </div>

                    {{-- Queue Statistics --}}
                    <div class="row g-2 mb-4">
                        <div class="col-4 text-center">
                            <div class="p-2 bg-warning-subtle rounded-3">
                                <div class="fw-bold text-warning text-lg">{{ $schedule->queue_stats['total_waiting'] }}</div>
                                <small class="text-muted text-xs">Chờ</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2 bg-info-subtle rounded-3">
                                <div class="fw-bold text-info text-lg">{{ $schedule->queue_stats['total_in_progress'] ?? 0 }}</div>
                                <small class="text-muted text-xs">Khám</small>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2 bg-success-subtle rounded-3">
                                <div class="fw-bold text-success text-lg">{{ $schedule->queue_stats['total_completed'] }}</div>
                                <small class="text-muted text-xs">Xong</small>
                            </div>
                        </div>
                    </div>

                    {{-- Current Ticket (if exists) --}}
                    @if($schedule->current_ticket)
                    <div class="alert alert-info bg-info-subtle border-0 p-3 mb-3 rounded-3 d-flex gap-2" role="alert">
                        <i class="bi bi-megaphone-fill text-info mt-1 flex-shrink-0"></i>
                        <div class="text-sm">
                            <strong class="d-block">Đang gọi: #{{ $schedule->current_ticket['queue_number'] ?? $schedule->current_ticket->queue_number }}</strong>
                            <small>{{ $schedule->current_ticket['patient_name'] ?? $schedule->current_ticket->patient_name }}</small>
                        </div>
                    </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.queue.show', $schedule->schedule_id) }}" class="btn btn-outline-primary btn-sm rounded-3 py-2 fw-600">
                            <i class="bi bi-arrow-right-short me-1"></i>Xem Chi Tiết
                        </a>
                        @if($userRole === 1 || $userRole === 4)
                        <a href="{{ route('queue.manage.show', $schedule->schedule_id) }}" class="btn btn-warning btn-sm rounded-3 py-2 fw-600">
                            <i class="bi bi-person-badge me-1"></i>Quản Lý Lễ Tân
                        </a>
                        @endif
                        @if($userRole === 1 || $userRole === 2)
                        <a href="{{ route('queue.doctor.index') }}" class="btn btn-success btn-sm rounded-3 py-2 fw-600">
                            <i class="bi bi-stethoscope me-1"></i>Bảng Điều Khiển Bác Sĩ
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning bg-warning-subtle border-0 py-4 px-4 rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Chưa có ca khám nào hoạt động.</strong> 
                @if($userRole === 2)
                    Hôm nay bạn không có lịch trực.
                @else
                    Hãy thêm lịch trực bác sĩ trước.
                @endif
            </div>
        </div>
        @endforelse
    </div>

    @if($schedules->isNotEmpty() && $userRole === 1)
    {{-- ══ RECENT COMPLETED ══════════════════════════════════════ --}}
    <div class="mt-5 pt-4">
        <h4 class="fw-bold text-gray-800 mb-3 text-base">✅ Bệnh Nhân Hoàn Thành Gần Đây</h4>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th class="ps-4">Số TT</th>
                            <th>Bệnh Nhân</th>
                            <th>Bác Sĩ / Ca</th>
                            <th>Thời Gian</th>
                            <th>Đối Tượng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCompleted as $ticket)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-secondary text-white fw-bold text-lg" style="min-width: 40px; text-align: center;">
                                    #{{ $ticket->queue_number }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-600 small">{{ $ticket->patient_name }}</div>
                                <div class="text-muted text-xs">{{ $ticket->patient_phone ?? '—' }}</div>
                            </td>
                            <td>
                                <div class="small">BS. {{ $ticket->schedule->doctor->full_name ?? '—' }}</div>
                                <div class="text-muted text-xs">{{ \Carbon\Carbon::parse($ticket->schedule->start_time)->format('H:i') }}</div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    {{ $ticket->completed_at ? \Carbon\Carbon::parse($ticket->completed_at)->format('H:i:s') : '—' }}
                                </small>
                            </td>
                            <td>
                                @php
                                    $badgeBg = match($ticket->priority) {
                                        'emergency' => '#ef4444',
                                        'disabled' => '#a855f7',
                                        'elderly' => '#3b82f6',
                                        default => '#6b7280'
                                    };
                                @endphp
                                <span class="badge badge-priority" style="--badge-bg-color: {{ $badgeBg }};">
                                    {{ $ticket->priority_icon }} {{ $ticket->priority_label }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Chưa có bệnh nhân hoàn thành.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="mt-4 d-flex gap-3">
            <a href="{{ route('admin.queue.report') }}" class="btn btn-outline-secondary">
                <i class="bi bi-bar-chart me-2"></i>Xem Báo Cáo Chi Tiết
            </a>
        </div>
    </div>
    @endif
</div>

@if($userRole === 1)
<script>
    // Auto-refresh stats every 10 seconds for Admin only
    setInterval(function() {
        location.reload();
    }, 10000);
</script>
@endif
@endsection
