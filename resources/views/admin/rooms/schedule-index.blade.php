{{-- resources/views/admin/rooms/schedule-index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Phân bổ Ca làm việc')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Phân bổ Ca làm việc</h4>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ $date ?? date('Y-m-d') }}" style="width:180px">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i> Xem
                </button>
            </form>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#autoAllocateModal">
                <i class="bi bi-magic me-1"></i>Tự động phân ca
            </button>
            <button type="button" class="btn btn-primary" onclick="openCreateScheduleModal('', '{{ $date ?? date('Y-m-d') }}')">
                <i class="bi bi-plus-lg me-1"></i>Thêm ca
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Thanh điều hướng ngày --}}
    <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
        @php
            $dateObj = \Carbon\Carbon::parse($date ?? date('Y-m-d'));
            $prevDate = $dateObj->copy()->subDay()->toDateString();
            $nextDate = $dateObj->copy()->addDay()->toDateString();
        @endphp
        <a href="{{ route('admin.rooms.schedule.index', ['date' => $prevDate]) }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-chevron-left"></i> Hôm trước
        </a>
        <span class="fw-semibold fs-5">
            {{ $dateObj->isoFormat('dddd, DD/MM/YYYY') }}
            @if($dateObj->isToday())
                <span class="badge bg-primary ms-2">Hôm nay</span>
            @endif
        </span>
        <a href="{{ route('admin.rooms.schedule.index', ['date' => $nextDate]) }}"
           class="btn btn-outline-secondary btn-sm">
            Hôm sau <i class="bi bi-chevron-right"></i>
        </a>
    </div>

    {{-- Thống kê nhanh --}}
    @php
        $totalSchedules = isset($rooms) ? $rooms->sum(fn($r) => $r->schedules->count()) : 0;
        $totalSlots     = isset($rooms) ? $rooms->sum(fn($r) => $r->schedules->sum('max_slot')) : 0;
        $totalBooked    = isset($rooms) ? $rooms->sum(fn($r) => $r->schedules->sum('booked_slots')) : 0;
        $roomsInUse     = isset($rooms) ? $rooms->filter(fn($r) => $r->schedules->isNotEmpty())->count() : 0;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-primary-subtle">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-primary">{{ $totalSchedules }}</div>
                    <div class="small text-muted">Ca làm việc</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-success-subtle">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-success">{{ $roomsInUse }}</div>
                    <div class="small text-muted">Phòng có ca</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-info-subtle">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-info">{{ $totalBooked }}</div>
                    <div class="small text-muted">Lượt đặt</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-warning-subtle">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-warning">{{ $totalSlots - $totalBooked }}</div>
                    <div class="small text-muted">Slot còn trống</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lưới phòng --}}
    @if(isset($rooms) && $rooms->count() > 0)
    <div class="row g-3">
        @foreach($rooms as $room)
        @php
            $statusColors = [
                'Trống'        => 'success',
                'Đang sử dụng' => 'primary',
                'Bảo trì'      => 'danger',
                'Vệ sinh'      => 'warning',
            ];
            $roomColor = $statusColors[$room->status] ?? 'secondary';
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm h-100">
                {{-- Header phòng --}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">{{ $room->room_name ?? $room->room_code }}</span>
                        <span class="badge bg-info-subtle text-info ms-2">{{ $room->room_type }}</span>
                        @if($room->department)
                            <div class="small text-muted">{{ $room->department->department_name }}</div>
                        @endif
                    </div>
                    <span class="badge bg-{{ $roomColor }}">{{ $room->status }}</span>
                </div>
                <div class="card-body p-0">
                    @forelse($room->schedules as $schedule)
                    @php
                        $pct = $schedule->max_slot > 0
                            ? round($schedule->booked_slots / $schedule->max_slot * 100)
                            : 0;
                        $barColor = $pct >= 100 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
                    @endphp
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <i class="bi bi-clock text-muted me-1"></i>
                                <span class="fw-semibold">
                                    {{ substr($schedule->start_time, 0, 5) }} – {{ substr($schedule->end_time, 0, 5) }}
                                </span>
                                <span class="badge bg-{{ $schedule->status === 'Hoạt động' ? 'success' : 'secondary' }} ms-1">
                                    {{ $schedule->status }}
                                </span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-sm" title="Sửa ca"
                                    onclick="openEditScheduleModal('{{ $schedule->schedule_id }}', '{{ $schedule->doctor_id }}', '{{ $schedule->room_id }}', '{{ $schedule->work_date->toDateString() }}', '{{ substr($schedule->start_time, 0, 5) }}', '{{ substr($schedule->end_time, 0, 5) }}', '{{ $schedule->slot_duration }}', '{{ $schedule->max_slot }}', '{{ $schedule->booked_slots }}', '{{ $schedule->status }}', '{{ addslashes($schedule->note) }}', '{{ $schedule->updated_at?->timestamp }}', '{{ route('admin.rooms.schedule.update', $schedule) }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($schedule->booked_slots === 0)
                                <form method="POST"
                                      action="{{ route('admin.rooms.schedule.destroy', $schedule) }}"
                                      onsubmit="return confirm('Xoá ca này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Xoá">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-person-badge me-1"></i>
                            {{ $schedule->doctor->full_name ?? '—' }}
                        </div>
                        {{-- Thanh tiến độ slot --}}
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Đã đặt: <strong class="text-{{ $barColor }}">{{ $schedule->booked_slots }}</strong> / {{ $schedule->max_slot }}</span>
                            <span class="text-muted">{{ $pct }}%</span>
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar bg-{{ $barColor }}" style="width:{{ $pct }}%"></div>
                        </div>
                        @if($schedule->note)
                        <div class="small text-muted mt-1">
                            <i class="bi bi-chat"></i> {{ $schedule->note }}
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="p-3 text-center text-muted small">
                        <i class="bi bi-calendar-x me-1"></i>Chưa có ca làm việc
                    </div>
                    @endforelse
                </div>
                <div class="card-footer p-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="openCreateScheduleModal('{{ $room->room_id }}', '{{ $date ?? date('Y-m-d') }}')">
                        <i class="bi bi-plus-circle me-1"></i>Thêm ca cho phòng này
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else


    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle me-2"></i>
        Không có phòng nào trong hệ thống. Vui lòng <a href="{{ route('admin.rooms.create') }}">thêm phòng</a> trước khi phân bổ ca.
    </div>
    @endif
</div>

{{-- ── MODAL THÊM CA LÀM VIỆC MỚI ────────────────────────────────────────── --}}
<div class="modal fade" id="modalCreateSchedule" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2 text-primary"></i>Thêm Ca làm việc mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.rooms.schedule.store') }}" id="createScheduleFormModal" novalidate>
                @csrf
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="create_sched_doctor_id">Bác sĩ <span class="text-danger">*</span></label>
                        <select name="doctor_id" id="create_sched_doctor_id" class="form-select" required>
                            <option value="">-- Chọn bác sĩ --</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->doctor_id }}">
                                    {{ $doctor->full_name }} ({{ $doctor->department->department_name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="create_sched_room_id">Phòng khám</label>
                        <select name="room_id" id="create_sched_room_id" class="form-select">
                            <option value="">-- Chọn phòng --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->room_id }}">
                                    {{ $room->room_code }} – {{ $room->room_name ?? '' }} ({{ $room->room_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="create_sched_work_date">Ngày làm việc <span class="text-danger">*</span></label>
                        <input type="date" name="work_date" id="create_sched_work_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="create_sched_start_time">Giờ bắt đầu <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="create_sched_start_time" class="form-control" value="08:00" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="create_sched_end_time">Giờ kết thúc <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="create_sched_end_time" class="form-control" value="12:00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="create_sched_slot_duration">Thời lượng slot <span class="text-danger">*</span></label>
                        <select name="slot_duration" id="create_sched_slot_duration" class="form-select" required>
                            @foreach([10, 15, 20, 30, 45, 60] as $d)
                                <option value="{{ $d }}" {{ $d == 30 ? 'selected' : '' }}>{{ $d }} phút</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="create_sched_max_slot">Số slot tối đa <span class="text-danger">*</span></label>
                        <input type="number" name="max_slot" id="create_sched_max_slot" class="form-control" value="8" min="1" max="100" required>
                        <div class="form-text small" id="create_sched_slot_hint"></div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold" for="create_sched_status">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" id="create_sched_status" class="form-select" required>
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" {{ $st === 'Hoạt động' ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold" for="create_sched_note">Ghi chú</label>
                        <textarea name="note" id="create_sched_note" class="form-control" rows="2" maxlength="255" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="createSchedSubmitBtn">
                        <i class="bi bi-floppy me-1"></i>Tạo ca
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── MODAL SỬA CA LÀM VIỆC ────────────────────────────────────────── --}}
<div class="modal fade" id="modalEditSchedule" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Sửa Ca làm việc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editScheduleFormModal" novalidate>
                @csrf @method('PUT')
                <input type="hidden" name="_lock_version" id="edit_sched_lock_version">
                <div class="modal-body row g-3">
                    <div id="editSchedAlertContainer"></div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="edit_sched_doctor_id">Bác sĩ <span class="text-danger">*</span></label>
                        <select name="doctor_id" id="edit_sched_doctor_id" class="form-select" required>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->doctor_id }}">
                                    {{ $doctor->full_name }} ({{ $doctor->department->department_name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="edit_sched_room_id">Phòng khám</label>
                        <select name="room_id" id="edit_sched_room_id" class="form-select">
                            <option value="">-- Không chỉ định --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->room_id }}">
                                    {{ $room->room_code }} – {{ $room->room_name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="edit_sched_work_date">Ngày làm việc <span class="text-danger">*</span></label>
                        <input type="date" name="work_date" id="edit_sched_work_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="edit_sched_start_time">Giờ bắt đầu <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="edit_sched_start_time" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold" for="edit_sched_end_time">Giờ kết thúc <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="edit_sched_end_time" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="edit_sched_slot_duration">Thời lượng slot <span class="text-danger">*</span></label>
                        <select name="slot_duration" id="edit_sched_slot_duration" class="form-select" required>
                            @foreach([10, 15, 20, 30, 45, 60] as $d)
                                <option value="{{ $d }}">{{ $d }} phút</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="edit_sched_max_slot">
                            Số slot tối đa <span class="text-danger">*</span>
                            <span id="edit_sched_min_slot_warning" class="text-danger small"></span>
                        </label>
                        <input type="number" name="max_slot" id="edit_sched_max_slot" class="form-control" min="1" max="100" required>
                        <div class="form-text small" id="edit_sched_slot_hint"></div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold" for="edit_sched_status">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" id="edit_sched_status" class="form-select" required>
                            @foreach($statuses as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold" for="edit_sched_note">Ghi chú</label>
                        <textarea name="note" id="edit_sched_note" class="form-control" rows="2" maxlength="255" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="editSchedSubmitBtn">
                        <i class="bi bi-floppy me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
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
                                   value="{{ today()->toDateString() }}" required>
                        </div>
                        {{-- Ngày kết thúc --}}
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Đến ngày</label>
                            <input type="date" name="end_date" class="form-control" 
                                   value="{{ today()->addDays(6)->toDateString() }}" required>
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

{{-- ── MODAL HIỂN THỊ KẾT QUẢ TỰ ĐỘNG PHÂN CA ────────────────────────── --}}
<div class="modal fade" id="autoAllocateResultModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-info-circle-fill me-2"></i>Kết quả phân bổ ca trực tự động
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.reload()"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                <div id="allocationSummary" class="alert alert-success border-0 mb-3">
                    <!-- Summary message will be injected here -->
                </div>
                
                <h6 class="fw-bold text-success mb-2"><i class="bi bi-check-circle-fill me-1"></i>Danh sách phân ca thành công (<span id="allocatedCount">0</span>)</h6>
                <div class="table-responsive mb-4 shadow-sm rounded" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6;">
                    <table class="table table-sm table-striped table-hover align-middle border-0 mb-0" id="allocatedTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Bác sĩ</th>
                                <th>Ngày trực</th>
                                <th>Ca trực</th>
                                <th>Phòng khám</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Success rows will be injected here -->
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold text-warning mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>Danh sách bỏ qua/không thể phân (<span id="skippedCount">0</span>)</h6>
                <div class="table-responsive shadow-sm rounded" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6;">
                    <table class="table table-sm table-striped table-hover align-middle border-0 mb-0" id="skippedTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Bác sĩ</th>
                                <th>Ngày trực</th>
                                <th>Ca trực</th>
                                <th>Lý do bỏ qua</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Skipped rows will be injected here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light">
                <button type="button" class="btn btn-primary btn-sm px-4" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Đóng & Tải lại trang
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('autoAllocateForm');
    if (form) {
        form.addEventListener('submit', async function (e) {
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
            
            if (startDateInput.value) {
                const today = new Date();
                today.setHours(0,0,0,0);
                const startVal = new Date(startDateInput.value);
                startVal.setHours(0,0,0,0);
                if (startVal < today) {
                    errors.push("Ngày bắt đầu không được ở trong quá khứ.");
                }
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
            } else {
                e.preventDefault();
                const btn = form.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang phân ca...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-play-fill"></i> Tiến hành phân ca';

                    if (!response.ok || !data.success) {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger py-2 px-3 small border-0 mb-3 d-flex align-items-center gap-2';
                        alertDiv.innerHTML = `
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div>${data.message || 'Có lỗi xảy ra khi tự động phân ca.'}</div>
                        `;
                        alertContainer.appendChild(alertDiv);
                        form.querySelector('.modal-body').scrollTop = 0;
                    } else {
                        // Success! Hide the input modal
                        const allocateModal = bootstrap.Modal.getInstance(document.getElementById('autoAllocateModal'));
                        if (allocateModal) allocateModal.hide();

                        // Populate summary details
                        document.getElementById('allocationSummary').textContent = data.message;
                        
                        // Populate success table
                        const allocatedTableBody = document.querySelector('#allocatedTable tbody');
                        allocatedTableBody.innerHTML = '';
                        document.getElementById('allocatedCount').textContent = data.allocated.length;
                        if (data.allocated.length === 0) {
                            allocatedTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Không có ca trực nào được tạo mới.</td></tr>';
                        } else {
                            data.allocated.forEach(item => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td><strong>${item.doctor}</strong></td>
                                    <td>${item.date}</td>
                                    <td><span class="badge bg-primary-subtle text-primary">${item.session}</span></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">${item.room}</span></td>
                                `;
                                allocatedTableBody.appendChild(tr);
                            });
                        }

                        // Populate skipped table
                        const skippedTableBody = document.querySelector('#skippedTable tbody');
                        skippedTableBody.innerHTML = '';
                        document.getElementById('skippedCount').textContent = data.skipped.length;
                        if (data.skipped.length === 0) {
                            skippedTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted text-success py-3">Không có ca nào bị bỏ qua.</td></tr>';
                        } else {
                            data.skipped.forEach(item => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td>${item.doctor}</td>
                                    <td>${item.date}</td>
                                    <td><span class="badge bg-light text-dark">${item.session}</span></td>
                                    <td class="text-danger small">${item.reason}</td>
                                `;
                                skippedTableBody.appendChild(tr);
                            });
                        }

                        // Show results modal
                        const resultModal = new bootstrap.Modal(document.getElementById('autoAllocateResultModal'));
                        resultModal.show();
                    }
                } catch (err) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-play-fill"></i> Tiến hành phân ca';
                    
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger py-2 px-3 small border-0 mb-3 d-flex align-items-center gap-2';
                    alertDiv.innerHTML = `
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>Không thể kết nối máy chủ. Vui lòng thử lại.</div>
                    `;
                    alertContainer.appendChild(alertDiv);
                    form.querySelector('.modal-body').scrollTop = 0;
                }
            }
        });
    }
});

