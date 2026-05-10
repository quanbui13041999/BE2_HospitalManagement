{{-- resources/views/medical-records/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Danh sách hồ sơ bệnh án')

@section('content')
<div style="max-width:1100px;margin:20px auto;padding:0 16px">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">📁 Hồ Sơ Bệnh Án</h5>
    <a href="{{ route('medical-records.create') }}" class="btn btn-primary btn-sm">
        + Tạo hồ sơ mới
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13.5px">
            <thead style="background:#f8f9fa;font-size:12px;color:#666;">
                <tr>
                    <th>Mã phiếu</th>
                    <th>Bệnh nhân</th>
                    <th>Bác sĩ</th>
                    <th>Ngày khám</th>
                    <th>Loại khám</th>
                    <th>Chẩn đoán chính</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td>
                        <a href="{{ route('medical-records.show', $record->record_id) }}"
                           style="color:#1a6fb3;font-weight:600;text-decoration:none">
                            {{ $record->record_code }}
                        </a>
                    </td>
                    <td>{{ $record->patient_name }}</td>
                    <td>BS. {{ $record->doctor_name }}</td>
                    <td>{{ $record->exam_date->format('d/m/Y') }}</td>
                    <td>
                        <span style="background:#eaf4ff;color:#1a6fb3;padding:2px 8px;border-radius:10px;font-size:12px">
                            {{ $record->visit_type }}
                        </span>
                    </td>
                    <td>
                        @if($record->diagnoses->isNotEmpty())
                        {{ Str::limit($record->diagnoses->first()->diagnosis_name, 40) }}
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('medical-records.show', $record->record_id) }}" class="btn btn-sm btn-outline-primary">Xem</a>
                        <a href="{{ route('medical-records.edit', $record->record_id) }}" class="btn btn-sm btn-outline-secondary">Sửa</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Chưa có hồ sơ bệnh án nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="card-footer bg-white">
        {{ $records->links() }}
    </div>
    @endif
</div>
</div>
@endsection
