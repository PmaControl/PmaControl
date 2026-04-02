(function () {
    function formatValue(value, unit) {
        if (value === null || typeof value === 'undefined' || value === '') {
            return 'n/a';
        }

        if (unit === 'percent') {
            return Number(value).toFixed(2) + '%';
        }

        if (unit === 'bytes' || unit === 'bytes_per_second') {
            var units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
            var idx = 0;
            var num = Number(value);

            while (Math.abs(num) >= 1024 && idx < units.length - 1) {
                num /= 1024;
                idx++;
            }

            return num.toFixed(2) + ' ' + units[idx] + (unit === 'bytes_per_second' ? '/s' : '');
        }

        if (unit === 'ops_per_second') {
            return Number(value).toFixed(2) + '/s';
        }

        if (unit === 'milliseconds') {
            return Number(value).toFixed(2) + ' ms/s';
        }

        return Number(value).toFixed(2);
    }

    function buildOptions(chart) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + formatValue(context.parsed.y, chart.unit || 'count');
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 12
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return formatValue(value, chart.unit || 'count');
                        }
                    }
                }
            }
        };
    }

    function renderCharts() {
        if (typeof Chart === 'undefined' || !window.pmmDashboardPayload) {
            return;
        }

        var chartMap = {};
        (window.pmmDashboardPayload.sections || []).forEach(function (section) {
            (section.charts || []).forEach(function (chart) {
                chartMap[chart.id] = chart;
            });
        });

        document.querySelectorAll('.js-pmm-chart').forEach(function (canvas) {
            var chartId = canvas.getAttribute('data-chart-id');
            var chart = chartMap[chartId];

            if (!chart) {
                return;
            }

            if (chart.renderer === 'engineMemoryProcess' && typeof window.renderEngineMemoryProcessChart === 'function') {
                window.renderEngineMemoryProcessChart(canvas, chart);
                return;
            }

            var ctx = canvas.getContext('2d');
            if (!ctx) {
                return;
            }

            new Chart(ctx, {
                type: chart.type || 'line',
                data: {
                    labels: chart.labels || [],
                    datasets: chart.datasets || []
                },
                options: buildOptions(chart)
            });
        });
    }

    document.addEventListener('DOMContentLoaded', renderCharts);
})();
