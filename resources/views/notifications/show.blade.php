@extends($layout)

@section('title', 'Chi tiết thông báo')

@section('content')
@php
    if (!function_exists('notificationDetailIconClass')) {
        function notificationDetailIconClass($type) {
            $value = mb_strtolower((string) $type, 'UTF-8');

            if (str_contains($value, 'appointment') || str_contains($value, 'lịch') || str_contains($value, 'hẹn')) {
                return ['bi-calendar-check', 'detail-icon-appointment'];
            }
            if (str_contains($value, 'payment') || str_contains($value, 'thanh toán') || str_contains($value, 'hóa đơn')) {
                return ['bi-credit-card', 'detail-icon-payment'];
            }
            if (str_contains($value, 'news') || str_contains($value, 'bản tin')) {
                return ['bi-newspaper', 'detail-icon-news'];
            }
            if (str_contains($value, 'treatment') || str_contains($value, 'nhắc') || str_contains($value, 'thuốc')) {
                return ['bi-capsule-pill', 'detail-icon-treatment'];
            }
            if (str_contains($value, 'system') || str_contains($value, 'khẩn') || str_contains($value, 'cảnh báo')) {
                return ['bi-exclamation-triangle', 'detail-icon-alert'];
            }

            return ['bi-bell', 'detail-icon-default'];
        }
    }

    [$icon, $iconClass] = notificationDetailIconClass($notification->displayType());
@endphp

<div class="notification-detail-page">
    <div class="notification-detail-shell">
        <div class="mb-3">
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>

        <article class="notification-detail-card">
            <header class="notification-detail-header">
                <div class="notification-detail-icon {{ $iconClass }}">
                    <i class="bi {{ $icon }}"></i>
                </div>

                <div class="notification-detail-heading">
                    <div class="notification-detail-badges">
                        <span class="detail-type">{{ $notification->displayType() }}</span>
                        <span class="detail-read">Đã đọc</span>
                    </div>
                    <h1>{{ $notification->title }}</h1>
                    <div class="detail-time">
                        <i class="bi bi-clock me-1"></i>
                        {{ optional($notification->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
            </header>

            <section class="notification-detail-message">
                {!! nl2br(e($notification->displayMessage())) !!}
            </section>

            <section class="notification-detail-meta">
                <h2>Thông tin chi tiết</h2>

                <div class="meta-grid">
                    <div class="meta-item">
                        <span>Người gửi</span>
                        <strong>{{ $notification->sender?->full_name ?? 'Hệ thống tự động' }}</strong>
                    </div>

                    <div class="meta-item">
                        <span>Phạm vi</span>
                        <strong>
                            @switch($notification->target_type)
                                @case('all')
                                    Toàn hệ thống
                                    @break
                                @case('role')
                                    Role: {{ $notification->target_role }}
                                    @break
                                @case('users')
                                    Nhóm người dùng
                                    @break
                                @default
                                    Cá nhân gửi nhận
                            @endswitch
                        </strong>
                    </div>

                    @if($notification->related_type || $notification->ref_type)
                        <div class="meta-item">
                            <span>Đối tượng liên quan</span>
                            <strong>
                                {{ $notification->related_type ?: $notification->ref_type }}
                                #{{ $notification->related_id ?: $notification->ref_id }}
                            </strong>
                        </div>
                    @endif
                </div>

                @if($notification->relatedUrl())
                    <div class="detail-action">
                        <a class="btn btn-primary" href="{{ $notification->relatedUrl() }}">
                            Mở chức năng liên quan
                        </a>
                    </div>
                @endif
            </section>
        </article>
    </div>
</div>

<style>
    .notification-detail-page {
        min-height: calc(100vh - 80px);
        padding: 28px 16px 44px;
        background: #f6f8fb;
    }

    .notification-detail-shell {
        max-width: 920px;
        margin: 0 auto;
    }

    .notification-detail-card {
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
    }

    .notification-detail-header {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        gap: 16px;
        padding: 24px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .notification-detail-icon {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        font-size: 25px;
    }

    .detail-icon-default { background: #dbeafe; color: #2563eb; }
    .detail-icon-appointment { background: #dcfce7; color: #15803d; }
    .detail-icon-payment { background: #cffafe; color: #0e7490; }
    .detail-icon-news { background: #ede9fe; color: #6d28d9; }
    .detail-icon-treatment { background: #fef3c7; color: #b45309; }
    .detail-icon-alert { background: #fee2e2; color: #dc2626; }

    .notification-detail-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }

    .detail-type,
    .detail-read {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }

    .detail-type {
        color: #475569;
        background: #f1f5f9;
    }

    .detail-read {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .notification-detail-heading h1 {
        margin: 0;
        color: #0f172a;
        font-size: clamp(25px, 4vw, 34px);
        font-weight: 800;
        line-height: 1.18;
    }

    .detail-time {
        margin-top: 10px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .notification-detail-message {
        padding: 28px 24px;
        color: #1f2937;
        font-size: 16px;
        line-height: 1.75;
        border-bottom: 1px solid #e2e8f0;
        white-space: normal;
    }

    .notification-detail-meta {
        padding: 24px;
        background: #fbfdff;
    }

    .notification-detail-meta h2 {
        margin: 0 0 14px;
        color: #0f172a;
        font-size: 16px;
        font-weight: 800;
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .meta-item {
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
    }

    .meta-item span {
        display: block;
        margin-bottom: 5px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .meta-item strong {
        display: block;
        color: #0f172a;
        font-size: 14px;
        line-height: 1.4;
    }

    .detail-action {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    @media (max-width: 768px) {
        .notification-detail-header {
            grid-template-columns: 1fr;
        }

        .meta-grid {
            grid-template-columns: 1fr;
        }

        .detail-action {
            justify-content: stretch;
        }

        .detail-action .btn {
            width: 100%;
        }
    }
</style>
@endsection
