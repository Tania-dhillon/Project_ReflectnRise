
// Check-in 
// - dashboard mood chart
// - multi-step check-in navigation
// - mood selection state
// - range slider labels

document.addEventListener('DOMContentLoaded', function () {
    // Dashboard mood trend chart
    const chartCanvas = document.getElementById('moodTrendChart');
    if (chartCanvas && window.Chart) {
        new Chart(chartCanvas, {
            type: 'line',
            data: {
                labels: window.moodTrendLabels || [],
                datasets: [{
                    data: window.moodTrendValues || [],
                    tension: 0.4,
                    borderWidth: 3,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        min: 1,
                        max: 5,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // Multi-step check-in form
    const form = document.getElementById('checkinForm');
    if (!form) return;

    const steps = Array.from(document.querySelectorAll('.checkin-step'));
    const segments = Array.from(document.querySelectorAll('.progress-segment'));
    const nextBtn = document.getElementById('nextStepBtn');
    const prevBtn = document.getElementById('prevStepBtn');
    const submitBtn = document.getElementById('submitStepBtn');
    const moodButtons = Array.from(document.querySelectorAll('.mood-select-card'));
    const moodLabelInput = document.getElementById('moodLabelInput');
    const moodRatingInput = document.getElementById('moodRatingInput');

    let currentStep = 1;

    function showStep(step) {
        currentStep = step;

        steps.forEach((item) => {
            item.classList.toggle('active', Number(item.dataset.step) === step);
        });

        segments.forEach((seg, index) => {
            seg.classList.toggle('active', index < step);
        });

        prevBtn.style.visibility = step === 1 ? 'hidden' : 'visible';
        nextBtn.classList.toggle('d-none', step === 4);
        submitBtn.classList.toggle('d-none', step !== 4);
    }

    function validateCurrentStep() {
        if (currentStep === 1 && (!moodLabelInput.value || Number(moodRatingInput.value) < 1)) {
            alert('Please select how you are feeling.');
            return false;
        }
        return true;
    }

    nextBtn?.addEventListener('click', function () {
        if (!validateCurrentStep()) return;
        if (currentStep < 4) {
            showStep(currentStep + 1);
        }
    });

    prevBtn?.addEventListener('click', function () {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    });

    moodButtons.forEach((btn) => {
        btn.addEventListener('click', function () {
            moodButtons.forEach((b) => b.classList.remove('selected'));
            this.classList.add('selected');
            moodLabelInput.value = this.dataset.label;
            moodRatingInput.value = this.dataset.rating;
        });
    });

    function levelText(value) {
        const map = {
            1: 'Very Low',
            2: 'Low',
            3: 'Moderate',
            4: 'Good',
            5: 'High'
        };
        return map[value] || 'Moderate';
    }

    ['energyLevel', 'stressLevel', 'sleepQuality'].forEach((id) => {
        const input = document.getElementById(id);
        const label = document.getElementById(id + 'Text');
        if (!input || !label) return;

        const update = () => {
            label.textContent = levelText(input.value);
        };
        input.addEventListener('input', update);
        update();
    });

    showStep(1);
});
