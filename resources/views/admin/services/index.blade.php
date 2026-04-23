{{-- resources/views/admin/services/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quản lý Dịch vụ & Bảng giá')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Quản lý Dịch vụ & Bảng giá</h4>
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Thêm dịch vụ
        </a>
    </div>

    {{-- Alert --}}
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
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Tìm mã / tên dịch vụ..."
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
                    <select name="status" class="form-select">
                        <option value="">-- Trạng thái --</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Vô hiệu</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-fill">
                        <i class="bi bi-search me-1"></i>Tìm kiếm
                    </button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng dịch vụ --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã DV</th>
                            <th>Tên dịch vụ</th>
                            <th>Khoa</th>
                            <th>Thời gian (phút)</th>
                            <th>Giá hiện hành</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                        <tr>
                            <td><code>{{ $service->service_code }}</code></td>
                            <td>
                                <a href="{{ route('admin.services.show', $service) }}" class="text-decoration-none fw-semibold">
                                    {{ $service->service_name }}
                                </a>
                            </td>
                            <td>{{ $service->department->department_name ?? '—' }}</td>
                            <td>{{ $service->duration_minutes }} phút</td>
                            <td>
                                @if($service->activePrices->isEmpty())
                                    <span class="text-muted fst-italic">Chưa có giá</span>
                                @else
                                    @foreach($service->activePrices as $p)
                                        <div class="small">
                                            <span class="badge bg-secondary">{{ $p->price_type }}</span>
                                            {{ number_format($p->price, 0, ',', '.') }} đ
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if($service->status)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger">Vô hiệu</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.services.show', $service) }}"
                                       class="btn btn-outline-info" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.services.edit', $service) }}"
                                       class="btn btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.services.toggle-status', $service) }}"
                                          class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-outline-{{ $service->status ? 'warning' : 'success' }}"
                                                title="{{ $service->status ? 'Vô hiệu hoá' : 'Kích hoạt' }}">
                                            <i class="bi bi-{{ $service->status ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy dịch vụ nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($services->hasPages())
        <div class="card-footer">
            {{ $services->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
