@extends('layouts.user')

@section('title', 'Danh mục dịch vụ y tế & Bảng giá')

@section('content')
<!-- Google Fonts Outfit & Inter -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');
    
    .public-services-page {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        color: #1e293b;
    }
    
    .public-services-page h1, 
    .public-services-page h2, 
    .public-services-page h3, 
    .public-services-page h4, 
    .public-services-page h5, 
    .public-services-page h6,
    .public-services-page .font-outfit {
        font-family: 'Outfit', sans-serif;
    }
</style>

<div class="public-services-page py-5">
    <div class="container">
        
        {{-- Hero Banner Header --}}
        <div class="text-center mb-5 p-5 bg-white border rounded-4 shadow-sm position-relative overflow-hidden" style="border-color: #e2e8f0 !important;">
            <!-- Decorative soft background shapes -->
            <div class="position-absolute top-0 start-0 translate-middle" style="width: 300px; height: 300px; background: radial-gradient(circle, rgba(13,110,253,0.08) 0%, transparent 70%); pointer-events: none;"></div>
            <div class="position-absolute bottom-0 end-0 translate-middle-y" style="width: 250px; height: 250px; background: radial-gradient(circle, rgba(16,185,129,0.06) 0%, transparent 70%); pointer-events: none;"></div>

            <div class="position-relative">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-3 font-outfit fw-bold tracking-wide">
                    <i class="bi bi-shield-fill-check me-1"></i> BẢNG GIÁ CÔNG KHAI & ĐẦY ĐỦ
                </span>
                <h1 class="display-4 fw-extrabold text-dark mb-3 font-outfit" style="letter-spacing: -1px;">
                    Dịch Vụ Y Tế & <span class="text-primary">Bảng Giá Đa Tầng</span>
                </h1>
                <p class="lead text-muted mx-auto" style="max-width: 650px; font-size: 1.1rem; line-height: 1.6;">
                    Tra cứu bảng giá dịch vụ khám chữa bệnh công khai. Hệ thống hỗ trợ đa dạng mức giá linh hoạt (Giá thường, Bảo hiểm y tế BHYT, Giá khám VIP) phù hợp mọi nhu cầu.
                </p>
                <div class="divider mx-auto mt-4" style="width: 60px; height: 4px; background: #0d6efd; border-radius: 2px;"></div>
            </div>
        </div>

        {{-- Form tìm kiếm và bộ lọc --}}
        <div class="card shadow-sm border-0 mb-5 rounded-4 bg-white overflow-hidden" style="box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05) !important;">
            <div class="card-body p-4 bg-white">
                <form method="GET" action="{{ route('user.services.index') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted fw-bold font-outfit mb-2"><i class="bi bi-search me-1"></i> Từ khóa tìm kiếm</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px;">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       id="searchInput"
                                       class="form-control border-start-0 bg-light"
                                       style="border-radius: 0 10px 10px 0; height: 45px; font-size: 14px;"
                                       placeholder="Tìm theo tên dịch vụ, mã dịch vụ..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted fw-bold font-outfit mb-2"><i class="bi bi-building me-1"></i> Chuyên khoa</label>
                            <select name="department_id" id="deptSelect" class="form-select bg-light" style="height: 45px; font-size: 14px; border-radius: 10px;">
                                <option value="">-- Tất cả chuyên khoa --</option>
                                @foreach($departments ?? [] as $dept)
                                    <option value="{{ $dept->department_id }}" 
                                        {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" 
                                    id="toggleFiltersBtn"
                                    class="btn btn-outline-primary w-100 font-outfit fw-bold d-flex align-items-center justify-content-center gap-1" 
                                    style="height: 45px; border-radius: 10px;"
                                    onclick="toggleAdvancedFilters()">
                                <i class="bi bi-sliders"></i> Bộ lọc nâng cao <i id="advancedFilterIcon" class="bi bi-chevron-down small"></i>
                            </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('user.services.index') }}" class="btn btn-outline-secondary w-100 font-outfit fw-semibold d-flex align-items-center justify-content-center gap-1" style="height: 45px; border-radius: 10px;">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </a>
                        </div>
                    </div>

                    {{-- Collapsible Advanced Filters Section --}}
                    <div id="advancedFilters" class="mt-4 pt-3 border-top" style="display: none; border-top-style: dashed !important; border-color: #cbd5e1 !important; transition: all 0.3s ease;">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold font-outfit mb-2"><i class="bi bi-tags me-1"></i> Phân khúc gói giá</label>
                                <select name="price_tier" id="priceTierSelect" class="form-select bg-light" style="height: 45px; font-size: 14px; border-radius: 10px;">
                                    <option value="">-- Tất cả mức giá --</option>
                                    <option value="BHYT" {{ request('price_tier') === 'BHYT' ? 'selected' : '' }}>Hỗ trợ chi trả BHYT</option>
                                    <option value="VIP" {{ request('price_tier') === 'VIP' ? 'selected' : '' }}>Có gói VIP cao cấp</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold font-outfit mb-2"><i class="bi bi-clock me-1"></i> Thời lượng khám</label>
                                <select name="duration_range" id="durationRangeSelect" class="form-select bg-light" style="height: 45px; font-size: 14px; border-radius: 10px;">
                                    <option value="">-- Tất cả thời lượng --</option>
                                    <option value="fast" {{ request('duration_range') === 'fast' ? 'selected' : '' }}>Khám nhanh (&lt; 30 phút)</option>
                                    <option value="medium" {{ request('duration_range') === 'medium' ? 'selected' : '' }}>Khám tiêu chuẩn (30 - 60 phút)</option>
                                    <option value="long" {{ request('duration_range') === 'long' ? 'selected' : '' }}>Khám chuyên sâu (&gt; 60 phút)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted fw-bold font-outfit mb-2"><i class="bi bi-sort-alpha-down me-1"></i> Sắp xếp kết quả</label>
                                <select name="sort_by" id="sortBySelect" class="form-select bg-light" style="height: 45px; font-size: 14px; border-radius: 10px;">
                                    <option value="name_asc" {{ request('sort_by') === 'name_asc' ? 'selected' : '' }}>Tên dịch vụ A - Z (Mặc định)</option>
                                    <option value="name_desc" {{ request('sort_by') === 'name_desc' ? 'selected' : '' }}>Tên dịch vụ Z - A</option>
                                    <option value="price_asc" {{ request('sort_by') === 'price_asc' ? 'selected' : '' }}>Giá dịch vụ: Thấp tới Cao</option>
                                    <option value="price_desc" {{ request('sort_by') === 'price_desc' ? 'selected' : '' }}>Giá dịch vụ: Cao xuống Thấp</option>
                                    <option value="duration_asc" {{ request('sort_by') === 'duration_asc' ? 'selected' : '' }}>Thời gian thực hiện ngắn nhất</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Thống kê kết quả --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <div>
                <span class="badge bg-primary text-white rounded-pill px-3 py-2 font-outfit fw-semibold" id="svcCountBadge" style="font-size: 13px;">
                    <i class="bi bi-grid-3x3-gap-fill me-1"></i> Tổng số: {{ $services->total() }} dịch vụ
                </span>
                <span class="spinner-border spinner-border-sm text-primary ms-3 d-none" id="searchSpinner" role="status"></span>
            </div>
            <div class="mt-2 mt-sm-0">
                <div class="btn-group btn-group-sm p-1 bg-white border rounded-3" role="group">
                    <button type="button" class="btn btn-outline-primary border-0 active" id="gridViewBtn" style="border-radius: 6px;">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Lưới
                    </button>
                    <button type="button" class="btn btn-outline-primary border-0" id="listViewBtn" style="border-radius: 6px;">
                        <i class="bi bi-list-ul"></i> Danh sách
                    </button>
                </div>
            </div>
        </div>

        {{-- Dynamic Helper icons mapping --}}
        @php
            if (!function_exists('getServiceIcon')) {
                function getServiceIcon($name, $deptName) {
                    $name = strtolower($name);
                    $deptName = strtolower($deptName);
                    if (str_contains($name, 'tim') || str_contains($name, 'mạch') || str_contains($deptName, 'tim')) {
                        return ['icon' => 'bi-heart-pulse-fill', 'class' => 'icon-danger'];
                    }
                    if (str_contains($name, 'răng') || str_contains($name, 'hàm') || str_contains($deptName, 'răng')) {
                        return ['icon' => 'bi-emoji-smile-fill', 'class' => 'icon-info'];
                    }
                    if (str_contains($name, 'nhi') || str_contains($deptName, 'nhi')) {
                        return ['icon' => 'bi-balloon-fill', 'class' => 'icon-warning'];
                    }
                    if (str_contains($name, 'não') || str_contains($name, 'thần kinh') || str_contains($deptName, 'thần kinh')) {
                        return ['icon' => 'bi-cpu-fill', 'class' => 'icon-purple'];
                    }
                    if (str_contains($name, 'xét nghiệm') || str_contains($name, 'máu') || str_contains($deptName, 'xét nghiệm')) {
                        return ['icon' => 'bi-droplet-fill', 'class' => 'icon-primary'];
                    }
                    if (str_contains($name, 'khám tổng quát') || str_contains($name, 'tổng quát') || str_contains($deptName, 'tổng quát')) {
                        return ['icon' => 'bi-stethoscope', 'class' => 'icon-success'];
                    }
                    if (str_contains($name, 'mắt') || str_contains($name, 'nhãn khoa') || str_contains($deptName, 'mắt')) {
                        return ['icon' => 'bi-eye-fill', 'class' => 'icon-teal'];
                    }
                    return ['icon' => 'bi-activity', 'class' => 'icon-primary'];
                }
            }
        @endphp

        {{-- Hiển thị dịch vụ dạng Grid --}}
        <div id="gridView">
            <div class="row g-4" id="servicesGridContainer">
                @forelse($services as $service)
                    @php
                        $priceNormal = $service->activePrices->first(fn($p) => str_contains(strtolower($p->price_type), 'thường') || str_contains(strtolower($p->price_type), 'normal')) ?? $service->activePrices->first();
                        $priceBhyt = $service->activePrices->first(fn($p) => str_contains(strtolower($p->price_type), 'bhyt') || str_contains(strtolower($p->price_type), 'bảo hiểm'));
                        $priceVip = $service->activePrices->first(fn($p) => str_contains(strtolower($p->price_type), 'vip') || str_contains(strtolower($p->price_type), 'cao cấp'));
                        $lowestPrice = $service->activePrices->min('price');
                        $iconData = getServiceIcon($service->service_name, $service->department?->department_name ?? '');
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm hover-card border-0 rounded-4 overflow-hidden bg-white" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                            <div class="card-icon text-center pt-4">
                                <div class="service-icon-wrapper mx-auto {{ $iconData['class'] }}">
                                    <i class="bi {{ $iconData['icon'] }} fs-2"></i>
                                </div>
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold font-outfit" style="font-size: 11px;">
                                        {{ $service->service_code ?? 'N/A' }}
                                    </span>
                                    @if($service->department)
                                        <small class="text-muted font-outfit fw-medium">
                                            <i class="bi bi-building me-1"></i> {{ $service->department->department_name }}
                                        </small>
                                    @endif
                                </div>
                                <h5 class="card-title fw-bold text-dark font-outfit mb-2 lh-base">
                                    {{ $service->service_name }}
                                </h5>
                                <p class="card-text text-muted small mb-3 flex-grow-1" style="line-height: 1.5;">
                                    {{ \Illuminate\Support\Str::limit($service->description ?? 'Chưa cập nhật thuyết minh mô tả cho dịch vụ y khoa này.', 90) }}
                                </p>

                                {{-- Multi-tier price list on card --}}
                                <div class="pricing-tiers mt-auto pt-3 border-top" style="border-top-style: dashed !important; border-color: #e2e8f0 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small"><i class="bi bi-cash me-1"></i> Giá Thường</span>
                                        <span class="fw-bold text-dark font-outfit" style="font-size: 14px;">
                                            {{ $priceNormal ? number_format($priceNormal->price, 0, ',', '.') . 'đ' : 'Liên hệ' }}
                                        </span>
                                    </div>
                                    @if($priceBhyt)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-success small fw-semibold"><i class="bi bi-shield-check me-1"></i> Có BHYT</span>
                                        <span class="fw-bold text-success font-outfit" style="font-size: 14px;">
                                            {{ number_format($priceBhyt->price, 0, ',', '.') }}đ
                                        </span>
                                    </div>
                                    @endif
                                    @if($priceVip)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-purple small fw-semibold"><i class="bi bi-gem me-1"></i> Khám VIP</span>
                                        <span class="fw-bold text-purple font-outfit" style="font-size: 14px;">
                                            {{ number_format($priceVip->price, 0, ',', '.') }}đ
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="card-footer bg-light border-0 px-4 pb-4 pt-0">
                                <div class="d-flex gap-2">
                                    <button onclick="openQuickView({{ $service->service_id }})" 
                                            class="btn btn-outline-primary btn-sm flex-fill font-outfit fw-bold" 
                                            style="height: 38px; border-radius: 8px;">
                                        <i class="bi bi-eye"></i> Xem nhanh
                                    </button>
                                    <a href="{{ route('user.services.show', $service->service_id) }}" 
                                       class="btn btn-primary btn-sm flex-fill font-outfit fw-bold d-flex align-items-center justify-content-center gap-1" 
                                       style="height: 38px; border-radius: 8px;">
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
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-white">
                <div class="list-group list-group-flush" id="servicesListContainer">
                    @forelse($services as $service)
                        @php
                            $priceNormal = $service->activePrices->first(fn($p) => str_contains(strtolower($p->price_type), 'thường') || str_contains(strtolower($p->price_type), 'normal')) ?? $service->activePrices->first();
                            $priceBhyt = $service->activePrices->first(fn($p) => str_contains(strtolower($p->price_type), 'bhyt') || str_contains(strtolower($p->price_type), 'bảo hiểm'));
                            $priceVip = $service->activePrices->first(fn($p) => str_contains(strtolower($p->price_type), 'vip') || str_contains(strtolower($p->price_type), 'cao cấp'));
                            $lowestPrice = $service->activePrices->min('price');
                            $iconData = getServiceIcon($service->service_name, $service->department?->department_name ?? '');
                        @endphp
                        <div class="list-group-item p-4 bg-white" style="border-color: #f1f5f9 !important;">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-4 col-md-5">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="service-icon-wrapper-small {{ $iconData['class'] }}">
                                            <i class="bi {{ $iconData['icon'] }} fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary px-2 py-0.5 rounded mb-1 font-outfit" style="font-size: 10px;">
                                                {{ $service->service_code ?? 'N/A' }}
                                            </span>
                                            <h6 class="mb-0 fw-bold text-dark font-outfit">{{ $service->service_name }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <small class="text-muted d-block small font-outfit">Khoa phụ trách</small>
                                    <span class="fw-semibold text-dark font-outfit" style="font-size: 14px;">
                                        {{ $service->department->department_name ?? '—' }}
                                    </span>
                                </div>
                                <div class="col-lg-3 col-md-3">
                                    <small class="text-muted d-block small font-outfit">Mức giá BHYT / VIP</small>
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        @if($priceBhyt)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle font-outfit">BHYT: {{ number_format($priceBhyt->price, 0, ',', '.') }}đ</span>
                                        @endif
                                        @if($priceVip)
                                            <span class="badge bg-purple-subtle text-purple border border-purple-subtle font-outfit">VIP: {{ number_format($priceVip->price, 0, ',', '.') }}đ</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-12 text-end d-flex gap-2 justify-content-end">
                                    <button onclick="openQuickView({{ $service->service_id }})" 
                                            class="btn btn-outline-primary btn-sm px-3 font-outfit fw-bold" 
                                            style="height: 38px; border-radius: 8px;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('user.services.show', $service->service_id) }}" 
                                       class="btn btn-primary btn-sm px-3 font-outfit fw-bold d-flex align-items-center gap-1" 
                                       style="height: 38px; border-radius: 8px;">
                                        <i class="bi bi-calendar-plus"></i> Đặt lịch
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

        {{-- Phân trang (Hidden on live dynamic search if needed, but standard boot-5 handles it nicely) --}}
        <div class="mt-5" id="paginationSection">
            <div class="d-flex justify-content-center">
                {{ $services->links('pagination::bootstrap-5') }}
            </div>
            
            @if($services->hasPages())
            <div class="text-center mt-3" id="paginationStatus">
                <span class="text-muted">
                    Hiển thị <strong>{{ $services->firstItem() }}</strong> 
                    đến <strong>{{ $services->lastItem() }}</strong> 
                    trong tổng số <strong>{{ $services->total() }}</strong> kết quả
                </span>
            </div>
            @endif
        </div>

        {{-- Professional Info Cards --}}
        <div class="row mt-5 g-4">
            <div class="col-md-4">
                <div class="text-center p-4 bg-white rounded-4 border" style="border-color: #e2e8f0 !important;">
                    <div class="icon-circle bg-primary-subtle text-primary mx-auto mb-3">
                        <i class="bi bi-shield-plus fs-3"></i>
                    </div>
                    <h6 class="fw-bold font-outfit mb-2 text-dark">Hỗ trợ BHYT theo quy định</h6>
                    <p class="small text-muted mb-0 leading-relaxed">Giảm thiểu chi phí cho bệnh nhân có thẻ BHYT hợp lệ theo quy chuẩn hiện hành.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4 bg-white rounded-4 border" style="border-color: #e2e8f0 !important;">
                    <div class="icon-circle bg-warning-subtle text-warning mx-auto mb-3">
                        <i class="bi bi-star-fill fs-3"></i>
                    </div>
                    <h6 class="fw-bold font-outfit mb-2 text-dark">Chuyên môn chuẩn quốc tế</h6>
                    <p class="small text-muted mb-0 leading-relaxed">Đội ngũ giáo sư, bác sĩ đầu ngành cùng cơ sở vật chất trang thiết bị y tế cao cấp bậc nhất.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4 bg-white rounded-4 border" style="border-color: #e2e8f0 !important;">
                    <div class="icon-circle bg-success-subtle text-success mx-auto mb-3">
                        <i class="bi bi-calendar-check-fill fs-3"></i>
                    </div>
                    <h6 class="fw-bold font-outfit mb-2 text-dark">Đặt khám trực tuyến 24/7</h6>
                    <p class="small text-muted mb-0 leading-relaxed">Lựa chọn khung giờ hẹn khám thông minh, thủ tục tinh gọn, tiết kiệm thời gian chờ đợi.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick View Popup Modal --}}
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden bg-white">
            <div class="modal-header border-0 bg-light p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-service-icon-container" id="modalIconContainer">
                        <i class="bi bi-activity fs-2" id="modalIcon"></i>
                    </div>
                    <div>
                        <span class="badge bg-primary-subtle text-primary mb-1 font-outfit fw-bold" id="modalCode" style="font-size: 11px;"></span>
                        <h4 class="modal-title fw-bold text-dark font-outfit mb-0" id="modalTitle"></h4>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="row g-4 mb-2">
                    <div class="col-md-7">
                        <h6 class="fw-bold text-secondary font-outfit mb-2"><i class="bi bi-card-text me-1"></i> Mô tả chi tiết dịch vụ</h6>
                        <p class="text-dark leading-relaxed mb-4" id="modalDesc" style="font-size: 14px; line-height: 1.6; text-align: justify;"></p>
                        
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="badge bg-light text-dark p-2 border font-outfit fw-semibold" style="font-size: 12px; border-radius: 8px;">
                                <i class="bi bi-building me-1"></i> Khoa: <span id="modalDept" class="text-primary"></span>
                            </span>
                            <span class="badge bg-light text-dark p-2 border font-outfit fw-semibold" style="font-size: 12px; border-radius: 8px;">
                                <i class="bi bi-clock me-1"></i> Thời gian: <span id="modalDuration" class="text-primary"></span> phút
                            </span>
                        </div>
                    </div>
                    <div class="col-md-5 p-4 rounded-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                        <h6 class="fw-bold text-secondary font-outfit mb-3"><i class="bi bi-tags-fill me-1"></i> Bảng giá chi tiết</h6>
                        <div class="d-flex flex-column gap-2" id="modalPrices">
                            <!-- Dynamic price lists render here -->
                        </div>
                    </div>
                </div>

                {{-- Quy trình thăm khám & Cam kết chất lượng --}}
                <hr style="border-top-style: dashed !important; border-color: #cbd5e1 !important;" class="my-4">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <h6 class="fw-bold text-secondary font-outfit mb-3"><i class="bi bi-patch-check-fill text-primary me-1"></i> Quy trình thăm khám tiêu chuẩn</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center border h-100">
                                    <div class="fw-bold text-dark font-outfit mb-1" style="font-size: 13px;">B1: Đăng ký hẹn</div>
                                    <span class="text-muted d-block" style="font-size: 11px;">Chọn khoa, giờ & bác sĩ online</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center border h-100">
                                    <div class="fw-bold text-dark font-outfit mb-1" style="font-size: 13px;">B2: Tiếp đón</div>
                                    <span class="text-muted d-block" style="font-size: 11px;">Xác thực hồ sơ tại quầy nhanh</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center border h-100">
                                    <div class="fw-bold text-dark font-outfit mb-1" style="font-size: 13px;">B3: Khám bệnh</div>
                                    <span class="text-muted d-block" style="font-size: 11px;">Bác sĩ chuyên khoa chẩn đoán</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 text-center border h-100">
                                    <div class="fw-bold text-dark font-outfit mb-1" style="font-size: 13px;">B4: Nhận thuốc</div>
                                    <span class="text-muted d-block" style="font-size: 11px;">Nhận kết quả & cấp thuốc BHYT</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h6 class="fw-bold text-secondary font-outfit mb-3"><i class="bi bi-shield-lock-fill text-success me-1"></i> Cam kết & Tiện ích kèm theo</h6>
                        <ul class="list-unstyled d-flex flex-column gap-2 mb-0" style="font-size: 13.5px;">
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-fill-check text-success fs-5"></i>
                                <span>Hỗ trợ bảo hiểm y tế (BHYT) giảm tới 80% chi phí.</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-patch-check text-primary fs-5"></i>
                                <span>Đội ngũ bác sĩ đầu ngành trực tiếp hội chẩn, kê đơn.</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-lightning-charge text-warning fs-5"></i>
                                <span>Thời gian chờ đợi trung bình dưới 15 phút nhờ hẹn giờ online.</span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="bi bi-laptop text-info fs-5"></i>
                                <span>Nhận kết quả xét nghiệm, lịch sử bệnh án qua sổ điện tử.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 bg-light d-flex gap-2">
                <a href="#" class="btn btn-outline-primary flex-fill font-outfit fw-bold py-2" id="modalShowBtn" style="border-radius: 10px;">
                    <i class="bi bi-eye"></i> Xem trang chi tiết
                </a>
                <a href="#" class="btn btn-primary flex-fill font-outfit fw-bold py-2" id="modalBookBtn" style="border-radius: 10px;">
                    <i class="bi bi-calendar-plus"></i> Đặt hẹn ngay
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-card {
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
    }
    .hover-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -15px rgba(13,110,253,0.15) !important;
        border-color: rgba(13,110,253,0.3) !important;
    }
    
    /* Elegant dynamic icon styles */
    .service-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    .service-icon-wrapper-small {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-service-icon-container {
        width: 54px;
        height: 54px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Vibrant dynamic specialty themes */
    .icon-danger {
        background: linear-gradient(135deg, #fff5f5, #ffe3e3);
        color: #fa5252;
        box-shadow: 0 4px 10px rgba(250,82,82,0.1);
    }
    .icon-info {
        background: linear-gradient(135deg, #eef9ff, #d0ebff);
        color: #228be6;
        box-shadow: 0 4px 10px rgba(34,139,247,0.1);
    }
    .icon-warning {
        background: linear-gradient(135deg, #fff9db, #fff3bf);
        color: #f59f00;
        box-shadow: 0 4px 10px rgba(245,159,0,0.1);
    }
    .icon-purple {
        background: linear-gradient(135deg, #f3f0ff, #e5dbff);
        color: #7950f2;
        box-shadow: 0 4px 10px rgba(121,80,242,0.1);
    }
    .icon-primary {
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: #4f46e5;
        box-shadow: 0 4px 10px rgba(79,70,229,0.1);
    }
    .icon-success {
        background: linear-gradient(135deg, #ebfbee, #d3f9d8);
        color: #40c057;
        box-shadow: 0 4px 10px rgba(64,192,87,0.1);
    }
    .icon-teal {
        background: linear-gradient(135deg, #e6fcf5, #c3fae8);
        color: #0ca678;
        box-shadow: 0 4px 10px rgba(12,166,120,0.1);
    }
    
    /* Pagination colors */
    .pagination {
        --bs-pagination-active-bg: #0d6efd;
        --bs-pagination-active-border-color: #0d6efd;
    }
    
    /* Secondary accent tags */
    .bg-primary-subtle {
        background-color: #e6f0fa !important;
    }
    .bg-success-subtle {
        background-color: #e6f6ec !important;
        color: #0f8f46 !important;
    }
    .bg-purple-subtle {
        background-color: #f3ebfc !important;
        color: #6a1b9a !important;
    }
    
    /* Info circles */
    .icon-circle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@push('scripts')
<script>
    // Global data references for quick lookups
    const SERVICES_JSON_ROUTE = '{{ route("user.services.data") }}';
    const PUBLIC_DETAILS_ROUTE_BASE = '{{ route("user.services.index") }}';
    const BOOKING_ROUTE_BASE = '{{ route("appointments.create") }}';
    
    // View state
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    
    // Quick View Modal elements
    const quickViewModal = new bootstrap.Modal(document.getElementById('quickViewModal'));
    const modalIconContainer = document.getElementById('modalIconContainer');
    const modalIcon = document.getElementById('modalIcon');
    const modalCode = document.getElementById('modalCode');
    const modalTitle = document.getElementById('modalTitle');
    const modalDesc = document.getElementById('modalDesc');
    const modalDept = document.getElementById('modalDept');
    const modalDuration = document.getElementById('modalDuration');
    const modalPrices = document.getElementById('modalPrices');
    const modalShowBtn = document.getElementById('modalShowBtn');
    const modalBookBtn = document.getElementById('modalBookBtn');

    // Retrieve active view from localStorage
    const savedMode = localStorage.getItem('serviceViewMode') || 'grid';
    if (savedMode === 'list') {
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

    // Helper functions for mapping dynamic icons in JavaScript
    function getSpecialtyIconJs(name, deptName) {
        name = (name || '').toLowerCase();
        deptName = (deptName || '').toLowerCase();
        
        if (name.includes('tim') || name.includes('mạch') || deptName.includes('tim')) {
            return { icon: 'bi-heart-pulse-fill', class: 'icon-danger' };
        }
        if (name.includes('răng') || name.includes('hàm') || deptName.includes('răng')) {
            return { icon: 'bi-emoji-smile-fill', class: 'icon-info' };
        }
        if (name.includes('nhi') || deptName.includes('nhi')) {
            return { icon: 'bi-balloon-fill', class: 'icon-warning' };
        }
        if (name.includes('não') || name.includes('thần kinh') || deptName.includes('thần kinh')) {
            return { icon: 'bi-cpu-fill', class: 'icon-purple' };
        }
        if (name.includes('xét nghiệm') || name.includes('máu') || deptName.includes('xét nghiệm')) {
            return { icon: 'bi-droplet-fill', class: 'icon-primary' };
        }
        if (name.includes('khám tổng quát') || name.includes('tổng quát') || deptName.includes('tổng quát')) {
            return { icon: 'bi-stethoscope', class: 'icon-success' };
        }
        if (name.includes('mắt') || name.includes('nhãn khoa') || deptName.includes('mắt')) {
            return { icon: 'bi-eye-fill', class: 'icon-teal' };
        }
        return { icon: 'bi-activity', class: 'icon-primary' };
    }

    // Dynamic Quick View Fetch
    window.openQuickView = function(serviceId) {
        // Fetch detailed service parameters directly from API
        fetch(`${SERVICES_JSON_ROUTE}?search=&department_id=`)
            .then(res => res.json())
            .then(data => {
                const service = data.services.find(s => s.service_id === serviceId);
                if (service) {
                    const icons = getSpecialtyIconJs(service.service_name, service.department);
                    
                    // Assign icon classes
                    modalIconContainer.className = `modal-service-icon-container ${icons.class}`;
                    modalIcon.className = `bi ${icons.icon} fs-2`;
                    
                    modalCode.textContent = service.service_code || 'N/A';
                    modalTitle.textContent = service.service_name;
                    modalDesc.textContent = service.description || 'Chưa cập nhật nội dung giới thiệu chi tiết cho danh mục dịch vụ khám bệnh này.';
                    modalDept.textContent = service.department || 'Phòng khám chung';
                    modalDuration.textContent = service.duration_minutes || 30;
                    
                    // Populate multi-tier prices
                    let priceHtml = `
                        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 border mb-2 shadow-sm">
                            <span class="text-muted small fw-semibold font-outfit"><i class="bi bi-tag-fill me-1"></i> Giá Thường</span>
                            <span class="fw-bold text-dark font-outfit" style="font-size: 15px;">${service.price_normal ? parseInt(service.price_normal).toLocaleString('vi-VN') + 'đ' : 'Liên hệ'}</span>
                        </div>
                    `;
                    
                    if (service.price_bhyt) {
                        priceHtml += `
                            <div class="d-flex justify-content-between align-items-center p-3 rounded-3 border mb-2 shadow-sm" style="background-color: #e6f6ec; border-color: #bbf7d0 !important;">
                                <span class="text-success small fw-semibold font-outfit"><i class="bi bi-shield-plus me-1"></i> Hỗ trợ BHYT</span>
                                <span class="fw-bold text-success font-outfit" style="font-size: 15px;">${parseInt(service.price_bhyt).toLocaleString('vi-VN')}đ</span>
                            </div>
                        `;
                    }
                    if (service.price_vip) {
                        priceHtml += `
                            <div class="d-flex justify-content-between align-items-center p-3 rounded-3 border shadow-sm" style="background-color: #f3ebfc; border-color: #e9d5ff !important;">
                                <span class="text-purple small fw-semibold font-outfit"><i class="bi bi-gem me-1"></i> Khám VIP</span>
                                <span class="fw-bold text-purple font-outfit" style="font-size: 15px;">${parseInt(service.price_vip).toLocaleString('vi-VN')}đ</span>
                            </div>
                        `;
                    }
                    
                    modalPrices.innerHTML = priceHtml;
                    
                    // Update actions links
                    modalShowBtn.href = `${PUBLIC_DETAILS_ROUTE_BASE}/${service.service_id}`;
                    modalBookBtn.href = `${PUBLIC_DETAILS_ROUTE_BASE}/${service.service_id}`;
                    
                    // Show modal
                    quickViewModal.show();
                }
            })
            .catch(err => console.error("Error loading quick view details:", err));
    }

    // ── DEBOUNCED LIVE AJAX FILTER ─────────────────────────────────────────
    const searchInput = document.getElementById('searchInput');
    const deptSelect = document.getElementById('deptSelect');
    const searchSpinner = document.getElementById('searchSpinner');
    const svcCountBadge = document.getElementById('svcCountBadge');
    
    const servicesGridContainer = document.getElementById('servicesGridContainer');
    const servicesListContainer = document.getElementById('servicesListContainer');
    const paginationSection = document.getElementById('paginationSection');
    
    let debounceTimer;
    
    function performLiveSearch() {
        searchSpinner.classList.remove('d-none');
        
        const params = new URLSearchParams({
            search: searchInput.value,
            department_id: deptSelect.value,
            price_tier: document.getElementById('priceTierSelect').value,
            duration_range: document.getElementById('durationRangeSelect').value,
            sort_by: document.getElementById('sortBySelect').value
        });
        
        fetch(`${SERVICES_JSON_ROUTE}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            // Update total badge
            svcCountBadge.innerHTML = `<i class="bi bi-grid-3x3-gap-fill me-1"></i> Tổng số: ${data.total} dịch vụ`;
            
            // Rebuild Grid View
            let gridHtml = '';
            let listHtml = '';
            
            if (data.services.length === 0) {
                const emptyStateHtml = `
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">Không tìm thấy dịch vụ nào</h5>
                            <p class="text-muted">Vui lòng thử lại với từ khóa hoặc chuyên khoa khác.</p>
                        </div>
                    </div>
                `;
                gridHtml = emptyStateHtml;
                listHtml = `<div class="list-group-item text-center py-5"><i class="bi bi-inbox fs-1 text-muted"></i><p class="mt-2 text-muted">Không tìm thấy dịch vụ nào</p></div>`;
            } else {
                data.services.forEach(s => {
                    const icons = getSpecialtyIconJs(s.service_name, s.department);
                    
                    // Grid template
                    gridHtml += `
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm hover-card border-0 rounded-4 overflow-hidden bg-white" style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                <div class="card-icon text-center pt-4">
                                    <div class="service-icon-wrapper mx-auto ${icons.class}">
                                        <i class="bi ${icons.icon} fs-2"></i>
                                    </div>
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold font-outfit" style="font-size: 11px;">
                                            ${s.service_code || 'N/A'}
                                        </span>
                                        ${s.department ? `
                                            <small class="text-muted font-outfit fw-medium">
                                                <i class="bi bi-building me-1"></i> ${s.department}
                                            </small>
                                        ` : ''}
                                    </div>
                                    <h5 class="card-title fw-bold text-dark font-outfit mb-2 lh-base">
                                        ${s.service_name}
                                    </h5>
                                    <p class="card-text text-muted small mb-3 flex-grow-1" style="line-height: 1.5;">
                                        ${s.description ? (s.description.length > 90 ? s.description.substring(0, 90) + '...' : s.description) : 'Chưa cập nhật thuyết minh mô tả cho dịch vụ y khoa này.'}
                                    </p>
                                    
                                    <div class="pricing-tiers mt-auto pt-3 border-top" style="border-top-style: dashed !important; border-color: #e2e8f0 !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted small"><i class="bi bi-cash me-1"></i> Giá Thường</span>
                                            <span class="fw-bold text-dark font-outfit" style="font-size: 14px;">
                                                ${s.price_normal ? parseInt(s.price_normal).toLocaleString('vi-VN') + 'đ' : 'Liên hệ'}
                                            </span>
                                        </div>
                                        ${s.price_bhyt ? `
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-success small fw-semibold"><i class="bi bi-shield-check me-1"></i> Có BHYT</span>
                                                <span class="fw-bold text-success font-outfit" style="font-size: 14px;">
                                                    ${parseInt(s.price_bhyt).toLocaleString('vi-VN')}đ
                                                </span>
                                            </div>
                                        ` : ''}
                                        ${s.price_vip ? `
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-purple small fw-semibold"><i class="bi bi-gem me-1"></i> Khám VIP</span>
                                                <span class="fw-bold text-purple font-outfit" style="font-size: 14px;">
                                                    ${parseInt(s.price_vip).toLocaleString('vi-VN')}đ
                                                </span>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="card-footer bg-light border-0 px-4 pb-4 pt-0">
                                    <div class="d-flex gap-2">
                                        <button onclick="openQuickView(${s.service_id})" 
                                                class="btn btn-outline-primary btn-sm flex-fill font-outfit fw-bold" 
                                                style="height: 38px; border-radius: 8px;">
                                            <i class="bi bi-eye"></i> Xem nhanh
                                        </button>
                                        <a href="${s.book_url}" 
                                           class="btn btn-primary btn-sm flex-fill font-outfit fw-bold d-flex align-items-center justify-content-center gap-1" 
                                           style="height: 38px; border-radius: 8px;">
                                            <i class="bi bi-calendar-plus"></i> Đặt lịch
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // List template
                    listHtml += `
                        <div class="list-group-item p-4 bg-white" style="border-color: #f1f5f9 !important;">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-4 col-md-5">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="service-icon-wrapper-small ${icons.class}">
                                            <i class="bi ${icons.icon} fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary px-2 py-0.5 rounded mb-1 font-outfit" style="font-size: 10px;">
                                                ${s.service_code || 'N/A'}
                                            </span>
                                            <h6 class="mb-0 fw-bold text-dark font-outfit">${s.service_name}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <small class="text-muted d-block small font-outfit">Khoa phụ trách</small>
                                    <span class="fw-semibold text-dark font-outfit" style="font-size: 14px;">
                                        ${s.department || '—'}
                                    </span>
                                </div>
                                <div class="col-lg-3 col-md-3">
                                    <small class="text-muted d-block small font-outfit">Mức giá BHYT / VIP</small>
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        ${s.price_bhyt ? `<span class="badge bg-success-subtle text-success border border-success-subtle font-outfit">BHYT: ${parseInt(s.price_bhyt).toLocaleString('vi-VN')}đ</span>` : ''}
                                        ${s.price_vip ? `<span class="badge bg-purple-subtle text-purple border border-purple-subtle font-outfit">VIP: ${parseInt(s.price_vip).toLocaleString('vi-VN')}đ</span>` : ''}
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-12 text-end d-flex gap-2 justify-content-end">
                                    <button onclick="openQuickView(${s.service_id})" 
                                            class="btn btn-sm btn-outline-primary px-3 font-outfit fw-bold" 
                                            style="height: 38px; border-radius: 8px;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="${s.book_url}" 
                                       class="btn btn-primary btn-sm px-3 font-outfit fw-bold d-flex align-items-center gap-1" 
                                       style="height: 38px; border-radius: 8px;">
                                        <i class="bi bi-calendar-plus"></i> Đặt lịch
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            // Inject new DOM structures
            servicesGridContainer.innerHTML = gridHtml;
            servicesListContainer.innerHTML = listHtml;
            
            // Hide pagination since live filter results are client-side snapshot
            if (searchInput.value.trim() !== '' || 
                deptSelect.value !== '' || 
                document.getElementById('priceTierSelect').value !== '' || 
                document.getElementById('durationRangeSelect').value !== '' || 
                document.getElementById('sortBySelect').value !== 'name_asc') {
                paginationSection.classList.add('d-none');
            } else {
                paginationSection.classList.remove('d-none');
            }
            
            searchSpinner.classList.add('d-none');
        })
        .catch(err => {
            console.error(err);
            searchSpinner.classList.add('d-none');
        });
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performLiveSearch, 300); // 300ms Debounce
    });

    window.toggleAdvancedFilters = function() {
        const panel = document.getElementById('advancedFilters');
        const btnIcon = document.getElementById('advancedFilterIcon');
        if (panel.style.display === 'none') {
            panel.style.display = 'block';
            btnIcon.className = 'bi bi-chevron-up small';
        } else {
            panel.style.display = 'none';
            btnIcon.className = 'bi bi-chevron-down small';
        }
    };

    // Auto-expand if request filters exist
    @if(request('price_tier') || request('duration_range') || request('sort_by'))
        document.getElementById('advancedFilters').style.display = 'block';
        document.getElementById('advancedFilterIcon').className = 'bi bi-chevron-up small';
    @endif

    deptSelect.addEventListener('change', performLiveSearch);
    document.getElementById('priceTierSelect').addEventListener('change', performLiveSearch);
    document.getElementById('durationRangeSelect').addEventListener('change', performLiveSearch);
    document.getElementById('sortBySelect').addEventListener('change', performLiveSearch);

    // ── Silent Realtime Polling (Only reloads if changes exist) ─────────────
    let svcSnapshot = {{ $services->total() }};
    setInterval(() => {
        fetch(`${SERVICES_JSON_ROUTE}?search=&department_id=`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.total !== svcSnapshot) {
                svcSnapshot = data.total;
                // Soft refresh
                window.location.reload();
            }
        })
        .catch(() => {});
    }, 20000); // 20s Silent check interval
</script>
@endpush

@endsection