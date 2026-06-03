{{-- resources/views/admin/rooms/schedule-edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Sửa Ca làm việc')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.rooms.schedule.index', ['date' => $schedule->work_date->toDateString()]) }}"
           class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0">
            Sửa Ca: <span class="text-primary">{{ $schedule->doctor->full_name }}</span>
            – {{ $schedule->work_date->format('d/m/Y') }}
        </h4>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Thông tin slot đã đặt --}}
    @if($schedule->booked_slots > 0)
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <span>Ca này đã có <strong>{{ $schedule->booked_slots }}</strong> lượt đặt. Số slot tối đa không thể nhỏ hơn con số này.</span>
    </div>
    @endif

    <div class="card shadow-sm" style="max-width:760px">
        <div class="card-header fw-semibold">
            <i class="bi bi-pencil me-2"></i>Thông tin ca làm việc
        </div>
        <form method="POST" action="{{ route('admin.rooms.schedule.update', $schedule) }}" id="scheduleEditForm">
            @csrf @method('PUT')
            {{-- Optimistic lock token: phát hiện xung đột cập nhật 2 tab --}}
            <input type="hidden" name="_lock_version" value="{{ $schedule->updated_at?->timestamp }}">
            <div class="card-body row g-3">

                <div class="col-md-6">
                    <label class="form-label">Bác sĩ <span class="text-danger">*</span></label>
                    <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}"
                                {{ old('doctor_id', $schedule->doctor_id) == $doctor->doctor_id ? 'selected' : '' }}>
                                {{ $doctor->full_name }} ({{ $doctor->department->department_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phòng khám</label>
                    <select name="room_id" class="form-select @error('room_id') is-invalid @enderror">
                        <option value="">-- Không chỉ định --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->room_id }}"
                                {{ old('room_id', $schedule->room_id) == $room->room_id ? 'selected' : '' }}>
                                {{ $room->room_code }} – {{ $room->room_name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ngày làm việc <span class="text-danger">*</span></label>
                    <input type="date" name="work_date"
                           class="form-control @error('work_date') is-invalid @enderror"
                           value="{{ old('work_date', $schedule->work_date->toDateString()) }}" required>
                    @error('work_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Giờ bắt đầu <span class="text-danger">*</span></label>
                    <input type="time" name="start_time"
                           class="form-control @error('start_time') is-invalid @enderror"
                           value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" required>
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Giờ kết thúc <span class="text-danger">*</span></label>
                    <input type="time" name="end_time"
                           class="form-control @error('end_time') is-invalid @enderror"
                           value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" required>
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Thời gian mỗi slot (phút)</label>
                    <select name="slot_duration" class="form-select" required>
                        @foreach([10, 15, 20, 30, 45, 60] as $d)
                            <option value="{{ $d }}"
                                {{ old('slot_duration', $schedule->slot_duration) == $d ? 'selected' : '' }}>
                                {{ $d }} phút
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Số slot tối đa <span class="text-danger">*</span>
                        @if($schedule->booked_slots > 0)
                            <span class="text-danger">(Tối thiểu: {{ $schedule->booked_slots }})</span>
                        @endif
                    </label>
                    <input type="number" name="max_slot"
                           class="form-control @error('max_slot') is-invalid @enderror"
                           value="{{ old('max_slot', $schedule->max_slot) }}"
                           min="{{ $schedule->booked_slots ?: 1 }}" max="100" required>
                    @error('max_slot')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select" required>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}"
                                {{ old('status', $schedule->status) === $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2">{{ old('note', $schedule->note) }}</textarea>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="scheduleSubmitBtn">
                    <i class="bi bi-floppy me-1"></i>Lưu thay đổi
                </button>
                <a href="{{ route('admin.rooms.schedule.index', ['date' => $schedule->work_date->toDateString()]) }}"
                   class="btn btn-outline-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('scheduleEditForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('scheduleSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang lưu...';
});

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

startRealtimeCheck('schedule', '{{ $schedule->schedule_id }}', '{{ $schedule->updated_at?->timestamp }}');
</script>
@endpush
