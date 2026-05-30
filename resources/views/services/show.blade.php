@extends('layouts.user')

@section('title', $service->service_name)

@push('styles')
<style>
    .price-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .price-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .price-normal { border-left: 4px solid #0D47A1; }
    .price-bhyt { border-left: 4px solid #2e7d32; }
    .price-vip { border-left: 4px solid #e65100; }
    .price-other { border-left: 4px solid #6c757d; }
    .sticky-sidebar {
        position: sticky;
        top: 20px;
    }
    .breadcrumb-nav {
        background: #F8FAFF;
        padding: 12px 0;
        margin-bottom: 20px;
        border-radius: 12px;
    }
    .hover-card {
        transition: all 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15) !important;
    }
</style>
@endpush

@section('content')
<div class="container py-4">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="breadcrumb-nav">
        <div class="container">
            <ol class="breadcrumb mb-0 bg-transparent">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="text-decoration-none">
                        <i class="bi bi-house-door"></i> Trang chủ
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('user.services.index') }}" class="text-decoration-none">
                        Dịch vụ y tế
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $service->service_name }}
                </li>
            </ol>
        </div>
    </nav>

    <div class="row g-4">
        {{-- Nội dung chính bên trái --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    {{-- Header thông tin --}}
                    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap">
                        <div>
                            <h1 class="display-6 fw-bold text-primary mb-2">{{ $service->service_name }}</h1>
                            <div class="d-flex gap-3 flex-wrap">
                                <span class="badge bg-light text-dark px-3 py-2">
                                    <i class="bi bi-upc-scan me-1"></i> Mã: {{ $service->service_code }}
                                </span>
                                @if($service->department)
                                    <span class="badge bg-light text-dark px-3 py-2">
                                        <i class="bi bi-building me-1"></i> {{ $service->department->department_name }}
                                    </span>
                                @endif
                                <span class="badge bg-light text-dark px-3 py-2">
                                    <i class="bi bi-clock me-1"></i> Thời gian: {{ $service->duration_minutes }} phút
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 mt-sm-0">
                            <span class="badge bg-success px-3 py-2">
                                <i class="bi bi-check-circle-fill me-1"></i> Đang hoạt động
                            </span>
                        </div>
                    </div>

                    {{-- Mô tả --}}
                    @if($service->description)
                        <div class="mt-4">
                            <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Mô tả dịch vụ</h5>
                            <div class="p-3 bg-light rounded-3">
                                <p class="mb-0">{{ $service->description }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Quy trình dịch vụ --}}
                    <div class="mt-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-diagram-3 me-2 text-primary"></i>Quy trình thực hiện</h5>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 bg-light rounded-3">
                                    <i class="bi bi-calendar-heart fs-2 text-primary"></i>
                                    <p class="small mt-2 mb-0 fw-bold">Bước 1</p>
                                    <p class="small text-muted">Đặt lịch hẹn</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 bg-light rounded-3">
                                    <i class="bi bi-person-badge fs-2 text-primary"></i>
                                    <p class="small mt-2 mb-0 fw-bold">Bước 2</p>
                                    <p class="small text-muted">Tiếp đón & làm thủ tục</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 bg-light rounded-3">
                                    <i class="bi bi-clipboard2-pulse fs-2 text-primary"></i>
                                    <p class="small mt-2 mb-0 fw-bold">Bước 3</p>
                                    <p class="small text-muted">Thăm khám & điều trị</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 bg-light rounded-3">
                                    <i class="bi bi-cash-stack fs-2 text-primary"></i>
                                    <p class="small mt-2 mb-0 fw-bold">Bước 4</p>
                                    <p class="small text-muted">Thanh toán & kết thúc</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar bên phải --}}
        <div class="col-lg-4">
            <div class="sticky-sidebar">
                {{-- Bảng giá --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="bi bi-tag me-2 text-primary"></i>Bảng giá tham khảo</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $priceGroups = [
                                'Thường' => ['class' => 'price-normal', 'icon' => 'bi-person', 'color' => '#0D47A1'],
                                'BHYT' => ['class' => 'price-bhyt', 'icon' => 'bi-shield-check', 'color' => '#2e7d32'],
                                'VIP' => ['class' => 'price-vip', 'icon' => 'bi-star', 'color' => '#e65100'],
                                'Theo yêu cầu' => ['class' => 'price-other', 'icon' => 'bi-gear', 'color' => '#6c757d']
                            ];
                        @endphp

                        @foreach($priceGroups as $type => $config)
                            @php
                                $price = $service->activePrices->firstWhere('price_type', $type);
                            @endphp
                            <div class="price-card {{ $config['class'] }} p-3 mb-3 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi {{ $config['icon'] }} me-2" style="color: {{ $config['color'] }}"></i>
                                        <span class="fw-semibold">{{ $type }}</span>
                                    </div>
                                    <div>
                                        @if($price)
                                            <span class="fs-5 fw-bold" style="color: {{ $config['color'] }}">
                                                {{ number_format($price->price, 0, ',', '.') }}đ
                                            </span>
                                        @else
                                            <span class="text-muted">Chưa cập nhật</span>
                                        @endif
                                    </div>
                                </div>
                                @if($price && $price->effective_date)
                                    <small class="text-muted d-block mt-1">
                                        Áp dụng từ {{ date('d/m/Y', strtotime($price->effective_date)) }}
                                    </small>
                                @endif
                            </div>
                        @endforeach

                        <div class="alert alert-info small mt-3 mb-0">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            * Giá có thể thay đổi tùy thời điểm. Vui lòng liên hệ để biết thêm chi tiết.
                        </div>
                    </div>
                </div>

                {{-- Nút đặt lịch kèm bác sĩ --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check fs-1 text-primary mb-2 d-block"></i>
                        <h6 class="fw-bold">Bạn muốn khám cùng Bác sĩ?</h6>
                        <p class="small text-muted">Đặt hẹn bác sĩ & chọn giờ khám</p>
                        <a href="{{ route('appointments.create', ['service_id' => $service->service_id]) }}" 
                           class="btn btn-primary btn-lg w-100 rounded-3">
                            <i class="bi bi-calendar-plus me-2"></i> Đặt lịch bác sĩ ngay
                        </a>
                    </div>
                </div>

                {{-- Đăng ký & Thanh toán dịch vụ riêng biệt --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(145deg, #ffffff, #f8fafc); border: 1.5px solid #e2e8f0;">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <i class="bi bi-credit-card-2-front fs-1 text-success mb-2 d-block"></i>
                            <h6 class="fw-bold" style="color: #1e293b;">Đăng ký & Thanh toán nhanh</h6>
                            <p class="small text-muted mb-0">Thực hiện dịch vụ trực tiếp không cần bác sĩ</p>
                        </div>
                        
                        <form action="{{ route('user.services.book', $service->service_id) }}" method="POST">
                            @csrf
                            
                            {{-- Loại giá --}}
                            <div class="mb-3 text-start">
                                <label class="small fw-bold text-muted mb-1 d-block">Chọn loại mức giá:</label>
                                <select name="price_type" class="form-select rounded-3" required style="border: 1.5px solid #cbd5e1; font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                    @foreach($service->activePrices as $price)
                                        <option value="{{ $price->price_type }}">
                                            Giá {{ $price->price_type }} - {{ number_format($price->price, 0, ',', '.') }}đ
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Ngày hẹn --}}
                            <div class="mb-3 text-start">
                                <label class="small fw-bold text-muted mb-1 d-block">Chọn ngày thực hiện:</label>
                                <input type="date" name="work_date" class="form-control rounded-3" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required style="border: 1.5px solid #cbd5e1; font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                            </div>

                            {{-- Giờ hẹn --}}
                            <div class="mb-3 text-start">
                                <label class="small fw-bold text-muted mb-1 d-block">Chọn giờ hẹn:</label>
                                <select name="appointment_time" class="form-select rounded-3" required style="border: 1.5px solid #cbd5e1; font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                    <option value="08:00">08:00 (Sáng)</option>
                                    <option value="09:00">09:00 (Sáng)</option>
                                    <option value="10:00">10:00 (Sáng)</option>
                                    <option value="11:00">11:00 (Sáng)</option>
                                    <option value="13:30" selected>13:30 (Chiều)</option>
                                    <option value="14:30">14:30 (Chiều)</option>
                                    <option value="15:30">15:30 (Chiều)</option>
                                    <option value="16:30">16:30 (Chiều)</option>
                                </select>
                            </div>

                            {{-- Ghi chú --}}
                            <div class="mb-3 text-start">
                                <label class="small fw-bold text-muted mb-1 d-block">Ghi chú (nếu có):</label>
                                <textarea name="note" class="form-control rounded-3" rows="2" placeholder="Ví dụ: Cần chuẩn bị gì trước khi khám..." style="border: 1.5px solid #cbd5e1; font-size: .85rem; padding: 0.5rem 0.75rem;"></textarea>
                            </div>

                            @auth
                                <button type="submit" class="btn btn-success btn-lg w-100 rounded-3 fw-bold" style="background: #10b981; border: none; font-size: 0.95rem; padding: 0.75rem;">
                                    <i class="bi bi-wallet2 me-2"></i> Thanh toán & Đăng ký
                                </button>
                            @else
                                <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-warning btn-lg w-100 rounded-3 fw-bold text-white" style="border: none; font-size: 0.95rem; padding: 0.75rem;">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Đăng nhập để thanh toán
                                </a>
                            @endauth
                        </form>
                    </div>
                </div>

                {{-- Thời gian hoạt động --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Giờ làm việc</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Thứ 2 - Thứ 6:</span>
                            <span class="fw-semibold">7:30 - 17:00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Thứ 7:</span>
                            <span class="fw-semibold">7:30 - 12:00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Chủ nhật & Lễ:</span>
                            <span class="fw-semibold text-danger">Nghỉ</span>
                        </div>
                        <hr>
                        <div class="text-center">
                            <small class="text-muted d-block">
                                <i class="bi bi-telephone-fill me-1"></i> Hotline: 1900 XXXX
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-envelope-fill me-1"></i> Email: info@hospital.com
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dịch vụ liên quan --}}
    @if(isset($relatedServices) && $relatedServices->count() > 0)
        <div class="mt-5">
            <h4 class="fw-bold mb-4">
                <i class="bi bi-diagram-3 me-2 text-primary"></i>Dịch vụ cùng khoa
            </h4>
            <div class="row g-4">
                @foreach($relatedServices as $related)
                    <div class="col-md-3 col-6">
                        <div class="card h-100 border-0 shadow-sm text-center p-3 rounded-4 hover-card">
                            <i class="bi bi-activity fs-1 text-primary"></i>
                            <h6 class="mt-2 fw-bold">
                                <a href="{{ route('user.services.show', $related->service_id) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ Str::limit($related->service_name, 40) }}
                                </a>
                            </h6>
                            @php $lowest = $related->activePrices->min('price'); @endphp
                            <p class="small text-muted mb-0">
                                Từ <span class="text-danger fw-bold">{{ number_format($lowest, 0, ',', '.') }}đ</span>
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Thêm hiệu ứng hover cho các price card
    document.querySelectorAll('.price-card').forEach(card => {
        card.addEventListener('click', () => {
            // Có thể thêm modal chi tiết giá nếu cần
        });
    });
</script>
@endpush

@endsection