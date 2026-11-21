@extends('layouts.admin')

@section('title', 'Chỉnh sửa Bài viết')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.news.index') }}" class="text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách
    </a>
    <h4 class="mt-2">Chỉnh sửa Bài viết</h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.news.update', $article->news_id) }}" method="POST" enctype="multipart/form-data" data-disable-submit>
            @csrf
            @method('PUT')
            <input type="hidden" name="version" value="{{ $article->news_version }}"> {{-- fixed: phat hien form cu khi nhieu admin cung sua --}}
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $article->title) }}" maxlength="255" placeholder="Nhập tiêu đề...">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nội dung <span class="text-danger">*</span></label>
                        <textarea name="content" id="editor" class="form-control @error('content') is-invalid @enderror" rows="15" maxlength="10000">{{ old('content', $article->content) }}</textarea> {{-- fixed: chan noi dung qua dai o giao dien --}}
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
                                        <option value="{{ $cat }}" {{ old('category', $article->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh đại diện (Thumbnail)</label>
                                <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                                <div class="mt-2 text-center" id="image-preview-container">
                                    <img id="image-preview" src="{{ $article->thumbnail_url }}" class="img-fluid rounded border" style="max-height: 200px;">
                                </div>
                                @error('thumbnail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_published">Đã đăng</label>
                                </div>
                                @if($article->published_at)
                                <small class="text-muted d-block mt-1">Đã đăng lần đầu: {{ $article->published_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>

                            <hr>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Cập nhật bài viết
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
        setup: function (editor) {
            editor.on('change keyup', function () {
                var content = editor.getContent();
                if (content.length > 10000 && window.showAppNotification) {
                    window.showAppNotification('Nội dung bài viết tối đa 10000 ký tự. Vui lòng rút ngắn nội dung.', 'warning');
                }
            });
        },
    });

    document.querySelector('form').addEventListener('submit', function (event) {
        var editor = tinymce.get('editor');
        if (editor && editor.getContent().length > 10000) {
            event.preventDefault();
            window.showAppNotification('Nội dung bài viết tối đa 10000 ký tự. Vui lòng rút ngắn nội dung.', 'warning'); /* fixed: bao loi tren man hinh thay vi gui form qua dai */
        }
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
