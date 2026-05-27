@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    {{-- ══ STATS CARDS ════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <span class="badge bg-success-subtle text-success">+12%</span>
                    </div>
                    <h6 class="text-muted mb-1">Tổng bệnh nhân</h6>
                    <h3 class="mb-0 fw-bold">{{ number_format($stats['total_patients']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-calendar-check fs-4"></i>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">{{ $stats['pending_appointments'] }} Chờ duyệt</span>
                    </div>
                    <h6 class="text-muted mb-1">Lịch hôm nay</h6>
                    <h3 class="mb-0 fw-bold">{{ $stats['appointments_today'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <span class="text-muted small">Hôm nay: {{ number_format($stats['revenue_today']) }}đ</span>
                    </div>
                    <h6 class="text-muted mb-1">Tổng doanh thu</h6>
                    <h3 class="mb-0 fw-bold">{{ number_format($stats['total_revenue']) }}đ</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">Tổng bác sĩ</h6>
                    <h3 class="mb-0 fw-bold">{{ $stats['total_doctors'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ══ RECENT APPOINTMENTS ══════════════════════════════════════ --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Lịch hẹn gần đây</h5>
                    <a href="{{ route('admin.rooms.weekly') }}" class="btn btn-sm btn-light text-primary fw-600">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th class="ps-4">Bệnh nhân</th>
                                    <th>Bác sĩ</th>
                                    <th>Thời gian</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAppointments as $appt)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                                {{ substr($appt->user->full_name ?? 'P', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-600 small">{{ $appt->user->full_name ?? 'Ẩn danh' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">#{{ $appt->appointment_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-500">BS. {{ $appt->schedule->doctor->full_name ?? '---' }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i d/m') }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($appt->status) {
                                                'Chờ xác nhận' => 'bg-warning-subtle text-warning',
                                                'Đã xác nhận'  => 'bg-info-subtle text-info',
                                                'Hoàn thành'   => 'bg-success-subtle text-success',
                                                'Đã hủy'       => 'bg-danger-subtle text-danger',
                                                default        => 'bg-secondary-subtle text-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill" style="font-size: 10px;">{{ $appt->status }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Chưa có lịch hẹn nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ RECENT NEWS (BẢN TIN) ════════════════════════════════════ --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Bản tin mới nhất</h5>
                    <div>
                        <a href="{{ route('admin.news.index') }}" class="btn btn-sm btn-light text-primary fw-600">Quản lý</a>
                        <a href="{{ route('admin.rehab.index') }}" class="btn btn-sm btn-outline-primary ms-2">Quản lý phục hồi</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentNews as $news)
                        <div class="list-group-item border-0 px-4 py-3">
                            <div class="d-flex gap-3">
                                @if($news->thumbnail)
                                <img src="{{ $news->thumbnail_url }}" class="rounded shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-newspaper text-muted"></i>
                                </div>
                                @endif
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold small text-truncate mb-1">{{ $news->title }}</div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-light text-dark border" style="font-size: 9px;">{{ $news->category }}</span>
                                        <small class="text-muted" style="font-size: 10px;">{{ $news->created_at->diffForHumans() }}</small>
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

        {{-- ══ RECENT PAYMENTS ══════════════════════════════════════════ --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Giao dịch gần đây</h5>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-light text-primary fw-600">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th class="ps-4">Mã GD</th>
                                    <th>Bệnh nhân</th>
                                    <th>Số tiền</th>
                                    <th>Phương thức</th>
                                    <th>Trạng thái</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $pay)
                                <tr>
                                    <td class="ps-4"><code class="small fw-bold">{{ $pay->transaction_ref ?? '---' }}</code></td>
                                    <td>
                                        <div class="small fw-500">{{ $pay->appointment->user->full_name ?? 'Ẩn danh' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small">{{ number_format($pay->total_amount) }}đ</div>
                                    </td>
                                    <td>
                                        <span class="small">{{ $pay->method }}</span>
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
                                        <span class="badge {{ $pBadge }}" style="font-size: 9px;">{{ $pay->status }}</span>
                                    </td>
                                    <td>
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($pay->payment_date)->format('H:i d/m/Y') }}</div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Chưa có giao dịch nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-600 { font-weight: 600; }
    .fw-500 { font-weight: 500; }
    .bg-light-subtle { background-color: #f8f9fa; }
    .card { transition: transform 0.2s; }
    .card:hover { transform: translateY(-2px); }
</style>
@endsection
