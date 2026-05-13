@extends('layouts.user')

@section('title', $article->title)

@section('content')
<div class="bg-zinc-950 text-white min-h-screen py-20 px-6">
    <article class="max-w-4xl mx-auto">
        {{-- Breadcrumb --}}
        <nav class="mb-12 flex items-center gap-3 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Trang chủ</a>
            <i class="bi bi-chevron-right text-[10px]"></i>
            <a href="{{ route('news.index') }}" class="hover:text-white transition-colors">Bản tin</a>
            <i class="bi bi-chevron-right text-[10px]"></i>
            <span class="text-white">{{ $article->category }}</span>
        </nav>

        <header class="mb-12">
            <div class="mb-6">
                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-white/10 text-white border border-white/10">
                    {{ $article->category }}
                </span>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-semibold mb-8 leading-tight">
                {{ $article->title }}
            </h1>
            <div class="flex items-center gap-6 text-sm text-gray-400 border-y border-white/5 py-6">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                        <i class="bi bi-person"></i>
                    </div>
                    <span>Đăng bởi <strong>{{ $article->author->full_name ?? 'Admin' }}</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="bi bi-calendar3"></i>
                    <span>{{ $article->published_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </header>

        @if($article->thumbnail)
        <div class="mb-12 rounded-3xl overflow-hidden border border-white/5">
            <img src="{{ $article->thumbnail_url }}" alt="{{ $article->title }}" class="w-full object-cover">
        </div>
        @endif

        <div class="prose prose-invert prose-lg max-w-none prose-headings:font-semibold prose-a:text-blue-400">
            {!! $article->content !!}
        </div>

        <footer class="mt-20 pt-10 border-t border-white/5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">Chia sẻ:</span>
                    <div class="flex gap-2">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors">
                            <i class="bi bi-link-45deg"></i>
                        </a>
                    </div>
                </div>
                <a href="{{ route('news.index') }}" class="text-sm font-medium border border-white/20 px-6 py-2 rounded-full hover:bg-white hover:text-black transition-all">
                    Quay lại bản tin
                </a>
            </div>
        </footer>
    </article>
</div>
@endsection
