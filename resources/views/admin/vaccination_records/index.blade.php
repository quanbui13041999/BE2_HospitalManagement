@extends('layouts.admin')

@section('title', 'Quản Lý Tiêm Chủng & Lịch Tiêm')

@section('content')
<div class="container-fluid px-4 py-3" style="background-color: #f8f9fa; min-height: 100vh;">
    <!-- Breadcrumb & Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i></a></li>
                <li class="breadcrumb-item text-muted">Tiêm chủng & Lịch tiêm</li>
            </ol>
        </nav>
        <h3 class="mb-1 fw-bold" style="color: #0b328f;">Quản Lý Tiêm Chủng & Lịch Tiêm</h3>
        <p class="text-muted small">Lưu lịch sử tiêm, nhắc lịch tiêm tiếp theo cho từng bệnh nhân</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- LEFT COLUMN: Hồ sơ tiêm chủng bệnh nhân -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header border-0 py-3" style="background-color: #1254b8; color: white;">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Hồ sơ tiêm chủng – BN-{{ str_pad($selectedPatient->user_id ?? 0, 5, '0', STR_PAD_LEFT) }}</h6>
                </div>
                
                @if($selectedPatient)
                <div class="card-body p-0 bg-white">
                    <!-- Profile Info -->
                    <div class="d-flex align-items-center p-4 border-bottom">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-3" style="width: 50px; height: 50px; background-color: #1254b8; font-size: 1.2rem;">
                            {{ strtoupper(substr($selectedPatient->full_name, 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $selectedPatient->full_name }}</h6>
                            <p class="mb-0 text-muted small">
                                {{ $selectedPatient->gender ?? 'Không rõ' }} • 
                                {{ $selectedPatient->date_of_birth ? \Carbon\Carbon::parse($selectedPatient->date_of_birth)->age . ' tuổi' : 'N/A' }} • 
                                {{ $selectedPatient->phone ?? 'Không có SĐT' }}
                            </p>
                        </div>
                    </div>

                    <!-- Vaccination Timeline -->
                    <div class="p-4" style="max-height: 350px; overflow-y: auto;">
                        @forelse($patientRecords as $record)
                        @php
                            $statusColor = 'text-success';
                            $icon = 'bi-check-circle-fill text-success';
                            $statusText = 'Đã tiêm';
                            $dateText = $record->administered_at ? $record->administered_at->format('d/m/Y') : '';
                            
                            if ($record->status === 'Chưa tiêm') {
                                if ($record->next_dose_date && \Carbon\Carbon::parse($record->next_dose_date)->isPast()) {
                                    $statusColor = 'text-danger';
                                    $icon = 'bi-x-circle-fill text-danger';
                                    $statusText = 'Quá hạn';
                                    $dateText = 'Hẹn tiêm: ' . \Carbon\Carbon::parse($record->next_dose_date)->format('d/m/Y') . ' (Đã trễ)';
                                } else {
                                    $statusColor = 'text-warning';
                                    $icon = 'bi-clock-fill text-warning';
                                    $statusText = 'Sắp đến';
                                    $dateText = 'Hẹn tiêm: ' . \Carbon\Carbon::parse($record->next_dose_date)->format('d/m/Y');
                                }
                            } elseif ($record->status === 'Đã hủy') {
                                $statusColor = 'text-secondary';
                                $icon = 'bi-slash-circle-fill text-secondary';
                                $statusText = 'Đã hủy';
                            }
                        @endphp
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                            <div class="d-flex">
                                <i class="bi {{ $icon }} fs-5 me-3 mt-1"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark">{{ $record->vaccine->vaccine_name ?? 'N/A' }}</h6>
                                    <p class="mb-0 text-muted small">Liều {{ $record->dose_number }} • {{ $dateText }}</p>
                                </div>
                            </div>
                            <span class="badge bg-opacity-10 rounded-pill {{ $statusColor }} bg-{{ str_replace('text-', '', $statusColor) }}" style="border: 1px solid currentColor;">
                                {{ $statusText }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2"></i>
                            <p class="mt-2">Bệnh nhân chưa có lịch sử tiêm chủng.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                @else
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-0">Vui lòng chọn một bệnh nhân từ danh sách bên phải.</p>
                </div>
                @endif
            </div>

            <!-- Ghi nhận tiêm mới -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold" style="color: #0b328f;"><i class="bi bi-plus-circle me-2 text-primary"></i>Ghi nhận tiêm mới</h6>
                </div>
                <div class="card-body p-4 bg-white border-top">
                    <form action="{{ route('admin.vaccination-records.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $selectedPatient->user_id ?? '' }}">
                        <input type="hidden" name="status" value="Đã tiêm">
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">Loại vaccine</label>
                            <select name="vaccine_id" class="form-select border-light-subtle" required {{ !$selectedPatient ? 'disabled' : '' }}>
                                <option value="">Chọn vaccine...</option>
                                @foreach($vaccines as $v)
                                    <option value="{{ $v->vaccine_id }}">{{ $v->vaccine_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label text-muted small fw-semibold">Ngày tiêm</label>
                                <input type="date" name="administered_at" class="form-control border-light-subtle" value="{{ date('Y-m-d') }}" required {{ !$selectedPatient ? 'disabled' : '' }}>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">Liều số</label>
                                <select name="dose_number" class="form-select border-light-subtle" required {{ !$selectedPatient ? 'disabled' : '' }}>
                                    @for($i=1; $i<=5; $i++)
                                        <option value="{{ $i }}">Liều {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-semibold">Lịch tiêm tiếp theo (Tùy chọn)</label>
                            <input type="date" name="next_dose_date" class="form-control border-light-subtle" {{ !$selectedPatient ? 'disabled' : '' }}>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn text-white py-2" style="background-color: #1254b8; border-radius: 8px;" {{ !$selectedPatient ? 'disabled' : '' }}>
                                Lư u thông tin tiêm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Lịch tiêm sắp đến -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; height: 100%;">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color: #0b328f;">
                        <i class="bi bi-calendar-event me-2"></i>Lịch tiêm sắp đến 
                        <span class="badge bg-success-subtle text-success ms-2 rounded-pill px-3 py-1 fw-normal" style="font-size: 0.75rem;">● Tháng này</span>
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                        Tạo lịch hẹn
                    </button>
                </div>
                <div class="card-body p-4 bg-white border-top">
                    
                    @php
                        $upcomingCount = $upcomingSchedules->filter(function($s) {
                            return \Carbon\Carbon::parse($s->next_dose_date)->diffInDays(now()) <= 7 && !\Carbon\Carbon::parse($s->next_dose_date)->isPast();
                        })->count();
                    @endphp
                    
                    @if($upcomingCount > 0)
                    <div class="alert mb-4" style="background-color: #fff8e6; border: 1px solid #ffe8a1; color: #b38600; border-radius: 8px;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong class="text-dark">{{ $upcomingCount }} bệnh nhân</strong> có lịch tiêm trong 7 ngày tới chưa được xác nhận.
                    </div>
                    @endif
                    
                    <div class="table-responsive rounded border">
                        <table class="table table-hover mb-0 align-middle">
                            <thead style="background-color: #f0f5fa; color: #1254b8; font-size: 0.85rem;">
                                <tr>
                                    <th class="ps-3">BỆNH NHÂN</th>
                                    <th>VACCINE</th>
                                    <th>LIỀU</th>
                                    <th>NGÀY DỰ KIẾN</th>
                                    <th class="text-center">TRẠNG THÁI</th>
                                    <th class="text-center pe-3">NHẮC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingSchedules as $schedule)
                                @php
                                    $isPast = \Carbon\Carbon::parse($schedule->next_dose_date)->isPast();
                                    $daysDiff = \Carbon\Carbon::parse($schedule->next_dose_date)->diffInDays(now());
                                    
                                    if ($isPast) {
                                        $statusText = "Quá hạn $daysDiff ngày";
                                        $statusClass = 'text-danger';
                                        $iconClass = 'btn-danger bg-danger text-white';
                                        $icon = 'bi-bell-fill';
                                    } else {
                                        $statusText = 'Chưa đặt lịch';
                                        $statusClass = 'text-warning';
                                        $iconClass = 'btn-primary bg-primary text-white';
                                        $icon = 'bi-bell';
                                    }
                                @endphp
                                <tr onclick="window.location='{{ route('admin.vaccination-records.index', ['patient_id' => $schedule->user_id]) }}'" style="cursor: pointer;" class="{{ ($selectedPatient && $selectedPatient->user_id == $schedule->user_id) ? 'bg-light' : '' }}">
                                    <td class="ps-3 fw-medium text-dark">{{ $schedule->user->full_name ?? 'N/A' }}</td>
                                    <td class="text-muted">{{ $schedule->vaccine->vaccine_name ?? 'N/A' }}</td>
                                    <td class="text-muted">Liều {{ $schedule->dose_number }}</td>
                                    <td class="fw-medium">{{ \Carbon\Carbon::parse($schedule->next_dose_date)->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <span class="{{ $statusClass }} fw-semibold" style="font-size: 0.85rem;">{{ $statusText }}</span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <button class="btn btn-sm rounded-circle {{ $iconClass }}" style="width: 32px; height: 32px; padding: 0;" title="Nhắc nhở">
                                            <i class="bi {{ $icon }}"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-calendar-check fs-2 mb-2"></i>
                                        <p class="mb-0">Không có lịch tiêm nào sắp đến.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo lịch hẹn nhanh (Tùy chọn) -->
<div class="modal fade" id="createScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.vaccination-records.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tạo lịch hẹn tiêm chủng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="Chưa tiêm">
                    <div class="mb-3">
                        <label class="form-label">Bệnh nhân</label>
                        <select name="user_id" class="form-select" required>
                            @foreach($patients as $p)
                                <option value="{{ $p->user_id }}">{{ $p->full_name }} (BN-{{ $p->user_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vaccine</label>
                        <select name="vaccine_id" class="form-select" required>
                            @foreach($vaccines as $v)
                                <option value="{{ $v->vaccine_id }}">{{ $v->vaccine_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày dự kiến tiêm</label>
                            <input type="date" name="next_dose_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Liều số</label>
                            <select name="dose_number" class="form-select" required>
                                @for($i=1; $i<=5; $i++)
                                    <option value="{{ $i }}">Liều {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu lịch hẹn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    body {
        font-family: 'Inter', sans-serif;
    }
    .table-hover tbody tr:hover {
        background-color: #f8fbff;
    }
</style>
@endsection
