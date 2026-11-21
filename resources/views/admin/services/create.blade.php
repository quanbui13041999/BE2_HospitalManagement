{{-- resources/views/admin/services/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Thêm Dịch vụ mới')

@push('styles')
<style>
.char-count { font-size:11px; color:#90A4AE; text-align:right; }
.char-count.warn { color:#f57c00; }
.char-count.over { color:#c62828; }
.field-hint { font-size:12px; color:#90A4AE; margin-top:3px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0">Thêm Dịch vụ mới</h4>
    </div>

    {{-- Server-side errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Vui lòng kiểm tra lại thông tin:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.services.store') }}" id="serviceForm" novalidate>
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
                            <input type="text" name="service_code" id="service_code"
                                   class="form-control @error('service_code') is-invalid @enderror"
                                   value="{{ old('service_code') }}" placeholder="VD: DV006"
                                   maxlength="30" pattern="[A-Za-z0-9\-.]+" required>
                            @error('service_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="field-hint">Chỉ dùng chữ cái, số, gạch ngang, dấu chấm</div>
                            @enderror
                            <div class="char-count" id="service_code_count">0 / 30</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                            <input type="text" name="service_name" id="service_name"
                                   class="form-control @error('service_name') is-invalid @enderror"
                                   value="{{ old('service_name') }}" maxlength="150" required>
                            @error('service_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="char-count" id="service_name_count">0 / 150</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Khoa phụ trách</label>
                                <select name="department_id" id="department_id"
                                        class="form-select @error('department_id') is-invalid @enderror">
                                    <option value="">-- Chọn khoa --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->department_id }}"
                                            {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>
                                            {{ $dept->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Thời gian thực hiện (phút) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_minutes" id="duration_minutes"
                                       class="form-control @error('duration_minutes') is-invalid @enderror"
                                       value="{{ old('duration_minutes', 30) }}" min="5" max="480" required>
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="field-hint">5 – 480 phút</div>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" id="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3" maxlength="500">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="char-count" id="description_count">0 / 500</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select name="status" id="status"
                                    class="form-select @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Vô hiệu</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                        <input type="number" name="prices[${priceIndex}][price]" class="form-control"
                               placeholder="Đơn giá" min="0" step="1000" required>
                        <span class="input-group-text">đ</span>
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label form-label-sm mb-1">Hiệu lực từ</label>
                    <input type="date" name="prices[${priceIndex}][effective_date]" class="form-control form-control-sm"
                           value="{{ now()->toDateString() }}" required>
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
            btn.onclick = function () { this.closest('.price-row').remove(); };
        });
    }
    bindRemove();

    // ── Char counters ─────────────────────────────────────────────────
    function bindCharCount(id, counterId, max) {
        const el = document.getElementById(id);
        const counter = document.getElementById(counterId);
        if (!el || !counter) return;
        const update = () => {
            const len = el.value.length;
            counter.textContent = len + ' / ' + max;
            counter.className = 'char-count' + (len >= max ? ' over' : len >= max * 0.8 ? ' warn' : '');
        };
        el.addEventListener('input', update);
        update();
    }
    bindCharCount('service_code',  'service_code_count',  30);
    bindCharCount('service_name',  'service_name_count',  150);
    bindCharCount('description',   'description_count',   500);

    // ── Client-side validation ────────────────────────────────────────
    document.getElementById('serviceForm').addEventListener('submit', function(e) {
        let valid = true;
        const code = document.getElementById('service_code');
        const name = document.getElementById('service_name');
        const dur  = document.getElementById('duration_minutes');
        [code, name, dur].forEach(el => el.classList.remove('is-invalid'));

        if (!code.value.trim()) {
            showErr(code, 'Mã dịch vụ là bắt buộc.'); valid = false;
        } else if (!/^[A-Za-z0-9\-.]+$/.test(code.value.trim())) {
            showErr(code, 'Mã dịch vụ chỉ chứa chữ cái, số, gạch ngang, dấu chấm.'); valid = false;
        }
        if (!name.value.trim()) {
            showErr(name, 'Tên dịch vụ là bắt buộc.'); valid = false;
        }
        const durVal = parseInt(dur.value);
        if (isNaN(durVal) || durVal < 5 || durVal > 480) {
            showErr(dur, 'Thời gian phải từ 5 đến 480 phút.'); valid = false;
        }
        if (!valid) e.preventDefault();
    });

    function showErr(el, msg) {
        el.classList.add('is-invalid');
        let fb = el.nextElementSibling;
        if (!fb || !fb.classList.contains('invalid-feedback')) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            el.parentNode.insertBefore(fb, el.nextSibling);
        }
        fb.textContent = msg;
    }
</script>
@endpush
@endsection
