<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Không có quyền truy cập | Bệnh viện</title>
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
    <div class="error-card">
        <div class="error-icon-wrap">
            <i class="bi bi-shield-lock-fill" style="font-size: 40px; color: #E65100;"></i>
        </div>
        <div class="error-code">403</div>
        <div class="error-title">Không có quyền truy cập</div>
        <div class="error-desc">
            Bạn không được phép truy cập vào trang hoặc thực hiện hành động này.<br>
            Nếu bạn cho rằng đây là lỗi, vui lòng liên hệ quản trị viên hệ thống.
        </div>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn-back">
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
</body>
</html>
