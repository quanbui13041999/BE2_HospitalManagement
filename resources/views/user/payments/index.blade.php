{{-- resources/views/admin/payments/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Quản lý thanh toán')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap');

    .admin-pay { font-family: 'Be Vietnam Pro', sans-serif; }

    /* ---- Stat cards ---- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        border-left: 4px solid;
    }

    .stat-card.green  { border-color: #10b981; }
    .stat-card.blue   { border-color: #3b82f6; }
    .stat-card.orange { border-color: #f59e0b; }
    .stat-card.red    { border-color: #ef4444; }

    .stat-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin-bottom: .35rem; }
    .stat-value { font-size: 1.5rem; font-weight: 800; color: #1e293b; }
    .stat-sub   { font-size: .78rem; color: #94a3b8; margin-top: .2rem; }

    /* ---- Filter bar ---- */
    .filter-bar {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: flex-end;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }

    .filter-group { display: flex; flex-direction: column; gap: .3rem; }
    .filter-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; }

    .filter-input, .filter-select {
        padding: .55rem .9rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: .875rem;
        color: #1e293b;
        outline: none;
    }

    .filter-input:focus, .filter-select:focus { border-color: #6366f1; }

    .btn-filter {
        padding: .55rem 1.25rem;
        background: #6366f1;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-weight: 600;
        font-size: .875rem;
        cursor: pointer;
        transition: background .2s;
        white-space: nowrap;
    }

    .btn-filter:hover { background: #4f46e5; }

    .btn-reset {
        padding: .55rem 1rem;
        background: none;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: .875rem;
        color: #64748b;
        cursor: pointer;
        white-space: nowrap;
    }

    /* ---- Table ---- */
    .pay-table-wrap {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        overflow: hidden;
    }

    .table-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }

    .table-count {
        font-size: .82rem;
        color: #94a3b8;
    }

    table { width: 100%; border-collapse: collapse; }

    thead th {
        background: #f8fafc;
        padding: .75rem 1.25rem;
        text-align: left;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    tbody td {
        padding: 1rem 1.25rem;
        font-size: .875rem;
        color: #334155;
        border-bottom: 1px solid #f8fafc;
        vertical-align: middle;
    }

    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #f8fafc; }

    .patient-cell {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .patient-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700;
        font-size: .9rem;
        flex-shrink: 0;
    }

    .patient-name { font-weight: 600; color: #1e293b; }
    .patient-appt { font-size: .75rem; color: #94a3b8; }

    .method-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .2rem .65rem;
        border-radius: 6px;
        font-size: .78rem;
        font-weight: 600;
    }

    .status-badge {
        display: inline-block;
        padding: .25rem .75rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
    }

    .s-paid    { background: #dcfce7; color: #16a34a; }
    .s-pending { background: #fef9c3; color: #92400e; }
    .s-failed  { background: #fee2e2; color: #dc2626; }
    .s-refund  { background: #dbeafe; color: #1d4ed8; }

    .amount-cell { font-weight: 700; color: #1e293b; font-size: .95rem; }

    .action-link {
        color: #6366f1;
        text-decoration: none;
        font-weight: 600;
        font-size: .82rem;
    }

    .action-link:hover { text-decoration: underline; }

    /* Revenue chart */
    .chart-section {
        background: #fff;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        margin-bottom: 1.75rem;
    }

    .chart-title {
        font-size: .9rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .mini-chart {
        display: flex;
        align-items: flex-end;
        gap: .5rem;
        height: 80px;
    }

    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: .3rem; }

    .bar {
        width: 100%;
        background: linear-gradient(to top, #6366f1, #8b5cf6);
        border-radius: 4px 4px 0 0;
        min-height: 4px;
        transition: height .3s ease;
    }

    .bar-label { font-size: .65rem; color: #94a3b8; }

    @media (max-width: 900px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .stats-grid { grid-template-columns: 1fr; }
        thead th:nth-child(4),
        tbody td:nth-child(4) { display: none; }
    }
</style>
@endpush

@section('content')
<div class="admin-pay">

    {{-- Page title --}}
    <div style="margin-bottom:1.5rem">
        <h1 style="font-size:1.5rem;font-weight:800;color:#1e293b;margin:0 0 .25rem">
            💳 Quản lý thanh toán
        </h1>
        <p style="font-size:.88rem;color:#64748b;margin:0">
            Theo dõi và quản lý tất cả giao dịch thanh toán
        </p>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card green">
            <div class="stat-label">Doanh thu hôm nay</div>
            <div class="stat-value">{{ number_format($todayStats['total'], 0, ',', '.') }}đ</div>
            <div class="stat-sub">{{ $todayStats['count'] }} giao dịch</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Doanh thu tháng này</div>
            <div class="stat-value">{{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }}đ</div>
            <div class="stat-sub">Tháng {{ now()->month }}/{{ now()->year }}</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-label">Tỉ lệ thành công</div>
            <div class="stat-value">{{ $todayStats['rate'] }}%</div>
            <div class="stat-sub">Hôm nay</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Thất bại hôm nay</div>
            <div class="stat-value">{{ $todayStats['failed'] }}</div>
            <div class="stat-sub">Giao dịch</div>
        </div>
    </div>

    {{-- Revenue mini chart --}}
    @if(!empty($revenueByDay))
    <div class="chart-section">
        <div class="chart-title">📊 Doanh thu 7 ngày gần nhất</div>
        @php
        $maxRev = max(array_column($revenueByDay, 'total') ?: [1]);
        @endphp
        <div class="mini-chart">
            @for($i = 6; $i >= 0; $i--)
                @php
                $date = now()->subDays($i)->format('Y-m-d');
                $rev  = $revenueByDay[$date]['total'] ?? 0;
                $h    = $maxRev > 0 ? max(4, round($rev / $maxRev * 72)) : 4;
                @endphp
                <div class="bar-col">
                    <div class="bar" style="height:{{ $h }}px"
                         title="{{ number_format($rev,0,',','.') }}đ"></div>
                    <div class="bar-label">{{ now()->subDays($i)->format('d/m') }}</div>
                </div>
            @endfor
        </div>
    </div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.payments.index') }}">
        <div class="filter-bar">
            <div class="filter-group">
                <span class="filter-label">Tìm kiếm</span>
                <input type="text" name="search" class="filter-input"
                       placeholder="Mã GD, tên bệnh nhân..." value="{{ request('search') }}"
                       style="min-width:200px">
            </div>
            <div class="filter-group">
                <span class="filter-label">Từ ngày</span>
                <input type="date" name="from_date" class="filter-input" value="{{ request('from_date') }}">
            </div>
            <div class="filter-group">
                <span class="filter-label">Đến ngày</span>
                <input type="date" name="to_date" class="filter-input" value="{{ request('to_date') }}">
            </div>
            <div class="filter-group">
                <span class="filter-label">Trạng thái</span>
                <select name="status" class="filter-select">
                    <option value="">Tất cả</option>
                    @foreach(['Đã thanh toán','Chờ thanh toán','Chưa thanh toán','Thất bại','Hoàn tiền'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Phương thức</span>
                <select name="method" class="filter-select">
                    <option value="">Tất cả</option>
                    @foreach(['QR','ATM','MoMo','ZaloPay','Counter'] as $m)
                    <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-filter">🔍 Lọc</button>
            <a href="{{ route('admin.payments.index') }}"><button type="button" class="btn-reset">✕ Reset</button></a>
        </div>
    </form>

    {{-- Table --}}
    <div class="pay-table-wrap">
        <div class="table-header">
            <div class="table-title">Danh sách giao dịch</div>
            <div class="table-count">Tổng: {{ $payments->total() }} giao dịch</div>
        </div>

        @if($payments->isEmpty())
            <div style="padding:3rem;text-align:center;color:#94a3b8">
                <div style="font-size:2.5rem;margin-bottom:.75rem">📭</div>
                <p>Không có giao dịch nào phù hợp.</p>
            </div>
        @else
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Bệnh nhân</th>
                        <th>Thời gian</th>
                        <th>Phương thức</th>
                        <th>Mã giao dịch</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                @php
                $methodIcons = [
                    'QR' => '📱', 'ATM' => '🏦', 'MoMo' => '💜',
                    'ZaloPay' => '💙', 'Counter' => '🏥',
                ];
                $methodColors = [
                    'QR' => '#f0fdf4,#166534', 'ATM' => '#eff6ff,#1d4ed8',
                    'MoMo' => '#fdf4ff,#7e22ce', 'ZaloPay' => '#eff6ff,#0369a1',
                    'Counter' => '#fff7ed,#c2410c',
                ];
                @endphp

                @foreach($payments as $payment)
                @php
                $patient = $payment->appointment?->user;
                $statusClass = match($payment->status) {
                    'Đã thanh toán'   => 's-paid',
                    'Chờ thanh toán',
                    'Chưa thanh toán' => 's-pending',
                    'Thất bại'        => 's-failed',
                    'Hoàn tiền'       => 's-refund',
                    default           => 's-pending',
                };
                [$mBg, $mColor] = explode(',', $methodColors[$payment->method] ?? '#f1f5f9,#475569');
                @endphp
                <tr>
                    <td>
                        <div class="patient-cell">
                            <div class="patient-avatar">
                                {{ strtoupper(substr($patient?->full_name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div class="patient-name">{{ $patient?->full_name ?? '—' }}</div>
                                <div class="patient-appt">
                                    BS. {{ $payment->appointment?->schedule?->doctor?->full_name ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600">{{ $payment->payment_date?->format('H:i') }}</div>
                        <div style="font-size:.78rem;color:#94a3b8">{{ $payment->payment_date?->format('d/m/Y') }}</div>
                    </td>
                    <td>
                        <span class="method-badge" style="background:{{ $mBg }};color:{{ $mColor }}">
                            {{ $methodIcons[$payment->method] ?? '💳' }}
                            {{ $payment->method }}
                        </span>
                    </td>
                    <td>
                        <code style="font-size:.78rem;background:#f1f5f9;padding:.2rem .5rem;border-radius:4px;color:#475569">
                            {{ $payment->transaction_ref }}
                        </code>
                    </td>
                    <td class="amount-cell">
                        {{ number_format($payment->total_amount, 0, ',', '.') }}đ
                        @if($payment->discount_amount > 0)
                        <div style="font-size:.72rem;color:#16a34a;font-weight:500">
                            -{{ number_format($payment->discount_amount, 0, ',', '.') }}đ giảm
                        </div>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ $statusClass }}">{{ $payment->status }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.payments.show', $payment->appointment_id) }}"
                           class="action-link">Chi tiết</a>
                        @if($payment->isPending())
                        <br>
                        <form method="POST"
                              action="{{ route('admin.payments.confirm', $payment->payment_id) }}"
                              style="display:inline" onsubmit="return confirm('Xác nhận đã thanh toán?')">
                            @csrf
                            <input type="hidden" name="ref" value="{{ $payment->transaction_ref }}">
                            <button type="submit"
                                    style="background:none;border:none;color:#16a34a;font-weight:600;font-size:.82rem;cursor:pointer;padding:0;font-family:inherit">
                                ✅ Xác nhận
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:1rem 1.5rem;border-top:1px solid #f1f5f9">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
