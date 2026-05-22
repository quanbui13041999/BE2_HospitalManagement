@extends('layouts.app')

@section('title', 'Hàng đợi khám bệnh - Quản lý')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-b">
        <div>
            <h1 class="fw-black text-primary text-3xl tracking-tight mb-1">
                <i class="bi bi-collection-play-fill me-2"></i>Hệ thống Hàng Đợi Khám Bệnh
            </h1>
            <p class="text-secondary mb-0">Hôm nay: {{ now()->translatedFormat('l, d/m/Y') }} • Lễ tân kiểm soát ca khám và check-in bệnh nhân</p>
        </div>
        <div>
            <a href="{{ route('queue.manage.checkin') }}" class="btn btn-primary btn-lg rounded-pill shadow px-4">
                <i class="bi bi-person-plus-fill me-2"></i>Check-in Bệnh Nhân Mới
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0 border-start border-success border-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3 fs-4 text-success"></i>
                <div>
                    <strong>Thành công!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        @forelse($schedules as $s)
            <div class="col-xl-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition-all duration-300">
                    <div class="card-header bg-gradient bg-primary text-white border-0 py-3 px-4 rounded-top-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-white/20 rounded-pill px-3 py-2 text-white font-semibold">
                                <i class="bi bi-door-open-fill me-1"></i> Phòng {{ $s->room->room_code ?? '—' }}
                            </span>
                            <span class="badge bg-light text-primary font-bold">
                                {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4 flex flex-col justify-between">
                        <div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar bg-blue-50 text-primary rounded-circle d-flex items-center justify-center me-3" style="width: 52px; height: 52px;">
                                    <i class="bi bi-stethoscope fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 text-gray-800">{{ $s->doctor->full_name }}</h5>
                                    <p class="text-secondary text-xs mb-0 uppercase tracking-wider font-semibold">
                                        {{ $s->doctor->department->department_name ?? 'Khoa Khám Bệnh' }}
                                    </p>
                                </div>
                            </div>

                            <hr class="text-gray-200 my-3">

                            <div class="d-flex justify-content-around text-center py-2 bg-light rounded-3 mb-4">
                                <div>
                                    <span class="d-block text-2xl font-black text-warning">{{ $s->queue_count }}</span>
                                    <small class="text-secondary text-xs font-semibold">Đang chờ</small>
                                </div>
                                <div class="vr text-gray-300"></div>
                                <div>
                                    <span class="d-block text-2xl font-black text-primary">
                                        {{ \App\Models\QueueTicket::forSchedule($s->schedule_id)->where('status', 'completed')->count() }}
                                    </span>
                                    <small class="text-secondary text-xs font-semibold">Đã khám</small>
                                </div>
                                <div class="vr text-gray-300"></div>
                                <div>
                                    <span class="d-block text-2xl font-black text-secondary">
                                        {{ \App\Models\QueueTicket::forSchedule($s->schedule_id)->count() }}
                                    </span>
                                    <small class="text-secondary text-xs font-semibold">Tổng số ca</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('queue.manage.show', $s->schedule_id) }}" class="btn btn-outline-primary rounded-3 py-2 font-bold transition-all duration-200">
                                <i class="bi bi-gear-fill me-2"></i>Quản Lý Hàng Đợi
                            </a>
                            <a href="{{ route('queue.display', $s->schedule_id) }}" target="_blank" class="btn btn-light text-secondary rounded-3 py-2 font-semibold">
                                <i class="bi bi-tv me-2"></i>Mở Màn Hình TV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <div class="p-4 bg-gray-50 rounded-circle mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-calendar-x fs-1 text-secondary"></i>
                    </div>
                    <h4 class="fw-bold text-gray-700">Chưa có ca khám nào đang hoạt động hôm nay</h4>
                    <p class="text-secondary">Vui lòng kiểm tra lại lịch trực của các bác sĩ trong ngày hôm nay.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
