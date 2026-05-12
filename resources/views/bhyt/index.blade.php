@extends('layouts.admin')

@section('title', 'Quản lý Bảo hiểm Y tế')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Quản Lý Bảo Hiểm Y Tế (BHYT)</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Mọi thắc mắc về BHYT, vui lòng liên hệ phòng khám để được hỗ trợ!
                    </div>

                    <!-- Form tra cứu BHYT -->
                    <div class="row mb-4">
                        <div class="col-md-6 mx-auto">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bi bi-search"></i> Tra cứu thẻ BHYT</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.bhyt.lookup') }}" method="POST" id="bhytLookupForm">
                                        @csrf
                                        <div class="input-group">
                                            <input type="text"
                                                name="card_number"
                                                class="form-control @error('card_number') is-invalid @enderror"
                                                placeholder="Nhập mã thẻ BHYT (VD: HC4230145678910)"
                                                value="{{ old('card_number') }}">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="bi bi-search"></i> Tra cứu
                                            </button>
                                        </div>
                                        @error('card_number')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </form>

                                    @if(session('bhyt_result'))
                                    <div class="mt-4 p-3 bg-light rounded" id="bhytResult">
                                        <h6 class="text-success"><i class="bi bi-check-circle"></i> Kết quả tra cứu:</h6>
                                        <table class="table table-sm table-bordered mt-2">
                                            <tr>
                                                <th width="30%">Mã thẻ BHYT:</th>
                                                <td>{{ session('bhyt_result')['card']->card_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tên bệnh nhân:</th>
                                                <td>{{ session('bhyt_result')['card']->patient->full_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Số điện thoại:</th>
                                                <td>{{ session('bhyt_result')['card']->patient->phone ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td>{{ session('bhyt_result')['card']->patient->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Địa chỉ:</th>
                                                <td>{{ session('bhyt_result')['card']->patient->address ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Ngày sinh:</th>
                                                <td>{{ session('bhyt_result')['card']->patient->dob ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Ngày hết hạn:</th>
                                                <td>
                                                    {{ \Carbon\Carbon::parse(session('bhyt_result')['card']->expiry_date)->format('d/m/Y') }}
                                                    <span class="badge bg-{{ session('bhyt_result')['expiry_status'] === 'expired' ? 'danger' : (session('bhyt_result')['expiry_status'] === 'danger' ? 'warning' : 'success') }}">
                                                        Còn {{ session('bhyt_result')['days_remaining'] }} ngày
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Tỷ lệ BHYT chi trả:</th>
                                                <td>{{ session('bhyt_result')['card']->coverage_rate }}%</td>
                                            </tr>
                                        </table>

                                        @if(session('bhyt_result')['pending_invoice'])
                                        <div class="alert alert-success">
                                            <i class="bi bi-receipt"></i>
                                            Hóa đơn chưa thanh toán #{{ session('bhyt_result')['pending_invoice']->invoice_number }}
                                            <button class="btn btn-sm btn-success float-end" onclick="applyBhyt({{ session('bhyt_result')['pending_invoice']->invoice_id }}, '{{ session('bhyt_result')['card']->card_number }}')">
                                                <i class="bi bi-check-circle"></i> Áp dụng BHYT
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách thẻ sắp hết hạn -->
                    <div class="card mt-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="bi bi-clock-history"></i> Thẻ BHYT sắp hết hạn (trong 60 ngày)</h6>
                        </div>
                        <div class="card-body">
                            @if(isset($expiringSoon) && $expiringSoon->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Mã thẻ BHYT</th>
                                            <th>Bệnh nhân</th>
                                            <th>Số điện thoại</th>
                                            <th>Email</th>
                                            <th>Địa chỉ</th>
                                            <th>Ngày sinh</th>
                                            <th>Ngày hết hạn</th>
                                            <th>Ngày còn lại</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expiringSoon as $card)
                                        <tr>
                                            <td>{{ $card->card_number }}</td>
                                            <td>{{ $card->patient->full_name ?? 'N/A' }}</td>
                                            <td>{{ $card->patient->phone ?? 'N/A' }}</td>
                                            <td>{{ $card->patient->email ?? 'N/A' }}</td>
                                            <td>{{ $card->patient->address ?? 'N/A' }}</td>
                                            <td>{{ ($card->patient && $card->patient->dob) ? \Carbon\Carbon::parse($card->patient->dob)->format('d/m/Y') : 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($card->expiry_date)->format('d/m/Y') }}</td>
                                            <td>
                                                @php
                                                $daysLeft = now()->diffInDays($card->expiry_date, false);
                                                @endphp
                                                <span class="badge bg-{{ $daysLeft <= 30 ? 'danger' : 'warning' }}">
                                                    {{ max(0, $daysLeft) }} ngày
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle"></i> Không có thẻ BHYT nào sắp hết hạn!
                            </div>
                            @endif
                        </div>
                    </div>
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
                alert('Có lỗi xảy ra khi áp dụng BHYT');
            });
    }
</script>
@endsection