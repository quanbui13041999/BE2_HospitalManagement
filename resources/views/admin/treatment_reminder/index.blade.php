@extends('layouts.admin')

@section('title', 'Quản lý Tuân Thủ Điều Trị')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Quản lý Tuân Thủ Điều Trị</h4>
            <small class="text-muted">Theo dõi và nhắc nhở bệnh nhân tuân thủ phác đồ</small>
        </div>
        <div>
            <a href="{{ route('admin.treatment.compliance') }}" class="btn btn-info btn-sm me-2">
                <i class="fas fa-chart-pie me-1"></i> Báo cáo tổng hợp
            </a>
            <a href="{{ route('admin.treatment.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Tạo nhắc nhở
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">Bệnh nhân</th>
                            <th>Liên hệ</th>
                            <th class="text-center">Tổng nhắc nhở</th>
                            <th class="text-center">Đã xác nhận</th>
                            <th class="text-center">Tỷ lệ tuân thủ</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($patient->full_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $patient->full_name }}</div>
                                            <small class="text-muted">ID: #{{ $patient->user_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $patient->email }}</div>
                                    <small class="text-muted">{{ $patient->phone }}</small>
                                </td>
                                <td class="text-center">{{ $patient->total_reminders }}</td>
                                <td class="text-center">{{ $patient->confirmed_reminders }}</td>
                                <td class="text-center">
                                    @php
                                        $rate = $patient->total_reminders > 0 ? round(($patient->confirmed_reminders / $patient->total_reminders) * 100) : 0;
                                        $color = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                                    @endphp
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; max-width: 100px;">
                                            <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $rate }}%"></div>
                                        </div>
                                        <span class="fw-bold text-{{ $color }}">{{ $rate }}%</span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.treatment.show', $patient->user_id) }}" class="btn btn-light btn-sm text-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.treatment.create', ['user_id' => $patient->user_id]) }}" class="btn btn-light btn-sm text-success">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    Không tìm thấy dữ liệu bệnh nhân nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($patients->hasPages())
            <div class="card-footer bg-white py-3 border-0">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
