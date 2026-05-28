@extends($layout)

@section('title', 'Chi tiết thông báo')

@section('content')
<div class="container py-4">
    <div class="mb-3">
        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
                <div>
                    <span class="badge text-bg-primary mb-2">{{ $notification->displayType() }}</span>
                    <h1 class="h3 mb-0">{{ $notification->title }}</h1>
                </div>
                <div class="text-muted text-end">
                    <div>Đã gửi lúc</div>
                    <strong>{{ optional($notification->created_at)->format('d/m/Y H:i') }}</strong>
                </div>
            </div>

            <div class="border-top pt-4">
                <div class="notification-detail-content">
                    {!! nl2br(e($notification->displayMessage())) !!}
                </div>
            </div>

            <dl class="row mt-4 mb-0">
                <dt class="col-sm-3">Người gửi</dt>
                <dd class="col-sm-9">{{ $notification->sender?->full_name ?? 'Hệ thống' }}</dd>

                <dt class="col-sm-3">Phạm vi</dt>
                <dd class="col-sm-9">
                    @switch($notification->target_type)
                        @case('all')
                            Toàn hệ thống
                            @break
                        @case('role')
                            Role: {{ $notification->target_role }}
                            @break
                        @case('users')
                            Nhiều người dùng
                            @break
                        @default
                            Cá nhân
                    @endswitch
                </dd>

                @if($notification->related_type || $notification->ref_type)
                    <dt class="col-sm-3">Đối tượng liên quan</dt>
                    <dd class="col-sm-9">
                        {{ $notification->related_type ?: $notification->ref_type }}
                        #{{ $notification->related_id ?: $notification->ref_id }}
                    </dd>
                @endif
            </dl>

            @if($notification->relatedUrl())
                <div class="mt-4">
                    <a class="btn btn-primary" href="{{ $notification->relatedUrl() }}">
                        Mở chức năng liên quan
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
