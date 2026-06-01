<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — HospitalC</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <x-typography-base />

    <style>
        body { background: #f4f6fb; font-family: 'Segoe UI', sans-serif; }

        /* ── Sidebar ── */
        #sidebar {
            width: 260px; min-height: 100vh; background: #fff;
            position: fixed; top: 0; left: 0; z-index: 100;
            display: flex; flex-direction: column;
            border-right: 1px solid #e2e8f0;
            height: 100vh;
            overflow: hidden;
        }
        #sidebar .brand {
            padding: 20px 24px;
            font-size: 18px; font-weight: 700; color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
            display: block;
        }
        #sidebar .brand:hover {
            color: #1d4ed8;
        }
        #sidebar .nav-link {
            color: #64748b; padding: 10px 20px;
            font-size: 14px; border-radius: 8px; transition: .15s;
            display: flex; align-items: center; gap: 10px;
            margin: 2px 12px;
            text-decoration: none;
        }
        #sidebar .nav-link:hover {
            background: #f8fafc; color: #1e293b;
        }
        #sidebar .nav-link.active {
            background: #dbeafe; color: #1d4ed8; font-weight: 600;
        }
        #sidebar .nav-section {
            padding: 18px 24px 6px;
            font-size: 11px; text-transform: uppercase;
            letter-spacing: .05em; color: #94a3b8; font-weight: 700;
        }
        #sidebar nav {
            flex: 1;
            min-height: 0;
            height: calc(100vh - 69px - 111px);
            max-height: calc(100vh - 69px - 111px);
            overflow-y: scroll;
            overscroll-behavior: contain;
            padding-bottom: 24px;
        }
        #sidebar nav::-webkit-scrollbar {
            width: 8px;
        }
        #sidebar nav::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }
        #sidebar nav::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        /* ── Main area ── */
        #main-wrap { margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Topbar ── */
        #topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 0 24px; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 99;
        }
        #topbar .page-title { font-weight: 700; font-size: 16px; color: #1e293b; }

        /* ── Content ── */
        #content { padding: 24px; flex: 1; }

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

