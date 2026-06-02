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
            <form action="{{ route('admin.devices.update', $device->id) }}" method="POST" data-device-form novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="lock_version" value="{{ $device->lock_version }}">

                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Tên thiết bị <span class="text-danger">*</span></label>
                        <input type="text" name="name" minlength="2" maxlength="150" pattern="^[A-Za-zÀ-ỹ]+( [A-Za-zÀ-ỹ]+)*$" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $device->name) }}" required data-error-required="Vui lòng nhập tên thiết bị." data-error-pattern="Tên thiết bị chỉ được nhập chữ và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ." data-error-minlength="Tên thiết bị phải có ít nhất 2 ký tự.">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Mã thiết bị <span class="text-danger">*</span></label>
                        <input type="text" name="code" maxlength="50" pattern="^[A-Z]+(-[A-Z]+)*-[0-9]+$" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $device->code) }}" required data-uppercase="1" data-no-edge-space="1" data-error-edge-space="Mã thiết bị không được có khoảng trắng ở đầu hoặc cuối." data-error-required="Vui lòng nhập mã thiết bị." data-error-pattern="Mã thiết bị phải đúng định dạng chữ-gạch ngang-số, ví dụ IMG-US-001.">
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                        <select name="device_type_id" class="form-select @error('device_type_id') is-invalid @enderror" required data-error-required="Vui lòng chọn danh mục thiết bị.">
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ old('device_type_id', $device->device_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('device_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required data-error-required="Vui lòng chọn trạng thái thiết bị.">
                            @foreach(\App\Models\Device::STATUSES as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $device->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Ngày mua <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', $device->purchase_date?->toDateString()) }}" max="{{ now()->toDateString() }}" required data-error-required="Vui lòng nhập ngày mua thiết bị." data-error-max="Ngày mua không được lớn hơn hôm nay.">
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

@include('admin.devices._form_validation')
