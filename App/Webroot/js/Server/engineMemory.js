(function () {
    if (typeof Chart !== 'undefined' && Chart.Tooltip && Chart.Tooltip.positioners) {
        Chart.Tooltip.positioners.pmacontrolOffset = function (elements, eventPosition) {
            var base = Chart.Tooltip.positioners.average.call(this, elements, eventPosition);

            if (!base) {
                base = { x: eventPosition.x, y: eventPosition.y };
            }

            return {
                x: base.x + 18,
                y: base.y - 18
            };
        };
    }

    function kbToMiB(valueKb) {
        return valueKb / 1024;
    }

    function kbToGiB(valueKb) {
        return valueKb / (1024 * 1024);
    }

    function formatMemoryFromMiB(valueMiB) {
        var value = Number(valueMiB || 0);

        if (Math.abs(value) >= (1024 * 1024)) {
            return (value / (1024 * 1024)).toFixed(2) + ' TiB';
        }

        if (Math.abs(value) >= 1024) {
            return (value / 1024).toFixed(2) + ' GiB';
        }

        return value.toFixed(0) + ' MiB';
    }

    function computeProcessStackMaxMiB(datasets) {
        var max = 0;
        var pointCount = 0;

        datasets.forEach(function (dataset) {
            if (dataset.isReferenceLine) {
                return;
            }
            pointCount = Math.max(pointCount, (dataset.data || []).length);
        });

        for (var i = 0; i < pointCount; i += 1) {
            var sum = 0;

            datasets.forEach(function (dataset) {
                if (dataset.isReferenceLine) {
                    return;
                }

                sum += Number((dataset.data || [])[i] || 0);
            });

            if (sum > max) {
                max = sum;
            }
        }

        return max;
    }

    function computeReferenceMaxMiB(datasets) {
        var max = 0;

        datasets.forEach(function (dataset) {
            if (!dataset.isReferenceLine) {
                return;
            }

            (dataset.data || []).forEach(function (value) {
                var number = Number(value || 0);
                if (number > max) {
                    max = number;
                }
            });
        });

        return max;
    }

    function computeChartMaxMiB(datasets) {
        return Math.max(computeProcessStackMaxMiB(datasets), computeReferenceMaxMiB(datasets));
    }

    function computeVisibleProcessStackMaxMiB(chart) {
        var max = 0;
        var pointCount = 0;

        chart.data.datasets.forEach(function (dataset, datasetIndex) {
            if (dataset.isReferenceLine || !chart.isDatasetVisible(datasetIndex)) {
                return;
            }

            pointCount = Math.max(pointCount, (dataset.data || []).length);
        });

        for (var i = 0; i < pointCount; i += 1) {
            var sum = 0;

            chart.data.datasets.forEach(function (dataset, datasetIndex) {
                if (dataset.isReferenceLine || !chart.isDatasetVisible(datasetIndex)) {
                    return;
                }

                sum += Number((dataset.data || [])[i] || 0);
            });

            if (sum > max) {
                max = sum;
            }
        }

        return max;
    }

    function computeVisibleReferenceMaxMiB(chart) {
        var max = 0;

        chart.data.datasets.forEach(function (dataset, datasetIndex) {
            if (!dataset.isReferenceLine || !chart.isDatasetVisible(datasetIndex)) {
                return;
            }

            (dataset.data || []).forEach(function (value) {
                var number = Number(value || 0);
                if (number > max) {
                    max = number;
                }
            });
        });

        return max;
    }

    function updateAxesForVisibleDatasets(chart) {
        var processMax = computeVisibleProcessStackMaxMiB(chart);
        var referenceMax = computeVisibleReferenceMaxMiB(chart);
        var globalMax = Math.max(processMax, referenceMax);
        var processScaleMax = processMax > 0 ? processMax * 1.05 : undefined;
        var overlayScaleMax = globalMax > 0 ? globalMax * 1.05 : undefined;

        chart.options.scales.y.suggestedMax = processScaleMax;
        chart.options.scales.y.max = processScaleMax;
        chart.options.scales.yOverlay.suggestedMax = overlayScaleMax;
        chart.options.scales.yOverlay.max = overlayScaleMax;
    }

    function buildDatasets(datasets, memoryTotalKb, memoryUsedKb, pointCount) {
        var chartDatasets = datasets.map(function (dataset) {
            return {
                label: dataset.label,
                data: (dataset.data || []).map(function (value) {
                    return kbToMiB(Number(value || 0));
                }),
                fill: true,
                stack: 'process-memory',
                yAxisID: 'y',
                borderColor: dataset.borderColor,
                backgroundColor: dataset.backgroundColor,
                pointRadius: 0,
                pointHoverRadius: 0,
                borderWidth: 1,
                tension: 0,
                order: dataset.label === 'Others' ? 999 : 0
            };
        });

        if (Array.isArray(memoryUsedKb) && memoryUsedKb.length > 0) {
            chartDatasets.push({
                label: 'memory_used',
                data: memoryUsedKb.map(function (value) {
                    return value === null || typeof value === 'undefined' ? null : kbToMiB(Number(value));
                }),
                type: 'line',
                fill: false,
                stack: undefined,
                yAxisID: 'yOverlay',
                borderColor: 'rgba(31, 119, 180, 0.95)',
                backgroundColor: 'transparent',
                borderDash: [3, 3],
                pointRadius: 0,
                pointHoverRadius: 0,
                borderWidth: 1,
                tension: 0,
                order: -999,
                isReferenceLine: true
            });
        }

        if (Number(memoryTotalKb || 0) > 0 && Number(pointCount || 0) > 0) {
            chartDatasets.push({
                label: 'Total memory',
                data: Array(pointCount).fill(kbToMiB(Number(memoryTotalKb))),
                type: 'line',
                fill: false,
                stack: undefined,
                yAxisID: 'yOverlay',
                borderColor: 'rgba(20, 20, 20, 0.9)',
                backgroundColor: 'transparent',
                borderDash: [6, 4],
                pointRadius: 0,
                pointHoverRadius: 0,
                borderWidth: 1,
                tension: 0,
                order: -1000,
                isReferenceLine: true
            });
        }

        return chartDatasets;
    }

    function buildLegendClickHandler() {
        var isolatedIndex = null;

        return function (_event, legendItem, legend) {
            var chart = legend.chart;
            var clickedIndex = legendItem.datasetIndex;

            if (isolatedIndex === clickedIndex) {
                chart.data.datasets.forEach(function (dataset, datasetIndex) {
                    chart.setDatasetVisibility(datasetIndex, true);
                    if (dataset.isReferenceLine) {
                        chart.setDatasetVisibility(datasetIndex, true);
                    }
                });
                isolatedIndex = null;
                updateAxesForVisibleDatasets(chart);
                chart.update();
                return;
            }

            chart.data.datasets.forEach(function (dataset, datasetIndex) {
                if (dataset.isReferenceLine) {
                    chart.setDatasetVisibility(datasetIndex, false);
                    return;
                }

                chart.setDatasetVisibility(datasetIndex, datasetIndex === clickedIndex);
            });

            isolatedIndex = clickedIndex;
            updateAxesForVisibleDatasets(chart);
            chart.update();
        };
    }

    function buildPhysicalMemoryPlugin() {
        return {
            id: 'physicalMemoryLabel',
            afterDraw: function (chart) {
                var datasetIndex = chart.data.datasets.findIndex(function (dataset) {
                    return dataset.isReferenceLine;
                });

                if (datasetIndex === -1 || !chart.isDatasetVisible(datasetIndex)) {
                    return;
                }

                var dataset = chart.data.datasets[datasetIndex];
                var yScale = chart.scales.yOverlay;
                var area = chart.chartArea;
                if (!yScale || !area) {
                    return;
                }

                var value = Number((dataset.data || [])[0] || 0);
                if (value <= 0) {
                    return;
                }

                var y = yScale.getPixelForValue(value);
                var ctx = chart.ctx;
                var label = 'Physical memory : ' + kbToGiB((chart.$memoryTotalKb || 0)).toFixed(2) + ' GiB';

                ctx.save();
                ctx.fillStyle = 'rgba(20, 20, 20, 0.95)';
                ctx.font = '12px sans-serif';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'alphabetic';
                ctx.fillText(label, area.left + 8, y - 14);
                ctx.restore();
            }
        };
    }

    function renderProcessMemoryChart() {
        var payload = window.engineMemoryChartPayload || null;
        var canvas = document.getElementById('engine-memory-process-chart');
        var datasets = buildDatasets(
            payload ? (payload.datasets || []) : [],
            payload ? (payload.memory_total_kb || 0) : 0,
            payload ? (payload.memory_used_kb || []) : [],
            payload ? ((payload.labels || []).length) : 0
        );
        var chartMaxMiB = computeChartMaxMiB(datasets);

        if (!payload || !canvas || typeof Chart === 'undefined') {
            return;
        }

        var chart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: payload.labels || [],
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                normalized: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'right',
                        onClick: buildLegendClickHandler(),
                        labels: {
                            boxWidth: 10,
                            usePointStyle: false,
                            filter: function (legendItem, chartData) {
                                var dataset = chartData.datasets[legendItem.datasetIndex];
                                return !dataset.isReferenceLine;
                            }
                        }
                    },
                    tooltip: {
                        position: 'pmacontrolOffset',
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + formatMemoryFromMiB(context.parsed.y);
                            }
                        }
                    },
                    physicalMemoryLabel: {}
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 24
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        suggestedMax: chartMaxMiB > 0 ? chartMaxMiB * 1.05 : undefined,
                        title: {
                            display: true,
                            text: 'Memory'
                        },
                        ticks: {
                            callback: function (value) {
                                return formatMemoryFromMiB(value);
                            }
                        }
                    },
                    yOverlay: {
                        stacked: false,
                        beginAtZero: true,
                        display: false,
                        position: 'right',
                        min: 0,
                        suggestedMax: chartMaxMiB > 0 ? chartMaxMiB * 1.05 : undefined,
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            },
            plugins: [buildPhysicalMemoryPlugin()]
        });

        chart.$memoryTotalKb = Number(payload.memory_total_kb || 0);
        updateAxesForVisibleDatasets(chart);
        window.engineMemoryProcessChart = chart;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderProcessMemoryChart);
    } else {
        renderProcessMemoryChart();
    }
})();
