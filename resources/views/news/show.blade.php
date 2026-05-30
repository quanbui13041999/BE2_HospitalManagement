@extends('layouts.user')

@section('title', $article->title)

@section('content')
<style>
    .news-detail-shell {
        background: #121417;
        color: #fff;
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
        padding: 96px 16px 32px;
    }

    .news-detail-topnav {
        position: fixed;
        top: 1rem;
        left: clamp(1.5rem, 4vw, 4rem);
        right: clamp(1.5rem, 4vw, 4rem);
        z-index: 999;
        border-radius: 0.75rem;
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        background: rgba(0, 0, 0, 0.62);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.1);
    }

    .news-detail-brand {
        color: #fff;
        text-decoration: none;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: -0.04em;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .news-detail-navlinks {
        display: flex;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .news-detail-navlinks a {
        color: #fff;
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.2s;
    }

    .news-detail-navlinks a:hover,
    .news-detail-navlinks a.active {
        color: #d1d5db;
    }

    .news-detail-profile {
        background: #fff;
        color: #000;
        padding: 0.5rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .news-detail-profile:hover {
        background: #f3f4f6;
        color: #000;
    }

    .news-detail-page {
        max-width: 920px;
        margin: 0 auto;
        background: #121417;
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        overflow: hidden;
    }

    .news-detail-hero {
        padding: 3rem 2.5rem 2.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .news-detail-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        font-size: 11px;
        color: #444;
        margin-bottom: 1.75rem;
    }

    .news-detail-breadcrumb a {
        color: #555;
        text-decoration: none;
    }

    .news-detail-breadcrumb a:hover {
        color: #aaa;
    }

    .news-detail-breadcrumb span {
        color: #333;
    }

    .news-detail-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #888;
        border: 1px solid rgba(255, 255, 255, 0.12);
        padding: 4px 10px;
        border-radius: 2px;
        margin-bottom: 1.25rem;
    }

    .news-detail-badge::before {
        content: '';
        width: 5px;
        height: 5px;
        background: #fff;
        border-radius: 50%;
    }

    .news-detail-title {
        font-size: clamp(28px, 4vw, 46px);
        font-weight: 600;
        line-height: 1.15;
        letter-spacing: -0.03em;
        color: #fff;
        margin-bottom: 2rem;
    }

    .news-detail-meta {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }

    .news-detail-author,
    .news-detail-meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .news-detail-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: #888;
    }

    .news-detail-meta-info {
        display: flex;
        flex-direction: column;
    }

    .news-detail-meta-label {
        font-size: 9px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #3a3a3a;
    }

    .news-detail-meta-value {
        font-size: 12px;
        font-weight: 500;
        color: #ccc;
    }

    .news-detail-divider {
        width: 1px;
        height: 28px;
        background: rgba(255, 255, 255, 0.07);
    }

    .news-detail-thumb-wrap {
        padding: 2rem 2.5rem 0;
    }

    .news-detail-thumb {
        width: 100%;
        max-height: 360px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.06);
        display: block;
        background: #1a1a1a;
    }

    .news-detail-thumb-placeholder {
        width: 100%;
        height: 220px;
        background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.06);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .news-detail-thumb-placeholder span,
    .news-detail-thumb-caption {
        font-size: 9px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #333;
    }

    .news-detail-thumb-caption {
        text-align: right;
        margin-top: 8px;
    }

    .news-detail-body {
        padding: 2.5rem 2.5rem 2rem;
    }

    .news-detail-lead-bar {
        width: 40px;
        height: 2px;
        background: #fff;
        border-radius: 2px;
        margin-bottom: 1.5rem;
    }

    .news-detail-content {
        color: #909090;
    }

    .news-detail-content p {
        font-size: 13px;
        line-height: 1.85;
        color: #909090;
        margin-bottom: 1rem;
    }

    .news-detail-content p:first-child {
        color: #c0c0c0;
    }

    .news-detail-content h1,
    .news-detail-content h2,
    .news-detail-content h3,
    .news-detail-content h4 {
        color: #f5f5f5;
        margin: 1.5rem 0 0.75rem;
    }

    .news-detail-content a {
        color: #c0c0c0;
    }

    .news-detail-content ul,
    .news-detail-content ol {
        padding-left: 1.2rem;
        margin-bottom: 1rem;
    }

    .news-detail-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.06);
        padding: 1.75rem 2.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .news-detail-share-label {
        font-size: 9px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #333;
        margin-bottom: 10px;
    }

    .news-detail-share-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .news-detail-share,
    .news-detail-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 2px;
        border: 1px solid rgba(255, 255, 255, 0.09);
        background: transparent;
        text-decoration: none;
        transition: color 0.2s, border-color 0.2s;
    }

    .news-detail-share {
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 500;
        color: #777;
    }

    .news-detail-back {
        padding: 7px 14px;
        font-size: 10px;
        font-weight: 500;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #444;
    }

    .news-detail-share:hover,
    .news-detail-back:hover {
        color: #ccc;
        border-color: rgba(255, 255, 255, 0.2);
    }

    @media (max-width: 640px) {
        .news-detail-shell {
            padding: 112px 0 16px;
        }

        .news-detail-page {
            border-radius: 0;
            border-left: 0;
            border-right: 0;
        }

        .news-detail-hero,
        .news-detail-thumb-wrap,
        .news-detail-body,
        .news-detail-footer {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        .news-detail-divider {
            display: none;
        }

        .news-detail-topnav {
            left: 1rem;
            right: 1rem;
            align-items: flex-start;
            flex-direction: column;
        }

        .news-detail-navlinks {
            gap: 1rem;
        }
    }

    body {
        background: #121417;
    }

    body > nav.navbar {
        display: none !important;
    }

    footer.mt-5 {
        margin-top: 0 !important;
    }
</style>

<div class="news-detail-shell">
    <nav class="news-detail-topnav">
        <a href="{{ route('home') }}" class="news-detail-brand">
            <span>HOPITAL</span>
        </a>

        <div class="news-detail-navlinks">
            <a href="{{ route('home') }}">Trang chủ</a>
            <a href="{{ route('user.services.index') }}">Dịch vụ</a>
            <a href="{{ route('news.index') }}" class="active">Bản tin</a>
            <a href="{{ route('appointments.create') }}">Đặt lịch</a>
        </div>

        <div style="display: flex; align-items: center; gap: 1rem;">
            @auth
                <x-notification-bell :direct="true" />
                <a href="{{ route('profile.show') }}" class="news-detail-profile">Hồ sơ</a>
            @else
                <a href="{{ route('login') }}" class="news-detail-profile">Đăng nhập</a>
            @endauth
        </div>
    </nav>

    <div class="news-detail-page">
        <div class="news-detail-hero">
            <nav class="news-detail-breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('news.index') }}">Bản tin</a>
                <span>/</span>
                <span style="color:#666">{{ $article->category }}</span>
            </nav>

            <div class="news-detail-badge">{{ $article->category }}</div>

            <h1 class="news-detail-title">{{ $article->title }}</h1>

            <div class="news-detail-meta">
                <div class="news-detail-author">
                    <div class="news-detail-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="news-detail-meta-info">
                        <span class="news-detail-meta-label">Tác giả</span>
                        <span class="news-detail-meta-value">{{ $article->author->full_name ?? 'Admin' }}</span>
                    </div>
                </div>

                <div class="news-detail-divider"></div>

                <div class="news-detail-meta-item">
                    <i class="bi bi-calendar3" style="font-size:12px;color:#666"></i>
                    <div class="news-detail-meta-info">
                        <span class="news-detail-meta-label">Ngày đăng</span>
                        <span class="news-detail-meta-value">{{ $article->published_at->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="news-detail-divider"></div>

                <div class="news-detail-meta-item">
                    <i class="bi bi-clock" style="font-size:12px;color:#666"></i>
                    <div class="news-detail-meta-info">
                        <span class="news-detail-meta-label">Giờ đăng</span>
                        <span class="news-detail-meta-value">{{ $article->published_at->format('H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="news-detail-thumb-wrap">
            @if($article->thumbnail)
                <img src="{{ $article->thumbnail_url }}" alt="{{ $article->title }}" class="news-detail-thumb">
            @else
                <div class="news-detail-thumb-placeholder">
                    <span>Hình ảnh bài viết</span>
                </div>
            @endif
            <div class="news-detail-thumb-caption">{{ $article->category }} &mdash; {{ $article->published_at->format('d/m/Y') }}</div>
        </div>

        <div class="news-detail-body">
            <div class="news-detail-lead-bar"></div>
            <div class="news-detail-content">
                {!! $article->content !!}
            </div>
        </div>

        <div class="news-detail-footer">
            <div>
                <div class="news-detail-share-label">Chia sẻ bài viết</div>
                <div class="news-detail-share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" rel="noopener" class="news-detail-share">
                        <i class="bi bi-facebook"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($article->title) }}" target="_blank" rel="noopener" class="news-detail-share">
                        <i class="bi bi-twitter-x"></i> Twitter
                    </a>
                    <button type="button" class="news-detail-share" onclick="navigator.clipboard && navigator.clipboard.writeText(window.location.href)">
                        <i class="bi bi-link-45deg"></i> Sao chép link
                    </button>
                </div>
            </div>

            <a href="{{ route('news.index') }}" class="news-detail-back">
                <i class="bi bi-arrow-left"></i> Quay lại bản tin
            </a>
        </div>
    </div>
</div>
@endsection
