// Insights module charts

document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const lineCanvas = document.getElementById('insightsLineChart');
    if (lineCanvas) {
        new Chart(lineCanvas, {
            type: 'line',
            data: {
                labels: window.insightsLineLabels || [],
                datasets: [
                    {
                        label: 'Mood',
                        data: window.insightsMoodValues || [],
                        tension: 0.4,
                        borderWidth: 2,
                        fill: true
                    },
                    {
                        label: 'Energy',
                        data: window.insightsEnergyValues || [],
                        tension: 0.4,
                        borderWidth: 2,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 1, max: 5, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    const radarCanvas = document.getElementById('insightsRadarChart');
    if (radarCanvas) {
        new Chart(radarCanvas, {
            type: 'radar',
            data: {
                labels: ['Mood', 'Energy', 'Sleep Quality', 'Low Stress'],
                datasets: [{
                    data: window.insightsRadarValues || [0,0,0,0],
                    borderWidth: 1.5,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    r: {
                        min: 0,
                        max: 5,
                        ticks: { stepSize: 1, display: false }
                    }
                }
            }
        });
    }

    const pieCanvas = document.getElementById('insightsPieChart');
    if (pieCanvas) {
        new Chart(pieCanvas, {
            type: 'pie',
            data: {
                labels: window.insightsPieLabels || [],
                datasets: [{
                    data: window.insightsPieValues || []
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    const barCanvas = document.getElementById('insightsBarChart');
    if (barCanvas) {
        new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: window.insightsBarLabels || [],
                datasets: [{
                    data: window.insightsBarValues || [],
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    }
});
