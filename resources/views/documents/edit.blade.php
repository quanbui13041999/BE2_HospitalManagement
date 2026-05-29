@extends('documents.app')

@section('title', 'Chỉnh sửa tài liệu')
@section('breadcrumb', 'Chỉnh sửa thông tin tài liệu')

@section('content')
<div class="edit-wrap">
    <div class="edit-card">

        {{-- Header --}}
        <div class="edit-header">
            <a href="{{ route('documents.index') }}" class="btn-back">← Quay lại</a>
            <h1 class="edit-title">✏️ Chỉnh sửa tài liệu</h1>
        </div>

        {{-- File info (readonly) --}}
        <div class="file-info-box">
            <div class="file-info-icon">
                {{ $document->is_image ? '🖼' : '📄' }}
            </div>
            <div>
                <div class="file-info-name">{{ $document->doc_name }}</div>
                <div class="file-info-meta">
                    {{ $document->formatted_size }}
                    · Tải lên {{ optional($document->uploaded_at)->format('d/m/Y H:i') ?? 'Không rõ' }}
                </div>
            </div>
            <a href="{{ route('documents.show', $document) }}"
               target="_blank" class="btn-preview-link">👁 Xem file</a>
        </div>

        {{-- Edit form --}}
        <form action="{{ route('documents.update', $document) }}"
              method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="document_snapshot" value="{{ $documentSnapshot }}">

            <div class="section-label">Thay ảnh tài liệu</div>
            <div class="form-group">
                <input class="note-input" type="file" id="file" name="file"
                       accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                       onchange="validateReplacementFile(this)">
                <p class="upload-hint">Chỉ chấp nhận ảnh JPG, JPEG hoặc PNG · Tối đa 20MB. Bỏ trống nếu không muốn đổi file.</p>
                <p class="field-error" id="fileClientError" style="display:none"></p>
                @error('file')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-row">
                
                <div class="form-group">
                    <label class="section-label" for="document_date">Ngày tài liệu</label>
                    <input class="note-input" type="date" id="document_date"
                           name="document_date"
                           value="{{ old('document_date', optional($document->uploaded_at)->format('Y-m-d')) }}">
                </div>
            </div>

            {{-- Category --}}
            <div class="section-label">Phân loại tài liệu</div>
            <div class="tag-group" id="category-group">
                @foreach(App\Models\MedicalDocument::categories() as $key => $cat)
                    <button type="button"
                            class="tag {{ old('category', $document->doc_type) === $key ? 'active-blue' : 'gray' }}"
                            data-value="{{ $key }}"
                            onclick="selectTag(this)">
                        {{ $cat['icon'] }} {{ $cat['label'] }}
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="category" id="category-input"
                   value="{{ old('category', $document->doc_type) }}">

            @error('category')
                <p class="field-error">{{ $message }}</p>
            @enderror

            {{-- Actions --}}
            <div class="edit-actions">
                <a href="{{ route('documents.index') }}" class="btn-cancel">Huỷ</a>
                <button type="submit" class="btn-save">💾 Lưu thay đổi</button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
function selectTag(el) {
    document.querySelectorAll('#category-group .tag').forEach(t => t.className = 'tag gray');
    el.className = 'tag active-blue';
    document.getElementById('category-input').value = el.dataset.value;
}

function validateReplacementFile(input) {
    const error = document.getElementById('fileClientError');
    const file = input.files && input.files[0];

    error.style.display = 'none';
    error.textContent = '';

    if (!file) {
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png'];
    const allowedExtensions = ['jpg', 'jpeg', 'png'];
    const extension = file.name.split('.').pop().toLowerCase();
    const maxSize = 20 * 1024 * 1024;

    if (!allowedTypes.includes(file.type) || !allowedExtensions.includes(extension) || file.size > maxSize) {
        input.value = '';
        error.textContent = 'Chỉ được chọn ảnh JPG, JPEG hoặc PNG, tối đa 20MB.';
        error.style.display = 'block';
    }
}
</script>
@endpush
