{{-- resources/views/layouts/nutrition.blade.php --}}
{{-- Layout dùng chung cho toàn bộ module dinh dưỡng --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dinh dưỡng') – Bệnh viện</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #198754;
            --primary-light: #d1e7dd;
            --danger-light: #f8d7da;
            --card-shadow: 0 2px 12px rgba(0,0,0,.08);
        }
        body { background: #f4f7f4; font-family: 'Segoe UI', sans-serif; }
        .sidebar { min-height: 100vh; background: #fff; border-right: 1px solid #dee2e6; }
        .nav-link.active { background: var(--primary-light); color: var(--primary) !important; border-radius: 8px; font-weight: 600; }
        .card { border: none; border-radius: 14px; box-shadow: var(--card-shadow); }
        .badge-should-eat   { background: var(--primary-light); color: #0a3622; }
        .badge-should-avoid { background: var(--danger-light);  color: #58151c; }
        .progress { height: 20px; border-radius: 10px; }
        .meal-card { border-left: 4px solid var(--primary); }
    </style>

    @stack('styles')
</head>
<body>

<div class="d-flex">

    {{-- Sidebar --}}
    <nav class="sidebar p-3" style="width:240px; flex-shrink:0;">
        <a href="/" class="d-flex align-items-center mb-4 text-decoration-none">
            <i class="bi bi-hospital fs-4 text-success me-2"></i>
            <span class="fw-bold text-success">BV Trung tâm</span>
        </a>
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a href="{{ route('patient.nutrition.index') }}"
                   class="nav-link text-dark {{ request()->routeIs('patient.nutrition.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text me-2"></i> Dinh dưỡng của tôi
                </a>
            </li>
            @if(in_array(auth()->user()->role_id ?? 0, [1, 2]))
            <li class="nav-item">
                <a href="{{ route('admin.nutrition.index') }}"
                   class="nav-link text-dark {{ request()->routeIs('admin.nutrition.index', 'admin.nutrition.create', 'admin.nutrition.edit') ? 'active' : '' }}">
                    <i class="bi bi-newspaper me-2"></i> Quản lý bài viết
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.nutrition.rules.index') }}"
                   class="nav-link text-dark {{ request()->routeIs('admin.nutrition.rules.*') ? 'active' : '' }}">
                    <i class="bi bi-lightbulb me-2"></i> Quy tắc dinh dưỡng
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.nutrition.foods.index') }}"
                   class="nav-link text-dark {{ request()->routeIs('admin.nutrition.foods.*') ? 'active' : '' }}">
                    <i class="bi bi-egg-fried me-2"></i> Danh mục thực phẩm
                </a>
            </li>
            @endif
            <li class="nav-item mt-3">
                <a href="{{ route('logout') }}"
                   class="nav-link text-danger"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-left me-2"></i> Đăng xuất
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </li>
        </ul>
    </nav>

    {{-- Main content --}}
    <main class="flex-grow-1 p-4">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
@include('components.back-to-previous')
</body>
</html>
