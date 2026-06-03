document.addEventListener('DOMContentLoaded', function () {
    const heightInput = document.querySelector('input[name="height"]');
    const weightInput = document.querySelector('input[name="weight"]');
    const bmiValue = document.getElementById('bmi-value');
    const bmiStatus = document.getElementById('bmi-status');

    if (!heightInput || !weightInput || !bmiValue || !bmiStatus) {
        return;
    }

    function showNumberError(input, message) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');

        let feedback = input.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.after(feedback);
        }

        feedback.textContent = message;
    }

    function clearNumberError(input) {
        input.classList.remove('is-invalid');
        if (input.value.trim() !== '') {
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
        }

        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
    }

    function normalizePositiveNumber(input) {
        const original = input.value;
        let normalized = original.replace(',', '.').replace(/[^\d.]/g, '');
        const firstDot = normalized.indexOf('.');

        if (firstDot !== -1) {
            normalized = normalized.slice(0, firstDot + 1) + normalized.slice(firstDot + 1).replace(/\./g, '');
        }

        if (normalized.startsWith('.')) {
            normalized = '';
        }

        if (normalized !== '' && !/^\d+(\.\d{0,2})?$/.test(normalized)) {
            normalized = normalized.slice(0, -1);
        }

        if (normalized !== original) {
            input.value = normalized;
            showNumberError(input, 'Ô này chỉ được nhập số dương, tối đa 2 chữ số thập phân.');
            return false;
        }

        const value = parseFloat(input.value);
        const min = Number(input.getAttribute('min') || 0);

        if (Number.isFinite(value) && min && value < min) {
            showNumberError(input, `Giá trị không được nhỏ hơn ${min}.`);
            return false;
        }

        const max = Number(input.getAttribute('max') || 0);
        if (max && Number.isFinite(value) && value > max) {
            showNumberError(input, `Giá trị không được lớn hơn ${max}.`);
            return false;
        }

        clearNumberError(input);
        return true;
    }

    function calculateBMI() {
        const heightValid = normalizePositiveNumber(heightInput);
        const weightValid = normalizePositiveNumber(weightInput);

        const height = parseFloat(heightInput.value) / 100;
        const weight = parseFloat(weightInput.value);

        if (heightValid && weightValid && height >= 0.3 && weight >= 1) {
            const bmi = (weight / (height * height)).toFixed(2);
            bmiValue.innerText = bmi;

            if (bmi < 18.5) {
                bmiStatus.innerText = ' - Gầy';
                bmiStatus.className = 'text-warning';
            } else if (bmi < 24.9) {
                bmiStatus.innerText = ' - Bình thường';
                bmiStatus.className = 'text-success';
            } else {
                bmiStatus.innerText = ' - Thừa cân';
                bmiStatus.className = 'text-danger';
            }
        } else {
            bmiValue.innerText = '0';
            bmiStatus.innerText = ' - Chưa đủ dữ liệu';
            bmiStatus.className = 'text-muted';
        }
    }

    ['keydown', 'paste'].forEach(eventName => {
        [heightInput, weightInput].forEach(input => {
            input.addEventListener(eventName, function (event) {
                if (eventName === 'keydown') {
                    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];
                    if (allowedKeys.includes(event.key) || event.ctrlKey || event.metaKey) {
                        return;
                    }

                    if ((event.key === '.' || event.key === ',') && input.value.trim() === '') {
                        event.preventDefault();
                        showNumberError(input, 'Phải nhập số trước dấu thập phân.');
                        return;
                    }

                    if (!/[\d.,]/.test(event.key)) {
                        event.preventDefault();
                        showNumberError(input, 'Không được nhập chữ hoặc ký tự đặc biệt vào ô số.');
                    }
                }

                if (eventName === 'paste') {
                    setTimeout(() => calculateBMI(), 0);
                }
            });
        });
    });

    heightInput.addEventListener('input', calculateBMI);
    weightInput.addEventListener('input', calculateBMI);

    const vietnameseWordInputs = document.querySelectorAll('.js-vietnamese-words');
    const vietnameseWordPattern = /^[\p{L}\p{M}]+(?: [\p{L}\p{M}]+)*$/u;
    const vietnameseWordMessage = 'Chỉ nhập chữ tiếng Việt và đúng một khoảng trắng giữa các từ, không nhập số hoặc ký tự lạ.';

    function normalizeVietnameseWords(input) {
        const original = input.value;
        const normalized = original
            .replace(/\s+/gu, ' ')
            .replace(/^\s+/u, '');

        if (normalized !== original) {
            input.value = normalized;
        }

        const trimmed = input.value.trimEnd();
        const isValid = trimmed === '' || vietnameseWordPattern.test(trimmed);
        input.setCustomValidity(isValid ? '' : vietnameseWordMessage);
        input.classList.toggle('is-invalid', !isValid);

        if (isValid && input.value.trim() !== '') {
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
        }
    }

    vietnameseWordInputs.forEach(input => {
        input.addEventListener('keydown', function (event) {
            const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End', 'Enter'];
            if (allowedKeys.includes(event.key) || event.ctrlKey || event.metaKey) {
                return;
            }

            if (event.key === ' ') {
                const cursor = input.selectionStart ?? input.value.length;
                if (cursor === 0 || input.value[cursor - 1] === ' ' || input.value[cursor] === ' ') {
                    event.preventDefault();
                    input.setCustomValidity(vietnameseWordMessage);
                }
                return;
            }

            if (!/^[\p{L}\p{M}]$/u.test(event.key)) {
                event.preventDefault();
                input.setCustomValidity(vietnameseWordMessage);
            }
        });

        input.addEventListener('input', () => normalizeVietnameseWords(input));
        input.addEventListener('blur', function () {
            input.value = input.value.trim().replace(/\s+/gu, ' ');
            normalizeVietnameseWords(input);
        });
        input.addEventListener('paste', () => setTimeout(() => normalizeVietnameseWords(input), 0));
        normalizeVietnameseWords(input);
    });

    const healthForm = heightInput.closest('form');
    healthForm?.addEventListener('submit', function (event) {
        for (const input of vietnameseWordInputs) {
            input.value = input.value.trim().replace(/\s+/gu, ' ');
            normalizeVietnameseWords(input);

            if (input.validationMessage) {
                event.preventDefault();
                input.focus();
                input.reportValidity();
                break;
            }
        }
    });

    calculateBMI();
});
