{{-- resources/views/admin/rooms/schedule-all.blade.php --}}
@extends('layouts.admin')

@section('title', 'Lịch phân bổ đầy đủ')

@push('styles')
<style>
    .stat-card {
        border-radius: 12px;
        padding: 16px 20px;
        background: #fff;
        border: 1px solid #e0e7ef;
        transition: all 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .schedule-table th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 13px;
    }
    .schedule-table td {
        vertical-align: middle;
        font-size: 14px;
    }
    .filter-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-calendar-week me-2 text-primary"></i>Lịch phân bổ đầy đủ</h4>
            <p class="text-muted small mb-0">Danh sách tất cả các ca làm việc đã phân bổ</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rooms.schedule.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Thêm ca mới
            </a>
            <a href="{{ route('admin.rooms.schedule.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-calendar-day me-1"></i>Xem theo ngày
            </a>
        </div>
    </div>

    {{-- Alerts --}}
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

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Tổng số ca</div>
                        <div class="fs-2 fw-bold text-primary">{{ $stats['total'] }}</div>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Đang hoạt động</div>
                        <div class="fs-2 fw-bold text-success">{{ $stats['active'] }}</div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-play-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Tạm dừng / Đã huỷ</div>
                        <div class="fs-2 fw-bold text-warning">{{ $stats['paused'] + $stats['cancelled'] }}</div>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Tổng slot / Đã đặt</div>
                        <div class="fs-2 fw-bold text-info">{{ $stats['total_booked'] }}/{{ $stats['total_slots'] }}</div>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="filter-section">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Từ ngày</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Đến ngày</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Bác sĩ</label>
                <select name="doctor_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->doctor_id }}" {{ request('doctor_id') == $doctor->doctor_id ? 'selected' : '' }}>
                            {{ $doctor->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Phòng</label>
                <select name="room_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->room_id }}" {{ request('room_id') == $room->room_id ? 'selected' : '' }}>
                            {{ $room->room_code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                            {{ $st }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Lọc
                </button>
            </div>
        </form>
    </div>

    {{-- Bảng danh sách ca --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-list-ul me-2"></i>Danh sách ca làm việc
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover schedule-table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ngày làm việc</th>
                            <th>Giờ</th>
                            <th>Bác sĩ</th>
                            <th>Khoa</th>
                            <th>Phòng</th>
                            <th>Slot</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                        <tr>
                            <td>#{{ $schedule->schedule_id }}</td>
                            <td>
                                <strong>{{ $schedule->work_date->format('d/m/Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ $schedule->work_date->format('l') }}</small>
                            </td>
                            <td>
                                {{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}
                                <br>
                                <small class="text-muted">{{ $schedule->slot_duration }} phút/slot</small>
                            </td>
                            <td>
                                <strong>{{ $schedule->doctor->full_name ?? '—' }}</strong>
                            </td>
                            <td>
                                {{ $schedule->doctor->department->department_name ?? '—' }}
                            </td>
                            <td>
                                @if($schedule->room)
                                    <span class="badge bg-secondary">{{ $schedule->room->room_code }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    <span class="fw-bold text-primary">{{ $schedule->booked_slots }}</span> / {{ $schedule->max_slot }}
                                </div>
                                <div class="progress" style="height: 4px; width: 80px;">
                                    @php $pct = $schedule->max_slot > 0 ? round($schedule->booked_slots / $schedule->max_slot * 100) : 0; @endphp
                                    <div class="progress-bar bg-{{ $pct >= 100 ? 'danger' : ($pct >= 70 ? 'warning' : 'success') }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusBadge = [
                                        'Hoạt động' => 'success',
                                        'Tạm dừng' => 'warning',
                                        'Đã huỷ' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusBadge[$schedule->status] ?? 'secondary' }}">
                                    {{ $schedule->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.rooms.schedule.edit', $schedule) }}"
                                       class="btn btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($schedule->booked_slots == 0)
                                    <form method="POST"
                                          action="{{ route('admin.rooms.schedule.destroy', $schedule) }}"
                                          onsubmit="return confirm('Xoá ca này?')"
                                          class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Xoá">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">Chưa có ca làm việc nào</p>
                                <a href="{{ route('admin.rooms.schedule.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Tạo ca đầu tiên
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($schedules->hasPages())
        <div class="card-footer bg-white">
            {{ $schedules->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection