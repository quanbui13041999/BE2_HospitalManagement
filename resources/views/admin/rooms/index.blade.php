{{-- resources/views/admin/rooms/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quản lý Phòng khám')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-hospital me-2"></i>Quản lý Phòng khám</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rooms.schedule.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-calendar3 me-1"></i>Phân bổ ca
            </a>
            <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Thêm phòng
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Bộ lọc --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control"
                           placeholder="Tìm mã / tên phòng..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="department_id" class="form-select">
                        <option value="">-- Tất cả khoa --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}"
                                {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="room_type" class="form-select">
                        <option value="">-- Loại phòng --</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type }}" {{ request('room_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">-- Trạng thái --</option>
                        @foreach($roomStatuses as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-fill">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng phòng --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã phòng</th>
                            <th>Tên phòng</th>
                            <th>Khoa</th>
                            <th>Loại phòng</th>
                            <th>Trạng thái</th>
                            <th>Ghi chú</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                        @php
                            $statusColors = [
                                'Trống'          => 'success',
                                'Đang sử dụng'   => 'primary',
                                'Bảo trì'        => 'danger',
                                'Vệ sinh'        => 'warning',
                            ];
                            $color = $statusColors[$room->status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td><code>{{ $room->room_code }}</code></td>
                            <td>
                                <a href="{{ route('admin.rooms.show', $room) }}" class="text-decoration-none fw-semibold">
                                    {{ $room->room_name ?? $room->room_code }}
                                </a>
                            </td>
                            <td>{{ $room->department->department_name ?? '—' }}</td>
                            <td><span class="badge bg-info-subtle text-info">{{ $room->room_type }}</span></td>
                            <td>
                                {{-- Dropdown đổi trạng thái nhanh --}}
                                <form method="POST" action="{{ route('admin.rooms.update-status', $room) }}">
                                    @csrf @method('PATCH')
                                    <select name="status" class="form-select form-select-sm border-{{ $color }} text-{{ $color }}"
                                            style="width:auto" onchange="this.form.submit()">
                                        @foreach($roomStatuses as $st)
                                            <option value="{{ $st }}" {{ $room->status === $st ? 'selected' : '' }}>
                                                {{ $st }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="small text-muted">{{ $room->notes ?? '—' }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.rooms.show', $room) }}"
                                       class="btn btn-outline-info" title="Xem lịch">
                                        <i class="bi bi-calendar3"></i>
                                    </a>
                                    <a href="{{ route('admin.rooms.edit', $room) }}"
                                       class="btn btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('admin.rooms.schedule.create', ['room_id' => $room->room_id]) }}"
                                       class="btn btn-outline-success" title="Thêm ca">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy phòng khám nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rooms->hasPages())
        <div class="card-footer">{{ $rooms->links() }}</div>
        @endif
    </div>
</div>
@endsection
