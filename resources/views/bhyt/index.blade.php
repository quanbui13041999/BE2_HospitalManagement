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
                                                placeholder="Nhập mã thẻ BHYT (VD: BHYT-001-2025-001)"
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
                                    @php
                                        $res  = session('bhyt_result');
                                        $card = $res['card'];
                                        $user = $card->user;
                                    @endphp
                                    <div class="mt-4 p-3 bg-light rounded" id="bhytResult">
                                        <h6 class="text-success"><i class="bi bi-check-circle"></i> Kết quả tra cứu:</h6>
                                        <table class="table table-sm table-bordered mt-2">
                                            <tr>
                                                <th width="30%">Mã thẻ BHYT:</th>
                                                <td>{{ $card->card_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>Nhà cung cấp:</th>
                                                <td>{{ $card->provider ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tên bệnh nhân:</th>
                                                <td>{{ $user->full_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Số điện thoại:</th>
                                                <td>{{ $user->phone ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td>{{ $user->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Địa chỉ:</th>
                                                <td>{{ $user->address ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Ngày sinh:</th>
                                                <td>{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Ngày cấp:</th>
                                                <td>{{ $card->issued_date ? \Carbon\Carbon::parse($card->issued_date)->format('d/m/Y') : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Ngày hết hạn:</th>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($card->expiry_date)->format('d/m/Y') }}
                                                    @php
                                                        $badgeColor = match($res['expiry_status']) {
                                                            'expired' => 'danger',
                                                            'danger'  => 'warning',
                                                            default   => 'success',
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $badgeColor }}">
                                                        Còn {{ $res['days_remaining'] }} ngày
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Tỷ lệ BHYT chi trả:</th>
                                                <td>{{ $card->discount_pct }}%</td>
                                            </tr>
                                            <tr>
                                                <th>Trạng thái:</th>
                                                <td>
                                                    <span class="badge bg-{{ $card->status === 'Còn hạn' ? 'success' : 'danger' }}">
                                                        {{ $card->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                        @if(!empty($res['pending_payment']))
                                        <div class="alert alert-success">
                                            <i class="bi bi-receipt"></i>
                                            Hóa đơn chưa thanh toán #{{ $res['pending_payment']->payment_id }}
                                            — {{ number_format($res['pending_payment']->total_amount, 0, ',', '.') }} đ
                                            <button class="btn btn-sm btn-success float-end"
                                                onclick="applyBhyt({{ $res['pending_payment']->payment_id }}, '{{ $card->card_number }}')">
                                                <i class="bi bi-check-circle"></i> Áp dụng BHYT
                                            </button>
                                        </div>
                                        @else
                                        <div class="alert alert-secondary mb-0">
                                            <i class="bi bi-info-circle"></i> Không có hóa đơn đang chờ thanh toán.
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
                        <div class="card-body p-0">
                            @if(isset($expiringSoon) && $expiringSoon->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
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
                                        @php
                                            $daysLeft = (int) now()->diffInDays($card->expiry_date, false);
                                            $u = $card->user;
                                        @endphp
                                        <tr>
                                            <td>{{ $card->card_number }}</td>
                                            <td>{{ $u->full_name ?? 'N/A' }}</td>
                                            <td>{{ $u->phone ?? 'N/A' }}</td>
                                            <td>{{ $u->email ?? 'N/A' }}</td>
                                            <td>{{ $u->address ?? 'N/A' }}</td>
                                            <td>{{ ($u && $u->date_of_birth) ? \Carbon\Carbon::parse($u->date_of_birth)->format('d/m/Y') : 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($card->expiry_date)->format('d/m/Y') }}</td>
                                            <td>
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
                            <div class="alert alert-success m-3">
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
    function applyBhyt(paymentId, cardNumber) {
        fetch('{{ route("admin.bhyt.apply") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    payment_id: paymentId,
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
