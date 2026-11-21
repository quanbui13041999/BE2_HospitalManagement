@once
@push('styles')
<style>
    form[data-treatment-reminder-form] textarea[name="message"].is-valid {
        border-color: #198754;
        padding-right: calc(1.5em + .75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-.52.37-1.12.77-.6l1.1 1.43 3.25-3.76c.43-.5 1.18.15.75.65L2.3 6.73z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(.375em + .1875rem) top calc(.375em + .1875rem);
        background-size: calc(.75em + .375rem) calc(.75em + .375rem);
    }

    form[data-treatment-reminder-form] textarea[name="message"].is-invalid {
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form[data-treatment-reminder-form]');

    forms.forEach(function (form) {
        const field = form.querySelector('textarea[name="message"]');
        if (!field) return;

        const messagePattern = /^[\p{L}\p{M}\p{N} .,:;()\/+\-%–—]+$/u;

        function feedbackOf() {
            const wrap = field.closest('.mb-4') || field.parentElement;
            let feedback = wrap.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                field.insertAdjacentElement('afterend', feedback);
            }
            return feedback;
        }

        function messageOf(edgeSpace = false, innerSpace = false, patternMismatch = false) {
            if (edgeSpace) return field.dataset.errorEdgeSpace;
            if (innerSpace) return field.dataset.errorInnerSpace;
            if (patternMismatch) return field.dataset.errorPattern;
            if (field.validity.valueMissing) return field.dataset.errorRequired;
            if (field.validity.tooShort) return field.dataset.errorMinlength;
            if (field.validity.tooLong) return 'Nội dung nhắc nhở tối đa 255 ký tự.';
            return 'Nội dung nhắc nhở không hợp lệ.';
        }

        function validate(force = false) {
            const value = field.value || '';
            const empty = value.length === 0;
            const edgeSpace = value !== value.trim();
            const innerSpace = / {2,}/u.test(value);
            const patternMismatch = !empty && !edgeSpace && !innerSpace && !messagePattern.test(value);
            const feedback = feedbackOf();

            if (!force && empty) {
                field.classList.remove('is-valid', 'is-invalid');
                feedback.textContent = '';
                return true;
            }

            const ok = !edgeSpace && !innerSpace && !patternMismatch && field.checkValidity();

            field.classList.toggle('is-valid', ok && !empty);
            field.classList.toggle('is-invalid', !ok);
            feedback.textContent = ok ? '' : messageOf(edgeSpace, innerSpace, patternMismatch);

            return ok;
        }

        field.addEventListener('input', () => validate());
        field.addEventListener('blur', () => validate(true));

        form.addEventListener('submit', function (event) {
            if (!validate(true)) {
                event.preventDefault();
                field.focus({ preventScroll: true });
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
});
</script>
@endpush
@endonce
