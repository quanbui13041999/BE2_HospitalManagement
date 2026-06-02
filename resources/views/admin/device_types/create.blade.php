@extends('layouts.admin')

@section('title', 'Thêm danh mục thiết bị')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-plus-circle me-2 text-primary"></i>Thêm danh mục thiết bị</h4>
            <a href="{{ route('admin.device-types.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 760px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.device-types.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" name="name" maxlength="120" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea name="description" maxlength="1000" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="text-end">
                    <a href="{{ route('admin.device-types.index') }}" class="btn btn-light me-2">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
