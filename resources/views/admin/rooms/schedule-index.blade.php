{{-- resources/views/admin/rooms/schedule-index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Phân bổ Ca làm việc')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Phân bổ Ca làm việc</h4>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ $date ?? date('Y-m-d') }}" style="width:180px">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i> Xem
                </button>
            </form>
            <a href="{{ route('admin.rooms.schedule.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Thêm ca
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Thanh điều hướng ngày --}}
    <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
        @php
            $dateObj = \Carbon\Carbon::parse($date ?? date('Y-m-d'));
            $prevDate = $dateObj->copy()->subDay()->toDateString();
            $nextDate = $dateObj->copy()->addDay()->toDateString();
        @endphp
        <a href="{{ route('admin.rooms.schedule.index', ['date' => $prevDate]) }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-chevron-left"></i> Hôm trước
        </a>
        <span class="fw-semibold fs-5">
            {{ $dateObj->isoFormat('dddd, DD/MM/YYYY') }}
            @if($dateObj->isToday())
                <span class="badge bg-primary ms-2">Hôm nay</span>
            @endif
        </span>
        <a href="{{ route('admin.rooms.schedule.index', ['date' => $nextDate]) }}"
           class="btn btn-outline-secondary btn-sm">
            Hôm sau <i class="bi bi-chevron-right"></i>
        </a>
    </div>

    {{-- Thống kê nhanh --}}
    @php
        $totalSchedules = isset($rooms) ? $rooms->sum(fn($r) => $r->schedules->count()) : 0;
        $totalSlots     = isset($rooms) ? $rooms->sum(fn($r) => $r->schedules->sum('max_slot')) : 0;
        $totalBooked    = isset($rooms) ? $rooms->sum(fn($r) => $r->schedules->sum('booked_slots')) : 0;
        $roomsInUse     = isset($rooms) ? $rooms->filter(fn($r) => $r->schedules->isNotEmpty())->count() : 0;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-primary-subtle">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-primary">{{ $totalSchedules }}</div>
                    <div class="small text-muted">Ca làm việc</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-success-subtle">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-success">{{ $roomsInUse }}</div>
                    <div class="small text-muted">Phòng có ca</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-info-subtle">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-info">{{ $totalBooked }}</div>
                    <div class="small text-muted">Lượt đặt</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-warning-subtle">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-warning">{{ $totalSlots - $totalBooked }}</div>
                    <div class="small text-muted">Slot còn trống</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lưới phòng --}}
    @if(isset($rooms) && $rooms->count() > 0)
    <div class="row g-3">
        @foreach($rooms as $room)
        @php
            $statusColors = [
                'Trống'        => 'success',
                'Đang sử dụng' => 'primary',
                'Bảo trì'      => 'danger',
                'Vệ sinh'      => 'warning',
            ];
            $roomColor = $statusColors[$room->status] ?? 'secondary';
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm h-100">
                {{-- Header phòng --}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">{{ $room->room_name ?? $room->room_code }}</span>
                        <span class="badge bg-info-subtle text-info ms-2">{{ $room->room_type }}</span>
                        @if($room->department)
                            <div class="small text-muted">{{ $room->department->department_name }}</div>
                        @endif
                    </div>
                    <span class="badge bg-{{ $roomColor }}">{{ $room->status }}</span>
                </div>
                <div class="card-body p-0">
                    @forelse($room->schedules as $schedule)
                    @php
                        $pct = $schedule->max_slot > 0
                            ? round($schedule->booked_slots / $schedule->max_slot * 100)
                            : 0;
                        $barColor = $pct >= 100 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
                    @endphp
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <i class="bi bi-clock text-muted me-1"></i>
                                <span class="fw-semibold">
                                    {{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}
                                </span>
                                <span class="badge bg-{{ $schedule->status === 'Hoạt động' ? 'success' : 'secondary' }} ms-1">
                                    {{ $schedule->status }}
                                </span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.rooms.schedule.edit', $schedule) }}"
                                   class="btn btn-outline-primary btn-sm" title="Sửa ca">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($schedule->booked_slots === 0)
                                <form method="POST"
                                      action="{{ route('admin.rooms.schedule.destroy', $schedule) }}"
                                      onsubmit="return confirm('Xoá ca này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Xoá">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-person-badge me-1"></i>
                            {{ $schedule->doctor->full_name ?? '—' }}
                        </div>
                        {{-- Thanh tiến độ slot --}}
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Đã đặt: <strong class="text-{{ $barColor }}">{{ $schedule->booked_slots }}</strong> / {{ $schedule->max_slot }}</span>
                            <span class="text-muted">{{ $pct }}%</span>
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar bg-{{ $barColor }}" style="width:{{ $pct }}%"></div>
                        </div>
                        @if($schedule->note)
                        <div class="small text-muted mt-1">
                            <i class="bi bi-chat"></i> {{ $schedule->note }}
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="p-3 text-center text-muted small">
                        <i class="bi bi-calendar-x me-1"></i>Chưa có ca làm việc
                    </div>
                    @endforelse
                </div>
                <div class="card-footer p-2 text-end">
                    <a href="{{ route('admin.rooms.schedule.create', ['room_id' => $room->room_id]) }}"
                       class="btn btn-sm btn-outline-success">
                        <i class="bi bi-plus-circle me-1"></i>Thêm ca cho phòng này
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle me-2"></i>
        Không có phòng nào trong hệ thống. Vui lòng <a href="{{ route('admin.rooms.create') }}">thêm phòng</a> trước khi phân bổ ca.
    </div>
    @endif
</div>
@endsection