@once
@push('styles')
<style>
    form[data-device-form] .form-control.is-valid,
    form[data-device-form] .form-select.is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + .75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-.52.37-1.12.77-.6l1.1 1.43 3.25-3.76c.43-.5 1.18.15.75.65L2.3 6.73z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }

    form[data-device-form] .form-control.is-invalid,
    form[data-device-form] .form-select.is-invalid {
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form[data-device-form]');
    const selector = 'input:not([type="hidden"]), select, textarea';

    function labelOf(field) {
        const wrapper = field.closest('.mb-3, .col-md-3, .col-md-4, .col-md-5, .col-md-7, .col-md-12');
        return wrapper?.querySelector('label')?.textContent.replace('*', '').trim() || field.name || 'Trường này';
    }

    function feedbackOf(field) {
        const wrapper = field.closest('.mb-3, .col-md-3, .col-md-4, .col-md-5, .col-md-7, .col-md-12') || field.parentElement;
        let feedback = wrapper.querySelector('.invalid-feedback');

        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.insertAdjacentElement('afterend', feedback);
        }

        return feedback;
    }

    function customError(field) {
        const value = String(field.value || '');

        if (!value) return '';

        if (field.dataset.noEdgeSpace === '1' && (/^\s|\s$/u).test(value)) {
            return field.dataset.errorEdgeSpace || `${labelOf(field)} không được có khoảng trắng ở đầu hoặc cuối.`;
        }

        if (field.dataset.pattern) {
            const regex = new RegExp(field.dataset.pattern, 'u');
            if (!regex.test(value)) {
                return field.dataset.errorPattern || field.title || `${labelOf(field)} không đúng định dạng.`;
            }
        }

        return '';
    }

    function messageOf(field, custom = '') {
        const label = labelOf(field);
        const v = field.validity;

        if (custom) return custom;
        if (v.valueMissing) return field.dataset.errorRequired || `${label} không được bỏ trống.`;
        if (v.patternMismatch) return field.dataset.errorPattern || field.title || `${label} không đúng định dạng.`;
        if (v.tooShort) return field.dataset.errorMinlength || `${label} chưa đủ số ký tự tối thiểu.`;
        if (v.tooLong) return field.dataset.errorMaxlength || `${label} vượt quá số ký tự cho phép.`;
        if (v.rangeUnderflow) return field.dataset.errorMin || `${label} phải lớn hơn hoặc bằng ${field.min}.`;
        if (v.rangeOverflow) return field.dataset.errorMax || `${label} phải nhỏ hơn hoặc bằng ${field.max}.`;
        if (v.badInput) return field.dataset.errorInput || `${label} không hợp lệ.`;

        return field.dataset.errorInvalid || `${label} không hợp lệ.`;
    }

    function validateField(field, force = false) {
        if (!field || field.disabled) return true;
        const feedback = feedbackOf(field);
        const empty = String(field.value || '').length === 0;

        if (!force && empty && !field.required) {
            field.classList.remove('is-valid', 'is-invalid');
            feedback.textContent = '';
            return true;
        }

        const custom = customError(field);
        const valid = !custom && field.checkValidity();
        field.classList.toggle('is-valid', valid && (!empty || field.required));
        field.classList.toggle('is-invalid', !valid);
        feedback.textContent = valid ? '' : messageOf(field, custom);

        return valid;
    }

    forms.forEach(function (form) {
        form.noValidate = true;

        form.querySelectorAll(selector).forEach(function (field) {
            field.addEventListener('input', () => {
                if (field.dataset.uppercase === '1') {
                    const pos = field.selectionStart;
                    field.value = field.value.toUpperCase();
                    if (pos !== null) field.setSelectionRange(pos, pos);
                }
                validateField(field);
            });
            field.addEventListener('change', () => validateField(field, true));
            field.addEventListener('blur', () => validateField(field, true));
        });

        form.addEventListener('submit', function (event) {
            const fields = Array.from(form.querySelectorAll(selector));
            const invalid = fields.filter(field => !validateField(field, true));

            if (invalid.length > 0) {
                event.preventDefault();
                invalid[0].focus({ preventScroll: true });
                invalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
});
</script>
@endpush
@endonce
