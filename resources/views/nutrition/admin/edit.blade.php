{{-- resources/views/nutrition/admin/edit.blade.php --}}
{{-- Form chỉnh sửa bài viết dinh dưỡng --}}

@extends('layouts.nutrition')

@section('title', 'Chỉnh sửa bài viết')

@push('styles')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
@endpush

@section('content')

<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.nutrition.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Chỉnh sửa bài viết</h2>
        <p class="text-muted mb-0">{{ $article->title }}</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('admin.nutrition.update', $article->article_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Tiêu đề --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                    <input type="text" name="title"
                           class="form-control form-control-lg @error('title') is-invalid @enderror"
                           value="{{ old('title', $article->title) }}">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Tác giả & Bệnh mục tiêu --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tác giả (Bác sĩ)</label>
                    <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                        <option value="">-- Admin --</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}"
                                    {{ old('doctor_id', $article->doctor_id) == $doctor->doctor_id ? 'selected' : '' }}>
                                {{ $doctor->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Bệnh mục tiêu</label>
                    <input type="text" name="target_disease"
                           class="form-control @error('target_disease') is-invalid @enderror"
                           value="{{ old('target_disease', $article->target_disease) }}"
                           placeholder="Ví dụ: Đái tháo đường">
                    @error('target_disease')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Nội dung CKEditor --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                    <textarea name="content" id="editor"
                              class="form-control @error('content') is-invalid @enderror"
                              rows="12">{{ old('content', $article->content) }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Trạng thái --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="0" {{ old('status', $article->status) == 0 ? 'selected' : '' }}>📝 Nháp</option>
                        <option value="1" {{ old('status', $article->status) == 1 ? 'selected' : '' }}>✅ Xuất bản</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Thông tin thêm --}}
                <div class="col-md-8">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Slug hiện tại:</strong> <code>{{ $article->slug }}</code><br>
                            Slug sẽ tự động cập nhật nếu bạn thay đổi tiêu đề.
                        </small>
                    </div>
                </div>

                {{-- Nút submit --}}
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Cập nhật bài viết
                    </button>
                    <a href="{{ route('admin.nutrition.index') }}" class="btn btn-outline-secondary px-4">Hủy</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
ClassicEditor.create(document.querySelector('#editor'), {
    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'undo', 'redo'],
}).catch(err => console.error(err));
</script>
@endpush
