{{-- resources/views/medical-records/print.blade.php --}}
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Hồ Sơ Bệnh Án - {{ $record->record_code }}</title>
<style>
* { box-sizing: border-box; }
body { font-family: 'Times New Roman', serif; font-size: 13px; color: #000; padding: 20px 30px; }
h1 { text-align: center; font-size: 18px; margin-bottom: 4px; }
.subtitle { text-align: center; font-size: 13px; color: #555; margin-bottom: 16px; }
hr { border: 1px solid #000; margin: 10px 0; }
.section-title { font-weight: bold; font-size: 14px; border-bottom: 1px solid #aaa; padding-bottom: 3px; margin: 14px 0 6px; }
.info-row { display: flex; gap: 30px; margin-bottom: 6px; }
.info-row span { min-width: 140px; font-weight: bold; }
.vitals-table, .rx-table, .order-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.vitals-table td, .rx-table td, .rx-table th, .order-table td, .order-table th {
    border: 1px solid #999; padding: 5px 8px; font-size: 12px;
}
.rx-table th, .order-table th { background: #f0f0f0; font-weight: bold; }
.diagnosis-box { border-left: 3px solid #333; padding: 4px 8px; margin-bottom: 5px; background: #fafafa; }
.allergy-warning { border: 1px solid #c00; padding: 6px 10px; background: #fff5f5; margin-bottom: 10px; }
.signature-row { display: flex; justify-content: space-between; margin-top: 30px; }
.signature-box { text-align: center; width: 200px; }
.signature-box .line { border-bottom: 1px solid #000; height: 50px; margin: 8px 0; }
@media print { body { padding: 0; } }
</style>
</head>
<body>

<h1>HỒ SƠ BỆNH ÁN CHI TIẾT</h1>
<p class="subtitle">Phiếu khám: <strong>{{ $record->record_code }}</strong> &nbsp;|&nbsp; Ngày: {{ $record->exam_date->format('d/m/Y') }}</p>
<hr>

@if($record->allergies->count())
<div class="allergy-warning">
    ⚠️ <strong>CẢNH BÁO DỊ ỨNG:</strong>
    {{ $record->allergies->pluck('allergen')->implode(', ') }}
    — Kiểm tra kỹ trước khi kê đơn
</div>
@endif

<div class="section-title">I. THÔNG TIN CHUNG</div>
<div class="info-row"><span>Mã phiếu:</span> {{ $record->record_code }}</div>
<div class="info-row"><span>Bệnh nhân:</span> {{ $record->patient_name }} &nbsp;|&nbsp; <span>Mã BN:</span> {{ $record->patient_code ?? '—' }}</div>
<div class="info-row"><span>Ngày khám:</span> {{ $record->exam_date->format('d/m/Y') }} {{ $record->exam_time ? 'lúc ' . \Carbon\Carbon::parse($record->exam_time)->format('H:i') : '' }}</div>
<div class="info-row"><span>Bác sĩ:</span> BS. {{ $record->doctor_name }}</div>
<div class="info-row"><span>Loại khám:</span> {{ $record->visit_type_label }}</div>
@if($record->chief_complaint)
<div class="info-row"><span>Lý do khám:</span> {{ $record->chief_complaint }}</div>
@endif

@if($record->vitalSigns)
<div class="section-title">II. CHỈ SỐ SINH TỒN</div>
@php $v = $record->vitalSigns; @endphp
<table class="vitals-table">
    <tr>
        <td><strong>Huyết áp</strong><br>{{ $v->blood_pressure ?? '—' }} mmHg</td>
        <td><strong>Nhịp tim</strong><br>{{ $v->heart_rate ?? '—' }} bpm</td>
        <td><strong>Nhiệt độ</strong><br>{{ $v->temperature ?? '—' }} °C</td>
        <td><strong>SpO2</strong><br>{{ $v->spo2 ?? '—' }} %</td>
        <td><strong>Cân nặng</strong><br>{{ $v->weight ?? '—' }} kg</td>
        <td><strong>Đường huyết</strong><br>{{ $v->blood_sugar ?? '—' }} mmol/L</td>
    </tr>
</table>
@endif

@if($record->diagnoses->count())
<div class="section-title">III. CHẨN ĐOÁN</div>
@foreach($record->diagnoses as $diag)
<div class="diagnosis-box">
    <strong>{{ $diag->diagnosis_name }}</strong>
    @if($diag->icd_code) <em>({{ $diag->icd_code }})</em> @endif
    — <em>{{ ['primary'=>'Chẩn đoán chính','secondary'=>'Chẩn đoán phụ','complication'=>'Biến chứng'][$diag->diagnosis_type] }}</em>
</div>
@endforeach
@endif

@if($record->prescriptions->count())
<div class="section-title">IV. ĐƠN THUỐC</div>
<table class="rx-table">
    <thead>
        <tr><th>#</th><th>Tên thuốc</th><th>Liều dùng</th><th>Hướng dẫn</th><th>Số ngày</th></tr>
    </thead>
    <tbody>
        @foreach($record->prescriptions as $i => $rx)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $rx->drug_name }}</td>
            <td>{{ $rx->dosage }}</td>
            <td>{{ $rx->instructions }}</td>
            <td>{{ $rx->duration_days }} ngày</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if($record->medicalOrders->count())
<div class="section-title">V. CHỈ ĐỊNH XÉT NGHIỆM / HÌNH ẢNH</div>
<table class="order-table">
    <thead><tr><th>#</th><th>Loại</th><th>Tên</th><th>Mô tả</th><th>Trạng thái</th></tr></thead>
    <tbody>
        @foreach($record->medicalOrders as $i => $order)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ ['lab'=>'Xét nghiệm','imaging'=>'Hình ảnh','other'=>'Khác'][$order->order_type] }}</td>
            <td>{{ $order->order_name }}</td>
            <td>{{ $order->description }}</td>
            <td>{{ $order->result_status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="signature-row">
    <div class="signature-box">
        <div>Ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}</div>
        <div class="line"></div>
        <strong>Bệnh nhân ký tên</strong>
    </div>
    <div class="signature-box">
        <div>Ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}</div>
        <div class="line"></div>
        <strong>Bác sĩ phụ trách</strong><br>
        <em>{{ $record->doctor_name }}</em>
    </div>
</div>

<script>window.print();</script>
</body>
</html>
