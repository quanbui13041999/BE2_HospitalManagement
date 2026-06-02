{{-- resources/views/nutrition/admin/edit.blade.php --}}

@extends('layouts.nutrition')

@section('title', 'Chỉnh sửa bài viết')

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
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        <form action="{{ route('admin.nutrition.update', $article->article_id) }}" method="POST" data-nutrition-article-form novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="article_snapshot" value="{{ $articleSnapshot }}">

            <div class="row g-4">
                <div class="col-12">
                    <label for="title" class="form-label fw-semibold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                    <input type="text"
                           name="title"
                           id="title"
                           class="form-control form-control-lg @error('title') is-invalid @enderror"
                           value="{{ old('title', $article->title) }}"
                           required
                           minlength="3"
                           maxlength="150"
                           pattern="^[A-Za-zÀ-ỹ]+( [A-Za-zÀ-ỹ]+)*$"
                           data-text-only="1"
                           data-error-required="Vui lòng nhập tiêu đề bài viết."
                           data-error-pattern="Tiêu đề chỉ được nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ."
                           data-error-minlength="Tiêu đề phải có ít nhất 3 ký tự.">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="doctor_id" class="form-label fw-semibold">Tác giả (Bác sĩ)</label>
                    <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                        <option value="">-- Admin --</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}" {{ old('doctor_id', $article->doctor_id) == $doctor->doctor_id ? 'selected' : '' }}>
                                {{ $doctor->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="target_disease" class="form-label fw-semibold">Bệnh mục tiêu</label>
                    <input type="text"
                           name="target_disease"
                           id="target_disease"
                           class="form-control @error('target_disease') is-invalid @enderror"
                           value="{{ old('target_disease', $article->target_disease) }}"
                           maxlength="120"
                           pattern="^[A-Za-zÀ-ỹ]+( [A-Za-zÀ-ỹ]+)*$"
                           data-text-only="1"
                           data-error-pattern="Tên bệnh chỉ được nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ."
                           placeholder="Ví dụ: Đái tháo đường">
                    @error('target_disease')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="editor" class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                    <textarea name="content"
                              id="editor"
                              class="form-control @error('content') is-invalid @enderror"
                              rows="12"
                              required
                              minlength="10"
                              maxlength="5000"
                              data-error-required="Vui lòng nhập nội dung bài viết."
                              data-error-pattern="Nội dung chỉ được nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ."
                              data-error-minlength="Nội dung phải có ít nhất 10 ký tự.">{{ old('content', $article->content) }}</textarea>
                    <div id="content-client-error" class="invalid-feedback d-block" style="display:none !important;"></div>
                    @error('content')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="0" {{ old('status', $article->status) == 0 ? 'selected' : '' }}>Nháp</option>
                        <option value="1" {{ old('status', $article->status) == 1 ? 'selected' : '' }}>Xuất bản</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-8">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Slug hiện tại:</strong> <code>{{ $article->slug }}</code><br>
                            Slug sẽ tự động cập nhật nếu bạn thay đổi tiêu đề.
                        </small>
                    </div>
                </div>

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
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
ClassicEditor.create(document.querySelector('#editor'), {
    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'undo', 'redo'],
}).then(editor => {
    window.nutritionArticleEditor = editor;
}).catch(err => console.error(err));
</script>
@endpush

@include('nutrition.admin._article_form_validation')
