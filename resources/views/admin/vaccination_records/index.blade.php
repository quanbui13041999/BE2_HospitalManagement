@extends('layouts.admin')

@section('title', 'Hồ sơ tiêm chủng')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-journal-medical me-2 text-primary"></i>Hồ sơ tiêm chủng</h4>
            <p class="text-muted small mb-0">Quản lý lịch sử tiêm chủng của bệnh nhân</p>
        </div>
        <div>
            <a href="{{ route('admin.vaccination-records.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Thêm hồ sơ
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('admin.vaccination-records.index') }}" method="GET" class="d-flex gap-2">
                <select name="status" class="form-select" style="max-width: 200px;">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Đã tiêm" {{ request('status') == 'Đã tiêm' ? 'selected' : '' }}>Đã tiêm</option>
                    <option value="Chưa tiêm" {{ request('status') == 'Chưa tiêm' ? 'selected' : '' }}>Chưa tiêm</option>
                    <option value="Đã hủy" {{ request('status') == 'Đã hủy' ? 'selected' : '' }}>Đã hủy</option>
                </select>
                <button type="submit" class="btn btn-primary">Lọc</button>
                @if(request('status'))
                    <a href="{{ route('admin.vaccination-records.index') }}" class="btn btn-outline-secondary">Xóa lọc</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Bệnh nhân</th>
                            <th>Vắc xin</th>
                            <th>Bác sĩ chỉ định</th>
                            <th class="text-center">Mũi thứ</th>
                            <th>Ngày tiêm</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td>#{{ $record->vaccination_id }}</td>
                            <td class="fw-semibold text-primary">{{ $record->user->full_name ?? '—' }}</td>
                            <td>{{ $record->vaccine->vaccine_name ?? '—' }}</td>
                            <td>{{ $record->doctor->full_name ?? '—' }}</td>
                            <td class="text-center"><span class="badge bg-info text-dark">{{ $record->dose_number }}</span></td>
                            <td>{{ $record->administered_at ? $record->administered_at->format('d/m/Y H:i') : '—' }}</td>
                            <td class="text-center">
                                @if($record->status == 'Đã tiêm')
                                    <span class="badge bg-success-subtle text-success">Đã tiêm</span>
                                @elseif($record->status == 'Chưa tiêm')
                                    <span class="badge bg-warning-subtle text-warning">Chưa tiêm</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $record->status }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.vaccination-records.edit', $record) }}" class="btn btn-outline-primary" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.vaccination-records.destroy', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa hồ sơ này?');">
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
                            <td colspan="8" class="text-center text-muted py-4">Chưa có hồ sơ tiêm chủng.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 pt-3">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
