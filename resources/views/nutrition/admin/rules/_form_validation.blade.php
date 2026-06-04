@once
@push('styles')
<style>
    form[data-nutrition-rule-form] .form-control.is-valid,
    form[data-nutrition-rule-form] .form-select.is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + .75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-.52.37-1.12.77-.6l1.1 1.43 3.25-3.76c.43-.5 1.18.15.75.65L2.3 6.73z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }

    form[data-nutrition-rule-form] .form-control.is-invalid,
    form[data-nutrition-rule-form] .form-select.is-invalid {
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form[data-nutrition-rule-form]');
    const vietnameseWordsPattern = /^[\p{L}\p{M}]+(?: [\p{L}\p{M}]+)*$/u;

    function feedbackOf(field) {
        let feedback = field.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.insertAdjacentElement('afterend', feedback);
        }

        return feedback;
    }

    function messageOf(field, value) {
        const label = field.dataset.vietnameseWords || 'Trường này';

        if (field.validity.valueMissing) {
            return `${label} không được để trống.`;
        }

        if (value !== value.trim()) {
            return `${label} không được có khoảng trắng ở đầu hoặc cuối.`;
        }

        if (/ {2,}/u.test(value)) {
            return `${label} không được có 2 khoảng trắng liên tiếp.`;
        }

        if (!vietnameseWordsPattern.test(value)) {
            return `${label} chỉ được nhập chữ tiếng Việt, không nhập số hoặc ký tự đặc biệt.`;
        }

        if (field.validity.tooShort) {
            return `${label} phải có ít nhất ${field.minLength} ký tự.`;
        }

        if (field.validity.tooLong) {
            return `${label} không được vượt quá ${field.maxLength} ký tự.`;
        }

        return `${label} không hợp lệ.`;
    }

    function validateVietnameseWords(field, force = false) {
        const value = field.value || '';
        const optionalEmpty = !field.required && value.length === 0;
        const feedback = feedbackOf(field);

        if (!force && value.length === 0) {
            field.classList.remove('is-valid', 'is-invalid');
            feedback.textContent = '';
            return true;
        }

        if (optionalEmpty) {
            field.classList.remove('is-valid', 'is-invalid');
            feedback.textContent = '';
            return true;
        }

        const invalidSpacing = value !== value.trim() || / {2,}/u.test(value);
        const invalidText = !vietnameseWordsPattern.test(value);
        const ok = field.checkValidity() && !invalidSpacing && !invalidText;

        field.classList.toggle('is-valid', ok && value.length > 0);
        field.classList.toggle('is-invalid', !ok);
        feedback.textContent = ok ? '' : messageOf(field, value);

        return ok;
    }

    function labelOf(field) {
        const label = field.id ? field.form?.querySelector(`label[for="${field.id}"]`) : null;
        return (label?.textContent || 'Trường này').replace('*', '').trim();
    }

    function validateRequiredField(field, force = false) {
        const feedback = feedbackOf(field);

        if (!force && field.value !== '') {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            feedback.textContent = '';
            return true;
        }

        const ok = field.checkValidity();
        field.classList.toggle('is-valid', ok && field.value !== '');
        field.classList.toggle('is-invalid', !ok);
        feedback.textContent = ok ? '' : `Vui lòng chọn ${labelOf(field).toLowerCase()}.`;

        return ok;
    }

    forms.forEach(function (form) {
        const textFields = form.querySelectorAll('[data-vietnamese-words]');
        const requiredFields = form.querySelectorAll('select[required]');

        textFields.forEach(function (field) {
            field.addEventListener('input', () => validateVietnameseWords(field));
            field.addEventListener('blur', () => validateVietnameseWords(field, true));
        });

        requiredFields.forEach(function (field) {
            field.addEventListener('change', () => validateRequiredField(field));
            field.addEventListener('blur', () => validateRequiredField(field, true));
        });

        form.addEventListener('submit', function (event) {
            let firstInvalid = null;

            textFields.forEach(function (field) {
                if (!validateVietnameseWords(field, true) && !firstInvalid) {
                    firstInvalid = field;
                }
            });

            requiredFields.forEach(function (field) {
                if (!validateRequiredField(field, true) && !firstInvalid) {
                    firstInvalid = field;
                }
            });

            if (firstInvalid) {
                event.preventDefault();
                firstInvalid.focus({ preventScroll: true });
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
});
</script>
@endpush
@endonce
