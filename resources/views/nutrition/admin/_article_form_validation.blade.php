@once
@push('styles')
<style>
    form[data-nutrition-article-form] .form-control.is-valid,
    form[data-nutrition-article-form] .form-select.is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + .75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-.52.37-1.12.77-.6l1.1 1.43 3.25-3.76c.43-.5 1.18.15.75.65L2.3 6.73z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }

    form[data-nutrition-article-form] .form-control.is-invalid,
    form[data-nutrition-article-form] .form-select.is-invalid {
        border-color: #dc3545;
    }

    form[data-nutrition-article-form] .ck-editor__editable.is-valid {
        border-color: #198754 !important;
        box-shadow: 0 0 0 .25rem rgba(25, 135, 84, .15) !important;
    }

    form[data-nutrition-article-form] .ck-editor__editable.is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 .25rem rgba(220, 53, 69, .18) !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[data-nutrition-article-form]');
    if (!form) return;

    const textRegex = /^[\p{L}\p{M}]+(?: [\p{L}\p{M}]+)*$/u;
    const fields = Array.from(form.querySelectorAll('input:not([type="hidden"]), select'));

    function labelOf(field) {
        return field.closest('.col-md-8, .col-md-6, .col-md-4, .col-12')?.querySelector('label')?.textContent.replace('*', '').trim()
            || field.name
            || 'Trường này';
    }

    function feedbackOf(field) {
        const wrap = field.closest('.col-md-8, .col-md-6, .col-md-4, .col-12') || field.parentElement;
        let feedback = wrap.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            field.insertAdjacentElement('afterend', feedback);
        }
        return feedback;
    }

    function messageOf(field, custom = '') {
        const v = field.validity;
        const label = labelOf(field);

        if (custom) return custom;
        if (v.valueMissing) return field.dataset.errorRequired || `${label} không được bỏ trống.`;
        if (v.patternMismatch) return field.dataset.errorPattern || `${label} không đúng định dạng.`;
        if (v.tooShort) return field.dataset.errorMinlength || `${label} chưa đủ số ký tự tối thiểu.`;
        if (v.tooLong) return `${label} vượt quá số ký tự cho phép.`;

        return `${label} không hợp lệ.`;
    }

    function validateField(field, force = false) {
        if (field.disabled) return true;

        const value = String(field.value || '');
        const empty = value.length === 0;
        const feedback = feedbackOf(field);

        if (!force && empty && !field.required) {
            field.classList.remove('is-valid', 'is-invalid');
            feedback.textContent = '';
            return true;
        }

        const custom = field.dataset.textOnly === '1' && !empty && !textRegex.test(value)
            ? field.dataset.errorPattern
            : '';
        const ok = !custom && field.checkValidity();

        field.classList.toggle('is-valid', ok && (!empty || field.required));
        field.classList.toggle('is-invalid', !ok);
        feedback.textContent = ok ? '' : messageOf(field, custom);

        return ok;
    }

    function htmlToText(html) {
        const div = document.createElement('div');
        div.innerHTML = html;
        return (div.textContent || div.innerText || '').replace(/\u00a0/g, ' ');
    }

    function contentFeedback() {
        return document.getElementById('content-client-error');
    }

    function editorEditable() {
        return form.querySelector('.ck-editor__editable');
    }

    function validateContent(force = false) {
        const editor = window.nutritionArticleEditor;
        const textarea = form.querySelector('#editor');
        const feedback = contentFeedback();
        const editable = editorEditable();
        if (!editor || !textarea || !feedback) return true;

        const value = htmlToText(editor.getData());
        const empty = value.length === 0;
        let message = '';

        if (!force && empty) {
            textarea.classList.remove('is-valid', 'is-invalid');
            editable?.classList.remove('is-valid', 'is-invalid');
            feedback.style.setProperty('display', 'none', 'important');
            feedback.textContent = '';
            return true;
        }

        if (empty) {
            message = textarea.dataset.errorRequired;
        } else if (value.length < 10) {
            message = textarea.dataset.errorMinlength;
        } else if (value.length > 5000) {
            message = 'Nội dung tối đa 5000 ký tự.';
        } else if (!textRegex.test(value)) {
            message = textarea.dataset.errorPattern;
        }

        const ok = message === '';
        textarea.classList.toggle('is-valid', ok);
        textarea.classList.toggle('is-invalid', !ok);
        editable?.classList.toggle('is-valid', ok);
        editable?.classList.toggle('is-invalid', !ok);
        feedback.textContent = message;
        feedback.style.setProperty('display', ok ? 'none' : 'block', 'important');

        return ok;
    }

    fields.forEach(field => {
        field.addEventListener('input', () => validateField(field));
        field.addEventListener('change', () => validateField(field, true));
        field.addEventListener('blur', () => validateField(field, true));
    });

    const waitEditor = setInterval(function () {
        const editor = window.nutritionArticleEditor;
        if (!editor) return;

        clearInterval(waitEditor);
        editor.model.document.on('change:data', () => validateContent());
        editor.editing.view.document.on('blur', () => validateContent(true));
    }, 100);

    form.addEventListener('submit', function (event) {
        const invalidFields = fields.filter(field => !validateField(field, true));
        const contentOk = validateContent(true);

        if (invalidFields.length > 0 || !contentOk) {
            event.preventDefault();
            const first = invalidFields[0] || editorEditable();
            first?.focus({ preventScroll: true });
            first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        window.nutritionArticleEditor?.updateSourceElement();
    });
});
</script>
@endpush
@endonce
