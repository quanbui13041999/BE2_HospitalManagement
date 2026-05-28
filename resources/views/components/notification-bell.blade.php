@props(['floating' => false, 'direct' => false])

@php($uid = 'notification-' . uniqid())

<div class="hb-notification {{ $floating ? 'hb-notification-floating' : '' }}"
     data-notification-root
     data-dropdown-url="{{ route('notifications.dropdown') }}"
     data-mark-all-url="{{ route('notifications.mark-all-read') }}">
    @if($direct)
    <a class="hb-notification-trigger" href="{{ route('notifications.index') }}" title="Thông báo">
        <svg class="hb-notification-bell-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
        </svg>
        <span class="hb-notification-badge" data-notification-badge style="display:none;">0</span>
    </a>
    @else
    <button class="hb-notification-trigger" type="button" data-notification-toggle aria-expanded="false" aria-controls="{{ $uid }}" title="Thông báo">
        <svg class="hb-notification-bell-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
        </svg>
        <span class="hb-notification-badge" data-notification-badge style="display:none;">0</span>
    </button>

    <div id="{{ $uid }}" class="hb-notification-menu" data-notification-menu hidden>
        <div class="hb-notification-head">
            <div>
                <div class="hb-notification-heading">Thông báo</div>
                <div class="hb-notification-summary" data-notification-summary>Đang tải...</div>
            </div>
            <button class="hb-notification-mark-all" type="button" data-notification-mark-all>
                Đánh dấu tất cả
            </button>
        </div>

        <div class="hb-notification-list" data-notification-list>
            <div class="hb-notification-empty">
                <svg class="hb-notification-empty-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" />
                </svg>
                <span>Đang tải thông báo...</span>
            </div>
        </div>

        <a class="hb-notification-footer" href="{{ route('notifications.index') }}">
            Xem tất cả thông báo
        </a>
    </div>
    @endif
</div>

