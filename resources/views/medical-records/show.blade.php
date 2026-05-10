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
    .record-topbar {
        background: #fff;
        border-bottom: 1px solid var(--border);
        padding: 10px 24px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-muted);
    }

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
</style>
@endpush

@section('content')

{{-- ── Top breadcrumb ─────────────────────────────────────── --}}
<div class="record-topbar">
    <a href="{{ route('medical-records.index') }}">hồ_sơ_bệnh_án.info</a>
    <span class="sep">—</span>
    <span>Phiếu khám: {{ $record->record_code }}</span>
    <span class="sep">—</span>
    <span>BS. {{ $record->doctor_name }}</span>
    <span class="sep">—</span>
    <span>{{ $record->exam_date->format('d/m/Y') }}</span>

     {{-- Chỉ hiển thị nút [In], [Sửa], [Xóa] khi là Admin hoặc Doctor --}}
    @php
        $user = Auth::user();
        $isAdmin = ($user->role_id == 1 || $user->role == 'admin');
        $isDoctor = ($user->role_id == 2 || $user->role == 'doctor');
        $canEdit = ($isAdmin || $isDoctor);
    @endphp

    @if($canEdit)
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('medical-records.print', $record->record_id) }}"
            target="_blank" class="btn btn-sm btn-outline-secondary">
            🖨️ In
        </a>
        <a href="{{ route('medical-records.edit', $record->record_id) }}"
            class="btn btn-sm btn-outline-primary">
            ✏️ Chỉnh sửa
        </a>
        <form action="{{ route('medical-records.destroy', $record->record_id) }}"
            method="POST" onsubmit="return confirm('Xác nhận xóa hồ sơ này?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">🗑️ Xóa</button>
        </form>
    </div>
    @endif
</div>

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
                                    {{-- Chưa có → nút vàng --}}
                                    <button type="button"
                                        onclick="showResultInput({{ $order->order_id }}, {{ $record->record_id }}, '')"
                                        style="padding:3px 14px;border:1px dashed #f39c12;border-radius:20px;
                                               background:#fffbf0;color:#f39c12;font-size:12px;cursor:pointer">
                                        ⏳ Chờ kết quả — Click để thêm
                                    </button>
                                @else
                                    {{-- Đã có → hiển thị + nút sửa --}}
                                    <span style="font-size:12px;color:#666">📊 Kết quả:</span>
                                    <span style="background:#e8f5e9;padding:3px 10px;border-radius:20px;
                                                 font-size:12px;color:#27ae60;margin:0 6px">
                                        {{ $order->result_note }}
                                    </span>
                                    <button type="button"
                                        onclick="showResultInput({{ $order->order_id }}, {{ $record->record_id }}, '{{ addslashes($order->result_note) }}')"
                                        style="padding:2px 8px;border:1px solid #ddd;border-radius:15px;
                                               background:none;font-size:11px;cursor:pointer">
                                        ✏️ Sửa
                                    </button>
                                @endif
                            @else
                                {{-- Bệnh nhân chỉ xem --}}
                                <span style="font-size:12px;color:#666">📊 Kết quả:</span>
                                @if(!empty($order->result_note))
                                    <span style="background:#e8f5e9;padding:3px 10px;border-radius:20px;
                                                 font-size:12px;color:#27ae60;margin-left:6px">
                                        {{ $order->result_note }}
                                    </span>
                                @else
                                    <span style="font-size:11px;color:#f39c12;margin-left:6px">⏳ Chờ kết quả</span>
                                @endif
                            @endif
                        </div>
                        {{-- end kết quả --}}

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
                        <a href="{{ asset('storage/' . $att->file_path) }}"
                            target="_blank" class="att-btn">⬇ Xem</a>
                        <button class="att-btn danger"
                            onclick="deleteAttachment({{ $record->record_id }}, {{ $att->attachment_id }})">
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
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                    onchange="handleFileUpload(this)">
                📎 Kéo thả hoặc click để tải lên tập đính kèm
                <div style="font-size:11px;margin-top:4px;color:#bbb">PDF, JPG, PNG, DOC — tối đa 10MB</div>
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
            } catch (e) {
                alert('Lỗi khi tải lên: ' + file.name);
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
                <button class="att-btn danger" onclick="deleteAttachment(${RECORD_ID}, ${att.id})">🗑</button>
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
        if (data.success) document.getElementById(`att-${attId}`)?.remove();
    }

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
    
   // ── Thêm / Sửa kết quả xét nghiệm ───────────────────────────────
function showResultInput(orderId, recordId, currentValue) {
    const area = document.getElementById(`result_area_${orderId}`);
    area.innerHTML = `
        <span style="font-size:12px;color:#666">📊 Kết quả:</span>
        <input type="text" id="ri_${orderId}" class="form-control form-control-sm"
               style="width:220px;display:inline-block;margin:0 6px"
               placeholder="Nhập kết quả..." value="${currentValue}">
        <button class="btn btn-sm btn-success" onclick="saveResult(${orderId},${recordId})">💾 Lưu</button>
        <button class="btn btn-sm btn-secondary" onclick="location.reload()">Hủy</button>
    `;
    document.getElementById(`ri_${orderId}`)?.focus();
}


    // Hiển thị input để sửa kết quả
    function editResult(button, orderId, recordId, currentResult) {
        const container = button.parentElement;
        // Escape quotes để tránh lỗi JavaScript
        const escapedResult = currentResult.replace(/['"]/g, '\\"');
        
        const inputHtml = `
            <span style="font-size:12px; color:#666;">📊 Kết quả:</span>
            <input type="text" id="result_input_${orderId}" class="form-control form-control-sm" 
                   style="width:200px; display:inline-block; margin:0 8px;" 
                   value="${escapedResult}">
            <button type="button" class="btn btn-sm btn-success" onclick="saveResult(${orderId}, ${recordId})">💾 Lưu</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="cancelResult(this, ${orderId})">Hủy</button>
        `;
        
        container.innerHTML = inputHtml;
        document.getElementById(`result_input_${orderId}`).focus();
    }

   async function saveResult(orderId, recordId) {
    const input  = document.getElementById(`ri_${orderId}`);
    const result = input?.value.trim();
    if (!result) { alert('Vui lòng nhập kết quả!'); return; }

    try {
        const res  = await fetch(`/medical-records/${recordId}/orders/${orderId}/result`, {
            method : 'PUT',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
            body   : JSON.stringify({ result })
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Lỗi: ' + (data.error || 'Không thể lưu'));
    } catch (err) {
        alert('Có lỗi: ' + err.message);
    }
}
</script>
@endpush