@extends('layouts.admin')

@section('title', 'Thêm Bài viết mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.news.index') }}" class="text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách
    </a>
    <h4 class="mt-2">Thêm Bài viết mới</h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Nhập tiêu đề...">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nội dung <span class="text-danger">*</span></label>
                        <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" rows="15">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh đại diện (Thumbnail)</label>
                                <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                                <div class="mt-2 text-center" id="image-preview-container" style="display: none;">
                                    <img id="image-preview" src="#" class="img-fluid rounded border" style="max-height: 200px;">
                                </div>
                                @error('thumbnail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_published">Đăng bài ngay</label>
                                </div>
                                <small class="text-muted">Nếu không chọn, bài viết sẽ được lưu dưới dạng nháp.</small>
                            </div>

                            <hr>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Lưu bài viết
                                </button>
                                <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    });

    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        const container = document.getElementById('image-preview-container');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                container.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
