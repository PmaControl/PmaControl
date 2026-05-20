(function () {
    "use strict";

    var payload = window.kpiServerCharts || {};
    var palette = {
        up: "#15803d",
        readonly: "#2563eb",
        down: "#dc2626",
        unknown: "#9ca3af"
    };

    function colorForValue(value) {
        if (value === 1) {
            return palette.up;
        }

        if (value === 2) {
            return palette.readonly;
        }

        if (value === 0) {
            return palette.down;
        }

        return palette.unknown;
    }

    function renderTimeline() {
        var canvas = document.getElementById("kpi-server-timeline");
        var timeline = payload.timeline || {};
        if (!canvas || typeof Chart === "undefined") {
            return;
        }

        new Chart(canvas.getContext("2d"), {
            type: "bar",
            data: {
                labels: timeline.labels || [],
                datasets: [{
                    data: (timeline.values || []).map(function () { return 1; }),
                    backgroundColor: (timeline.values || []).map(colorForValue),
                    borderWidth: 0,
                    barPercentage: 1,
                    categoryPercentage: 1,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                events: [],
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    x: { display: false, grid: { display: false }, border: { display: false } },
                    y: { display: false, min: 0, max: 1, grid: { display: false }, border: { display: false } }
                }
            }
        });
    }

    function renderPing() {
        var canvas = document.getElementById("kpi-server-ping");
        var sparklines = payload.sparklines || {};
        if (!canvas || typeof Chart === "undefined") {
            return;
        }

        var colors = ["#2563eb", "#15803d", "#dc2626", "#7c3aed", "#d97706", "#0891b2", "#4b5563"];
        var index = 0;
        var datasets = Object.keys(sparklines).map(function (kind) {
            var color = colors[index % colors.length];
            index++;

            return {
                label: kind,
                data: sparklines[kind] || [],
                borderColor: color,
                backgroundColor: color,
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.25
            };
        });

        new Chart(canvas.getContext("2d"), {
            type: "line",
            data: { datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: true, position: "bottom" },
                    tooltip: { mode: "nearest", intersect: false }
                },
                scales: {
                    x: { type: "time", time: { unit: "hour" }, grid: { display: false } },
                    y: { beginAtZero: true, title: { display: true, text: "ms" } }
                }
            }
        });
    }

    renderTimeline();
    renderPing();
})();
