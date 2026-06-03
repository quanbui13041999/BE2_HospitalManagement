@extends('layouts.admin')

@section('title', 'Danh mục thiết bị')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-tags me-2 text-primary"></i>Danh mục thiết bị</h4>
            <p class="text-muted small mb-0">Quản lý các nhóm thiết bị trong bệnh viện</p>
        </div>
        <a href="{{ route('admin.device-types.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Thêm danh mục
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
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th class="text-center">Số thiết bị</th>
                            <th class="text-center">Phiên bản</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td class="fw-semibold">{{ $type->name }}</td>
                                <td class="text-muted">{{ $type->description ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">{{ $type->devices_count }}</span>
                                </td>
                                <td class="text-center text-muted">#{{ $type->lock_version }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.device-types.edit', $type->id) }}" class="btn btn-outline-primary" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.device-types.destroy', $type->id) }}" method="POST" data-confirm="Bạn có chắc muốn xóa danh mục thiết bị này?">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="lock_version" value="{{ $type->lock_version }}">
                                            <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Chưa có danh mục thiết bị.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 pt-3">
            {{ $types->links() }}
        </div>
    </div>
</div>
@endsection
