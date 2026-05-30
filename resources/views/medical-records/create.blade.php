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
    
    /* Select2 style */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 6px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
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

                    @if(isset($appointment) && $appointment)
                    <input type="hidden" name="appointment_id" value="{{ $appointment->appointment_id }}">
                    @endif

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bệnh nhân <span class="text-danger">*</span></label>
                        <input type="text" name="patient_name" class="form-control"
                            value="{{ old('patient_name', $record->patient_name ?? $appointment?->user?->full_name) }}"
                            required>
                        <input type="hidden" name="patient_id" id="patient_id"
                            value="{{ old('patient_id', $record->patient_id ?? $appointment?->user_id) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Mã bệnh nhân</label>
                        <input type="text" name="patient_code" id="patient_code" class="form-control" readonly
                            value="{{ old('patient_code', $record->patient_code ?? ($appointment?->user_id ? 'BN' . str_pad((string) $appointment->user_id, 6, '0', STR_PAD_LEFT) : '')) }}">
                        <small class="text-muted" id="patientCodeHint">Mã bệnh nhân tự động, không cho phép sửa.</small>
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
                            <input type="number" name="vitals[heart_rate]" class="form-control" step="0.1" placeholder="Bình thường: 60 - 100 bpm"
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
                            <input type="number" name="vitals[temperature]" class="form-control" step="0.1" placeholder="Bình thường: 36.5 - 37.5 °C"
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
                        <input type="number" name="vitals[spo2]" class="form-control" step="0.1"placeholder="Bình thường: 95 - 100 %"
                            value="{{ old('vitals.spo2', $v?->spo2) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Cân nặng (kg) <span class="text-danger">*</span></label>
                        <input type="number" name="vitals[weight]" class="form-control" step="1"
                            value="{{ old('vitals.weight', $v?->weight) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Đường huyết (mmol/L)</label>
                        <div class="input-group">
                            <input type="number" name="vitals[blood_sugar]" class="form-control" step="0.01" placeholder="Bình thường: 3.9 - 7.8 mmol/L"
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

        {{-- ── Dị ứng (CÓ DROPDOWN) ───────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">⚠️ Dị ứng</div>
            <div class="form-section-body">
                <div id="allergyContainer">
                    @forelse($record->allergies ?? [] as $i => $allergy)
                    <div class="allergy-row">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <select name="allergies[{{ $i }}][allergen]" class="form-select allergy-select" onchange="handleAllergySelect(this)">
                                    <option value="">-- Chọn chất gây dị ứng --</option>
                                    @foreach($commonAllergens ?? ['Thuốc', 'Thức ăn', 'Phấn hoa', 'Bụi nhà', 'Lông động vật', 'Hải sản', 'Đậu phộng', 'Sữa', 'Trứng', 'Cao su'] as $allergen)
                                    <option value="{{ $allergen }}" {{ $allergy->allergen == $allergen ? 'selected' : '' }}>
                                        {{ $allergen }}
                                    </option>
                                    @endforeach
                                    <option value="other">-- Khác (nhập tay) --</option>
                                </select>
                                <input type="text" name="allergies[{{ $i }}][allergen_custom]" class="form-control mt-1" 
                                    style="display: {{ !in_array($allergy->allergen, ($commonAllergens ?? [])) && $allergy->allergen ? 'block' : 'none' }};" 
                                    placeholder="Nhập chất gây dị ứng khác"
                                    value="{{ !in_array($allergy->allergen, ($commonAllergens ?? [])) ? $allergy->allergen : '' }}">
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

        {{-- ── Chẩn đoán (CÓ DROPDOWN) ────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">🩺 Chẩn đoán <span class="text-danger">*</span></div>
            <div class="form-section-body">
                <div id="diagnosisContainer">
                    @forelse($record->diagnoses ?? [] as $i => $diag)
                    <div class="diagnosis-row">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <select name="diagnoses[{{ $i }}][diagnosis_name]" class="form-select diagnosis-select" onchange="handleDiagnosisSelect(this)">
                                    <option value="">-- Chọn chẩn đoán --</option>
                                    @foreach($commonDiagnoses ?? ['Cảm cúm', 'Viêm họng', 'Viêm phổi', 'Đau dạ dày', 'Cao huyết áp', 'Tiểu đường', 'Viêm da', 'Hen suyễn', 'Viêm khớp', 'Đau đầu'] as $diagnosis)
                                    <option value="{{ $diagnosis }}" {{ $diag->diagnosis_name == $diagnosis ? 'selected' : '' }}>
                                        {{ $diagnosis }}
                                    </option>
                                    @endforeach
                                    <option value="other">-- Khác (nhập tay) --</option>
                                </select>
                                <input type="text" name="diagnoses[{{ $i }}][diagnosis_name_custom]" class="form-control mt-1" 
                                    style="display: {{ !in_array($diag->diagnosis_name, ($commonDiagnoses ?? [])) && $diag->diagnosis_name ? 'block' : 'none' }};" 
                                    placeholder="Nhập chẩn đoán khác"
                                    value="{{ !in_array($diag->diagnosis_name, ($commonDiagnoses ?? [])) ? $diag->diagnosis_name : '' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="diagnoses[{{ $i }}][icd_code]" class="form-control"
                                    placeholder="Mã ICD" value="{{ $diag->icd_code }}">
                            </div>
                            <div class="col-md-3">
                                <select name="diagnoses[{{ $i }}][diagnosis_type]" class="form-select">
                                    <option value="primary" {{ $diag->diagnosis_type === 'primary' ? 'selected' : '' }}>Chính</option>
                                    <option value="secondary" {{ $diag->diagnosis_type === 'secondary' ? 'selected' : '' }}>Phụ</option>
                                    <option value="complication" {{ $diag->diagnosis_type === 'complication' ? 'selected' : '' }}>Biến chứng</option>
                                </select>
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="row-delete-btn" onclick="this.closest('.diagnosis-row').remove()">✕</button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <textarea name="diagnoses[{{ $i }}][note]" class="form-control" rows="1" placeholder="Ghi chú chẩn đoán">{{ $diag->note ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
                <button type="button" class="add-row-btn" onclick="addDiagnosis()">+ Thêm chẩn đoán</button>
            </div>
        </div>

        {{-- ── Đơn thuốc (CÓ DROPDOWN) ────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">💊 Đơn thuốc</div>
            <div class="form-section-body">
                <div id="rxContainer">
                    @forelse($record->prescriptions ?? [] as $i => $rx)
                    <div class="rx-row">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select name="prescriptions[{{ $i }}][drug_name]" class="form-select drug-select" onchange="handleDrugSelect(this)">
                                    <option value="">-- Chọn thuốc --</option>
                                    @foreach($commonDrugs ?? ['Paracetamol', 'Amoxicillin', 'Ibuprofen', 'Omeprazole', 'Loratadine', 'Vitamin C', 'Aspirin', 'Metformin', 'Amlodipine', 'Cetirizine'] as $drug)
                                    <option value="{{ $drug }}" {{ $rx->drug_name == $drug ? 'selected' : '' }}>
                                        {{ $drug }}
                                    </option>
                                    @endforeach
                                    <option value="other">-- Khác (nhập tay) --</option>
                                </select>
                                <input type="text" name="prescriptions[{{ $i }}][drug_name_custom]" class="form-control mt-1" 
                                    style="display: {{ !in_array($rx->drug_name, ($commonDrugs ?? [])) && $rx->drug_name ? 'block' : 'none' }};" 
                                    placeholder="Nhập tên thuốc khác"
                                    value="{{ !in_array($rx->drug_name, ($commonDrugs ?? [])) ? $rx->drug_name : '' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="prescriptions[{{ $i }}][dosage]" class="form-control"
                                    placeholder="Liều dùng" value="{{ $rx->dosage }}">
                            </div>
                            <div class="col-md-2">
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

        {{-- ── Chỉ định xét nghiệm (CÓ DROPDOWN) ──────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-header">🔬 Chỉ định xét nghiệm / hình ảnh</div>
            <div class="form-section-body">
                <div id="orderContainer">
                    @forelse($record->medicalOrders ?? [] as $i => $order)
                    <div class="order-row">
                        <div class="row g-2">
                            <div class="col-md-2">
                                <select name="orders[{{ $i }}][order_type]" class="form-select">
                                    <option value="lab" {{ $order->order_type === 'lab' ? 'selected' : '' }}>🧪 Xét nghiệm</option>
                                    <option value="imaging" {{ $order->order_type === 'imaging' ? 'selected' : '' }}>🩻 Hình ảnh</option>
                                    <option value="other" {{ $order->order_type === 'other' ? 'selected' : '' }}>📋 Khác</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="orders[{{ $i }}][order_name]" class="form-select order-select" onchange="handleOrderSelect(this)">
                                    <option value="">-- Chọn chỉ định --</option>
                                    @foreach($commonOrders ?? ['Xét nghiệm máu', 'X-quang', 'Siêu âm', 'CT Scanner', 'MRI', 'Nội soi', 'Điện tâm đồ', 'Xét nghiệm nước tiểu', 'Test COVID', 'Xét nghiệm chức năng gan'] as $orderName)
                                    <option value="{{ $orderName }}" {{ $order->order_name == $orderName ? 'selected' : '' }}>
                                        {{ $orderName }}
                                    </option>
                                    @endforeach
                                    <option value="other">-- Khác (nhập tay) --</option>
                                </select>
                                <input type="text" name="orders[{{ $i }}][order_name_custom]" class="form-control mt-1" 
                                    style="display: {{ !in_array($order->order_name, ($commonOrders ?? [])) && $order->order_name ? 'block' : 'none' }};" 
                                    placeholder="Nhập chỉ định khác"
                                    value="{{ !in_array($order->order_name, ($commonOrders ?? [])) ? $order->order_name : '' }}">
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
$commonAllergensJson = json_encode($commonAllergens ?? ['Thuốc', 'Thức ăn', 'Phấn hoa', 'Bụi nhà', 'Lông động vật', 'Hải sản', 'Đậu phộng', 'Sữa', 'Trứng', 'Cao su']);
$commonDiagnosesJson = json_encode($commonDiagnoses ?? ['Cảm cúm', 'Viêm họng', 'Viêm phổi', 'Đau dạ dày', 'Cao huyết áp', 'Tiểu đường', 'Viêm da', 'Hen suyễn', 'Viêm khớp', 'Đau đầu']);
$commonDrugsJson = json_encode($commonDrugs ?? ['Paracetamol', 'Amoxicillin', 'Ibuprofen', 'Omeprazole', 'Loratadine', 'Vitamin C', 'Aspirin', 'Metformin', 'Amlodipine', 'Cetirizine']);
$commonOrdersJson = json_encode($commonOrders ?? ['Xét nghiệm máu', 'X-quang', 'Siêu âm', 'CT Scanner', 'MRI', 'Nội soi', 'Điện tâm đồ', 'Xét nghiệm nước tiểu', 'Test COVID', 'Xét nghiệm chức năng gan']);
@endphp
<script>
let ai = {{ $aiCount }};
let di = {{ $diCount }};
let ri = {{ $riCount }};
let oi = {{ $oiCount }};
let commonAllergens = {!! $commonAllergensJson !!};
let commonDiagnoses = {!! $commonDiagnosesJson !!};
let commonDrugs = {!! $commonDrugsJson !!};
let commonOrders = {!! $commonOrdersJson !!};

// ── TỰ ĐỘNG TẠO MÃ BỆNH NHÂN ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const patientIdInput = document.querySelector('[name="patient_id"]');
    const patientCodeInput = document.getElementById('patient_code');
    const patientNameInput = document.querySelector('[name="patient_name"]');
    
    function generatePatientCode(patientId) {
        if (!patientId) return '';
        return 'BN' + String(patientId).padStart(6, '0');
    }
    
    if (patientCodeInput && !patientCodeInput.value.trim()) {
        const patientId = patientIdInput?.value;
        const newCode = generatePatientCode(patientId);
        patientCodeInput.value = newCode;
        document.getElementById('patientCodeHint').innerHTML = 'Mã bệnh nhân tự động: <strong>' + newCode + '</strong>';
        document.getElementById('patientCodeHint').style.color = '#28a745';
    }
});

// ── XỬ LÝ DROPDOWN DỊ ỨNG ────────────────────────────────────────────────
function handleAllergySelect(selectEl) {
    const row = selectEl.closest('.allergy-row');
    const customInput = row.querySelector('[name*="[allergen_custom]"]');
    if (selectEl.value === 'other') {
        customInput.style.display = 'block';
        customInput.required = true;
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }
}

// ── XỬ LÝ DROPDOWN CHẨN ĐOÁN ────────────────────────────────────────────────
function handleDiagnosisSelect(selectEl) {
    const row = selectEl.closest('.diagnosis-row');
    const customInput = row.querySelector('[name*="[diagnosis_name_custom]"]');
    if (selectEl.value === 'other') {
        customInput.style.display = 'block';
        customInput.required = true;
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }
}

// ── XỬ LÝ DROPDOWN THUỐC ────────────────────────────────────────────────
function handleDrugSelect(selectEl) {
    const row = selectEl.closest('.rx-row');
    const customInput = row.querySelector('[name*="[drug_name_custom]"]');
    if (selectEl.value === 'other') {
        customInput.style.display = 'block';
        customInput.required = true;
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }
}

// ── XỬ LÝ DROPDOWN CHỈ ĐỊNH ────────────────────────────────────────────────
function handleOrderSelect(selectEl) {
    const row = selectEl.closest('.order-row');
    const customInput = row.querySelector('[name*="[order_name_custom]"]');
    if (selectEl.value === 'other') {
        customInput.style.display = 'block';
        customInput.required = true;
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }
}

// ── THÊM DÒNG MỚI ────────────────────────────────────────────────

function addAllergy() {
    const index = Date.now();
    const optionsHtml = commonAllergens.map(a => `<option value="${a}">${a}</option>`).join('');
    document.getElementById('allergyContainer').insertAdjacentHTML('beforeend', `
    <div class="allergy-row" data-index="${index}">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <select name="allergies[${index}][allergen]" class="form-select allergy-select" onchange="handleAllergySelect(this)">
                    <option value="">-- Chọn chất gây dị ứng --</option>
                    ${optionsHtml}
                    <option value="other">-- Khác (nhập tay) --</option>
                </select>
                <input type="text" name="allergies[${index}][allergen_custom]" class="form-control mt-1" 
                    style="display: none;" placeholder="Nhập chất gây dị ứng khác">
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
    const index = di;
    const optionsHtml = commonDiagnoses.map(d => `<option value="${d}">${d}</option>`).join('');
    document.getElementById('diagnosisContainer').insertAdjacentHTML('beforeend', `
    <div class="diagnosis-row">
        <div class="row g-2">
            <div class="col-md-5">
                <select name="diagnoses[${index}][diagnosis_name]" class="form-select diagnosis-select" onchange="handleDiagnosisSelect(this)">
                    <option value="">-- Chọn chẩn đoán --</option>
                    ${optionsHtml}
                    <option value="other">-- Khác (nhập tay) --</option>
                </select>
                <input type="text" name="diagnoses[${index}][diagnosis_name_custom]" class="form-control mt-1" 
                    style="display: none;" placeholder="Nhập chẩn đoán khác">
            </div>
            <div class="col-md-2">
                <input type="text" name="diagnoses[${index}][icd_code]" class="form-control" placeholder="Mã ICD">
            </div>
            <div class="col-md-3">
                <select name="diagnoses[${index}][diagnosis_type]" class="form-select">
                    <option value="primary">Chính</option>
                    <option value="secondary">Phụ</option>
                    <option value="complication">Biến chứng</option>
                </select>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="row-delete-btn" onclick="this.closest('.diagnosis-row').remove()">✕</button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <textarea name="diagnoses[${index}][note]" class="form-control" rows="1" placeholder="Ghi chú chẩn đoán"></textarea>
            </div>
        </div>
    </div>`);
    di++;
}

function addRx() {
    const index = ri;
    const optionsHtml = commonDrugs.map(d => `<option value="${d}">${d}</option>`).join('');
    document.getElementById('rxContainer').insertAdjacentHTML('beforeend', `
    <div class="rx-row">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="prescriptions[${index}][drug_name]" class="form-select drug-select" onchange="handleDrugSelect(this)">
                    <option value="">-- Chọn thuốc --</option>
                    ${optionsHtml}
                    <option value="other">-- Khác (nhập tay) --</option>
                </select>
                <input type="text" name="prescriptions[${index}][drug_name_custom]" class="form-control mt-1" 
                    style="display: none;" placeholder="Nhập tên thuốc khác">
            </div>
            <div class="col-md-2">
                <input type="text" name="prescriptions[${index}][dosage]" class="form-control" placeholder="Liều dùng">
            </div>
            <div class="col-md-2">
                <input type="number" name="prescriptions[${index}][quantity]" class="form-control" placeholder="Số lượng" value="1" min="1">
            </div>
            <div class="col-md-2">
                <input type="number" name="prescriptions[${index}][duration_days]" class="form-control" placeholder="Số ngày" value="30" min="1">
            </div>
            <div class="col-md-2">
                <input type="text" name="prescriptions[${index}][instructions]" class="form-control" placeholder="Hướng dẫn">
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="row-delete-btn" onclick="this.closest('.rx-row').remove()">✕</button>
            </div>
        </div>
    </div>`);
    ri++;
}

function addOrder() {
    const index = oi;
    const optionsHtml = commonOrders.map(o => `<option value="${o}">${o}</option>`).join('');
    document.getElementById('orderContainer').insertAdjacentHTML('beforeend', `
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
                <select name="orders[${index}][order_name]" class="form-select order-select" onchange="handleOrderSelect(this)">
                    <option value="">-- Chọn chỉ định --</option>
                    ${optionsHtml}
                    <option value="other">-- Khác (nhập tay) --</option>
                </select>
                <input type="text" name="orders[${index}][order_name_custom]" class="form-control mt-1" 
                    style="display: none;" placeholder="Nhập chỉ định khác">
            </div>
            <div class="col-md-5">
                <input type="text" name="orders[${index}][description]" class="form-control" placeholder="Mô tả">
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="row-delete-btn" onclick="this.closest('.order-row').remove()">✕</button>
            </div>
        </div>
    </div>`);
    oi++;
}

// ── VALIDATION TRƯỚC KHI SUBMIT ───────────────────────────────────
document.getElementById('medicalRecordForm')?.addEventListener('submit', function(e) {
    let errors = [];
    
    // Xử lý custom fields
    document.querySelectorAll('.allergy-row, .diagnosis-row, .rx-row, .order-row').forEach(row => {
        const selectEl = row.querySelector('select[onchange*="handle"]');
        if (!selectEl) return;
        
        const customInput = row.querySelector('[class*="custom"]');
        const hiddenField = document.createElement('input');
        const originalName = selectEl.getAttribute('name');
        
        if (selectEl.value === 'other') {
            if (customInput && customInput.value.trim()) {
                hiddenField.type = 'hidden';
                hiddenField.name = originalName;
                hiddenField.value = customInput.value.trim();
                row.appendChild(hiddenField);
                selectEl.disabled = true;
            } else if (customInput) {
                errors.push(`⚠️ Vui lòng nhập giá trị cho mục "${selectEl.closest('.form-section-header')?.innerText?.trim() || 'này'}"`);
                customInput.classList.add('is-invalid');
            }
        }
    });
    
    // 1. Lý do khám
    const chiefComplaint = document.querySelector('[name="chief_complaint"]');
    if (!chiefComplaint?.value.trim()) {
        errors.push('⚠️ Vui lòng nhập lý do đến khám / triệu chứng');
        chiefComplaint?.classList.add('is-invalid');
    }

    // 2. Chỉ số sinh tồn
    const vitals = ['vitals[blood_pressure]', 'vitals[heart_rate]', 'vitals[temperature]', 'vitals[spo2]', 'vitals[weight]'];
    const vitalLabels = ['huyết áp', 'nhịp tim', 'nhiệt độ', 'SpO2', 'cân nặng'];
    vitals.forEach((name, idx) => {
        const el = document.querySelector(`[name="${name}"]`);
        if (!el?.value) {
            errors.push(`⚠️ Vui lòng nhập ${vitalLabels[idx]}`);
            el?.classList.add('is-invalid');
        }
    });

    // 3. Chẩn đoán (ít nhất 1)
    const diagSelects = document.querySelectorAll('[name*="diagnoses"][name*="[diagnosis_name]"]:not([disabled])');
    let hasValidDiagnosis = false;
    diagSelects.forEach(select => {
        if (select.value && select.value !== 'other') {
            hasValidDiagnosis = true;
        }
    });
    const diagCustoms = document.querySelectorAll('[name*="diagnoses"][name*="[diagnosis_name_custom]"]');
    diagCustoms.forEach(input => {
        if (input.value.trim() && input.style.display !== 'none') {
            hasValidDiagnosis = true;
        }
    });
    if (!hasValidDiagnosis) {
        errors.push('⚠️ Vui lòng thêm ít nhất 1 chẩn đoán');
    }

    if (errors.length > 0) {
        e.preventDefault();
        alert('⚠️ Vui lòng kiểm tra lại:\n\n' + errors.join('\n'));
        const firstError = document.querySelector('.is-invalid');
        firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
});

// Real-time validation
document.addEventListener('input', function(e) {
    if (e.target.matches('input, select, textarea') && e.target.value.trim()) {
        e.target.classList.remove('is-invalid');
    }
});
</script>
@endpush
