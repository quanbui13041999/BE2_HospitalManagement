{{-- resources/views/admin/rooms/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Chi tiết phòng: ' . ($room->room_name ?? $room->room_code))

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0 me-auto">
            {{ $room->room_name ?? $room->room_code }}
            <small class="text-muted fs-6 ms-2">({{ $room->room_code }})</small>
        </h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rooms.schedule.create', ['room_id' => $room->room_id]) }}"
               class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i>Thêm ca
            </a>
            <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Sửa phòng
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Thông tin phòng --}}
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2"></i>Thông tin phòng</div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Mã phòng</td>
                            <td><code>{{ $room->room_code }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Khoa</td>
                            <td>{{ $room->department->department_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Loại phòng</td>
                            <td><span class="badge bg-info-subtle text-info">{{ $room->room_type }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Trạng thái</td>
                            <td>
                                @php
                                    $colors = ['Trống'=>'success','Đang sử dụng'=>'primary','Bảo trì'=>'danger','Vệ sinh'=>'warning'];
                                    $c = $colors[$room->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $c }}">{{ $room->status }}</span>
                            </td>
                        </tr>
                        @if($room->notes)
                        <tr>
                            <td class="text-muted">Ghi chú</td>
                            <td>{{ $room->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Lịch ca trực --}}
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                    <span><i class="bi bi-calendar-week me-2"></i>Ca làm việc tại phòng này</span>
                    <form method="GET" class="d-flex gap-2">
                        <input type="date" name="date" class="form-control form-control-sm"
                               value="{{ request('date', now()->toDateString()) }}" style="width:160px">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Lọc</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày</th>
                                    <th>Giờ</th>
                                    <th>Bác sĩ</th>
                                    <th>Slot tối đa</th>
                                    <th>Đã đặt</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->work_date->format('d/m/Y') }}</td>
                                    <td>{{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}</td>
                                    <td>
                                        <strong>{{ $schedule->doctor->full_name }}</strong>
                                        <div class="small text-muted">{{ $schedule->doctor->department->department_name ?? '' }}</div>
                                    </td>
                                    <td class="text-center">{{ $schedule->max_slot }}</td>
                                    <td class="text-center">
                                        <span class="{{ $schedule->booked_slots >= $schedule->max_slot ? 'text-danger fw-bold' : 'text-success' }}">
                                            {{ $schedule->booked_slots }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $sc = ['Hoạt động'=>'success','Tạm dừng'=>'warning','Đã huỷ'=>'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $sc[$schedule->status] ?? 'secondary' }}">
                                            {{ $schedule->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.rooms.schedule.edit', $schedule) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($schedule->booked_slots === 0)
                                        <form method="POST"
                                              action="{{ route('admin.rooms.schedule.destroy', $schedule) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Xoá ca này?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Không có ca làm việc nào trong ngày được chọn.
                                    </td>
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
@endsection
