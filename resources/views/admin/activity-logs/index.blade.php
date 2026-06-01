@extends('layouts.admin')

@section('title', 'Nhật ký hoạt động')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">Nhật ký hoạt động</h1>
        <div class="text-muted small">Theo dõi ai đã làm gì, thời điểm nào và liên quan tới đối tượng nào.</div>
    </div>
</div>

<div class="bg-white border rounded-3 p-3 mb-3">
    <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-3">
        <div class="col-lg-4">
            <label class="form-label small text-muted">Tìm kiếm</label>
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" maxlength="150"
                   placeholder="Tên, email, hành động, nội dung...">
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label small text-muted">Vai trò</label>
            <select name="role_name" class="form-select">
                <option value="">Tất cả</option>
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['role_name'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label small text-muted">Hành động</label>
            <select name="action" class="form-select">
                <option value="">Tất cả</option>
                @foreach($actions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['action'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label small text-muted">Đối tượng</label>
            <select name="subject_type" class="form-select">
                <option value="">Tất cả</option>
                @foreach($subjectTypes as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['subject_type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label small text-muted">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="">Tất cả</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label small text-muted">Từ ngày</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label small text-muted">Đến ngày</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
        </div>
        <div class="col-md-6 col-lg-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel me-1"></i>Lọc
            </button>
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">Xóa lọc</a>
        </div>
    </form>
</div>

<div class="bg-white border rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:150px">Thời gian</th>
                    <th>Người thực hiện</th>
                    <th style="width:110px">Vai trò</th>
                    <th>Hành động</th>
                    <th style="width:170px">Đối tượng</th>
                    <th style="width:130px">IP</th>
                    <th>Chi tiết</th>
                    <th style="width:90px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="small text-nowrap">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $log->actor_display }}</div>
                            <div class="text-muted small">{{ $log->actor_email_display ?: 'Không có email' }}</div>
                        </td>
                        <td><span class="badge text-bg-light border">{{ $log->role_display }}</span></td>
                        <td>
                            <div>{{ $log->action }}</div>
                            @if($log->status === 'failed')
                                <span class="badge text-bg-danger">Thất bại</span>
                            @endif
                        </td>
                        <td class="small">
                            {{ $log->subject_display }}
                        </td>
                        <td class="small">{{ $log->ip_address ?: '-' }}</td>
                        <td class="small text-muted" style="max-width:360px">
                            {{ \Illuminate\Support\Str::limit($log->description_display, 130) }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.activity-logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">Chưa có nhật ký phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top">
        {{ $logs->links('vendor.pagination.bootstrap-5') }}
    </div>
</div>
@endsection
