<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; }
        .container { max-width: 600px; margin: 20px auto; background: white; padding: 24px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        p { line-height: 1.6; }
        .badge { display: inline-block; background: #e3f2fd; color: #1565c0; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: bold; margin-bottom: 12px; }
        .button { display: inline-block; background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 14px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <span class="badge">{{ $article->category }}</span>
        <h2>{{ $article->title }}</h2>

        <p>Kính gửi quý bệnh nhân,</p>
        <p>{{ $article->excerpt }}</p>

        <center>
            <a href="{{ route('news.show', $article->news_id) }}" class="button">Xem chi tiết bản tin</a>
        </center>

        <div class="footer">
            <p>Cảm ơn bạn đã tin tưởng HospitalBooking.</p>
            <p><strong>Bệnh Viện HospitalBooking</strong></p>
        </div>
    </div>
</body>
</html>
