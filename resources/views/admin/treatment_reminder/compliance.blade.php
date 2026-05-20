@extends('layouts.admin')

@section('title', 'Báo Cáo Tuân Thủ Điều Trị')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Báo Cáo Tuân Thủ Điều Trị</h4>
            <small class="text-muted">Tổng quan tuân thủ phác đồ toàn hệ thống tháng {{ $report['month'] }}/{{ $report['year'] }}</small>
        </div>
        <form class="d-flex gap-2" method="GET">
            <select name="month" class="form-select form-select-sm">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $m == $report['month'] ? 'selected' : '' }}>Tháng {{ $m }}</option>
                @endforeach
            </select>
            <select name="year" class="form-select form-select-sm">
                @foreach(range(now()->year - 2, now()->year) as $y)
                    <option value="{{ $y }}" {{ $y == $report['year'] ? 'selected' : '' }}>Năm {{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm px-3">Lọc</button>
        </form>
    </div>

    {{-- Thẻ thống kê --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #1a6b4a !important;">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Tỷ lệ tuân thủ TB</small>
                    <div class="h2 fw-bold mb-0 text-success">{{ $report['overall_rate'] }}%</div>
                    <div class="small text-muted mt-2">Toàn bộ hệ thống</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Tổng nhắc nhở</small>
                    <div class="h2 fw-bold mb-0">{{ number_format($report['total_reminders']) }}</div>
                    <div class="small text-muted mt-2">Đã được tạo trong tháng</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Xác nhận thực hiện</small>
                    <div class="h2 fw-bold mb-0 text-primary">{{ number_format($report['total_confirmed']) }}</div>
                    <div class="small text-muted mt-2">Bệnh nhân đã đánh dấu xong</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-body">
                    <small class="text-muted fw-bold text-uppercase">Email đã gửi</small>
                    <div class="h2 fw-bold mb-0 text-info">{{ number_format($report['sent_reminders']) }}</div>
                    <div class="small text-muted mt-2">Nhắc nhở tự động qua email</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Top 5 Tuân thủ cao nhất --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-success"><i class="fas fa-arrow-up me-2"></i>Top 5 Tuân thủ cao nhất</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                @foreach($report['top_compliant'] as $u)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $u->full_name }}</div>
                                            <small class="text-muted">#{{ $u->user_id }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="small text-muted">Thực hiện</div>
                                            <div class="fw-bold">{{ $u->confirmed }}/{{ $u->total }}</div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="badge bg-success text-white px-3">{{ $u->rate }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top 5 Tuân thủ thấp nhất --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-danger"><i class="fas fa-arrow-down me-2"></i>Top 5 Tuân thủ thấp nhất</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                @foreach($report['least_compliant'] as $u)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $u->full_name }}</div>
                                            <small class="text-muted">#{{ $u->user_id }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="small text-muted">Thực hiện</div>
                                            <div class="fw-bold">{{ $u->confirmed }}/{{ $u->total }}</div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="badge bg-danger text-white px-3">{{ $u->rate }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
