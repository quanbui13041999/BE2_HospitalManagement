@extends('layouts.admin')

@section('title', 'Sửa Nhắc Nhở Điều Trị')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.treatment.index') }}">Danh sách</a></li>
                        <li class="breadcrumb-item active">Sửa nhắc nhở</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0">Sửa Nhắc Nhở Điều Trị</h4>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-4">
                    @if(session('warning'))
                        <div class="alert alert-warning">{{ session('warning') }}</div>
                    @endif

                    <form action="{{ route('admin.treatment.update', $reminder->reminder_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="reminder_snapshot" value="{{ $reminderSnapshot }}">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Bệnh nhân</label>
                            <input type="text" class="form-control" value="{{ $reminder->user->full_name }}" disabled>
                            <input type="hidden" name="user_id" value="{{ $reminder->user_id }}">
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Loại nhắc nhở</label>
                                <select name="reminder_type" class="form-select @error('reminder_type') is-invalid @enderror" required>
                                    <option value="medicine" {{ old('reminder_type', $reminder->reminder_type) == 'medicine' ? 'selected' : '' }}>Uống thuốc</option>
                                    <option value="instruction" {{ old('reminder_type', $reminder->reminder_type) == 'instruction' ? 'selected' : '' }}>Hướng dẫn khác</option>
                                </select>
                                @error('reminder_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Thời gian nhắc</label>
                                <input type="datetime-local" name="remind_at" class="form-control @error('remind_at') is-invalid @enderror" required value="{{ old('remind_at', $reminder->remind_at->format('Y-m-d\TH:i')) }}">
                                @error('remind_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Nội dung nhắc nhở</label>
                            <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="3" required minlength="5" maxlength="255">{{ old('message', $reminder->message) }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ url()->previous() }}" class="btn btn-light px-4">Quay lại</a>
                            <button type="submit" class="btn btn-primary px-4">Cập nhật nhắc nhở</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
