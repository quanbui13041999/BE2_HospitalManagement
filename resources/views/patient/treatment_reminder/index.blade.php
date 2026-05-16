@extends('layouts.user')

@section('title', 'Hệ Thống Nhắc Nhở Tuân Thủ Điều Trị')

@push('styles')
<style>
    /* ── VARIABLES ── */
    :root {
        --primary:   #1a6b4a;
        --success:   #22a06b;
        --danger:    #e53935;
        --warning:   #f59e0b;
        --gray-100:  #f4f6f9;
        --gray-200:  #e8ecf0;
        --card-radius: 12px;
    }

    /* ── STAT CARDS (4 thẻ trên cùng) ── */
    .stat-card { background:#fff; border-radius:var(--card-radius); padding:20px 24px; border:1px solid var(--gray-200); height: 100%; }
    .stat-card .label { font-size:12px; color:#6b7280; margin-bottom:6px; }
    .stat-card .value { font-size:32px; font-weight:700; color:#111827; }
    .stat-card .value.green { color: var(--success); }
    .stat-card .value.orange { color: var(--warning); }

    /* ── SECTION HEADER ── */
    .section-header { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600;
        color:#374151; border-left:3px solid var(--primary); padding-left:10px; margin-bottom:16px; }

    /* ── MEDICINE ROW ── */
    .medicine-row { display:flex; align-items:center; justify-content:space-between;
        padding:14px 18px; border-radius:8px; margin-bottom:10px; background:#f9fafb;
        border:1px solid var(--gray-200); transition: background .2s; }
    .medicine-row.confirmed    { background:#f0fdf4; border-color:#bbf7d0; }
    .medicine-row.danger-alert { background:#fff5f5; border-color:#fca5a5; }
    .medicine-row .time-badge  { font-size:13px; font-weight:700; color:var(--primary); min-width:50px; }
    .medicine-row .drug-info h6 { margin:0; font-size:14px; font-weight:600; }
    .medicine-row .drug-info p  { margin:0; font-size:12px; color:#6b7280; }

    /* ── CONFIRM BUTTON ── */
    .btn-confirm     { background:var(--success); color:#fff; border:none; border-radius:6px;
        padding:6px 16px; font-size:13px; font-weight:600; cursor:pointer; transition:.2s; }
    .btn-confirm:hover { opacity:.85; }
    .btn-danger-label { background:var(--danger); color:#fff; border-radius:6px;
        padding:6px 16px; font-size:13px; font-weight:600; cursor:default; }

    /* ── INSTRUCTION CARD ── */
    .instruction-card { display:flex; align-items:center; justify-content:space-between;
        padding:12px 16px; border-radius:8px; background:#f9fafb; border:1px solid var(--gray-200);
        margin-bottom:8px; }
    .instruction-card .icon-wrap { width:36px; height:36px; border-radius:50%; background:var(--gray-100);
        display:flex; align-items:center; justify-content:center; margin-right:12px; }

    /* ── WEEKLY BAR CHART ── */
    .weekly-bar { display:flex; gap:6px; align-items:flex-end; height:60px; }
    .weekly-bar .bar { flex:1; border-radius:4px 4px 0 0; min-height:8px;
        background: var(--primary); opacity:.4; transition:.3s; }
    .weekly-bar .bar.full  { opacity:1; }
    .weekly-bar .bar.empty { background: var(--gray-200); opacity:1; }

    /* ── REPORT CARDS ── */
    .report-metric { text-align:center; padding:20px; background:#fff;
        border-radius:var(--card-radius); border:1px solid var(--gray-200); }
    .report-metric .pct { font-size:28px; font-weight:800; }
    .report-metric .desc{ font-size:12px; color:#6b7280; margin-top:4px; }
    .pct.green  { color: var(--success); }
    .pct.blue   { color: #3b82f6; }
    .pct.purple { color: #8b5cf6; }
    .pct.orange { color: var(--warning); }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- PAGE TITLE --}}
    <div class="mb-3">
        <h4 class="fw-bold mb-0">Hệ Thống Nhắc Nhở Tuân Thủ Điều Trị</h4>
        <small class="text-muted">Lịch uống thuốc, hướng dẫn điều trị và báo cáo tuân thủ</small>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label">Tuân thủ tháng này</div>
                <div class="value green">{{ $monthStats['compliance_rate'] }}%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label">Nhắc nhở hôm nay</div>
                <div class="value">{{ $monthStats['reminders_today'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label">Đã hoàn thành</div>
                <div class="value">{{ $monthStats['completed_today'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label">Ngày còn lại trong tháng</div>
                <div class="value orange">{{ $monthStats['days_left_in_month'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ── CỘT TRÁI: LỊCH UỐNG THUỐC + BÁO CÁO ── --}}
        <div class="col-lg-7">

            {{-- Lịch uống thuốc hôm nay --}}
            <div class="card shadow-sm mb-4" style="border-radius:var(--card-radius)">
                <div class="card-body p-4">
                    <div class="section-header">
                        💊 LỊCH UỐNG THUỐC HÔM NAY — {{ now()->format('d/m/Y') }}
                    </div>

                    @forelse($todayReminders as $reminder)
                        <div class="medicine-row {{ $reminder->isConfirmed() ? 'confirmed' : '' }} {{ $reminder->isDangerous() ? 'danger-alert' : '' }}"
                             id="reminder-{{ $reminder->reminder_id }}">

                            <div class="d-flex align-items-center gap-3">
                                <span class="time-badge">{{ $reminder->time_label }}</span>
                                <div class="drug-info">
                                    @if($reminder->isDangerous())
                                        <h6 class="text-danger">{{ $reminder->message }}</h6>
                                        <p class="text-danger">⚠️ Chú ý: Kiểm tra chống chỉ định trước khi dùng</p>
                                    @else
                                        <h6>{{ Str::before($reminder->message, '—') }}</h6>
                                        <p>{{ Str::after($reminder->message, '—') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div>
                                @if($reminder->isDangerous())
                                    <span class="btn-danger-label">⚠ NGUY HIỂM</span>
                                @elseif($reminder->isConfirmed())
                                    <span class="btn-confirm" style="background:#d1fae5;color:var(--success);">✓ Đã uống</span>
                                @else
                                    <button class="btn-confirm"
                                            data-reminder-id="{{ $reminder->reminder_id }}"
                                            onclick="confirmReminder(this)">
                                        Đánh dấu đã uống
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">Không có lịch uống thuốc hôm nay.</p>
                    @endforelse
                </div>
            </div>

            {{-- Báo cáo tuân thủ tháng --}}
            @include('patient.treatment_reminder.partials._compliance_report')
        </div>

        {{-- ── CỘT PHẢI: HƯỚNG DẪN + BIỂU ĐỒ ── --}}
        <div class="col-lg-5">

            {{-- Hướng dẫn điều trị tại nhà --}}
            <div class="card shadow-sm mb-4" style="border-radius:var(--card-radius)">
                <div class="card-body p-4">
                    <div class="section-header">🏠 HƯỚNG DẪN ĐIỀU TRỊ TẠI NHÀ</div>

                    @forelse($instructions as $instruction)
                        <div class="instruction-card">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrap">
                                    <i data-feather="{{ $instruction->icon ?? 'activity' }}" style="width:16px"></i>
                                </div>
                                <div>
                                    <div style="font-size:14px;font-weight:600">{{ $instruction->instruction_text }}</div>
                                    <div style="font-size:12px;color:#6b7280">{{ $instruction->detail }}</div>
                                </div>
                            </div>
                            <input type="checkbox"
                                   class="form-check-input instruction-check"
                                   style="width:18px;height:18px;cursor:pointer"
                                   data-id="{{ $instruction->id }}"
                                   {{ $instruction->isCheckedToday() ? 'checked' : '' }}>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">Chưa có hướng dẫn điều trị.</p>
                    @endforelse
                </div>
            </div>

            {{-- Biểu đồ tuân thủ 7 ngày --}}
            @include('patient.treatment_reminder.partials._weekly_chart')
        </div>
    </div>

    {{-- Ghi chú tái khám --}}
    @if($nextAppointment)
    <div class="alert alert-info mt-2" style="border-radius:8px; font-size:13px;">
        ℹ️ Báo cáo này sẽ được gửi tự động cho bác sĩ của bạn trước buổi tái khám ngày
        <strong>{{ \Carbon\Carbon::parse($nextAppointment->appointment_time)->format('d/m/Y') }}</strong>.
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/feather-icons"></script>
<script>
// Xác nhận đã uống thuốc
async function confirmReminder(btn) {
    const id = btn.dataset.reminderId;
    btn.disabled = true;
    btn.textContent = 'Đang xử lý...';
    try {
        const res  = await fetch(`/treatment/confirm/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            btn.textContent = '✓ Đã uống';
            btn.style.background = '#d1fae5';
            btn.style.color = 'var(--success)';
            btn.closest('.medicine-row').classList.add('confirmed');
            
            // Reload page or update stats via AJAX if needed
        }
    } catch(e) { 
        btn.disabled = false; 
        btn.textContent = 'Đánh dấu đã uống'; 
    }
}

// Toggle hướng dẫn tại nhà
document.querySelectorAll('.instruction-check').forEach(cb => {
    cb.addEventListener('change', async function() {
        try {
            const res = await fetch('/treatment/instruction/toggle', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                body: JSON.stringify({ instruction_id: this.dataset.id })
            });
            const data = await res.json();
            if (!data.success) this.checked = !this.checked; // rollback nếu lỗi
        } catch(e) {
            this.checked = !this.checked;
        }
    });
});

// Init Feather icons
feather.replace();
</script>
@endpush
