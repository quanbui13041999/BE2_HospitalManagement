@extends('layouts.admin')

@section('title', 'Danh sách Vắc xin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-shield-plus me-2 text-primary"></i>Quản lý Vắc xin</h4>
            <p class="text-muted small mb-0">Danh sách các loại vắc xin tiêm chủng</p>
        </div>
        <div>
            <a href="{{ route('admin.vaccines.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Thêm vắc xin
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tên Vắc xin</th>
                            <th>Nhà sản xuất</th>
                            <th class="text-center">Số mũi yêu cầu</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vaccines as $vaccine)
                        <tr>
                            <td>#{{ $vaccine->vaccine_id }}</td>
                            <td class="fw-semibold text-primary">{{ $vaccine->vaccine_name }}</td>
                            <td>{{ $vaccine->manufacturer ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">{{ $vaccine->doses_required }} mũi</span>
                            </td>
                            <td class="text-center">
                                @if($vaccine->status)
                                    <span class="badge bg-success-subtle text-success">Sẵn sàng</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Tạm ngưng</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.vaccines.edit', $vaccine) }}" class="btn btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.vaccines.destroy', $vaccine) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa vắc xin này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu vắc xin.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 pt-3">
            {{ $vaccines->links() }}
        </div>
    </div>
</div>
@endsection
