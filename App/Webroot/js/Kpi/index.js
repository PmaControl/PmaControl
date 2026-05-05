(function () {
    "use strict";

    var payload = window.kpiDashboard || {};
    var charts = [];

    function getPath(source, path) {
        var parts = path.split(".");
        var value = source;
        var i;

        for (i = 0; i < parts.length; i++) {
            if (value === null || typeof value !== "object" || typeof value[parts[i]] === "undefined") {
                return null;
            }

            value = value[parts[i]];
        }

        return value;
    }

    function formatValue(value, path) {
        var number;

        if (value === null || typeof value === "undefined" || value === "") {
            return "-";
        }

        if (!isNaN(value) && value !== true && value !== false) {
            number = Number(value);
            if (Math.abs(number - Math.round(number)) < 0.001) {
                return String(Math.round(number)) + (path.indexOf("_pct") !== -1 ? "%" : "");
            }

            return number.toFixed(2) + (path.indexOf("_pct") !== -1 ? "%" : "");
        }

        return String(value);
    }

    function updateFields(realtime) {
        var source = {
            generated_at: realtime.generated_at || "-",
            latest: realtime.latest || {},
            worker: realtime.worker || {},
            daemon: realtime.daemon || {},
            events: realtime.events || {}
        };

        Array.prototype.forEach.call(document.querySelectorAll("[data-kpi-field]"), function (element) {
            var path = element.getAttribute("data-kpi-field") || "";
            element.textContent = formatValue(getPath(source, path), path);
        });
    }

    function renderEvents(events) {
        var list = document.querySelector("[data-kpi-events]");
        var recent = (events && events.recent) || [];

        if (!list) {
            return;
        }

        list.textContent = "";
        if (recent.length === 0) {
            var empty = document.createElement("li");
            empty.className = "kd-muted";
            empty.textContent = "No recent events.";
            list.appendChild(empty);
            return;
        }

        recent.forEach(function (event) {
            var item = document.createElement("li");
            var type = document.createElement("div");
            var message = document.createElement("div");
            var date = document.createElement("div");

            type.className = "kd-event-type";
            type.textContent = event.type || "-";
            message.textContent = event.message || "-";
            date.className = "kd-muted";
            date.textContent = event.date_start || "-";

            item.appendChild(type);
            item.appendChild(message);
            item.appendChild(date);
            list.appendChild(item);
        });
    }

    function chartOptions(datasets) {
        var hasPercent = datasets.some(function (dataset) {
            return dataset.unit === "%";
        });
        var firstUnit = datasets.length > 0 ? datasets[0].unit : "count";

        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: true, position: "bottom" },
                tooltip: { mode: "index", intersect: false }
            },
            scales: {
                x: { type: "time", time: { unit: "hour" }, grid: { display: false } },
                count: {
                    type: "linear",
                    position: "left",
                    beginAtZero: true,
                    title: { display: true, text: firstUnit === "ms" ? "ms" : "count" }
                },
                pct: {
                    type: "linear",
                    position: "right",
                    beginAtZero: true,
                    max: 100,
                    display: hasPercent,
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: "%" }
                }
            }
        };
    }

    function renderCharts() {
        var series = payload.series || {};
        var chartDefinitions = series.charts || [];

        if (typeof Chart === "undefined") {
            return;
        }

        chartDefinitions.forEach(function (definition) {
            var canvas = document.getElementById(definition.canvas_id);
            var datasets = definition.datasets || [];
            var chartDatasets;

            if (!canvas) {
                return;
            }

            chartDatasets = datasets.map(function (dataset) {
                return {
                    label: dataset.label,
                    data: dataset.points || [],
                    borderColor: dataset.color,
                    backgroundColor: dataset.color,
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0.25,
                    yAxisID: dataset.unit === "%" ? "pct" : "count"
                };
            });

            charts.push(new Chart(canvas.getContext("2d"), {
                type: "line",
                data: { datasets: chartDatasets },
                options: chartOptions(datasets)
            }));
        });
    }

    function refreshRealtime() {
        var realtimeUrl = payload.urls && payload.urls.realtime;

        if (!realtimeUrl || typeof fetch === "undefined") {
            return;
        }

        fetch(realtimeUrl, { credentials: "same-origin", cache: "no-store" })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("KPI realtime HTTP " + response.status);
                }

                return response.json();
            })
            .then(function (realtime) {
                payload.realtime = realtime;
                updateFields(realtime);
                renderEvents(realtime.events || {});
            })
            .catch(function () {
            });
    }

    updateFields(payload.realtime || {});
    renderEvents((payload.realtime && payload.realtime.events) || {});
    renderCharts();
    window.setInterval(refreshRealtime, payload.refresh_interval_ms || 10000);
})();
