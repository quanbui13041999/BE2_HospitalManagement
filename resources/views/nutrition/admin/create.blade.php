{{-- resources/views/nutrition/admin/create.blade.php --}}
{{-- Form tạo bài viết dinh dưỡng mới --}}

@extends('layouts.nutrition')

@section('title', 'Tạo bài viết dinh dưỡng')

@push('styles')
{{-- CKEditor 5 CDN --}}
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
@endpush

@section('content')

<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.nutrition.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-plus-circle text-success me-2"></i>Tạo bài viết mới</h2>
        <p class="text-muted mb-0">Bài viết sẽ được gợi ý cho bệnh nhân theo tình trạng bệnh</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-4">
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        <form action="{{ route('admin.nutrition.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                {{-- Tiêu đề --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                    <input type="text" name="title"
                           class="form-control form-control-lg @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           required minlength="3" maxlength="150" pattern="^[A-Za-zÀ-ỹ\s]+$"
                           placeholder="Ví dụ: Chế độ ăn uống cho người bệnh tiểu đường">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Tác giả & Bệnh mục tiêu --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tác giả (Bác sĩ)</label>
                    <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                        <option value="">-- Admin (không gắn bác sĩ) --</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}"
                                    {{ old('doctor_id') == $doctor->doctor_id ? 'selected' : '' }}>
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
                           value="{{ old('target_disease') }}"
                           maxlength="120" pattern="^[A-Za-zÀ-ỹ\s]+$"
                           placeholder="Ví dụ: Đái tháo đường">
                    <div class="form-text">Bài viết sẽ được gợi ý cho bệnh nhân có chẩn đoán khớp với từ khóa này.</div>
                    @error('target_disease')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Nội dung --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                    <textarea name="content" id="editor"
                              class="form-control @error('content') is-invalid @enderror"
                              rows="12">{{ old('content') }}</textarea>
                    <div id="content-client-error" class="invalid-feedback d-block" style="display:none !important;"></div>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Trạng thái --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="0" {{ old('status', 0) == 0 ? 'selected' : '' }}>📝 Lưu nháp</option>
                        <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>✅ Xuất bản ngay</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Nút submit --}}
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-save me-1"></i> Lưu bài viết
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
let nutritionEditor;

ClassicEditor.create(document.querySelector('#editor'), {
    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'undo', 'redo'],
    placeholder: 'Nhập nội dung bài viết dinh dưỡng...'
}).then(editor => {
    nutritionEditor = editor;
}).catch(err => console.error(err));

document.querySelector('form').addEventListener('submit', function (event) {
    if (!nutritionEditor) return;

    const text = htmlToText(nutritionEditor.getData());
    const errorBox = document.getElementById('content-client-error');
    errorBox.style.setProperty('display', 'none', 'important');
    errorBox.textContent = '';

    if (text.length < 10) {
        event.preventDefault();
        return showContentError(errorBox, 'Nội dung phải có ít nhất 10 ký tự.');
    }

    if (text.length > 5000) {
        event.preventDefault();
        return showContentError(errorBox, 'Nội dung tối đa 5000 ký tự.');
    }

    if (!/^[\p{L}\s]+$/u.test(text)) {
        event.preventDefault();
        return showContentError(errorBox, 'Nội dung chỉ được nhập chữ và khoảng trắng, không nhập số hoặc ký tự đặc biệt.');
    }

    nutritionEditor.updateSourceElement();
});

function htmlToText(html) {
    const div = document.createElement('div');
    div.innerHTML = html;
    return (div.textContent || div.innerText || '').replace(/\s+/g, ' ').trim();
}

function showContentError(errorBox, message) {
    errorBox.textContent = message;
    errorBox.style.setProperty('display', 'block', 'important');
    document.querySelector('.ck-editor__editable')?.focus();
}
</script>
@endpush
