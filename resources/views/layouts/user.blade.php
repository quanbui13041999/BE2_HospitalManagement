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

    <style>
        .navbar .navbar-collapse {
            visibility: visible !important;
        }
        
        /* Premium Custom Navbar Styling */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
            padding-top: 12px !important;
            padding-bottom: 12px !important;
            transition: all 0.3s;
        }
        .navbar-custom .nav-link {
            color: #475569 !important;
            font-weight: 500;
            padding: 8px 16px !important;
            border-radius: 99px;
            transition: all 0.3s ease;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .navbar-custom .nav-link:hover {
            color: var(--accent-dark) !important;
            background: var(--accent-light);
        }
        .navbar-custom .nav-link.active {
            color: var(--accent) !important;
            background: var(--accent-light) !important;
            font-weight: 600;
        }
        .navbar-custom .navbar-brand {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--accent) !important;
            letter-spacing: -0.02em;
            transition: transform 0.3s;
        }
        .navbar-custom .navbar-brand:hover {
            transform: scale(1.02);
        }
        .dropdown-menu-custom {
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
            border-radius: 16px !important;
            padding: 8px !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(16px) !important;
            margin-top: 8px !important;
        }
        .dropdown-menu-custom .dropdown-item {
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #475569;
            transition: all 0.2s;
        }
        .dropdown-menu-custom .dropdown-item:hover {
            background-color: var(--accent-light) !important;
            color: var(--accent) !important;
        }
    </style>

    @stack('styles')
</head>

<body>
    {{-- Header Navigation --}}
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <i class="bi bi-heart-pulse-fill"></i> MediCore<sup>®</sup>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUser">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarUser">
                <ul class="navbar-nav ms-auto align-items-center mb-2 mb-lg-0 gap-1">
                    <!-- Trang chủ -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            Trang chủ
                        </a>
                    </li>
                    
                    @auth
                        <!-- Hồ sơ -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                                Hồ sơ
                            </a>
                        </li>
                        
                        <!-- Lịch hẹn -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('lich-hen*') ? 'active' : '' }}" href="/lich-hen">
                                Lịch hẹn
                            </a>
                        </li>
                    @endauth

                    <!-- Bản tin -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}" href="{{ route('news.index') }}">
                            Bản tin
                        </a>
                    </li>

                    @auth
                        <!-- Quản lý bác sĩ (chỉ cho Bác sĩ & Admin) -->
                        @if(Auth::user()->role_id == 2 || Auth::user()->role_id == 1 || Auth::user()->isDoctor)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}">
                                    Quản lý bác sĩ
                                </a>
                            </li>
                        @endif
                    @endauth

                    <!-- Khoa phòng -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.services.index') ? 'active' : '' }}" href="{{ route('user.services.index') }}">
                            Khoa phòng
                        </a>
                    </li>

                    @auth
                        <!-- Tuân thủ điều trị -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('treatment.index') ? 'active' : '' }}" href="{{ route('treatment.index') }}">
                                Tuân thủ điều trị
                            </a>
                        </li>

                        <!-- Dashboard (Chỉ cho Admin & Bác sĩ) -->
                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                            <li class="nav-item">
                                <a class="nav-link {{ (request()->routeIs('admin.news.*') || request()->routeIs('admin.dashboard')) ? 'active' : '' }}" href="{{ route('admin.news.index') }}">
                                    Dashboard
                                </a>
                            </li>
                        @endif

                        <!-- Notification bell widget -->
                        <li class="nav-item px-2 d-flex align-items-center">
                            <x-notification-bell />
                        </li>

                        <!-- Đăng xuất -->
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-sm btn-outline-danger px-3 py-1.5 rounded-pill" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="font-size: 13px; font-weight:600;">
                                <i class="bi bi-box-arrow-right me-1"></i>Đăng xuất
                            </a>
                            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                                @csrf
                            </form>
                        </li>
                    @else
                        <!-- Đăng nhập / Đăng ký cho Khách -->
                        <li class="nav-item ms-lg-3">
                            <a class="nav-link" href="{{ route('login') }}">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-sm btn-accent px-4 py-2 rounded-pill text-white" href="{{ route('register') }}" style="font-size: 13px; font-weight:600; background-color: var(--accent);">Đăng ký</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

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
    @stack('scripts')

    @auth
        @include('components.chat-widget')
    @endauth
</body>

</html>
