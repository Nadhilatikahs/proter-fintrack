document.addEventListener('DOMContentLoaded', function () {
    if (!window.goalChartData) return;

    const canvas = document.getElementById('goalPieChart');
    if (!canvas) return;

    const colors = [
        '#7CB342', // green
        '#FFB74D', // orange
        '#64B5F6', // blue
        '#BA68C8', // purple
        '#E57373'  // red
    ];

    const chart = new Chart(canvas, {
        type: 'pie',
        data: {
            labels: window.goalChartData.labels,
            datasets: [{
                data: window.goalChartData.values,
                backgroundColor: colors.slice(0, window.goalChartData.labels.length),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const value = context.raw.toLocaleString('id-ID');
                            return `Rp ${value}`;
                        }
                    }
                }
            }
        }
    });

    // ============================
    // DOWNLOAD CHART HANDLER
    // ============================
    const downloadBtn = document.querySelector('.btn-download');

    if (downloadBtn) {
        downloadBtn.addEventListener('click', function (e) {
            e.preventDefault();

            // canvas -> base64 image
            const chartImage = canvas.toDataURL('image/png');

            // append image to URL
            const url = new URL(this.href, window.location.origin);
            url.searchParams.set('chart', chartImage);

            window.location.href = url.toString();
        });
    }
});
