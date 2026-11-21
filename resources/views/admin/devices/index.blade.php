@extends('layouts.admin')

@section('title', 'Thiết bị')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-pc-display-horizontal me-2 text-primary"></i>Thiết bị</h4>
            <p class="text-muted small mb-0">Danh sách thiết bị do admin quản lý</p>
        </div>
        <a href="{{ route('admin.devices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Thêm thiết bị
        </a>
    </div>

    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        @endif
    @endforeach

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã</th>
                            <th>Tên thiết bị</th>
                            <th>Danh mục</th>
                            <th>Ngày mua</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Phiên bản</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                            <tr>
                                <td><span class="badge bg-light text-dark border">{{ $device->code }}</span></td>
                                <td class="fw-semibold">{{ $device->name }}</td>
                                <td>{{ $device->type?->name ?: '—' }}</td>
                                <td>{{ $device->purchase_date?->format('d/m/Y') ?: '—' }}</td>
                                <td class="text-center">
                                    @php
                                        $badge = [
                                            'active' => 'success',
                                            'broken' => 'danger',
                                            'maintenance' => 'warning text-dark',
                                        ][$device->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ \App\Models\Device::STATUSES[$device->status] ?? $device->status }}</span>
                                </td>
                                <td class="text-center text-muted">#{{ $device->lock_version }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-outline-primary" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.devices.destroy', $device->id) }}" method="POST" data-confirm="Bạn có chắc muốn xóa thiết bị này?">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="lock_version" value="{{ $device->lock_version }}">
                                            <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Chưa có thiết bị.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 pt-3">
            {{ $devices->links() }}
        </div>
    </div>
</div>
@endsection