// ── Gợi ý slot cho modals ───────────────────────────────────────────
function bindCalcSlots(startSelector, endSelector, durationSelector, maxSlotSelector, hintSelector) {
    const start = document.querySelector(startSelector);
    const end = document.querySelector(endSelector);
    const duration = document.querySelector(durationSelector);
    const hint = document.querySelector(hintSelector);
    const maxSlot = document.querySelector(maxSlotSelector);

    if (!start || !end || !duration || !hint) return;

    const update = () => {
        if (start.value && end.value && duration.value) {
            const [sh, sm] = start.value.split(':').map(Number);
            const [eh, em] = end.value.split(':').map(Number);
            const totalMin = (eh * 60 + em) - (sh * 60 + sm);
            if (totalMin > 0) {
                const suggested = Math.floor(totalMin / parseInt(duration.value));
                hint.textContent = `Gợi ý: ${suggested} slot (${totalMin} phút ÷ ${duration.value} phút)`;
                maxSlot.placeholder = suggested;
            } else {
                hint.textContent = '';
            }
        }
    };

    start.addEventListener('change', update);
    end.addEventListener('change', update);
    duration.addEventListener('change', update);
    update();
}

document.addEventListener('DOMContentLoaded', () => {
    bindCalcSlots('#create_sched_start_time', '#create_sched_end_time', '#create_sched_slot_duration', '#create_sched_max_slot', '#create_sched_slot_hint');
    bindCalcSlots('#edit_sched_start_time', '#edit_sched_end_time', '#edit_sched_slot_duration', '#edit_sched_max_slot', '#edit_sched_slot_hint');
});

