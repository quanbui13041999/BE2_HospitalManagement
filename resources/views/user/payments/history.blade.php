{{-- resources/views/user/payments/history.blade.php --}}
@extends('layouts.user')

@section('title', 'Lịch sử thanh toán cá nhân')

@section('content')
<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">
            
            <!-- HEADER -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                <div>
                    <h2 class="fw-extrabold text-dark mb-1 font-outfit" style="letter-spacing: -0.5px;">
                        <i class="bi bi-wallet2 text-primary me-2"></i> Lịch Sử Thanh Toán
                    </h2>
                    <p class="text-muted mb-0 font-inter" style="font-size: 14px;">
                        Quản lý, tra cứu hóa đơn dịch vụ và biên lai giao dịch y tế của bạn.
                    </p>
                </div>
                <a href="{{ route('user.services.index') }}" class="btn btn-outline-primary btn-sm rounded-3 font-outfit fw-bold px-3 py-2">
                    <i class="bi bi-plus-circle me-1"></i> Đăng ký dịch vụ mới
                </a>
            </div>

            <!-- STATS BLOCK -->
            <div class="row g-3 mb-4">
                <!-- Stat 1: Spent -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #10b981, #059669); color: white;">
                        <div class="card-body p-4 position-relative z-index-1">
                            <span class="text-white-50 small fw-bold font-inter text-uppercase tracking-wider">Tổng tích lũy chi tiêu</span>
                            <h3 class="fw-extrabold font-outfit mt-1 mb-0" style="font-size: 28px;">
                                {{ number_format($stats['total_spent'] ?? 0, 0, ',', '.') }}đ
                            </h3>
                            <div class="mt-3 text-white-50 small font-inter">
                                <i class="bi bi-check-circle-fill me-1"></i> Đã hoàn thành {{ $stats['completed_count'] ?? 0 }} giao dịch
                            </div>
                        </div>
                        <i class="bi bi-cash-coin position-absolute" style="font-size: 90px; right: -15px; bottom: -20px; opacity: 0.15; color: white;"></i>
                    </div>
                </div>

                <!-- Stat 2: Completed -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white;">
                        <div class="card-body p-4 position-relative z-index-1">
                            <span class="text-white-50 small fw-bold font-inter text-uppercase tracking-wider">Hóa đơn hoàn tất</span>
                            <h3 class="fw-extrabold font-outfit mt-1 mb-0" style="font-size: 28px;">
                                {{ $stats['completed_count'] ?? 0 }} <span style="font-size: 16px; font-weight: 500;">Giao dịch</span>
                            </h3>
                            <div class="mt-3 text-white-50 small font-inter">
                                <i class="bi bi-shield-fill-check me-1"></i> Được bảo mật & đối soát tự động
                            </div>
                        </div>
                        <i class="bi bi-check2-all position-absolute" style="font-size: 90px; right: -15px; bottom: -20px; opacity: 0.15; color: white;"></i>
                    </div>
                </div>

                <!-- Stat 3: Pending -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                        <div class="card-body p-4 position-relative z-index-1">
                            <span class="text-white-50 small fw-bold font-inter text-uppercase tracking-wider">Hóa đơn chờ xử lý</span>
                            <h3 class="fw-extrabold font-outfit mt-1 mb-0" style="font-size: 28px;">
                                {{ $stats['pending_count'] ?? 0 }} <span style="font-size: 16px; font-weight: 500;">Hóa đơn</span>
                            </h3>
                            <div class="mt-3 text-white-50 small font-inter">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Cần hoàn tất thanh toán sớm
                            </div>
                        </div>
                        <i class="bi bi-hourglass-split position-absolute" style="font-size: 90px; right: -15px; bottom: -20px; opacity: 0.15; color: white;"></i>
                    </div>
                </div>
            </div>

            <!-- SEARCH & FILTERS -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('user.payments.history') }}" class="row g-3 align-items-end">
                        
                        <!-- Keyword Search -->
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label text-secondary small fw-bold font-outfit"><i class="bi bi-search me-1"></i> Tìm kiếm giao dịch</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 font-inter font-14" 
                                       placeholder="Tên bác sĩ, dịch vụ, mã GD..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Date range -->
                        <div class="col-lg-2 col-md-3">
                            <label class="form-label text-secondary small fw-bold font-outfit"><i class="bi bi-calendar-event me-1"></i> Từ ngày</label>
                            <input type="date" name="from_date" class="form-control font-inter font-14" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <label class="form-label text-secondary small fw-bold font-outfit"><i class="bi bi-calendar-event me-1"></i> Đến ngày</label>
                            <input type="date" name="to_date" class="form-control font-inter font-14" value="{{ request('to_date') }}">
                        </div>

                        <!-- Status Filter -->
                        <div class="col-lg-2 col-md-6 col-sm-6">
                            <label class="form-label text-secondary small fw-bold font-outfit"><i class="bi bi-filter-circle me-1"></i> Trạng thái</label>
                            <select name="status" class="form-select font-inter font-14">
                                <option value="">Tất cả</option>
                                <option value="Thành công" {{ request('status') === 'Thành công' ? 'selected' : '' }}>Thành công</option>
                                <option value="Chờ thanh toán" {{ request('status') === 'Chờ thanh toán' ? 'selected' : '' }}>Chờ thanh toán</option>
                                <option value="Thất bại" {{ request('status') === 'Thất bại' ? 'selected' : '' }}>Thất bại</option>
                            </select>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="col-lg-2 col-md-6 col-sm-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill font-outfit fw-bold px-3 py-2 rounded-3">
                                <i class="bi bi-funnel-fill me-1"></i> Lọc
                            </button>
                            @if(request()->anyFilled(['search', 'from_date', 'to_date', 'status', 'method']))
                                <a href="{{ route('user.payments.history') }}" class="btn btn-outline-secondary font-outfit fw-bold px-3 py-2 rounded-3" title="Đặt lại bộ lọc">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- PAYMENT LIST -->
            @if($payments->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 bg-white text-center py-5 px-4">
                    <div class="py-4">
                        <div class="display-1 text-muted mb-3"><i class="bi bi-inbox-fill text-black-50"></i></div>
                        <h4 class="fw-bold font-outfit text-dark">Không tìm thấy giao dịch nào</h4>
                        <p class="text-muted font-inter mx-auto" style="max-width: 420px; font-size: 14px;">
                            Không khớp dữ liệu thanh toán nào theo từ khóa hoặc bộ lọc của bạn. Hãy thử thay đổi bộ lọc hoặc xem danh sách lịch khám.
                        </p>
                        <a href="{{ route('appointments.index') }}" class="btn btn-primary rounded-3 font-outfit fw-bold mt-2 px-4">
                            <i class="bi bi-calendar-date me-1"></i> Xem lịch khám của bạn
                        </a>
                    </div>
                </div>
            @else
                @php
                $methodIcons = [
                    'QR' => '📱', 'ATM' => '🏦', 'MoMo' => '💜',
                    'ZaloPay' => '💙', 'Counter' => '🏥',
                ];
                $methodColors = [
                    'QR' => '#f0fdf4', 'ATM' => '#eff6ff', 'MoMo' => '#fdf4ff',
                    'ZaloPay' => '#eff6ff', 'Counter' => '#fff7ed',
                ];
                @endphp

                <div class="d-flex flex-column gap-3">
                    @foreach($payments as $p)
                    @php
                    $statusClass = match($p->status) {
                        'Thành công', 'Đã thanh toán' => 'bg-success-subtle text-success border-success-subtle',
                        'Chờ thanh toán', 'Chờ xử lý', 'Chưa thanh toán' => 'bg-warning-subtle text-warning border-warning-subtle',
                        'Thất bại' => 'bg-danger-subtle text-danger border-danger-subtle',
                        default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                    };
                    @endphp
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white hover-card transition-all">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                
                                <!-- ICON METHOD -->
                                <div class="col-auto">
                                    <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm border" 
                                         style="width: 52px; height: 52px; background-color: {{ $methodColors[$p->method] ?? '#f8fafc' }}; border-color: rgba(0,0,0,0.05) !important;">
                                        <span class="fs-4">{{ $methodIcons[$p->method] ?? '💳' }}</span>
                                    </div>
                                </div>

                                <!-- CORE CONTENT -->
                                <div class="col">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        @if($p->appointment?->service)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-outfit" style="font-size: 11px; font-weight: 600;">
                                                <i class="bi bi-tag-fill me-1"></i> Dịch vụ
                                            </span>
                                        @else
                                            <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill font-outfit" style="font-size: 11px; font-weight: 600;">
                                                <i class="bi bi-person-fill me-1"></i> Bác sĩ
                                            </span>
                                        @endif
                                        <span class="text-muted small font-inter">
                                            Mã GD: <code class="text-dark fw-bold font-inter">{{ $p->transaction_ref }}</code>
                                        </span>
                                    </div>

                                    <h5 class="fw-bold text-dark font-outfit mb-1" style="font-size: 16px;">
                                        @if($p->appointment?->service)
                                            {{ $p->appointment->service->service_name }}
                                        @else
                                            Bác sĩ: {{ $p->appointment?->schedule?->doctor?->full_name ?? 'Không rõ bác sĩ' }}
                                        @endif
                                    </h5>

                                    <div class="d-flex flex-wrap align-items-center gap-x-3 gap-y-1 text-muted small font-inter">
                                        <span>
                                            <i class="bi bi-calendar3 me-1"></i> 
                                            {{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('H:i d/m/Y') : 'Chưa ghi nhận' }}
                                        </span>
                                        <span class="d-none d-sm-inline text-black-50">•</span>
                                        <span>
                                            Phương thức: <strong class="text-secondary">{{ $p->method }}</strong>
                                        </span>
                                    </div>
                                </div>

                                <!-- PRICE & ACTION -->
                                <div class="col-md-auto text-start text-md-end border-start-md ps-md-4" style="border-left: 1px solid #f1f5f9;">
                                    <div class="mb-2">
                                        <span class="text-muted small font-inter d-block d-md-inline me-1">Thực thu:</span>
                                        <span class="fw-extrabold font-outfit text-primary" style="font-size: 20px;">
                                            {{ number_format($p->total_amount, 0, ',', '.') }}đ
                                        </span>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-md-end align-items-center">
                                        <span class="badge border rounded-pill px-3 py-1.5 font-outfit fw-bold {{ $statusClass }}" style="font-size: 11px;">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> {{ $p->status }}
                                        </span>

                                        @if($p->isPaid())
                                            <a href="{{ route('user.payments.success', $p->payment_id) }}" 
                                               class="btn btn-outline-primary btn-sm rounded-3 font-outfit fw-bold px-3 py-1.5"
                                               style="font-size: 12px;">
                                                <i class="bi bi-receipt me-1"></i> Biên lai
                                            </a>
                                        @elseif($p->status === 'Chờ thanh toán' || $p->status === 'Chưa thanh toán')
                                            <a href="{{ route('user.payments.qr', $p->payment_id) }}" 
                                               class="btn btn-warning btn-sm rounded-3 text-dark font-outfit fw-bold px-3 py-1.5"
                                               style="font-size: 12px;">
                                                <i class="bi bi-credit-card me-1"></i> Thanh toán ngay
                                            </a>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- PAGINATION -->
                <div class="d-flex justify-content-center mt-5 font-outfit">
                    {{ $payments->links() }}
                </div>
            @endif
            
        </div>
    </div>
</div>

<style>
    .font-outfit { font-family: 'Outfit', 'Be Vietnam Pro', sans-serif !important; }
    .font-inter { font-family: 'Inter', 'Be Vietnam Pro', sans-serif !important; }
    .font-14 { font-size: 14px !important; }
    .hover-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .hover-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08) !important;
    }
    
    @media (min-width: 768px) {
        .border-start-md {
            border-left: 1px solid #e2e8f0 !important;
        }
    }
</style>
@endsection
