@props(['showBell' => true])

@php
    $user = auth()->user();
    $isPatient = $user && method_exists($user, 'isPatient') ? $user->isPatient() : false;
    $isDoctor = $user && method_exists($user, 'isDoctor') ? $user->isDoctor() : false;
    $isAdmin = $user && method_exists($user, 'isAdmin') ? $user->isAdmin() : (bool) ($user->is_admin ?? false);
    $displayName = $user->full_name ?? $user->name ?? 'Người dùng';
@endphp

<style>
    .hc-site-nav {
        background: rgba(255, 255, 255, 0.94);
        border-bottom: 1px solid #e5e7eb;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        position: sticky;
        top: 0;
        z-index: 110;
    }

    .hc-site-nav__inner {
        width: min(1320px, calc(100% - 32px));
        min-height: 70px;
        margin: 0 auto;
        padding: 10px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .hc-site-nav__brand {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #1e293b;
        text-decoration: none;
        flex: 0 0 auto;
    }

    .hc-site-nav__logo {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        background: linear-gradient(135deg, #0f52ba, #2563eb);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
    }

    .hc-site-nav__name {
        font-size: 1.18rem;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #0f172a;
    }

    .hc-site-nav__sub {
        margin-top: 2px;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 500;
    }

    .hc-site-nav__links {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex: 1 1 520px;
        flex-wrap: wrap;
        min-width: 260px;
    }

    .hc-site-nav__link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 36px;
        padding: 8px 15px;
        border-radius: 999px;
        color: #475569;
        text-decoration: none;
        font-size: 0.86rem;
        font-weight: 650;
        line-height: 1.2;
        white-space: normal;
        transition: background 0.16s ease, color 0.16s ease, box-shadow 0.16s ease;
    }

    .hc-site-nav__link:hover,
    .hc-site-nav__link.is-active {
        color: #0f52ba;
        background: #eef4ff;
        box-shadow: inset 0 0 0 1px rgba(15, 82, 186, 0.08);
    }

    .hc-site-nav__right {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex: 0 1 auto;
        flex-wrap: wrap;
    }

    .hc-site-nav__user {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        max-width: 190px;
        padding: 5px 12px 5px 6px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155;
        font-size: 0.84rem;
        font-weight: 650;
    }

    .hc-site-nav__avatar {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        background: linear-gradient(135deg, #2563eb, #0f52ba);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 800;
        flex: 0 0 auto;
    }

    .hc-site-nav__username {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hc-site-nav__logout,
    .hc-site-nav__auth {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 7px 15px;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #475569;
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
    }

    .hc-site-nav__logout:hover {
        border-color: #fecaca;
        background: #fef2f2;
        color: #dc2626;
    }

    .hc-site-nav__auth--primary {
        border-color: #0f52ba;
        background: #0f52ba;
        color: #fff;
    }

    @media (max-width: 720px) {
        .hc-site-nav__inner {
            width: min(100% - 20px, 1320px);
            align-items: flex-start;
        }

        .hc-site-nav__links {
            order: 3;
            justify-content: flex-start;
            flex-basis: 100%;
        }

        .hc-site-nav__link {
            padding: 8px 12px;
            font-size: 0.82rem;
        }
    }
</style>

<nav class="hc-site-nav">
    <div class="hc-site-nav__inner">
        <a href="{{ route('home') }}" class="hc-site-nav__brand">
            <span class="hc-site-nav__logo">H</span>
            <span>
                <span class="hc-site-nav__name">HospitalC</span>
                <span class="hc-site-nav__sub">Đặt lịch thông minh</span>
            </span>
        </a>

        <div class="hc-site-nav__links">
            <a href="{{ route('home') }}" class="hc-site-nav__link {{ request()->routeIs('home') ? 'is-active' : '' }}">🏠 Trang chủ</a>

            @auth
                <a href="{{ route('profile.show') }}" class="hc-site-nav__link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">👤 Hồ sơ</a>

                @if($isPatient)
                    <a href="{{ route('appointments.index') }}" class="hc-site-nav__link {{ request()->routeIs('appointments.index') || request()->routeIs('appointments.edit') ? 'is-active' : '' }}">📋 Lịch hẹn</a>
                    <a href="{{ route('medical_history.index') }}" class="hc-site-nav__link {{ request()->routeIs('medical_history.*') ? 'is-active' : '' }}">📁 Hồ sơ bệnh án</a>
                @endif

                @if($isPatient || $isAdmin)
                    <a href="{{ route('appointments.create') }}" class="hc-site-nav__link {{ request()->routeIs('appointments.create') ? 'is-active' : '' }}">✨ Đặt lịch mới</a>
                       <a href="{{ route('appointments.index') }}" class="hc-site-nav__link {{ request()->routeIs('appointments.index') || request()->routeIs('appointments.edit') ? 'is-active' : '' }}">📋 Lịch hẹn</a>
                @endif
            @endauth

            <a href="{{ route('news.index') }}" class="hc-site-nav__link {{ request()->routeIs('news.*') ? 'is-active' : '' }}">📰 Bản tin</a>
            <a href="{{ route('user.services.index') }}" class="hc-site-nav__link {{ request()->routeIs('user.services.*') ? 'is-active' : '' }}">🏥 Khoa phòng</a>
            <a href="{{ route('queue.display.index') }}" class="hc-site-nav__link {{ request()->routeIs('queue.display*') ? 'is-active' : '' }}">📺 Hàng đợi</a>

            @auth
                @if($isDoctor || $isAdmin)
                    <a href="{{ route('doctor.dashboard') }}" class="hc-site-nav__link {{ request()->routeIs('doctor.dashboard') ? 'is-active' : '' }}">🩺 Dashboard bác sĩ</a>
                    @if($isDoctor)
                        <a href="{{ route('queue.doctor.index') }}" class="hc-site-nav__link {{ request()->routeIs('queue.doctor.*') ? 'is-active' : '' }}">🧑‍⚕️ Khám bệnh</a>
                    @endif
                    <a href="{{ route('doctor.schedule') }}" class="hc-site-nav__link {{ request()->routeIs('doctor.schedule') ? 'is-active' : '' }}">🗓️ Lịch làm việc</a>
                    <a href="{{ route('medical-records.index') }}" class="hc-site-nav__link {{ request()->routeIs('medical-records.*') ? 'is-active' : '' }}">📁 Hồ sơ bệnh án</a>
                    <a href="{{ route('admin.nutrition.index') }}" class="hc-site-nav__link {{ request()->routeIs('admin.nutrition.*') ? 'is-active' : '' }}">🥗 Quản lí dinh dưỡng</a>
                @endif

                <a href="{{ route('treatment.index') }}" class="hc-site-nav__link {{ request()->routeIs('treatment.*') ? 'is-active' : '' }}">⏰ Tuân thủ điều trị</a>

                @if($isAdmin)
                    <a href="{{ route('admin.dashboard') }}" class="hc-site-nav__link {{ request()->routeIs('admin.*') && ! request()->routeIs('admin.nutrition.*') ? 'is-active' : '' }}">📊 Admin</a>
                @endif
            @endauth
        </div>

        <div class="hc-site-nav__right">
            @auth
                @if($showBell)
                    <x-notification-bell />
                @endif
                <span class="hc-site-nav__user">
                    <span class="hc-site-nav__avatar">{{ strtoupper(substr($displayName, 0, 1)) }}</span>
                    <span class="hc-site-nav__username">{{ $displayName }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="hc-site-nav__logout">Đăng xuất</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hc-site-nav__auth">Đăng nhập</a>
                <a href="{{ route('register') }}" class="hc-site-nav__auth hc-site-nav__auth--primary">Đăng ký</a>
            @endauth
        </div>
    </div>
</nav>
