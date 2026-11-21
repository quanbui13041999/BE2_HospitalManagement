@once
@push('styles')
<style>
    form[data-food-form] .form-control.is-valid,
    form[data-food-form] .form-select.is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + .75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-.52.37-1.12.77-.6l1.1 1.43 3.25-3.76c.43-.5 1.18.15.75.65L2.3 6.73z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) center;
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }

    form[data-food-form] .form-control.is-invalid,
    form[data-food-form] .form-select.is-invalid {
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[data-food-form]');
    if (!form) return;

    const fields = Array.from(form.querySelectorAll('input:not([type="hidden"]), textarea, select'));

    function labelOf(field) {
        return field.closest('.col-md-8, .col-md-4, .col-md-6, .col-12')?.querySelector('label')?.textContent.replace('*', '').trim()
            || field.name
            || 'Trường này';
    }

    function feedbackOf(field) {
        const wrap = field.closest('.col-md-8, .col-md-4, .col-md-6, .col-12') || field.parentElement;
        let feedback = wrap.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            (field.closest('.input-group') || field).insertAdjacentElement('afterend', feedback);
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
        if (v.stepMismatch) return `${label} phải là số nguyên.`;
        if (v.badInput) return field.dataset.errorInput || `${label} chỉ được nhập số.`;

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
@endonce