{{-- ══ SIDEBAR ══════════════════════════════════════════════════════ --}}
<div id="sidebar">
    <a href="{{ route('home') }}" class="brand">
        <i class="bi bi-hospital me-2 text-primary"></i>HospitalC Admin
    </a>

    <nav class="mt-3 flex-fill">
        <div class="nav-section">Tổng quan</div>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.dashboard.data') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Thống kê tổng quan
        </a>

        <a href="{{ route('doctor.dashboard') }}"
           class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard bác sĩ
        </a>
        <a href="{{ route('doctor.schedule') }}"
           class="nav-link {{ request()->routeIs('doctor.schedule') ? 'active' : '' }}">
            <i class="bi bi-calendar2-week"></i> Lịch làm việc
        </a>
        <div class="nav-section">Vận hành</div>
        <a href="{{ route('admin.services.index') }}"
           class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-pulse"></i> Dịch vụ & Giá
        </a>
        <a href="{{ route('admin.rooms.index') }}"
           class="nav-link {{ (request()->routeIs('admin.rooms.*') && !request()->routeIs('admin.rooms.schedule.*') && !request()->routeIs('admin.rooms.weekly')) ? 'active' : '' }}">
            <i class="bi bi-door-open"></i> Phòng khám
        </a>
        <a href="{{ route('admin.rooms.schedule.index') }}"
           class="nav-link {{ request()->routeIs('admin.rooms.schedule.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-range"></i> Phân bổ ca trực
        </a>
        <a href="{{ route('admin.rooms.weekly') }}"
           class="nav-link {{ request()->routeIs('admin.rooms.weekly') ? 'active' : '' }}">
            <i class="bi bi-calendar-week"></i> Lịch trực tuần
        </a>
        <a href="{{ route('appointments.create') }}"
           class="nav-link {{ request()->routeIs('appointments.create') ? 'active' : '' }}">
            <i class="bi bi-calendar-plus"></i> Đặt lịch
        </a>
        <a href="{{ route('admin.payments.index') }}"
           class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Thanh toán
        </a>
        <a href="{{ route('admin.queue.index') }}"
           class="nav-link {{ request()->routeIs('admin.queue.*') ? 'active' : '' }}">
            <i class="bi bi-collection-play"></i> Hàng đợi
        </a>
        <a href="{{ route('queue.display.index') }}"
           class="nav-link {{ request()->routeIs('queue.display*') ? 'active' : '' }}">
            <i class="bi bi-tv"></i> Màn hình hàng đợi
        </a>
        <a href="{{ route('admin.news.index') }}"
           class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
            <i class="bi bi-newspaper"></i> Bản tin
        </a>
        <a href="{{ route('admin.rehab.index') }}"
           class="nav-link {{ request()->routeIs('admin.rehab.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-medical"></i> Quản lý Phục hồi
        </a>
        <a href="{{ route('admin.activity-logs.index') }}"
           class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Nhật ký hoạt động
        </a>

        <div class="nav-section">Thống kê & Y tế</div>
        <a href="{{ route('admin.revenue.index') }}"
           class="nav-link {{ request()->routeIs('admin.revenue.index') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i> Doanh thu
        </a>
        <a href="{{ route('admin.doctor-statistics.index') }}"
           class="nav-link {{ request()->routeIs('admin.doctor-statistics.*') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill"></i> Thống kê bác sĩ
        </a>
        <a href="{{ route('admin.vaccination-records.index') }}"
           class="nav-link {{ request()->routeIs('admin.vaccination-records.*') ? 'active' : '' }}">
            <i class="bi bi-patch-check"></i> Tiêm chủng
        </a>
        <a href="{{ route('admin.treatment.index') }}"
           class="nav-link {{ request()->routeIs('admin.treatment.*') ? 'active' : '' }}">
            <i class="bi bi-alarm"></i> Nhắc nhở tuân thủ
        </a>
        <a href="{{ route('admin.nutrition.index') }}"
           class="nav-link {{ request()->routeIs('admin.nutrition.*') ? 'active' : '' }}">
            <i class="bi bi-alarm"></i> Quản lý Dinh dưỡng
        </a>
        @if(in_array(Auth::user()->role_id, [1, 2, 4]))
        <a href="{{ route('admin.patients.search') }}"
           class="nav-link {{ request()->routeIs('admin.patients.search*') ? 'active' : '' }}">
            <i class="bi bi-person-bounding-box"></i> Tìm kiếm bệnh nhân (AI)
        </a>
        @if(Auth::user()->role_id == 1)
        <a href="{{ route('medical-records.index') }}"
           class="nav-link {{ request()->routeIs('medical-records.*') ? 'active' : '' }}">
            <i class="bi bi-folder2-open"></i> Danh sách phiếu khám
        </a>
        @endif
        @endif

        <div class="nav-section">Hỗ trợ</div>
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
        <a href="{{ route('admin.chatroom.index') }}"
           class="nav-link {{ request()->routeIs('admin.chatroom.*') ? 'active' : '' }}">
            <i class="bi bi-chat-dots"></i> Chatroom CSKH
            <span id="admin-unread-badge" style="background:#ef4444;color:#fff;border-radius:9999px;
                  padding:1px 7px;font-size:11px;font-weight:700;display:none;margin-left:auto;">0</span>
        </a>
        @endif
    </nav>

    {{-- User info ở cuối sidebar --}}
    <div style="padding:16px 20px; border-top:1px solid #f1f5f9">
        @auth
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                {{ substr(Auth::user()->full_name ?? Auth::user()->name ?? 'A', 0, 1) }}
            </div>
            <div style="overflow:hidden">
                <div style="font-size:13px; color:#1e293b; font-weight:600; white-space:nowrap; text-overflow:ellipsis;">
                    {{ Auth::user()->full_name ?? Auth::user()->name ?? 'Admin' }}
                </div>
                <div style="font-size:11px; color:#64748b; white-space:nowrap; text-overflow:ellipsis;">
                    {{ Auth::user()->email }}
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger w-100" style="font-size:12px">
                <i class="bi bi-box-arrow-right me-1"></i>Đăng xuất
            </button>
        </form>
        @endauth
    </div>
</div>

{{-- ══ MAIN WRAP ═════════════════════════════════════════════════════ --}}
<div id="main-wrap">

    {{-- Topbar --}}
    <div id="topbar">
        <span class="page-title">@yield('title', 'Trang quản trị')</span>
        <div class="d-flex align-items-center gap-3">
            @auth
                <x-notification-bell />
            @endauth
            <span class="text-muted small">
                <i class="bi bi-calendar3 me-1"></i>
                {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>

    {{-- Content --}}
    <div id="content">
        @yield('content')
    </div>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

<script>
// Script để cập nhật badge thông báo chưa đọc cho admin
async function updateAdminUnreadCount() {
    try {
        const res = await fetch('/admin/chatroom/list');
        const data = await res.json();
        if (data.success) {
            const totalUnread = data.rooms.reduce((sum, r) => sum + (r.unread_count || 0), 0);
            const badge = document.getElementById('admin-unread-badge');
            if (badge) {
                if (totalUnread > 0) {
                    badge.style.display = 'inline-block';
                    badge.textContent = totalUnread;
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    } catch(e) {}
}

if (document.getElementById('admin-unread-badge')) {
    updateAdminUnreadCount();
    setInterval(updateAdminUnreadCount, 15000); // Cập nhật mỗi 15 giây
}
</script>

@stack('scripts')
    @auth
        @include('components.chat-widget')
    @endauth
@include('components.back-to-previous')
</body>
</html>
