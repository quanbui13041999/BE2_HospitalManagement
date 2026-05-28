@extends($layout)

@section('title', 'Thông báo')

@section('content')
@php
    if (!function_exists('notificationIconClass')) {
        function notificationIconClass($type) {
            $value = mb_strtolower((string) $type, 'UTF-8');

            if (str_contains($value, 'appointment') || str_contains($value, 'lịch') || str_contains($value, 'hẹn')) {
                return ['bi-calendar-check', 'notification-icon-appointment'];
            }
            if (str_contains($value, 'payment') || str_contains($value, 'thanh toán') || str_contains($value, 'hóa đơn')) {
                return ['bi-credit-card', 'notification-icon-payment'];
            }
            if (str_contains($value, 'news') || str_contains($value, 'bản tin')) {
                return ['bi-newspaper', 'notification-icon-news'];
            }
            if (str_contains($value, 'treatment') || str_contains($value, 'nhắc') || str_contains($value, 'thuốc')) {
                return ['bi-capsule-pill', 'notification-icon-treatment'];
            }
            if (str_contains($value, 'system') || str_contains($value, 'khẩn') || str_contains($value, 'cảnh báo')) {
                return ['bi-exclamation-triangle', 'notification-icon-alert'];
            }

            return ['bi-bell', 'notification-icon-default'];
        }
    }

    $unreadOnPage = $notifications->getCollection()
        ->filter(fn ($item) => ! $item->isReadBy(Auth::user()))
        ->count();
@endphp

<div class="notifications-page">
    <div class="notifications-shell">
        <div class="notifications-header">
            <div>
                <div class="notifications-eyebrow">Trung tâm thông báo</div>
                <h1>Thông báo của bạn</h1>
                <p>Theo dõi lịch hẹn, thanh toán, bản tin và các cập nhật từ bệnh viện.</p>
            </div>

            <div class="notifications-summary-card">
                <div class="summary-number">{{ $unreadOnPage }}</div>
                <div class="summary-label">Chưa đọc trên trang này</div>
            </div>
        </div>

        <div class="notifications-toolbar">
            <form method="GET" class="notifications-filter">
                <div class="filter-field">
                    <label for="notification-status">Trạng thái</label>
                    <select id="notification-status" name="status" class="form-select">
                        <option value="all" @selected($status === 'all')>Tất cả</option>
                        <option value="unread" @selected($status === 'unread')>Chưa đọc</option>
                        <option value="read" @selected($status === 'read')>Đã đọc</option>
                    </select>
                </div>

                <div class="filter-field filter-field-wide">
                    <label for="notification-type">Loại thông báo</label>
                    <select id="notification-type" name="type" class="form-select">
                        <option value="">Tất cả loại</option>
                        @foreach($types as $item)
                            <option value="{{ $item }}" @selected($type === $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="btn btn-outline-primary filter-button" type="submit">
                    <i class="bi bi-funnel me-1"></i> Lọc
                </button>
            </form>

            <button class="btn btn-primary mark-all-button" type="button" data-notification-mark-all>
                <i class="bi bi-check2-all me-1"></i> Đánh dấu tất cả đã đọc
            </button>
        </div>

        <div class="notifications-list">
            @forelse($notifications as $notification)
                @php
                    $isRead = $notification->isReadBy(Auth::user());
                    [$icon, $iconClass] = notificationIconClass($notification->displayType());
                @endphp

                <article class="notification-card {{ $isRead ? 'is-read' : 'is-unread' }}">
                    <div class="notification-icon {{ $iconClass }}">
                        <i class="bi {{ $icon }}"></i>
                    </div>

                    <div class="notification-main">
                        <div class="notification-topline">
                            <div class="notification-tags">
                                <span class="notification-type">{{ $notification->displayType() }}</span>
                                <span class="notification-status {{ $isRead ? 'read' : 'unread' }}">
                                    {{ $isRead ? 'Đã đọc' : 'Chưa đọc' }}
                                </span>
                            </div>
                            <time>{{ optional($notification->created_at)->format('d/m/Y H:i') }}</time>
                        </div>

                        <h2>
                            <a href="{{ route('notifications.show', $notification) }}">
                                {{ $notification->title }}
                            </a>
                        </h2>

                        <p>{{ Str::limit($notification->displayMessage(), 180) }}</p>

                        <div class="notification-actions">
                            @unless($isRead)
                                <button class="btn btn-sm btn-light mark-read-button"
                                        type="button"
                                        data-notification-mark-read="{{ route('notifications.mark-read', $notification) }}">
                                    <i class="bi bi-check2 me-1"></i> Đánh dấu đã đọc
                                </button>
                            @endunless

                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('notifications.show', $notification) }}">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="notifications-empty">
                    <div class="empty-icon"><i class="bi bi-bell-slash"></i></div>
                    <h2>Chưa có thông báo</h2>
                    <p>Không có thông báo nào phù hợp với bộ lọc hiện tại.</p>
                </div>
            @endforelse
        </div>

        <div class="notifications-pagination">
            {{ $notifications->links() }}
        </div>
    </div>
