<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ thống đặt lịch khám bệnh')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <x-typography-base />
    <style>
        .app-notification-stack {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 20000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: min(420px, calc(100vw - 32px));
        }

        .app-notification {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .16);
            font-size: 14px;
            line-height: 1.45;
        }

        .app-notification[data-type="error"] {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .app-notification[data-type="warning"] {
            border-color: #fde68a;
            background: #fffbeb;
            color: #92400e;
        }

        .app-notification[data-type="success"] {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .app-notification button {
            border: 0;
            background: transparent;
            color: inherit;
            margin-left: auto;
            padding: 0 0 0 8px;
            line-height: 1;
            font-size: 18px;
        }
    </style>
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app-notification-stack" class="app-notification-stack" aria-live="polite" aria-atomic="true"></div>
    @if(session('reload_page'))
        <div id="app-reload-message"
             data-message="{{ e(session('warning') ?? session('error') ?? 'Dữ liệu đã thay đổi, trang sẽ được tải lại.') }}"
             hidden></div>
    @endif

    @auth
        <x-notification-bell :floating="true" />
    @endauth

    @yield('content')
    @include('components.back-to-previous')
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.showAppNotification = function (message, type = 'error', options = {}) {
            const stack = document.getElementById('app-notification-stack');
            if (!stack) return;

            const notice = document.createElement('div');
            notice.className = 'app-notification';
            notice.dataset.type = type;
            notice.setAttribute('role', type === 'error' ? 'alert' : 'status');

            const icon = document.createElement('i');
            icon.className = type === 'success'
                ? 'bi bi-check-circle-fill'
                : (type === 'warning' ? 'bi bi-exclamation-triangle-fill' : 'bi bi-x-circle-fill');

            const text = document.createElement('div');
            text.textContent = message || 'Đã xảy ra lỗi, vui lòng thử lại sau.';

            const close = document.createElement('button');
            close.type = 'button';
            close.setAttribute('aria-label', 'Đóng thông báo');
            close.textContent = '×';
            close.addEventListener('click', () => notice.remove());

            notice.append(icon, text, close);
            stack.appendChild(notice);

            const timeout = typeof options.timeout === 'number' ? options.timeout : 5000;
            if (timeout > 0) {
                setTimeout(() => notice.remove(), timeout);
            }
        };

        window.alert = function (message) {
            window.showAppNotification(message, 'error');
        };
    </script>
    @if(session('reload_page'))
    <script>
        window.showAppNotification(document.getElementById('app-reload-message').dataset.message, 'warning', { timeout: 2500 });
        setTimeout(() => window.location.replace(window.location.href), 1800);
    </script>
    @endif

    @if(session('warning') && !session('reload_page'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.showAppNotification("{{ e(session('warning')) }}", 'warning');
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.showAppNotification("{{ e(session('error')) }}", 'error');
        });
    </script>
    @endif
    @stack('scripts')

    @auth
        @include('components.chat-widget')
    @endauth
</body>
</html>
