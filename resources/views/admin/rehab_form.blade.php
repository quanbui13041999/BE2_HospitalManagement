@extends('layouts.admin')

@section('title', $isEdit ? 'Sua bai tap phuc hoi' : 'Tao bai tap phuc hoi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $isEdit ? 'Sua bai tap' : 'Tao bai tap' }}</h4>
    <a href="{{ route('admin.rehab.index') }}" class="btn btn-outline-secondary">Quay lai</a>
</div>

<form method="POST" action="{{ $isEdit ? route('admin.rehab.update', $exercise) : route('admin.rehab.store') }}" enctype="multipart/form-data" class="card p-4">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Tieu de</label>
        <input name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $exercise->title) }}">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Nhom benh ly</label>
            <select name="category" class="form-select @error('category') is-invalid @enderror">
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" @selected(old('category', $exercise->category) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Giai doan</label>
            <select name="phase" class="form-select @error('phase') is-invalid @enderror">
                @foreach($phases as $value => $label)
                    <option value="{{ $value }}" @selected(old('phase', $exercise->phase) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('phase')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Trang thai</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="draft" @selected(old('status', $exercise->status) === 'draft')>Ban nhap</option>
                <option value="published" @selected(old('status', $exercise->status) === 'published')>Cong khai</option>
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Thoi luong tap (phut)</label>
            <input type="number" name="duration_minutes" min="1" max="240" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes', $exercise->duration_minutes) }}">
            @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Anh dai dien</label>
            <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*">
            @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Noi dung huong dan</label>
        <textarea name="content" rows="10" class="form-control @error('content') is-invalid @enderror">{{ old('content', $exercise->content) }}</textarea>
        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.rehab.index') }}" class="btn btn-outline-secondary">Huy</a>
        <button class="btn btn-primary">{{ $isEdit ? 'Cap nhat' : 'Luu bai tap' }}</button>
    </div>
</form>
@endsection