// ── Xử lý mở Modal Thêm Ca Trực ──────────────────────────────────────
function openCreateScheduleModal(roomId, workDate) {
    const form = document.getElementById('createScheduleFormModal');
    form.reset();

    document.getElementById('create_sched_room_id').value = roomId || '';
    document.getElementById('create_sched_work_date').value = workDate || '';
    document.getElementById('create_sched_start_time').value = '08:00';
    document.getElementById('create_sched_end_time').value = '12:00';
    document.getElementById('create_sched_slot_duration').value = '30';
    document.getElementById('create_sched_max_slot').value = '8';

    // Xóa class validation cũ
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    // Gợi ý slot
    const suggested = Math.floor((4 * 60) / 30);
    document.getElementById('create_sched_slot_hint').textContent = `Gợi ý: ${suggested} slot (240 phút ÷ 30 phút)`;

    new bootstrap.Modal(document.getElementById('modalCreateSchedule')).show();
}

// Interval cho realtime checking của ca trực đang sửa
let editSchedRealtimeInterval = null;

// ── Xử lý mở Modal Sửa Ca Trực ────────────────────────────────────────
function openEditScheduleModal(scheduleId, doctorId, roomId, workDate, startTime, endTime, duration, maxSlot, bookedSlots, status, note, lockVersion, actionUrl) {
    document.getElementById('edit_sched_doctor_id').value = doctorId;
    document.getElementById('edit_sched_room_id').value = roomId || '';
    document.getElementById('edit_sched_work_date').value = workDate;
    document.getElementById('edit_sched_start_time').value = startTime;
    document.getElementById('edit_sched_end_time').value = endTime;
    document.getElementById('edit_sched_slot_duration').value = duration;
    document.getElementById('edit_sched_max_slot').value = maxSlot;
    document.getElementById('edit_sched_max_slot').min = bookedSlots || 1;
    document.getElementById('edit_sched_status').value = status;
    document.getElementById('edit_sched_note').value = note === 'undefined' ? '' : note;
    document.getElementById('edit_sched_lock_version').value = lockVersion;

    // Cập nhật action url cho form
    document.getElementById('editScheduleFormModal').action = actionUrl;

    // Hiển thị cảnh báo slot tối thiểu nếu đã booked
    const warningEl = document.getElementById('edit_sched_min_slot_warning');
    if (parseInt(bookedSlots) > 0) {
        warningEl.textContent = `(Tối thiểu: ${bookedSlots} slot đã đặt)`;
    } else {
        warningEl.textContent = '';
    }

    // Xóa class validation cũ
    const form = document.getElementById('editScheduleFormModal');
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.getElementById('editSchedAlertContainer').innerHTML = '';

    // Hiển thị modal
    const modalEl = document.getElementById('modalEditSchedule');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Khởi chạy Realtime Checking khi mở modal sửa ca trực
    if (editSchedRealtimeInterval) clearInterval(editSchedRealtimeInterval);
    editSchedRealtimeInterval = setInterval(async () => {
        try {
            const response = await fetch(`/admin/api/check-entity-status?type=schedule&id=${scheduleId}&lock_version=${lockVersion}`);
            const data = await response.json();
            if (data.success && data.status !== 'unchanged') {
                clearInterval(editSchedRealtimeInterval);
                alert(data.message);
                window.location.reload();
            }
        } catch (error) {
            console.error('Lỗi khi kiểm tra trạng thái ca trực:', error);
        }
    }, 5000);
}

