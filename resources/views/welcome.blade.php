<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediCore — Hệ thống Quản lý Bệnh viện</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet" />
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --white: #FFFFFF;
            --black: #0A0F14;
            --muted: #5E6A78;
            --accent: #0A6EBD;
            --accent-light: #E8F3FC;
            --surface: #F4F7FA;
            --serif: 'Playfair Display', Georgia, serif;
            --sans: 'DM Sans', system-ui, sans-serif;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: var(--sans);
            background: var(--white);
            color: var(--black);
        }

        /* ── HERO WRAPPER ── */
        .hero-wrapper {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
            background: var(--white);
        }

        /* ── VIDEO LAYER ── */
        .video-layer {
            position: absolute;
            inset: auto 0 0 0;
            top: 280px;
            z-index: 0;
            pointer-events: none;
        }

        .video-layer video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        /* gradient overlay on video */
        .video-layer::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, var(--white) 0%, transparent 30%, transparent 70%, var(--white) 100%);
            z-index: 1;
            pointer-events: none;
        }

        /* ── MEDICAL GRID PATTERN ── */
        .grid-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            opacity: 0.035;
            background-image:
                linear-gradient(#0A6EBD 1px, transparent 1px),
                linear-gradient(90deg, #0A6EBD 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        /* subtle radial vignette */
        .vignette {
            position: absolute;
            inset: 0;
            z-index: 0;
            background: radial-gradient(ellipse 80% 60% at 50% 0%, transparent 40%, rgba(244, 247, 250, 0.6) 100%);
            pointer-events: none;
        }

        /* ── NAVIGATION ── */
        nav {
            position: relative;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 28px 40px;
            max-width: 1280px;
            margin: 0 auto;
        }

        .logo {
            font-family: var(--serif);
            font-size: 1.75rem;
            color: var(--black);
            letter-spacing: -0.5px;
            text-decoration: none;
            display: flex;
            align-items: baseline;
            gap: 2px;
        }

        .logo .logo-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: var(--accent);
            border-radius: 6px;
            margin-right: 8px;
        }

        .logo .logo-mark svg {
            width: 16px;
            height: 16px;
            fill: white;
        }

        .logo sup {
            font-family: var(--sans);
            font-size: 0.65rem;
            font-weight: 400;
            color: var(--muted);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 36px;
            list-style: none;
        }

        .nav-links a {
            font-family: var(--sans);
            font-size: 0.875rem;
            font-weight: 400;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s ease;
            letter-spacing: 0.01em;
        }

        .nav-links a:first-child,
        .nav-links a.active {
            color: var(--black);
        }

        .nav-links a:hover {
            color: var(--black);
        }

        .nav-cta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-outline {
            font-family: var(--sans);
            font-size: 0.875rem;
            font-weight: 400;
            color: var(--muted);
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 10px 0;
            transition: color 0.2s;
            text-decoration: none;
        }

        .btn-outline:hover {
            color: var(--black);
        }

        .btn-primary {
            font-family: var(--sans);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--white);
            background: var(--black);
            border: none;
            border-radius: 100px;
            padding: 11px 24px;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
            text-decoration: none;
            letter-spacing: 0.01em;
        }

        .btn-primary:hover {
            transform: scale(1.03);
            background: var(--accent);
        }

        /* ── HERO CONTENT ── */
        .hero-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding-top: calc(7rem - 60px);
            padding-bottom: 10rem;
            padding-left: 24px;
            padding-right: 24px;
        }

        /* badge */
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--accent-light);
            color: var(--accent);
            font-family: var(--sans);
            font-size: 0.78rem;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 100px;
            margin-bottom: 32px;
            letter-spacing: 0.03em;
            opacity: 0;
            animation: fadeRise 0.7s ease forwards;
        }

        .hero-badge .dot {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        /* headline */
        .hero-headline {
            font-family: var(--serif);
            font-size: clamp(3.2rem, 8vw, 6.5rem);
            font-weight: 400;
            line-height: 1.15;
            letter-spacing: -2.5px;
            color: var(--black);
            max-width: 900px;
            margin-bottom: 0;
            opacity: 0;
            animation: fadeRise 0.8s ease 0.15s forwards;
        }

        .hero-headline em {
            font-style: italic;
            color: var(--muted);
        }

        /* description */
        .hero-desc {
            font-family: var(--sans);
            font-size: clamp(1rem, 1.8vw, 1.125rem);
            font-weight: 300;
            color: var(--muted);
            max-width: 580px;
            margin-top: 32px;
            line-height: 1.7;
            letter-spacing: 0.01em;
            opacity: 0;
            animation: fadeRise 0.8s ease 0.3s forwards;
        }

        /* hero cta group */
        .hero-cta-group {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 48px;
            opacity: 0;
            animation: fadeRise 0.8s ease 0.45s forwards;
        }

        .btn-hero-primary {
            font-family: var(--sans);
            font-size: 1rem;
            font-weight: 500;
            color: var(--white);
            background: var(--black);
            border: none;
            border-radius: 100px;
            padding: 18px 52px;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
            text-decoration: none;
            letter-spacing: 0.01em;
        }

        .btn-hero-primary:hover {
            transform: scale(1.03);
            background: var(--accent);
        }

        .btn-hero-outline {
            font-family: var(--sans);
            font-size: 0.9rem;
            font-weight: 400;
            color: var(--muted);
            background: transparent;
            border: 1.5px solid rgba(0, 0, 0, 0.12);
            border-radius: 100px;
            padding: 17px 32px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            letter-spacing: 0.01em;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-hero-outline:hover {
            border-color: rgba(0, 0, 0, 0.3);
            color: var(--black);
        }

        /* ── STATS ROW ── */
        .stats-row {
            display: flex;
            align-items: center;
            gap: 48px;
            margin-top: 72px;
            opacity: 0;
            animation: fadeRise 0.8s ease 0.6s forwards;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .stat-number {
            font-family: var(--serif);
            font-size: 2rem;
            font-weight: 400;
            color: var(--black);
            letter-spacing: -1px;
            line-height: 1;
        }

        .stat-label {
            font-family: var(--sans);
            font-size: 0.75rem;
            font-weight: 400;
            color: var(--muted);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .stat-divider {
            width: 1px;
            height: 40px;
            background: rgba(0, 0, 0, 0.1);
        }

        /* ── FLOATING CARDS ── */
        .floating-cards {
            position: absolute;
            z-index: 5;
            pointer-events: none;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }

        .fcard {
            position: absolute;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 0, 0, 0.07);
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            opacity: 0;
            animation: floatIn 0.8s ease forwards;
        }

        .fcard-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .fcard-icon svg {
            width: 18px;
            height: 18px;
        }

        .fcard-text-title {
            font-family: var(--sans);
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--black);
            line-height: 1.3;
        }

        .fcard-text-sub {
            font-family: var(--sans);
            font-size: 0.7rem;
            font-weight: 400;
            color: var(--muted);
            margin-top: 1px;
        }

        /* card positions */
        .fcard-1 {
            left: 5%;
            top: 32%;
            animation-delay: 0.8s;
        }

        .fcard-2 {
            right: 5%;
            top: 28%;
            animation-delay: 1s;
        }

        .fcard-3 {
            left: 8%;
            top: 60%;
            animation-delay: 1.2s;
        }

        .fcard-4 {
            right: 6%;
            top: 55%;
            animation-delay: 1.1s;
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeRise {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(0.8);
            }
        }

        @keyframes floatY {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-6px);
            }
        }

        .fcard {
            animation: floatIn 0.8s ease forwards;
        }

        .fcard-1 {
            animation: floatIn 0.8s ease 0.8s forwards, floatY 4s ease-in-out 1.7s infinite;
        }

        .fcard-2 {
            animation: floatIn 0.8s ease 1s forwards, floatY 4.5s ease-in-out 2s infinite;
        }

        .fcard-3 {
            animation: floatIn 0.8s ease 1.2s forwards, floatY 5s ease-in-out 2.2s infinite;
        }

        .fcard-4 {
            animation: floatIn 0.8s ease 1.1s forwards, floatY 4.2s ease-in-out 2.1s infinite;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            nav {
                padding: 20px 24px;
            }

            .nav-links {
                display: none;
            }

            .hero-content {
                padding-top: 4rem;
                padding-bottom: 6rem;
            }

            .stats-row {
                gap: 24px;
            }

            .fcard-1,
            .fcard-3 {
                display: none;
            }

            .fcard-2 {
                right: 2%;
                top: 18%;
            }

            .fcard-4 {
                right: 2%;
                top: 45%;
            }
        }
    </style>
