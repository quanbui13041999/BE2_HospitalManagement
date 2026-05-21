<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Hospital Booking</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body { background: #f4f6fb; font-family: 'Segoe UI', sans-serif; }

        /* ── Sidebar ── */
        #sidebar {
            width: 260px; min-height: 100vh; background: #fff;
            position: fixed; top: 0; left: 0; z-index: 100;
            display: flex; flex-direction: column;
            border-right: 1px solid #e2e8f0;
        }
        #sidebar .brand {
            padding: 20px 24px;
            font-size: 18px; font-weight: 700; color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
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
    </style>

    @stack('styles')
</head>
<body>

{{-- ══ SIDEBAR ══════════════════════════════════════════════════════ --}}
<div id="sidebar">
    <div class="brand">
        <i class="bi bi-hospital me-2 text-primary"></i>Hospital Admin
    </div>

    <nav class="mt-3 flex-fill">
        <div class="nav-section">Tổng quan</div>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
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
        <a href="{{ route('admin.payments.index') }}"
           class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Thanh toán
        </a>
        <a href="{{ route('admin.news.index') }}"
           class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
            <i class="bi bi-newspaper"></i> Bản tin
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
</body>
</html>
