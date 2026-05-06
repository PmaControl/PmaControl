(function () {
    "use strict";

    var charts = {};
    var chartValues = {};
    var labels = [];
    var lastBucketKey = null;
    var stateConfig = window.serverStateConfig || {};
    var stateRoot = document.getElementById("server-state-root");
    var pollTimer = null;
    var staleBucketCount = 3;

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

        if (value === 2) {
            return "#2563eb";
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

        if (value === 2) {
            return "READ ONLY";
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

        if (value === 2) {
            return "server-state-status readonly";
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
        var chartTitle = "Availability timeline";
        var serverCount = (payload.servers || []).length;
        var upCount = 0;
        var downCount = 0;
        var readOnlyCount = 0;
        var signalCount = 0;
        var greyCount = 0;

        (payload.servers || []).forEach(function (server) {
            if (server.current_status === 1) {
                upCount++;
            } else if (server.current_status === 2) {
                readOnlyCount++;
            } else if (server.current_status === 0) {
                downCount++;
            }
        });

        if (payload.stats) {
            signalCount = payload.stats.signal || ((payload.stats.one || 0) + (payload.stats.two || 0) + (payload.stats.zero || 0));
            greyCount = payload.stats.missing || Math.max((payload.stats.total || 0) - signalCount, 0);
        }

        if (payload.range && payload.range.title) {
            chartTitle = payload.range.title;
        }

        if (payload.range && payload.range.stale_bucket_count) {
            staleBucketCount = payload.range.stale_bucket_count;
        }

        var html = [
            '<div class="server-state-summary-row">',
            buildSummaryCard("Servers", serverCount, "#0f766e"),
            buildSummaryCard("Current UP", upCount, "#2563eb"),
            buildSummaryCard("Current READ ONLY", readOnlyCount, "#2563eb"),
            buildSummaryCard("Current DOWN", downCount, "#dc2626"),
            buildSummaryCard("No Data Buckets", greyCount, "#6b7280"),
            '</div>',
            '<div class="server-state-panel">',
            '<div class="server-state-panel-head">',
            '<div class="row">',
            '<div class="col-md-8">',
            '<div class="server-state-panel-title">Availability Timeline</div>',
            '<div class="server-state-panel-subtitle">' + escapeHtml(chartTitle) + '</div>',
            '</div>',
            '<div class="col-md-4 text-right">',
            '<span class="label label-default" style="font-size:13px;margin-top:6px;display:inline-block;">Signal buckets: ' + signalCount + '</span>',
            '</div>',
            '</div>',
            '</div>',
            '<div class="server-state-panel-body">',
            '<table class="table table-condensed table-bordered table-striped server-state-table">',
            '<thead>',
            '<tr><th>Server</th><th>Current status</th><th>Availability / Coverage</th><th>Timeline</th></tr>',
            '</thead>',
            '<tbody>'
        ];

        payload.servers.forEach(function (server) {
            html.push(
                '<tr data-server-id="' + server.server_id + '">',
                '<td class="server-state-name">' + (server.display_html || server.name) + '</td>',
                '<td class="server-state-status-cell">' + formatStatusHtml(server.current_status, server.is_stale) + '</td>',
                '<td><span class="server-state-ratio">' + formatRatio(server.ratio) + '</span></td>',
                '<td><div class="server-state-chart-wrap"><canvas id="server-state-chart-' + server.server_id + '"></canvas></div></td>',
                '</tr>'
            );
        });

        html.push('</tbody></table></div></div>');
        stateRoot.innerHTML = html.join("");
    }

    function buildSummaryCard(label, value, color) {
        return [
            '<div class="server-state-summary-col">',
            '<div class="server-state-summary-card" style="border-left-color:' + color + ';">',
            '<div class="server-state-summary-label">' + escapeHtml(String(label)) + '</div>',
            '<div class="server-state-summary-value">' + escapeHtml(String(value)) + '</div>',
            '</div>',
            '</div>'
        ].join("");
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderCharts(payload) {
        payload.servers.forEach(function (server) {
            var canvas = document.getElementById("server-state-chart-" + server.server_id);
            if (!canvas) {
                return;
            }

            chartValues[server.server_id] = (server.values || []).slice();
            charts[server.server_id] = createChart(canvas, server.values);
        });
    }

    function formatRatio(ratio) {
        if (!ratio) {
            ratio = computeRatio([]);
        }

        return [
            '<span class="server-state-ratio-line">Availability: ' + escapeHtml(ratio.availability_label || ratio.label || ((ratio.one || 0) + " / " + (ratio.availability_signal || 0))) + '</span>',
            '<span class="server-state-ratio-line server-state-ratio-muted">Coverage: ' + escapeHtml(ratio.coverage_label || ((ratio.signal || 0) + " / " + (ratio.total || 0))) + '</span>',
            '<span class="server-state-ratio-line server-state-ratio-muted">' + escapeHtml(ratio.missing_label || ((ratio.missing || 0) + " missing")) + '</span>'
        ].join("");
    }

    function formatStatusHtml(value, isStale) {
        return [
            '<span class="' + statusClass(value) + '">' + statusLabel(value) + '</span>',
            isStale ? '<span class="server-state-stale-badge">STALE</span>' : ''
        ].join("");
    }

    function updateStatusCell(serverId, value, isStale) {
        var row = stateRoot.querySelector('tr[data-server-id="' + serverId + '"]');
        if (!row) {
            return;
        }

        var statusNode = row.querySelector(".server-state-status-cell");
        if (!statusNode) {
            return;
        }

        statusNode.innerHTML = formatStatusHtml(value, isStale);
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

        ratioNode.innerHTML = formatRatio(computeRatio(chartValues[serverId] || []));
    }

    function computeRatio(values) {
        var zeroCount = 0;
        var oneCount = 0;
        var twoCount = 0;
        var signalCount = 0;
        var totalCount = values.length;

        values.forEach(function (value) {
            if (value === 1) {
                oneCount++;
                signalCount++;
            } else if (value === 0) {
                zeroCount++;
                signalCount++;
            } else if (value === 2) {
                twoCount++;
                signalCount++;
            }
        });

        return {
            zero: zeroCount,
            one: oneCount,
            two: twoCount,
            signal: signalCount,
            availability_signal: oneCount + zeroCount,
            missing: Math.max(totalCount - signalCount, 0),
            total: totalCount,
            availability_label: oneCount + " / " + (oneCount + zeroCount),
            coverage_label: signalCount + " / " + totalCount,
            missing_label: Math.max(totalCount - signalCount, 0) + " missing"
        };
    }

    function isStale(values) {
        var tail;

        if (!values || values.length < staleBucketCount) {
            return false;
        }

        tail = values.slice(values.length - staleBucketCount);
        return tail.every(function (value) {
            return value === null;
        });
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

            if (!chartValues[serverId]) {
                chartValues[serverId] = [];
            }

            if (isNewBucket) {
                chart.data.labels.shift();
                chart.data.labels.push(payload.label);
                chart.data.datasets[0].data.shift();
                chart.data.datasets[0].data.push(1);
                colors.shift();
                colors.push(colorForValue(value));
                chartValues[serverId].shift();
                chartValues[serverId].push(value);
            } else if (colors.length > 0) {
                colors[colors.length - 1] = colorForValue(value);
                if (chartValues[serverId].length > 0) {
                    chartValues[serverId][chartValues[serverId].length - 1] = value;
                }
            }

            updateStatusCell(serverId, statusValue, isStale(chartValues[serverId]));
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
            if (stateConfig.liveEnabled === false || (payload.range && payload.range.live_enabled === false)) {
                return;
            }

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
