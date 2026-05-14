{{-- resources/views/admin/services/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Danh mục Dịch vụ & Bảng giá')

@push('styles')
<style>
/* ── Tab bar ────────────────────────────────────────────────── */
.svc-tabs        { background:#F0F4FF; border-radius:12px; padding:4px; display:flex; gap:4px; }
.svc-tab-btn     { flex:1; padding:9px 16px; border-radius:9px; border:none; background:transparent;
                   color:#546e7a; font-weight:600; font-size:13.5px; cursor:pointer; transition:.18s; }
.svc-tab-btn.active { background:#fff; color:#0D47A1; box-shadow:0 2px 10px rgba(13,71,161,.13); }

/* ── Stat badge ─────────────────────────────────────────────── */
.svc-stat { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:20px;
            font-size:12.5px; font-weight:600; }

/* ── Danh sách dịch vụ ──────────────────────────────────────── */
.svc-code { background:#F0F4FF; color:#0D47A1; border-radius:5px; padding:2px 8px;
            font-family:monospace; font-size:12px; }
.svc-row:hover td { background:#F7F9FF; }
.svc-name-main  { font-weight:700; font-size:13.5px; color:#1a2332; }
.svc-name-sub   { font-size:11.5px; color:#90A4AE; margin-top:2px; }

/* ── Price cols ─────────────────────────────────────────────── */
.price-normal { font-weight:600; color:#1a2332; }
.price-bhyt   { font-weight:600; color:#2e7d32; }
.price-vip    { font-weight:600; color:#e65100; }

/* ── Bảng giá 3 col ─────────────────────────────────────────── */
.price-card          { border-radius:14px; overflow:hidden; }
.price-card-header   { padding:14px 20px; font-weight:700; font-size:14px; display:flex; align-items:center; gap:8px; }
.price-row-item      { display:flex; justify-content:space-between; align-items:center;
                       padding:10px 20px; border-bottom:1px solid #edf1f7; font-size:13px; }
.price-row-item:last-child { border-bottom:none; }
.price-row-name      { color:#546e7a; }
.price-row-val       { font-weight:700; }

/* ── Lịch sử ────────────────────────────────────────────────── */
.hist-old { text-decoration:line-through; color:#90A4AE; }

/* ── Delete modal ───────────────────────────────────────────── */
.delete-modal-icon { width:64px; height:64px; background:#FEE2E2; border-radius:50%;
                     display:flex; align-items:center; justify-content:center; margin:0 auto 12px; }

/* ── Action buttons ─────────────────────────────────────────── */
.btn-action { width:32px; height:32px; padding:0; display:inline-flex; align-items:center;
              justify-content:center; border-radius:8px; font-size:14px; transition:.15s; }
.btn-action:hover { transform:translateY(-1px); box-shadow:0 3px 8px rgba(0,0,0,.12); }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Header ─────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-clipboard2-pulse me-2 text-primary"></i>Danh Mục Dịch Vụ & Bảng Giá</h4>
            <p class="text-muted small mb-0">Quản lý dịch vụ, bảng giá theo loại và lịch sử thay đổi</p>
        </div>
        @if($tab === 'services')
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary px-4">
            <i class="bi bi-plus-lg me-1"></i>Thêm dịch vụ
        </a>
        @endif
    </div>

    {{-- Alert ───────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tab bar ─────────────────────────────────────────────────── --}}
    <div class="svc-tabs mb-4">
        <button class="svc-tab-btn {{ $tab === 'services' ? 'active' : '' }}"
                onclick="switchTab('services', event)">
            <i class="bi bi-list-ul me-1"></i>Danh sách dịch vụ
        </button>
        <button class="svc-tab-btn {{ $tab === 'prices' ? 'active' : '' }}"
                onclick="switchTab('prices', event)">
            <i class="bi bi-tags me-1"></i>Bảng giá
        </button>
        <button class="svc-tab-btn {{ $tab === 'history' ? 'active' : '' }}"
                onclick="switchTab('history', event)">
            <i class="bi bi-clock-history me-1"></i>Lịch sử thay đổi giá
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         TAB 1: DANH SÁCH DỊCH VỤ
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="tab-services" class="{{ $tab !== 'services' ? 'd-none' : '' }}">

        {{-- Bộ lọc --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="tab" value="services">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm fw-semibold text-muted mb-1">Tìm kiếm</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0"
                                   placeholder="Tìm mã / tên dịch vụ..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm fw-semibold text-muted mb-1">Khoa</label>
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
                        <label class="form-label form-label-sm fw-semibold text-muted mb-1">Trạng thái</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm ngưng</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search me-1"></i>Tìm
                        </button>
                        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary" title="Xoá bộ lọc">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bảng dịch vụ --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:100px">Mã DV</th>
                                <th>Tên dịch vụ</th>
                                <th>Khoa</th>
                                <th class="text-center" style="width:100px">Thời lượng</th>
                                <th class="text-end" style="width:120px">Giá thường</th>
                                <th class="text-end" style="width:120px">Giá BHYT</th>
                                <th class="text-end" style="width:120px">Giá VIP</th>
                                <th class="text-center" style="width:130px">Trạng thái</th>
                                <th class="text-center" style="width:130px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                            @php
                                $priceNormal = $service->activePrices->firstWhere('price_type','Thường');
                                $priceBhyt   = $service->activePrices->firstWhere('price_type','BHYT');
                                $priceVip    = $service->activePrices->firstWhere('price_type','VIP');
                            @endphp
                            <tr class="svc-row">
                                <td class="ps-3">
                                    <span class="svc-code">{{ $service->service_code }}</span>
                                </td>
                                <td>
                                    <div class="svc-name-main">
                                        <a href="{{ route('admin.services.show', $service) }}"
                                           class="text-decoration-none text-reset">
                                            {{ $service->service_name }}
                                        </a>
                                    </div>
                                    @if($service->description)
                                    <div class="svc-name-sub">{{ Str::limit($service->description, 60) }}</div>
                                    @endif
                                </td>
                                <td class="small">{{ $service->department->department_name ?? '—' }}</td>
                                <td class="text-center small">{{ $service->duration_minutes }} phút</td>
                                <td class="text-end">
                                    @if($priceNormal)
                                        <span class="price-normal">{{ number_format($priceNormal->price,0,',','.') }} đ</span>
                                    @else <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($priceBhyt)
                                        <span class="price-bhyt">{{ number_format($priceBhyt->price,0,',','.') }} đ</span>
                                    @else <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($priceVip)
                                        <span class="price-vip">{{ number_format($priceVip->price,0,',','.') }} đ</span>
                                    @else <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($service->status)
                                        <span class="badge bg-success-subtle text-success fw-semibold">Đang hoạt động</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning fw-semibold">Tạm ngưng</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        {{-- Xem --}}
                                        <a href="{{ route('admin.services.show', $service) }}"
                                           class="btn btn-action btn-outline-info"
                                           title="Xem chi tiết" data-bs-toggle="tooltip">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        {{-- Sửa --}}
                                        <a href="{{ route('admin.services.edit', $service) }}"
                                           class="btn btn-action btn-outline-primary"
                                           title="Sửa" data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        {{-- Toggle trạng thái --}}
                                        <form method="POST"
                                              action="{{ route('admin.services.toggle-status', $service) }}"
                                              class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-action btn-outline-{{ $service->status ? 'warning' : 'success' }}"
                                                    title="{{ $service->status ? 'Tạm ngưng' : 'Kích hoạt' }}"
                                                    data-bs-toggle="tooltip">
                                                <i class="bi bi-{{ $service->status ? 'pause-circle' : 'play-circle' }}"></i>
                                            </button>
                                        </form>
                                        {{-- Xoá --}}
                                        <button type="button"
                                                class="btn btn-action btn-outline-danger"
                                                title="Xoá dịch vụ" data-bs-toggle="tooltip"
                                                onclick="confirmDelete({{ $service->service_id }}, '{{ addslashes($service->service_name) }}', '{{ addslashes($service->service_code) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Không tìm thấy dịch vụ nào.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($services->hasPages())
            <div class="card-footer">{{ $services->links() }}</div>
            @endif
        </div>

        <div class="alert d-flex align-items-center gap-2 mt-3"
             style="background:#E3F2FD; border:1px solid #90CAF9; border-radius:10px; color:#1565C0;">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0"></i>
            <span class="small">
                Dịch vụ <strong>tạm ngưng</strong> sẽ không hiển thị khi bệnh nhân đặt lịch.
                Giá ước tính sẽ được hiển thị trước khi xác nhận đặt lịch để giảm thắc mắc khi thanh toán.
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
                <div class="card shadow-sm price-card" style="border-top:3px solid #0D47A1">
                    <div class="price-card-header" style="background:#F0F4FF; color:#0D47A1">
                        <i class="bi bi-person fs-5"></i> Giá Thường
                        <span class="badge ms-auto" style="background:#0D47A1">
                            {{ $pricesByType['Thường']->count() }} dịch vụ
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @forelse($pricesByType['Thường'] as $p)
                        <div class="price-row-item">
                            <span class="price-row-name">{{ $p->service_name }}</span>
                            <span class="price-row-val price-normal">{{ number_format($p->price,0,',','.') }} đ</span>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted small">Chưa có giá</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Giá BHYT --}}
            <div class="col-lg-4">
                <div class="card shadow-sm price-card" style="border-top:3px solid #2e7d32">
                    <div class="price-card-header" style="background:#E8F5E9; color:#2e7d32">
                        <i class="bi bi-shield-check fs-5"></i> Giá BHYT
                        <span class="badge ms-auto bg-success">
                            {{ $pricesByType['BHYT']->count() }} dịch vụ
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @forelse($pricesByType['BHYT'] as $p)
                        <div class="price-row-item">
                            <span class="price-row-name">{{ $p->service_name }}</span>
                            <span class="price-row-val price-bhyt">{{ number_format($p->price,0,',','.') }} đ</span>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted small">Chưa có giá</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Giá VIP --}}
            <div class="col-lg-4">
                <div class="card shadow-sm price-card" style="border-top:3px solid #e65100">
                    <div class="price-card-header" style="background:#FFF3E0; color:#e65100">
                        <i class="bi bi-star fs-5"></i> Giá VIP / Theo yêu cầu
                        <span class="badge ms-auto" style="background:#e65100">
                            {{ $pricesByType['VIP']->count() }} dịch vụ
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @forelse($pricesByType['VIP'] as $p)
                        <div class="price-row-item">
                            <span class="price-row-name">{{ $p->service_name }}</span>
                            <span class="price-row-val price-vip">{{ number_format($p->price,0,',','.') }} đ</span>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted small">Chưa có giá</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="alert d-flex align-items-center gap-2 mt-4"
             style="background:#E3F2FD; border:1px solid #90CAF9; border-radius:10px; color:#1565C0;">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0"></i>
            <span class="small">
                Bảng giá hiển thị các mức <strong>đang có hiệu lực</strong> tại thời điểm này.
                Để chỉnh sửa giá, vào <strong>Danh sách dịch vụ</strong> → Xem chi tiết → mục Bảng giá.
            </span>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         TAB 3: LỊCH SỬ THAY ĐỔI GIÁ
    ═══════════════════════════════════════════════════════════════ --}}
    <div id="tab-history" class="{{ $tab !== 'history' ? 'd-none' : '' }}">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i>
                Lịch sử thay đổi giá
                <span class="badge bg-warning-subtle text-warning ms-2">
                    Tính năng đang được phát triển
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Thời gian</th>
                                <th>Dịch vụ</th>
                                <th>Người sửa</th>
                                <th>Loại giá</th>
                                <th class="text-end">Giá cũ</th>
                                <th class="text-end">Giá mới</th>
                                <th>Lý do</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($priceHistory as $log)
                            @php
                                $typeColors = [
                                    'Thường'       => ['bg-primary-subtle','text-primary'],
                                    'BHYT'         => ['bg-success-subtle','text-success'],
                                    'VIP'          => ['bg-warning-subtle','text-warning'],
                                    'Theo yêu cầu' => ['bg-info-subtle','text-info'],
                                ];
                                [$bg, $fg] = $typeColors[$log->price_type] ?? ['bg-secondary-subtle','text-secondary'];
                            @endphp
                            <tr>
                                <td class="ps-3 small text-muted">
                                    {{ \Carbon\Carbon::parse($log->changed_at)->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="fw-semibold small">{{ $log->service_name }}</div>
                                    <div class="text-muted" style="font-size:11px">{{ $log->service_code }}</div>
                                </td>
                                <td class="small">{{ $log->changed_by_name ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $bg }} {{ $fg }} fw-semibold">{{ $log->price_type }}</span>
                                </td>
                                <td class="text-end">
                                    @if($log->old_price !== null)
                                        <span class="hist-old">{{ number_format($log->old_price,0,',','.') }} đ</span>
                                    @else
                                        <span class="text-muted small">Mới tạo</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold"
                                    style="color:{{ $log->old_price === null ? '#2e7d32' : ($log->new_price > $log->old_price ? '#c62828' : '#2e7d32') }}">
                                    {{ number_format($log->new_price,0,',','.') }} đ
                                </td>
                                <td class="small text-muted">{{ $log->reason ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                                    Chưa có lịch sử thay đổi giá nào.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($priceHistory, 'hasPages') && $priceHistory->hasPages())
            <div class="card-footer">
                {{ $priceHistory->appends(['tab' => 'history'])->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODAL XÁC NHẬN XOÁ DỊCH VỤ
═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-labelledby="deleteServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-4">
                <div class="delete-modal-icon mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-3"></i>
                </div>
                <h5 class="fw-bold mb-1" id="deleteServiceModalLabel">Xác nhận xoá dịch vụ</h5>
                <p class="text-muted small mb-3">
                    Bạn có chắc muốn xoá dịch vụ
                    <strong id="deleteServiceName" class="text-dark"></strong>?<br>
                    <span class="text-danger">Toàn bộ bảng giá liên quan cũng sẽ bị xoá và không thể khôi phục.</span>
                </p>

                <div class="alert alert-warning py-2 px-3 text-start small mb-3" id="deleteWarningBlock">
                    <i class="bi bi-info-circle me-1"></i>
                    <span id="deleteWarningText"></span>
                </div>

                <form id="deleteServiceForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary px-4"
                                data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Huỷ
                        </button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-trash me-1"></i>Xoá dịch vụ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
/* ── Tab switching ─────────────────────────────────────────── */
function switchTab(name, event) {
    document.querySelectorAll('.svc-tab-btn').forEach(b => b.classList.remove('active'));
    event.currentTarget.classList.add('active');
    ['services','prices','history'].forEach(t => {
        document.getElementById('tab-' + t).classList.toggle('d-none', t !== name);
    });
    const url = new URL(window.location);
    url.searchParams.set('tab', name);
    window.history.replaceState({}, '', url);
}

/* ── Delete confirmation ───────────────────────────────────── */
function confirmDelete(serviceId, serviceName, serviceCode) {
    document.getElementById('deleteServiceName').textContent = serviceName + ' (' + serviceCode + ')';
    document.getElementById('deleteServiceForm').action =
        '{{ url("admin/services") }}/' + serviceId;
    document.getElementById('deleteWarningBlock').style.display = 'none';

    // Hiển thị modal
    const modal = new bootstrap.Modal(document.getElementById('deleteServiceModal'));
    modal.show();
}

/* ── Tooltips ─────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(el => new bootstrap.Tooltip(el, { trigger: 'hover' }));
});
</script>
@endpush
@endsection
