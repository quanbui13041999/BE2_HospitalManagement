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
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @auth
        <x-notification-bell :floating="true" />
    @endauth

    @yield('content')
    @include('components.back-to-previous')
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @if(session('reload_page'))
    <script>
        alert(@js(session('warning') ?? session('error') ?? 'Dữ liệu đã thay đổi, trang sẽ được tải lại.'));
        window.location.replace(window.location.href);
    </script>
    @endif
    @stack('scripts')

    @auth
        @include('components.chat-widget')
    @endauth
</body>
</html>