// Dừng Realtime Checking khi đóng modal
document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('modalEditSchedule');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', () => {
            if (editSchedRealtimeInterval) {
                clearInterval(editSchedRealtimeInterval);
                editSchedRealtimeInterval = null;
            }
        });
    }
});

// ── Client-side validation cho Create/Edit Schedule Modals ───────────
document.addEventListener('DOMContentLoaded', () => {
    const createForm = document.getElementById('createScheduleFormModal');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            let valid = true;
            const doc = document.getElementById('create_sched_doctor_id');
            const workDate = document.getElementById('create_sched_work_date');
            const startTime = document.getElementById('create_sched_start_time');
            const endTime = document.getElementById('create_sched_end_time');
            const maxSlot = document.getElementById('create_sched_max_slot');

            [doc, workDate, startTime, endTime, maxSlot].forEach(el => el.classList.remove('is-invalid'));

            if (!doc.value) {
                showError(doc, 'Vui lòng chọn bác sĩ.'); valid = false;
            }
            if (!workDate.value) {
                showError(workDate, 'Vui lòng chọn ngày làm việc.'); valid = false;
            }
            if (!startTime.value) {
                showError(startTime, 'Vui lòng chọn giờ bắt đầu.'); valid = false;
            }
            if (!endTime.value) {
                showError(endTime, 'Vui lòng chọn giờ kết thúc.'); valid = false;
            }
            if (startTime.value && endTime.value && startTime.value >= endTime.value) {
                showError(endTime, 'Giờ kết thúc phải lớn hơn giờ bắt đầu.'); valid = false;
            }
            if (!maxSlot.value || parseInt(maxSlot.value) < 1) {
                showError(maxSlot, 'Số slot tối đa phải >= 1.'); valid = false;
            }

            if (!valid) {
                e.preventDefault();
            } else {
                // Double submit protection
                const btn = document.getElementById('createSchedSubmitBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang tạo...';
            }
        });
    }

    const editForm = document.getElementById('editScheduleFormModal');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            let valid = true;
            const doc = document.getElementById('edit_sched_doctor_id');
            const workDate = document.getElementById('edit_sched_work_date');
            const startTime = document.getElementById('edit_sched_start_time');
            const endTime = document.getElementById('edit_sched_end_time');
            const maxSlot = document.getElementById('edit_sched_max_slot');

            [doc, workDate, startTime, endTime, maxSlot].forEach(el => el.classList.remove('is-invalid'));

            if (!doc.value) {
                showError(doc, 'Vui lòng chọn bác sĩ.'); valid = false;
            }
            if (!workDate.value) {
                showError(workDate, 'Vui lòng chọn ngày làm việc.'); valid = false;
            }
            if (!startTime.value) {
                showError(startTime, 'Vui lòng chọn giờ bắt đầu.'); valid = false;
            }
            if (!endTime.value) {
                showError(endTime, 'Vui lòng chọn giờ kết thúc.'); valid = false;
            }
            if (startTime.value && endTime.value && startTime.value >= endTime.value) {
                showError(endTime, 'Giờ kết thúc phải lớn hơn giờ bắt đầu.'); valid = false;
            }
            if (!maxSlot.value || parseInt(maxSlot.value) < parseInt(maxSlot.min)) {
                showError(maxSlot, `Số slot tối đa không được nhỏ hơn số slot đã đặt (${maxSlot.min}).`); valid = false;
            }

            if (!valid) {
                e.preventDefault();
            } else {
                // Double submit protection
                const btn = document.getElementById('editSchedSubmitBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang lưu...';
            }
        });
    }
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
@endsection