@extends('layouts.admin')

@section('title', 'Chi tiết Tuân Thủ — ' . $user->full_name)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.treatment.index') }}">Danh sách</a></li>
                <li class="breadcrumb-item active">{{ $user->full_name }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0">Hồ sơ Tuân thủ Điều trị</h4>
            <div class="btn-group">
                <a href="{{ route('admin.treatment.create', ['user_id' => $user->user_id]) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tạo nhắc nhở
                </a>
            </div>
        </div>
    </div>

    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        {{-- Thông tin bệnh nhân & Thống kê --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-4 text-center">
                    <div class="avatar-lg mx-auto mb-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 32px;">
                        {{ strtoupper(substr($user->full_name, 0, 1)) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $user->full_name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->email }} | {{ $user->phone }}</p>
                    
                    <div class="row g-2 text-start">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Tuân thủ</small>
                                <span class="h4 fw-bold text-success mb-0">{{ $data['monthStats']['compliance_rate'] }}%</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-muted d-block mb-1">Hôm nay</small>
                                <span class="h4 fw-bold mb-0">{{ $data['monthStats']['completed_today'] }}/{{ $data['monthStats']['reminders_today'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hướng dẫn điều trị --}}
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0">Hướng dẫn tại nhà</h6>
                </div>
                <div class="card-body p-4 pt-0">
                    @forelse($data['instructions'] as $instruction)
                        <div class="d-flex align-items-center p-3 border rounded-3 mb-2 bg-light">
                            <div class="me-3 text-primary">
                                <i class="fas fa-{{ $instruction->icon ?? 'tasks' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold small">{{ $instruction->instruction_text }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $instruction->detail }}</div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" disabled {{ $instruction->isCheckedToday() ? 'checked' : '' }}>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3 small">Chưa có hướng dẫn nào.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Lịch nhắc nhở & Hồ sơ bệnh án --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Lịch nhắc nhở hôm nay</h6>
                    <span class="badge bg-light text-dark">{{ now()->format('d/m/Y') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                @forelse($data['todayReminders'] as $reminder)
                                    <tr>
                                        <td class="ps-4 py-3" style="width: 100px;">
                                            <span class="fw-bold text-primary">{{ $reminder->time_label }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold small">{{ $reminder->message }}</div>
                                            <small class="text-muted">{{ ucfirst($reminder->reminder_type) }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($reminder->isConfirmed())
                                                <span class="badge bg-success-soft text-success">
                                                    <i class="fas fa-check-circle me-1"></i> Đã thực hiện
                                                </span>
                                                <div class="small text-muted" style="font-size: 10px;">{{ $reminder->confirmation->confirmed_at->format('H:i') }}</div>
                                            @else
                                                <span class="badge bg-warning-soft text-warning">Chưa thực hiện</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4" style="width: 170px;">
                                            <a href="{{ route('admin.treatment.edit', $reminder->reminder_id) }}"
                                               class="btn btn-outline-primary btn-sm me-1">
                                                <i class="fas fa-edit me-1"></i> Sửa
                                            </a>
                                            <form action="{{ route('admin.treatment.destroy', $reminder->reminder_id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-outline-danger btn-sm"
                                                        onclick="return confirm('Bạn chắc chắn muốn xóa nhắc nhở này?')">
                                                    <i class="fas fa-trash me-1"></i> Xóa
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">Không có lịch nhắc hôm nay.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0">Hồ sơ bệnh án & Đơn thuốc</h6>
                </div>
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="recordsAccordion">
                        @foreach($records as $record)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#record-{{ $record->record_id }}">
                                        <div class="d-flex justify-content-between w-100 me-3">
                                            <div>
                                                <span class="fw-bold me-2">#{{ $record->record_code }}</span>
                                                <span class="small text-muted">{{ $record->exam_date->format('d/m/Y') }}</span>
                                            </div>
                                            <span class="badge bg-light text-dark">{{ $record->doctor_name }}</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="record-{{ $record->record_id }}" class="accordion-collapse collapse" data-bs-parent="#recordsAccordion">
                                    <div class="accordion-body bg-light bg-opacity-50">
                                        <h6>Đơn thuốc:</h6>
                                        <ul class="list-group list-group-flush mb-3">
                                            @forelse($record->prescriptions as $rx)
                                                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-bold small">{{ $rx->drug_name }} - {{ $rx->dosage }}</div>
                                                        <div class="small text-muted">{{ $rx->instructions }}</div>
                                                    </div>
                                                    <span class="badge bg-white text-dark border">{{ $rx->duration_days }} ngày</span>
                                                </li>
                                            @empty
                                                <li class="list-group-item bg-transparent small text-muted">Không có đơn thuốc.</li>
                                            @endforelse
                                        </ul>
                                        
                                        <form action="{{ route('admin.treatment.generate', $record->record_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="fas fa-magic me-1"></i> Sinh nhắc nhở từ đơn thuốc này
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-success-soft { background-color: rgba(34, 160, 107, 0.1); }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1); }
</style>
@endsection
