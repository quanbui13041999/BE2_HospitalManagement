{{-- resources/views/medical-records/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Chỉnh sửa hồ sơ bệnh án')

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
        transition: all 0.2s;
    }
    
    .allergy-row:hover,
    .diagnosis-row:hover {
        background: #f0f4f8;
    }

    /* Style cho editable field */
    .editable-field {
        cursor: pointer;
        position: relative;
        width: 100%;
    }

    .field-value {
        display: block;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: #fff;
        transition: all 0.2s;
        font-size: 14px;
    }

    .field-value:hover {
        border-color: #1a6fb3;
        background: #f0f7ff;
    }

    .field-dropdown {
        display: none;
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #1a6fb3;
        border-radius: 6px;
        background: #fff;
        font-size: 14px;
        margin-top: 0;
    }

    /* Style cho field bị lỗi */
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

    /* Highlight row bị lỗi */
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
        <a href="{{ route('medical-records.show', $record->record_id) }}" class="btn btn-sm btn-outline-secondary me-3">← Quay lại</a>
        <h5 class="mb-0 fw-bold">
            Chỉnh sửa hồ sơ: {{ $record->record_code ?? 'Mã số: ' . $record->record_id }}
        </h5>
    </div>

    <form action="{{ route('medical-records.update', $record->record_id) }}" method="POST" enctype="multipart/form-data" id="medicalRecordForm">
        @csrf
        @method('PUT')

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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bệnh nhân <span class="text-danger">*</span></label>
                        <input type="text" name="patient_name" class="form-control"
                            value="{{ old('patient_name', $record->patient_name) }}" required>
                        <input type="hidden" name="patient_id" value="{{ old('patient_id', $record->patient_id ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mã bệnh nhân</label>
                        <input type="text" class="form-control" readonly
                            value="{{ $record->patient_code ?: 'BN' . str_pad((string) $record->patient_id, 6, '0', STR_PAD_LEFT) }}">
                        <small class="text-muted">Mã bệnh nhân tự động, không cho phép sửa.</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Ngày khám <span class="text-danger">*</span></label>
                        <input type="date" name="exam_date" class="form-control"
                            value="{{ old('exam_date', $record->exam_date instanceof \Carbon\Carbon ? $record->exam_date->format('Y-m-d') : date('Y-m-d', strtotime($record->exam_date))) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Giờ khám</label>
                        <input type="time" name="exam_time" class="form-control"
                            value="{{ old('exam_time', isset($record->exam_time) ? \Carbon\Carbon::parse($record->exam_time)->format('H:i') : '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Bác sĩ <span class="text-danger">*</span></label>
                        <input type="text" name="doctor_name" class="form-control"
                            value="{{ old('doctor_name', $record->doctor_name) }}" required>
                        <input type="hidden" name="doctor_id" value="{{ old('doctor_id', $record->doctor_id) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Loại khám <span class="text-danger">*</span></label>
                        <select name="visit_type" class="form-select" required>
                            <option value="">Chọn loại khám</option>
                            @foreach(['Khám mới','Tái khám','Cấp cứu'] as $type)
                            <option value="{{ $type }}"
                                {{ old('visit_type', $record->visit_type) === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Lý do đến khám / Triệu chứng <span class="text-danger">*</span></label>
                        <textarea name="chief_complaint" class="form-control" rows="2" required>{{ old('chief_complaint', $record->chief_complaint) }}</textarea>
                    </div>
                    @if($record->appointment_id)
                    <input type="hidden" name="appointment_id" value="{{ $record->appointment_id }}">
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Chỉ số sinh tồn ──────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">❤️ Chỉ số sinh tồn <span class="text-danger">*</span></div>
            <div class="form-section-body">
                <div class="row g-3">
                    @php $v = $record->vitalSigns ?? null; @endphp
                    <div class="col-md-4">
                        <label class="form-label">Huyết áp (mmHg) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="vitals[blood_pressure]" class="form-control"
                                placeholder="VD: 130/80" value="{{ old('vitals.blood_pressure', $v?->blood_pressure) }}" required>
                            <select name="vitals[bp_status]" class="form-select" style="max-width:90px">
                                <option value="normal" {{ old('vitals.bp_status', $v?->bp_status) === 'normal' ? 'selected' : '' }}>✓</option>
                                <option value="high" {{ old('vitals.bp_status', $v?->bp_status) === 'high'   ? 'selected' : '' }}>▲ Cao</option>
                                <option value="low" {{ old('vitals.bp_status', $v?->bp_status) === 'low'    ? 'selected' : '' }}>▼ Thấp</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nhịp tim (bpm) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="vitals[heart_rate]" class="form-control" step="0.1"laceholder="Bình thường: 60 - 100 bpm"
                                value="{{ old('vitals.heart_rate', $v?->heart_rate) }}" required>
                            <select name="vitals[hr_status]" class="form-select" style="max-width:90px">
                                <option value="normal" {{ old('vitals.hr_status', $v?->hr_status) === 'normal' ? 'selected' : '' }}>✓</option>
                                <option value="high" {{ old('vitals.hr_status', $v?->hr_status) === 'high' ? 'selected' : '' }}>▲</option>
                                <option value="low" {{ old('vitals.hr_status', $v?->hr_status) === 'low'  ? 'selected' : '' }}>▼</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nhiệt độ (°C) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="vitals[temperature]" class="form-control" step="0.1"    placeholder="Bình thường: 36.5 - 37.5 °C"
                                value="{{ old('vitals.temperature', $v?->temperature) }}" required>
                            <select name="vitals[temp_status]" class="form-select" style="max-width:90px">
                                <option value="normal" {{ old('vitals.temp_status', $v?->temp_status) === 'normal' ? 'selected' : '' }}>✓</option>
                                <option value="high" {{ old('vitals.temp_status', $v?->temp_status) === 'high' ? 'selected' : '' }}>▲</option>
                                <option value="low" {{ old('vitals.temp_status', $v?->temp_status) === 'low'  ? 'selected' : '' }}>▼</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SpO2 (%) <span class="text-danger">*</span></label>
                        <input type="number" name="vitals[spo2]" class="form-control" step="0.1" placeholder="Bình thường: 95 - 100 %"
                            value="{{ old('vitals.spo2', $v?->spo2) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cân nặng (kg) <span class="text-danger">*</span></label>
                        <input type="number" name="vitals[weight]" class="form-control" step="0.1"
                            value="{{ old('vitals.weight', $v?->weight) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Đường huyết (mmol/L)</label>
                        <div class="input-group">
                            <input type="number" name="vitals[blood_sugar]" class="form-control" step="0.01"   placeholder="Bình thường: 3.9 - 7.8 mmol/L"
                                value="{{ old('vitals.blood_sugar', $v?->blood_sugar) }}">
                            <select name="vitals[sugar_status]" class="form-select" style="max-width:90px">
                                <option value="normal" {{ old('vitals.sugar_status', $v?->sugar_status) === 'normal' ? 'selected' : '' }}>✓</option>
                                <option value="high" {{ old('vitals.sugar_status', $v?->sugar_status) === 'high' ? 'selected' : '' }}>▲</option>
                                <option value="low" {{ old('vitals.sugar_status', $v?->sugar_status) === 'low'  ? 'selected' : '' }}>▼</option>
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
                    @forelse($record->allergies ?? [] as $index => $allergy)
                    <div class="allergy-row" data-index="{{ $index }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="editable-field" data-field="allergen" data-index="{{ $index }}">
                                    <div class="field-value" id="allergen_val_{{ $index }}">{{ $allergy->allergen ?: 'Chọn chất gây dị ứng' }}</div>
                                    <select class="field-dropdown" id="allergen_drop_{{ $index }}" data-field="allergen" data-index="{{ $index }}">
                                        <option value="">-- Chọn --</option>
                                        <option value="Penicillin">Penicillin</option>
                                        <option value="Cá">Cá</option>
                                        <option value="Tôm">Tôm</option>
                                        <option value="Cua">Cua</option>
                                        <option value="Sữa">Sữa</option>
                                        <option value="Trứng">Trứng</option>
                                        <option value="Đậu phộng">Đậu phộng</option>
                                        <option value="Phấn hoa">Phấn hoa</option>
                                        <option value="Bụi nhà">Bụi nhà</option>
                                        <option value="Lông chó mèo">Lông chó/mèo</option>
                                    </select>
                                    <input type="hidden" name="allergies[{{ $index }}][allergen]" value="{{ $allergy->allergen }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="editable-field" data-field="severity" data-index="{{ $index }}">
                                    <div class="field-value" id="severity_val_{{ $index }}">{{ $allergy->severity ?: 'Chọn mức độ' }}</div>
                                    <select class="field-dropdown" id="severity_drop_{{ $index }}" data-field="severity" data-index="{{ $index }}">
                                        <option value="">-- Chọn --</option>
                                        <option value="Nhẹ">Nhẹ</option>
                                        <option value="Vừa">Vừa</option>
                                        <option value="Nặng">Nặng</option>
                                    </select>
                                    <input type="hidden" name="allergies[{{ $index }}][severity]" value="{{ $allergy->severity }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="editable-field" data-field="reaction" data-index="{{ $index }}">
                                    <div class="field-value" id="reaction_val_{{ $index }}">{{ $allergy->reaction ?: 'Chọn phản ứng' }}</div>
                                    <select class="field-dropdown" id="reaction_drop_{{ $index }}" data-field="reaction" data-index="{{ $index }}">
                                        <option value="">-- Chọn --</option>
                                        <option value="Phát ban">Phát ban</option>
                                        <option value="Ngứa">Ngứa</option>
                                        <option value="Nổi mề đay">Nổi mề đay</option>
                                        <option value="Khó thở">Khó thở</option>
                                        <option value="Sốc phản vệ">Sốc phản vệ</option>
                                        <option value="Đau bụng">Đau bụng</option>
                                        <option value="Tiêu chảy">Tiêu chảy</option>
                                        <option value="Hắt hơi">Hắt hơi</option>
                                    </select>
                                    <input type="hidden" name="allergies[{{ $index }}][reaction]" value="{{ $allergy->reaction }}">
                                </div>
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="row-delete-btn" onclick="this.closest('.allergy-row').remove()">✕</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
                <button type="button" class="add-row-btn" onclick="addAllergyRow()">+ Thêm dị ứng</button>
            </div>
        </div>

        {{-- ── Chẩn đoán ────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">🩺 Chẩn đoán <span class="text-danger">*</span></div>
            <div class="form-section-body">
                <div id="diagnosisContainer">
                    @forelse($record->diagnoses ?? [] as $index => $diagnosis)
                    <div class="diagnosis-row" data-index="{{ $index }}">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <div class="editable-field" data-field="diag_name" data-index="{{ $index }}">
                                    <div class="field-value" id="diag_name_val_{{ $index }}">{{ $diagnosis->diagnosis_name ?: 'Chọn chẩn đoán' }}</div>
                                    <select class="field-dropdown" id="diag_name_drop_{{ $index }}" data-field="diag_name" data-index="{{ $index }}">
                                        <option value="">-- Chọn --</option>
                                        <option value="Cúm A">Cúm A</option>
                                        <option value="Cúm B">Cúm B</option>
                                        <option value="COVID-19">COVID-19</option>
                                        <option value="Viêm phổi">Viêm phổi</option>
                                        <option value="Viêm phế quản">Viêm phế quản</option>
                                        <option value="Viêm họng">Viêm họng</option>
                                        <option value="Viêm amidan">Viêm amidan</option>
                                        <option value="Viêm dạ dày">Viêm dạ dày</option>
                                        <option value="Tăng huyết áp">Tăng huyết áp</option>
                                        <option value="Đái tháo đường type 2">Đái tháo đường type 2</option>
                                        <option value="Gout">Gout</option>
                                        <option value="Thoái hóa khớp">Thoái hóa khớp</option>
                                    </select>
                                    <input type="hidden" name="diagnoses[{{ $index }}][diagnosis_name]" value="{{ $diagnosis->diagnosis_name }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="editable-field" data-field="icd" data-index="{{ $index }}">
                                    <div class="field-value" id="icd_val_{{ $index }}">{{ $diagnosis->icd_code ?: 'Mã ICD' }}</div>
                                    <select class="field-dropdown" id="icd_drop_{{ $index }}" data-field="icd" data-index="{{ $index }}">
                                        <option value="">-- Chọn ICD --</option>
                                        <option value="J10.1">J10.1 - Cúm A</option>
                                        <option value="J11.1">J11.1 - Cúm B</option>
                                        <option value="U07.1">U07.1 - COVID-19</option>
                                        <option value="J18.9">J18.9 - Viêm phổi</option>
                                        <option value="J20.9">J20.9 - Viêm phế quản</option>
                                        <option value="J02.9">J02.9 - Viêm họng</option>
                                        <option value="J03.9">J03.9 - Viêm amidan</option>
                                        <option value="K29.7">K29.7 - Viêm dạ dày</option>
                                        <option value="I10">I10 - Tăng huyết áp</option>
                                        <option value="E11">E11 - Đái tháo đường type 2</option>
                                        <option value="M10.9">M10.9 - Gout</option>
                                        <option value="M19.9">M19.9 - Thoái hóa khớp</option>
                                    </select>
                                    <input type="hidden" name="diagnoses[{{ $index }}][icd_code]" value="{{ $diagnosis->icd_code }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="editable-field" data-field="diag_type" data-index="{{ $index }}">
                                    <div class="field-value" id="diag_type_val_{{ $index }}">{{ $diagnosis->diagnosis_type ?: 'Chọn loại' }}</div>
                                    <select class="field-dropdown" id="diag_type_drop_{{ $index }}" data-field="diag_type" data-index="{{ $index }}">
                                        <option value="">-- Chọn --</option>
                                        <option value="primary">Chính</option>
                                        <option value="secondary">Phụ</option>
                                        <option value="complication">Biến chứng</option>
                                    </select>
                                    <input type="hidden" name="diagnoses[{{ $index }}][diagnosis_type]" value="{{ $diagnosis->diagnosis_type }}">
                                </div>
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="row-delete-btn" onclick="this.closest('.diagnosis-row').remove()">✕</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
                <button type="button" class="add-row-btn" onclick="addDiagnosisRow()">+ Thêm chẩn đoán</button>
            </div>
        </div>

        {{-- ── Đơn thuốc ────────────────────────────────────── --}}
<div class="form-section">
    <div class="form-section-header">💊 Đơn thuốc</div>
    <div class="form-section-body">
        <div id="rxContainer">
            @forelse($record->prescriptions ?? [] as $index => $prescription)
            <div class="rx-row" data-index="{{ $index }}">
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="editable-field" data-field="drug_name" data-index="{{ $index }}">
                            <div class="field-value" id="drug_name_val_{{ $index }}">{{ $prescription->drug_name ?: 'Chọn thuốc' }}</div>
                            <select class="field-dropdown" id="drug_name_drop_{{ $index }}" data-field="drug_name" data-index="{{ $index }}">
                                <option value="">-- Chọn thuốc --</option>
                                <option value="Paracetamol">Paracetamol</option>
                                <option value="Amoxicillin">Amoxicillin</option>
                                <option value="Azithromycin">Azithromycin</option>
                                <option value="Ibuprofen">Ibuprofen</option>
                                <option value="Omeprazol">Omeprazol</option>
                                <option value="Losartan">Losartan</option>
                                <option value="Metformin">Metformin</option>
                                <option value="Cetirizine">Cetirizine</option>
                                <option value="Vitamin C">Vitamin C</option>
                                <option value="Vitamin B-complex">Vitamin B-complex</option>
                                <option value="Domperidone">Domperidone</option>
                                <option value="Amlodipine">Amlodipine</option>
                                <option value="Metoprolol">Metoprolol</option>
                                <option value="Atorvastatin">Atorvastatin</option>
                                <option value="Aspirin">Aspirin</option>
                            </select>
                            <input type="hidden" name="prescriptions[{{ $index }}][drug_name]" value="{{ $prescription->drug_name }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="editable-field" data-field="dosage" data-index="{{ $index }}">
                            <div class="field-value" id="dosage_val_{{ $index }}">{{ $prescription->dosage ?: 'Liều dùng' }}</div>
                            <select class="field-dropdown" id="dosage_drop_{{ $index }}" data-field="dosage" data-index="{{ $index }}">
                                <option value="">-- Chọn liều --</option>
                                <option value="500mg x 2 lần/ngày">500mg x 2 lần/ngày</option>
                                <option value="500mg x 3 lần/ngày">500mg x 3 lần/ngày</option>
                                <option value="250mg x 2 lần/ngày">250mg x 2 lần/ngày</option>
                                <option value="100mg x 1 lần/ngày">100mg x 1 lần/ngày</option>
                                <option value="50mg x 1 lần/ngày">50mg x 1 lần/ngày</option>
                                <option value="20mg x 1 lần/ngày">20mg x 1 lần/ngày</option>
                                <option value="10mg x 1 lần/ngày">10mg x 1 lần/ngày</option>
                                <option value="5mg x 1 lần/ngày">5mg x 1 lần/ngày</option>
                                <option value="1 viên x 1 lần/ngày">1 viên x 1 lần/ngày</option>
                                <option value="1 gói x 2 lần/ngày">1 gói x 2 lần/ngày</option>
                            </select>
                            <input type="hidden" name="prescriptions[{{ $index }}][dosage]" value="{{ $prescription->dosage }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="editable-field" data-field="quantity" data-index="{{ $index }}">
                            <div class="field-value" id="quantity_val_{{ $index }}">{{ $prescription->quantity ?? 'Số lượng' }}</div>
                            <select class="field-dropdown" id="quantity_drop_{{ $index }}" data-field="quantity" data-index="{{ $index }}">
                                <option value="">-- Số lượng --</option>
                                <option value="1">1</option>
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50">50</option>
                                <option value="60">60</option>
                                <option value="100">100</option>
                            </select>
                            <input type="hidden" name="prescriptions[{{ $index }}][quantity]" value="{{ $prescription->quantity ?? 1 }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="editable-field" data-field="duration_days" data-index="{{ $index }}">
                            <div class="field-value" id="duration_days_val_{{ $index }}">{{ $prescription->duration_days ?? 'Số ngày' }}</div>
                            <select class="field-dropdown" id="duration_days_drop_{{ $index }}" data-field="duration_days" data-index="{{ $index }}">
                                <option value="">-- Số ngày --</option>
                                <option value="3">3 ngày</option>
                                <option value="5">5 ngày</option>
                                <option value="7">7 ngày</option>
                                <option value="10">10 ngày</option>
                                <option value="14">14 ngày</option>
                                <option value="30">30 ngày</option>
                                <option value="60">60 ngày</option>
                                <option value="90">90 ngày</option>
                            </select>
                            <input type="hidden" name="prescriptions[{{ $index }}][duration_days]" value="{{ $prescription->duration_days ?? 30 }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="editable-field" data-field="instructions" data-index="{{ $index }}">
                            <div class="field-value" id="instructions_val_{{ $index }}">{{ $prescription->instructions ?: 'Hướng dẫn' }}</div>
                            <select class="field-dropdown" id="instructions_drop_{{ $index }}" data-field="instructions" data-index="{{ $index }}">
                                <option value="">-- Hướng dẫn --</option>
                                <option value="Uống sau ăn">Uống sau ăn</option>
                                <option value="Uống trước ăn 30 phút">Uống trước ăn 30 phút</option>
                                <option value="Uống cùng bữa ăn">Uống cùng bữa ăn</option>
                                <option value="Uống lúc đói">Uống lúc đói</option>
                                <option value="Uống trước khi ngủ">Uống trước khi ngủ</option>
                                <option value="Pha với nước uống">Pha với nước uống</option>
                                <option value="Ngậm dưới lưỡi">Ngậm dưới lưỡi</option>
                                <option value="Uống với nhiều nước">Uống với nhiều nước</option>
                            </select>
                            <input type="hidden" name="prescriptions[{{ $index }}][instructions]" value="{{ $prescription->instructions }}">
                        </div>
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="row-delete-btn" onclick="this.closest('.rx-row').remove()">✕</button>
                    </div>
                </div>
            </div>
            @empty
            @endforelse
        </div>
        <button type="button" class="add-row-btn" onclick="addRxRow()">+ Thêm thuốc</button>
    </div>
</div>

        {{-- ── Chỉ định xét nghiệm / hình ảnh ──────────────────────────── --}}
<div class="form-section">
    <div class="form-section-header">🔬 Chỉ định xét nghiệm / hình ảnh</div>
    <div class="form-section-body">
        <div id="orderContainer">
            @forelse($record->medicalOrders ?? [] as $index => $order)
            <div class="order-row" data-index="{{ $index }}">
                <div class="row g-2">
                    <div class="col-md-2">
                        <div class="editable-field" data-field="order_type" data-index="{{ $index }}">
                            <div class="field-value" id="order_type_val_{{ $index }}">{{ $order->order_type ?: 'Chọn loại' }}</div>
                            <select class="field-dropdown" id="order_type_drop_{{ $index }}" data-field="order_type" data-index="{{ $index }}">
                                <option value="">-- Chọn loại --</option>
                                <option value="lab">Xét nghiệm</option>
                                <option value="imaging">Hình ảnh</option>
                                <option value="other">Khác</option>
                            </select>
                            <input type="hidden" name="orders[{{ $index }}][order_type]" value="{{ $order->order_type }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="editable-field" data-field="order_name" data-index="{{ $index }}">
                            <div class="field-value" id="order_name_val_{{ $index }}">{{ $order->order_name ?: 'Chọn xét nghiệm' }}</div>
                            <select class="field-dropdown" id="order_name_drop_{{ $index }}" data-field="order_name" data-index="{{ $index }}">
                                <option value="">-- Chọn xét nghiệm --</option>
                                <optgroup label="Xét nghiệm máu">
                                    <option value="Công thức máu">Công thức máu (CBC)</option>
                                    <option value="Đường huyết">Đường huyết (Glucose)</option>
                                    <option value="HbA1c">HbA1c</option>
                                    <option value="Mỡ máu">Mỡ máu (Lipid profile)</option>
                                    <option value="Chức năng gan">Chức năng gan (GOT, GPT)</option>
                                    <option value="Chức năng thận">Chức năng thận (Ure, Creatinin)</option>
                                    <option value="Acid uric">Acid uric</option>
                                    <option value="Điện giải đồ">Điện giải đồ (Na+, K+, Cl-)</option>
                                    <option value="Troponin">Troponin</option>
                                    <option value="TSH">TSH (Tuyến giáp)</option>
                                    <option value="Ferritin">Ferritin</option>
                                    <option value="Vitamin D">Vitamin D</option>
                                </optgroup>
                                <optgroup label="Xét nghiệm vi sinh">
                                    <option value="Cấy máu">Cấy máu</option>
                                    <option value="Cấy đờm">Cấy đờm</option>
                                    <option value="Cấy nước tiểu">Cấy nước tiểu</option>
                                    <option value="Test COVID-19">Test COVID-19</option>
                                    <option value="Test Dengue">Test Dengue (NS1, IgM, IgG)</option>
                                    <option value="Test H.pylori">Test H.pylori</option>
                                </optgroup>
                                <optgroup label="Chẩn đoán hình ảnh">
                                    <option value="X-quang phổi">X-quang phổi</option>
                                    <option value="X-quang cột sống">X-quang cột sống</option>
                                    <option value="X-quang khớp">X-quang khớp</option>
                                    <option value="Siêu âm ổ bụng">Siêu âm ổ bụng</option>
                                    <option value="Siêu âm tim">Siêu âm tim</option>
                                    <option value="Siêu âm tuyến giáp">Siêu âm tuyến giáp</option>
                                    <option value="CT Scanner não">CT Scanner não</option>
                                    <option value="CT Scanner ngực">CT Scanner ngực</option>
                                    <option value="MRI cột sống">MRI cột sống</option>
                                    <option value="MRI khớp gối">MRI khớp gối</option>
                                    <option value="Điện tim (ECG)">Điện tim (ECG)</option>
                                    <option value="Điện não đồ (EEG)">Điện não đồ (EEG)</option>
                                    <option value="Nội soi dạ dày">Nội soi dạ dày</option>
                                    <option value="Nội soi đại tràng">Nội soi đại tràng</option>
                                </optgroup>
                                <optgroup label="Xét nghiệm khác">
                                    <option value="Tổng phân tích nước tiểu">Tổng phân tích nước tiểu</option>
                                    <option value="Thử thai">Thử thai</option>
                                    <option value="Test nhanh cúm">Test nhanh cúm A/B</option>
                                    <option value="Test nhanh Strep">Test nhanh liên cầu khuẩn</option>
                                </optgroup>
                            </select>
                            <input type="hidden" name="orders[{{ $index }}][order_name]" value="{{ $order->order_name }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="editable-field" data-field="description" data-index="{{ $index }}">
                            <div class="field-value" id="description_val_{{ $index }}">{{ $order->description ?: 'Mô tả' }}</div>
                            <select class="field-dropdown" id="description_drop_{{ $index }}" data-field="description" data-index="{{ $index }}">
                                <option value="">-- Mô tả --</option>
                                <option value="Đánh giá tình trạng thiếu máu, nhiễm trùng">Đánh giá tình trạng thiếu máu, nhiễm trùng</option>
                                <option value="Tầm soát đái tháo đường">Tầm soát đái tháo đường</option>
                                <option value="Đánh giá kiểm soát đường huyết 3 tháng">Đánh giá kiểm soát đường huyết 3 tháng</option>
                                <option value="Đánh giá nguy cơ tim mạch">Đánh giá nguy cơ tim mạch</option>
                                <option value="Đánh giá chức năng gan">Đánh giá chức năng gan</option>
                                <option value="Đánh giá chức năng thận">Đánh giá chức năng thận</option>
                                <option value="Chẩn đoán Gout">Chẩn đoán Gout</option>
                                <option value="Chẩn đoán sốt xuất huyết">Chẩn đoán sốt xuất huyết</option>
                                <option value="Chẩn đoán viêm phổi">Chẩn đoán viêm phổi</option>
                                <option value="Phát hiện lao phổi">Phát hiện lao phổi</option>
                                <option value="Đánh giá gan, mật, tụy, thận">Đánh giá gan, mật, tụy, thận</option>
                                <option value="Đánh giá cấu trúc và chức năng tim">Đánh giá cấu trúc và chức năng tim</option>
                                <option value="Phát hiện tổn thương não">Phát hiện tổn thương não</option>
                                <option value="Phát hiện thoát vị đĩa đệm">Phát hiện thoát vị đĩa đệm</option>
                            </select>
                            <input type="hidden" name="orders[{{ $index }}][description]" value="{{ $order->description }}">
                        </div>
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="row-delete-btn" onclick="this.closest('.order-row').remove()">✕</button>
                    </div>
                </div>
            </div>
            @empty
            @endforelse
        </div>
        <button type="button" class="add-row-btn" onclick="addOrderRow()">+ Thêm chỉ định</button>
    </div>
</div>

        {{-- Submit --}}
        <div class="d-flex gap-2 justify-content-end mb-4">
            <a href="{{ route('medical-records.show', $record->record_id) }}" class="btn btn-outline-secondary">Hủy</a>
            <button type="submit" class="btn btn-primary px-4">💾 Lưu thay đổi</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let diagnosisCounter = {{ $record->diagnoses->count() ?? 0 }};
    let prescriptionCounter = {{ $record->prescriptions->count() ?? 0 }};
    let orderCounter = {{ $record->medicalOrders->count() ?? 0 }};

    // Hàm xử lý click vào editable field
    function handleEditableClick(fieldDiv) {
        const valueDiv = fieldDiv.querySelector('.field-value');
        const dropdown = fieldDiv.querySelector('.field-dropdown');
        const hiddenInput = fieldDiv.querySelector('input[type="hidden"]');
        
        if (!valueDiv || !dropdown) return;
        
        // Ẩn value div, hiện dropdown
        valueDiv.style.display = 'none';
        dropdown.style.display = 'block';
        
        // Set giá trị hiện tại cho dropdown
        if (hiddenInput && hiddenInput.value) {
            dropdown.value = hiddenInput.value;
        }
        
        // Xử lý khi chọn giá trị từ dropdown
        dropdown.onchange = function() {
            const newValue = dropdown.value;
            valueDiv.innerText = newValue || valueDiv.getAttribute('data-placeholder') || 'Chọn';
            if (hiddenInput) hiddenInput.value = newValue;
            
            // Ẩn dropdown, hiện value div
            dropdown.style.display = 'none';
            valueDiv.style.display = 'block';
        };
        
        // Xử lý khi blur (click ra ngoài)
        dropdown.onblur = function() {
            dropdown.style.display = 'none';
            valueDiv.style.display = 'block';
        };
        
        dropdown.focus();
    }

    // Gán sự kiện click cho tất cả editable-field
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.editable-field').forEach(field => {
            const valueDiv = field.querySelector('.field-value');
            if (valueDiv) {
                // Lưu placeholder
                valueDiv.setAttribute('data-placeholder', valueDiv.innerText);
            }
            field.addEventListener('click', function(e) {
                e.stopPropagation();
                handleEditableClick(this);
            });
        });
    });

    // Thêm dòng dị ứng mới
    function addAllergyRow() {
        const index = Date.now();
        const html = `
        <div class="allergy-row" data-index="${index}">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="editable-field" data-field="allergen" data-index="${index}">
                        <div class="field-value" data-placeholder="Chọn chất gây dị ứng">Chọn chất gây dị ứng</div>
                        <select class="field-dropdown" data-field="allergen" data-index="${index}">
                            <option value="">-- Chọn --</option>
                            <option value="Penicillin">Penicillin</option>
                            <option value="Cá">Cá</option>
                            <option value="Tôm">Tôm</option>
                            <option value="Sữa">Sữa</option>
                            <option value="Trứng">Trứng</option>
                        </select>
                        <input type="hidden" name="allergies[${index}][allergen]" value="">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="editable-field" data-field="severity" data-index="${index}">
                        <div class="field-value" data-placeholder="Chọn mức độ">Chọn mức độ</div>
                        <select class="field-dropdown" data-field="severity" data-index="${index}">
                            <option value="">-- Chọn --</option>
                            <option value="Nhẹ">Nhẹ</option>
                            <option value="Vừa">Vừa</option>
                            <option value="Nặng">Nặng</option>
                        </select>
                        <input type="hidden" name="allergies[${index}][severity]" value="">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="editable-field" data-field="reaction" data-index="${index}">
                        <div class="field-value" data-placeholder="Chọn phản ứng">Chọn phản ứng</div>
                        <select class="field-dropdown" data-field="reaction" data-index="${index}">
                            <option value="">-- Chọn --</option>
                            <option value="Phát ban">Phát ban</option>
                            <option value="Ngứa">Ngứa</option>
                            <option value="Khó thở">Khó thở</option>
                        </select>
                        <input type="hidden" name="allergies[${index}][reaction]" value="">
                    </div>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="row-delete-btn" onclick="this.closest('.allergy-row').remove()">✕</button>
                </div>
            </div>
        </div>`;
        document.getElementById('allergyContainer').insertAdjacentHTML('beforeend', html);
        
        // Gán sự kiện cho editable-field mới
        const newRow = document.getElementById('allergyContainer').lastElementChild;
        newRow.querySelectorAll('.editable-field').forEach(field => {
            field.addEventListener('click', function(e) {
                e.stopPropagation();
                handleEditableClick(this);
            });
        });
    }

    // Thêm dòng chẩn đoán mới
    function addDiagnosisRow() {
        const index = diagnosisCounter;
        const html = `
        <div class="diagnosis-row" data-index="${index}">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="editable-field" data-field="diag_name" data-index="${index}">
                        <div class="field-value" data-placeholder="Chọn chẩn đoán">Chọn chẩn đoán</div>
                        <select class="field-dropdown" data-field="diag_name" data-index="${index}">
                            <option value="">-- Chọn --</option>
                            <option value="Cúm A">Cúm A</option>
                            <option value="Viêm phổi">Viêm phổi</option>
                            <option value="Tăng huyết áp">Tăng huyết áp</option>
                            <option value="Đái tháo đường">Đái tháo đường</option>
                        </select>
                        <input type="hidden" name="diagnoses[${index}][diagnosis_name]" value="">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="editable-field" data-field="icd" data-index="${index}">
                        <div class="field-value" data-placeholder="Mã ICD">Mã ICD</div>
                        <select class="field-dropdown" data-field="icd" data-index="${index}">
                            <option value="">-- Chọn ICD --</option>
                            <option value="J10.1">J10.1 - Cúm A</option>
                            <option value="J18.9">J18.9 - Viêm phổi</option>
                            <option value="I10">I10 - Tăng huyết áp</option>
                            <option value="E11">E11 - Đái tháo đường type 2</option>
                        </select>
                        <input type="hidden" name="diagnoses[${index}][icd_code]" value="">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="editable-field" data-field="diag_type" data-index="${index}">
                        <div class="field-value" data-placeholder="Chọn loại">Chọn loại</div>
                        <select class="field-dropdown" data-field="diag_type" data-index="${index}">
                            <option value="">-- Chọn --</option>
                            <option value="primary">Chính</option>
                            <option value="secondary">Phụ</option>
                            <option value="complication">Biến chứng</option>
                        </select>
                        <input type="hidden" name="diagnoses[${index}][diagnosis_type]" value="">
                    </div>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="row-delete-btn" onclick="this.closest('.diagnosis-row').remove()">✕</button>
                </div>
            </div>
        </div>`;
        document.getElementById('diagnosisContainer').insertAdjacentHTML('beforeend', html);
        diagnosisCounter++;
        
        // Gán sự kiện cho editable-field mới
        const newRow = document.getElementById('diagnosisContainer').lastElementChild;
        newRow.querySelectorAll('.editable-field').forEach(field => {
            field.addEventListener('click', function(e) {
                e.stopPropagation();
                handleEditableClick(this);
            });
        });
    }

   function addRxRow() {
    const index = prescriptionCounter;
    const html = `
    <div class="rx-row" data-index="${index}">
        <div class="row g-2">
            <div class="col-md-3">
                <div class="editable-field" data-field="drug_name" data-index="${index}">
                    <div class="field-value" data-placeholder="Chọn thuốc">Chọn thuốc</div>
                    <select class="field-dropdown" data-field="drug_name" data-index="${index}">
                        <option value="">-- Chọn thuốc --</option>
                        <option value="Paracetamol">Paracetamol</option>
                        <option value="Amoxicillin">Amoxicillin</option>
                        <option value="Azithromycin">Azithromycin</option>
                        <option value="Ibuprofen">Ibuprofen</option>
                        <option value="Omeprazol">Omeprazol</option>
                        <option value="Losartan">Losartan</option>
                        <option value="Metformin">Metformin</option>
                        <option value="Cetirizine">Cetirizine</option>
                        <option value="Vitamin C">Vitamin C</option>
                        <option value="Vitamin B-complex">Vitamin B-complex</option>
                        <option value="Domperidone">Domperidone</option>
                        <option value="Amlodipine">Amlodipine</option>
                        <option value="Metoprolol">Metoprolol</option>
                        <option value="Atorvastatin">Atorvastatin</option>
                        <option value="Aspirin">Aspirin</option>
                    </select>
                    <input type="hidden" name="prescriptions[${index}][drug_name]" value="">
                </div>
            </div>
            <div class="col-md-2">
                <div class="editable-field" data-field="dosage" data-index="${index}">
                    <div class="field-value" data-placeholder="Liều dùng">Liều dùng</div>
                    <select class="field-dropdown" data-field="dosage" data-index="${index}">
                        <option value="">-- Chọn liều --</option>
                        <option value="500mg x 2 lần/ngày">500mg x 2 lần/ngày</option>
                        <option value="500mg x 3 lần/ngày">500mg x 3 lần/ngày</option>
                        <option value="250mg x 2 lần/ngày">250mg x 2 lần/ngày</option>
                        <option value="100mg x 1 lần/ngày">100mg x 1 lần/ngày</option>
                        <option value="50mg x 1 lần/ngày">50mg x 1 lần/ngày</option>
                        <option value="20mg x 1 lần/ngày">20mg x 1 lần/ngày</option>
                        <option value="10mg x 1 lần/ngày">10mg x 1 lần/ngày</option>
                        <option value="5mg x 1 lần/ngày">5mg x 1 lần/ngày</option>
                        <option value="1 viên x 1 lần/ngày">1 viên x 1 lần/ngày</option>
                        <option value="1 gói x 2 lần/ngày">1 gói x 2 lần/ngày</option>
                    </select>
                    <input type="hidden" name="prescriptions[${index}][dosage]" value="">
                </div>
            </div>
            <div class="col-md-2">
                <div class="editable-field" data-field="quantity" data-index="${index}">
                    <div class="field-value" data-placeholder="Số lượng">Số lượng</div>
                    <select class="field-dropdown" data-field="quantity" data-index="${index}">
                        <option value="">-- Số lượng --</option>
                        <option value="1">1</option>
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="60">60</option>
                        <option value="100">100</option>
                    </select>
                    <input type="hidden" name="prescriptions[${index}][quantity]" value="1">
                </div>
            </div>
            <div class="col-md-2">
                <div class="editable-field" data-field="duration_days" data-index="${index}">
                    <div class="field-value" data-placeholder="Số ngày">Số ngày</div>
                    <select class="field-dropdown" data-field="duration_days" data-index="${index}">
                        <option value="">-- Số ngày --</option>
                        <option value="3">3 ngày</option>
                        <option value="5">5 ngày</option>
                        <option value="7">7 ngày</option>
                        <option value="10">10 ngày</option>
                        <option value="14">14 ngày</option>
                        <option value="30">30 ngày</option>
                        <option value="60">60 ngày</option>
                        <option value="90">90 ngày</option>
                    </select>
                    <input type="hidden" name="prescriptions[${index}][duration_days]" value="30">
                </div>
            </div>
            <div class="col-md-2">
                <div class="editable-field" data-field="instructions" data-index="${index}">
                    <div class="field-value" data-placeholder="Hướng dẫn">Hướng dẫn</div>
                    <select class="field-dropdown" data-field="instructions" data-index="${index}">
                        <option value="">-- Hướng dẫn --</option>
                        <option value="Uống sau ăn">Uống sau ăn</option>
                        <option value="Uống trước ăn 30 phút">Uống trước ăn 30 phút</option>
                        <option value="Uống cùng bữa ăn">Uống cùng bữa ăn</option>
                        <option value="Uống lúc đói">Uống lúc đói</option>
                        <option value="Uống trước khi ngủ">Uống trước khi ngủ</option>
                        <option value="Pha với nước uống">Pha với nước uống</option>
                        <option value="Ngậm dưới lưỡi">Ngậm dưới lưỡi</option>
                        <option value="Uống với nhiều nước">Uống với nhiều nước</option>
                    </select>
                    <input type="hidden" name="prescriptions[${index}][instructions]" value="">
                </div>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="row-delete-btn" onclick="this.closest('.rx-row').remove()">✕</button>
            </div>
        </div>
    </div>`;
    document.getElementById('rxContainer').insertAdjacentHTML('beforeend', html);
    prescriptionCounter++;
    
    // Gán sự kiện cho editable-field mới
    const newRow = document.getElementById('rxContainer').lastElementChild;
    newRow.querySelectorAll('.editable-field').forEach(field => {
        field.addEventListener('click', function(e) {
            e.stopPropagation();
            handleEditableClick(this);
        });
    });
}

    // Thêm dòng chỉ định mới
    function addOrderRow() {
        const index = orderCounter;
        const html = `
        <div class="order-row">
            <div class="row g-2">
                <div class="col-md-2">
                    <select name="orders[${index}][order_type]" class="form-select">
                        <option value="lab">🧪 Xét nghiệm</option>
                        <option value="imaging">🩻 Hình ảnh</option>
                        <option value="other">📋 Khác</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="orders[${index}][order_name]" class="form-control" placeholder="Tên xét nghiệm">
                </div>
                <div class="col-md-5">
                    <input type="text" name="orders[${index}][description]" class="form-control" placeholder="Mô tả">
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="row-delete-btn" onclick="this.closest('.order-row').remove()">✕</button>
                </div>
            </div>
        </div>`;
        document.getElementById('orderContainer').insertAdjacentHTML('beforeend', html);
        orderCounter++;
    }
</script>
@endpush