@once
    <style>
        .hb-notification,
        .hb-notification * {
            box-sizing: border-box;
        }

        .hb-notification {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #0f172a;
        }

        .hb-notification-floating {
            position: fixed;
            top: 18px;
            right: 18px;
            padding: 3px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .14);
        }

        .hb-notification-trigger {
            position: relative;
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #2563eb;
            line-height: 1;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s ease, color .15s ease;
        }

        .hb-notification-trigger:hover,
        .hb-notification-trigger[aria-expanded="true"] {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .hb-notification-bell-icon {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .hb-notification-badge {
            position: absolute;
            top: 3px;
            right: 2px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ef4444;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            line-height: 18px;
            text-align: center;
            border: 2px solid #ffffff;
            box-shadow: 0 3px 8px rgba(239, 68, 68, .35);
        }

        .hb-notification-menu {
            position: fixed;
            top: var(--hb-notification-top, 64px);
            left: var(--hb-notification-left, 12px);
            width: min(390px, calc(100vw - 24px));
            max-width: calc(100vw - 24px);
            overflow: hidden;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 22px 55px rgba(15, 23, 42, .22);
            color: #0f172a;
            text-align: left;
            z-index: 100000;
        }

        .hb-notification-menu[hidden] {
            display: none !important;
        }

        .hb-notification-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .hb-notification-heading {
            margin: 0;
            color: #0f172a;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.25;
        }

        .hb-notification-summary {
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.3;
        }

        .hb-notification-mark-all {
            flex: 0 0 auto;
            padding: 6px 9px;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            cursor: pointer;
            white-space: nowrap;
        }

        .hb-notification-mark-all:hover {
            background: #dbeafe;
        }

        .hb-notification-list {
            max-height: 390px;
            overflow-y: auto;
            background: #ffffff;
        }

        .hb-notification-item {
            display: flex;
            gap: 12px;
            width: 100%;
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #0f172a;
            text-decoration: none;
            background: #ffffff;
        }

        .hb-notification-item:hover {
            background: #f8fafc;
            color: #0f172a;
            text-decoration: none;
        }

        .hb-notification-item.is-unread {
            background: #eff6ff;
        }

        .hb-notification-icon {
            position: relative;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #dbeafe;
            color: #2563eb;
        }

        .hb-notification-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .hb-notification-dot {
            position: absolute;
            top: 1px;
            right: 1px;
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #ef4444;
            border: 2px solid #ffffff;
        }

        .hb-notification-body {
            min-width: 0;
            flex: 1;
        }

        .hb-notification-meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 4px;
            color: #64748b;
            font-size: 11px;
            line-height: 1.3;
        }

        .hb-notification-type {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: .03em;
        }

        .hb-notification-time {
            flex: 0 0 auto;
        }

        .hb-notification-title {
            margin: 0 0 4px;
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.35;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .hb-notification-text {
            margin: 0;
            color: #475569;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .hb-notification-empty {
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 24px 16px;
            color: #64748b;
            background: #ffffff;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
        }

        .hb-notification-empty-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            padding: 10px;
        }

        .hb-notification-footer {
            display: block;
            padding: 12px 16px;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
            color: #2563eb;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.3;
            text-align: center;
            text-decoration: none;
        }

        .hb-notification-footer:hover {
            background: #eff6ff;
            color: #1d4ed8;
            text-decoration: none;
        }

        @media (max-width: 575.98px) {
            .hb-notification-menu {
                top: 58px;
                left: 12px;
                width: auto;
                max-width: none;
            }
        }
    </style>

    <script>
            (() => {
                if (window.__hbNotificationBooted) return;
                window.__hbNotificationBooted = true;

                const firstRoot = document.querySelector('[data-notification-root]');
                const dropdownUrl = firstRoot?.dataset.dropdownUrl || '/notifications/dropdown';
                const markAllUrl = firstRoot?.dataset.markAllUrl || '/notifications/mark-all-read';
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                function escapeHtml(value) {
                    return String(value ?? '').replace(/[&<>"']/g, char => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    }[char]));
                }

                function iconFor(type) {
                    const value = String(type ?? '').toLowerCase();
                    if (value.includes('appointment') || value.includes('hẹn') || value.includes('lịch')) return 'M8 7h8M8 11h8M8 15h5M7 3v3m10-3v3M5 5h14v15H5z';
                    if (value.includes('payment') || value.includes('hóa đơn') || value.includes('thanh toán')) return 'M3 6h18v12H3zM3 10h18M7 15h4';
                    if (value.includes('news') || value.includes('bản tin')) return 'M5 4h14v16H5zM8 8h8M8 12h8M8 16h5';
                    if (value.includes('treatment') || value.includes('nhắc') || value.includes('thuốc')) return 'M10 21 21 10a4 4 0 0 0-6-6L4 15a4 4 0 0 0 6 6Zm3-14 4 4M7 14l3 3';
                    if (value.includes('system') || value.includes('khẩn') || value.includes('cảnh báo')) return 'M12 3 22 20H2L12 3Zm0 6v5m0 3h.01';
                    return 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9';
                }

                function roots() {
                    return Array.from(document.querySelectorAll('[data-notification-root]'));
                }

                function positionMenu(root) {
                    const menu = root.querySelector('[data-notification-menu]');
                    const trigger = root.querySelector('[data-notification-toggle]');
                    if (!menu || !trigger || window.innerWidth <= 575) return;

                    const rect = trigger.getBoundingClientRect();
                    const gap = 10;
                    const viewportPadding = 12;
                    const menuWidth = Math.min(390, window.innerWidth - viewportPadding * 2);
                    const left = Math.max(
                        viewportPadding,
                        Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - viewportPadding)
                    );
                    const top = Math.min(rect.bottom + gap, window.innerHeight - 80);

                    menu.style.setProperty('--hb-notification-left', `${left}px`);
                    menu.style.setProperty('--hb-notification-top', `${top}px`);
                }

                function setOpen(root, open) {
                    const menu = root.querySelector('[data-notification-menu]');
                    const trigger = root.querySelector('[data-notification-toggle]');
                    if (!menu || !trigger) return;

                    if (open) {
                        positionMenu(root);
                    }

                    menu.hidden = !open;
                    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                }

                function render(data) {
                    const count = Number(data.unread_count || 0);

                    roots().forEach(root => {
                        const badge = root.querySelector('[data-notification-badge]');
                        const summary = root.querySelector('[data-notification-summary]');
                        const list = root.querySelector('[data-notification-list]');

                        if (badge) {
                            badge.textContent = count > 99 ? '99+' : count;
                            badge.style.display = count > 0 ? 'inline-block' : 'none';
                        }

                        if (summary) {
                            summary.textContent = count > 0
                                ? `${count} thông báo chưa đọc`
                                : 'Không có thông báo chưa đọc';
                        }

                        if (!list) return;

                        if (!data.items || data.items.length === 0) {
                            list.innerHTML = `
                                <div class="hb-notification-empty">
                                    <svg class="hb-notification-empty-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-9.3-5M6 6.7A5.9 5.9 0 0 0 6 11v3.2c0 .5-.2 1-.6 1.4L4 17h13M9 17a3 3 0 0 0 5.2 2M3 3l18 18" /></svg>
                                    <span>Chưa có thông báo.</span>
                                </div>
                            `;
                            return;
                        }

                        list.innerHTML = data.items.map(item => {
                            const read = !!item.is_read;
                            return `
                                <a class="hb-notification-item ${read ? '' : 'is-unread'}" href="${escapeHtml(item.url)}">
                                    <span class="hb-notification-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="${iconFor(item.type)}" /></svg>
                                        ${read ? '' : '<span class="hb-notification-dot"></span>'}
                                    </span>
                                    <span class="hb-notification-body">
                                        <span class="hb-notification-meta">
                                            <span class="hb-notification-type">${escapeHtml(item.type)}</span>
                                            <span class="hb-notification-time">${escapeHtml(item.created_at)}</span>
                                        </span>
                                        <span class="hb-notification-title">${escapeHtml(item.title)}</span>
                                        <span class="hb-notification-text">${escapeHtml(item.message)}</span>
                                    </span>
                                </a>
                            `;
                        }).join('');
                    });
                }

                async function loadNotifications() {
                    try {
                        const response = await fetch(dropdownUrl, { headers: { 'Accept': 'application/json' } });
                        if (response.ok) render(await response.json());
                    } catch (error) {}
                }

                async function postJson(url) {
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            }
                        });
                        if (response.ok) {
                            await loadNotifications();
                            if (document.querySelector('[data-notification-mark-read]')) {
                                window.location.reload();
                            }
                        }
                    } catch (error) {}
                }

                document.addEventListener('click', event => {
                    const toggle = event.target.closest('[data-notification-toggle]');
                    if (toggle) {
                        event.preventDefault();
                        const root = toggle.closest('[data-notification-root]');
                        const willOpen = root.querySelector('[data-notification-menu]')?.hidden !== false;
                        roots().forEach(item => setOpen(item, false));
                        setOpen(root, willOpen);
                        if (willOpen) loadNotifications();
                        return;
                    }

                    if (event.target.closest('[data-notification-mark-all]')) {
                        event.preventDefault();
                        postJson(markAllUrl);
                        return;
                    }

                    const markReadButton = event.target.closest('[data-notification-mark-read]');
                    if (markReadButton) {
                        event.preventDefault();
                        postJson(markReadButton.getAttribute('data-notification-mark-read'));
                        return;
                    }

                    if (!event.target.closest('[data-notification-root]')) {
                        roots().forEach(root => setOpen(root, false));
                    }
                });

                document.addEventListener('keydown', event => {
                    if (event.key === 'Escape') {
                        roots().forEach(root => setOpen(root, false));
                    }
                });

                window.addEventListener('resize', () => {
                    roots().forEach(root => {
                        const menu = root.querySelector('[data-notification-menu]');
                        if (menu && !menu.hidden) positionMenu(root);
                    });
                });

                window.addEventListener('scroll', () => {
                    roots().forEach(root => {
                        const menu = root.querySelector('[data-notification-menu]');
                        if (menu && !menu.hidden) positionMenu(root);
                    });
                }, true);

                document.addEventListener('DOMContentLoaded', () => {
                    loadNotifications();
                    setInterval(loadNotifications, 45000);
                });
            })();
    </script>
@endonce
