@extends('layouts.user')

@section('title', 'Danh sách dịch vụ y tế')

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-primary mb-3">
            <i class="bi bi-hospital"></i> Dịch Vụ Y Tế
        </h1>
        <p class="lead text-muted">
            Các dịch vụ khám chữa bệnh chất lượng cao tại bệnh viện chúng tôi
        </p>
        <div class="divider mx-auto" style="width: 80px; height: 3px; background: linear-gradient(90deg, #0D47A1, #64B5F6);"></div>
    </div>

    {{-- Form tìm kiếm và bộ lọc --}}
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('user.services.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control border-start-0"
                                   placeholder="Tìm theo tên dịch vụ, mã dịch vụ hoặc mô tả..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="department_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Tất cả khoa --</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->department_id }}" 
                                    {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                    {{ $dept->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Tìm kiếm
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('user.services.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-repeat me-1"></i> Làm mới
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Thống kê kết quả --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <span class="badge bg-primary rounded-pill px-3 py-2">
                <i class="bi bi-grid-3x3-gap-fill me-1"></i> {{ $services->total() }} dịch vụ
            </span>
            @if(request('search'))
                <span class="text-muted ms-2">
                    Kết quả tìm kiếm cho "{{ request('search') }}"
                </span>
            @endif
        </div>
        <div class="mt-2 mt-sm-0">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary active" id="gridViewBtn">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Lưới
                </button>
                <button type="button" class="btn btn-outline-primary" id="listViewBtn">
                    <i class="bi bi-list-ul"></i> Danh sách
                </button>
            </div>
        </div>
    </div>

    {{-- Hiển thị dịch vụ dạng Grid --}}
    <div id="gridView">
        <div class="row g-4">
            @forelse($services as $service)
                @php
                    $priceNormal = $service->activePrices->firstWhere('price_type', 'Thường') ?? null;
                    $lowestPrice = $service->activePrices->min('price');
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm hover-card border-0 rounded-4 overflow-hidden">
                        <div class="card-icon text-center pt-4">
                            <div class="service-icon mx-auto">
                                <i class="bi bi-activity fs-1 text-primary"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill">
                                    {{ $service->service_code ?? 'N/A' }}
                                </span>
                                @if($service->department)
                                    <small class="text-muted">
                                        <i class="bi bi-building"></i> {{ $service->department->department_name }}
                                    </small>
                                @endif
                            </div>
                            <h5 class="card-title fw-bold mb-2">
                                <a href="{{ route('user.services.show', $service->service_id) }}" 
                                   class="text-decoration-none text-dark stretched-link">
                                    {{ $service->service_name }}
                                </a>
                            </h5>
                            <p class="card-text text-muted small mb-3">
                                {{ \Illuminate\Support\Str::limit($service->description ?? 'Không có mô tả', 80) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if($lowestPrice)
                                        <span class="text-muted text-decoration-line-through small">
                                            @if($priceNormal)
                                                {{ number_format($priceNormal->price, 0, ',', '.') }}đ
                                            @endif
                                        </span>
                                        <span class="text-danger fw-bold fs-5">
                                            {{ number_format($lowestPrice, 0, ',', '.') }}đ
                                        </span>
                                        <small class="text-success">*</small>
                                    @else
                                        <span class="text-muted">Liên hệ</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-1">
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-clock"></i> {{ $service->duration_minutes ?? 30 }} phút
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-3">
                            <div class="d-flex gap-2">
                                <a href="{{ route('user.services.show', $service->service_id) }}" 
                                   class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="bi bi-eye"></i> Chi tiết
                                </a>
                                <a href="{{ route('appointments.create') }}?service_id={{ $service->service_id }}" 
                                   class="btn btn-primary btn-sm flex-fill">
                                    <i class="bi bi-calendar-plus"></i> Đặt lịch
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">Không tìm thấy dịch vụ nào</h5>
                        <p class="text-muted">Vui lòng thử lại với từ khóa khác.</p>
                        <a href="{{ route('user.services.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i> Xem tất cả
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Hiển thị dịch vụ dạng List (ẩn mặc định) --}}
    <div id="listView" style="display: none;">
        <div class="card shadow-sm border-0">
            <div class="list-group list-group-flush">
                @forelse($services as $service)
                    <div class="list-group-item p-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-activity fs-4 text-primary"></i>
                                    <div>
                                        <h6 class="mb-1 fw-bold">
                                            <a href="{{ route('user.services.show', $service->service_id) }}" 
                                               class="text-decoration-none text-dark">
                                                {{ $service->service_name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">Mã: {{ $service->service_code ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Khoa phụ trách</small>
                                <span>{{ $service->department->department_name ?? '—' }}</span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Thời gian</small>
                                <span><i class="bi bi-clock"></i> {{ $service->duration_minutes ?? 30 }} phút</span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Giá từ</small>
                                <span class="text-danger fw-bold">
                                    @php $lowest = $service->activePrices->min('price'); @endphp
                                    @if($lowest)
                                        {{ number_format($lowest, 0, ',', '.') }}đ
                                    @else
                                        <span class="text-muted">Liên hệ</span>
                                    @endif
                                </span>
                            </div>
                            <div class="col-md-1 text-end">
                                <a href="{{ route('user.services.show', $service->service_id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="mt-2 text-muted">Không tìm thấy dịch vụ nào</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Phân trang --}}
<div class="mt-5">
    <div class="d-flex justify-content-center">
        {{ $services->links('pagination::bootstrap-5') }}
    </div>
    
    @if($services->hasPages())
    <div class="text-center mt-3">
        <span class="text-muted">
            Hiển thị <strong>{{ $services->firstItem() }}</strong> 
            đến <strong>{{ $services->lastItem() }}</strong> 
            trong tổng số <strong>{{ $services->total() }}</strong> kết quả
        </span>
    </div>
    @endif
</div>

    {{-- FAQ hoặc thông tin thêm --}}
    <div class="row mt-5 g-4">
        <div class="col-md-4">
            <div class="text-center">
                <i class="bi bi-shield-check fs-2 text-primary"></i>
                <h6 class="mt-2 fw-bold">BHYT & Bảo hiểm</h6>
                <p class="small text-muted">Dịch vụ được hỗ trợ BHYT theo quy định</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center">
                <i class="bi bi-star fs-2 text-warning"></i>
                <h6 class="mt-2 fw-bold">Chất lượng cao</h6>
                <p class="small text-muted">Đội ngũ bác sĩ giàu kinh nghiệm</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center">
                <i class="bi bi-calendar-check fs-2 text-success"></i>
                <h6 class="mt-2 fw-bold">Đặt lịch dễ dàng</h6>
                <p class="small text-muted">Đặt lịch nhanh chóng qua hệ thống</p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15) !important;
    }
    .service-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #E3F2FD, #BBDEFB);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pagination {
        --bs-pagination-active-bg: #0D47A1;
        --bs-pagination-active-border-color: #0D47A1;
    }
    .bg-primary-subtle {
        background-color: #E3F2FD !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Toggle giữa Grid và List view
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');

    // Lấy mode từ localStorage
    const viewMode = localStorage.getItem('serviceViewMode') || 'grid';
    
    if (viewMode === 'list') {
        gridView.style.display = 'none';
        listView.style.display = 'block';
        gridBtn.classList.remove('active');
        listBtn.classList.add('active');
    }

    gridBtn.addEventListener('click', () => {
        gridView.style.display = 'block';
        listView.style.display = 'none';
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
        localStorage.setItem('serviceViewMode', 'grid');
    });

    listBtn.addEventListener('click', () => {
        gridView.style.display = 'none';
        listView.style.display = 'block';
        gridBtn.classList.remove('active');
        listBtn.classList.add('active');
        localStorage.setItem('serviceViewMode', 'list');
    });
</script>
@endpush

@endsection