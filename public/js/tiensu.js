document.addEventListener('DOMContentLoaded', function () {
    const heightInput = document.querySelector('input[name="height"]');
    const weightInput = document.querySelector('input[name="weight"]');
    const bmiValue = document.getElementById('bmi-value');
    const bmiStatus = document.getElementById('bmi-status');

    function calculateBMI() {
        const height = parseFloat(heightInput.value) / 100; // Đổi cm sang m
        const weight = parseFloat(weightInput.value);

        if (height > 0 && weight > 0) {
            const bmi = (weight / (height * height)).toFixed(2);
            bmiValue.innerText = bmi;

            // Hiển thị trạng thái sức khỏe
            if (bmi < 18.5) {
                bmiStatus.innerText = " — Gầy";
                bmiStatus.className = "text-warning";
            } else if (bmi < 24.9) {
                bmiStatus.innerText = " — Bình thường";
                bmiStatus.className = "text-success";
            } else {
                bmiStatus.innerText = " — Thừa cân";
                bmiStatus.className = "text-danger";
            }
        } else {
            bmiValue.innerText = "0";
            bmiStatus.innerText = " — Chưa đủ dữ liệu";
        }
    }

    heightInput.addEventListener('input', calculateBMI);
    weightInput.addEventListener('input', calculateBMI);
});