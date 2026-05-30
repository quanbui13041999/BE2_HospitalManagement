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

    <style>
        body { background: #f4f6fb; font-family: 'Segoe UI', sans-serif; }

        /* ── Sidebar ── */
        #sidebar {
            width: 240px; min-height: 100vh; background: #0D47A1;
            position: fixed; top: 0; left: 0; z-index: 100;
            display: flex; flex-direction: column;
        }
        #sidebar .brand {
            padding: 20px 20px 16px;
            font-size: 17px; font-weight: 700; color: #fff;
            border-bottom: 1px solid rgba(255,255,255,.15);
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,.75); padding: 10px 20px;
            font-size: 13.5px; border-radius: 0; transition: .15s;
            display: flex; align-items: center; gap: 10px;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(255,255,255,.15); color: #fff;
        }
        #sidebar .nav-section {
            padding: 14px 20px 4px;
            font-size: 10.5px; text-transform: uppercase;
            letter-spacing: .08em; color: rgba(255,255,255,.4);
        }

        /* ── Main area ── */
        #main-wrap { margin-left: 240px; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Topbar ── */
        #topbar {
            background: #fff; border-bottom: 1px solid #e8ecf1;
            padding: 0 24px; height: 58px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 99;
        }
        #topbar .page-title { font-weight: 700; font-size: 15px; color: #1a2332; }

        /* ── Content ── */
        #content { padding: 28px 24px; flex: 1; }
    </style>

    @stack('styles')
</head>
<body>

{{-- ══ SIDEBAR ══════════════════════════════════════════════════════ --}}
<div id="sidebar">
    <div class="brand">
        <i class="bi bi-hospital me-2"></i>HospitalC Admin
    </div>

    <nav class="mt-2 flex-fill">
        <div class="nav-section">Tổng quan</div>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.dashboard.data') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Thống kê tổng quan
        </a>

        <div class="nav-section">Danh mục</div>
        <a href="{{ route('admin.services.index') }}"
           class="nav-link {{ request()->is('services*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-pulse"></i> Dịch vụ & Giá
        </a>
        <a href="{{ url('/admin/departments') }}"
           class="nav-link {{ request()->is('admin/departments*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Khoa / Phòng ban
        </a>
        <a href="{{ url('/admin/doctors') }}"
           class="nav-link {{ request()->is('admin/doctors*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Bác sĩ
        </a>
        <a href="{{ route('admin.rooms.index') }}"
           class="nav-link {{ request()->is('admin/rooms*') ? 'active' : '' }}">
            <i class="bi bi-door-open"></i> Phòng khám
        </a>

        <div class="nav-section">Vận hành</div>
        <a href="{{ url('/admin/appointments') }}"
           class="nav-link {{ request()->is('admin/appointments*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> Lịch hẹn
        </a>
        <a href="{{ url('/admin/schedules') }}"
           class="nav-link {{ request()->is('admin/schedules*') ? 'active' : '' }}">
            <i class="bi bi-clock"></i> Ca làm việc
        </a>

        <div class="nav-section">Hệ thống</div>
        <a href="{{ url('/admin/users') }}"
           class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Người dùng
        </a>
    </nav>

    {{-- User info ở cuối sidebar --}}
    <div style="padding:14px 20px; border-top:1px solid rgba(255,255,255,.15)">
        @auth
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-circle fs-5 text-white-50"></i>
            <div>
                <div style="font-size:12.5px; color:#fff; font-weight:600">
                    {{ Auth::user()->full_name ?? Auth::user()->name ?? 'Admin' }}
                </div>
                <div style="font-size:11px; color:rgba(255,255,255,.5)">
                    {{ Auth::user()->email }}
                </div>
            </div>
        </div>
        <form method="POST" action="{{ url('/logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-sm w-100"
                    style="background:rgba(255,255,255,.1); color:#fff; font-size:12px">
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
@stack('scripts')
</body>
</html>
