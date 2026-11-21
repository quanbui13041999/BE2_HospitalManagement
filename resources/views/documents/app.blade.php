<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Lưu Trữ & Tra Cứu Tài Liệu Y Khoa Cá Nhân')</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/document.css') }}">
@stack('styles')
</head>
<body>

<!-- ═══ HEADER ═══ -->
<header class="header">
    <a href="{{ route('documents.index') }}" class="header-brand">
        <span class="header-icon">🏥</span>
        <div>
            <div class="header-title">Lưu Trữ &amp; Tra Cứu Tài Liệu Y Khoa Cá Nhân</div>
            <div class="header-breadcrumb">
                <span class="header-file">medical_documents</span>
                <span>›</span>
                @yield('breadcrumb', 'Kho lưu trữ số tài liệu y tế – Upload, phân loại và xem trực tuyến')
            </div>
        </div>
    </a>
</header>

<!-- ═══ FLASH MESSAGES ═══ -->
@if(session('success'))
<div class="alert alert-success" id="flash-msg">
    <span>✅</span> {{ session('success') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif

@if(session('error'))
<div class="alert alert-error" id="flash-msg">
    <span>❌</span> {{ session('error') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-error" id="flash-msg">
    <span>⚠</span> {{ session('warning') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif

<!-- ═══ CONTENT ═══ -->
<main>
    @yield('content')
</main>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')

<script>
    // Auto-dismiss flash message sau 4 giây
    setTimeout(() => {
        const msg = document.getElementById('flash-msg');
        if (msg) msg.style.opacity = '0', setTimeout(() => msg.remove(), 400);
    }, 4000);
</script>
</body>
</html>
