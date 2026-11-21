<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chi tiết nhật ký</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body{background:#f0f4f8}.card{border:none;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07)}
        .metric-card{text-align:center;padding:1.5rem .5rem}
        .metric-card .val{font-size:1.6rem;font-weight:700;line-height:1.1}
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-primary shadow-sm px-3 mb-4">
    <a class="navbar-brand fw-bold" href="{{ route('health-tracking.index') }}">
        <i class="bi bi-heart-pulse-fill me-2"></i>Health Tracker
    </a>
</nav>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('health-tracking.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h5 class="fw-bold mb-0"><i class="bi bi-file-medical text-primary me-2"></i>Chi tiết nhật ký</h5>
                        <small class="text-muted">{{ $tracking->created_at->format('H:i, d/m/Y') }}</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @can('update', $tracking)
                    <a href="{{ route('health-tracking.edit', $tracking) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Sửa
                    </a>
                    @endcan
                    @can('delete', $tracking)
                    <button class="btn btn-outline-danger btn-sm"
                        onclick="confirmDelete('{{ route('health-tracking.destroy', $tracking) }}','{{ csrf_token() }}')">
                        <i class="bi bi-trash me-1"></i>Xóa
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Flash --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex gap-2">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show d-flex gap-2">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('warning') }}
                <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- Risk summary --}}
            @php
            $alertClass = match($tracking->risk_level) {
                'danger'  => 'danger',
                'warning' => 'warning',
                default   => 'success',
            };
            $alertMsg = match($tracking->risk_level) {
                'danger'  => ['bi-exclamation-triangle-fill', 'Mức độ nguy hiểm! Có chỉ số cần liên hệ bác sĩ ngay.'],
                'warning' => ['bi-exclamation-circle-fill',   'Cần chú ý! Một số chỉ số cần theo dõi.'],
                default   => ['bi-check-circle-fill',          'Tất cả chỉ số trong ngưỡng an toàn.'],
            };
            @endphp
            <div class="alert alert-{{ $alertClass }} d-flex gap-3 align-items-center mb-4">
                <i class="bi {{ $alertMsg[0] }} fs-3 flex-shrink-0"></i>
                <div>{{ $alertMsg[1] }}</div>
            </div>

            {{-- Chi tiết cảnh báo --}}
            @if($tracking->risk_warnings && count($tracking->risk_warnings))
            <div class="card mb-4">
                <div class="card-header bg-{{ $alertClass }} {{ $alertClass==='warning'?'text-dark':'text-white' }}">
                    <i class="bi bi-exclamation-triangle me-2"></i>Chi tiết cảnh báo
                </div>
                <div class="list-group list-group-flush">
                    @foreach($tracking->risk_warnings as $w)
                    <div class="list-group-item d-flex align-items-center gap-3 py-3">
                        <i class="bi {{ $w['icon'] }} text-{{ $w['level']==='danger'?'danger':'warning' }} fs-5"></i>
                        <span class="flex-grow-1">{{ $w['message'] }}</span>
                        <span class="badge bg-{{ $w['level']==='danger'?'danger':'warning' }} {{ $w['level']!=='danger'?'text-dark':'' }}">
                            {{ $w['level']==='danger'?'Nguy hiểm':'Cảnh báo' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Metrics --}}
            @php
            $metrics = [
                ['label'=>'Huyết áp tâm thu', 'val'=>$tracking->systolic, 'unit'=>'mmHg', 'icon'=>'bi-arrow-up-circle text-danger',
                 'status'=> $tracking->systolic>180?'danger':($tracking->systolic>140?'warning':'normal')],
                ['label'=>'Huyết áp tâm trương','val'=>$tracking->diastolic,'unit'=>'mmHg','icon'=>'bi-arrow-down-circle text-warning',
                 'status'=> $tracking->diastolic>120?'danger':($tracking->diastolic>90?'warning':'normal')],
                ['label'=>'Nhịp tim', 'val'=>$tracking->heart_rate,'unit'=>'bpm','icon'=>'bi-activity text-success',
                 'status'=> ($tracking->heart_rate<40||$tracking->heart_rate>180)?'danger':(($tracking->heart_rate<50||$tracking->heart_rate>120)?'warning':'normal')],
                ['label'=>'SpO2', 'val'=>$tracking->spo2,'unit'=>'%','icon'=>'bi-lungs text-info',
                 'status'=> $tracking->spo2<90?'danger':($tracking->spo2<95?'warning':'normal')],
                ['label'=>'Cân nặng', 'val'=>$tracking->weight,'unit'=>'kg','icon'=>'bi-person text-secondary','status'=>'normal'],
                ['label'=>'Đường huyết','val'=>$tracking->blood_sugar,'unit'=>'mg/dL','icon'=>'bi-droplet text-danger',
                 'status'=> $tracking->blood_sugar>300?'danger':($tracking->blood_sugar>200||$tracking->blood_sugar<70?'warning':'normal')],
            ];
            $sc = ['normal'=>'success','warning'=>'warning','danger'=>'danger'];
            @endphp
            <div class="row g-3 mb-4">
                @foreach($metrics as $m)
                <div class="col-md-4 col-6">
                    <div class="card metric-card h-100">
                        <i class="bi {{ $m['icon'] }} fs-2 mb-2 d-block"></i>
                        <div class="text-muted small mb-1">{{ $m['label'] }}</div>
                        <div class="val text-{{ $sc[$m['status']] }}">{{ $m['val'] }}<small class="fs-6 fw-normal text-muted"> {{ $m['unit'] }}</small></div>
                        @if($m['status'] !== 'normal')
                        <span class="badge bg-{{ $sc[$m['status']] }} {{ $m['status']==='warning'?'text-dark':'' }} mt-2">
                            {{ $m['status']==='danger'?'Nguy hiểm':'Cảnh báo' }}
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            @if($tracking->symptoms)
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-chat-text me-2"></i>Triệu chứng</div>
                <div class="card-body">{{ $tracking->symptoms }}</div>
            </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4"><small class="text-muted d-block">Ngày tạo</small><span class="fw-semibold small">{{ $tracking->created_at->format('d/m/Y H:i') }}</span></div>
                        <div class="col-4"><small class="text-muted d-block">Cập nhật lần cuối</small><span class="fw-semibold small">{{ $tracking->updated_at->format('d/m/Y H:i') }}</span></div>
                        <div class="col-4"><small class="text-muted d-block">Phiên bản</small><span class="fw-semibold">#{{ $tracking->version }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center py-4">
                <i class="bi bi-trash3-fill text-danger fs-1 d-block mb-3"></i>
                <h6 class="fw-bold">Xác nhận xóa nhật ký này?</h6>
                <p class="text-muted small mb-4">Thao tác này không thể hoàn tác.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-danger" id="delConfirmBtn">Xóa</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let delUrl, delToken;
const delModal = new bootstrap.Modal(document.getElementById('delModal'));
function confirmDelete(url, token) { delUrl = url; delToken = token; delModal.show(); }
document.getElementById('delConfirmBtn').addEventListener('click', () => {
    const f = document.createElement('form');
    f.method = 'POST'; f.action = delUrl;
    f.innerHTML = `<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="${delToken}">`;
    document.body.appendChild(f); f.submit();
});
</script>
</body>
</html>
