@extends('layouts.admin')

@section('title', 'Quản Lý Bảo Hiểm Y Tế (BHYT)')

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8f9fa; min-height: 100vh;">
    <!-- Breadcrumb & Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i></a></li>
                <li class="breadcrumb-item active text-primary" aria-current="page">Quản lý BHYT</li>
            </ol>
        </nav>
        <h3 class="mb-1 fw-bold" style="color: #0b328f;"><i class="bi bi-shield-check me-2"></i>Quản Lý Bảo Hiểm Y Tế (BHYT)</h3>
        <p class="text-muted small">Nhập mã, kiểm tra hạn và áp dụng giảm giá theo BHYT</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- LEFT COLUMN: Tra cứu & Sắp hết hạn -->
        <div class="col-lg-5">
            <!-- Tra cứu thẻ BHYT -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header border-0 py-3" style="background-color: #1254b8; color: white;">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-search me-2"></i>Tra cứu thẻ BHYT</h6>
                </div>
                <div class="card-body p-4 bg-white">
                    <form action="{{ route('admin.bhyt.lookup') }}" method="POST">
                        @csrf
                        <label class="form-label text-muted small fw-semibold">Mã thẻ BHYT</label>
                        <div class="input-group">
                            <input type="text" 
                                name="card_number" 
                                class="form-control" 
                                placeholder="Nhập mã thẻ..." 
                                value="{{ old('card_number', session('bhyt_result')['card']->card_number ?? '') }}"
                                style="border-radius: 8px 0 0 8px;">
                            <button class="btn btn-primary px-4" type="submit" style="background-color: #1254b8; border-color: #1254b8; border-radius: 0 8px 8px 0;">
                                <i class="bi bi-search me-1"></i> Tra
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Thẻ sắp hết hạn -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold" style="color: #0b328f;"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Thẻ sắp hết hạn</h6>
                </div>
                <div class="card-body p-0 bg-white">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background-color: #f0f5fa; color: #1254b8; font-size: 0.85rem;">
                                <th class="ps-4">BỆNH NHÂN</th>
                                <th>MÃ THẺ</th>
                                <th class="text-center pe-4">CÒN LẠI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringSoon as $card)
                            @php
                                $daysLeft = max(0, (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($card->expiry_date)->startOfDay(), false));
                                $badgeColor = $daysLeft <= 15 ? 'text-danger' : 'text-warning';
                            @endphp
                            <tr>
                                <td class="ps-4 fw-medium">{{ $card->patient->full_name ?? 'N/A' }}</td>
                                <td class="text-muted">{{ $card->card_number }}</td>
                                <td class="text-center pe-4">
                                    <span class="{{ $badgeColor }} fw-bold" style="font-size: 0.85rem;">{{ $daysLeft }} ngày</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Không có thẻ nào sắp hết hạn.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Áp dụng BHYT -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; height: 100%;">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold" style="color: #0b328f;"><i class="bi bi-receipt me-2"></i>Áp dụng BHYT vào thanh toán</h6>
                </div>
                <div class="card-body p-4 bg-white">
                    @if(session('bhyt_result'))
                        @php
                            $card = session('bhyt_result')['card'];
                            $invoice = session('bhyt_result')['pending_invoice'];
                        @endphp
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label text-muted small fw-semibold">Bệnh nhân</label>
                                <input type="text" class="form-control bg-light text-dark border-0" value="{{ data_get($card, 'patient.full_name') ?? 'N/A' }} – BN-{{ str_pad(data_get($card, 'patient_id', data_get($card, 'user_id', 0)), 5, '0', STR_PAD_LEFT) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">Mức hưởng BHYT</label>
                                <input type="text" class="form-control bg-light text-success fw-bold border-0 text-center" value="{{ data_get($card, 'coverage_rate', data_get($card, 'discount_pct', 0)) }}%" readonly>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3" style="color: #0b328f;">Chi tiết tính giảm</h6>
                        
                        @if($invoice)
                            <div class="table-responsive rounded border mb-4">
                                <table class="table mb-0 align-middle">
                                    <thead style="background-color: #f0f5fa; color: #1254b8; font-size: 0.85rem;">
                                        <tr>
                                            <th class="ps-3">DỊCH VỤ</th>
                                            <th class="text-end">GIÁ GỐC</th>
                                            <th class="text-end">BHYT CHI TRẢ</th>
                                            <th class="text-end pe-3">BN PHẢI TRẢ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalOriginal = data_get($invoice, 'subtotal', 0);
                                            $coveragePercent = data_get($card, 'coverage_rate', data_get($card, 'discount_pct', 0)) / 100;
                                            $totalBhyt = $totalOriginal * $coveragePercent;
                                            $totalPatient = $totalOriginal - $totalBhyt;
                                        @endphp
                                        
                                        <!-- Placeholder items as invoice items might not be loaded in session -->
                                        <tr>
                                            <td class="ps-3 fw-medium">Khám tổng quát / Dịch vụ y tế</td>
                                            <td class="text-end">{{ number_format($totalOriginal, 0, ',', '.') }} đ</td>
                                            <td class="text-end text-success">{{ number_format($totalBhyt, 0, ',', '.') }} đ ({{ data_get($card, 'coverage_rate', data_get($card, 'discount_pct', 0)) }}%)</td>
                                            <td class="text-end fw-bold pe-3">{{ number_format($totalPatient, 0, ',', '.') }} đ</td>
                                        </tr>
                                        
                                        <tr style="background-color: #f0f5fa;">
                                            <td class="ps-3 fw-bold text-dark">Tổng cộng</td>
                                            <td class="text-end fw-bold text-dark">{{ number_format($totalOriginal, 0, ',', '.') }} đ</td>
                                            <td class="text-end fw-bold text-success">{{ number_format($totalBhyt, 0, ',', '.') }} đ</td>
                                            <td class="text-end fw-bold text-primary pe-3">{{ number_format($totalPatient, 0, ',', '.') }} đ</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert d-flex align-items-center mb-4" style="background-color: #e6f0ff; border: 1px solid #cce0ff; color: #0b328f; border-radius: 8px;">
                                <i class="bi bi-info-circle-fill me-3 fs-5"></i>
                                <div>
                                    BHYT chi trả <strong>{{ number_format($totalBhyt, 0, ',', '.') }} đ</strong> ({{ data_get($card, 'coverage_rate', data_get($card, 'discount_pct', 0)) }}%). 
                                    Bệnh nhân chỉ cần thanh toán thêm <strong>{{ number_format($totalPatient, 0, ',', '.') }} đ</strong>.
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button class="btn px-5 text-white" style="background-color: #1254b8; border-radius: 8px;" onclick="applyBhyt({{ data_get($invoice, 'payment_id') }}, '{{ data_get($card, 'card_number') }}')">
                                    <i class="bi bi-check-circle me-2"></i>Xác nhận áp dụng BHYT
                                </button>
                            </div>
                        @else
                            <div class="text-center py-5 bg-light rounded" style="border: 1px dashed #ccc;">
                                <i class="bi bi-emoji-smile text-muted" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0 text-muted">Bệnh nhân không có hóa đơn chờ thanh toán nào.</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5 d-flex flex-column align-items-center justify-content-center h-100">
                            <i class="bi bi-shield-check" style="font-size: 3rem; color: #e2e8f0;"></i>
                            <h6 class="mt-3 text-muted">Vui lòng tra cứu thẻ BHYT ở khung bên trái</h6>
                            <p class="text-muted small">Chi tiết thanh toán sẽ được hiển thị tại đây.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function applyBhyt(invoiceId, cardNumber) {
        fetch('{{ route("admin.bhyt.apply") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                invoice_id: invoiceId,
                card_number: cardNumber
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi áp dụng BHYT. Hóa đơn có thể không tồn tại hoặc đã được thanh toán.');
        });
    }
</script>

<style>
    body {
        font-family: 'Inter', sans-serif;
    }
    .table-hover tbody tr:hover {
        background-color: #f8fbff;
    }
</style>
@endsection