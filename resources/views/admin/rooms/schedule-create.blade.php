{{-- resources/views/admin/rooms/schedule-create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Thêm Ca làm việc')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.rooms.schedule.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0">Thêm Ca làm việc</h4>
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

    <div class="card shadow-sm" style="max-width:760px">
        <div class="card-header fw-semibold">
            <i class="bi bi-calendar-plus me-2"></i>Thông tin ca làm việc
        </div>
        <form method="POST" action="{{ route('admin.rooms.schedule.store') }}">
            @csrf
            <div class="card-body row g-3">

                {{-- Bác sĩ --}}
                <div class="col-md-6">
                    <label class="form-label">Bác sĩ <span class="text-danger">*</span></label>
                    <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                        <option value="">-- Chọn bác sĩ --</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}"
                                {{ old('doctor_id') == $doctor->doctor_id ? 'selected' : '' }}>
                                {{ $doctor->full_name }}
                                ({{ $doctor->department->department_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Phòng --}}
                <div class="col-md-6">
                    <label class="form-label">Phòng khám</label>
                    <select name="room_id" class="form-select @error('room_id') is-invalid @enderror">
                        <option value="">-- Chọn phòng --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->room_id }}"
                                {{ (old('room_id') ?? $selectedRoom?->room_id) == $room->room_id ? 'selected' : '' }}>
                                {{ $room->room_code }} – {{ $room->room_name ?? '' }} ({{ $room->room_type }})
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Ngày làm việc --}}
                <div class="col-md-4">
                    <label class="form-label">Ngày làm việc <span class="text-danger">*</span></label>
                    <input type="date" name="work_date"
                           class="form-control @error('work_date') is-invalid @enderror"
                           value="{{ old('work_date', request('work_date', today()->toDateString())) }}"
                           required>
                    @error('work_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Giờ bắt đầu --}}
                <div class="col-md-4">
                    <label class="form-label">Giờ bắt đầu <span class="text-danger">*</span></label>
                    <input type="time" name="start_time"
                           class="form-control @error('start_time') is-invalid @enderror"
                           value="{{ old('start_time', '08:00') }}" required>
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Giờ kết thúc --}}
                <div class="col-md-4">
                    <label class="form-label">Giờ kết thúc <span class="text-danger">*</span></label>
                    <input type="time" name="end_time"
                           class="form-control @error('end_time') is-invalid @enderror"
                           value="{{ old('end_time', '12:00') }}" required>
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Thời gian mỗi slot --}}
                <div class="col-md-4">
                    <label class="form-label">Thời gian mỗi slot (phút) <span class="text-danger">*</span></label>
                    <select name="slot_duration" class="form-select" id="slotDuration" required>
                        @foreach([10, 15, 20, 30, 45, 60] as $d)
                            <option value="{{ $d }}" {{ old('slot_duration', 30) == $d ? 'selected' : '' }}>
                                {{ $d }} phút
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Số slot tối đa --}}
                <div class="col-md-4">
                    <label class="form-label">Số slot tối đa <span class="text-danger">*</span></label>
                    <input type="number" name="max_slot" id="maxSlot"
                           class="form-control @error('max_slot') is-invalid @enderror"
                           value="{{ old('max_slot', 8) }}" min="1" max="100" required>
                    @error('max_slot')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" id="slotHint"></div>
                </div>

                {{-- Trạng thái --}}
                <div class="col-md-4">
                    <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ old('status', 'Hoạt động') === $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Ghi chú --}}
                <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2"
                              maxlength="255">{{ old('note') }}</textarea>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Tạo ca
                </button>
                <a href="{{ route('admin.rooms.schedule.index') }}" class="btn btn-outline-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Tính số slot gợi ý dựa trên khoảng thời gian
    function calcSlots() {
        const start = document.querySelector('[name=start_time]').value;
        const end   = document.querySelector('[name=end_time]').value;
        const dur   = parseInt(document.getElementById('slotDuration').value);
        const hint  = document.getElementById('slotHint');

        if (start && end && dur) {
            const [sh, sm] = start.split(':').map(Number);
            const [eh, em] = end.split(':').map(Number);
            const totalMin = (eh * 60 + em) - (sh * 60 + sm);
            if (totalMin > 0) {
                const suggested = Math.floor(totalMin / dur);
                hint.textContent = `Gợi ý: ${suggested} slot (${totalMin} phút ÷ ${dur} phút)`;
                document.getElementById('maxSlot').placeholder = suggested;
            }
        }
    }

    document.querySelector('[name=start_time]').addEventListener('change', calcSlots);
    document.querySelector('[name=end_time]').addEventListener('change', calcSlots);
    document.getElementById('slotDuration').addEventListener('change', calcSlots);
    calcSlots();
</script>
@endpush
@endsection
