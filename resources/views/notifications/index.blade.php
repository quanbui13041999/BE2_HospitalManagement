@extends($layout)

@section('title', 'Thông báo')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Thông báo</h1>
            <div class="text-muted">Danh sách thông báo dành cho tài khoản hiện tại.</div>
        </div>
        <button class="btn btn-primary" type="button" data-notification-mark-all>
            <i class="bi bi-check2-all me-1"></i> Đánh dấu tất cả là đã đọc
        </button>
    </div>

    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="all" @selected($status === 'all')>Tất cả</option>
                <option value="unread" @selected($status === 'unread')>Chưa đọc</option>
                <option value="read" @selected($status === 'read')>Đã đọc</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Loại thông báo</label>
            <select name="type" class="form-select">
                <option value="">Tất cả loại</option>
                @foreach($types as $item)
                    <option value="{{ $item }}" @selected($type === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-primary w-100" type="submit">
                <i class="bi bi-funnel me-1"></i> Lọc
            </button>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                @php($isRead = $notification->isReadBy(Auth::user()))
                <div class="list-group-item py-3 {{ $isRead ? '' : 'bg-primary-subtle' }}">
                    <div class="d-flex gap-3">
                        <div class="pt-1">
                            <span class="d-inline-block rounded-circle {{ $isRead ? 'bg-secondary-subtle' : 'bg-primary' }}" style="width:10px;height:10px;"></span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <h2 class="h6 mb-1 {{ $isRead ? '' : 'fw-bold' }}">
                                    <a class="text-decoration-none text-reset" href="{{ route('notifications.show', $notification) }}">
                                        {{ $notification->title }}
                                    </a>
                                </h2>
                                <small class="text-muted">{{ optional($notification->created_at)->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="text-muted mb-2">{{ Str::limit($notification->displayMessage(), 150) }}</div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge text-bg-light border">{{ $notification->displayType() }}</span>
                                    <span class="badge {{ $isRead ? 'text-bg-secondary' : 'text-bg-primary' }}">
                                        {{ $isRead ? 'Đã đọc' : 'Chưa đọc' }}
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    @unless($isRead)
                                        <button class="btn btn-sm btn-outline-primary"
                                                type="button"
                                                data-notification-mark-read="{{ route('notifications.mark-read', $notification) }}">
                                            Đánh dấu đã đọc
                                        </button>
                                    @endunless
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('notifications.show', $notification) }}">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                    Chưa có thông báo phù hợp.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
