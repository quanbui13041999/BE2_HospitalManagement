{{-- resources/views/admin/rooms/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Thêm Phòng khám mới')

@push('styles')
<style>
.form-card { max-width: 700px; }
.field-hint { font-size: 12px; color: #90A4AE; margin-top: 3px; }
.char-count { font-size: 11px; color: #90A4AE; text-align: right; }
.char-count.warn { color: #f57c00; }
.char-count.over { color: #c62828; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">Thêm Phòng khám mới</h4>
            <p class="text-muted small mb-0">Điền đầy đủ thông tin để tạo phòng khám mới</p>
        </div>
    </div>

    {{-- Server-side errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" style="max-width:700px">
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

    <div class="card shadow-sm form-card">
        <div class="card-header fw-semibold">
            <i class="bi bi-door-open me-2 text-primary"></i>Thông tin phòng khám
        </div>
        <form method="POST" action="{{ route('admin.rooms.store') }}" id="createRoomForm" novalidate>
            @csrf
            <div class="card-body row g-3">

                {{-- Mã phòng --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="room_code">
                        Mã phòng <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="room_code"
                           name="room_code"
                           class="form-control @error('room_code') is-invalid @enderror"
                           value="{{ old('room_code') }}"
                           placeholder="VD: P501"
                           maxlength="20"
                           pattern="[A-Za-z0-9\-\.]+"
                           autocomplete="off"
                           required>
                    @error('room_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="field-hint">Chỉ dùng chữ cái, số, dấu gạch ngang (-) hoặc chấm (.)</div>
                    @enderror
                    <div class="char-count" id="room_code_count">0 / 20</div>
                </div>

                {{-- Tên phòng --}}
                <div class="col-md-8">
                    <label class="form-label fw-semibold" for="room_name">Tên phòng</label>
                    <input type="text"
                           id="room_name"
                           name="room_name"
                           class="form-control @error('room_name') is-invalid @enderror"
                           value="{{ old('room_name') }}"
                           placeholder="VD: Phòng khám Tim mạch"
                           maxlength="100">
                    @error('room_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="char-count" id="room_name_count">0 / 100</div>
                </div>

                {{-- Khoa --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="department_id">Khoa phụ trách</label>
                    <select name="department_id" id="department_id"
                            class="form-select @error('department_id') is-invalid @enderror">
                        <option value="">-- Chọn khoa (không bắt buộc) --</option>
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

                {{-- Loại phòng --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="room_type">
                        Loại phòng <span class="text-danger">*</span>
                    </label>
                    <select name="room_type" id="room_type"
                            class="form-select @error('room_type') is-invalid @enderror" required>
                        <option value="">-- Chọn loại phòng --</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type }}" {{ old('room_type') === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Trạng thái --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="status">
                        Trạng thái <span class="text-danger">*</span>
                    </label>
                    <select name="status" id="status"
                            class="form-select @error('status') is-invalid @enderror" required>
                        @foreach($roomStatuses as $st)
                            <option value="{{ $st }}" {{ old('status', 'Trống') === $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Ghi chú --}}
                <div class="col-12">
                    <label class="form-label fw-semibold" for="notes">Ghi chú</label>
                    <textarea name="notes" id="notes"
                              class="form-control @error('notes') is-invalid @enderror"
                              rows="3"
                              maxlength="500"
                              placeholder="Mô tả thêm về phòng khám...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="char-count" id="notes_count">0 / 500</div>
                </div>

            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bi bi-floppy me-1"></i>Tạo phòng
                </button>
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Huỷ
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Char counter ─────────────────────────────────────────────
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
bindCharCount('room_code', 'room_code_count', 20);
bindCharCount('room_name', 'room_name_count', 100);
bindCharCount('notes',     'notes_count',     500);

// ── Client-side validation ────────────────────────────────────
document.getElementById('createRoomForm').addEventListener('submit', function(e) {
    let valid = true;
    const code = document.getElementById('room_code');
    const type = document.getElementById('room_type');
    const status = document.getElementById('status');

    // Reset
    [code, type, status].forEach(el => el.classList.remove('is-invalid'));

    const codePattern = /^[A-Za-z0-9\-\.]+$/;
    if (!code.value.trim()) {
        showError(code, 'Mã phòng là bắt buộc.'); valid = false;
    } else if (!codePattern.test(code.value.trim())) {
        showError(code, 'Mã phòng chỉ được chứa chữ cái, số, dấu gạch ngang và dấu chấm.'); valid = false;
    } else if (code.value.length > 20) {
        showError(code, 'Mã phòng không được vượt quá 20 ký tự.'); valid = false;
    }

    if (!type.value) {
        showError(type, 'Vui lòng chọn loại phòng.'); valid = false;
    }
    if (!status.value) {
        showError(status, 'Vui lòng chọn trạng thái.'); valid = false;
    }

    if (!valid) e.preventDefault();
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
</script>
@endpush
