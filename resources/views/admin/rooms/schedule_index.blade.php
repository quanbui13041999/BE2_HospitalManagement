{{-- resources/views/admin/rooms/schedule-index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Phân bổ Ca làm việc')

@push('styles')
<style>
.sch-stat { border-radius:12px; padding:16px 18px; text-align:center; border:1px solid #e0e7ef; }
.sch-stat-val { font-size:24px; font-weight:800; line-height:1; }

.room-sched-card { border-radius:12px; border:1px solid #e0e7ef; overflow:hidden; }
.room-sched-head { padding:12px 16px; display:flex; justify-content:space-between; align-items:center;
                   border-bottom:1px solid #e0e7ef; }
.slot-item { padding:12px 16px; border-bottom:1px solid #f0f4ff; }
.slot-item:last-child { border-bottom:none; }

.progress-slim { height:6px; border-radius:3px; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-calendar3 me-2 text-primary"></i>Phân bổ Ca làm việc</h4>
            <p class="text-muted small mb-0">Quản lý lịch phân ca theo ngày cho từng phòng khám</p>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ $date }}" style="width:175px">
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
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Điều hướng ngày --}}
    @php
        $dateObj  = \Carbon\Carbon::parse($date);
        $prevDate = $dateObj->copy()->subDay()->toDateString();
        $nextDate = $dateObj->copy()->addDay()->toDateString();
    @endphp
    <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
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

    {{-- Stat cards --}}
    @php
        $totalSchedules = $rooms->sum(fn($r) => $r->schedules->count());
        $totalSlots     = $rooms->sum(fn($r) => $r->schedules->sum('max_slot'));
        $totalBooked    = $rooms->sum(fn($r) => $r->schedules->sum('booked_slots'));
        $roomsInUse     = $rooms->filter(fn($r) => $r->schedules->isNotEmpty())->count();
    @endphp
    <div class="row g-3 mb-4">
        @foreach([
            ['val'=>$totalSchedules, 'label'=>'Ca làm việc',   'color'=>'#0D47A1','bg'=>'#E3F2FD','icon'=>'bi-calendar-check'],
            ['val'=>$roomsInUse,     'label'=>'Phòng có ca',   'color'=>'#2e7d32','bg'=>'#E8F5E9','icon'=>'bi-door-open'],
            ['val'=>$totalBooked,    'label'=>'Lượt đặt',      'color'=>'#0288d1','bg'=>'#E1F5FE','icon'=>'bi-people'],
            ['val'=>$totalSlots-$totalBooked,'label'=>'Slot còn trống','color'=>'#f57c00','bg'=>'#FFF3E0','icon'=>'bi-calendar-minus'],
        ] as $s)
        <div class="col-6 col-md-3">
            <div class="sch-stat" style="background:#fff">
                <div style="color:{{ $s['color'] }}; font-size:20px; margin-bottom:6px">
                    <i class="bi {{ $s['icon'] }}"></i>
                </div>
                <div class="sch-stat-val" style="color:{{ $s['color'] }}">{{ $s['val'] }}</div>
                <div class="small text-muted mt-1">{{ $s['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Lưới phòng + ca --}}
    @php
        $statusColors = ['Trống'=>'success','Đang sử dụng'=>'primary','Bảo trì'=>'danger','Vệ sinh'=>'warning'];
    @endphp
    <div class="row g-3">
        @forelse($rooms as $room)
        @php $roomColor = $statusColors[$room->status] ?? 'secondary'; @endphp
        <div class="col-md-6 col-xl-4">
            <div class="room-sched-card bg-white shadow-sm">
                <div class="room-sched-head">
                    <div>
                        <span class="fw-bold">{{ $room->room_name ?? $room->room_code }}</span>
                        <span class="badge bg-info-subtle text-info ms-2">{{ $room->room_type }}</span>
                        @if($room->department)
                            <div class="text-muted small">{{ $room->department->department_name }}</div>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $roomColor }}">{{ $room->status }}</span>
                        <a href="{{ route('admin.rooms.schedule.create', ['room_id' => $room->room_id]) }}"
                           class="btn btn-sm btn-outline-success" title="Thêm ca">
                            <i class="bi bi-plus-circle"></i>
                        </a>
                    </div>
                </div>

                @forelse($room->schedules as $schedule)
                @php
                    $pct      = $schedule->max_slot > 0
                                    ? round($schedule->booked_slots / $schedule->max_slot * 100) : 0;
                    $barColor = $pct >= 100 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
                @endphp
                <div class="slot-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="fw-semibold">
                                <i class="bi bi-clock text-muted me-1"></i>
                                {{ substr($schedule->start_time,0,5) }} – {{ substr($schedule->end_time,0,5) }}
                            </span>
                            <span class="badge ms-2 {{ $schedule->status==='Hoạt động' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}"
                                  style="font-size:10px">
                                {{ $schedule->status }}
                            </span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.rooms.schedule.edit', $schedule) }}"
                               class="btn btn-outline-primary btn-sm" title="Sửa">
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
                        <i class="bi bi-person-badge me-1"></i>{{ $schedule->doctor->full_name ?? '—' }}
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Đã đặt: <strong class="text-{{ $barColor }}">{{ $schedule->booked_slots }}</strong> / {{ $schedule->max_slot }}</span>
                        <span class="text-muted">{{ $pct }}%</span>
                    </div>
                    <div class="progress progress-slim">
                        <div class="progress-bar bg-{{ $barColor }}" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-calendar-x d-block fs-4 mb-1"></i>
                    Chưa có ca làm việc
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">Không có phòng nào trong hệ thống.</div>
        </div>
        @endforelse
    </div>
</div>
@endsection
