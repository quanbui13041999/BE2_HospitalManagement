@extends('layouts.admin')

@section('title', 'Sửa Hồ sơ tiêm chủng')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>Sửa Hồ sơ tiêm chủng</h4>
            <a href="{{ route('admin.vaccination-records.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 800px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.vaccination-records.update', $vaccination_record) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bệnh nhân <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">-- Chọn bệnh nhân --</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->user_id }}" {{ old('user_id', $vaccination_record->user_id) == $patient->user_id ? 'selected' : '' }}>
                                    {{ $patient->full_name }} - {{ $patient->phone ?? $patient->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bác sĩ chỉ định</label>
                        <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                            <option value="">-- Chọn bác sĩ --</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->doctor_id }}" {{ old('doctor_id', $vaccination_record->doctor_id) == $doctor->doctor_id ? 'selected' : '' }}>
                                    {{ $doctor->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Vắc xin <span class="text-danger">*</span></label>
                        <select name="vaccine_id" class="form-select @error('vaccine_id') is-invalid @enderror" required>
                            <option value="">-- Chọn vắc xin --</option>
                            @foreach($vaccines as $vaccine)
                                <option value="{{ $vaccine->vaccine_id }}" {{ old('vaccine_id', $vaccination_record->vaccine_id) == $vaccine->vaccine_id ? 'selected' : '' }}>
                                    {{ $vaccine->vaccine_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vaccine_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mũi thứ <span class="text-danger">*</span></label>
                        <input type="number" name="dose_number" class="form-control @error('dose_number') is-invalid @enderror" value="{{ old('dose_number', $vaccination_record->dose_number) }}" min="1" required>
                        @error('dose_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày tiêm</label>
                        <input type="datetime-local" name="administered_at" class="form-control @error('administered_at') is-invalid @enderror" value="{{ old('administered_at', $vaccination_record->administered_at ? $vaccination_record->administered_at->format('Y-m-d\TH:i') : '') }}">
                        @error('administered_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày hẹn mũi tiếp</label>
                        <input type="date" name="next_dose_date" class="form-control @error('next_dose_date') is-invalid @enderror" value="{{ old('next_dose_date', $vaccination_record->next_dose_date ? $vaccination_record->next_dose_date->format('Y-m-d') : '') }}">
                        @error('next_dose_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số lô vắc xin</label>
                        <input type="text" name="batch_number" class="form-control @error('batch_number') is-invalid @enderror" value="{{ old('batch_number', $vaccination_record->batch_number) }}">
                        @error('batch_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Chưa tiêm" {{ old('status', $vaccination_record->status) == 'Chưa tiêm' ? 'selected' : '' }}>Chưa tiêm</option>
                            <option value="Đã tiêm" {{ old('status', $vaccination_record->status) == 'Đã tiêm' ? 'selected' : '' }}>Đã tiêm</option>
                            <option value="Đã hủy" {{ old('status', $vaccination_record->status) == 'Đã hủy' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Ghi chú</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $vaccination_record->notes) }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.vaccination-records.index') }}" class="btn btn-light me-2">Hủy</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
