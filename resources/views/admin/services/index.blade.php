{{-- resources/views/admin/services/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Danh mục Dịch vụ & Bảng giá')

@push('styles')
<style>
/* ── UI Enhancements ────────────────────────────────────────── */
.svc-tabs {
    background: #f0f4ff;
    border-radius: 12px;
    padding: 6px;
    display: flex;
    gap: 6px;
}
.svc-tab-btn {
    flex: 1;
    padding: 10px 18px;
    border-radius: 9px;
    border: none;
    background: transparent;
    color: #546e7a;
    font-weight: 600;
    font-size: 13.5px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}
.svc-tab-btn:hover {
    background: rgba(255, 255, 255, 0.4);
    color: #0D47A1;
}
.svc-tab-btn.active {
    background: #fff;
    color: #0D47A1;
    box-shadow: 0 4px 12px rgba(13, 71, 161, 0.15);
}

/* Stat badges */
.svc-code {
    background: #F0F4FF;
    color: #0D47A1;
    border-radius: 6px;
    padding: 3px 10px;
    font-family: monospace;
    font-size: 12px;
    font-weight: 600;
}
.svc-row:hover td {
    background: #F7F9FF !important;
}
.svc-name-main {
    font-weight: 700;
    font-size: 14px;
    color: #1a2332;
    transition: color 0.15s;
}
.svc-name-main:hover {
    color: #0D47A1;
}
.svc-name-sub {
    font-size: 11.5px;
    color: #90A4AE;
    margin-top: 3px;
}

/* Pricing typography */
.price-normal { font-weight: 700; color: #0D47A1; }
.price-bhyt   { font-weight: 700; color: #2e7d32; }
.price-vip    { font-weight: 700; color: #e65100; }

/* 3-Column price cards */
.price-card {
    border-radius: 16px;
    overflow: hidden;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    transition: transform 0.2s;
}
.price-card:hover {
    transform: translateY(-3px);
}
.price-card-header {
    padding: 16px 20px;
    font-weight: 700;
    font-size: 14.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.price-row-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    border-bottom: 1px solid #edf1f7;
    font-size: 13.5px;
    transition: background 0.15s;
}
.price-row-item:hover {
    background: #fafbfe;
}
.price-row-item:last-child {
    border-bottom: none;
}
.price-row-name {
    color: #455a64;
    font-weight: 500;
}
.price-row-val {
    font-weight: 700;
}

.hist-old {
    text-decoration: line-through;
    color: #b0bec5;
    font-size: 12px;
}

/* Modals */
.delete-modal-icon {
    width: 72px;
    height: 72px;
    background: #FEE2E2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}
.btn-action {
    width: 34px;
    height: 34px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}
.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Toast popup notifications */
#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Toast container --}}
    <div id="toast-container"></div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="bi bi-clipboard2-pulse me-2 text-primary"></i>Danh Mục Dịch Vụ & Bảng Giá</h4>
            <p class="text-muted small mb-0">Quản lý dịch vụ, bảng giá theo từng loại và tra cứu lịch sử chỉnh sửa giá</p>
        </div>
        <button type="button" class="btn btn-primary px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#createServiceModal">
            <i class="bi bi-plus-lg me-1"></i>Thêm dịch vụ
        </button>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 rounded-3">
            <i class="bi bi-check-circle-fill flex-shrink-0 fs-5 text-success"></i>
            <span class="fw-medium">{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 rounded-3">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 fs-5 text-danger"></i>
            <span class="fw-medium">{{ session('error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tab bar --}}
    <div class="svc-tabs mb-4">
        <button class="svc-tab-btn {{ $tab === 'services' ? 'active' : '' }}" onclick="switchTab('services', event)">
            <i class="bi bi-list-ul me-1"></i>Danh sách dịch vụ
        </button>
        <button class="svc-tab-btn {{ $tab === 'prices' ? 'active' : '' }}" onclick="switchTab('prices', event)">
            <i class="bi bi-tags me-1"></i>Bảng giá
        </button>
        <button class="svc-tab-btn {{ $tab === 'history' ? 'active' : '' }}" onclick="switchTab('history', event)">
            <i class="bi bi-clock-history me-1"></i>Lịch sử thay đổi giá
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         TAB 1: DANH SÁCH DỊCH VỤ
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="tab-services" class="{{ $tab !== 'services' ? 'd-none' : '' }}">
        {{-- Bộ lọc --}}
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="tab" value="services">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-semibold text-muted mb-1 small">Tìm kiếm</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0"
                                   placeholder="Tìm mã hoặc tên dịch vụ..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-semibold text-muted mb-1 small">Khoa</label>
                        <select name="department_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả khoa</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}"
                                    {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm fw-semibold text-muted mb-1 small">Trạng thái</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm ngưng</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill fw-semibold">
                            <i class="bi bi-funnel me-1"></i>Lọc
                        </button>
                        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary" title="Xoá bộ lọc">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bảng dịch vụ --}}
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-4" style="width:120px">Mã DV</th>
                                <th>Tên dịch vụ</th>
                                <th>Khoa phụ trách</th>
                                <th class="text-center" style="width:110px">Thời lượng</th>
                                <th class="text-end" style="width:130px">Giá thường</th>
                                <th class="text-end" style="width:130px">Giá BHYT</th>
                                <th class="text-end" style="width:130px">Giá VIP</th>
                                <th class="text-center" style="width:140px">Trạng thái</th>
                                <th class="text-center" style="width:150px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                            @php
                                $priceNormal = $service->activePrices->firstWhere('price_type','Thường');
                                $priceBhyt   = $service->activePrices->firstWhere('price_type','BHYT');
                                $priceVip    = $service->activePrices->firstWhere('price_type','VIP');
                            @endphp
                            <tr class="svc-row" id="service-row-{{ $service->service_id }}">
                                <td class="ps-4">
                                    <span class="svc-code">{{ $service->service_code }}</span>
                                </td>
                                <td>
                                    <div class="svc-name-main cursor-pointer" onclick="openShowModal({{ $service->service_id }})">
                                        {{ $service->service_name }}
                                    </div>
                                    @if($service->description)
                                    <div class="svc-name-sub">{{ Str::limit($service->description, 65) }}</div>
                                    @endif
                                </td>
                                <td class="small fw-medium">{{ $service->department->department_name ?? '—' }}</td>
                                <td class="text-center small fw-semibold">{{ $service->duration_minutes }} phút</td>
                                <td class="text-end font-monospace">
                                    @if($priceNormal)
                                        <span class="price-normal">{{ number_format($priceNormal->price,0,',','.') }} đ</span>
                                    @else <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace">
                                    @if($priceBhyt)
                                        <span class="price-bhyt">{{ number_format($priceBhyt->price,0,',','.') }} đ</span>
                                    @else <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace">
                                    @if($priceVip)
                                        <span class="price-vip">{{ number_format($priceVip->price,0,',','.') }} đ</span>
                                    @else <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center badge-status-cell">
                                    @if($service->status)
                                        <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1">Đang hoạt động</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning fw-semibold px-2 py-1">Tạm ngưng</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        {{-- Xem chi tiết --}}
                                        <button type="button" class="btn btn-action btn-outline-info"
                                                title="Xem chi tiết & Quản lý giá" data-bs-toggle="tooltip"
                                                onclick="openShowModal({{ $service->service_id }})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        {{-- Sửa dịch vụ --}}
                                        <button type="button" class="btn btn-action btn-outline-primary"
                                                title="Chỉnh sửa thông tin" data-bs-toggle="tooltip"
                                                onclick="openEditModal({{ $service->service_id }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        {{-- Toggle Status --}}
                                        <button type="button"
                                                class="btn btn-action btn-outline-{{ $service->status ? 'warning' : 'success' }} status-toggle-btn"
                                                title="{{ $service->status ? 'Tạm ngưng hoạt động' : 'Kích hoạt hoạt động' }}"
                                                data-bs-toggle="tooltip"
                                                onclick="toggleServiceStatus({{ $service->service_id }}, this)">
                                            <i class="bi bi-{{ $service->status ? 'pause-circle' : 'play-circle' }}"></i>
                                        </button>
                                        {{-- Xoá --}}
                                        <button type="button" class="btn btn-action btn-outline-danger"
                                                title="Xoá dịch vụ" data-bs-toggle="tooltip"
                                                onclick="openDeleteModal({{ $service->service_id }}, '{{ addslashes($service->service_name) }}', '{{ addslashes($service->service_code) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Không tìm thấy dịch vụ nào phù hợp.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($services->hasPages())
            <div class="card-footer bg-white border-top py-3">{{ $services->links() }}</div>
            @endif
        </div>

        <div class="alert d-flex align-items-center gap-2 mt-4 shadow-sm border-0"
             style="background:#E3F2FD; border-radius:12px; color:#1565C0;">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 text-primary"></i>
            <span class="small fw-medium">
                Dịch vụ ở trạng thái <strong>Tạm ngưng</strong> sẽ bị ẩn khi bệnh nhân thực hiện đặt lịch hẹn. 
                Giúp tối ưu hoá vận hành phòng khám một cách linh hoạt.
            </span>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         TAB 2: BẢNG GIÁ 3 CỘT
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="tab-prices" class="{{ $tab !== 'prices' ? 'd-none' : '' }}">
        <div class="row g-4">
            {{-- Giá thường --}}
            <div class="col-lg-4">
                <div class="card price-card" style="border-top:4px solid #0D47A1">
                    <div class="price-card-header" style="background:#F0F4FF; color:#0D47A1">
                        <i class="bi bi-person fs-5"></i> Giá Thường
                        <span class="badge ms-auto rounded-pill" style="background:#0D47A1">
                            {{ $pricesByType['Thường']->count() }} dịch vụ
                        </span>
                    </div>
                    <div class="card-body p-0" style="max-height:600px; overflow-y:auto;">
                        @forelse($pricesByType['Thường'] as $p)
                        <div class="price-row-item">
                            <span class="price-row-name">{{ $p->service_name }}</span>
                            <span class="price-row-val price-normal">{{ number_format($p->price,0,',','.') }} đ</span>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted small">Chưa có bảng giá thường</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Giá BHYT --}}
            <div class="col-lg-4">
                <div class="card price-card" style="border-top:4px solid #2e7d32">
                    <div class="price-card-header" style="background:#E8F5E9; color:#2e7d32">
                        <i class="bi bi-shield-check fs-5"></i> Giá BHYT
                        <span class="badge ms-auto bg-success rounded-pill">
                            {{ $pricesByType['BHYT']->count() }} dịch vụ
                        </span>
                    </div>
                    <div class="card-body p-0" style="max-height:600px; overflow-y:auto;">
                        @forelse($pricesByType['BHYT'] as $p)
                        <div class="price-row-item">
                            <span class="price-row-name">{{ $p->service_name }}</span>
                            <span class="price-row-val price-bhyt">{{ number_format($p->price,0,',','.') }} đ</span>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted small">Chưa có bảng giá BHYT</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Giá VIP --}}
            <div class="col-lg-4">
                <div class="card price-card" style="border-top:4px solid #e65100">
                    <div class="price-card-header" style="background:#FFF3E0; color:#e65100">
                        <i class="bi bi-star fs-5"></i> Giá VIP / Theo yêu cầu
                        <span class="badge ms-auto rounded-pill" style="background:#e65100">
                            {{ $pricesByType['VIP']->count() }} dịch vụ
                        </span>
                    </div>
                    <div class="card-body p-0" style="max-height:600px; overflow-y:auto;">
                        @forelse($pricesByType['VIP'] as $p)
                        <div class="price-row-item">
                            <span class="price-row-name">{{ $p->service_name }}</span>
                            <span class="price-row-val price-vip">{{ number_format($p->price,0,',','.') }} đ</span>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted small">Chưa có bảng giá VIP</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="alert d-flex align-items-center gap-2 mt-4 shadow-sm border-0"
             style="background:#E3F2FD; border-radius:12px; color:#1565C0;">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 text-primary"></i>
            <span class="small fw-medium">
                Bảng giá ở trên hiển thị mức giá <strong>đang có hiệu lực tức thời</strong> tại thời điểm hiện tại.
                Để thay đổi hoặc lên lịch các mức giá mới, vui lòng nhấp vào nút <i class="bi bi-eye"></i> (Xem chi tiết) ở mỗi hàng dịch vụ.
            </span>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         TAB 3: LỊCH SỬ THAY ĐỔI GIÁ (DỰA TRÊN DATABASE THỰC TẾ)
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="tab-history" class="{{ $tab !== 'history' ? 'd-none' : '' }}">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3 fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary fs-5"></i>
                Nhật ký tạo và chỉnh sửa bảng giá
                <span class="badge bg-primary-subtle text-primary ms-2 px-2 py-1 small rounded-pill">
                    Dữ liệu thời gian thực
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-4" style="width:180px">Thời gian tạo/sửa</th>
                                <th>Dịch vụ áp dụng</th>
                                <th>Người cập nhật</th>
                                <th style="width:130px">Loại giá</th>
                                <th class="text-end" style="width:150px">Đơn giá mới</th>
                                <th class="ps-4">Trạng thái áp dụng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($priceHistory as $log)
                            @php
                                $typeColors = [
                                    'Thường' => ['bg-primary-subtle','text-primary'],
                                    'BHYT'   => ['bg-success-subtle','text-success'],
                                    'VIP'    => ['bg-warning-subtle','text-warning'],
                                ];
                                [$bg, $fg] = $typeColors[$log->price_type] ?? ['bg-secondary-subtle','text-secondary'];
                            @endphp
                            <tr>
                                <td class="ps-4 small text-muted font-monospace">
                                    {{ \Carbon\Carbon::parse($log->changed_at)->format('d/m/Y H:i:s') }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size:13.5px">{{ $log->service_name }}</div>
                                    <div class="text-muted monospace" style="font-size:11px">{{ $log->service_code }}</div>
                                </td>
                                <td class="small fw-semibold text-secondary">{{ $log->changed_by_name ?? 'Hệ thống' }}</td>
                                <td>
                                    <span class="badge {{ $bg }} {{ $fg }} fw-semibold px-2 py-1">{{ $log->price_type }}</span>
                                </td>
                                <td class="text-end fw-bold font-monospace text-primary" style="font-size:14px;">
                                    {{ number_format($log->new_price,0,',','.') }} đ
                                </td>
                                <td class="ps-4 small text-muted font-medium">
                                    <i class="bi bi-info-circle me-1 text-primary"></i>{{ $log->reason }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                                    Chưa có bản ghi thay đổi bảng giá nào được tạo.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($priceHistory, 'hasPages') && $priceHistory->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $priceHistory->appends(['tab' => 'history'])->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL 1: THÊM DỊCH VỤ MỚI (POPUP TRÊN TRANG CHÍNH)
═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="createServiceModal" tabindex="-1" aria-labelledby="createServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="createServiceModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Khai báo Dịch vụ Khám chữa bệnh
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createServiceForm" method="POST" action="{{ route('admin.services.store') }}">
                @csrf
                <div class="modal-body p-4 row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Mã dịch vụ <span class="text-danger">*</span></label>
                        <input type="text" name="service_code" class="form-control" placeholder="VD: DV008" required>
                        <div class="invalid-feedback error-service_code"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Tên dịch vụ y tế <span class="text-danger">*</span></label>
                        <input type="text" name="service_name" class="form-control" placeholder="VD: Siêu âm ổ bụng tổng quát" required>
                        <div class="invalid-feedback error-service_name"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Khoa khám phụ trách</label>
                        <select name="department_id" class="form-select">
                            <option value="">-- Chọn khoa --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback error-department_id"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Thời lượng thực hiện (phút) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_minutes" class="form-control" value="30" min="5" max="480" required>
                        <div class="invalid-feedback error-duration_minutes"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-secondary">Mô tả ngắn dịch vụ</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Nhập tóm tắt mô tả về dịch vụ khám y tế này..."></textarea>
                        <div class="invalid-feedback error-description"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-secondary">Trạng thái phát hành <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="1">Đang hoạt động (Kích hoạt ngay)</option>
                            <option value="0">Tạm ngưng hoạt động (Ẩn)</option>
                        </select>
                        <div class="invalid-feedback error-status"></div>
                    </div>

                    <!-- Bảng giá ban đầu inline -->
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <span class="fw-bold text-primary"><i class="bi bi-tags-fill me-1"></i>Thiết lập bảng giá khởi tạo (Tuỳ chọn)</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-3 fw-semibold" id="createAddPriceRowBtn">
                                <i class="bi bi-plus-lg me-1"></i>Thêm mức giá
                            </button>
                        </div>
                        <div id="createPricesContainer">
                            <!-- Dynamic rows inside create form -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">Huỷ bỏ</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="bi bi-floppy me-1"></i>Lưu dịch vụ</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL 2: SỬA THÔNG TIN DỊCH VỤ (POPUP TRÊN TRANG CHÍNH)
═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="editServiceModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Cập nhật Thông tin Dịch vụ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editServiceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-secondary">Mã dịch vụ y tế (Không được thay đổi)</label>
                        <input type="text" id="edit_service_code" class="form-control bg-light fw-bold font-monospace" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-secondary">Tên dịch vụ y tế <span class="text-danger">*</span></label>
                        <input type="text" name="service_name" id="edit_service_name" class="form-control" required>
                        <div class="invalid-feedback error-service_name"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Khoa khám phụ trách</label>
                        <select name="department_id" id="edit_department_id" class="form-select">
                            <option value="">-- Chọn khoa --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback error-department_id"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Thời lượng y tế (phút) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_minutes" id="edit_duration_minutes" class="form-control" min="5" max="480" required>
                        <div class="invalid-feedback error-duration_minutes"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-secondary">Mô tả ngắn</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback error-description"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-secondary">Trạng thái phát hành <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="1">Hoạt động (Đang áp dụng)</option>
                            <option value="0">Tạm ngưng hoạt động (Ẩn)</option>
                        </select>
                        <div class="invalid-feedback error-status"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">Huỷ bỏ</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="bi bi-floppy me-1"></i>Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL 3: XEM CHI TIẾT DỊCH VỤ & QUẢN LÝ BẢNG GIÁ ĐA NĂNG
═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="viewServiceModal" tabindex="-1" aria-labelledby="viewServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="viewServiceModalLabel">
                    <i class="bi bi-eye me-2"></i>Thông tin dịch vụ & Thiết lập Bảng giá
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Cột thông tin dịch vụ -->
                    <div class="col-lg-4">
                        <div class="card h-100 shadow-sm border-0 bg-light rounded-3">
                            <div class="card-body p-4">
                                <h6 class="fw-bold border-bottom pb-2 mb-3 text-secondary text-uppercase" style="letter-spacing: 0.05em; font-size:12px;">
                                    <i class="bi bi-info-circle me-1"></i>Chi tiết dịch vụ
                                </h6>
                                <table class="table table-sm table-borderless small mb-0">
                                    <tr class="align-middle">
                                        <td class="text-muted py-2 font-medium" style="width:40%">Mã dịch vụ:</td>
                                        <td class="py-2"><span class="svc-code font-monospace" id="view_service_code"></span></td>
                                    </tr>
                                    <tr class="align-middle">
                                        <td class="text-muted py-2 font-medium">Tên dịch vụ:</td>
                                        <td class="fw-bold py-2 text-dark" id="view_service_name" style="font-size:13.5px"></td>
                                    </tr>
                                    <tr class="align-middle">
                                        <td class="text-muted py-2 font-medium">Khoa trực thuộc:</td>
                                        <td class="py-2 fw-medium" id="view_department"></td>
                                    </tr>
                                    <tr class="align-middle">
                                        <td class="text-muted py-2 font-medium">Thời lượng xử lý:</td>
                                        <td class="py-2 fw-semibold text-primary" id="view_duration"></td>
                                    </tr>
                                    <tr class="align-middle">
                                        <td class="text-muted py-2 font-medium">Trạng thái:</td>
                                        <td class="py-2" id="view_status"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2 font-medium" colspan="2">Mô tả chi tiết:</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary small bg-white p-3 border rounded-3" colspan="2" id="view_description" style="min-height:70px; font-style:italic; line-height: 1.5;"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Cột quản lý bảng giá -->
                    <div class="col-lg-8">
                        <div class="card h-100 shadow-sm border-0 rounded-3">
                            <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom py-3 px-4">
                                <span class="fw-bold text-dark mb-0"><i class="bi bi-tags-fill me-1 text-primary"></i>Quản lý Các mức giá của dịch vụ</span>
                                <button type="button" class="btn btn-sm btn-primary fw-semibold px-3" id="toggleAddPriceFormBtn">
                                    <i class="bi bi-plus-lg me-1"></i>Thêm giá mới
                                </button>
                            </div>
                            
                            <!-- Form thêm giá mới (collapsible) -->
                            <div class="d-none p-3 border-bottom bg-light" id="addPriceFormContainer">
                                <form id="addPriceForm" method="POST">
                                    @csrf
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label form-label-sm mb-1 fw-bold text-secondary" style="font-size:11px">Loại giá</label>
                                            <select name="price_type" class="form-select form-select-sm" required>
                                                @foreach($priceTypes as $type)
                                                    <option value="{{ $type }}">{{ $type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label form-label-sm mb-1 fw-bold text-secondary" style="font-size:11px">Đơn giá (đ)</label>
                                            <input type="number" name="price" class="form-control form-control-sm" min="0" step="1000" placeholder="Đơn giá" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label form-label-sm mb-1 fw-bold text-secondary" style="font-size:11px">Áp dụng từ ngày</label>
                                            <input type="date" name="effective_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label form-label-sm mb-1 fw-bold text-secondary" style="font-size:11px">Hết hạn (Tuỳ chọn)</label>
                                            <input type="date" name="end_date" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-12 mt-3 d-flex justify-content-between align-items-center">
                                            <div class="text-danger small fw-semibold" id="addPriceError" style="max-width: 60%"></div>
                                            <div>
                                                <button type="submit" class="btn btn-sm btn-success px-4 fw-semibold me-1"><i class="bi bi-check-lg me-1"></i>Lưu lại</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="cancelAddPriceBtn">Huỷ</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="table-light text-secondary">
                                            <tr>
                                                <th style="width:15%">Loại giá</th>
                                                <th style="width:25%">Đơn giá áp dụng</th>
                                                <th style="width:25%">Hiệu lực thời gian</th>
                                                <th style="width:20%">Trạng thái</th>
                                                <th style="width:15%" class="text-center">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody id="view_prices_tbody">
                                            <!-- Dynamic prices loaded via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">Đóng cửa sổ</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL 4: XÁC NHẬN XOÁ DỊCH VỤ (POPUP NGĂN CHẶN LỖI DB THỰC TẾ)
═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-labelledby="deleteServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-4">
                <div class="delete-modal-icon mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-2"></i>
                </div>
                <h5 class="fw-bold mb-1" id="deleteServiceModalLabel">Xác nhận xoá dịch vụ</h5>
                <p class="text-muted small mb-3">
                    Bạn có chắc muốn xoá hoàn toàn dịch vụ: <br>
                    <strong id="deleteServiceName" class="text-dark" style="font-size:14px"></strong>?
                </p>

                <!-- Vùng thông báo lỗi db thực tế (nếu có appointments/invoices) -->
                <div class="alert alert-danger py-2 px-3 text-start small mb-3" id="deleteWarningBlock" style="display:none">
                    <i class="bi bi-exclamation-octagon me-1 fw-bold"></i>
                    <span id="deleteWarningText" class="fw-semibold"></span>
                </div>

                <form id="deleteServiceForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Huỷ
                        </button>
                        <button type="submit" class="btn btn-danger px-4 fw-semibold" id="confirmDeleteSubmitBtn">
                            <i class="bi bi-trash me-1"></i>Xác nhận Xoá
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentActiveServiceId = null;

/* ── SWITCH TAB ────────────────────────────────────────────── */
function switchTab(name, event) {
    document.querySelectorAll('.svc-tab-btn').forEach(b => b.classList.remove('active'));
    event.currentTarget.classList.add('active');
    ['services','prices','history'].forEach(t => {
        const tabEl = document.getElementById('tab-' + t);
        if (tabEl) {
            tabEl.classList.toggle('d-none', t !== name);
        }
    });
    const url = new URL(window.location);
    url.searchParams.set('tab', name);
    window.history.replaceState({}, '', url);
}

/* ── SHOW TOAST NOTIFICATION ───────────────────────────────── */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} shadow-lg d-flex align-items-center gap-2 border-0 rounded-3 py-2 px-3 mb-2`;
    toast.style.minWidth = '280px';
    toast.style.animation = 'slideInRight 0.3s ease-out';
    
    let icon = '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
    if (type === 'danger') icon = '<i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>';
    if (type === 'warning') icon = '<i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>';
    
    toast.innerHTML = `
        ${icon}
        <div class="fw-medium small text-dark">${message}</div>
        <button type="button" class="btn-close ms-auto small" onclick="this.parentElement.remove()" style="font-size:10px"></button>
    `;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.5s ease-out forwards';
        setTimeout(() => toast.remove(), 500);
    }, 4500);
}

/* ── TOGGLE SERVICE STATUS VIA AJAX ───────────────────────── */
async function toggleServiceStatus(serviceId, btn) {
    try {
        btn.disabled = true;
        const res = await fetch(`/admin/services/${serviceId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        btn.disabled = false;
        
        if (data.success) {
            showToast(data.message, 'success');
            // Cập nhật giao diện trực quan ngay lập tức
            const row = document.getElementById(`service-row-${serviceId}`);
            if (row) {
                const statusCell = row.querySelector('.badge-status-cell');
                if (data.status) {
                    statusCell.innerHTML = `<span class="badge bg-success-subtle text-success fw-semibold px-2 py-1">Đang hoạt động</span>`;
                    btn.className = "btn btn-action btn-outline-warning status-toggle-btn";
                    btn.innerHTML = `<i class="bi bi-pause-circle"></i>`;
                    btn.title = "Tạm ngưng hoạt động";
                } else {
                    statusCell.innerHTML = `<span class="badge bg-warning-subtle text-warning fw-semibold px-2 py-1">Tạm ngưng</span>`;
                    btn.className = "btn btn-action btn-outline-success status-toggle-btn";
                    btn.innerHTML = `<i class="bi bi-play-circle"></i>`;
                    btn.title = "Kích hoạt hoạt động";
                }
                
                // Re-init Tooltip
                const oldTooltip = bootstrap.Tooltip.getInstance(btn);
                if (oldTooltip) oldTooltip.dispose();
                new bootstrap.Tooltip(btn, { trigger: 'hover' });
            }
        } else {
            showToast(data.message || 'Lỗi hệ thống', 'danger');
        }
    } catch (e) {
        btn.disabled = false;
        showToast('Không thể kết nối máy chủ', 'danger');
    }
}

/* ── OPEN CREATE SERVICE PRICE INLINE ROWS ───────────────────── */
let createPriceIdx = 0;
document.getElementById('createAddPriceRowBtn').addEventListener('click', () => {
    const container = document.getElementById('createPricesContainer');
    const rowId = `create-price-row-${createPriceIdx}`;
    
    const tpl = `
        <div class="row g-2 border rounded p-3 mb-2 align-items-end" id="${rowId}">
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1 fw-bold text-muted small">Loại giá</label>
                <select name="prices[${createPriceIdx}][price_type]" class="form-select form-select-sm">
                    <option value="Thường">Thường</option>
                    <option value="BHYT">BHYT</option>
                    <option value="VIP">VIP</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1 fw-bold text-muted small">Đơn giá (đ)</label>
                <input type="number" name="prices[${createPriceIdx}][price]" class="form-control form-control-sm" placeholder="Nhập giá" min="0" step="1000" required>
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1 fw-bold text-muted small">Hiệu lực từ</label>
                <input type="date" name="prices[${createPriceIdx}][effective_date]" class="form-control form-control-sm" value="${new Date().toISOString().split('T')[0]}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm mb-1 fw-bold text-muted small">Hết hạn</label>
                <input type="date" name="prices[${createPriceIdx}][end_date]" class="form-control form-control-sm">
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('${rowId}').remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', tpl);
    createPriceIdx++;
});

/* ── SUBMIT CREATE SERVICE FORM VIA AJAX ───────────────────── */
document.getElementById('createServiceForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    
    // Clear validation state
    form.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
    
    const formData = new FormData(form);
    
    try {
        const res = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const data = await res.json();
        submitBtn.disabled = false;
        
        if (res.status === 422) {
            // Render validation errors dynamically inside modal
            Object.keys(data.errors).forEach(key => {
                // handles standard fields and dynamic prices.* fields
                let fieldKey = key;
                if (key.includes('.')) {
                    showToast(`Bảng giá ban đầu bị lỗi: ${data.errors[key][0]}`, 'warning');
                    return;
                }
                const input = form.querySelector(`[name="${fieldKey}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const errBlock = form.querySelector(`.error-${fieldKey}`);
                    if (errBlock) errBlock.textContent = data.errors[fieldKey][0];
                }
            });
            showToast('Vui lòng kiểm tra lại thông tin biểu mẫu', 'warning');
        } else if (data.success) {
            // Success
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('createServiceModal')).hide();
            form.reset();
            document.getElementById('createPricesContainer').innerHTML = '';
            
            // Reload page to reflect full list changes safely
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message || 'Gặp lỗi khi tạo dịch vụ', 'danger');
        }
    } catch (err) {
        submitBtn.disabled = false;
        showToast('Lỗi đường truyền kết nối máy chủ', 'danger');
    }
});

/* ── OPEN & POPULATE EDIT MODAL VIA AJAX ───────────────────── */
async function openEditModal(serviceId) {
    const modalEl = document.getElementById('editServiceModal');
    const form = document.getElementById('editServiceForm');
    form.reset();
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    form.action = `/admin/services/${serviceId}`;
    
    try {
        const res = await fetch(`/admin/services/${serviceId}/edit`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        
        if (data.success) {
            const svc = data.service;
            document.getElementById('edit_service_code').value = svc.service_code;
            document.getElementById('edit_service_name').value = svc.service_name;
            document.getElementById('edit_department_id').value = svc.department_id || '';
            document.getElementById('edit_duration_minutes').value = svc.duration_minutes;
            document.getElementById('edit_description').value = svc.description || '';
            document.getElementById('edit_status').value = svc.status ? '1' : '0';
            
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            showToast(data.message || 'Không thể tải thông tin chỉnh sửa', 'danger');
        }
    } catch (e) {
        showToast('Lỗi kết nối máy chủ', 'danger');
    }
}

/* ── SUBMIT EDIT FORM VIA AJAX ─────────────────────────────── */
document.getElementById('editServiceForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    
    form.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));

    const name = document.getElementById('edit_service_name');
    const dur = document.getElementById('edit_duration_minutes');
    
    let valid = true;
    if (!name.value.trim()) {
        name.classList.add('is-invalid');
        const errBlock = form.querySelector('.error-service_name');
        if (errBlock) errBlock.textContent = 'Tên dịch vụ là bắt buộc.';
        valid = false;
    }
    const durVal = parseInt(dur.value);
    if (isNaN(durVal) || durVal < 5 || durVal > 480) {
        dur.classList.add('is-invalid');
        const errBlock = form.querySelector('.error-duration_minutes');
        if (errBlock) errBlock.textContent = 'Thời gian phải từ 5 đến 480 phút.';
        valid = false;
    }
    
    if (!valid) {
        submitBtn.disabled = false;
        showToast('Vui lòng kiểm tra lại thông tin biểu mẫu', 'warning');
        return;
    }
    
    const formData = new FormData(form);
    
    try {
        const res = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        submitBtn.disabled = false;
        
        if (res.status === 422) {
            Object.keys(data.errors).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const errBlock = form.querySelector(`.error-${key}`);
                    if (errBlock) errBlock.textContent = data.errors[key][0];
                }
            });
            showToast('Thông tin nhập liệu không hợp lệ', 'warning');
        } else if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('editServiceModal')).hide();
            
            // Reload page to reflect updates safely with pagination/filters
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message || 'Cập nhật thất bại', 'danger');
        }
    } catch (err) {
        submitBtn.disabled = false;
        showToast('Lỗi máy chủ', 'danger');
    }
});

