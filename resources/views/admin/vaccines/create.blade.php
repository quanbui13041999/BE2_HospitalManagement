@extends('layouts.admin')

@section('title', 'Thêm Vắc xin mới')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-plus-circle me-2 text-primary"></i>Thêm Vắc xin mới</h4>
            <a href="{{ route('admin.vaccines.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 800px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.vaccines.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Tên vắc xin <span class="text-danger">*</span></label>
                        <input type="text" name="vaccine_name" class="form-control @error('vaccine_name') is-invalid @enderror" value="{{ old('vaccine_name') }}" required>
                        @error('vaccine_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nhà sản xuất</label>
                        <input type="text" name="manufacturer" class="form-control @error('manufacturer') is-invalid @enderror" value="{{ old('manufacturer') }}">
                        @error('manufacturer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số mũi yêu cầu <span class="text-danger">*</span></label>
                        <input type="number" name="doses_required" class="form-control @error('doses_required') is-invalid @enderror" value="{{ old('doses_required', 1) }}" min="1" required>
                        @error('doses_required') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Sẵn sàng</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Tạm ngưng</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.vaccines.index') }}" class="btn btn-light me-2">Hủy</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu thông tin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
