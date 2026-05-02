{{-- resources/views/admin/services/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Sửa Dịch vụ: ' . $service->service_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.services.show', $service) }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0">Sửa Dịch vụ: <span class="text-primary">{{ $service->service_name }}</span></h4>
    </div>

    <form method="POST" action="{{ route('admin.services.update', $service) }}">
        @csrf @method('PUT')
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-pencil me-2"></i>Thông tin dịch vụ</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Mã dịch vụ <span class="text-danger">*</span></label>
                        <input type="text" name="service_code"
                               class="form-control @error('service_code') is-invalid @enderror"
                               value="{{ old('service_code', $service->service_code) }}" required>
                        @error('service_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                        <input type="text" name="service_name"
                               class="form-control @error('service_name') is-invalid @enderror"
                               value="{{ old('service_name', $service->service_name) }}" required>
                        @error('service_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Khoa phụ trách</label>
                        <select name="department_id" class="form-select">
                            <option value="">-- Chọn khoa --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}"
                                    {{ old('department_id', $service->department_id) == $dept->department_id ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thời gian thực hiện (phút) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_minutes" class="form-control"
                               value="{{ old('duration_minutes', $service->duration_minutes) }}"
                               min="5" max="480" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select" required>
                            <option value="1" {{ old('status', $service->status ? '1' : '0') == '1' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="0" {{ old('status', $service->status ? '1' : '0') == '0' ? 'selected' : '' }}>Vô hiệu</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $service->description) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Lưu thay đổi
                </button>
                <a href="{{ route('admin.services.show', $service) }}" class="btn btn-outline-secondary">Huỷ</a>
            </div>
        </div>
    </form>
</div>
@endsection
