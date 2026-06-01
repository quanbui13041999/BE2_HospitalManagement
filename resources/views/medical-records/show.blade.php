{{-- resources/views/medical-records/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Hồ Sơ Bệnh Án Chi Tiết')

@push('styles')
<style>
    /* ── Root & Reset ─────────────────────────────────────────── */
    :root {
        --primary: #1a6fb3;
        --danger: #e74c3c;
        --success: #27ae60;
        --warning: #f39c12;
        --purple: #8e44ad;
        --gray-bg: #f4f6f9;
        --card-bg: #ffffff;
        --border: #e0e6ed;
        --text: #2c3e50;
        --text-muted: #7f8c9a;
    }

    body {
        background: var(--gray-bg);
        font-family: 'Segoe UI', sans-serif;
        color: var(--text);
    }

    /* ── Top breadcrumb bar ──────────────────────────────────── */
  

    .record-topbar a {
        color: var(--primary);
        text-decoration: none;
    }

    .record-topbar .sep {
        color: #ccc;
    }

    /* ── Allergy warning banner ──────────────────────────────── */
    .allergy-banner {
        background: #fff8f8;
        border: 1px solid #fcc;
        border-left: 4px solid var(--danger);
        border-radius: 6px;
        padding: 10px 16px;
        margin: 12px 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }

    .allergy-banner .icon {
        color: var(--danger);
        font-size: 16px;
    }

    .allergy-banner strong {
        color: var(--danger);
    }

    /* ── Section wrapper ─────────────────────────────────────── */
    .mr-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        padding: 0 20px 20px;
    }

    .mr-grid.full {
        grid-template-columns: 1fr;
    }

    .mr-card {
        background: var(--card-bg);
        border-radius: 10px;
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .mr-card-header {
        background: #fafbfc;
        border-bottom: 1px solid var(--border);
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .mr-card-body {
        padding: 16px;
    }

    /* ── Info General ─────────────────────────────────────────── */
    .info-grid {
        display: grid;
        grid-template-columns: 120px 1fr;
        row-gap: 10px;
        font-size: 13.5px;
    }

    .info-label {
        color: var(--text-muted);
        font-weight: 500;
    }

    .info-val {
        font-weight: 600;
        color: var(--text);
    }

    .info-val.code {
        color: var(--primary);
    }

    .info-val.tag {
        display: inline-block;
        background: #eaf4ff;
        color: var(--primary);
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
    }

    .chief-complaint {
        margin-top: 12px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
        font-size: 13px;
        line-height: 1.6;
        color: #555;
    }

    /* ── Vitals grid ─────────────────────────────────────────── */
    .vitals-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .vital-item {
        background: #f8f9fc;
        border-radius: 8px;
        padding: 12px 14px;
    }

    .vital-label {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .vital-value {
        font-size: 20px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .vital-value.text-danger {
        color: var(--danger);
    }

    .vital-value.text-success {
        color: var(--success);
    }

    .vital-value.text-warning {
        color: var(--warning);
    }

    .vital-value .unit {
        font-size: 12px;
        font-weight: 400;
        color: var(--text-muted);
    }

    .vital-value .status-icon {
        font-size: 13px;
    }

    .chief-complaint-box {
        grid-column: 1/-1;
        background: #f8f9fa;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 13px;
        color: #555;
        line-height: 1.6;
    }

    /* ── Diagnoses ───────────────────────────────────────────── */
    .diagnosis-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .diagnosis-item {
        border-left: 4px solid var(--primary);
        background: #f8f9fa;
        border-radius: 0 6px 6px 0;
        padding: 10px 14px;
    }

    .diagnosis-item.primary {
        border-color: var(--danger);
    }

    .diagnosis-item.secondary {
        border-color: var(--warning);
    }

    .diagnosis-item.complication {
        border-color: var(--purple);
    }

    .diagnosis-name {
        font-weight: 600;
        font-size: 13.5px;
    }

    .icd-badge {
        display: inline-block;
        background: #e8f4ff;
        color: var(--primary);
        padding: 1px 8px;
        border-radius: 10px;
        font-size: 11px;
        margin-top: 3px;
    }

    /* ── 3-col diagnoses ─────────────────────────────────────── */
    .diagnoses-3col {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    /* ── Prescription list ───────────────────────────────────── */
    .rx-list {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .rx-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        transition: background .15s;
    }

    .rx-item:last-child {
        border-bottom: none;
    }

    .rx-item:hover {
        background: #f8faff;
    }

    .rx-name {
        font-weight: 600;
        font-size: 13.5px;
    }

    .rx-dose {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .rx-inst {
        font-size: 12px;
        color: #888;
        margin-top: 1px;
        font-style: italic;
    }

    .rx-days {
        font-size: 12px;
        font-weight: 600;
        color: var(--primary);
        background: #eaf4ff;
        padding: 3px 10px;
        border-radius: 12px;
        white-space: nowrap;
    }

    /* ── Orders / Lab / Imaging ──────────────────────────────── */
    .order-list {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .order-icon {
        font-size: 20px;
        margin-right: 10px;
    }

    .order-name {
        font-weight: 600;
        font-size: 13.5px;
    }

    .order-desc {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .order-status-pending {
        font-size: 12px;
        color: var(--warning);
        font-weight: 600;
        white-space: nowrap;
    }

    .order-status-done {
        font-size: 12px;
        color: var(--success);
        font-weight: 600;
        white-space: nowrap;
    }

    /* ── Attachments ─────────────────────────────────────────── */
    .attachment-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .attachment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background: #f9f9fb;
        border-radius: 6px;
        border: 1px solid #eee;
    }

    .att-icon {
        font-size: 20px;
        margin-right: 10px;
        color: #e74c3c;
    }

    .att-icon.img {
        color: #3498db;
    }

    .att-name {
        font-size: 13px;
        font-weight: 500;
    }

    .att-size {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 1px;
    }

    .att-btn {
        font-size: 12px;
        padding: 4px 12px;
        border-radius: 5px;
        border: 1px solid var(--primary);
        color: var(--primary);
        background: #fff;
        cursor: pointer;
        text-decoration: none;
        transition: all .15s;
    }

    .att-btn:hover {
        background: var(--primary);
        color: #fff;
    }

    .att-btn.danger {
        border-color: var(--danger);
        color: var(--danger);
    }

    .att-btn.danger:hover {
        background: var(--danger);
        color: #fff;
    }

    /* ── Upload zone ─────────────────────────────────────────── */
    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        cursor: pointer;
        color: var(--text-muted);
        font-size: 13px;
        transition: border-color .2s, background .2s;
        margin-top: 10px;
    }
.upload-zone:hover {
    border-color: var(--primary);
    background: #f0f7ff;
}

/* ── New topbar styles ─────────────────────────────────── */



.record-topbar {
    border-bottom: 1px solid #e5e7eb;
    padding: 0 1rem;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 48px;
    gap: 12px;
}

.topbar-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;        /* không xuống dòng */
    gap: 6px;
    font-size: 13px;
    color: #6b7280;
    overflow: hidden;
    min-width: 0;
}
.topbar-breadcrumb a { color: #2563eb; text-decoration: none; font-weight: 500; white-space: nowrap; }
.topbar-breadcrumb span { white-space: nowrap; }
.topbar-breadcrumb .ti { font-size: 12px; color: #d1d5db; flex-shrink: 0; }

.topbar-chip {
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 2px 8px;
    font-size: 12px;
    font-weight: 500;
    color: #111827;
    white-space: nowrap;
}

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;           /* không bị co lại */
}

.topbar-btn-group { display: flex; align-items: center; gap: 6px; }
.topbar-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #374151;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
}
.topbar-btn.btn-back  { border-color: #fcd34d; color: #92400e; background: #fffbeb; }
.topbar-btn.btn-print { color: #6b7280; }
.topbar-btn.btn-edit  { border-color: #93c5fd; color: #1d4ed8; background: #eff6ff; }
.topbar-btn.btn-delete{ border-color: #fca5a5; color: #b91c1c; background: #fef2f2; }
</style>
@endpush

@section('content')

{{-- ── Top breadcrumb ─────────────────────────────────────── --}}
<div class="record-topbar">
    <div class="topbar-breadcrumb">
        <a href="{{ route('medical-records.index') }}">Danh sách Phiếu khám</a>
        <i class="ti ti-chevron-right"></i>
        <span class="topbar-chip">{{ $record->record_code }}</span>
        <i class="ti ti-chevron-right"></i>
        <span>BS. {{ $record->doctor_name }}</span>
        <i class="ti ti-chevron-right"></i>
        <span>{{ $record->exam_date->format('d/m/Y') }}</span>
        <i class="ti ti-chevron-right"></i>
        <a href="{{ route('health.patient.show', $record->patient_id) }}">Tiền sử bệnh án</a>
        <i class="ti ti-chevron-right"></i>
        <a href="{{ route('documents.patient.index', $record->patient_id) }}">Tài liệu y khoa</a>
    </div>

    @php
        $user = Auth::user();
        $canEdit = in_array($user->role_id ?? 0, [1,2])
                || in_array($user->role ?? '', ['admin','doctor']);
    @endphp

    @if($canEdit)
    <div class="topbar-actions">
        <a href="{{ url('/bac-si/lich-hen') }}" class="topbar-btn btn-back">
            <i class="ti ti-calendar-event"></i> Lịch khám
        </a>
        <a href="{{ route('medical-records.print', $record->record_id) }}" target="_blank" class="topbar-btn btn-print">
            <i class="ti ti-printer"></i> In
        </a>
        <a href="{{ route('medical-records.edit', $record->record_id) }}" class="topbar-btn btn-edit">
            <i class="ti ti-edit"></i> Chỉnh sửa
        </a>
        <form action="{{ route('medical-records.destroy', $record->record_id) }}"
            method="POST" onsubmit="return confirm('Xác nhận xóa hồ sơ này?')" style="display:inline;margin:0">
            @csrf @method('DELETE')
            <button type="submit" class="topbar-btn btn-delete">
                <i class="ti ti-trash"></i> Xóa
            </button>
        </form>
    </div>
    @endif
</div>

@if(session('success') || session('warning') || session('error'))
<div style="padding: 12px 20px 0;">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
</div>
@endif

{{-- Page title --}}
<div style="padding: 16px 20px 4px;">
    <h5 style="font-weight:700;color:#222;margin:0;">Hồ Sơ Bệnh Án Chi Tiết</h5>
</div>

{{-- ── Allergy Banner ──────────────────────────────────────── --}}
@if($record->allergies->count())
<div class="allergy-banner mx-0 mb-0" style="margin:0 20px 8px;">
    <span class="icon">⚠️</span>
    <span>
        <strong>Cảnh báo dị ứng:</strong>
        Bệnh nhân dị ứng với
        <strong>{{ $record->allergies->pluck('allergen')->implode(', ') }}</strong>
        — Kiểm tra kỹ trước khi kê đơn
    </span>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- ROW 1: Thông tin chung  |  Kết quả lâm sàng & sinh tồn   --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="mr-grid">

    {{-- Thông tin chung --}}
    <div class="mr-card">
        <div class="mr-card-header">📋 THÔNG TIN CHUNG</div>
        <div class="mr-card-body">
            <div class="info-grid">
                <span class="info-label">MÃ PHIẾU</span>
                <span class="info-val code">{{ $record->record_code }}</span>

                <span class="info-label">BỆNH NHÂN</span>
                <span class="info-val">
                    {{ $record->patient_name }}
                    @if($record->appointment?->user?->dob)
                    — N{{ now()->diffInYears($record->appointment->user->dob) }} tuổi
                    @endif
                </span>

                <span class="info-label">MÃ BN</span>
                <span class="info-val">{{ $record->patient_code ?? '—' }}</span>

                <span class="info-label">NGÀY KHÁM</span>
                <span class="info-val">
                    {{ $record->exam_date->format('d/m/Y') }}
                    @if($record->exam_time)
                    lúc {{ \Carbon\Carbon::parse($record->exam_time)->format('H:i') }}
                    @endif
                </span>

                <span class="info-label">BÁC SĨ</span>
                <span class="info-val">BS. {{ $record->doctor_name }}</span>

                <span class="info-label">LOẠI KHÁM</span>
                <span class="info-val">
                    <span class="info-val tag">{{ $record->visit_type }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Kết quả lâm sàng & chỉ số sinh tồn --}}
    <div class="mr-card">
        <div class="mr-card-header">❤️ KẾT QUẢ LÂM SÀNG &amp; CHỈ SỐ SINH TỒN</div>
        <div class="mr-card-body">
            @if($record->vitalSigns)
            @php $v = $record->vitalSigns; @endphp
            <div class="vitals-grid">

                {{-- Huyết áp --}}
                <div class="vital-item">
                    <div class="vital-label">HUYẾT ÁP</div>
                    <div class="vital-value {{ $v->getStatusClass('bp') }}">
                        {{ $v->blood_pressure ?? '—' }}
                        <span class="unit">mmHg</span>
                        <span class="status-icon">{{ $v->getStatusIcon('bp') }}</span>
                    </div>
                </div>

                {{-- Nhịp tim --}}
                <div class="vital-item">
                    <div class="vital-label">NHỊP TIM</div>
                    <div class="vital-value {{ $v->getStatusClass('hr') }}">
                        {{ $v->heart_rate ?? '—' }}
                        <span class="unit">bpm</span>
                        <span class="status-icon">{{ $v->getStatusIcon('hr') }}</span>
                    </div>
                </div>

                {{-- Nhiệt độ --}}
                <div class="vital-item">
                    <div class="vital-label">NHIỆT ĐỘ</div>
                    <div class="vital-value {{ $v->getStatusClass('temp') }}">
                        {{ $v->temperature ?? '—' }}
                        <span class="unit">°C</span>
                        <span class="status-icon">{{ $v->getStatusIcon('temp') }}</span>
                    </div>
                </div>

                {{-- SpO2 --}}
                <div class="vital-item">
                    <div class="vital-label">SPO2</div>
                    <div class="vital-value {{ $v->getStatusClass('spo2') }}">
                        {{ $v->spo2 ?? '—' }}
                        <span class="unit">%</span>
                        <span class="status-icon">{{ $v->getStatusIcon('spo2') }}</span>
                    </div>
                </div>

                {{-- Cân nặng --}}
                <div class="vital-item">
                    <div class="vital-label">CÂN NẶNG</div>
                    <div class="vital-value text-success">
                        {{ $v->weight ?? '—' }}
                        <span class="unit">kg</span>
                    </div>
                </div>

                {{-- Đường huyết --}}
                <div class="vital-item">
                    <div class="vital-label">ĐƯỜNG HUYẾT</div>
                    <div class="vital-value {{ $v->getStatusClass('sugar') }}">
                        {{ $v->blood_sugar ?? '—' }}
                        <span class="unit">mmol/L</span>
                        <span class="status-icon">{{ $v->getStatusIcon('sugar') }}</span>
                    </div>
                </div>

                {{-- Triệu chứng --}}
                @if($record->chief_complaint)
                <div class="chief-complaint-box">
                    <strong>TRIỆU CHỨNG / LÝ DO ĐẾN KHÁM</strong><br>
                    <span style="color:#555">{{ $record->chief_complaint }}</span>
                </div>
                @endif

            </div>
            @else
            <p class="text-muted" style="font-size:13px">Chưa có chỉ số sinh tồn.</p>
            @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- ROW 2: CHẨN ĐOÁN (full width, 3 columns)                 --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div style="padding: 0 20px 16px;">
    <div class="mr-card">
        <div class="mr-card-header">🩺 CHẨN ĐOÁN</div>
        <div class="mr-card-body">
            @if($record->diagnoses->count())
            <div class="diagnoses-3col">
                @foreach($record->diagnoses as $diag)
                <div class="diagnosis-item {{ $diag->diagnosis_type }}">
                    <div class="diagnosis-name">{{ $diag->diagnosis_name }}</div>
                    @if($diag->icd_code)
                    <div><span class="icd-badge">{{ $diag->icd_code }}</span></div>
                    @endif
                    @if($diag->note)
                    <div style="font-size:12px;color:#888;margin-top:4px">{{ $diag->note }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <p class="text-muted" style="font-size:13px">Chưa có chẩn đoán.</p>
            @endif
        </div>
    </div>
</div>

{{-- ROW 3: Đơn thuốc  |  Chỉ định xét nghiệm / hình ảnh --}}
<div class="mr-grid">

    {{-- Đơn thuốc --}}
    <div class="mr-card">
        <div class="mr-card-header">💊 ĐƠN THUỐC</div>
        <div class="rx-list">
            @forelse($record->prescriptions as $rx)
            <div class="rx-item">
                <div>
                    <div class="rx-name">{{ $rx->drug_name }}</div>
                    <div class="rx-dose">{{ $rx->dosage }}</div>
                    @if($rx->quantity)
                    <div class="rx-dose" style="color:#1a6fb3;">
                        Số lượng: <strong>{{ $rx->quantity }}</strong>
                    </div>
                    @endif
                    @if($rx->instructions)
                    <div class="rx-inst">{{ $rx->instructions }}</div>
                    @endif
                </div>
                <div class="rx-days">{{ $rx->duration_days }} ngày</div>
            </div>
            @empty
            <div class="mr-card-body">
                <p class="text-muted" style="font-size:13px">Chưa có đơn thuốc.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Chỉ định xét nghiệm / hình ảnh --}}
    <div class="mr-card">
        <div class="mr-card-header">🔬 CHỈ ĐỊNH XÉT NGHIỆM / HÌNH ẢNH</div>
        <div class="order-list">
            @php
            $canEdit = in_array(Auth::user()->role_id ?? 0, [1, 2])
            || in_array(Auth::user()->role ?? '', ['admin', 'doctor']);
            @endphp

            @forelse($record->medicalOrders as $order)
            <div class="order-item" data-order-id="{{ $order->order_id }}">
                <div style="display:flex;align-items:flex-start">
                    <span class="order-icon">
                        {{ $order->order_type === 'lab' ? '🔬' : ($order->order_type === 'imaging' ? '🩻' : '📋') }}
                    </span>
                    <div style="flex:1">
                        <div class="order-name">{{ $order->order_name }}</div>
                        <div class="order-desc">{{ $order->description }}</div>

                        {{-- KẾT QUẢ --}}
                        <div style="margin-top:8px" id="result_area_{{ $order->order_id }}">
                            @if($canEdit)
                            @if(empty($order->result_note))
                            <button type="button"
                                class="btn btn-sm btn-warning js-open-result-dropdown"
                                data-order-id="{{ $order->order_id }}"
                                data-record-id="{{ $record->record_id }}"
                                data-note="">
                                ⏳ Chờ kết quả — Click để chọn
                            </button>
                            @else
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <span style="font-size:12px;color:#666">📊 Kết quả:</span>
                                <span style="background:#d4edda;padding:4px 12px;border-radius:20px; font-size:12px;color:#155724;">
                                    {{ $order->result_note }}
                                </span>
                                <button type="button"
                                    class="btn-edit-result btn btn-sm btn-outline-primary js-open-result-dropdown"
                                    data-order-id="{{ $order->order_id }}"
                                    data-record-id="{{ $record->record_id }}"
                                    data-note="{{ $order->result_note }}">
                                    ✏️ Sửa
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger js-delete-result"
                                    data-order-id="{{ $order->order_id }}"
                                    data-record-id="{{ $record->record_id }}">
                                    🗑 Xóa
                                </button>
                            </div>
                            @endif
                            @else
                            <span style="font-size:12px;color:#666">📊 Kết quả:</span>
                            @if(!empty($order->result_note))
                            <span style="background:#d4edda;padding:4px 12px;border-radius:20px; font-size:12px;color:#155724;margin-left:6px">
                                {{ $order->result_note }}
                            </span>
                            @else
                            <span style="font-size:11px;color:#f39c12;margin-left:6px">⏳ Chờ kết quả</span>
                            @endif
                            @endif
                        </div>

                    </div>
                </div>
            </div>
            @empty
            <div class="mr-card-body">
                <p class="text-muted" style="font-size:13px">Chưa có chỉ định.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>{{-- end mr-grid ROW 3 --}}
{{-- ══════════════════════════════════════════════════════════ --}}
{{-- ROW 4: Tập đính kèm (full width)                         --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div style="padding: 0 20px 20px;">
    <div class="mr-card">
        <div class="mr-card-header">📁 TẬP ĐÍNH KÈM</div>
        <div class="mr-card-body">
            <div class="attachment-list" id="attachmentList">
                @forelse($record->attachments as $att)
                <div class="attachment-item" id="att-{{ $att->attachment_id }}">
                    <div style="display:flex;align-items:center">
                        @if($att->isPdf())
                        <span class="att-icon">📄</span>
                        @else
                        <span class="att-icon img">🖼️</span>
                        @endif
                        <div>
                            <div class="att-name">{{ $att->file_name }}</div>
                            <div class="att-size">{{ $att->file_size_formatted }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('medical-records.attachments.view', [$record->record_id, $att->attachment_id]) }}"
                            target="_blank" class="att-btn">⬇ Xem</a>
                        <button class="att-btn danger"
                            data-attachment-id="{{ $att->attachment_id }}">
                            🗑
                        </button>
                    </div>
                </div>
                @empty
                <p class="text-muted" style="font-size:13px">Chưa có tập đính kèm.</p>
                @endforelse
            </div>

            {{-- Upload zone --}}
            <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                <input type="file" id="fileInput" style="display:none" multiple
                    accept=".pdf,.jpg,.jpeg,.png"
                    onchange="handleFileUpload(this)">
                📎 Kéo thả hoặc click để tải lên tập đính kèm
                <div style="font-size:11px;margin-top:4px;color:#bbb">PDF, JPG, JPEG, PNG — tối đa 10MB</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const RECORD_ID = {{ $record->record_id }};
    const UPLOAD_URL = "{{ route('medical-records.attachments.upload', $record->record_id) }}";
    const DELETE_BASE = "{{ url('medical-records/' . $record->record_id . '/attachments') }}";
    const CSRF = "{{ csrf_token() }}";

    function escapeHtmlAttribute(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // Upload files
    async function handleFileUpload(input) {
        const files = Array.from(input.files);
        for (const file of files) {
            const fd = new FormData();
            fd.append('file', file);
            fd.append('_token', CSRF);
            try {
                const res = await fetch(UPLOAD_URL, {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (data.success) appendAttachment(data.attachment);
                else window.showAppNotification('Lỗi: ' + (data.error || 'Không thể upload'), 'error');
            } catch (e) {
                window.showAppNotification('Lỗi khi tải lên: ' + file.name, 'error');
            }
        }
        input.value = '';
    }

    function appendAttachment(att) {
        const icon = att.file_type === 'pdf' ? '📄' : '🖼️';
        const html = `
        <div class="attachment-item" id="att-${att.id}">
            <div style="display:flex;align-items:center">
                <span class="att-icon">${icon}</span>
                <div>
                    <div class="att-name">${att.file_name}</div>
                    <div class="att-size">${att.file_size}</div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="${att.url}" target="_blank" class="att-btn">⬇ Xem</a>
                <button class="att-btn danger" data-attachment-id="${att.id}">🗑</button>
            </div>
        </div>`;
        document.getElementById('attachmentList').insertAdjacentHTML('beforeend', html);
    }

    // Delete attachment
    async function deleteAttachment(recordId, attId) {
        if (!confirm('Xóa tập đính kèm này?')) return;
        const res = await fetch(`${DELETE_BASE}/${attId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF
            },
        });
        const data = await res.json();
        if (data.success) {
            const attachmentItem = document.getElementById(`att-${attId}`);
            if (attachmentItem) attachmentItem.remove();
        }
        else {
            window.showAppNotification(data.error || 'Tập đính kèm đã được người khác xóa trước đó. Trang sẽ được tải lại.', 'warning');
            setTimeout(() => window.location.reload(), 1800);
        }
    }

    document.addEventListener('click', function(event) {
        const attachmentButton = event.target.closest('[data-attachment-id]');
        if (attachmentButton) {
            event.preventDefault();
            deleteAttachment(RECORD_ID, attachmentButton.getAttribute('data-attachment-id'));
            return;
        }

        const openResultButton = event.target.closest('.js-open-result-dropdown');
        if (openResultButton) {
            event.preventDefault();
            showResultDropdown(
                openResultButton.getAttribute('data-order-id'),
                openResultButton.getAttribute('data-record-id'),
                openResultButton.getAttribute('data-note') || ''
            );
            return;
        }

        const deleteResultButton = event.target.closest('.js-delete-result');
        if (deleteResultButton) {
            event.preventDefault();
            deleteResult(
                deleteResultButton.getAttribute('data-order-id'),
                deleteResultButton.getAttribute('data-record-id')
            );
        }
    });

    // Drag & drop upload
    const zone = document.getElementById('uploadZone');
    if (zone) {
        zone.addEventListener('dragover', e => {
            e.preventDefault();
            zone.style.background = '#e8f4ff';
        });
        zone.addEventListener('dragleave', () => {
            zone.style.background = '';
        });
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.style.background = '';
            const dt = e.dataTransfer;
            const input = document.getElementById('fileInput');
            const transfer = new DataTransfer();
            for (const f of dt.files) transfer.items.add(f);
            input.files = transfer.files;
            handleFileUpload(input);
        });
    }

    // ========== XỬ LÝ KẾT QUẢ XÉT NGHIỆM ==========

    const resultOptions = {
        'lab': [{
                value: 'Bình thường',
                label: '✅ Bình thường'
            },
            {
                value: 'Bất thường',
                label: '⚠️ Bất thường'
            },
            {
                value: 'Âm tính',
                label: '🔴 Âm tính (-)'
            },
            {
                value: 'Dương tính',
                label: '🟢 Dương tính (+)'
            },
            {
                value: 'Tăng cao',
                label: '📈 Tăng cao'
            },
            {
                value: 'Giảm thấp',
                label: '📉 Giảm thấp'
            }
        ],
        'imaging': [{
                value: 'Bình thường',
                label: '✅ Bình thường'
            },
            {
                value: 'Bất thường',
                label: '⚠️ Phát hiện bất thường'
            },
            {
                value: 'Cần chụp lại',
                label: '🔄 Cần chụp lại'
            },
            {
                value: 'Có tổn thương',
                label: '🎯 Có tổn thương'
            },
            {
                value: 'Không tổn thương',
                label: '✅ Không tổn thương'
            }
        ],
        'default': [{
                value: 'Bình thường',
                label: '✅ Bình thường'
            },
            {
                value: 'Bất thường',
                label: '⚠️ Bất thường'
            },
            {
                value: 'Đã hoàn thành',
                label: '✔️ Đã hoàn thành'
            }
        ]
    };

    // Render lại khu vực kết quả sau khi lưu thành công (không reload)
    function renderResultSaved(orderId, recordId, resultValue) {
        const area = document.getElementById(`result_area_${orderId}`);
        if (!area) return;
        area.innerHTML = `
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <span style="font-size:12px;color:#666">📊 Kết quả:</span>
            <span style="background:#d4edda;padding:4px 12px;border-radius:20px;font-size:12px;color:#155724;">
                ${resultValue}
            </span>
            <button type="button"
                class="btn btn-sm btn-outline-primary js-open-result-dropdown"
                data-order-id="${orderId}"
                data-record-id="${recordId}"
                data-note="${escapeHtmlAttribute(resultValue)}">
                ✏️ Sửa
            </button>
            <button type="button"
                class="btn btn-sm btn-outline-danger js-delete-result"
                data-order-id="${orderId}"
                data-record-id="${recordId}">
                🗑 Xóa
            </button>
        </div>
    `;
    }

    // Render lại khu vực khi chưa có kết quả
    function renderResultEmpty(orderId, recordId) {
        const area = document.getElementById(`result_area_${orderId}`);
        if (!area) return;
        area.innerHTML = `
        <button type="button"
            class="btn btn-sm btn-warning js-open-result-dropdown"
            data-order-id="${orderId}"
            data-record-id="${recordId}"
            data-note="">
            ⏳ Chờ kết quả — Click để chọn
        </button>
    `;
    }

    // Hiển thị dropdown chọn kết quả
    function showResultDropdown(orderId, recordId, currentValue) {
        const area = document.getElementById(`result_area_${orderId}`);
        if (!area) return;

        const orderItem = area.closest('.order-item');
        const orderIconElement = orderItem ? orderItem.querySelector('.order-icon') : null;
        const orderIcon = orderIconElement ? orderIconElement.innerText : '';
        let orderType = 'default';
        if (orderIcon.includes('🔬')) orderType = 'lab';
        else if (orderIcon.includes('🩻')) orderType = 'imaging';

        const options = resultOptions[orderType] || resultOptions.default;

        let optionsHtml = '';
        // Chỉ thêm option placeholder khi chưa có giá trị
        if (!currentValue) {
            optionsHtml += `<option value="">-- Chọn kết quả --</option>`;
        }
        options.forEach(opt => {
            const selected = (currentValue === opt.value) ? 'selected' : '';
            optionsHtml += `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
        });
        optionsHtml += `<option value="other">✏️ Nhập kết quả khác...</option>`;

        area.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size:12px; color:#666;">📊 Kết quả:</span>
            <select id="result_select_${orderId}"
                    class="form-select form-select-sm"
                    style="width: 220px; display: inline-block;">
                ${optionsHtml}
            </select>
            <button type="button" class="btn btn-sm btn-success"
                    onclick="saveResultFromSelect(${orderId}, ${recordId}, '${currentValue.replace(/'/g, "\\'")}')">
                💾 Lưu
            </button>
            <button type="button" class="btn btn-sm btn-secondary"
                    onclick="cancelResult(${orderId}, ${recordId}, '${currentValue.replace(/'/g, "\\'")}')">
                Hủy
            </button>
        </div>
    `;

        const select = document.getElementById(`result_select_${orderId}`);
        if (select) {
            select.addEventListener('change', function() {
                if (this.value === 'other') {
                    showManualInput(orderId, recordId, currentValue);
                }
            });
        }
    }

    // Hiển thị input nhập tay
    function showManualInput(orderId, recordId, currentValue) {
        const area = document.getElementById(`result_area_${orderId}`);
        if (!area) return;

        const escaped = (currentValue || '').replace(/'/g, "\\'");

        area.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size:12px; color:#666;">📊 Kết quả:</span>
            <input type="text" id="result_input_${orderId}"
                class="form-control form-control-sm"
                style="width: 250px; display: inline-block;"
                placeholder="Nhập kết quả xét nghiệm..."
                value="${currentValue || ''}">
            <button type="button" class="btn btn-sm btn-success"
                    onclick="saveResultFromInput(${orderId}, ${recordId}, '${escaped}')">
                💾 Lưu
            </button>
            <button type="button" class="btn btn-sm btn-secondary"
                    onclick="cancelResult(${orderId}, ${recordId}, '${escaped}')">
                🔙 Quay lại
            </button>
        </div>
    `;

        const resultInput = document.getElementById(`result_input_${orderId}`);
        if (resultInput) resultInput.focus();
    }

    // Lưu từ dropdown select
    function saveResultFromSelect(orderId, recordId, originalValue) {
        const select = document.getElementById(`result_select_${orderId}`);
        if (!select) return;

        const result = select.value;
        if (!result || result === 'other') {
            // Highlight select thay vì alert
            select.style.borderColor = 'red';
            select.focus();
            return;
        }
        select.style.borderColor = '';
        saveResultData(orderId, recordId, result, originalValue);
    }

    // Lưu từ input nhập tay
    function saveResultFromInput(orderId, recordId, originalValue) {
        const input = document.getElementById(`result_input_${orderId}`);
        if (!input) return;

        const result = input.value.trim();
        if (!result) {
            input.style.borderColor = 'red';
            input.focus();
            return;
        }
        input.style.borderColor = '';
        saveResultData(orderId, recordId, result, originalValue);
    }

    // Gửi API lưu kết quả
    async function saveResultData(orderId, recordId, result, originalValue) {
        const url = `/medical-records/${recordId}/orders/${orderId}/result`;

        // Hiển thị trạng thái đang lưu
        const area = document.getElementById(`result_area_${orderId}`);
        if (area) {
            const saveBtn = area.querySelector('.btn-success');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = '⏳ Đang lưu...';
            }
        }

        try {
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    result: result
                })
            });

            const data = await response.json();

            if (data.success) {
                // ✅ Lưu thành công: render lại UI, không alert, không reload
                renderResultSaved(orderId, recordId, result);
            } else {
                // ❌ Thất bại: hiện thông báo nhỏ inline, không alert
                if (area) {
                    const saveBtn = area.querySelector('.btn-success');
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.textContent = '💾 Lưu';
                    }
                    // Hiện lỗi inline
                    let errEl = area.querySelector('.save-error');
                    if (!errEl) {
                        errEl = document.createElement('span');
                        errEl.className = 'save-error';
                        errEl.style.cssText = 'color:red;font-size:12px;';
                        area.querySelector('div').appendChild(errEl);
                    }
                    errEl.textContent = '❌ ' + (data.error || 'Không thể lưu');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            if (area) {
                const saveBtn = area.querySelector('.btn-success');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = '💾 Lưu';
                }
            }
        }
    }

    // Hủy: khôi phục về trạng thái ban đầu (không reload)
    function cancelResult(orderId, recordId, originalValue) {
        if (originalValue) {
            // Có kết quả cũ → render lại badge + nút Sửa/Xóa
            renderResultSaved(orderId, recordId, originalValue);
        } else {
            // Chưa có kết quả → render lại nút "Chờ kết quả"
            renderResultEmpty(orderId, recordId);
        }
    }

    // Xóa kết quả
    async function deleteResult(orderId, recordId) {
        if (!confirm('Bạn có chắc muốn xóa kết quả này?')) return;

        const url = `/medical-records/${recordId}/orders/${orderId}/result`;

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                renderResultEmpty(orderId, recordId);
            } else {
                console.error('Xóa thất bại:', data.error);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
</script>
@endpush
