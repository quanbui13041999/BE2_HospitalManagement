@extends('layouts.admin')

@section('title', 'Sửa danh mục thiết bị')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>Sửa danh mục thiết bị</h4>
            <a href="{{ route('admin.device-types.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
            </a>
        </div>
        <small class="text-muted">Phiên bản #{{ $type->lock_version }}</small>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 760px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.device-types.update', $type->id) }}" method="POST" data-device-form novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="lock_version" value="{{ $type->lock_version }}">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           minlength="2"
                           maxlength="120"
                           pattern="^[A-Za-zÀ-ỹ]+( [A-Za-zÀ-ỹ]+)*$"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $type->name) }}"
                           required
                           data-error-required="Vui lòng nhập tên danh mục thiết bị."
                           data-error-pattern="Tên danh mục chỉ được nhập chữ và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ."
                           data-error-minlength="Tên danh mục phải có ít nhất 2 ký tự.">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea name="description"
                              maxlength="1000"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="4"
                              data-pattern="^[A-Za-zÀ-ỹ]+( [A-Za-zÀ-ỹ]+)*$"
                              data-error-pattern="Mô tả chỉ được nhập chữ và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ.">{{ old('description', $type->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="text-end">
                    <a href="{{ route('admin.device-types.index') }}" class="btn btn-light me-2">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@include('admin.devices._form_validation')
