@extends('layouts.admin')

@section('title', 'Tạo Nhắc Nhở Điều Trị')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.treatment.index') }}">Danh sách</a></li>
                        <li class="breadcrumb-item active">Tạo nhắc nhở</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0">Tạo Nhắc Nhở Điều Trị Thủ Công</h4>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <form action="{{ route('admin.treatment.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">Bệnh nhân</label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">-- Chọn bệnh nhân --</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->user_id }}" {{ (request('user_id') == $patient->user_id) ? 'selected' : '' }}>
                                        {{ $patient->full_name }} (#{{ $patient->user_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Hồ sơ bệnh án (Tùy chọn)</label>
                            <select name="record_id" class="form-select @error('record_id') is-invalid @enderror">
                                <option value="">-- Không liên kết --</option>
                                @foreach($records as $record)
                                    <option value="{{ $record->record_id }}">
                                        {{ $record->record_code }} - {{ $record->patient->full_name }} ({{ $record->exam_date->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('record_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Loại nhắc nhở</label>
                                <select name="reminder_type" class="form-select @error('reminder_type') is-invalid @enderror" required>
                                    <option value="medicine">Uống thuốc</option>
                                    <option value="instruction">Hướng dẫn khác</option>
                                </select>
                                @error('reminder_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Thời gian nhắc</label>
                                <input type="datetime-local" name="remind_at" class="form-control @error('remind_at') is-invalid @enderror" required value="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
                                @error('remind_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Nội dung nhắc nhở</label>
                            <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="3" required placeholder="Ví dụ: Uống thuốc Paracetamol 500mg sau khi ăn sáng"></textarea>
                            <div class="form-text">Gợi ý: Thêm cụm từ "NGUY HIỂM" để làm nổi bật cảnh báo.</div>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.treatment.index') }}" class="btn btn-light px-4">Hủy</a>
                            <button type="submit" class="btn btn-primary px-4">Lưu nhắc nhở</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
