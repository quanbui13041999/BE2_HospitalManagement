<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Không thể truy cập | Bệnh viện</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #fff3e0 0%, #fce4ec 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .error-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            padding: 56px 48px;
            max-width: 520px;
            width: 100%;
            text-align: center;
        }
        .error-icon-wrap {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #FFF3E0, #FFE0B2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .error-code {
            font-size: 72px;
            font-weight: 900;
            color: #E65100;
            line-height: 1;
            letter-spacing: -2px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a2332;
            margin: 12px 0 8px;
        }
        .error-desc {
            color: #78909C;
            font-size: 14.5px;
            line-height: 1.6;
        }
        .btn-home {
            background: linear-gradient(135deg, #E65100, #F4511E);
            border: none;
            color: #fff;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 32px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(230,81,0,0.3);
            color: #fff;
        }
        .btn-back {
            color: #546E7A;
            text-decoration: none;
            font-size: 13.5px;
            display: block;
            margin-top: 14px;
        }
        .btn-back:hover { color: #E65100; }
    </style>
</head>
<body>
    <div id="app-notification-stack" style="position:fixed;top:18px;right:18px;z-index:20000;width:min(420px,calc(100vw - 32px));display:flex;flex-direction:column;gap:10px"></div>
    <div class="error-card">
        <div class="error-icon-wrap">
            <i class="bi bi-shield-lock-fill" style="font-size: 40px; color: #E65100;"></i>
        </div>
        <div class="error-title">Không thể truy cập trang này</div>
        <div class="error-desc">
            Hệ thống sẽ tự quay lại trang trước sau giây lát.
            Nếu trang trước cũng không hợp lệ, bạn sẽ được đưa về trang chủ.
        </div>
        <a href="{{ url('/') }}" class="btn-back">
            <i class="bi bi-arrow-left me-1"></i>Quay lại trang trước
        </a>
        @auth
            @if(auth()->user()?->role_id == 1)
                <a href="{{ route('admin.dashboard') }}" class="btn-home">
                    <i class="bi bi-house-door-fill"></i>Về Dashboard Admin
                </a>
            @else
                <a href="{{ route('Home.trangchu') }}" class="btn-home">
                    <i class="bi bi-house-door-fill"></i>Về Trang chủ
                </a>
            @endif
        @else
            <a href="{{ route('home') }}" class="btn-home">
                <i class="bi bi-house-door-fill"></i>Về Trang chủ
            </a>
        @endauth
    </div>
    <script>
        const targetUrl = "{{ url('/') }}";
        const notice = document.createElement('div');
        notice.textContent = 'Bạn không thể truy cập trang này. Trang sẽ được tải lại.';
        notice.setAttribute('role', 'alert');
        notice.style.cssText = 'padding:12px 14px;border-radius:10px;border:1px solid #fde68a;background:#fffbeb;color:#92400e;box-shadow:0 16px 40px rgba(15,23,42,.16);font-size:14px;line-height:1.45';
        document.getElementById('app-notification-stack')?.appendChild(notice);
        setTimeout(function () {
            window.location.replace(targetUrl);
        }, 1800); /* fixed: khong dung o man 403, thong bao roi quay ve trang an toan */
    </script>
</body>
</html>
