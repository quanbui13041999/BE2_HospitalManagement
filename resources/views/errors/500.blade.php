<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đã xảy ra lỗi | Bệnh viện</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
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
            padding: 48px;
            max-width: 520px;
            width: 100%;
            text-align: center;
        }
        .error-icon-wrap {
            width: 92px;
            height: 92px;
            background: #eef2ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a2332;
            margin-bottom: 8px;
        }
        .error-desc {
            color: #64748b;
            font-size: 14.5px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div id="app-notification-stack" style="position:fixed;top:18px;right:18px;z-index:20000;width:min(420px,calc(100vw - 32px));display:flex;flex-direction:column;gap:10px"></div>
    <div class="error-card">
        <div class="error-icon-wrap">
            <i class="bi bi-arrow-clockwise" style="font-size: 40px; color: #4f46e5;"></i>
        </div>
        <div class="error-title">Đã xảy ra lỗi, trang sẽ được tải lại</div>
        <div class="error-desc">
            Vui lòng chờ giây lát. Hệ thống sẽ quay lại trang trước hoặc trang chủ.
        </div>
    </div>
    <script>
        const targetUrl = @json(url('/'));
        const notice = document.createElement('div');
        notice.textContent = 'Đã xảy ra lỗi, trang sẽ được tải lại.';
        notice.setAttribute('role', 'alert');
        notice.style.cssText = 'padding:12px 14px;border-radius:10px;border:1px solid #fde68a;background:#fffbeb;color:#92400e;box-shadow:0 16px 40px rgba(15,23,42,.16);font-size:14px;line-height:1.45';
        document.getElementById('app-notification-stack')?.appendChild(notice);
        setTimeout(function () {
            window.location.replace(targetUrl);
        }, 1800); /* fixed: khong lo man loi he thong, thong bao roi quay ve trang an toan */
    </script>
</body>
</html>
