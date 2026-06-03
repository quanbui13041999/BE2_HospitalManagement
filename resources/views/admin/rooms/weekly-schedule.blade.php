{{-- resources/views/admin/rooms/weekly-schedule.blade.php --}}
@extends('layouts.admin')

@section('title', 'Lịch phân bổ theo tuần - ' . ($selectedRoom->room_name ?? $selectedRoom->room_code ?? 'Chọn phòng'))

@push('styles')
<style>
    .weekly-header {
        background: linear-gradient(135deg, #0D47A1, #1976D2);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .week-grid {
        display: grid;
        grid-template-columns: 80px repeat(7, 1fr);
        border-radius: 12px;
        overflow-x: auto;
        border: 1px solid #e0e7ef;
        background: #fff;
    }
    .week-header {
        background: #F0F4FF;
        font-weight: 700;
        font-size: 13px;
        color: #0D47A1;
        text-align: center;
        padding: 12px 8px;
        border-right: 1px solid #e0e7ef;
        border-bottom: 1px solid #e0e7ef;
    }
    .week-time {
        background: #fafafa;
        font-size: 12px;
        font-weight: 600;
        color: #555;
        padding: 10px 8px;
        border-right: 1px solid #e0e7ef;
        border-bottom: 1px solid #f0f0f0;
        text-align: center;
    }
    .week-cell {
        border-right: 1px solid #f0f4ff;
        border-bottom: 1px solid #f0f4ff;
        padding: 6px 4px;
        min-height: 55px;
        background: #fff;
        transition: all 0.2s;
    }
    .week-cell:hover {
        background: #f8faff;
    }
    .week-slot {
        border-radius: 6px;
        padding: 4px 6px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.3;
        cursor: pointer;
        transition: all 0.2s;
    }
    .week-slot.has-dr {
        background: #DBEAFE;
        color: #1e40af;
        border-left: 3px solid #1e40af;
    }
    .week-slot.has-dr:hover {
        background: #bfdbfe;
        transform: translateX(2px);
    }
    .week-slot.empty {
        background: #DCFCE7;
        color: #166534;
        border-left: 3px solid #166534;
    }
    .week-slot.locked {
        background: #F1F5F9;
        color: #94a3b8;
        border-left: 3px solid #94a3b8;
    }
    .room-selector {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .week-nav {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 20px;
    }
    .room-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 20px;
    }
    .legend {
        display: flex;
        gap: 20px;
        margin-top: 20px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 10px;
        justify-content: center;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
    }
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-calendar-week me-2 text-primary"></i>Lịch phân bổ theo tuần</h4>
            <p class="text-muted small mb-0">Xem lịch làm việc của từng phòng khám theo tuần</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#autoAllocateModal">
                <i class="bi bi-magic me-1"></i>Tự động phân ca
            </button>
            <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Quay lại
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Chọn phòng --}}
    <div class="room-selector">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold"><i class="bi bi-door-open me-1"></i>Chọn phòng khám</label>
                <select name="room_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Chọn phòng --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->room_id }}" {{ ($selectedRoomId ?? '') == $room->room_id ? 'selected' : '' }}>
                            {{ $room->room_code }} - {{ $room->room_name ?? 'Không tên' }} ({{ $room->room_type }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold"><i class="bi bi-calendar me-1"></i>Tuần bắt đầu từ</label>
                <input type="date" name="week_start" class="form-control" value="{{ $weekStart->toDateString() }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Xem lịch
                </button>
            </div>
        </form>
    </div>

    @if($selectedRoom)
        {{-- Thông tin phòng --}}
        <div class="room-info-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $selectedRoom->room_name ?? $selectedRoom->room_code }}</h3>
                    <p class="mb-0 opacity-75">
                        <i class="bi bi-building me-1"></i>{{ $selectedRoom->department->department_name ?? 'Chưa có khoa' }}
                        | <i class="bi bi-tag me-1"></i>{{ $selectedRoom->room_type }}
                        | <span class="badge bg-light text-dark">{{ $selectedRoom->status }}</span>
                    </p>
                </div>
                <a href="{{ route('admin.rooms.schedule.create', ['room_id' => $selectedRoom->room_id]) }}" 
                   class="btn btn-light">
                    <i class="bi bi-plus-circle me-1"></i>Thêm ca cho phòng này
                </a>
            </div>
        </div>

        {{-- Điều hướng tuần --}}
        <div class="week-nav">
            <a href="{{ route('admin.rooms.weekly', ['room_id' => $selectedRoom->room_id, 'week_start' => $prevWeek]) }}" 
               class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i> Tuần trước
            </a>
            <span class="fw-semibold fs-5">
                <i class="bi bi-calendar3 me-2"></i>
                {{ $weekDates->first()->format('d/m/Y') }} – {{ $weekDates->last()->format('d/m/Y') }}
            </span>
            <a href="{{ route('admin.rooms.weekly', ['room_id' => $selectedRoom->room_id, 'week_start' => $nextWeek]) }}" 
               class="btn btn-outline-secondary">
                Tuần sau <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        {{-- Bảng lịch tuần --}}
        <div class="card shadow-sm">
            <div class="card-body p-0" style="overflow-x: auto;">
                <div class="week-grid" style="min-width: 800px;">
                    {{-- Header: Giờ --}}
                    <div class="week-header" style="border-bottom: 2px solid #e0e7ef;">Giờ</div>
                    @foreach($weekDates as $date)
                        <div class="week-header {{ $date->isToday() ? 'bg-primary text-white' : '' }}" 
                             style="border-bottom: 2px solid #e0e7ef;">
                            {{ $date->isoFormat('dddd') }}<br>
                            <span style="font-size: 11px;">{{ $date->format('d/m/Y') }}</span>
                        </div>
                    @endforeach

                    {{-- Các dòng giờ --}}
                    @foreach($timeSlots as $time)
                        <div class="week-time">{{ $time }}</div>
                        @foreach($weekDates as $date)
                            @php
                                $dateKey = $date->format('Y-m-d');
                                $daySchedules = $weekSchedules->get($dateKey, collect());
                                $slot = $daySchedules->first(function($s) use ($time) {
                                    return $s->start_time <= $time . ':00' && $s->end_time > $time . ':00';
                                });
                            @endphp
                            <div class="week-cell">
                                @if($slot)
                                    @if($slot->status === 'Hoạt động')
                                        <div class="week-slot has-dr" 
                                             title="Bác sĩ: {{ $slot->doctor->full_name ?? '' }}&#10;Thời gian: {{ substr($slot->start_time,0,5) }} - {{ substr($slot->end_time,0,5) }}&#10;Slot: {{ $slot->booked_slots }}/{{ $slot->max_slot }}"
                                             onclick="window.location='{{ route('admin.rooms.schedule.edit', $slot) }}'">
                                            <i class="bi bi-person-fill me-1"></i>
                                            {{ $slot->doctor ? mb_substr($slot->doctor->full_name, 0, 12) : '—' }}
                                        </div>
                                    @else
                                        <div class="week-slot locked" title="Ca đã tạm dừng">
                                            <i class="bi bi-pause-circle me-1"></i>Tạm dừng
                                        </div>
                                    @endif
                                @else
                                    <div class="week-slot empty" 
                                         style="opacity: 0.5; cursor: pointer;"
                                         onclick="window.location='{{ route('admin.rooms.schedule.create', ['room_id' => $selectedRoom->room_id]) }}?work_date={{ $date->toDateString() }}'">
                                        <i class="bi bi-plus-circle me-1"></i>Trống
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Chú thích --}}
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #DBEAFE; border-left: 3px solid #1e40af;"></div>
                <span>Có ca hoạt động</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #DCFCE7; border-left: 3px solid #166534;"></div>
                <span>Trống - Có thể thêm ca</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #F1F5F9; border-left: 3px solid #94a3b8;"></div>
                <span>Tạm dừng / Đã huỷ</span>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-door-open fs-1 d-block mb-3"></i>
            <h5>Vui lòng chọn phòng để xem lịch phân bổ</h5>
            <p class="mb-0">Chọn một phòng từ danh sách để xem chi tiết lịch làm việc trong tuần</p>
        </div>
    @endif
