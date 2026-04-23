{{-- resources/views/admin/rooms/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quản lý Phòng khám')

@push('styles')
<style>
    .room-stat {
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid #e0e7ef;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
        background: #fff;
    }

    .room-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .room-stat-val {
        font-size: 26px;
        font-weight: 800;
        line-height: 1;
    }

    .room-stat-label {
        font-size: 12px;
        color: #90A4AE;
        margin-top: 3px;
    }

    .room-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
    }

    .room-card {
        border-radius: 12px;
        padding: 16px 14px;
        cursor: pointer;
        transition: .2s;
        border: 2px solid transparent;
        user-select: none;
    }

    .room-card:hover {
        border-color: #0D47A1;
        box-shadow: 0 4px 16px rgba(13, 71, 161, .14);
        transform: translateY(-2px);
    }

    .room-card.s-using {
        background: #F0F4FF;
    }

    .room-card.s-empty {
        background: #E8F5E9;
    }

    .room-card.s-maintain {
        background: #FFEBEE;
    }

    .room-card.s-clean {
        background: #FFFDE7;
    }

    .room-card-code {
        font-size: 22px;
        font-weight: 800;
        color: #1a2332;
        line-height: 1.1;
    }

    .room-card-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-top: 6px;
    }

    .room-card-doc {
        font-size: 11px;
        color: #546e7a;
        margin-top: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .s-using .room-card-label {
        color: #0D47A1;
    }

    .s-empty .room-card-label {
        color: #2e7d32;
    }

    .s-maintain .room-card-label {
        color: #c62828;
    }

    .s-clean .room-card-label {
        color: #f57c00;
    }

    .sch-row {
        display: flex;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #edf1f7;
        align-items: center;
    }

    .sch-row:last-child {
        border-bottom: none;
    }

    .sch-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #0D47A1;
        flex-shrink: 0;
    }

    .sch-dot.on {
        background: #2e7d32;
    }

    .week-grid {
        display: grid;
        grid-template-columns: 70px repeat(7, 1fr);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e0e7ef;
    }

    .week-header {
        background: #F0F4FF;
        font-weight: 700;
        font-size: 12px;
        color: #0D47A1;
        text-align: center;
        padding: 8px 4px;
        border-right: 1px solid #e0e7ef;
    }

    .week-time {
        background: #fafafa;
        font-size: 11px;
        color: #90A4AE;
        padding: 6px 8px;
        border-right: 1px solid #e0e7ef;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
    }

    .week-cell {
        border-right: 1px solid #f0f4ff;
        border-bottom: 1px solid #f0f4ff;
        padding: 4px 3px;
        min-height: 38px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .week-slot {
        border-radius: 4px;
        padding: 2px 5px;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .week-slot.has-dr {
        background: #DBEAFE;
        color: #1e40af;
    }

    .week-slot.empty {
        background: #DCFCE7;
        color: #166534;
    }

    .week-slot.locked {
        background: #F1F5F9;
        color: #94a3b8;
    }

    .modal-assign .modal-header {
        background: linear-gradient(135deg, #0D47A1, #1976D2);
        color: #fff;
    }

    .modal-assign .btn-close {
        filter: invert(1);
    }

    .conflict-alert {
        display: none;
    }

    .conflict-alert.show {
        display: flex;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-hospital me-2 text-primary"></i>Quản Lý Phòng Khám & Phân Bổ Ca</h4>
            <p class="text-muted small mb-0">Theo dõi trạng thái phòng và phân bổ bác sĩ theo ca làm việc</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rooms.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle me-1"></i>Thêm phòng
            </a>
        </div>
    </div>

    {{-- Alerts --}}
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

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        @php
        $statDefs = [
        ['label'=>'Tổng số phòng', 'val'=>$stats['total'], 'icon'=>'bi-door-open', 'bg'=>'#E3F2FD','color'=>'#0D47A1'],
        ['label'=>'Đang sử dụng', 'val'=>$stats['in_use'], 'icon'=>'bi-person-check','bg'=>'#E8F5E9','color'=>'#2e7d32'],
        ['label'=>'Trống', 'val'=>$stats['empty'], 'icon'=>'bi-check-circle','bg'=>'#E8F5E9','color'=>'#388e3c'],
        ['label'=>'Bảo trì / Vệ sinh','val'=>$stats['maintain']+$stats['clean'],'icon'=>'bi-wrench','bg'=>'#FFEBEE','color'=>'#c62828'],
        ];
        @endphp
        @foreach($statDefs as $s)
        <div class="col-6 col-md-3">
            <div class="room-stat">
                <div class="room-stat-icon" style="background:{{ $s['bg'] }}; color:{{ $s['color'] }}">
                    <i class="bi {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="room-stat-val" style="color:{{ $s['color'] }}">{{ $s['val'] }}</div>
                    <div class="room-stat-label">{{ $s['label'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- HAI CỘT CHÍNH --}}
    <div class="row g-4">

        {{-- ========== CỘT TRÁI: Lưới phòng ========== --}}
        <div class="col-lg-8">

            {{-- Bộ lọc nhỏ --}}
            <div class="d-flex gap-2 mb-3 align-items-center">
                <span class="fw-semibold text-muted small">Lọc:</span>
                <form method="GET" class="d-flex gap-2 flex-fill">
                    <select name="department_id" class="form-select form-select-sm" style="max-width:180px" onchange="this.form.submit()">
                        <option value="">Tất cả khoa</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}"
                            {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_name }}
                        </option>
                        @endforeach
                    </select>
                    <select name="room_type" class="form-select form-select-sm" style="max-width:150px" onchange="this.form.submit()">
                        <option value="">Tất cả loại</option>
                        @foreach($roomTypes as $type)
                        <option value="{{ $type }}" {{ request('room_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    <div class="d-flex gap-1 ms-auto small text-muted align-items-center">
                        <span class="d-inline-block" style="width:10px;height:10px;background:#F0F4FF;border:1px solid #90CAF9;border-radius:2px"></span> Đang dùng &nbsp;
                        <span class="d-inline-block" style="width:10px;height:10px;background:#E8F5E9;border:1px solid #a5d6a7;border-radius:2px"></span> Trống &nbsp;
                        <span class="d-inline-block" style="width:10px;height:10px;background:#FFEBEE;border:1px solid #ef9a9a;border-radius:2px"></span> Bảo trì &nbsp;
                        <span class="d-inline-block" style="width:10px;height:10px;background:#FFFDE7;border:1px solid #ffe082;border-radius:2px"></span> Vệ sinh
                    </div>
                </form>
            </div>

            {{-- Lưới phòng khám --}}
            @php
            $roomsByFloor = $rooms->groupBy(function($r) {
            return intdiv((int) filter_var($r->room_code, FILTER_SANITIZE_NUMBER_INT), 100) * 100;
            });
            $statusMap = ['Đang sử dụng'=>'s-using','Trống'=>'s-empty','Bảo trì'=>'s-maintain','Vệ sinh'=>'s-clean'];
            @endphp

            @forelse($roomsByFloor->sortKeys() as $floor => $floorRooms)
            @php $floorLabel = $floor > 0 ? "Tầng " . intdiv($floor,100) : 'Tầng trệt'; @endphp
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="fw-bold text-muted small text-uppercase">{{ $floorLabel }}</span>
                    <hr class="flex-fill m-0" style="border-color:#e0e7ef">
                </div>
                <div class="room-grid">
                    @foreach($floorRooms as $room)
                    @php
                    $cls = $statusMap[$room->status] ?? 's-empty';
                    $todayDoc = $todaySchedules->firstWhere('room_id', $room->room_id);
                    @endphp
                    <div class="room-card {{ $cls }}"
                        onclick="window.location='{{ route('admin.rooms.show', $room) }}'">
                        <div class="room-card-code">{{ $room->room_code }}</div>
                        <div class="room-card-label">{{ $room->status }}</div>
                        @if($todayDoc)
                        <div class="room-card-doc">
                            <i class="bi bi-person-fill me-1"></i>{{ $todayDoc->doctor->full_name ?? '' }}
                        </div>
                        @else
                        <div class="room-card-doc text-muted">+ Chưa phân ca</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="alert alert-info text-center">Không có phòng nào.</div>
            @endforelse

        </div> {{-- Đóng col-lg-8 --}}

        {{-- ========== CỘT PHẢI: Phân bổ hôm nay + Hành động nhanh ========== --}}
        <div class="col-lg-4">
            <div class="mt-0 mt-lg-0">

                {{-- Phân bổ ca hôm nay --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-check text-primary"></i>
                        Phân bổ ca – Hôm nay
                        <span class="badge bg-primary-subtle text-primary ms-auto">
                            {{ now()->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="card-body">
                        @php
                        $caGroups = $todaySchedules->groupBy(function($s) {
                        $h = (int) substr($s->start_time, 0, 2);
                        if ($h < 12) return 'Ca Sáng (07:00 – 12:00)' ;
                            if ($h < 17) return 'Ca Chiều (13:00 – 17:00)' ;
                            return 'Ca Tối (17:00 – 22:00)' ;
                            });
                            @endphp
                            @forelse($caGroups as $caLabel=> $caSchedules)
                            <div class="small text-muted fw-semibold text-uppercase mb-1 mt-2">{{ $caLabel }}</div>
                            @foreach($caSchedules as $s)
                            <div class="sch-row">
                                <div class="sch-dot {{ $s->status==='Hoạt động' ? 'on' : '' }}"></div>
                                <div class="flex-fill">
                                    <div class="fw-semibold small">{{ $s->room->room_code ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:11.5px">{{ $s->doctor->full_name ?? '—' }}</div>
                                </div>
                                <span class="badge {{ $s->status==='Hoạt động' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}" style="font-size:10px">
                                    {{ $s->status }}
                                </span>
                            </div>
                            @endforeach
                            @empty
                            <div class="text-center text-muted py-3 small">
                                <i class="bi bi-calendar-x d-block fs-4 mb-1"></i>
                                Chưa có ca nào hôm nay
                            </div>
                            @endforelse
                    </div>
                    <div class="card-footer p-2">
                        <button class="btn btn-primary w-100 btn-sm"
                            data-bs-toggle="modal" data-bs-target="#modalAssign">
                            <i class="bi bi-person-plus me-1"></i>Phân bổ bác sĩ vào phòng
                        </button>
                    </div>
                </div>

                {{-- Hành động nhanh --}}
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold"><i class="bi bi-bar-chart me-2 text-primary"></i>Hành động nhanh</div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.rooms.schedule.all') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-week text-primary"></i> Xem lịch phân bổ đầy đủ
                            <i class="bi bi-chevron-right ms-auto text-muted"></i>
                        </a>
                        <a href="{{ route('admin.rooms.schedule.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-calendar3 text-primary"></i> Xem lịch theo ngày
                            <i class="bi bi-chevron-right ms-auto text-muted"></i>
                        </a>
                        <a href="{{ route('admin.rooms.schedule.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-plus-circle text-success"></i> Tạo ca làm việc mới
                            <i class="bi bi-chevron-right ms-auto text-muted"></i>
                        </a>
                        <a href="{{ route('admin.rooms.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-door-open text-info"></i> Thêm phòng khám
                            <i class="bi bi-chevron-right ms-auto text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lịch phân ca theo tuần --}}
    <div class="card shadow-sm mt-2">
        <div class="card-header fw-semibold d-flex align-items-center gap-2 flex-wrap">
            <i class="bi bi-calendar3 text-primary"></i>
            <span>Lịch phân bổ theo tuần</span>

            {{-- Dropdown chọn phòng --}}
            <div class="ms-3" style="min-width: 250px;">
                <select id="weeklyRoomSelect" class="form-select form-select-sm" onchange="loadWeeklySchedule()">
                    <option value="">-- Chọn phòng để xem lịch --</option>
                    @foreach($allRooms as $room)
                    <option value="{{ $room->room_id }}" {{ request('weekly_room') == $room->room_id ? 'selected' : '' }}>
                        {{ $room->room_code }} - {{ $room->room_name ?? 'Không tên' }} ({{ $room->room_type }})
                    </option>
                    @endforeach
                </select>
            </div>

            <span class="text-muted small fw-normal ms-1" id="weekRangeLabel">
                ({{ $weekDates->first()->format('d/m') }} – {{ $weekDates->last()->format('d/m/Y') }})
            </span>

            {{-- Nút điều hướng tuần --}}
            <div class="ms-auto d-flex gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeWeek(-1)">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeWeek(1)">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <a href="{{ route('admin.rooms.schedule.all') }}" class="btn btn-sm btn-outline-primary ms-2">
                    Xem chi tiết <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        {{-- Loading indicator --}}
        <div id="weeklyLoading" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-2 text-muted">Đang tải lịch...</p>
        </div>

        {{-- Bảng lịch - sẽ được cập nhật bằng AJAX --}}
        <div id="weeklyScheduleContainer">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                <p>Chọn phòng để xem lịch phân bổ theo tuần</p>
            </div>
        </div>
    </div>
    {{-- MODAL PHÂN BỔ CA --}}
    <div class="modal fade" id="modalAssign" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.rooms.schedule.store') }}" id="assignForm">
                @csrf
                <div class="modal-content modal-assign">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Phân bổ bác sĩ vào phòng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="conflict-alert alert alert-warning d-flex align-items-center gap-2 mb-3"
                            id="conflictAlert">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                            <span id="conflictMsg">Bác sĩ đã được gán phòng khác trong ca này!</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Phòng khám <span class="text-danger">*</span></label>
                                <select name="room_id" id="assign_room" class="form-select" required>
                                    <option value="">-- Chọn phòng --</option>
                                    @foreach($allRooms as $room)
                                    <option value="{{ $room->room_id }}">
                                        {{ $room->room_code }} – {{ $room->room_name ?? '' }} ({{ $room->room_type }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Bác sĩ <span class="text-danger">*</span></label>
                                <select name="doctor_id" id="assign_doctor" class="form-select" required>
                                    <option value="">-- Chọn bác sĩ --</option>
                                    @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->doctor_id }}">
                                        {{ $doctor->full_name }}
                                        ({{ $doctor->department->department_name ?? '' }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ngày làm việc <span class="text-danger">*</span></label>
                                <input type="date" name="work_date" id="assign_date" class="form-control"
                                    value="{{ now()->toDateString() }}"
                                    min="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ca làm việc <span class="text-danger">*</span></label>
                                <select name="_ca" id="assign_ca" class="form-select" required>
                                    <option value="">-- Chọn ca --</option>
                                    <option value="sang">Ca sáng (07:00–12:00)</option>
                                    <option value="chieu">Ca chiều (13:00–17:00)</option>
                                    <option value="toi">Ca tối (17:00–22:00)</option>
                                </select>
                                <input type="hidden" name="start_time" id="assign_start">
                                <input type="hidden" name="end_time" id="assign_end">
                            </div>
                            <input type="hidden" name="slot_duration" value="30">
                            <input type="hidden" name="max_slot" value="8">
                            <input type="hidden" name="status" value="Hoạt động">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Hủy
                        </button>
                        <button type="submit" class="btn btn-primary" id="assignSubmit" disabled>
                            <i class="bi bi-check-lg me-1"></i>Xác nhận phân bổ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Lịch phân ca theo tuần --}}


@push('scripts')
<script>
    const caMap = {
        sang: {
            start: '07:00',
            end: '12:00'
        },
        chieu: {
            start: '13:00',
            end: '17:00'
        },
        toi: {
            start: '17:00',
            end: '22:00'
        },
    };

    function updateCaTime() {
        const ca = document.getElementById('assign_ca').value;
        if (caMap[ca]) {
            document.getElementById('assign_start').value = caMap[ca].start;
            document.getElementById('assign_end').value = caMap[ca].end;
        }
        checkFormReady();
        if (allFilled()) checkConflict();
    }

    function allFilled() {
        return ['assign_room', 'assign_doctor', 'assign_date', 'assign_ca']
            .every(id => document.getElementById(id).value);
    }

    function checkFormReady() {
        document.getElementById('assignSubmit').disabled = !allFilled();
    }

    ['assign_room', 'assign_doctor', 'assign_date'].forEach(id =>
        document.getElementById(id).addEventListener('change', () => {
            checkFormReady();
            if (allFilled()) checkConflict();
        })
    );
    document.getElementById('assign_ca').addEventListener('change', updateCaTime);

    function checkConflict() {
        const doctorId = document.getElementById('assign_doctor').value;
        const workDate = document.getElementById('assign_date').value;
        const ca = document.getElementById('assign_ca').value;
        if (!doctorId || !workDate || !ca) return;

        const {
            start,
            end
        } = caMap[ca];

        fetch('{{ route("admin.rooms.schedule.check-conflict") }}?' + new URLSearchParams({
                doctor_id: doctorId,
                work_date: workDate,
                start_time: start,
                end_time: end,
            }))
            .then(r => r.json())
            .then(data => {
                const alert = document.getElementById('conflictAlert');
                if (data.conflict) {
                    alert.classList.add('show');
                    document.getElementById('conflictMsg').textContent =
                        'Cảnh báo: Bác sĩ đã có lịch trùng trong ca ' + document.getElementById('assign_ca').options[document.getElementById('assign_ca').selectedIndex].text;
                } else {
                    alert.classList.remove('show');
                }
            })
            .catch(() => {});
    }

    document.getElementById('assignForm').addEventListener('submit', function(e) {
        if (!allFilled()) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ: Phòng, Bác sĩ, Ngày và Ca làm việc.');
            return false;
        }
        const ca = document.getElementById('assign_ca').value;
        document.getElementById('assign_start').value = caMap[ca].start;
        document.getElementById('assign_end').value = caMap[ca].end;
    });
</script>

<script>
    let currentWeekStart = '{{ $weekStart->toDateString() }}';

    function loadWeeklySchedule() {
        const roomSelect = document.getElementById('weeklyRoomSelect');
        if (!roomSelect) return;

        const roomId = roomSelect.value;
        const loadingDiv = document.getElementById('weeklyLoading');
        const container = document.getElementById('weeklyScheduleContainer');

        if (!roomId) {
            if (container) {
                container.innerHTML = `<div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                    <p>Chọn phòng để xem lịch phân bổ theo tuần</p>
                </div>`;
            }
            return;
        }

        if (loadingDiv) loadingDiv.style.display = 'block';
        if (container) container.style.opacity = '0.3';

        fetch('/admin/rooms/weekly-ajax?room_id=' + roomId + '&week_start=' + currentWeekStart)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateWeeklyTable(data);
                    const rangeLabel = document.getElementById('weekRangeLabel');
                    if (rangeLabel) rangeLabel.innerHTML = '(' + data.week_start + ' – ' + data.week_end + ')';
                } else {
                    if (container) {
                        container.innerHTML = `<div class="card-body text-center py-5 text-danger">
                            <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                            <p>${data.message || 'Không thể tải dữ liệu'}</p>
                        </div>`;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (container) {
                    container.innerHTML = `<div class="card-body text-center py-5 text-danger">
                        <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                        <p>Có lỗi xảy ra khi tải dữ liệu</p>
                    </div>`;
                }
            })
            .finally(() => {
                if (loadingDiv) loadingDiv.style.display = 'none';
                if (container) container.style.opacity = '1';
            });
    }

    function updateWeeklyTable(data) {
        const timeSlots = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00',
            '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'
        ];
        const weekDates = data.week_dates;
        const schedules = data.schedules;

        let html = `<div class="card-body p-0" style="overflow-x:auto">
                        <div class="week-grid" style="min-width:600px">
                            <div class="week-header" style="border-bottom:2px solid #e0e7ef">Giờ</div>`;

        // Header ngày
        for (let i = 0; i < weekDates.length; i++) {
            const d = weekDates[i];
            html += `<div class="week-header" style="border-bottom:2px solid #e0e7ef">
                            ${d.day}<br>
                            <span style="font-size:11px;font-weight:400">${d.date}</span>
                        </div>`;
        }

        // Các dòng giờ
        for (let t = 0; t < timeSlots.length; t++) {
            const time = timeSlots[t];
            html += `<div class="week-time">${time}</div>`;

            for (let d = 0; d < weekDates.length; d++) {
                const dateKey = weekDates[d].full_date;
                const slot = schedules[dateKey]?.find(s => s.start_time <= time + ':00' && s.end_time > time + ':00');

                if (slot && slot.status === 'Hoạt động') {
                    html += `<div class="week-cell">
                                <span class="week-slot has-dr" title="${slot.doctor_name} - ${slot.room_code}">
                                    <i class="bi bi-person-fill me-1"></i>
                                    ${slot.doctor_name.substring(0, 12)}
                                </span>
                            </div>`;
                } else if (slot && slot.status !== 'Hoạt động') {
                    html += `<div class="week-cell">
                                <span class="week-slot locked">Tạm dừng</span>
                            </div>`;
                } else {
                    html += `<div class="week-cell">
                                <span class="week-slot empty" style="opacity:0.4;">Trống</span>
                            </div>`;
                }
            }
        }

        html += `</div></div>
                 <div class="card-footer bg-white text-muted small">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-3">
                            <span><i class="bi bi-square-fill text-primary me-1" style="font-size:10px;"></i> Có bác sĩ</span>
                            <span><i class="bi bi-square-fill text-success me-1" style="font-size:10px; opacity:0.4;"></i> Trống</span>
                            <span><i class="bi bi-square-fill text-secondary me-1" style="font-size:10px;"></i> Tạm dừng</span>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-door-open me-1"></i> Đang xem: ${data.room_code}
                        </div>
                    </div>
                </div>`;

        const container = document.getElementById('weeklyScheduleContainer');
        if (container) container.innerHTML = html;
    }

    function changeWeek(direction) {
        const roomSelect = document.getElementById('weeklyRoomSelect');
        if (!roomSelect || !roomSelect.value) {
            alert('Vui lòng chọn phòng trước');
            return;
        }

        let date = new Date(currentWeekStart);
        date.setDate(date.getDate() + direction * 7);
        currentWeekStart = date.toISOString().split('T')[0];

        loadWeeklySchedule();
    }

    // Load tự động nếu đã chọn phòng
    if (document.getElementById('weeklyRoomSelect') && document.getElementById('weeklyRoomSelect').value) {
        loadWeeklySchedule();
    }
</script>
@endpush
@endsection