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
    <div class="modal fade" id="appConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận thao tác</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="appConfirmMessage">Bạn có chắc muốn thực hiện thao tác này?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="appConfirmSubmit">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>
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

        function appInputLimitMessage(field) {
            const label = document.querySelector(`label[for="${field.id}"]`);
            const name = label ? label.textContent.trim() : (field.getAttribute('name') || 'Trường này');
            return `${name} tối đa ${field.getAttribute('maxlength')} ký tự. Vui lòng rút ngắn nội dung.`;
        }

        document.addEventListener('input', function (event) {
            const field = event.target;
            if (!field || !field.matches('input[maxlength], textarea[maxlength]')) return;

            const maxLength = Number(field.getAttribute('maxlength'));
            if (!Number.isFinite(maxLength) || maxLength <= 0 || field.value.length < maxLength) return;
            if (field.dataset.limitNotified === '1') return;

            field.dataset.limitNotified = '1';
            field.classList.add('is-invalid');
            window.showAppNotification(appInputLimitMessage(field), 'warning');
        });

        document.addEventListener('blur', function (event) {
            const field = event.target;
            if (!field || !field.matches('input[maxlength], textarea[maxlength]')) return;

            const maxLength = Number(field.getAttribute('maxlength'));
            if (!Number.isFinite(maxLength) || maxLength <= 0 || field.value.length < maxLength) return;
            field.dataset.limitNotified = '';
        }, true);

        document.addEventListener('submit', function (event) {
            const invalidField = Array.from(event.target.querySelectorAll('input[maxlength], textarea[maxlength]'))
                .find(field => {
                    const maxLength = Number(field.getAttribute('maxlength'));
                    return Number.isFinite(maxLength) && maxLength > 0 && field.value.length > maxLength;
                });

            if (invalidField) {
                event.preventDefault();
                invalidField.classList.add('is-invalid');
                invalidField.focus();
                window.showAppNotification(appInputLimitMessage(invalidField), 'warning');
            }
        });

        function appDisableSubmitButtons(form) {
            if (!form || form.dataset.submitLocked === '1') return false;
            form.dataset.submitLocked = '1';
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
                button.disabled = true;
                if (button.tagName === 'BUTTON' && !button.dataset.originalText) {
                    button.dataset.originalText = button.innerHTML;
                    button.innerHTML = 'Đang xử lý...';
                }
            });
            return true;
        }

        document.addEventListener('submit', function (event) {
            const form = event.target.closest('form[data-disable-submit]');
            if (!form) return;

            if (!appDisableSubmitButtons(form)) {
                event.preventDefault();
                window.showAppNotification('Yêu cầu đang được xử lý, vui lòng không bấm lưu nhiều lần.', 'warning');
            }
        }); /* fixed: chan double submit tao trung du lieu */

        document.addEventListener('submit', function (event) {
            const form = event.target.closest('form[data-confirm]');
            if (!form || form.dataset.confirmed === '1') return;

            event.preventDefault();
            const messageEl = document.getElementById('appConfirmMessage');
            const submitBtn = document.getElementById('appConfirmSubmit');
            const modalEl = document.getElementById('appConfirmModal');
            if (!messageEl || !submitBtn || !modalEl || !window.bootstrap) return;

            messageEl.textContent = form.dataset.confirm || 'Bạn có chắc muốn thực hiện thao tác này?';
            submitBtn.onclick = function () {
                form.dataset.confirmed = '1';
                appDisableSubmitButtons(form);
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                form.submit();
            };
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        window.appConfirm = function (message) {
            return new Promise(function (resolve) {
                const messageEl = document.getElementById('appConfirmMessage');
                const submitBtn = document.getElementById('appConfirmSubmit');
                const modalEl = document.getElementById('appConfirmModal');
                if (!messageEl || !submitBtn || !modalEl || !window.bootstrap) {
                    resolve(false);
                    return;
                }

                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                const cleanup = function () {
                    submitBtn.onclick = null;
                    modalEl.removeEventListener('hidden.bs.modal', onHidden);
                };
                const onHidden = function () {
                    cleanup();
                    resolve(false);
                };

                messageEl.textContent = message || 'Bạn có chắc muốn thực hiện thao tác này?';
                submitBtn.onclick = function () {
                    cleanup();
                    modal.hide();
                    resolve(true);
                };
                modalEl.addEventListener('hidden.bs.modal', onHidden);
                modal.show();
            });
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