</div>

{{-- ── MODAL TỰ ĐỘNG PHÂN CA ────────────────────────────────────────── --}}
<div class="modal fade" id="autoAllocateModal" tabindex="-1" aria-labelledby="autoAllocateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold" id="autoAllocateModalLabel">
                    <i class="bi bi-magic me-2"></i>Tự động Phân bổ Ca trực
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.rooms.schedule.auto-allocate') }}" method="POST" id="autoAllocateForm">
                @csrf
                <div class="modal-body p-4">
                    <div id="modalAlertContainer"></div>
                    
                    <div class="alert alert-info py-2 px-3 small border-0 mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Hệ thống sẽ tự động ghép các bác sĩ đang hoạt động vào các phòng khám phù hợp với chuyên khoa trong khoảng thời gian đã chọn, đảm bảo không trùng giờ làm việc của bác sĩ hoặc phòng.
                    </div>

                    <div class="row g-3">
                        {{-- Ngày bắt đầu --}}
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Từ ngày</label>
                            <input type="date" name="start_date" class="form-control" 
                                   value="{{ $weekDates->first()->toDateString() }}" required>
                        </div>
                        {{-- Ngày kết thúc --}}
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Đến ngày</label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="{{ $weekDates->last()->toDateString() }}" required>
                        </div>

                        {{-- Chọn khoa --}}
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Khoa áp dụng</label>
                            <select name="department_id" class="form-select">
                                <option value="">-- Tất cả các khoa --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Thời gian & Max slots --}}
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Thời lượng slot</label>
                            <select name="slot_duration" class="form-select">
                                <option value="15">15 phút</option>
                                <option value="20">20 phút</option>
                                <option value="30" selected>30 phút</option>
                                <option value="45">45 phút</option>
                                <option value="60">60 phút</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Số slot tối đa</label>
                            <input type="number" name="max_slot" class="form-control" value="8" min="1" max="100" required>
                        </div>

                        {{-- Các ca trực --}}
                        <div class="col-12 mt-2">
                            <label class="form-label small fw-semibold d-block">Ca trực áp dụng</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="morning_enabled" value="1" id="morningCheck" checked>
                                <label class="form-check-label" for="morningCheck">
                                    Ca Sáng (08:00 - 12:00)
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="afternoon_enabled" value="1" id="afternoonCheck" checked>
                                <label class="form-check-label" for="afternoonCheck">
                                    Ca Chiều (13:30 - 17:30)
                                </label>
                            </div>
                        </div>

                        {{-- Ghi đè ca cũ --}}
                        <div class="col-12 mt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="overwriteSwitch">
                                <label class="form-check-label fw-semibold text-danger small" for="overwriteSwitch">
                                    Xóa dọn dẹp các ca trống cũ trong khoảng ngày này trước khi phân bổ
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Huỷ bỏ</button>
                    <button type="submit" class="btn btn-success btn-sm px-3">
                        <i class="bi bi-play-fill"></i> Tiến hành phân ca
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('autoAllocateForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const startDateInput = form.querySelector('[name="start_date"]');
            const endDateInput = form.querySelector('[name="end_date"]');
            const morningCheck = form.querySelector('#morningCheck');
            const afternoonCheck = form.querySelector('#afternoonCheck');
            const alertContainer = document.getElementById('modalAlertContainer');
            
            // Clear previous alerts
            alertContainer.innerHTML = '';
            
            let errors = [];
            
            if (!startDateInput.value) {
                errors.push("Vui lòng chọn Ngày bắt đầu.");
            }
            if (!endDateInput.value) {
                errors.push("Vui lòng chọn Ngày kết thúc.");
            }
            
            if (startDateInput.value && endDateInput.value) {
                const startVal = new Date(startDateInput.value);
                const endVal = new Date(endDateInput.value);
                if (endVal < startVal) {
                    errors.push("Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.");
                }
            }
            
            if (!morningCheck.checked && !afternoonCheck.checked) {
                errors.push("Vui lòng chọn ít nhất một ca trực (Sáng hoặc Chiều).");
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger py-2 px-3 small border-0 mb-3 d-flex align-items-center gap-2';
                alertDiv.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>${errors.join('<br>')}</div>
                `;
                alertContainer.appendChild(alertDiv);
                
                // Scroll modal to top to view error
                form.querySelector('.modal-body').scrollTop = 0;
            }
        });
    }
});
</script>
@endpush
@endsection