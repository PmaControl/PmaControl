(function () {
    "use strict";

    var payload = window.kpiDaemonCharts || {};

    function lineDataset(label, series, color) {
        return {
            label: label,
            data: (series && series.points) || [],
            borderColor: color,
            backgroundColor: color,
            borderWidth: 2,
            pointRadius: 0,
            tension: 0.2,
            spanGaps: false
        };
    }

    function thresholdDataset(series, threshold) {
        var points = (series && series.points) || [];
        if (!points.length || !Number.isFinite(threshold) || threshold <= 0) {
            return null;
        }

        return {
            label: "max_delay",
            data: [
                { x: points[0].x, y: threshold },
                { x: points[points.length - 1].x, y: threshold }
            ],
            borderColor: "#dc2626",
            backgroundColor: "#dc2626",
            borderDash: [6, 4],
            borderWidth: 2,
            pointRadius: 0,
            tension: 0
        };
    }

    function renderLine(canvasId, datasets, beginAtZero) {
        var canvas = document.getElementById(canvasId);
        if (!canvas || typeof Chart === "undefined") {
            return;
        }

        new Chart(canvas.getContext("2d"), {
            type: "line",
            data: { datasets: datasets.filter(Boolean) },
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
                    y: { beginAtZero: beginAtZero === true, title: { display: true, text: "ms" } }
                }
            }
        });
    }

    renderLine("kpi-daemon-duration", [
        lineDataset("cycle_duration_ms", payload.duration || {}, "#2563eb")
    ], true);

    renderLine("kpi-daemon-delay", [
        lineDataset("delay_ms", payload.delay || {}, "#d97706"),
        thresholdDataset(payload.delay || {}, Number(payload.maxDelayMs || 0))
    ], false);
})();