</head>

<body>

    <div class="hero-wrapper">

        <!-- Background grid -->
        <div class="grid-bg"></div>
        <div class="vignette"></div>

        <!-- Video Background -->
        <div class="video-layer" id="videoLayer">
            <video id="bgVideo" muted playsinline preload="auto">
                <source
                    src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260328_083109_283f3553-e28f-428b-a723-d639c617eb2b.mp4"
                    type="video/mp4" />
            </video>
        </div>

        <!-- Floating Info Cards -->
        <div class="floating-cards" aria-hidden="true">

            <!-- Card 1: Live patient -->
            <div class="fcard fcard-1">
                <div class="fcard-icon" style="background:#E8F3FC;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0A6EBD" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div>
                    <div class="fcard-text-title">Tổng số bệnh nhân</div>
                    <div class="fcard-text-sub">{{ $stats['patients'] }} người đã đăng ký</div>
                </div>
            </div>

            <!-- Card 2: Surgery scheduled -->
            <div class="fcard fcard-2">
                <div class="fcard-icon" style="background:#E8F8EE;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#1A9B5F" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </div>
                <div>
                    <div class="fcard-text-title">Lịch khám hôm nay</div>
                    <div class="fcard-text-sub">{{ $stats['appointments_today'] }} ca đang chờ</div>
                </div>
            </div>

            <!-- Card 3: Alert -->
            <div class="fcard fcard-3">
                <div class="fcard-icon" style="background:#FEF3E8;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                    </svg>
                </div>
                <div>
                    <div class="fcard-text-title">Dịch vụ y tế</div>
                    <div class="fcard-text-sub">{{ $stats['services'] }} dịch vụ đang cung cấp</div>
                </div>
            </div>

            <!-- Card 4: Doctor online -->
            <div class="fcard fcard-4">
                <div class="fcard-icon" style="background:#F0EBFE;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                </div>
                <div>
                    <div class="fcard-text-title">Phòng khám</div>
                    <div class="fcard-text-sub" style="color:#1A9B5F; font-weight:500;">● {{ $stats['rooms'] }} phòng hoạt động</div>
                </div>
            </div>

        </div>

        <!-- Navigation -->
        <nav>
            <a href="{{ route('home') }}" class="logo">
                <span class="logo-mark">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                    </svg>
                </span>
                MediCore<sup>®</sup>
            </a>

            <ul class="nav-links">
                <li><a href="{{ route('home') }}" class="active">Trang chủ</a></li>
                @auth
                <li><a href="{{ route('profile.show') }}">Hồ sơ</a></li>
                <li><a href="{{ route('appointments.index') }}">Lịch hẹn</a></li>
                @endauth
                <li><a href="{{ route('news.index') }}">Bản tin</a></li>
                <li><a href="{{ route('doctors.index') }}">Bác sĩ</a></li>
                <li><a href="{{ route('user.services.index') }}">Khoa phòng</a></li>
            </ul>

            <div class="nav-cta">
                @auth
                @if (Auth::user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="btn-outline">Dashboard</a>
                @else
                <a href="{{ route('Home.trangchu') }}" class="btn-outline">Trang của tôi</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-primary" style="background:#f44336; padding: 11px 24px; border:none; cursor:pointer; color:white; font-weight:500; font-size:0.875rem; border-radius:100px;">Đăng xuất</button>
                </form>
                @else
                <a href="{{ route('login') }}" class="btn-outline">Đăng nhập</a>
                <a href="{{ route('register') }}" class="btn-primary">Dùng thử miễn phí</a>
                @endauth
            </div>
        </nav>

        <!-- Hero Content -->
        <div class="hero-content">

            <div class="hero-badge">
                <span class="dot"></span>
                Phiên bản 2.4 — Hệ thống mới ra mắt
            </div>

            <h1 class="hero-headline">
                Vượt giới hạn,<br />
                <em>chúng tôi xây dựng</em><br />
                sức khỏe bền vững.
            </h1>

            <p class="hero-desc">
                Nền tảng quản lý bệnh viện toàn diện — từ hồ sơ bệnh nhân, lịch khám, điều phối bác sĩ đến báo cáo tài
                chính. Tất cả trong một hệ thống thông minh, an toàn và hiện đại.
            </p>

            <div class="hero-cta-group">
                <a href="{{ route('appointments.create') }}" class="btn-hero-primary">Đặt lịch ngay</a>
                <a href="{{ route('user.services.index') }}" class="btn-hero-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <polygon points="10 8 16 12 10 16 10 8" />
                    </svg>
                    Xem dịch vụ
                </a>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['doctors'] }}</span>
                    <span class="stat-label">Bác sĩ</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['patients'] }}</span>
                    <span class="stat-label">Bệnh nhân</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['total_appointments'] }}</span>
                    <span class="stat-label">Lượt khám</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['services'] }}</span>
                    <span class="stat-label">Dịch vụ</span>
                </div>
            </div>

        </div>

    </div>

    <script>
        const video = document.getElementById('bgVideo');
        const FADE_DURATION = 0.5;
        let rafId = null;
        let loopRestarting = false;

        function tick() {
            if (!video.duration || isNaN(video.duration)) {
                rafId = requestAnimationFrame(tick);
                return;
            }
            const t = video.currentTime;
            const d = video.duration;

            if (t < FADE_DURATION) {
                video.style.opacity = t / FADE_DURATION;
            } else if (t > d - FADE_DURATION) {
                video.style.opacity = Math.max(0, (d - t) / FADE_DURATION);
            } else {
                video.style.opacity = 1;
            }
            rafId = requestAnimationFrame(tick);
        }

        video.addEventListener('ended', () => {
            if (loopRestarting) return;
            loopRestarting = true;
            video.style.opacity = 0;
            cancelAnimationFrame(rafId);
            setTimeout(() => {
                video.currentTime = 0;
                video.play().then(() => {
                    loopRestarting = false;
                    rafId = requestAnimationFrame(tick);
                }).catch(() => {
                    loopRestarting = false;
                });
            }, 100);
        });

        video.addEventListener('canplay', () => {
            video.play().then(() => {
                rafId = requestAnimationFrame(tick);
            }).catch(() => {});
        });

        video.load();
    </script>

    @auth
        @include('components.chat-widget')
    @endauth
</body>

</html>
