<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bệnh viện') - Hệ thống đặt lịch khám</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    {{-- Google Fonts: DM Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <x-typography-base />

    <style>
        :root {
            --accent: #0A6EBD;
            --accent-light: #E8F3FC;
            --accent-dark: #074B83;
            --surface: #F4F7FA;
            --black: #0A0F14;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: var(--surface);
        }
    </style>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    @stack('styles')
</head>

<body>
    <div id="app-notification-stack" style="position:fixed;top:18px;right:18px;z-index:20000;width:min(420px,calc(100vw - 32px));display:flex;flex-direction:column;gap:10px"></div>

    @unless(request()->routeIs('news.*'))
        <x-site-nav />
    @endunless

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-dark text-white-50 mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-white">Bệnh viện</h6>
                    <p class="small">Chăm sóc sức khỏe cộng đồng - Chất lượng hàng đầu</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white">Liên hệ</h6>
                    <p class="small mb-1"><i class="bi bi-geo-alt"></i> Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM</p>
                    <p class="small mb-1"><i class="bi bi-telephone"></i> Điện thoại: 1900 XXXX</p>
                    <p class="small"><i class="bi bi-envelope"></i> Email: info@hospital.com</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white">Giờ làm việc</h6>
                    <p class="small mb-0">Thứ 2 - Thứ 6: 7:30 - 17:00</p>
                    <p class="small">Thứ 7: 7:30 - 12:00</p>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center small">
                © {{ date('Y') }} Bệnh viện. All rights reserved.
            </div>
        </div>
    </footer>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.showAppNotification = window.showAppNotification || function (message, type = 'error', options = {}) {
            const stack = document.getElementById('app-notification-stack');
            if (!stack) return;

            const notice = document.createElement('div');
            notice.textContent = message || 'Đã xảy ra lỗi, vui lòng thử lại sau.';
            notice.setAttribute('role', type === 'error' ? 'alert' : 'status');
            notice.style.cssText = 'padding:12px 14px;border-radius:10px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;box-shadow:0 16px 40px rgba(15,23,42,.16);font-size:14px;line-height:1.45';
            if (type === 'warning') {
                notice.style.borderColor = '#fde68a';
                notice.style.background = '#fffbeb';
                notice.style.color = '#92400e';
            }

            stack.appendChild(notice);
            setTimeout(() => notice.remove(), typeof options.timeout === 'number' ? options.timeout : 5000);
        };

        function appSnapshotSelectOptions(root = document) {
            root.querySelectorAll('select[name]').forEach(function (select) {
                if (select.dataset.allowedValues) return;

                select.dataset.allowedValues = JSON.stringify(
                    Array.from(select.options).map(option => option.value)
                );
            });
        }

        function appSelectLabel(select) {
            const label = select.id ? document.querySelector(`label[for="${select.id}"]`) : null;
            return label ? label.textContent.trim() : (select.getAttribute('name') || 'Trường chọn');
        }

        function appReloadCurrentPage(delay = 1600) {
            if (window.appReloadScheduled) return;

            window.appReloadScheduled = true;
            setTimeout(function () {
                window.location.reload();
            }, delay);
        }

        function appReloadCleanUrl(delay = 1800) {
            if (window.appReloadScheduled) return;

            window.appReloadScheduled = true;
            setTimeout(function () {
                window.location.replace(window.location.pathname);
            }, delay);
        }

        function appValidateSelectOptions(root = document) {
            const invalidSelect = Array.from(root.querySelectorAll('select[name]')).find(function (select) {
                let allowedValues = [];

                try {
                    allowedValues = JSON.parse(select.dataset.allowedValues || '[]');
                } catch (e) {
                    allowedValues = [];
                }

                return allowedValues.length > 0 && !allowedValues.includes(select.value);
            });

            if (!invalidSelect) return true;

            invalidSelect.classList.add('is-invalid');
            invalidSelect.focus();
            window.showAppNotification(
                appSelectLabel(invalidSelect) + ' không hợp lệ. Trang sẽ được tải lại.',
                'warning'
            );
            appReloadCurrentPage(); /* fixed: select bi chen bang DevTools thi thong bao roi reload de reset DOM */

            return false;
        }

        window.appSnapshotSelectOptions = appSnapshotSelectOptions;
        window.appValidateSelectOptions = appValidateSelectOptions;
        appSnapshotSelectOptions(); /* fixed: chot option hop le ban dau, chan option gia chen bang DevTools */

        document.addEventListener('submit', function (event) {
            if (!appValidateSelectOptions(event.target)) {
                event.preventDefault();
            }
        });
    </script>
    @if($errors->any())
        <script data-reload-clean-url="{{ request()->isMethod('get') && request()->getQueryString() ? '1' : '0' }}">
            const currentErrorNotificationScript = document.currentScript;
            document.addEventListener('DOMContentLoaded', function () {
                window.showAppNotification("{{ e($errors->first()) }}", 'warning');
                const shouldReloadCleanUrl = currentErrorNotificationScript?.dataset.reloadCleanUrl === '1';
                if (shouldReloadCleanUrl) {
                    appReloadCleanUrl(); /* fixed: URL/filter GET sai thi thong bao roi tai lai trang sach query */
                }
            });
        </script>
    @endif
    @stack('scripts')

    @auth
        @include('components.chat-widget')
    @endauth
    @include('components.back-to-previous')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        const method = form.getAttribute('method');
        if (method && method.toUpperCase() !== 'GET') {
            form.addEventListener('submit', function (e) {
                if (form.checkValidity && !form.checkValidity()) {
                    return;
                }
                if (form.dataset.submitting === 'true') {
                    e.preventDefault();
                    return false;
                }
                form.dataset.submitting = 'true';
                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
                    button.disabled = true;
                    if (button.tagName === 'BUTTON') {
                        button.dataset.originalHtml = button.innerHTML;
                        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang xử lý...';
                    } else if (button.tagName === 'INPUT') {
                        button.dataset.originalValue = button.value;
                        button.value = 'Đang xử lý...';
                    }
                });
            });
        }
    });
});
</script>
</body>

</html>