/* ── OPEN & LOAD SHOW DETAILS / PRICE LIST MODAL VIA AJAX ─── */
async function openShowModal(serviceId) {
    currentActiveServiceId = serviceId;
    const modalEl = document.getElementById('viewServiceModal');
    
    // Hide add price form container initially
    document.getElementById('addPriceFormContainer').classList.add('d-none');
    document.getElementById('addPriceForm').reset();
    document.getElementById('addPriceError').textContent = '';
    
    try {
        const res = await fetch(`/admin/services/${serviceId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        
        if (data.success) {
            const svc = data.service;
            
            document.getElementById('view_service_code').textContent = svc.service_code;
            document.getElementById('view_service_name').textContent = svc.service_name;
            document.getElementById('view_department').textContent = svc.department ? svc.department.department_name : '—';
            document.getElementById('view_duration').textContent = `${svc.duration_minutes} phút`;
            document.getElementById('view_status').innerHTML = svc.status 
                ? `<span class="badge bg-success">Đang hoạt động</span>`
                : `<span class="badge bg-danger">Vô hiệu</span>`;
            document.getElementById('view_description').textContent = svc.description || 'Chưa có mô tả chi tiết.';
            
            // Render price rows inside view modal
            renderPriceTableRows(svc.prices);
            
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            showToast(data.message || 'Không thể hiển thị chi tiết', 'danger');
        }
    } catch (e) {
        showToast('Giao tiếp máy chủ thất bại', 'danger');
    }
}

/* ── RENDER DYNAMIC PRICE ROWS ─────────────────────────────── */
function renderPriceTableRows(prices) {
    const tbody = document.getElementById('view_prices_tbody');
    tbody.innerHTML = '';
    
    if (!prices || prices.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Chưa cấu hình bảng giá nào cho dịch vụ này.</td></tr>`;
        return;
    }
    
    // Sort descending by effective date
    prices.sort((a, b) => new Date(b.effective_date) - new Date(a.effective_date));
    
    prices.forEach(price => {
        const effDate = new Date(price.effective_date);
        const endDateStr = price.end_date ? new Date(price.end_date).toLocaleDateString('vi-VN') : '—';
        const effDateStr = effDate.toLocaleDateString('vi-VN');
        
        // Active indicator logic
        const now = new Date();
        now.setHours(0,0,0,0);
        const effTime = new Date(price.effective_date);
        effTime.setHours(0,0,0,0);
        const endTime = price.end_date ? new Date(price.end_date) : null;
        if (endTime) endTime.setHours(0,0,0,0);
        
        const isActive = effTime <= now && (!endTime || endTime >= now);
        const isUpcoming = effTime > now;
        
        let statusBadge = '<span class="badge bg-secondary-subtle text-secondary">Hết hiệu lực</span>';
        if (isActive) statusBadge = '<span class="badge bg-success-subtle text-success">Đang áp dụng</span>';
        else if (isUpcoming) statusBadge = '<span class="badge bg-info-subtle text-info">Sắp áp dụng</span>';
        
        const rowId = `price-tr-${price.price_id}`;
        
        const trHtml = `
            <tr id="${rowId}" class="${isActive ? '' : 'text-muted bg-light-subtle'}">
                <td>
                    <span class="badge bg-secondary">${price.price_type}</span>
                </td>
                <td class="fw-bold text-dark font-monospace price-cell-val">
                    ${Number(price.price).toLocaleString('vi-VN')} đ
                </td>
                <td class="small">
                    <div>Bắt đầu: <strong class="price-cell-eff">${effDateStr}</strong></div>
                    <div>Hết hạn: <span class="price-cell-end">${endDateStr}</span></div>
                </td>
                <td>${statusBadge}</td>
                <td class="text-center action-buttons-price">
                    <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2" title="Sửa giá" onclick="editPriceInline(${price.price_id}, '${price.price_type}', ${price.price}, '${price.effective_date.split('T')[0]}', '${price.end_date ? price.end_date.split('T')[0] : ''}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger py-1 px-2" title="Xoá giá" onclick="deletePriceAjax(${price.price_id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', trHtml);
    });
}

/* ── TOGGLE ADD PRICE FORM INLINE ──────────────────────────── */
const toggleBtn = document.getElementById('toggleAddPriceFormBtn');
const formContainer = document.getElementById('addPriceFormContainer');

toggleBtn.addEventListener('click', () => {
    formContainer.classList.toggle('d-none');
    document.getElementById('addPriceError').textContent = '';
});
document.getElementById('cancelAddPriceBtn').addEventListener('click', () => {
    formContainer.classList.add('d-none');
    document.getElementById('addPriceError').textContent = '';
});

/* ── SUBMIT ADD PRICE FORM VIA AJAX (OVERLAP DATE PROTECTION) ── */
document.getElementById('addPriceForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = this;
    const errBlock = document.getElementById('addPriceError');
    errBlock.textContent = '';
    
    // Clear previous validation states
    form.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));
    
    const priceValInput = form.querySelector('[name="price"]');
    const effDateInput = form.querySelector('[name="effective_date"]');
    const endDateInput = form.querySelector('[name="end_date"]');

    const price = parseFloat(priceValInput.value);
    const effDate = effDateInput.value;
    const endDate = endDateInput.value;

    if (isNaN(price) || price < 0) {
        errBlock.textContent = 'Đơn giá phải là số và không được âm.';
        priceValInput.classList.add('is-invalid');
        showToast('Vui lòng kiểm tra lại thông tin đơn giá!', 'warning');
        return;
    }

    if (!effDate) {
        errBlock.textContent = 'Ngày áp dụng là bắt buộc.';
        effDateInput.classList.add('is-invalid');
        showToast('Vui lòng nhập ngày áp dụng!', 'warning');
        return;
    }

    if (endDate && new Date(endDate) < new Date(effDate)) {
        errBlock.textContent = 'Ngày kết thúc phải bằng hoặc sau ngày áp dụng.';
        endDateInput.classList.add('is-invalid');
        showToast('Ngày kết thúc không hợp lệ!', 'warning');
        return;
    }

    const formData = new FormData(form);
    
    try {
        const res = await fetch(`/admin/services/${currentActiveServiceId}/prices`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        
        if (res.status === 422) {
            // Overlapping validation or input validation error
            errBlock.textContent = data.errors.effective_date 
                ? data.errors.effective_date[0] 
                : (data.errors.price ? data.errors.price[0] : 'Vui lòng kiểm tra lại dữ liệu nhập vào.');
            showToast('Lỗi trùng lặp hoặc sai thông tin bảng giá!', 'warning');
        } else if (data.success) {
            showToast(data.message, 'success');
            form.reset();
            formContainer.classList.add('d-none');
            
            // Reload price table instantly inside details modal
            reloadModalPrices();
        } else {
            errBlock.textContent = data.message || 'Lưu giá thất bại';
        }
    } catch (err) {
        showToast('Kết nối thất bại', 'danger');
    }
});

/* ── RE-LOAD PRICE ITEMS INSIDE VIEW DETAILS MODAL ──────────── */
async function reloadModalPrices() {
    try {
        const res = await fetch(`/admin/services/${currentActiveServiceId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.success) {
            renderPriceTableRows(data.service.prices);
        }
    } catch (e) {}
}

/* ── INLINE EDIT PRICE ROW VIA AJAX (SLICK UX) ──────────────── */
window.priceRowBackups = window.priceRowBackups || {};

function editPriceInline(priceId, type, priceVal, effDate, endDate) {
    const tr = document.getElementById(`price-tr-${priceId}`);
    if (!tr) return;
    
    // Save previous html markup to cancel back
    window.priceRowBackups[priceId] = tr.innerHTML;
    
    tr.className = "bg-warning-subtle";
    tr.innerHTML = `
        <td>
            <select class="form-select form-select-sm" id="inline-type-${priceId}" style="width:90px" required>
                <option value="Thường" ${type === 'Thường' ? 'selected' : ''}>Thường</option>
                <option value="BHYT" ${type === 'BHYT' ? 'selected' : ''}>BHYT</option>
                <option value="VIP" ${type === 'VIP' ? 'selected' : ''}>VIP</option>
            </select>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" class="form-control form-control-sm fw-bold font-monospace" id="inline-price-${priceId}" value="${priceVal}" min="0" step="1000" required>
                <span class="input-group-text">đ</span>
            </div>
        </td>
        <td>
            <div class="mb-1">
                <label class="fw-semibold text-muted" style="font-size:9px; display:block; margin:0">Từ ngày</label>
                <input type="date" class="form-control form-control-sm py-0" id="inline-eff-${priceId}" value="${effDate}" required>
            </div>
            <div>
                <label class="fw-semibold text-muted" style="font-size:9px; display:block; margin:0">Đến ngày</label>
                <input type="date" class="form-control form-control-sm py-0" id="inline-end-${priceId}" value="${endDate}">
            </div>
        </td>
        <td>
            <span class="badge bg-warning text-dark px-2 py-1">Đang sửa</span>
        </td>
        <td class="text-center">
            <div class="d-flex gap-1 justify-content-center">
                <button type="button" class="btn btn-sm btn-success px-2 py-1" onclick="savePriceInline(${priceId})" title="Lưu lại">
                    <i class="bi bi-check-lg"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1" onclick="cancelPriceInline('${priceId}')" title="Huỷ bỏ">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="text-danger small fw-semibold mt-1" id="inline-err-${priceId}" style="font-size:9px; max-width: 140px; white-space: normal;"></div>
        </td>
    `;
}

function cancelPriceInline(priceId) {
    const tr = document.getElementById(`price-tr-${priceId}`);
    if (!tr) return;
    tr.className = "";
    tr.innerHTML = window.priceRowBackups[priceId];
}

async function savePriceInline(priceId) {
    const tr = document.getElementById(`price-tr-${priceId}`);
    const inlineErr = document.getElementById(`inline-err-${priceId}`);
    inlineErr.textContent = '';
    
    const pType = document.getElementById(`inline-type-${priceId}`).value;
    const pPrice = document.getElementById(`inline-price-${priceId}`).value;
    const pEff = document.getElementById(`inline-eff-${priceId}`).value;
    const pEnd = document.getElementById(`inline-end-${priceId}`).value;
    
    const priceValInput = document.getElementById(`inline-price-${priceId}`);
    const effDateInput = document.getElementById(`inline-eff-${priceId}`);
    const endDateInput = document.getElementById(`inline-end-${priceId}`);

    priceValInput.classList.remove('is-invalid');
    effDateInput.classList.remove('is-invalid');
    if (endDateInput) endDateInput.classList.remove('is-invalid');

    const price = parseFloat(pPrice);

    if (isNaN(price) || price < 0) {
        inlineErr.textContent = 'Đơn giá phải là số và không được âm.';
        priceValInput.classList.add('is-invalid');
        showToast('Vui lòng kiểm tra lại thông tin đơn giá!', 'warning');
        return;
    }

    if (!pEff) {
        inlineErr.textContent = 'Ngày áp dụng là bắt buộc.';
        effDateInput.classList.add('is-invalid');
        showToast('Vui lòng nhập ngày áp dụng!', 'warning');
        return;
    }

    if (pEnd && new Date(pEnd) < new Date(pEff)) {
        inlineErr.textContent = 'Ngày kết thúc phải bằng hoặc sau ngày áp dụng.';
        if (endDateInput) endDateInput.classList.add('is-invalid');
        showToast('Ngày kết thúc không hợp lệ!', 'warning');
        return;
    }

    // AJAX payload
    const payload = {
        price_type: pType,
        price: pPrice,
        effective_date: pEff,
        end_date: pEnd || null
    };
    
    try {
        const res = await fetch(`/admin/services/${currentActiveServiceId}/prices/${priceId}`, {
            method: 'PUT',
            body: JSON.stringify(payload),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await res.json();
        
        if (res.status === 422) {
            // Show overlapping price error row in-place
            inlineErr.textContent = data.errors.effective_date 
                ? data.errors.effective_date[0] 
                : (data.errors.price ? data.errors.price[0] : 'Thông tin nhập chưa đúng.');
            showToast('Lỗi trùng lặp khoảng thời gian bảng giá!', 'warning');
        } else if (data.success) {
            showToast(data.message, 'success');
            reloadModalPrices();
        } else {
            inlineErr.textContent = data.message || 'Gặp lỗi khi lưu';
        }
    } catch (e) {
        showToast('Kết nối thất bại', 'danger');
    }
}

/* ── DELETE PRICE VIA AJAX ─────────────────────────────────── */
async function deletePriceAjax(priceId) {
    if (!confirm('Bạn có thực sự muốn xoá mức đơn giá này của dịch vụ?')) return;
    
    try {
        const res = await fetch(`/admin/services/${currentActiveServiceId}/prices/${priceId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            reloadModalPrices();
        } else {
            showToast(data.message || 'Xoá giá thất bại', 'danger');
        }
    } catch (e) {
        showToast('Không kết nối được server', 'danger');
    }
}

/* ── DELETE SERVICE MODAL & DATA CONSTRAINT GRACEFUL CATCH ── */
function openDeleteModal(serviceId, serviceName, serviceCode) {
    document.getElementById('deleteServiceName').textContent = serviceName + ' (' + serviceCode + ')';
    document.getElementById('deleteServiceForm').action = `/admin/services/${serviceId}`;
    document.getElementById('deleteWarningBlock').style.display = 'none';
    document.getElementById('deleteWarningText').textContent = '';
    document.getElementById('confirmDeleteSubmitBtn').disabled = false;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteServiceModal'));
    modal.show();
}

/* SUBMIT DELETE SERVICE VIA AJAX */
document.getElementById('deleteServiceForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = this;
    const warningBlock = document.getElementById('deleteWarningBlock');
    const warningText = document.getElementById('deleteWarningText');
    const confirmBtn = document.getElementById('confirmDeleteSubmitBtn');
    
    confirmBtn.disabled = true;
    
    try {
        const res = await fetch(form.action, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        confirmBtn.disabled = false;
        
        if (res.status === 400 || !data.success) {
            // Realistic error caught (associated appointments/invoices exist in DB)
            warningBlock.style.display = 'block';
            warningText.textContent = data.message || 'Không thể xoá dịch vụ này do ràng buộc dữ liệu.';
            showToast('Không thể xoá: Ràng buộc dữ liệu!', 'danger');
        } else if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('deleteServiceModal')).hide();
            
            // Reload page to refresh standard service paginated tables safely
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (err) {
        confirmBtn.disabled = false;
        showToast('Kết nối thất bại', 'danger');
    }
});

/* ── TOOLTIPS INITIALIZATION ──────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(el => new bootstrap.Tooltip(el, { trigger: 'hover' }));

    // ── Realtime polling: phát hiện thay đổi mỗi 20 giây ──────
    const ADMIN_DATA_URL = '{{ route("admin.services.data") }}';
    let lastTotal = {{ $services->total() }};
    let realtimeEl;

    realtimeEl = document.createElement('div');
    realtimeEl.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#0D47A1;color:#fff;'
        + 'padding:6px 16px;border-radius:20px;font-size:12px;opacity:0;transition:opacity .4s;z-index:9999;pointer-events:none;box-shadow:0 2px 8px rgba(0,0,0,.25);';
    realtimeEl.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Đang kiểm tra cập nhật...';
    document.body.appendChild(realtimeEl);

    setInterval(() => {
        fetch(ADMIN_DATA_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.stats && data.stats.total !== lastTotal) {
                    lastTotal = data.stats.total;
                    realtimeEl.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Dữ liệu đã thay đổi – đang tải lại...';
                    realtimeEl.style.background = '#2e7d32';
                    realtimeEl.style.opacity = '1';
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    realtimeEl.style.opacity = '1';
                    setTimeout(() => { realtimeEl.style.opacity = '0'; }, 1800);
                }
            })
            .catch(() => {});
    }, 20000);
});
</script>
@endpush
@endsection
