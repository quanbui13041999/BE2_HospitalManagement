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
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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
@endsection