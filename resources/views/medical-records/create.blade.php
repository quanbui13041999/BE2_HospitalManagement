{{-- resources/views/medical-records/create.blade.php --}}
@extends('layouts.app')
@section('title', isset($record) ? 'Chỉnh sửa hồ sơ bệnh án' : 'Tạo hồ sơ bệnh án')

@push('styles')
<style>
    .form-section {
        background: #fff;
        border: 1px solid #e0e6ed;
        border-radius: 10px;
        margin-bottom: 16px;
        overflow: hidden;
    }

    .form-section-header {
        background: #fafbfc;
        border-bottom: 1px solid #e0e6ed;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        color: #1a6fb3;
    }

    .form-section-body {
        padding: 16px;
    }

    .add-row-btn {
        background: none;
        border: 1px dashed #ccc;
        border-radius: 6px;
        padding: 8px 16px;
        font-size: 12px;
        color: #888;
        cursor: pointer;
        width: 100%;
        margin-top: 8px;
        transition: .15s;
    }

    .add-row-btn:hover {
        border-color: #1a6fb3;
        color: #1a6fb3;
        background: #f0f7ff;
    }

    .row-delete-btn {
        background: none;
        border: none;
        color: #e74c3c;
        cursor: pointer;
        font-size: 16px;
        padding: 0 4px;
    }

    .diagnosis-row,
    .rx-row,
    .order-row,
    .allergy-row {
        background: #f9f9fb;
        border-radius: 6px;
        padding: 10px 12px;
        margin-bottom: 6px;
        border: 1px solid #eee;
    }

    .is-invalid {
        border-color: #dc3545 !important;
        background-color: #fff8f8 !important;
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .diagnosis-row:has(.is-invalid),
    .allergy-row:has(.is-invalid),
    .rx-row:has(.is-invalid),
    .order-row:has(.is-invalid) {
        border: 1px solid #dc3545;
        background-color: #fff8f8;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .form-label .text-danger {
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<div style="max-width:1100px;margin:20px auto;padding:0 16px">

    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('medical-records.index') }}" class="btn btn-sm btn-outline-secondary me-3">← Quay lại</a>
        <h5 class="mb-0 fw-bold">
            {{ isset($record) ? 'Chỉnh sửa hồ sơ: ' . $record->record_code : 'Tạo Hồ Sơ Bệnh Án Mới' }}
        </h5>
    </div>

    <form action="{{ isset($record) ? route('medical-records.update', $record->record_id) : route('medical-records.store') }}"
        method="POST" enctype="multipart/form-data" id="medicalRecordForm">
        @csrf
        @if(isset($record)) @method('PUT') @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <strong>⚠️ Vui lòng kiểm tra lại:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ── Thông tin chung ──────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">📋 Thông tin chung</div>
            <div class="form-section-body">
                <div class="row g-3">

                    {{-- appointment_id hidden — PHẢI ĐẶT TRONG row --}}
                    @if(isset($appointment) && $appointment)
                    <input type="hidden" name="appointment_id" value="{{ $appointment->appointment_id }}">
                    @endif

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bệnh nhân <span class="text-danger">*</span></label>
                        <input type="text" name="patient_name" class="form-control"
                            value="{{ old('patient_name', $record->patient_name ?? $appointment?->user?->full_name) }}"
                            required>
                        <input type="hidden" name="patient_id"
                            value="{{ old('patient_id', $record->patient_id ?? $appointment?->user_id) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mã bệnh nhân</label>
                        <input type="text" name="patient_code" class="form-control"
                            value="{{ old('patient_code', $record->patient_code ?? '') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Ngày khám <span class="text-danger">*</span></label>
                        <input type="date" name="exam_date" class="form-control"
                            value="{{ old('exam_date', isset($record) ? $record->exam_date->format('Y-m-d') : ($appointment?->appointment_time?->format('Y-m-d') ?? today()->format('Y-m-d'))) }}"
                            required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Giờ khám</label>
                        <input type="time" name="exam_time" class="form-control"
                            value="{{ old('exam_time', isset($record) ? \Carbon\Carbon::parse($record->exam_time)->format('H:i') : ($appointment?->appointment_time?->format('H:i') ?? now()->format('H:i'))) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Bác sĩ <span class="text-danger">*</span></label>
                        {{-- DoctorSchedule::doctor() trả về User, dùng ->full_name --}}
                        <input type="text" name="doctor_name" class="form-control"
                            value="{{ old('doctor_name',
                                $record->doctor_name
                                ?? $appointment?->schedule?->doctor?->full_name
                                ?? Auth::user()->full_name
                            ) }}" required>
                        <input type="hidden" name="doctor_id"
                            value="{{ old('doctor_id', $record->doctor_id ?? $appointment?->schedule?->doctor?->user_id ?? Auth::id()) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Loại khám</label>
                        <select name="visit_type" class="form-select">
                            @foreach(['Khám mới', 'Tái khám', 'Cấp cứu'] as $type)
                            <option value="{{ $type }}"
                                {{ old('visit_type', $record->visit_type ?? 'Khám mới') === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Lý do đến khám / Triệu chứng</label>
                        {{-- Lấy từ note của appointment khi đặt lịch --}}
                        <textarea name="chief_complaint" class="form-control" rows="2">{{
                            old('chief_complaint',
                                $record->chief_complaint
                                ?? $appointment?->note
                            )
                        }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Chỉ số sinh tồn ──────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">❤️ Chỉ số sinh tồn</div>
            <div class="form-section-body">
                <div class="row g-3">
                    @php $v = $record->vitalSigns ?? null; @endphp

                    <div class="col-md-4">
                        <label class="form-label">Huyết áp (mmHg) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="vitals[blood_pressure]" class="form-control"
                                placeholder="VD: 130/80"
                                value="{{ old('vitals.blood_pressure', $v?->blood_pressure) }}">
                            <select name="vitals[bp_status]" class="form-select" style="max-width:90px">
                                <option value="normal" {{ ($v?->bp_status ?? 'normal') === 'normal' ? 'selected' : '' }}>✓</option>
                                <option value="high" {{ ($v?->bp_status) === 'high'   ? 'selected' : '' }}>▲ Cao</option>
                                <option value="low" {{ ($v?->bp_status) === 'low'    ? 'selected' : '' }}>▼ Thấp</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nhịp tim (bpm) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="vitals[heart_rate]" class="form-control" step="0.1"
                                value="{{ old('vitals.heart_rate', $v?->heart_rate) }}">
                            <select name="vitals[hr_status]" class="form-select" style="max-width:90px">
                                <option value="normal" {{ ($v?->hr_status ?? 'normal') === 'normal' ? 'selected' : '' }}>✓</option>
                                <option value="high" {{ ($v?->hr_status) === 'high' ? 'selected' : '' }}>▲</option>
                                <option value="low" {{ ($v?->hr_status) === 'low'  ? 'selected' : '' }}>▼</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nhiệt độ (°C) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="vitals[temperature]" class="form-control" step="0.1"
                                value="{{ old('vitals.temperature', $v?->temperature) }}">
                            <select name="vitals[temp_status]" class="form-select" style="max-width:90px">
                                <option value="normal" {{ ($v?->temp_status ?? 'normal') === 'normal' ? 'selected' : '' }}>✓</option>
                                <option value="high" {{ ($v?->temp_status) === 'high' ? 'selected' : '' }}>▲</option>
                                <option value="low" {{ ($v?->temp_status) === 'low'  ? 'selected' : '' }}>▼</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">SpO2 (%) <span class="text-danger">*</span></label>
                        <input type="number" name="vitals[spo2]" class="form-control" step="0.1"
                            value="{{ old('vitals.spo2', $v?->spo2) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Cân nặng (kg) <span class="text-danger">*</span></label>
                        <input type="number" name="vitals[weight]" class="form-control" step="0.1"
                            value="{{ old('vitals.weight', $v?->weight) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Đường huyết (mmol/L)</label>
                        <div class="input-group">
                            <input type="number" name="vitals[blood_sugar]" class="form-control" step="0.01"
                                value="{{ old('vitals.blood_sugar', $v?->blood_sugar) }}">
                            <select name="vitals[sugar_status]" class="form-select" style="max-width:90px">
                                <option value="normal" {{ ($v?->sugar_status ?? 'normal') === 'normal' ? 'selected' : '' }}>✓</option>
                                <option value="high" {{ ($v?->sugar_status) === 'high' ? 'selected' : '' }}>▲</option>
                                <option value="low" {{ ($v?->sugar_status) === 'low'  ? 'selected' : '' }}>▼</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Dị ứng ───────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">⚠️ Dị ứng</div>
            <div class="form-section-body">
                <div id="allergyContainer">
                    @forelse($record->allergies ?? [] as $i => $allergy)
                    <div class="allergy-row">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <input type="text" name="allergies[{{ $i }}][allergen]" class="form-control"
                                    placeholder="Tên chất gây dị ứng" value="{{ $allergy->allergen }}">
                            </div>
                            <div class="col-md-3">
                                <select name="allergies[{{ $i }}][severity]" class="form-select">
                                    <option value="">Mức độ</option>
                                    @foreach(['Nhẹ','Vừa','Nặng'] as $s)
                                    <option value="{{ $s }}" {{ $allergy->severity === $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="allergies[{{ $i }}][reaction]" class="form-control"
                                    placeholder="Phản ứng" value="{{ $allergy->reaction }}">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="row-delete-btn" onclick="this.closest('.allergy-row').remove()">✕</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
                <button type="button" class="add-row-btn" onclick="addAllergy()">+ Thêm dị ứng</button>
            </div>
        </div>

        {{-- ── Chẩn đoán ────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">🩺 Chẩn đoán <span class="text-danger">*</span></div>
            <div class="form-section-body">
                <div id="diagnosisContainer">
                    @forelse($record->diagnoses ?? [] as $i => $diag)
                    <div class="diagnosis-row">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" name="diagnoses[{{ $i }}][diagnosis_name]" class="form-control"
                                    placeholder="Tên chẩn đoán *" value="{{ $diag->diagnosis_name }}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="diagnoses[{{ $i }}][icd_code]" class="form-control"
                                    placeholder="Mã ICD" value="{{ $diag->icd_code }}">
                            </div>
                            <div class="col-md-3">
                                <select name="diagnoses[{{ $i }}][diagnosis_type]" class="form-select">
                                    <option value="primary" {{ $diag->diagnosis_type === 'primary'      ? 'selected' : '' }}>Chính</option>
                                    <option value="secondary" {{ $diag->diagnosis_type === 'secondary'    ? 'selected' : '' }}>Phụ</option>
                                    <option value="complication" {{ $diag->diagnosis_type === 'complication' ? 'selected' : '' }}>Biến chứng</option>
                                </select>
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="row-delete-btn" onclick="this.closest('.diagnosis-row').remove()">✕</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
                <button type="button" class="add-row-btn" onclick="addDiagnosis()">+ Thêm chẩn đoán</button>
            </div>
        </div>

        {{-- ── Đơn thuốc ────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">💊 Đơn thuốc</div>
            <div class="form-section-body">
                <div id="rxContainer">
                    @forelse($record->prescriptions ?? [] as $i => $rx)
                    <div class="rx-row">
                        <div class="row g-2">
                            <div class="col-md-3"> {{-- SỬA: col-md-4 -> col-md-3 --}}
                                <input type="text" name="prescriptions[{{ $i }}][drug_name]" class="form-control"
                                    placeholder="Tên thuốc *" value="{{ $rx->drug_name }}" required>
                            </div>
                            <div class="col-md-2"> {{-- col-md-3 -> col-md-2 --}}
                                <input type="text" name="prescriptions[{{ $i }}][dosage]" class="form-control"
                                    placeholder="Liều dùng" value="{{ $rx->dosage }}">
                            </div>
                            <div class="col-md-2"> {{-- THÊM DÒNG NÀY --}}
                                <input type="number" name="prescriptions[{{ $i }}][quantity]" class="form-control"
                                    placeholder="Số lượng" value="{{ $rx->quantity ?? 1 }}" min="1">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="prescriptions[{{ $i }}][duration_days]" class="form-control"
                                    placeholder="Số ngày" value="{{ $rx->duration_days ?? 30 }}" min="1">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="prescriptions[{{ $i }}][instructions]" class="form-control"
                                    placeholder="Hướng dẫn" value="{{ $rx->instructions }}">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="row-delete-btn" onclick="this.closest('.rx-row').remove()">✕</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
                <button type="button" class="add-row-btn" onclick="addRx()">+ Thêm thuốc</button>
            </div>
        </div>

        {{-- ── Chỉ định xét nghiệm ──────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">🔬 Chỉ định xét nghiệm / hình ảnh</div>
            <div class="form-section-body">
                <div id="orderContainer">
                    @forelse($record->medicalOrders ?? [] as $i => $order)
                    <div class="order-row">
                        <div class="row g-2">
                            <div class="col-md-2">
                                <select name="orders[{{ $i }}][order_type]" class="form-select">
                                    <option value="lab" {{ $order->order_type === 'lab'     ? 'selected' : '' }}>🧪 Xét nghiệm</option>
                                    <option value="imaging" {{ $order->order_type === 'imaging' ? 'selected' : '' }}>🩻 Hình ảnh</option>
                                    <option value="other" {{ $order->order_type === 'other'   ? 'selected' : '' }}>📋 Khác</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="orders[{{ $i }}][order_name]" class="form-control"
                                    placeholder="Tên xét nghiệm *" value="{{ $order->order_name }}" required>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="orders[{{ $i }}][description]" class="form-control"
                                    placeholder="Mô tả" value="{{ $order->description }}">
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="row-delete-btn" onclick="this.closest('.order-row').remove()">✕</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
                <button type="button" class="add-row-btn" onclick="addOrder()">+ Thêm chỉ định</button>
            </div>
        </div>

        {{-- Submit --}}
        <div class="d-flex gap-2 justify-content-end mb-4">
            <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">Hủy</a>
            <button type="submit" class="btn btn-primary px-4">
                {{ isset($record) ? '💾 Lưu thay đổi' : '✅ Tạo hồ sơ' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@php
$aiCount = isset($record) ? ($record->allergies->count() ?? 0) : 0;
$diCount = isset($record) ? ($record->diagnoses->count() ?? 0) : 0;
$riCount = isset($record) ? ($record->prescriptions->count() ?? 0) : 0;
$oiCount = isset($record) ? ($record->medicalOrders->count() ?? 0) : 0;
@endphp
<script>
let ai = {{ $aiCount }};
let di = {{ $diCount }};
let ri = {{ $riCount }};
let oi = {{ $oiCount }};

    // Kiểm tra xem các biến có được khởi tạo đúng không
    console.log('Counts:', {
        ai,
        di,
        ri,
        oi
    });

    // ── Thêm dòng mới ────────────────────────────────────────────────

    function addAllergy() {
        const index = Date.now();
        document.getElementById('allergyContainer').insertAdjacentHTML('beforeend', `
        <div class="allergy-row" data-index="${index}">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="allergies[${index}][allergen]" class="form-control" placeholder="Tên chất gây dị ứng">
                </div>
                <div class="col-md-3">
                    <select name="allergies[${index}][severity]" class="form-select">
                        <option value="">Mức độ</option>
                        <option value="Nhẹ">Nhẹ</option>
                        <option value="Vừa">Vừa</option>
                        <option value="Nặng">Nặng</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="allergies[${index}][reaction]" class="form-control" placeholder="Phản ứng">
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="row-delete-btn" onclick="this.closest('.allergy-row').remove()">✕</button>
                </div>
            </div>
        </div>`);
    }

    function addDiagnosis() {
        document.getElementById('diagnosisContainer').insertAdjacentHTML('beforeend', `
        <div class="diagnosis-row">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="diagnoses[${di}][diagnosis_name]" class="form-control" placeholder="Tên chẩn đoán *" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="diagnoses[${di}][icd_code]" class="form-control" placeholder="Mã ICD">
                </div>
                <div class="col-md-3">
                    <select name="diagnoses[${di}][diagnosis_type]" class="form-select">
                        <option value="primary">Chính</option>
                        <option value="secondary">Phụ</option>
                        <option value="complication">Biến chứng</option>
                    </select>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="row-delete-btn" onclick="this.closest('.diagnosis-row').remove()">✕</button>
                </div>
            </div>
        </div>`);
        di++;
    }

    function addRx() {
        document.getElementById('rxContainer').insertAdjacentHTML('beforeend', `
        <div class="rx-row">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="prescriptions[${ri}][drug_name]" class="form-control" placeholder="Tên thuốc *" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="prescriptions[${ri}][dosage]" class="form-control" placeholder="Liều dùng">
                </div>
                <div class="col-md-2">
                    <input type="number" name="prescriptions[${ri}][quantity]" class="form-control" placeholder="Số lượng" value="1" min="1">
                </div>
                <div class="col-md-2">
                    <input type="number" name="prescriptions[${ri}][duration_days]" class="form-control" placeholder="Số ngày" value="30" min="1">
                </div>
                <div class="col-md-2">
                    <input type="text" name="prescriptions[${ri}][instructions]" class="form-control" placeholder="Hướng dẫn">
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="row-delete-btn" onclick="this.closest('.rx-row').remove()">✕</button>
                </div>
            </div>
        </div>`);
        ri++;
    }

    function addOrder() {
        document.getElementById('orderContainer').insertAdjacentHTML('beforeend', `
        <div class="order-row">
            <div class="row g-2">
                <div class="col-md-2">
                    <select name="orders[${oi}][order_type]" class="form-select">
                        <option value="lab">🧪 Xét nghiệm</option>
                        <option value="imaging">🩻 Hình ảnh</option>
                        <option value="other">📋 Khác</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="orders[${oi}][order_name]" class="form-control" placeholder="Tên xét nghiệm *" required>
                </div>
                <div class="col-md-5">
                    <input type="text" name="orders[${oi}][description]" class="form-control" placeholder="Mô tả">
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="row-delete-btn" onclick="this.closest('.order-row').remove()">✕</button>
                </div>
            </div>
        </div>`);
        oi++;
    }

    // ── Validation trước khi submit ───────────────────────────────────

    document.getElementById('medicalRecordForm')?.addEventListener('submit', function(e) {
        let errors = [];

        // 1. Lý do khám
        const chiefComplaint = document.querySelector('[name="chief_complaint"]');
        if (!chiefComplaint?.value.trim()) {
            errors.push('⚠️ Vui lòng nhập lý do đến khám / triệu chứng');
            chiefComplaint?.classList.add('is-invalid');
        } else {
            chiefComplaint?.classList.remove('is-invalid');
        }

        // 2. Loại khám
        const visitType = document.querySelector('[name="visit_type"]');
        if (!visitType?.value) {
            errors.push('⚠️ Vui lòng chọn loại khám');
            visitType?.classList.add('is-invalid');
        } else {
            visitType?.classList.remove('is-invalid');
        }

        // 3. Chỉ số sinh tồn
        const vitals = {
            'vitals[blood_pressure]': 'huyết áp',
            'vitals[heart_rate]': 'nhịp tim',
            'vitals[temperature]': 'nhiệt độ',
            'vitals[spo2]': 'SpO2',
            'vitals[weight]': 'cân nặng',
        };
        for (const [name, label] of Object.entries(vitals)) {
            const el = document.querySelector(`[name="${name}"]`);
            if (!el?.value) {
                errors.push(`⚠️ Vui lòng nhập ${label}`);
                el?.classList.add('is-invalid');
            } else {
                el?.classList.remove('is-invalid');
            }
        }

        // 4. Chẩn đoán (ít nhất 1)
        const diagInputs = document.querySelectorAll('[name*="diagnoses"][name*="[diagnosis_name]"]');
        let hasValidDiagnosis = false;
        diagInputs.forEach(input => {
            if (input.value.trim()) {
                hasValidDiagnosis = true;
                input.classList.remove('is-invalid');
            } else {
                input.classList.add('is-invalid');
            }
        });
        if (!hasValidDiagnosis) {
            errors.push('⚠️ Vui lòng thêm ít nhất 1 chẩn đoán');
        }

        // 5. Dị ứng — nếu có severity/reaction phải có allergen
        document.querySelectorAll('.allergy-row').forEach((row, idx) => {
            const allergen = row.querySelector('[name*="[allergen]"]');
            const severity = row.querySelector('[name*="[severity]"]');
            const reaction = row.querySelector('[name*="[reaction]"]');
            if ((severity?.value || reaction?.value) && !allergen?.value) {
                errors.push(`⚠️ Dị ứng #${idx + 1}: Vui lòng nhập tên chất gây dị ứng`);
                allergen?.classList.add('is-invalid');
            }
        });

        // 6. Đơn thuốc — nếu có tên thuốc phải có liều dùng + hướng dẫn
        document.querySelectorAll('.rx-row').forEach((row, idx) => {
            const drugName = row.querySelector('[name*="[drug_name]"]');
            const dosage = row.querySelector('[name*="[dosage]"]');
            const instructions = row.querySelector('[name*="[instructions]"]');
            if (drugName?.value) {
                if (!dosage?.value) {
                    errors.push(`⚠️ Thuốc #${idx + 1}: Vui lòng nhập liều dùng`);
                    dosage?.classList.add('is-invalid');
                }
                if (!instructions?.value) {
                    errors.push(`⚠️ Thuốc #${idx + 1}: Vui lòng nhập hướng dẫn sử dụng`);
                    instructions?.classList.add('is-invalid');
                }
            }
        });

        // 7. Chỉ định — order_type là select có sẵn giá trị nên không cần check
        // (chỉ check order_name nếu muốn)

        if (errors.length > 0) {
            e.preventDefault();
            alert('⚠️ Vui lòng kiểm tra lại:\n\n' + errors.join('\n'));
            const firstError = document.querySelector('.is-invalid');
            firstError?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            return false;
        }
    });

    // Real-time: xóa is-invalid khi người dùng nhập
    document.addEventListener('input', function(e) {
        if (e.target.matches('input, select, textarea') && e.target.value.trim()) {
            e.target.classList.remove('is-invalid');
        }
    });
</script>
@endpush