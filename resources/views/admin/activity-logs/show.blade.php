@extends('layouts.admin')

@section('title', 'Chi tiết nhật ký')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">Chi tiết nhật ký #{{ $log->log_id }}</h1>
        <div class="text-muted small">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</div>
    </div>
    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="bg-white border rounded-3 p-3 h-100">
            <h2 class="h6 mb-3">Người thực hiện</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Tên</dt>
                <dd class="col-sm-8">{{ $log->actor_display }}</dd>

                <dt class="col-sm-4 text-muted">Vai trò</dt>
                <dd class="col-sm-8">{{ $log->role_display }}</dd>

                <dt class="col-sm-4 text-muted">Email</dt>
                <dd class="col-sm-8">{{ $log->actor_email_display ?: 'Không có email' }}</dd>

                <dt class="col-sm-4 text-muted">IP address</dt>
                <dd class="col-sm-8">{{ $log->ip_address ?: '-' }}</dd>

                <dt class="col-sm-4 text-muted">User agent</dt>
                <dd class="col-sm-8 small">{{ $log->user_agent_display }}</dd>

                <dt class="col-sm-4 text-muted">Trạng thái</dt>
                <dd class="col-sm-8">
                    @if($log->status === 'failed')
                        <span class="badge text-bg-danger">Thất bại</span>
                    @else
                        <span class="badge text-bg-success">Thành công</span>
                    @endif
                </dd>
            </dl>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="bg-white border rounded-3 p-3 h-100">
            <h2 class="h6 mb-3">Hoạt động</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Thời gian</dt>
                <dd class="col-sm-8">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</dd>

                <dt class="col-sm-4 text-muted">Hành động</dt>
                <dd class="col-sm-8">{{ $log->action }}</dd>

                <dt class="col-sm-4 text-muted">Đối tượng</dt>
                <dd class="col-sm-8">
                    {{ $log->subject_display }}
                </dd>

                <dt class="col-sm-4 text-muted">Nội dung</dt>
                <dd class="col-sm-8">{{ $log->description_display }}</dd>
            </dl>
        </div>
    </div>

    <div class="col-12">
        <div class="bg-white border rounded-3 p-3">
            <h2 class="h6 mb-3">Dữ liệu thay đổi</h2>
            @php($changes = $log->metadata['changes'] ?? null)
            @if(is_array($changes) && count($changes))
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Trường</th>
                                <th>Trước</th>
                                <th>Sau</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($changes as $field => $change)
                                <tr>
                                    <td class="fw-semibold">{{ $field }}</td>
                                    <td><code>{{ is_scalar($change['before'] ?? null) ? ($change['before'] ?? 'null') : json_encode($change['before'], JSON_UNESCAPED_UNICODE) }}</code></td>
                                    <td><code>{{ is_scalar($change['after'] ?? null) ? ($change['after'] ?? 'null') : json_encode($change['after'], JSON_UNESCAPED_UNICODE) }}</code></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted">Không có dữ liệu trước/sau hoặc hành động không phải cập nhật.</div>
            @endif
        </div>
    </div>

    <div class="col-12">
        <div class="bg-white border rounded-3 p-3">
            <h2 class="h6 mb-3">Metadata</h2>
            @if($log->metadata)
                <pre class="bg-light border rounded p-3 mb-0 small" style="white-space:pre-wrap">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            @else
                <div class="text-muted">Không có metadata.</div>
            @endif
        </div>
    </div>
</div>
@endsection
