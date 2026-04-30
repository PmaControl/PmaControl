(function () {
    function formatValue(value, unit) {
        if (value === null || typeof value === 'undefined' || value === '') {
            return 'n/a';
        }

        var num = Number(value);
        if (!Number.isFinite(num)) {
            return 'n/a';
        }

        if (unit === 'percent') {
            return num.toFixed(2) + '%';
        }

        if (unit === 'bytes') {
            var units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
            var idx = 0;
            while (Math.abs(num) >= 1024 && idx < units.length - 1) {
                num /= 1024;
                idx++;
            }

            return num.toFixed(idx === 0 ? 0 : 2) + ' ' + units[idx];
        }

        if (unit === 'seconds') {
            if (num < 60) {
                return Math.round(num) + 's';
            }

            if (num < 3600) {
                return Math.floor(num / 60) + 'm ' + Math.round(num % 60) + 's';
            }

            return Math.floor(num / 3600) + 'h ' + Math.floor((num % 3600) / 60) + 'm';
        }

        return num.toLocaleString();
    }

    function collectCharts(payload) {
        var charts = {};

        (payload.sections || []).forEach(function (section) {
            (section.charts || []).forEach(function (chart) {
                charts[chart.id] = chart;
            });
        });

        return charts;
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
                        maxTicksLimit: 10
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
        if (typeof Chart === 'undefined' || !window.reportsPayload) {
            return;
        }

        var charts = collectCharts(window.reportsPayload);
        document.querySelectorAll('.js-reports-chart').forEach(function (canvas) {
            var chart = charts[canvas.getAttribute('data-chart-id')];
            var ctx;

            if (!chart) {
                return;
            }

            ctx = canvas.getContext('2d');
            if (!ctx) {
                return;
            }

            new Chart(ctx, {
                type: chart.type || 'bar',
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
