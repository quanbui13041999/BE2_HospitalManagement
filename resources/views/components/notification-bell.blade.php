@props(['floating' => false])

<div class="notification-bell {{ $floating ? 'notification-bell-floating' : '' }}">
    <div class="dropdown">
        <button class="btn btn-link nav-link position-relative notification-bell-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Thông báo">
            <i class="bi bi-bell"></i>
            <span class="notification-badge" data-notification-badge style="display:none;">0</span>
        </button>
        <div class="dropdown-menu dropdown-menu-end notification-menu shadow">
            <div class="notification-menu-header">
                <div>
                    <div class="fw-semibold">Thông báo</div>
                    <small class="text-muted" data-notification-summary>Đang tải...</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" data-notification-mark-all>
                    Đánh dấu tất cả
                </button>
            </div>
            <div class="notification-list" data-notification-list>
                <div class="notification-empty">Đang tải thông báo...</div>
            </div>
            <div class="notification-footer">
                <a href="{{ route('notifications.index') }}">Xem tất cả</a>
            </div>
        </div>
    </div>
</div>

@once
    <style>
            .notification-bell { position: relative; }
            .notification-bell-floating {
                position: fixed;
                right: 18px;
                top: 18px;
                z-index: 1050;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 999px;
                box-shadow: 0 10px 30px rgba(15, 23, 42, .14);
            }
            .notification-bell-toggle {
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #334155;
                text-decoration: none;
            }
            .notification-bell-toggle:hover { color: #0d6efd; }
            .notification-badge {
                position: absolute;
                top: 2px;
                right: 0;
                min-width: 18px;
                height: 18px;
                padding: 0 5px;
                border-radius: 999px;
                background: #dc3545;
                color: #fff;
                font-size: 11px;
                font-weight: 700;
                line-height: 18px;
                text-align: center;
            }
            .notification-menu {
                width: min(380px, calc(100vw - 24px));
                padding: 0;
                border: 1px solid #e2e8f0;
                overflow: hidden;
            }
            .notification-menu-header,
            .notification-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 14px;
                background: #fff;
            }
            .notification-menu-header { border-bottom: 1px solid #eef2f7; }
            .notification-footer {
                border-top: 1px solid #eef2f7;
                justify-content: center;
                font-weight: 600;
            }
            .notification-list {
                max-height: 430px;
                overflow-y: auto;
                background: #fff;
            }
            .notification-item {
                display: flex;
                gap: 10px;
                padding: 11px 14px;
                color: #1f2937;
                text-decoration: none;
                border-bottom: 1px solid #f1f5f9;
            }
            .notification-item:hover { background: #f8fafc; }
            .notification-item.unread {
                background: #eef6ff;
                font-weight: 600;
            }
            .notification-dot {
                width: 8px;
                height: 8px;
                border-radius: 999px;
                background: #0d6efd;
                flex: 0 0 auto;
                margin-top: 7px;
                visibility: hidden;
            }
            .notification-item.unread .notification-dot { visibility: visible; }
            .notification-title {
                font-size: 14px;
                line-height: 1.35;
                margin-bottom: 3px;
            }
            .notification-message,
            .notification-meta,
            .notification-empty {
                font-size: 12px;
                color: #64748b;
                line-height: 1.4;
            }
            .notification-empty {
                padding: 22px 14px;
                text-align: center;
            }
    </style>

    @push('scripts')
        <script>
            (() => {
                const dropdownUrl = @json(route('notifications.dropdown'));
                const markAllUrl = @json(route('notifications.mark-all-read'));
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const badgeEls = () => document.querySelectorAll('[data-notification-badge]');
                const listEls = () => document.querySelectorAll('[data-notification-list]');
                const summaryEls = () => document.querySelectorAll('[data-notification-summary]');

                function escapeHtml(value) {
                    return String(value ?? '').replace(/[&<>"']/g, char => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    }[char]));
                }

                function render(data) {
                    badgeEls().forEach(badge => {
                        const count = Number(data.unread_count || 0);
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = count > 0 ? 'inline-block' : 'none';
                    });

                    summaryEls().forEach(summary => {
                        summary.textContent = data.unread_count > 0
                            ? `${data.unread_count} thông báo chưa đọc`
                            : 'Không có thông báo chưa đọc';
                    });

                    listEls().forEach(list => {
                        if (!data.items || data.items.length === 0) {
                            list.innerHTML = '<div class="notification-empty">Chưa có thông báo.</div>';
                            return;
                        }

                        list.innerHTML = data.items.map(item => `
                            <a class="notification-item ${item.is_read ? '' : 'unread'}" href="${escapeHtml(item.url)}">
                                <span class="notification-dot"></span>
                                <span class="flex-grow-1">
                                    <span class="notification-title d-block">${escapeHtml(item.title)}</span>
                                    <span class="notification-message d-block">${escapeHtml(item.message)}</span>
                                    <span class="notification-meta d-block">${escapeHtml(item.type)} · ${escapeHtml(item.created_at)}</span>
                                </span>
                            </a>
                        `).join('');
                    });
                }

                async function loadNotifications() {
                    try {
                        const response = await fetch(dropdownUrl, { headers: { 'Accept': 'application/json' } });
                        if (!response.ok) return;
                        render(await response.json());
                    } catch (error) {}
                }

                async function markAllRead() {
                    try {
                        const response = await fetch(markAllUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            }
                        });
                        if (response.ok) {
                            loadNotifications();
                            if (document.querySelector('[data-notification-mark-read]')) {
                                window.location.reload();
                            }
                        }
                    } catch (error) {}
                }

                async function markRead(url) {
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            }
                        });
                        if (response.ok) {
                            loadNotifications();
                            window.location.reload();
                        }
                    } catch (error) {}
                }

                document.addEventListener('click', event => {
                    if (event.target.closest('[data-notification-mark-all]')) {
                        event.preventDefault();
                        markAllRead();
                    }

                    const markReadButton = event.target.closest('[data-notification-mark-read]');
                    if (markReadButton) {
                        event.preventDefault();
                        markRead(markReadButton.getAttribute('data-notification-mark-read'));
                    }
                });

                document.addEventListener('DOMContentLoaded', () => {
                    loadNotifications();
                    setInterval(loadNotifications, 45000);
                });
            })();
        </script>
    @endpush
@endonce
