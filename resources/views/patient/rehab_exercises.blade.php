@extends('layouts.user')

@section('title', 'Bai tap phuc hoi')

@section('content')
<div class="container py-4">
    <h2 class="mb-1">Bai tap phuc hoi</h2>
    <div class="text-muted mb-4">Chon bai tap phu hop voi tinh trang va giai doan dieu tri.</div>

    <div class="mb-4 d-flex flex-wrap gap-2">
        @foreach($categories as $value => $label)
            <a href="{{ $value ? route('rehab.index', ['category' => $value]) : route('rehab.index') }}"
               class="btn btn-sm {{ $activeCategory === $value ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="row g-3">
        @forelse($exercises as $exercise)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <img src="{{ $exercise->thumbnail_url }}" class="card-img-top" alt="{{ $exercise->title }}" style="height:180px;object-fit:cover;">
                    <div class="card-body d-flex flex-column">
                        <div class="small text-muted mb-2">{{ $exercise->category_label }} - {{ $exercise->phase_label }}</div>
                        <h5 class="card-title">{{ $exercise->title }}</h5>
                        <p class="text-muted small flex-fill">{{ \Illuminate\Support\Str::limit(strip_tags($exercise->content), 110) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">{{ $exercise->duration_minutes ? $exercise->duration_minutes . ' phut' : 'Linh hoat' }}</span>
                            <a href="{{ route('rehab.show', $exercise) }}" class="btn btn-sm btn-primary">Xem</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info">Chua co bai tap cong khai.</div></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $exercises->links() }}</div>
</div>
@endsection
