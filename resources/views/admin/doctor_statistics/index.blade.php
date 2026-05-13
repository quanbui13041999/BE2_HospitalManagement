@extends('layouts.admin')

@section('title', 'Thống kê bác sĩ')

@push('styles')
<style>
    .stat-card {
        border-radius: 12px;
        padding: 20px;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Thống Kê Bác Sĩ</h4>
            <p class="text-muted small mb-0">Hiệu suất, đánh giá và lượt khám của các bác sĩ</p>
        </div>
    </div>

    {{-- Bảng thống kê chi tiết --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-columns-reverse me-2 text-primary"></i>Danh sách thống kê chi tiết</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Bác sĩ</th>
                            <th>Chuyên khoa</th>
                            <th class="text-center">Kinh nghiệm</th>
                            <th class="text-center">Đánh giá (Sao)</th>
                            <th class="text-center">Lượt đánh giá</th>
                            <th class="text-center">Số ca khám</th>
                            <th class="text-end">Giá khám</th>
                            <th class="text-center">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statistics as $stat)
                        <tr>
                            <td>#{{ $stat->doctor_id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="fw-semibold text-primary">{{ $stat->full_name }}</div>
                                </div>
                            </td>
                            <td>{{ $stat->department_name ?? '—' }}</td>
                            <td class="text-center">{{ $stat->experience ? $stat->experience . ' năm' : '—' }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <span class="fw-bold">{{ number_format($stat->avg_rating, 1) }}</span>
                                    <i class="bi bi-star-fill text-warning small"></i>
                                </div>
                            </td>
                            <td class="text-center">{{ $stat->total_reviews }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">{{ $stat->total_appointments }}</span>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($stat->price, 0, ',', '.') }} đ</td>
                            <td class="text-center">
                                @if($stat->status)
                                    <span class="badge bg-success-subtle text-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Ngừng HĐ</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Chưa có dữ liệu thống kê.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