</div>

<style>
    .notifications-page {
        background: #f6f8fb;
        min-height: calc(100vh - 80px);
        padding: 28px 16px 44px;
    }

    .notifications-shell {
        max-width: 1040px;
        margin: 0 auto;
    }

    .notifications-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 22px;
    }

    .notifications-eyebrow {
        color: #2563eb;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .notifications-header h1 {
        margin: 0;
        color: #0f172a;
        font-size: clamp(28px, 4vw, 40px);
        font-weight: 800;
        letter-spacing: -.02em;
    }

    .notifications-header p {
        max-width: 620px;
        margin: 8px 0 0;
        color: #64748b;
        font-size: 15px;
        line-height: 1.55;
    }

    .notifications-summary-card {
        min-width: 170px;
        padding: 16px 18px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: #eff6ff;
        text-align: right;
    }

    .summary-number {
        color: #1d4ed8;
        font-size: 32px;
        font-weight: 800;
        line-height: 1;
    }

    .summary-label {
        margin-top: 5px;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
    }

    .notifications-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 14px;
        padding: 16px;
        margin-bottom: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
    }

    .notifications-filter {
        display: flex;
        align-items: end;
        gap: 12px;
        flex: 1;
    }

    .filter-field {
        min-width: 180px;
    }

    .filter-field-wide {
        min-width: 260px;
    }

    .filter-field label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
    }

    .filter-button,
    .mark-all-button {
        height: 38px;
        font-weight: 700;
        white-space: nowrap;
    }

    .notifications-list {
        display: grid;
        gap: 12px;
    }

    .notification-card {
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr);
        gap: 14px;
        padding: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .notification-card.is-unread {
        border-color: #bfdbfe;
        background: linear-gradient(90deg, #eff6ff 0, #ffffff 34%);
    }

    .notification-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 20px;
    }

    .notification-icon-default { background: #dbeafe; color: #2563eb; }
    .notification-icon-appointment { background: #dcfce7; color: #15803d; }
    .notification-icon-payment { background: #cffafe; color: #0e7490; }
    .notification-icon-news { background: #ede9fe; color: #6d28d9; }
    .notification-icon-treatment { background: #fef3c7; color: #b45309; }
    .notification-icon-alert { background: #fee2e2; color: #dc2626; }

    .notification-topline {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .notification-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .notification-type,
    .notification-status {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }

    .notification-type {
        background: #f1f5f9;
        color: #475569;
    }

    .notification-status.read {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .notification-status.unread {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .notification-topline time {
        flex: 0 0 auto;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    .notification-main h2 {
        margin: 0 0 6px;
        font-size: 17px;
        font-weight: 800;
        line-height: 1.35;
    }

    .notification-main h2 a {
        color: #0f172a;
        text-decoration: none;
    }

    .notification-main h2 a:hover {
        color: #2563eb;
    }

    .notification-main p {
        margin: 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.55;
    }

    .notification-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }

    .mark-read-button {
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        font-weight: 700;
    }

    .notifications-empty {
        padding: 56px 20px;
        border: 2px dashed #cbd5e1;
        border-radius: 18px;
        background: #ffffff;
        text-align: center;
    }

    .empty-icon {
        width: 54px;
        height: 54px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 22px;
    }

    .notifications-empty h2 {
        margin: 0 0 6px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 800;
    }

    .notifications-empty p {
        margin: 0;
        color: #64748b;
    }

    .notifications-pagination {
        display: flex;
        justify-content: center;
        margin-top: 22px;
    }

    @media (max-width: 768px) {
        .notifications-header,
        .notifications-toolbar,
        .notifications-filter {
            display: block;
        }

        .notifications-summary-card {
            margin-top: 16px;
            text-align: left;
        }

        .filter-field,
        .filter-field-wide,
        .filter-button,
        .mark-all-button {
            width: 100%;
            margin-top: 12px;
        }

        .notification-card {
            grid-template-columns: 40px minmax(0, 1fr);
            padding: 14px;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            font-size: 18px;
        }

        .notification-topline,
        .notification-actions {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endsection
