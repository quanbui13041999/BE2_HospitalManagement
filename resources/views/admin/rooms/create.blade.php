{{-- resources/views/admin/rooms/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Thêm Phòng khám mới')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="mb-0">Thêm Phòng khám mới</h4>
    </div>

    <div class="card shadow-sm" style="max-width:680px">
        <div class="card-header fw-semibold"><i class="bi bi-door-open me-2"></i>Thông tin phòng</div>
        <form method="POST" action="{{ route('admin.rooms.store') }}">
            @csrf
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Mã phòng <span class="text-danger">*</span></label>
                    <input type="text" name="room_code"
                           class="form-control @error('room_code') is-invalid @enderror"
                           value="{{ old('room_code') }}" placeholder="VD: P501" required>
                    @error('room_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label">Tên phòng</label>
                    <input type="text" name="room_name" class="form-control"
                           value="{{ old('room_name') }}" placeholder="VD: Phòng khám 501">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Khoa</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Chọn khoa --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}"
                                {{ old('department_id') == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Loại phòng <span class="text-danger">*</span></label>
                    <select name="room_type" class="form-select @error('room_type') is-invalid @enderror" required>
                        <option value="">-- Chọn loại --</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type }}" {{ old('room_type') === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        @foreach($roomStatuses as $st)
                            <option value="{{ $st }}" {{ old('status', 'Trống') === $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i>Tạo phòng
                </button>
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
@endsection
