document.addEventListener('DOMContentLoaded', function () {
    const heightInput = document.querySelector('input[name="height"]');
    const weightInput = document.querySelector('input[name="weight"]');
    const bmiValue = document.getElementById('bmi-value');
    const bmiStatus = document.getElementById('bmi-status');

    if (!heightInput || !weightInput || !bmiValue || !bmiStatus) {
        return;
    }

    function normalizePositiveNumber(input) {
        const value = parseFloat(input.value);

        if (Number.isFinite(value) && value < 0) {
            input.value = '';
        }
    }

    function calculateBMI() {
        normalizePositiveNumber(heightInput);
        normalizePositiveNumber(weightInput);

        const height = parseFloat(heightInput.value) / 100;
        const weight = parseFloat(weightInput.value);

        if (height > 0 && weight > 0) {
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

    heightInput.addEventListener('input', calculateBMI);
    weightInput.addEventListener('input', calculateBMI);
});
