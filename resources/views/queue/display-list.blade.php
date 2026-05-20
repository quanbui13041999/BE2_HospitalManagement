@extends('layouts.user')

@section('title', 'Chọn Màn Hình Hiển Thị Hàng Đợi')

@section('content')
<div class="container py-5">
    {{-- ══ HEADER ════════════════════════════════════════════════ --}}
    <div class="mb-5">
        <h1 class="fw-bold text-dark text-2xl tracking-tight mb-2">
            <i class="bi bi-tv text-primary me-2"></i>Chọn Màn Hình Hiển Thị Hàng Đợi
        </h1>
        <p class="text-muted mb-0">Hôm nay: {{ now()->translatedFormat('l, d/m/Y') }} • Chọn ca khám để xem màn hình hiển thị số thứ tự</p>
    </div>

    {{-- ══ SCHEDULES GRID ════════════════════════════════════════ --}}
    <div class="row g-4">
        @forelse($schedules as $schedule)
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('queue.display', $schedule->schedule_id) }}" target="_blank" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-shadow transition-all duration-300" style="cursor: pointer;">
                    <div class="card-header bg-primary text-white border-0 py-3 px-4">
                        <h6 class="mb-1 fw-bold text-sm">
                            <i class="bi bi-stethoscope me-2"></i>BS. {{ $schedule->doctor->full_name }}
                        </h6>
                        <small class="text-primary-light">{{ $schedule->doctor->department->department_name ?? 'Khoa Khám' }}</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4 pb-4 border-bottom border-light">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted font-semibold">Phòng</small>
                                <span class="badge bg-secondary rounded-pill text-sm fw-bold">{{ $schedule->room->room_code ?? '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted font-semibold">Giờ Khám</small>
                                <span class="badge bg-light text-dark fw-bold text-sm">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small">Nhấp để xem màn hình</span>
                            <i class="bi bi-arrow-right-circle text-primary"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning bg-warning-subtle border-0 py-4 px-4 rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Không có ca khám nào hoạt động hôm nay.</strong> Vui lòng quay lại sau.
            </div>
        </div>
        @endforelse
    </div>

    {{-- ══ INSTRUCTIONS ════════════════════════════════════════ --}}
    @if($schedules->isNotEmpty())
    <div class="mt-5 pt-4">
        <div class="alert alert-info bg-info-subtle border-0 p-4 rounded-3">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>💡 Hướng dẫn:</strong> Nhấp vào bất kỳ ca khám nào để mở màn hình hiển thị hàng đợi trên một tab mới. 
            Màn hình này sẽ hiển thị số thứ tự bệnh nhân đang gọi, bệnh nhân đang khám và danh sách chờ cập nhật theo thời gian thực.
        </div>
    </div>
    @endif
</div>
@endsection
