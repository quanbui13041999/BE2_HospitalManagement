{{-- resources/views/medical-records/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Danh sách hồ sơ bệnh án')

@section('content')
<div style="max-width:1200px;margin:20px auto;padding:0 16px">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">📁 Danh Sách Phiếu Khám</h5>
    @if(auth()->user()->role === 'doctor' || auth()->user()->user_type === 'doctor' || auth()->user()->isDoctor())
       
        
    @endif
      <a href="{{ url('/') }}" class="btn btn-warning btn-sm">
            <i class="bi bi-house-door-fill"></i> Trang chủ
</a>
</div>

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

<!-- FORM TÌM KIẾM NÂNG CAO -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('medical-records.index') }}" id="searchForm">
            @if(request('patient_id'))
                <input type="hidden" name="patient_id" value="{{ request('patient_id') }}">
            @endif
            <div class="row g-3">
                <!-- Ô tìm kiếm chính -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-1" style="font-size: 12px; color: #555;">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </label>
                    <input type="text" 
                           name="search" 
                           class="form-control form-control-sm" 
                           placeholder="Mã phiếu, tên bệnh nhân, bác sĩ..."
                           value="{{ request('search') }}">
                </div>

                <!-- Lọc theo loại khám -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1" style="font-size: 12px; color: #555;">
                        <i class="bi bi-tag"></i> Loại khám
                    </label>
                    <select name="visit_type" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($visitTypes ?? [] as $type)
                            <option value="{{ $type }}" {{ request('visit_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Lọc theo trạng thái -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1" style="font-size: 12px; color: #555;">
                        <i class="bi bi-flag"></i> Trạng thái
                    </label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($statuses ?? [] as $key => $status)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Từ ngày -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1" style="font-size: 12px; color: #555;">
                        <i class="bi bi-calendar"></i> Từ ngày
                    </label>
                    <input type="date" 
                           name="date_from" 
                           class="form-control form-control-sm" 
                           value="{{ request('date_from') }}">
                </div>

                <!-- Đến ngày -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-1" style="font-size: 12px; color: #555;">
                        <i class="bi bi-calendar"></i> Đến ngày
                    </label>
                    <input type="date" 
                           name="date_to" 
                           class="form-control form-control-sm" 
                           value="{{ request('date_to') }}">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <i class="bi bi-search"></i> Tìm kiếm
                            </button>
                            <a href="{{ route('medical-records.index') }}" class="btn btn-secondary btn-sm px-3">
                                <i class="bi bi-arrow-repeat"></i> Xóa bộ lọc
                            </a>
                        </div>
                        
                        <div class="d-flex gap-2 align-items-center">
                            <label class="text-muted" style="font-size: 12px;">Sắp xếp:</label>
                            <select name="sort_by" form="searchForm" class="form-select form-select-sm" style="width: auto;">
                                <option value="exam_date" {{ request('sort_by', 'exam_date') == 'exam_date' ? 'selected' : '' }}>Ngày khám</option>
                                <option value="record_code" {{ request('sort_by') == 'record_code' ? 'selected' : '' }}>Mã phiếu</option>
                                <option value="patient_name" {{ request('sort_by') == 'patient_name' ? 'selected' : '' }}>Tên bệnh nhân</option>
                            </select>
                            <select name="sort_order" form="searchForm" class="form-select form-select-sm" style="width: auto;">
                                <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Cũ nhất</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- HIỂN THỊ THÔNG TIN BỘ LỌC -->
@if(request()->filled('search') || request()->filled('visit_type') || request()->filled('status') || request()->filled('date_from') || request()->filled('date_to'))
<div class="alert alert-info py-2 mb-3" style="font-size: 13px;">
    <i class="bi bi-funnel-fill"></i> 
    <strong>Đang lọc:</strong>
    @if(request('search')) <span class="badge bg-secondary">Tìm: {{ request('search') }}</span> @endif
    @if(request('visit_type')) <span class="badge bg-info">Loại: {{ request('visit_type') }}</span> @endif
    @if(request('status')) <span class="badge bg-warning">Trạng thái: {{ $statuses[request('status')] ?? request('status') }}</span> @endif
    @if(request('date_from')) <span class="badge bg-success">Từ: {{ \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') }}</span> @endif
    @if(request('date_to')) <span class="badge bg-success">Đến: {{ \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') }}</span> @endif
    <span class="badge bg-primary">Tổng: <strong>{{ $records->total() }}</strong> kết quả</span>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13.5px">
            <thead style="background:#f8f9fa;font-size:12px;color:#666;">
                <tr>
                    <th>
                        <a href="{{ route('medical-records.index', array_merge(request()->all(), ['sort_by' => 'record_code', 'sort_order' => (request('sort_by') == 'record_code' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}" 
                           class="text-decoration-none text-dark">
                            Mã phiếu
                            @if(request('sort_by') == 'record_code')
                                <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('medical-records.index', array_merge(request()->all(), ['sort_by' => 'patient_name', 'sort_order' => (request('sort_by') == 'patient_name' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}" 
                           class="text-decoration-none text-dark">
                            Bệnh nhân
                            @if(request('sort_by') == 'patient_name')
                                <i class="bi bi-arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>Bác sĩ</th>
                    <th>
                        <a href="{{ route('medical-records.index', array_merge(request()->all(), ['sort_by' => 'exam_date', 'sort_order' => (request('sort_by') == 'exam_date' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}" 
                           class="text-decoration-none text-dark">
                            Ngày / giờ khám
                            @if(request('sort_by', 'exam_date') == 'exam_date')
                                <i class="bi bi-arrow-{{ request('sort_order', 'desc') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>Loại khám</th>
                    <th>Chẩn đoán chính</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td>
                        <a href="{{ route('medical-records.show', $record->record_id) }}"
                           style="color:#1a6fb3;font-weight:600;text-decoration:none">
                            {{ $record->record_code }}
                        </a>
                    </td>
                    <td>{{ $record->patient_name }}</td>
                    <td>BS. {{ $record->doctor_name }}</td>
                    <td>
                        {{ $record->exam_date?->format('d/m/Y') ?? '—' }}
                        @if($record->exam_time)
                            <div class="text-muted" style="font-size:12px">{{ \Carbon\Carbon::parse($record->exam_time)->format('H:i') }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="background:#eaf4ff;color:#1a6fb3;padding:2px 8px;border-radius:10px;font-size:12px">
                            {{ $record->visit_type }}
                        </span>
                    </td>
                    <td>
                        @if($record->diagnoses->isNotEmpty())
                        {{ Str::limit($record->diagnoses->first()->diagnosis_name, 40) }}
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @php
                        $statusColors = [
                            'pending' => ['bg' => '#fff3cd', 'color' => '#856404', 'text' => '⏳ Chờ khám'],
                            'examining' => ['bg' => '#d1ecf1', 'color' => '#0c5460', 'text' => '🔍 Đang khám'],
                            'completed' => ['bg' => '#d4edda', 'color' => '#155724', 'text' => '✅ Đã khám'],
                            'prescribed' => ['bg' => '#d1ecf1', 'color' => '#0c5460', 'text' => '💊 Đã kê đơn'],
                            'follow_up' => ['bg' => '#fff3cd', 'color' => '#856404', 'text' => '🔄 Tái khám'],
                            'emergency' => ['bg' => '#f8d7da', 'color' => '#721c24', 'text' => '🚨 Cấp cứu'],
                            'cancelled' => ['bg' => '#e2e3e5', 'color' => '#383d41', 'text' => '❌ Đã hủy'],
                        ];
                        $status = $statusColors[$record->status] ?? ['bg' => '#e2e3e5', 'color' => '#383d41', 'text' => '📋 ' . ($record->status ?? 'Chưa xác định')];
                        @endphp
                        <span style="background:{{ $status['bg'] }}; color:{{ $status['color'] }}; padding:2px 8px; border-radius:10px; font-size:12px; white-space: nowrap;">
                            {{ $status['text'] }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('medical-records.show', $record->record_id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Xem
                        </a>
                        @php
                            $canEditRecord = auth()->user()->isAdmin()
                                || (auth()->user()->isDoctor() && (int) $record->doctor_id === (int) auth()->id());
                        @endphp
                        @if($canEditRecord)
                            <a href="{{ route('medical-records.edit', $record->record_id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i> Sửa
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                        Không tìm thấy phiếu khám nào
                        @if(request()->filled('search') || request()->filled('visit_type') || request()->filled('status') || request()->filled('date_from') || request()->filled('date_to'))
                            <br>
                            <a href="{{ route('medical-records.index') }}" class="btn btn-sm btn-primary mt-2">
                                Xóa bộ lọc
                            </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- PHÂN TRANG -->
    @if($records->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
            <div class="text-muted" style="font-size: 13px;">
                <i class="bi bi-info-circle"></i>
                Hiển thị <strong>{{ $records->firstItem() ?? 0 }}</strong> - 
                <strong>{{ $records->lastItem() ?? 0 }}</strong> 
                trong tổng số <strong>{{ $records->total() }}</strong> kết quả
            </div>
            <div>
                {{ $records->appends(request()->query())->links() }}
            </div>
            <div>
                <label class="text-muted me-2" style="font-size: 13px;">Hiển thị:</label>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                    @foreach([10, 20, 50, 100] as $size)
                        <option value="{{ route('medical-records.index', array_merge(request()->all(), ['per_page' => $size])) }}" 
                                {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                            {{ $size }} / trang
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @else
        @if($records->total() > 0)
        <div class="card-footer bg-white text-muted" style="font-size: 13px;">
            <i class="bi bi-info-circle"></i>
            Tổng số <strong>{{ $records->total() }}</strong> kết quả
        </div>
        @endif
    @endif
</div>

<style>
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transition: all 0.2s ease;
    }
    
    .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
    }
    
    .page-link {
        color: #1a6fb3;
        font-size: 13px;
        padding: 6px 12px;
    }
    
    .page-item.active .page-link {
        background-color: #1a6fb3;
        border-color: #1a6fb3;
        color: white;
    }
    
    .page-link:hover {
        background-color: #e8f4ff;
        color: #0d4d7a;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #1a6fb3;
        box-shadow: 0 0 0 0.2rem rgba(26, 111, 179, 0.25);
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 11px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tự động submit khi thay đổi select
    const autoSubmitSelects = ['visit_type', 'status', 'sort_by', 'sort_order'];
    autoSubmitSelects.forEach(selectName => {
        const select = document.querySelector(`select[name="${selectName}"]`);
        if (select && select.closest('form')) {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        }
    });
});
</script>

<!-- Bootstrap Icons -->
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush

</div>
@endsection
