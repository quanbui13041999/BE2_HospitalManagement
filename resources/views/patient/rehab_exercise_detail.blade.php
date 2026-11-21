@extends('layouts.user')

@section('title', $exercise->title)

@section('content')
<div class="container py-4">
    <a href="{{ route('rehab.index') }}" class="btn btn-outline-secondary btn-sm mb-3">Quay lai danh sach</a>

    <article class="card mb-4">
        <img src="{{ $exercise->thumbnail_url }}" class="card-img-top" alt="{{ $exercise->title }}" style="max-height:360px;object-fit:cover;">
        <div class="card-body">
            <div class="text-muted small mb-2">
                {{ $exercise->category_label }} - {{ $exercise->phase_label }} - {{ $exercise->view_count }} luot xem
            </div>
            <h2>{{ $exercise->title }}</h2>
            @if($exercise->duration_minutes)
                <div class="badge bg-primary mb-3">{{ $exercise->duration_minutes }} phut</div>
            @endif
            <div class="lh-lg">{!! nl2br(e($exercise->content)) !!}</div>
        </div>
    </article>

    @if($related->isNotEmpty())
        <h5 class="mb-3">Bai tap lien quan</h5>
        <div class="row g-3">
            @foreach($related as $item)
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="small text-muted mb-2">{{ $item->phase_label }}</div>
                            <h6>{{ $item->title }}</h6>
                            <a href="{{ route('rehab.show', $item) }}" class="btn btn-sm btn-outline-primary mt-2">Xem</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
