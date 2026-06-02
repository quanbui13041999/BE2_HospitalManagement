@extends('layouts.admin')

@section('title', 'Sửa thiết bị')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>Sửa thiết bị</h4>
            <a href="{{ route('admin.devices.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
            </a>
        </div>
        <small class="text-muted">Phiên bản #{{ $device->lock_version }}</small>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 860px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.devices.update', $device->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="lock_version" value="{{ $device->lock_version }}">

                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Tên thiết bị <span class="text-danger">*</span></label>
                        <input type="text" name="name" maxlength="150" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $device->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Mã thiết bị <span class="text-danger">*</span></label>
                        <input type="text" name="code" maxlength="50" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $device->code) }}" required>
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                        <select name="device_type_id" class="form-select @error('device_type_id') is-invalid @enderror" required>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ old('device_type_id', $device->device_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('device_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach(\App\Models\Device::STATUSES as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $device->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Ngày mua</label>
                        <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', $device->purchase_date?->toDateString()) }}" max="{{ now()->toDateString() }}">
                        @error('purchase_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.devices.index') }}" class="btn btn-light me-2">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
