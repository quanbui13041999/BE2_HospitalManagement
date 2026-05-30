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

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            transition: all 0.3s;
        }
        .navbar-custom .nav-link {
            color: #475569 !important;
            font-weight: 550;
            padding: 8px 14px !important;
            border-radius: 99px;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .navbar-custom .nav-link:hover {
            color: #2563eb !important;
            background: rgba(37, 99, 235, 0.06);
        }
        .navbar-custom .nav-link.active {
            color: #2563eb !important;
            background: rgba(37, 99, 235, 0.08) !important;
            font-weight: 600;
        }
        .navbar-custom .navbar-brand {
            font-size: 1.25rem;
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
            background-color: rgba(37, 99, 235, 0.06) !important;
            color: #2563eb !important;
        }
    </style>

    @stack('styles')
</head>

<body>
    {{-- Header Navigation --}}
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="{{ route('home') }}">
                <i class="bi bi-hospital"></i> Bệnh viện
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUser">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarUser">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house-door"></i> Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.services.index') ? 'active' : '' }}" href="{{ route('user.services.index') }}">
                            <i class="bi bi-clipboard2-pulse"></i> Dịch vụ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('queue.display.*') ? 'active' : '' }}" href="{{ route('queue.display.index') }}">
                            <i class="bi bi-tv"></i> Màn Hình Hàng Đợi
                        </a>
                    </li>
                    @auth
                    <li class="nav-item d-flex align-items-center">
                        <x-notification-bell />
                    </li>
                    {{-- Nút DEMO MỚI (bên trái nút lịch hẹn) --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('lich-hen*') ? 'active' : '' }}" href="/lich-hen"> 
                            <i class="bi bi-calendar-check"></i> Lịch hẹn
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('treatment.index') ? 'active' : '' }}" href="{{ route('treatment.index') }}">
                            <i class="bi bi-alarm"></i> Tuân thủ điều trị
                        </a>
                    </li>
                    @if(Auth::user()->role_id == 3 || (Auth::user()->role ?? '') == 'patient')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('patient.nutrition.*') ? 'active' : '' }}" href="{{ route('patient.nutrition.index') }}">
                            <i class="bi bi-heart-pulse"></i> Dinh dưỡng
                        </a>
                    </li>
                    @endif
                    @if(Auth::user()->isDoctor())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}">
                            <i class="bi bi-person-workspace"></i> Dashboard bác sĩ
                        </a>
                    </li>
                    @endif
                    @php
                    $user = Auth::user();
                    $roleId = $user->role_id ?? ($user->role === 'doctor' ? 2 : ($user->role === 'patient' ? 3 : 0));
                    $demoLink = '#';
                    $demoText = 'Demo';
                    $demoIcon = 'bi-star';
                    $demoClass = 'btn-outline-secondary';

                    if ($roleId == 2 || ($user->role == 'doctor')) {
                    $demoLink = route('doctor.dashboard');
                    $demoText = 'Dashboard BS';
                    $demoIcon = 'bi-person-workspace';
                    $demoClass = 'btn-outline-primary';
                    } elseif ($roleId == 3 || ($user->role == 'patient')) {
                    $demoLink = route('medical_history.index');
                    $demoText = 'Demo Bệnh án';
                    $demoIcon = 'bi-file-medical';
                    $demoClass = 'btn-outline-success';
                    } elseif ($roleId == 1 || ($user->role == 'admin')) {
                    $demoLink = route('admin.dashboard');
                    $demoText = 'Demo Admin';
                    $demoIcon = 'bi-speedometer2';
                    $demoClass = 'btn-outline-warning';
                    }
                    @endphp

                    <li class="nav-item me-2">
                        <a class="nav-link {{ $demoClass }}"
                            href="{{ $demoLink }}"
                            style="border-radius: 20px; padding: 5px 15px; border-width: 1px; border-style: solid;">
                            <i class="bi {{ $demoIcon }}"></i> {{ $demoText }}
                        </a>
                    </li>
                   
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->full_name ?? Auth::user()->name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">

                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">Hồ sơ cá nhân</a></li>
                            @if(Auth::user()->role_id == 1)
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Đăng ký</a>
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
