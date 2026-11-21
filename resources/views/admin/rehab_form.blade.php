@extends('layouts.admin')

@section('title', $isEdit ? 'Sửa bài tập phục hồi' : 'Tạo bài tập phục hồi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $isEdit ? 'Sửa bài tập' : 'Tạo bài tập' }}</h4>
    <a href="{{ route('admin.rehab.index') }}" class="btn btn-outline-secondary">Quay lại</a>
</div>

<form method="POST"
      action="{{ $isEdit ? route('admin.rehab.update', $exercise) : route('admin.rehab.store') }}"
      enctype="multipart/form-data"
      class="card p-4"
      data-rehab-form
      novalidate>
    @csrf
    @if($isEdit)
        @method('PUT')
        <input type="hidden" name="rehab_snapshot" value="{{ $rehabSnapshot }}">
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="mb-3">
        <label class="form-label">Tiêu đề</label>
        <input name="title"
               class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $exercise->title) }}"
               required
               minlength="3"
               maxlength="120"
               pattern="^[A-Za-zÀ-ỹ]+( [A-Za-zÀ-ỹ]+)*$"
               data-error-required="Vui lòng nhập tiêu đề bài tập."
               data-error-pattern="Tiêu đề chỉ được nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ."
               data-error-minlength="Tiêu đề phải có ít nhất 3 ký tự.">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Nhóm bệnh lý</label>
            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" @selected(old('category', $exercise->category) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Giai đoạn</label>
            <select name="phase" class="form-select @error('phase') is-invalid @enderror" required>
                @foreach($phases as $value => $label)
                    <option value="{{ $value }}" @selected(old('phase', $exercise->phase) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('phase')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="draft" @selected(old('status', $exercise->status) === 'draft')>Bản nháp</option>
                <option value="published" @selected(old('status', $exercise->status) === 'published')>Công khai</option>
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Thời lượng tập (phút)</label>
            <input type="number"
                   name="duration_minutes"
                   min="1"
                   max="240"
                   step="1"
                   inputmode="numeric"
                   class="form-control @error('duration_minutes') is-invalid @enderror"
                   value="{{ old('duration_minutes', $exercise->duration_minutes) }}"
                   data-error-min="Thời lượng tập phải lớn hơn 0."
                   data-error-max="Thời lượng tập tối đa 240 phút.">
            @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Ảnh đại diện</label>
            <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Nội dung hướng dẫn</label>
        <textarea name="content"
                  rows="10"
                  class="form-control @error('content') is-invalid @enderror"
                  required
                  minlength="10"
                  maxlength="5000"
                  data-pattern="^[A-Za-zÀ-ỹ]+( [A-Za-zÀ-ỹ]+)*$"
                  data-error-required="Vui lòng nhập nội dung hướng dẫn."
                  data-error-pattern="Nội dung hướng dẫn chỉ được nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ."
                  data-error-minlength="Nội dung hướng dẫn phải có ít nhất 10 ký tự.">{{ old('content', $exercise->content) }}</textarea>
        <div class="form-text">Tối đa 5000 ký tự. Chỉ nhập chữ tiếng Việt và một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ.</div>
        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.rehab.index') }}" class="btn btn-outline-secondary">Hủy</a>
        <button class="btn btn-primary">{{ $isEdit ? 'Cập nhật' : 'Lưu bài tập' }}</button>
    </div>
</form>
@endsection

@push('styles')
<style>
    form[data-rehab-form] .form-control.is-valid,
    form[data-rehab-form] .form-select.is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + .75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-.52.37-1.12.77-.6l1.1 1.43 3.25-3.76c.43-.5 1.18.15.75.65L2.3 6.73z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }

    form[data-rehab-form] .form-control.is-invalid,
    form[data-rehab-form] .form-select.is-invalid {
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[data-rehab-form]');
    if (!form) return;

    const fields = Array.from(form.querySelectorAll('input:not([type="hidden"]), textarea, select'));

    function labelOf(field) {
        return field.closest('.mb-3')?.querySelector('label')?.textContent.trim() || field.name || 'Trường này';
    }

    function feedbackOf(field) {
        const wrap = field.closest('.mb-3') || field.parentElement;
        let feedback = wrap.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.insertAdjacentElement('afterend', feedback);
        }
        return feedback;
    }

    function customError(field) {
        const value = String(field.value || '');
        if (!value || !field.dataset.pattern) return '';

        const regex = new RegExp(field.dataset.pattern, 'u');
        return regex.test(value) ? '' : (field.dataset.errorPattern || `${labelOf(field)} không đúng định dạng.`);
    }

    function messageOf(field, custom = '') {
        const v = field.validity;
        const label = labelOf(field);

        if (custom) return custom;
        if (v.valueMissing) return field.dataset.errorRequired || `${label} không được bỏ trống.`;
        if (v.patternMismatch) return field.dataset.errorPattern || `${label} không đúng định dạng.`;
        if (v.tooShort) return field.dataset.errorMinlength || `${label} chưa đủ số ký tự tối thiểu.`;
        if (v.tooLong) return `${label} vượt quá số ký tự cho phép.`;
        if (v.rangeUnderflow) return field.dataset.errorMin || `${label} phải lớn hơn hoặc bằng ${field.min}.`;
        if (v.rangeOverflow) return field.dataset.errorMax || `${label} phải nhỏ hơn hoặc bằng ${field.max}.`;
        if (v.badInput) return `${label} không hợp lệ.`;

        return `${label} không hợp lệ.`;
    }

    function validate(field, force = false) {
        if (field.disabled) return true;

        const feedback = feedbackOf(field);
        const empty = String(field.value || '').length === 0;
        if (!force && empty && !field.required) {
            field.classList.remove('is-valid', 'is-invalid');
            feedback.textContent = '';
            return true;
        }

        const custom = customError(field);
        const ok = !custom && field.checkValidity();
        field.classList.toggle('is-valid', ok && (!empty || field.required));
        field.classList.toggle('is-invalid', !ok);
        feedback.textContent = ok ? '' : messageOf(field, custom);
        return ok;
    }

    fields.forEach(field => {
        field.addEventListener('input', () => validate(field));
        field.addEventListener('change', () => validate(field, true));
        field.addEventListener('blur', () => validate(field, true));
    });

    form.addEventListener('submit', function (event) {
        const invalid = fields.filter(field => !validate(field, true));
        if (invalid.length > 0) {
            event.preventDefault();
            invalid[0].focus({ preventScroll: true });
            invalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>
@endpush
