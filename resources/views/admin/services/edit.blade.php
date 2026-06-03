{{-- resources/views/admin/services/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Sửa Dịch vụ: ' . $service->service_code)

@push('styles')
<style>
.form-card { max-width: 720px; }
.field-hint { font-size: 12px; color: #90A4AE; margin-top: 3px; }
.char-count { font-size: 11px; color: #90A4AE; text-align: right; }
.char-count.warn { color: #f57c00; }
.char-count.over  { color: #c62828; }
.locked-field {
    background: #f8fafc;
    border: 1.5px dashed #b0bec5;
    color: #546e7a;
    font-weight: 600;
    cursor: not-allowed;
}
.locked-badge {
    font-size: 11px;
    background: #e3f2fd;
    color: #0D47A1;
    border-radius: 6px;
    padding: 2px 8px;
    font-weight: 600;
    vertical-align: middle;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.services.show', $service) }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">
                Sửa Dịch vụ: <span class="text-primary">{{ $service->service_code }}</span>
            </h4>
            <p class="text-muted small mb-0">Cập nhật thông tin dịch vụ y tế</p>
        </div>
    </div>

    {{-- Server-side errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3 form-card">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Vui lòng kiểm tra lại thông tin:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.services.update', $service) }}"
          id="editServiceFormPage" novalidate>
        @csrf @method('PUT')
        {{-- Optimistic lock token: phát hiện xung đột cập nhật 2 tab --}}
        <input type="hidden" name="_lock_version" value="{{ $service->updated_at?->timestamp }}">
        <div class="card shadow-sm form-card">
            <div class="card-header fw-semibold">
                <i class="bi bi-pencil me-2 text-primary"></i>Thông tin dịch vụ
            </div>
            <div class="card-body row g-3">

                {{-- Mã dịch vụ – READONLY --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="service_code_display">
                        Mã dịch vụ
                        <span class="locked-badge ms-1"><i class="bi bi-lock-fill"></i> Không thể sửa</span>
                    </label>
                    <input type="text"
                           id="service_code_display"
                           class="form-control locked-field"
                           value="{{ $service->service_code }}"
                           readonly tabindex="-1">
                    <div class="field-hint">
                        <i class="bi bi-info-circle me-1"></i>Mã dịch vụ là khóa định danh, không thể thay đổi.
                    </div>
                </div>

                {{-- Tên dịch vụ --}}
                <div class="col-md-8">
                    <label class="form-label fw-semibold" for="service_name">
                        Tên dịch vụ <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="service_name"
                           name="service_name"
                           class="form-control @error('service_name') is-invalid @enderror"
                           value="{{ old('service_name', $service->service_name) }}"
                           maxlength="150"
                           placeholder="VD: Siêu âm ổ bụng tổng quát"
                           required>
                    @error('service_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="char-count" id="service_name_count">0 / 150</div>
                </div>

                {{-- Khoa phụ trách --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="department_id">Khoa phụ trách</label>
                    <select name="department_id" id="department_id"
                            class="form-select @error('department_id') is-invalid @enderror">
                        <option value="">-- Không thuộc khoa --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}"
                                {{ old('department_id', $service->department_id) == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Thời gian thực hiện --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold" for="duration_minutes">
                        Thời gian (phút) <span class="text-danger">*</span>
                    </label>
                    <input type="number"
                           id="duration_minutes"
                           name="duration_minutes"
                           class="form-control @error('duration_minutes') is-invalid @enderror"
                           value="{{ old('duration_minutes', $service->duration_minutes) }}"
                           min="5" max="480" required>
                    @error('duration_minutes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="field-hint">5 – 480 phút</div>
                </div>

                {{-- Trạng thái --}}
                <div class="col-md-3">
                    <label class="form-label fw-semibold" for="status">
                        Trạng thái <span class="text-danger">*</span>
                    </label>
                    <select name="status" id="status"
                            class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" {{ old('status', $service->status ? '1' : '0') == '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ old('status', $service->status ? '1' : '0') == '0' ? 'selected' : '' }}>Vô hiệu</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Mô tả --}}
                <div class="col-12">
                    <label class="form-label fw-semibold" for="description">Mô tả</label>
                    <textarea name="description" id="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="3"
                              maxlength="500"
                              placeholder="Mô tả ngắn về dịch vụ...">{{ old('description', $service->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="char-count" id="description_count">0 / 500</div>
                </div>

            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="svcSubmitBtn">
                    <i class="bi bi-floppy me-1"></i>Lưu thay đổi
                </button>
                <a href="{{ route('admin.services.show', $service) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Huỷ
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ── Char counter ────────────────────────────────────────────────────
function bindCharCount(inputId, counterId, max) {
    const el = document.getElementById(inputId);
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
bindCharCount('service_name', 'service_name_count', 150);
bindCharCount('description',  'description_count',  500);

// ── Client-side validation ──────────────────────────────────────────
document.getElementById('editServiceFormPage').addEventListener('submit', function(e) {
    let valid = true;
    const name     = document.getElementById('service_name');
    const duration = document.getElementById('duration_minutes');
    const status   = document.getElementById('status');

    [name, duration, status].forEach(el => el.classList.remove('is-invalid'));

    if (!name.value.trim()) {
        showError(name, 'Tên dịch vụ là bắt buộc.'); valid = false;
    }
    const dur = parseInt(duration.value);
    if (isNaN(dur) || dur < 5 || dur > 480) {
        showError(duration, 'Thời gian phải từ 5 đến 480 phút.'); valid = false;
    }
    if (status.value === '') {
        showError(status, 'Vui lòng chọn trạng thái.'); valid = false;
    }

    if (!valid) {
        e.preventDefault();
        return;
    }

    // Disable nút sau khi form hợp lệ
    const btn = document.getElementById('svcSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang lưu...';
});

function showError(el, msg) {
    el.classList.add('is-invalid');
    let fb = el.nextElementSibling;
    if (!fb || !fb.classList.contains('invalid-feedback')) {
        fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        el.parentNode.insertBefore(fb, el.nextSibling);
    }
    fb.textContent = msg;
}

// Realtime check database changes
function startRealtimeCheck(type, id, lockVersion) {
    const interval = setInterval(async () => {
        try {
            const response = await fetch(`/admin/api/check-entity-status?type=${type}&id=${id}&lock_version=${lockVersion}`);
            const data = await response.json();
            if (data.success && data.status !== 'unchanged') {
                clearInterval(interval);
                const overlay = document.createElement('div');
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100vw';
                overlay.style.height = '100vh';
                overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
                overlay.style.zIndex = '99999';
                overlay.style.display = 'flex';
                overlay.style.justifyContent = 'center';
                overlay.style.alignItems = 'center';
                overlay.style.backdropFilter = 'blur(5px)';
                overlay.innerHTML = `
                    <div class="card shadow-lg border-0 text-center p-4 m-3" style="max-width: 500px; border-radius: 16px;">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="card-title text-danger fw-bold mb-3">Cảnh báo đồng bộ dữ liệu</h4>
                            <p class="card-text text-secondary mb-4" style="font-size: 1.1rem;">${data.message}</p>
                            <button onclick="window.location.reload();" class="btn btn-primary btn-lg px-4" style="border-radius: 8px;">
                                <i class="bi bi-arrow-clockwise me-1"></i> Tải lại trang
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(overlay);
                document.querySelectorAll('form input, form select, form textarea, form button').forEach(el => {
                    el.disabled = true;
                });
            }
        } catch (error) {
            console.error('Lỗi khi kiểm tra trạng thái thực thể:', error);
        }
    }, 5000);
}

startRealtimeCheck('service', '{{ $service->service_id }}', '{{ $service->updated_at?->timestamp }}');
</script>
@endpush
