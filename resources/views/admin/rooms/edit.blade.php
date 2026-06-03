{{-- resources/views/admin/rooms/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Sửa Phòng: ' . $room->room_code)

@push('styles')
<style>
.form-card { max-width: 700px; }
.field-hint { font-size: 12px; color: #90A4AE; margin-top: 3px; }
.char-count { font-size: 11px; color: #90A4AE; text-align: right; }
.char-count.warn { color: #f57c00; }
.char-count.over { color: #c62828; }
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
        <a href="{{ route('admin.rooms.show', $room) }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">
                Sửa Phòng: <span class="text-primary">{{ $room->room_code }}</span>
            </h4>
            <p class="text-muted small mb-0">Cập nhật thông tin phòng khám</p>
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
            <i class="bi bi-pencil me-2 text-primary"></i>Thông tin phòng khám
        </div>
        <form method="POST" action="{{ route('admin.rooms.update', $room) }}" id="editRoomForm" novalidate>
            @csrf @method('PUT')
            {{-- Optimistic lock token: timestamp của updated_at để phát hiện xung đột 2 tab --}}
            <input type="hidden" name="_lock_version" value="{{ $room->updated_at?->timestamp }}">
            <div class="card-body row g-3">

                {{-- Mã phòng – READONLY, không cho sửa --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="room_code_display">
                        Mã phòng
                        <span class="locked-badge ms-1"><i class="bi bi-lock-fill"></i> Không thể sửa</span>
                    </label>
                    <input type="text"
                           id="room_code_display"
                           class="form-control locked-field"
                           value="{{ $room->room_code }}"
                           readonly
                           tabindex="-1">
                    <div class="field-hint">
                        <i class="bi bi-info-circle me-1"></i>Mã phòng là khóa định danh, không thể thay đổi sau khi tạo.
                    </div>
                </div>

                {{-- Tên phòng --}}
                <div class="col-md-8">
                    <label class="form-label fw-semibold" for="room_name">Tên phòng</label>
                    <input type="text"
                           id="room_name"
                           name="room_name"
                           class="form-control @error('room_name') is-invalid @enderror"
                           value="{{ old('room_name', $room->room_name) }}"
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
                        <option value="">-- Không thuộc khoa --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}"
                                {{ old('department_id', $room->department_id) == $dept->department_id ? 'selected' : '' }}>
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
                            <option value="{{ $type }}"
                                {{ old('room_type', $room->room_type) === $type ? 'selected' : '' }}>
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
                            <option value="{{ $st }}"
                                {{ old('status', $room->status) === $st ? 'selected' : '' }}>
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
                              placeholder="Mô tả thêm về phòng khám...">{{ old('notes', $room->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="char-count" id="notes_count">0 / 500</div>
                </div>

            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bi bi-floppy me-1"></i>Lưu thay đổi
                </button>
                <a href="{{ route('admin.rooms.show', $room) }}" class="btn btn-outline-secondary">
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
bindCharCount('room_name', 'room_name_count', 100);
bindCharCount('notes',     'notes_count',     500);

// ── Submit protection: disable sau khi click để tránh double-submit ──
document.getElementById('editRoomForm').addEventListener('submit', function(e) {
    let valid = true;
    const type   = document.getElementById('room_type');
    const status = document.getElementById('status');

    [type, status].forEach(el => el.classList.remove('is-invalid'));

    if (!type.value) {
        showError(type, 'Vui lòng chọn loại phòng.'); valid = false;
    }
    if (!status.value) {
        showError(status, 'Vui lòng chọn trạng thái.'); valid = false;
    }

    if (!valid) {
        e.preventDefault();
        return;
    }

    // Disable nút submit sau khi form hợp lệ để tránh double-submit
    const btn = document.getElementById('submitBtn');
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

startRealtimeCheck('room', '{{ $room->room_id }}', '{{ $room->updated_at?->timestamp }}');
</script>
@endpush
