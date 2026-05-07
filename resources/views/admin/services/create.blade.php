{{-- resources/views/admin/services/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Thêm Dịch vụ mới')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0">Thêm Dịch vụ mới</h4>
    </div>

    <form method="POST" action="{{ route('admin.services.store') }}" id="serviceForm">
        @csrf
        <div class="row g-4">
            {{-- Thông tin dịch vụ --}}
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">
                        <i class="bi bi-info-circle me-2"></i>Thông tin dịch vụ
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Mã dịch vụ <span class="text-danger">*</span></label>
                            <input type="text" name="service_code" class="form-control @error('service_code') is-invalid @enderror"
                                   value="{{ old('service_code') }}" placeholder="VD: DV006" required>
                            @error('service_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                            <input type="text" name="service_name" class="form-control @error('service_name') is-invalid @enderror"
                                   value="{{ old('service_name') }}" required>
                            @error('service_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Khoa phụ trách</label>
                                <select name="department_id" class="form-select">
                                    <option value="">-- Chọn khoa --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->department_id }}"
                                            {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>
                                            {{ $dept->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Thời gian thực hiện (phút) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_minutes" class="form-control"
                                       value="{{ old('duration_minutes', 30) }}" min="5" max="480" required>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3"
                                      maxlength="500">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Vô hiệu</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bảng giá ban đầu --}}
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                        <span><i class="bi bi-tag me-2"></i>Bảng giá ban đầu</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addPriceRow">
                            <i class="bi bi-plus-lg"></i> Thêm giá
                        </button>
                    </div>
                    <div class="card-body" id="pricesContainer">
                        {{-- Hàng giá mẫu --}}
                        <div class="price-row border rounded p-3 mb-3" data-index="0">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold small text-muted">Mức giá #1</span>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-price">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <select name="prices[0][price_type]" class="form-select form-select-sm">
                                        @foreach($priceTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="prices[0][price]" class="form-control"
                                               placeholder="Đơn giá" min="0" step="1000">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label form-label-sm mb-1">Hiệu lực từ</label>
                                    <input type="date" name="prices[0][effective_date]" class="form-control form-control-sm"
                                           value="{{ now()->toDateString() }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label form-label-sm mb-1">Đến ngày</label>
                                    <input type="date" name="prices[0][end_date]" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small text-center" id="emptyPriceHint" style="display:none">
                            Nhấn "Thêm giá" để khai báo bảng giá.
                        </p>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-floppy me-1"></i>Lưu dịch vụ
                    </button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">Huỷ</a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let priceIndex = 1;
    const priceTypes = @json($priceTypes);

    document.getElementById('addPriceRow').addEventListener('click', () => {
        const container = document.getElementById('pricesContainer');
        const tpl = `
        <div class="price-row border rounded p-3 mb-3" data-index="${priceIndex}">
            <div class="d-flex justify-content-between mb-2">
                <span class="fw-semibold small text-muted">Mức giá #${priceIndex + 1}</span>
                <button type="button" class="btn btn-sm btn-outline-danger remove-price"><i class="bi bi-trash"></i></button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <select name="prices[${priceIndex}][price_type]" class="form-select form-select-sm">
                        ${priceTypes.map(t => `<option value="${t}">${t}</option>`).join('')}
                    </select>
                </div>
                <div class="col-12">
                    <div class="input-group input-group-sm">
                        <input type="number" name="prices[${priceIndex}][price]" class="form-control" placeholder="Đơn giá" min="0" step="1000">
                        <span class="input-group-text">đ</span>
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label form-label-sm mb-1">Hiệu lực từ</label>
                    <input type="date" name="prices[${priceIndex}][effective_date]" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                </div>
                <div class="col-6">
                    <label class="form-label form-label-sm mb-1">Đến ngày</label>
                    <input type="date" name="prices[${priceIndex}][end_date]" class="form-control form-control-sm">
                </div>
            </div>
        </div>`;

        const hint = document.getElementById('emptyPriceHint');
        hint.insertAdjacentHTML('beforebegin', tpl);
        priceIndex++;
        bindRemove();
    });

    function bindRemove() {
        document.querySelectorAll('.remove-price').forEach(btn => {
            btn.onclick = function () {
                this.closest('.price-row').remove();
            };
        });
    }
    bindRemove();
</script>
@endpush
@endsection
