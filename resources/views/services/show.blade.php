@extends('layouts.user')

@section('title', $service->service_name . ' - Chi tiết dịch vụ y tế')

@section('content')
<!-- Google Fonts Outfit & Inter -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');
    
    .service-detail-page {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        color: #1e293b;
    }
    
    .service-detail-page h1, 
    .service-detail-page h2, 
    .service-detail-page h3, 
    .service-detail-page h4, 
    .service-detail-page h5, 
    .service-detail-page h6,
    .service-detail-page .font-outfit {
        font-family: 'Outfit', sans-serif;
    }
</style>

<div class="service-detail-page py-4">
    <div class="container">

        {{-- Breadcrumb Nav --}}
        <nav aria-label="breadcrumb" class="mb-4" style="background: #ffffff; padding: 12px 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <ol class="breadcrumb mb-0 bg-transparent font-outfit small fw-medium">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="text-decoration-none text-muted">
                        <i class="bi bi-house-door me-1"></i> Trang chủ
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('user.services.index') }}" class="text-decoration-none text-muted">
                        Dịch vụ y tế
                    </a>
                </li>
                <li class="breadcrumb-item active text-primary" aria-current="page">
                    {{ $service->service_name }}
                </li>
            </ol>
        </nav>

        {{-- Helper function for dynamic icons mapping --}}
        @php
            if (!function_exists('getServiceIconShow')) {
                function getServiceIconShow($name, $deptName) {
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
            $iconData = getServiceIconShow($service->service_name, $service->department?->department_name ?? '');
        @endphp

        <div class="row g-4">
            {{-- Main Content Column --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-4 p-md-5">
                        
                        {{-- Header Title Block --}}
                        <div class="d-flex align-items-start gap-3 flex-wrap mb-4 pb-4 border-bottom" style="border-bottom-style: dashed !important;">
                            <div class="show-service-icon-wrapper {{ $iconData['class'] }}">
                                <i class="bi {{ $iconData['icon'] }} fs-1"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex gap-2 align-items-center mb-2 flex-wrap">
                                    <span class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill fw-bold font-outfit" style="font-size: 11px;">
                                        {{ $service->service_code }}
                                    </span>
                                    <span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill fw-bold font-outfit" style="font-size: 11px;">
                                        <i class="bi bi-check-circle-fill me-1"></i> Đang mở công khai
                                    </span>
                                </div>
                                <h1 class="display-6 fw-extrabold text-dark font-outfit mb-0" style="letter-spacing: -0.5px;">
                                    {{ $service->service_name }}
                                </h1>
                            </div>
                        </div>

                        {{-- Service Details Badges --}}
                        <div class="row g-3 mb-5">
                            <div class="col-6 col-sm-4">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <small class="text-muted d-block mb-1 font-outfit fw-semibold"><i class="bi bi-clock me-1"></i> THỜI GIAN KHÁM</small>
                                    <span class="fw-bold text-dark font-outfit fs-5">{{ $service->duration_minutes }} phút</span>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <small class="text-muted d-block mb-1 font-outfit fw-semibold"><i class="bi bi-building me-1"></i> CHUYÊN KHOA</small>
                                    <span class="fw-bold text-primary font-outfit fs-5">{{ $service->department->department_name ?? 'Khám chung' }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="p-3 bg-light rounded-3 text-center border">
                                    <small class="text-muted d-block mb-1 font-outfit fw-semibold"><i class="bi bi-shield-check me-1"></i> BẢO HIỂM Y TẾ</small>
                                    <span class="fw-bold text-success font-outfit fs-5">Hỗ trợ chi trả</span>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if($service->description)
                            <div class="mb-5">
                                <h4 class="fw-bold text-dark font-outfit mb-3"><i class="bi bi-info-circle-fill text-primary me-2"></i> Giới thiệu dịch vụ</h4>
                                <div class="p-4 rounded-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0; line-height: 1.8; text-align: justify; font-size: 15px;">
                                    {{ $service->description }}
                                </div>
                            </div>
                        @endif

                        {{-- Quality Commitments workflow --}}
                        <div class="mb-4">
                            <h4 class="fw-bold text-dark font-outfit mb-4"><i class="bi bi-patch-check-fill text-primary me-2"></i> Quy trình thăm khám tiêu chuẩn</h4>
                            <div class="row g-3">
                                <div class="col-md-3 col-6">
                                    <div class="text-center p-3 rounded-4 border bg-white shadow-sm h-100">
                                        <div class="icon-circle bg-primary-subtle text-primary mx-auto mb-2" style="width: 50px; height: 50px;">
                                            <i class="bi bi-calendar2-event fs-4"></i>
                                        </div>
                                        <p class="small mb-1 fw-bold text-dark font-outfit">Bước 1</p>
                                        <p class="small text-muted mb-0 font-outfit">Đăng ký lịch trực tuyến</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-center p-3 rounded-4 border bg-white shadow-sm h-100">
                                        <div class="icon-circle bg-info-subtle text-info mx-auto mb-2" style="width: 50px; height: 50px;">
                                            <i class="bi bi-person-workspace fs-4"></i>
                                        </div>
                                        <p class="small mb-1 fw-bold text-dark font-outfit">Bước 2</p>
                                        <p class="small text-muted mb-0 font-outfit">Đón tiếp tại quầy hành chính</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-center p-3 rounded-4 border bg-white shadow-sm h-100">
                                        <div class="icon-circle bg-warning-subtle text-warning mx-auto mb-2" style="width: 50px; height: 50px;">
                                            <i class="bi bi-shield-shaded fs-4"></i>
                                        </div>
                                        <p class="small mb-1 fw-bold text-dark font-outfit">Bước 3</p>
                                        <p class="small text-muted mb-0 font-outfit">Bác sĩ khám & chẩn đoán</p>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-center p-3 rounded-4 border bg-white shadow-sm h-100">
                                        <div class="icon-circle bg-success-subtle text-success mx-auto mb-2" style="width: 50px; height: 50px;">
                                            <i class="bi bi-file-earmark-medical fs-4"></i>
                                        </div>
                                        <p class="small mb-1 fw-bold text-dark font-outfit">Bước 4</p>
                                        <p class="small text-muted mb-0 font-outfit">Nhận kết quả & cấp thuốc</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div class="col-lg-4">
                <div class="position-sticky" style="top: 24px;">

                    {{-- Pricing reference list --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="fw-bold mb-0 text-dark font-outfit"><i class="bi bi-tags-fill text-primary me-2"></i> Bảng giá tham khảo</h5>
                        </div>
                        <div class="card-body p-4">
                            @php
                                $priceGroups = [
                                    'Thường' => ['class' => 'price-normal-card', 'icon' => 'bi-cash-coin', 'color' => '#0d6efd', 'bg' => '#f0f7ff'],
                                    'BHYT' => ['class' => 'price-bhyt-card', 'icon' => 'bi-shield-fill-plus', 'color' => '#198754', 'bg' => '#e6f6ec'],
                                    'VIP' => ['class' => 'price-vip-card', 'icon' => 'bi-gem', 'color' => '#6f42c1', 'bg' => '#f3ebfc']
                                ];
                            @endphp

                            @foreach($priceGroups as $type => $config)
                                @php
                                    $price = $service->activePrices->first(fn($p) => str_contains(strtolower($p->price_type), strtolower($type)));
                                @endphp
                                <div class="p-3 mb-3 rounded-4 border d-flex justify-content-between align-items-center" 
                                     style="background: {{ $price ? $config['bg'] : '#f8fafc' }}; transition: all 0.2s; border-color: #e2e8f0 !important;">
                                    <div>
                                        <i class="bi {{ $config['icon'] }} me-2 fs-5" style="color: {{ $config['color'] }}"></i>
                                        <span class="fw-bold text-dark font-outfit" style="font-size: 14px;">Mức {{ $type }}</span>
                                        @if($price && $price->effective_date)
                                            <small class="text-muted d-block mt-0.5" style="font-size: 10px;">
                                                Từ {{ date('d/m/Y', strtotime($price->effective_date)) }}
                                            </small>
                                        @endif
                                    </div>
                                    <div>
                                        @if($price)
                                            <span class="fs-5 fw-extrabold font-outfit" style="color: {{ $config['color'] }}">
                                                {{ number_format($price->price, 0, ',', '.') }}đ
                                            </span>
                                        @else
                                            <span class="text-muted small italic">Liên hệ quầy</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="alert alert-warning small mt-3 mb-0" style="border-radius: 10px; font-size: 11.5px;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                Giá BHYT áp dụng khi có thẻ bảo hiểm y tế hợp lệ trùng khớp với danh mục bộ y tế quy định.
                            </div>
                        </div>
                    </div>

                    {{-- Quick Direct Booking & Purchase Form --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 2px solid #10b981 !important;">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="icon-circle bg-success-subtle text-success mx-auto mb-2" style="width: 50px; height: 50px;">
                                    <i class="bi bi-lightning-charge-fill fs-4"></i>
                                </div>
                                <h5 class="fw-extrabold text-dark font-outfit mb-1">Đăng Ký Khám Nhanh</h5>
                                <p class="small text-muted mb-0">Đặt chỗ trực tiếp thực hiện dịch vụ không qua bác sĩ</p>
                            </div>
                            
                            <form action="{{ route('user.services.book', $service->service_id) }}" method="POST" id="bookingForm">
                                @csrf
                                
                                {{-- Price Type --}}
                                <div class="mb-3">
                                    <label class="small fw-bold text-secondary font-outfit mb-1.5 d-block"><i class="bi bi-tag-fill me-1"></i> Loại mức giá thanh toán</label>
                                    <select name="price_type" class="form-select" required style="border-radius: 8px; font-size: 13.5px; height: 42px;">
                                        @foreach($service->activePrices as $price)
                                            <option value="{{ $price->price_type }}">
                                                Giá {{ $price->price_type }} - {{ number_format($price->price, 0, ',', '.') }}đ
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Date --}}
                                <div class="mb-3">
                                    <label class="small fw-bold text-secondary font-outfit mb-1.5 d-block"><i class="bi bi-calendar-check-fill me-1"></i> Ngày thực hiện</label>
                                    <input type="date" name="work_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required style="border-radius: 8px; font-size: 13.5px; height: 42px;">
                                </div>

                                {{-- Time --}}
                                <div class="mb-3">
                                    <label class="small fw-bold text-secondary font-outfit mb-1.5 d-block"><i class="bi bi-clock-fill me-1"></i> Giờ hẹn khám</label>
                                    <select name="appointment_time" class="form-select" required style="border-radius: 8px; font-size: 13.5px; height: 42px;">
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

                                {{-- Note --}}
                                <div class="mb-4">
                                    <label class="small fw-bold text-secondary font-outfit mb-1.5 d-block"><i class="bi bi-pencil-square me-1"></i> Ghi chú lâm sàng (nếu có)</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Mang thai, tiền sử dị ứng thuốc..." style="border-radius: 8px; font-size: 13px;"></textarea>
                                </div>

                                @auth
                                    <input type="hidden" name="payment_option" id="paymentOption" value="now">
                                    <button type="button" id="btnOpenBookingConfirm" class="btn btn-success btn-lg w-100 font-outfit fw-bold py-2.5 d-flex align-items-center justify-content-center gap-1" style="background: #10b981; border: none; border-radius: 10px; font-size: 15px;">
                                        <i class="bi bi-calendar2-check-fill"></i> Tiến hành Đặt lịch khám
                                    </button>
                                @else
                                    <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-warning btn-lg w-100 font-outfit fw-bold text-white py-2.5 d-flex align-items-center justify-content-center gap-1" style="border: none; border-radius: 10px; font-size: 15px;">
                                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập để thanh toán
                                    </a>
                                @endauth
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Related specialty services --}}
        @if(isset($related) && $related->count() > 0)
            <div class="mt-5 pt-4">
                <h4 class="fw-bold text-dark font-outfit mb-4">
                    <i class="bi bi-grid-fill text-primary me-2"></i> Dịch vụ khác cùng chuyên khoa
                </h4>
                <div class="row g-4">
                    @foreach($related as $rel)
                        @php $lowestRel = $rel->activePrices->min('price'); @endphp
                        <div class="col-md-3 col-6">
                            <div class="card h-100 border-0 shadow-sm text-center p-4 rounded-4 hover-card bg-white" style="transition: all 0.3s; border: 1px solid #e2e8f0 !important;">
                                <i class="bi bi-activity fs-2 text-primary mb-2"></i>
                                <h6 class="fw-bold text-dark font-outfit mb-2">
                                    <a href="{{ route('user.services.show', $rel->service_id) }}" 
                                       class="text-decoration-none text-dark stretched-link">
                                        {{ Str::limit($rel->service_name, 35) }}
                                    </a>
                                </h6>
                                <p class="small text-danger fw-extrabold font-outfit mb-0">
                                    Giá chỉ từ: {{ number_format($lowestRel, 0, ',', '.') }}đ
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ── MODAL XÁC NHẬN THANH TOÁN (PAY NOW OR LATER) ────────────────── --}}
<div class="modal fade" id="bookingConfirmModal" tabindex="-1" aria-labelledby="bookingConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-success text-white border-0 py-3" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold font-outfit" id="bookingConfirmModalLabel">
                    <i class="bi bi-wallet2 me-2"></i>Chọn hình thức thanh toán
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <p class="text-muted small mb-1">Dịch vụ đã chọn</p>
                    <h5 class="fw-bold text-dark font-outfit mb-3">{{ $service->service_name }}</h5>
                    <div class="p-3 bg-light rounded-4 border">
                        <div class="row g-2 text-start small">
                            <div class="col-6 text-muted">Mức giá chọn:</div>
                            <div class="col-6 text-end fw-bold text-dark" id="modalPriceType">Giá Thường</div>
                            <div class="col-6 text-muted">Ngày thực hiện:</div>
                            <div class="col-6 text-end fw-bold text-dark" id="modalWorkDate">01/01/2026</div>
                            <div class="col-6 text-muted">Giờ hẹn khám:</div>
                            <div class="col-6 text-end fw-bold text-dark" id="modalTime">13:30</div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info py-2.5 px-3 small border-0 mb-0" style="border-radius: 10px;">
                    <i class="bi bi-info-circle-fill text-success me-1"></i>
                    Vui lòng chọn hình thức thanh toán để hoàn tất thủ tục đăng ký lịch khám.
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light d-flex gap-2" style="border-radius: 0 0 16px 16px;">
                <button type="button" class="btn btn-outline-secondary flex-fill font-outfit fw-bold btn-sm py-2.5" id="btnPayLater" style="border-radius: 10px;">
                    <i class="bi bi-clock-history"></i> Thanh toán sau
                </button>
                <button type="button" class="btn btn-success flex-fill font-outfit fw-bold btn-sm py-2.5" id="btnPayNow" style="background: #10b981; border: none; border-radius: 10px;">
                    <i class="bi bi-credit-card-fill"></i> Thanh toán ngay
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .show-service-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px -10px rgba(0,0,0,0.1) !important;
        border-color: rgba(13,110,253,0.2) !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('bookingForm');
    const btnOpenConfirm = document.getElementById('btnOpenBookingConfirm');
    const payOptionInput = document.getElementById('paymentOption');
    const btnPayNow = document.getElementById('btnPayNow');
    const btnPayLater = document.getElementById('btnPayLater');
    
    if (btnOpenConfirm && bookingForm) {
        // Khởi tạo Bootstrap modal
        const confirmModal = new bootstrap.Modal(document.getElementById('bookingConfirmModal'));

        btnOpenConfirm.addEventListener('click', function() {
            // Validate form trước khi mở modal
            if (!bookingForm.checkValidity()) {
                bookingForm.reportValidity();
                return;
            }

            // Lấy thông tin từ form để hiển thị lên modal
            const priceSelect = bookingForm.querySelector('[name="price_type"]');
            const priceText = priceSelect ? priceSelect.options[priceSelect.selectedIndex].text : '';
            const workDate = bookingForm.querySelector('[name="work_date"]').value;
            const apptTime = bookingForm.querySelector('[name="appointment_time"]').value;

            // Định dạng ngày thành dd/mm/yyyy
            let formattedDate = workDate;
            if (workDate) {
                const parts = workDate.split('-');
                if (parts.length === 3) {
                    formattedDate = parts[2] + '/' + parts[1] + '/' + parts[0];
                }
            }

            // Gán dữ liệu vào modal
            document.getElementById('modalPriceType').textContent = priceText;
            document.getElementById('modalWorkDate').textContent = formattedDate;
            document.getElementById('modalTime').textContent = apptTime + ' (' + (apptTime >= '12:00' ? 'Chiều' : 'Sáng') + ')';

            // Mở modal
            confirmModal.show();
        });

        // Click Thanh toán ngay
        btnPayNow.addEventListener('click', function() {
            payOptionInput.value = 'now';
            submitBookingForm('btnPayNow');
        });

        // Click Thanh toán sau
        btnPayLater.addEventListener('click', function() {
            payOptionInput.value = 'later';
            submitBookingForm('btnPayLater');
        });

        function submitBookingForm(activeBtnId) {
            // Disable tất cả các nút để tránh nhấn nhiều lần
            btnPayNow.disabled = true;
            btnPayLater.disabled = true;
            btnOpenConfirm.disabled = true;

            const activeBtn = document.getElementById(activeBtnId);
            if (activeBtn) {
                activeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Đang xử lý...';
            }

            bookingForm.submit();
        }
    }
});
</script>
@endpush

@endsection