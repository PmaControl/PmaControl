(function () {
    "use strict";

    var charts = {};
    var labels = [];
    var lastBucketKey = null;
    var stateConfig = window.serverStateConfig || {};
    var stateRoot = document.getElementById("server-state-root");
    var pollTimer = null;

    if (!stateConfig.initialUrl && typeof window.GLIAL_LINK !== "undefined") {
        stateConfig.initialUrl = window.GLIAL_LINK + "server/stateInitial/ajax:true";
    }

    if (!stateConfig.liveUrl && typeof window.GLIAL_LINK !== "undefined") {
        stateConfig.liveUrl = window.GLIAL_LINK + "server/stateLive/ajax:true";
    }

    function colorForValue(value) {
        if (value === 1) {
            return "#2ca25f";
        }

        if (value === 0) {
            return "#de2d26";
        }

        return "#bdbdbd";
    }

    function statusLabel(value) {
        if (value === 1) {
            return "UP";
        }

        if (value === 0) {
            return "DOWN";
        }

        return "N/A";
    }

    function statusClass(value) {
        if (value === 1) {
            return "server-state-status up";
        }

        if (value === 0) {
            return "server-state-status down";
        }

        return "server-state-status na";
    }

    function buildDataset(values) {
        return {
            data: values.map(function () {
                return 1;
            }),
            backgroundColor: values.map(colorForValue),
            borderWidth: 0,
            barPercentage: 1,
            categoryPercentage: 1,
            borderSkipped: false
        };
    }

    function createChart(canvas, values) {
        return new Chart(canvas.getContext("2d"), {
            type: "bar",
            data: {
                labels: labels,
                datasets: [buildDataset(values)]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                normalized: true,
                events: [],
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                layout: {
                    padding: 0
                },
                scales: {
                    x: {
                        display: false,
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            display: false
                        }
                    },
                    y: {
                        display: false,
                        beginAtZero: true,
                        min: 0,
                        max: 1,
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function renderTable(payload) {
        var html = [
            '<table class="table table-condensed table-bordered table-striped server-state-table">',
            '<thead>',
            '<tr><th>Server</th><th>Current status</th><th>(nombre de 1) / (nombre 0 + 1)</th><th>Last 60 minutes</th></tr>',
            '</thead>',
            '<tbody>'
        ];

        payload.servers.forEach(function (server) {
            html.push(
                '<tr data-server-id="' + server.server_id + '">',
                '<td class="server-state-name">' + (server.display_html || server.name) + '</td>',
                '<td><span class="' + statusClass(server.current_status) + '">' + statusLabel(server.current_status) + '</span></td>',
                '<td><span class="server-state-ratio">' + formatRatio(server.ratio) + '</span></td>',
                '<td><div class="server-state-chart-wrap"><canvas id="server-state-chart-' + server.server_id + '"></canvas></div></td>',
                '</tr>'
            );
        });

        html.push('</tbody></table>');
        stateRoot.innerHTML = html.join("");
    }

    function renderCharts(payload) {
        payload.servers.forEach(function (server) {
            var canvas = document.getElementById("server-state-chart-" + server.server_id);
            if (!canvas) {
                return;
            }

            charts[server.server_id] = createChart(canvas, server.values);
        });
    }

    function formatRatio(ratio) {
        if (!ratio) {
            return "0 / 0";
        }

        return ratio.label || ((ratio.one || 0) + " / " + (ratio.signal || 0));
    }

    function updateStatusCell(serverId, value) {
        var row = stateRoot.querySelector('tr[data-server-id="' + serverId + '"]');
        if (!row) {
            return;
        }

        var statusNode = row.querySelector(".server-state-status");
        if (!statusNode) {
            return;
        }

        statusNode.className = statusClass(value);
        statusNode.textContent = statusLabel(value);
    }

    function updateRatioCell(serverId) {
        var row = stateRoot.querySelector('tr[data-server-id="' + serverId + '"]');
        if (!row || !charts[serverId]) {
            return;
        }

        var ratioNode = row.querySelector(".server-state-ratio");
        if (!ratioNode) {
            return;
        }

        var colors = charts[serverId].data.datasets[0].backgroundColor || [];
        var oneCount = 0;
        var signalCount = 0;

        colors.forEach(function (color) {
            if (color === colorForValue(1)) {
                oneCount++;
                signalCount++;
            } else if (color === colorForValue(0)) {
                signalCount++;
            }
        });

        ratioNode.textContent = oneCount + " / " + signalCount;
    }

    function applyLivePayload(payload) {
        if (!payload || !payload.values || !payload.current_statuses) {
            return;
        }

        var isNewBucket = payload.bucket_key !== lastBucketKey;

        Object.keys(charts).forEach(function (serverId) {
            var chart = charts[serverId];
            var value = Object.prototype.hasOwnProperty.call(payload.values, serverId) ? payload.values[serverId] : null;
            var statusValue = Object.prototype.hasOwnProperty.call(payload.current_statuses, serverId) ? payload.current_statuses[serverId] : null;
            var colors = chart.data.datasets[0].backgroundColor;

            if (isNewBucket) {
                chart.data.labels.shift();
                chart.data.labels.push(payload.label);
                chart.data.datasets[0].data.shift();
                chart.data.datasets[0].data.push(1);
                colors.shift();
                colors.push(colorForValue(value));
            } else if (colors.length > 0) {
                colors[colors.length - 1] = colorForValue(value);
            }

            updateStatusCell(serverId, statusValue);
            updateRatioCell(serverId);
            chart.update("none");
        });

        lastBucketKey = payload.bucket_key;
    }

    function stopPolling() {
        if (pollTimer !== null) {
            window.clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function fetchJson(url, callback, onError) {
        var request = new XMLHttpRequest();
        request.open("GET", url, true);
        request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        request.onreadystatechange = function () {
            var contentType;
            var responseText;
            var trimmed;

            if (request.readyState !== 4) {
                return;
            }

            if (request.status !== 200) {
                if (onError) {
                    onError("Unable to load data (" + request.status + ")");
                }
                return;
            }

            contentType = request.getResponseHeader("Content-Type") || "";
            responseText = request.responseText || "";
            trimmed = responseText.replace(/^\s+/, "");

            if (contentType.indexOf("application/json") === -1 || trimmed.charAt(0) === "<") {
                if (window.console && console.error) {
                    console.error("Server/state expected JSON but received:", contentType, trimmed.slice(0, 120));
                }

                if (onError) {
                    onError("Invalid JSON response");
                }
                return;
            }

            try {
                callback(JSON.parse(responseText));
            } catch (error) {
                if (window.console && console.error) {
                    console.error("Server/state JSON parse error:", error, responseText.slice(0, 120));
                }

                if (onError) {
                    onError("Invalid JSON payload");
                }
            }
        };
        request.send();
    }

    function bootstrap() {
        if (!stateRoot || typeof window.Chart === "undefined") {
            return;
        }

        function start(payload) {
            labels = payload.labels.slice();
            lastBucketKey = payload.bucket_key;
            renderTable(payload);
            renderCharts(payload);

            stopPolling();
            pollTimer = window.setInterval(function () {
                fetchJson(stateConfig.liveUrl, applyLivePayload, function (message) {
                    stopPolling();

                    if (window.console && console.warn) {
                        console.warn("Server/state live polling stopped:", message);
                    }
                });
            }, stateConfig.refreshIntervalMs || 10000);
        }

        if (window.serverStateInitialPayload && window.serverStateInitialPayload.servers) {
            start(window.serverStateInitialPayload);
            return;
        }

        if (!stateConfig.initialUrl) {
            stateRoot.innerHTML = '<div class="loading">Missing initialUrl</div>';
            return;
        }

        fetchJson(stateConfig.initialUrl, start, function (message) {
            stateRoot.innerHTML = '<div class="loading">' + message + '</div>';
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bootstrap);
    } else {
        bootstrap();
    }
})();
