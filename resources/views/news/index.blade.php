@extends('layouts.user')

@section('title', 'Bản tin Bệnh viện')

@section('content')
<div class="bg-black text-white min-h-screen font-sans antialiased overflow-x-hidden">
    {{-- CSS Styles from Prompt --}}
    <style>
        .liquid-glass {
            background: rgba(0, 0, 0, 0.4);
            background-blend-mode: luminosity;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: none;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }
        .liquid-glass::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1.4px;
            background: linear-gradient(180deg,
                rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.1) 20%,
                rgba(255, 255, 255, 0) 40%, rgba(255, 255, 255, 0) 60%,
                rgba(255, 255, 255, 0.1) 80%, rgba(255, 255, 255, 0.3) 100%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .hero-char {
            display: inline-block;
            opacity: 0;
            transform: translateX(-18px);
            transition: opacity 500ms, transform 500ms;
        }
        .hero-char.visible {
            opacity: 1;
            transform: translateX(0);
        }

        /* News List Global Fixes */
        #news-list a {
            text-decoration: none !important;
            color: inherit !important;
        }

        .category-tab {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.03);
            color: #9ca3af !important;
            transition: all 0.3s ease;
        }
        .category-tab:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff !important;
            border-color: rgba(255, 255, 255, 0.2);
        }
        .category-tab.active {
            background: #fff !important;
            color: #000 !important;
            border-color: #fff;
        }

        .fade-in-element {
            opacity: 0;
            transition-property: opacity;
            transition-duration: 1000ms;
        }
        .fade-in-element.visible {
            opacity: 1;
        }

        nav.navbar.sticky-top {
            display: none !important;
        }

        .nav-link-hover:hover {
            color: #d1d5db !important;
        }
        
        .btn-hover-white:hover {
            background: #f3f4f6 !important;
        }

        .btn-liquid-hover:hover {
            background: #fff !important;
            color: #000 !important;
        }

        .news-fixed-nav-wrap {
            position: fixed;
            top: 1rem;
            left: clamp(1.5rem, 4vw, 4rem);
            right: clamp(1.5rem, 4vw, 4rem);
            z-index: 999;
        }

        .news-fixed-nav {
            border-radius: 0.75rem;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(0, 0, 0, 0.62);
        }

        .weather-widget {
            margin-top: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.85rem;
            color: #fff;
            padding: 0.7rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(0, 0, 0, 0.42);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.1);
        }

        .hero-weather-wrap {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 1rem;
        }

        .weather-icon {
            width: 2.35rem;
            height: 2.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 1.25rem;
        }

        .weather-temp {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1;
        }

        .weather-desc {
            color: #d1d5db;
            font-size: 0.78rem;
            margin-top: 0.2rem;
        }

        .news-calm-bg {
            background:
                radial-gradient(circle at 16% 12%, rgba(14, 165, 233, 0.18), transparent 30%),
                radial-gradient(circle at 88% 20%, rgba(56, 189, 248, 0.12), transparent 32%),
                linear-gradient(180deg, #020617 0%, #07111f 48%, #0f172a 100%);
        }

        #news-list {
            color: #f8fafc;
        }

        #news-list h2 {
            color: #f8fafc !important;
            font-weight: 650 !important;
        }

        #news-list p {
            color: #94a3b8 !important;
        }

        #news-list .category-tab {
            background: rgba(15, 23, 42, 0.72) !important;
            border: 1px solid rgba(148, 163, 184, 0.16) !important;
            color: #cbd5e1 !important;
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.18);
        }

        #news-list .category-tab:hover,
        #news-list .category-tab.active {
            background: #38bdf8 !important;
            border-color: #38bdf8 !important;
            color: #082f49 !important;
        }

        #news-list .category-tab:hover {
            color: #fff !important;
        }

        #news-list .news-card {
            background: rgba(15, 23, 42, 0.82) !important;
            border: 1px solid rgba(148, 163, 184, 0.14) !important;
            border-radius: 1.15rem !important;
            box-shadow: 0 18px 40px rgba(2, 6, 23, 0.36);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        #news-list .news-card::before {
            display: none;
        }

        #news-list .news-card:hover {
            border-color: rgba(56, 189, 248, 0.38) !important;
            box-shadow: 0 24px 56px rgba(2, 6, 23, 0.48);
        }

        #news-list .news-thumb {
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }

        #news-list .news-thumb img {
            filter: none !important;
        }

        #news-list .news-thumb-overlay {
            background: linear-gradient(180deg, rgba(2, 6, 23, 0.08), rgba(2, 6, 23, 0.5)) !important;
            opacity: 1 !important;
        }

        #news-list .news-category-badge {
            background: rgba(15, 23, 42, 0.78) !important;
            border-color: rgba(226, 232, 240, 0.22) !important;
            color: #e0f2fe !important;
            box-shadow: 0 8px 20px rgba(2, 6, 23, 0.28);
        }

        #news-list .news-date {
            color: #94a3b8 !important;
        }

        #news-list .news-title,
        #news-list .news-title a {
            color: #f8fafc !important;
        }

        #news-list .news-card:hover .news-title,
        #news-list .news-card:hover .news-title a {
            color: #7dd3fc !important;
        }

        #news-list .news-excerpt {
            color: #94a3b8 !important;
        }

        #news-list .news-read-more {
            color: #7dd3fc !important;
        }

        #news-list .pagination {
            justify-content: center;
            gap: 0.35rem;
        }

        #news-list ul.pagination li.page-item .page-link {
            background: #f8fafc !important;
            border-color: rgba(148, 163, 184, 0.4) !important;
            color: #0f172a !important;
            min-width: 2.4rem;
            text-align: center;
            font-weight: 700;
        }

        #news-list ul.pagination li.page-item .page-link:hover {
            background: #38bdf8 !important;
            border-color: #38bdf8 !important;
            color: #082f49 !important;
        }

        #news-list ul.pagination li.page-item.active .page-link {
            background: #334155 !important;
            border-color: #e0f2fe !important;
            color: #ffffff !important;
            font-weight: 700;
        }

        #news-list ul.pagination li.page-item.disabled .page-link {
            background: #f8fafc !important;
            border-color: rgba(148, 163, 184, 0.34) !important;
            color: #64748b !important;
            opacity: 1;
        }

        body {
            background: #0f172a;
        }

        footer.mt-5 {
            margin-top: 0 !important;
        }

        .hero-container {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: #000;
        }

        .hero-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .hero-overlay {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            padding-left: clamp(1.5rem, 4vw, 4rem);
            padding-right: clamp(1.5rem, 4vw, 4rem);
        }

        .hero-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding-bottom: clamp(3rem, 5vw, 4rem);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            align-items: end;
        }

        @media (min-width: 1024px) {
            .hero-grid {
                grid-template-columns: 1fr 1fr !important;
            }
            .hero-tag-col {
                justify-content: flex-end !important;
            }
            .hero-weather-wrap {
                justify-content: flex-end;
            }
        }
    </style>

    {{-- Hero Section --}}
    <div class="hero-container">
        <video autoplay loop muted playsinline class="hero-video">
            <source src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260403_050628_c4e32401-fab4-4a27-b7a8-6e9291cd5959.mp4" type="video/mp4">
        </video>

        <div class="hero-overlay">
            {{-- Navbar --}}
            <div class="news-fixed-nav-wrap">
                <nav class="liquid-glass news-fixed-nav">
                    <span style="font-size: 1.5rem; font-weight: 600; letter-spacing: -0.04em; color: #fff;">HOPITAL</span>
                    
                    <div class="hidden md:flex items-center gap-8 text-sm">
                        @php $navLinks = ['Trang chủ' => 'home', 'Dịch vụ' => 'user.services.index', 'Bản tin' => 'news.index', 'Đặt lịch' => 'appointments.create']; @endphp
                        @foreach($navLinks as $label => $route)
                            <a href="{{ route($route) }}" class="nav-link-hover" style="font-size: 0.875rem; color: #fff; text-decoration: none; transition: color 200ms;">{{ $label }}</a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-4">
                        @auth
                            <x-notification-bell :direct="true" />
                        @endauth
                        <a href="{{ route('profile.show') }}" class="btn-hover-white" style="background: #fff; color: #000; padding: 0.5rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: background 200ms;">
                            Hồ sơ
                        </a>
                    </div>
                </nav>
            </div>

            {{-- Hero Content --}}
            <div class="hero-content">
                <div class="hero-grid">
                    <div>
                        <h1 id="animated-heading" style="font-size: clamp(2.25rem, 6vw, 4.5rem); font-weight: 400; margin-bottom: 1rem; letter-spacing: -0.04em; line-height: 1.1; color: #fff;">
                            {{-- Injected via JS --}}
                        </h1>

                        <div id="subheading-fade" class="fade-in-element">
                            <p style="font-size: clamp(1rem, 1.5vw, 1.125rem); color: #d1d5db; margin-bottom: 1.25rem; max-width: 520px;">
                                Chúng tôi đồng hành cùng cộng đồng để kiến tạo những giá trị y tế bền vững cho tương lai.
                            </p>

                        </div>

                        <div id="buttons-fade" class="fade-in-element" style="display: flex; flex-wrap: wrap; gap: 1rem;">
                            <a href="{{ route('appointments.create') }}" class="btn-hover-white" style="background: #fff; color: #000; padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; text-decoration: none; font-size: 1rem; transition: background 200ms;">
                                Đặt lịch ngay
                            </a>
                            <a href="#news-list" class="liquid-glass btn-liquid-hover" style="border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; text-decoration: none; font-size: 1rem; background: transparent; transition: background 200ms, color 200ms;">
                                Khám phá thêm
                            </a>
                            <a href="{{ route('rehab.index') }}" class="btn-hover-white" style="background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.12); padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; text-decoration: none; font-size: 1rem; transition: background 200ms;">
                                Thư viện phục hồi
                            </a>
                        </div>
                    </div>

                    <div class="hero-tag-col flex items-end justify-start lg:justify-end">
                        <div id="tag-fade" class="fade-in-element">
                            <div class="hero-weather-wrap">
                                <div id="weather-widget" class="weather-widget" aria-live="polite">
                                    <span id="weather-icon" class="weather-icon">--</span>
                                    <div>
                                        <div id="weather-temp" class="weather-temp">Đang tải...</div>
                                        <div id="weather-desc" class="weather-desc">Thời tiết hiện tại</div>
                                    </div>
                                </div>
                            </div>

                            <div class="liquid-glass" style="border: 1px solid rgba(255,255,255,0.2); padding: 0.75rem 1.5rem; border-radius: 0.75rem; display: inline-block;">
                                <span style="font-size: clamp(1.125rem, 2vw, 1.5rem); font-weight: 300; color: #fff; white-space: nowrap;">
                                    Investing. Building. Advisory.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Urgent News Highlight --}}
    @if($urgent)
    <div class="bg-orange-600 text-white py-4 px-6 relative z-30">
        <div class="container mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-2xl"></i>
                <div>
                    <span class="font-bold uppercase tracking-wider text-sm">Thông báo khẩn cấp:</span>
                    <a href="{{ route('news.show', $urgent->news_id) }}" class="ms-2 hover:underline font-medium no-underline text-white">
                        {{ $urgent->title }}
                    </a>
                </div>
            </div>
            <a href="{{ route('news.show', $urgent->news_id) }}" class="hidden md:block border border-white/40 px-4 py-1 rounded text-sm hover:bg-white hover:text-orange-600 transition-colors no-underline text-white">
                Xem ngay
            </a>
        </div>
    </div>
    @endif

    {{-- News Feed Section --}}
    <section id="news-list" class="py-32 news-calm-bg px-[clamp(1.5rem,4vw,4rem)]">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-8">
                <div>
                    <h2 class="text-4xl md:text-5xl font-normal tracking-tight mb-4 text-white">Bản tin Bệnh viện</h2>
                    <p class="text-gray-400 font-light max-w-xl">Cập nhật những thông tin mới nhất về sức khỏe và dịch vụ của chúng tôi.</p>
                </div>
                
                {{-- Category Tabs --}}
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('news.index') }}" class="category-tab px-6 py-2.5 rounded-full text-[10px] font-bold uppercase tracking-widest {{ !$category ? 'active' : '' }}">
                        Tất cả
                    </a>
                    @foreach($categories as $cat)
                    <a href="{{ route('news.index', ['category' => $cat]) }}" 
                       class="category-tab px-6 py-2.5 rounded-full text-[10px] font-bold uppercase tracking-widest {{ $category == $cat ? 'active' : '' }}">
                        {{ $cat }}
                    </a>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                @forelse($news as $item)
                <div class="group news-card flex flex-col liquid-glass rounded-3xl border border-white/5 overflow-hidden hover:border-white/20 transition-all duration-700 hover:-translate-y-2">
                    <a href="{{ route('news.show', $item->news_id) }}" class="news-thumb block aspect-[16/10] overflow-hidden relative">
                        <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 grayscale-[0.2] group-hover:grayscale-0">
                        <div class="news-thumb-overlay absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-60"></div>
                        <div class="absolute top-6 left-6">
                            <span class="news-category-badge px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-white/10 backdrop-blur-xl text-white border border-white/20">
                                {{ $item->category }}
                            </span>
                        </div>
                    </a>
                    <div class="p-8 flex-1 flex flex-col">
                        <div class="news-date text-[11px] font-medium tracking-widest text-gray-500 mb-4 flex items-center gap-2 uppercase">
                            <i class="bi bi-calendar3"></i>
                            {{ $item->published_at->format('d M, Y') }}
                        </div>
                        <h3 class="news-title text-2xl font-normal mb-5 leading-tight tracking-tight text-white group-hover:text-gray-200 transition-colors">
                            <a href="{{ route('news.show', $item->news_id) }}" class="no-underline text-inherit">{{ $item->title }}</a>
                        </h3>
                        <p class="news-excerpt text-gray-400 text-sm leading-relaxed mb-8 line-clamp-2 font-light">
                            {{ $item->excerpt }}
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('news.show', $item->news_id) }}" class="news-read-more text-xs font-semibold uppercase tracking-widest flex items-center gap-3 text-white no-underline hover:gap-5 transition-all">
                                Xem bài viết <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-20 text-center">
                    <p class="text-gray-500">Hiện chưa có bản tin nào.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-20">
                {{ $news->links() }}
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const heading = document.getElementById('animated-heading');
        const subheading = document.getElementById('subheading-fade');
        const buttons = document.getElementById('buttons-fade');
        const tag = document.getElementById('tag-fade');
        
        const text = "Shaping tomorrow\nwith vision and action.";
        const lines = text.split('\n');
        
        let html = '';
        const charDelay = 30;
        const initialDelay = 200;
        
        lines.forEach((line, lineIndex) => {
            const chars = line.split('');
            const lineLength = chars.length;
            
            html += '<span style="display: block;">';
            chars.forEach((char, charIndex) => {
                const displayChar = char === ' ' ? '\u00A0' : char;
                const delay = initialDelay + (lineIndex * lineLength * charDelay) + (charIndex * charDelay);
                html += `<span class="hero-char" style="transition-delay: ${delay}ms">${displayChar}</span>`;
            });
            html += '</span>';
        });
        
        heading.innerHTML = html;
        
        setTimeout(() => {
            document.querySelectorAll('.hero-char').forEach(el => el.classList.add('visible'));
        }, 50);
        
        setTimeout(() => subheading.classList.add('visible'), 800);
        setTimeout(() => buttons.classList.add('visible'), 1200);
        setTimeout(() => tag.classList.add('visible'), 1400);

        const weatherIcon = document.getElementById('weather-icon');
        const weatherTemp = document.getElementById('weather-temp');
        const weatherDesc = document.getElementById('weather-desc');
        const fallbackLocation = { latitude: 10.8231, longitude: 106.6297, label: 'TP.HCM' };

        const weatherLabels = {
            0: ['Nắng đẹp', '☀️'],
            1: ['Ít mây', '🌤️'],
            2: ['Mây rải rác', '⛅'],
            3: ['Nhiều mây', '☁️'],
            45: ['Sương mù', '🌫️'],
            48: ['Sương mù đóng băng', '🌫️'],
            51: ['Mưa phùn nhẹ', '🌦️'],
            53: ['Mưa phùn', '🌦️'],
            55: ['Mưa phùn dày', '🌧️'],
            61: ['Mưa nhẹ', '🌧️'],
            63: ['Mưa vừa', '🌧️'],
            65: ['Mưa lớn', '⛈️'],
            80: ['Mưa rào nhẹ', '🌦️'],
            81: ['Mưa rào', '🌧️'],
            82: ['Mưa rào mạnh', '⛈️'],
            95: ['Dông', '⛈️'],
            96: ['Dông kèm mưa đá', '⛈️'],
            99: ['Dông kèm mưa đá mạnh', '⛈️']
        };

        const renderWeather = (current, label) => {
            const code = current.weather_code;
            const [description, icon] = weatherLabels[code] || ['Thời tiết hiện tại', '🌡️'];
            weatherIcon.textContent = icon;
            weatherTemp.textContent = `${Math.round(current.temperature_2m)}°C`;
            weatherDesc.textContent = `${description} tại ${label}`;
        };

        const loadWeather = async ({ latitude, longitude, label }) => {
            const params = new URLSearchParams({
                latitude,
                longitude,
                current: 'temperature_2m,weather_code',
                timezone: 'auto'
            });
            const response = await fetch(`https://api.open-meteo.com/v1/forecast?${params}`);

            if (!response.ok) {
                throw new Error('Weather request failed');
            }

            const data = await response.json();
            renderWeather(data.current, label);
        };

        const loadFallbackWeather = () => {
            loadWeather(fallbackLocation).catch(() => {
                weatherIcon.textContent = '🌡️';
                weatherTemp.textContent = '--°C';
                weatherDesc.textContent = 'Không tải được thời tiết';
            });
        };

        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => loadWeather({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    label: 'vị trí của bạn'
                }).catch(loadFallbackWeather),
                loadFallbackWeather,
                { timeout: 5000, maximumAge: 600000 }
            );
        } else {
            loadFallbackWeather();
        }
    });
</script>
@endsection
