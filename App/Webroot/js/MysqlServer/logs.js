(function () {
    if (typeof console !== 'undefined' && typeof console.log === 'function') {
        console.log('[MysqlServer/logs]', 'script file evaluated');
    }

    function debug(label, value) {
        if (typeof console === 'undefined' || typeof console.log !== 'function') {
            return;
        }
        console.log('[MysqlServer/logs]', label, value);
    }

    function buildDatasets(data) {
        var datasets = (data && data.datasets) ? data.datasets : {};
        debug('buildDatasets input', data);
        return [
            {
                label: 'ERROR',
                data: datasets.ERROR || [],
                backgroundColor: 'rgba(220, 38, 38, 0.85)',
                borderColor: 'rgba(185, 28, 28, 1)',
                borderWidth: 1,
                stack: 'logs'
            },
            {
                label: 'WARNING',
                data: datasets.WARNING || [],
                backgroundColor: 'rgba(245, 158, 11, 0.85)',
                borderColor: 'rgba(180, 83, 9, 1)',
                borderWidth: 1,
                stack: 'logs'
            },
            {
                label: 'NOTE',
                data: datasets.NOTE || [],
                backgroundColor: 'rgba(59, 130, 246, 0.85)',
                borderColor: 'rgba(29, 78, 216, 1)',
                borderWidth: 1,
                stack: 'logs'
            }
        ];
    }

    function makeConfig(title, data) {
        debug('makeConfig title', title);
        debug('makeConfig labels', data && data.labels ? data.labels.length : null);
        return {
            type: 'bar',
            data: {
                labels: data.labels || [],
                datasets: buildDatasets(data)
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: title
                    }
                },
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        };
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function buildPageNumbers(currentPage, totalPages) {
        var marks = {};
        var page;
        var result = [];

        for (page = 1; page <= Math.min(10, totalPages); page += 1) {
            marks[page] = true;
        }
        for (page = Math.max(1, currentPage - 10); page <= Math.min(totalPages, currentPage + 10); page += 1) {
            marks[page] = true;
        }
        for (page = Math.max(1, totalPages - 4); page <= totalPages; page += 1) {
            marks[page] = true;
        }

        Object.keys(marks).forEach(function (key) {
            result.push(parseInt(key, 10));
        });
        result.sort(function (a, b) { return a - b; });

        return result;
    }

    debug('before DOMContentLoaded listener registration', true);
    document.addEventListener('DOMContentLoaded', function () {
        debug('DOMContentLoaded', true);
        debug('window location', typeof window !== 'undefined' ? window.location.href : null);
        debug('document readyState', document.readyState);
        debug('Chart typeof', typeof Chart);
        debug('window.Chart exists', typeof window !== 'undefined' ? !!window.Chart : null);

        if (typeof Chart === 'undefined') {
            debug('abort', 'Chart is undefined');
            return;
        }

        var payload = window.mysqlServerLogsChartPayload || null;
        var canvas = document.getElementById('mysql-logs-chart');
        var context = canvas && typeof canvas.getContext === 'function' ? canvas.getContext('2d') : null;
        var wrapper = canvas ? canvas.parentNode : null;
        var resetButton = document.getElementById('mysql-logs-chart-reset');
        var linesState = document.getElementById('mysql-logs-lines-state');
        var linesBody = document.getElementById('mysql-logs-lines-body');
        var linesPaginationTop = document.getElementById('mysql-logs-lines-pagination-top');
        var linesPaginationBottom = document.getElementById('mysql-logs-lines-pagination-bottom');
        var chart = null;
        var currentLinesScope = 'month';
        var currentLinesKey = '';

        debug('payload exists', !!payload);
        debug('payload current_type', payload && payload.current_type ? payload.current_type : null);
        debug('payload labels count', payload && payload.day && payload.day.labels ? payload.day.labels.length : null);
        debug('payload datasets count', payload && payload.day && payload.day.datasets ? {
            ERROR: (payload.day.datasets.ERROR || []).length,
            WARNING: (payload.day.datasets.WARNING || []).length,
            NOTE: (payload.day.datasets.NOTE || []).length
        } : null);
        debug('first labels', payload && payload.day && payload.day.labels ? payload.day.labels.slice(0, 5) : null);
        debug('last labels', payload && payload.day && payload.day.labels ? payload.day.labels.slice(-5) : null);
        debug('canvas exists', !!canvas);
        debug('canvas outerHTML', canvas ? canvas.outerHTML : null);
        debug('wrapper exists', !!wrapper);
        debug('wrapper size', wrapper ? {
            clientWidth: wrapper.clientWidth,
            clientHeight: wrapper.clientHeight
        } : null);
        debug('canvas size', canvas ? {
            width: canvas.width,
            height: canvas.height,
            clientWidth: canvas.clientWidth,
            clientHeight: canvas.clientHeight
        } : null);
        debug('2d context exists', !!context);
        debug('Chart.instances', typeof Chart.instances !== 'undefined' ? Chart.instances : null);

        if (!payload || !canvas || !context) {
            debug('abort', 'payload, canvas or context missing');
            return;
        }

        function fetchJson(url, callback) {
            debug('fetchJson url', url);
            var request = new XMLHttpRequest();
            request.open('GET', url, true);
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.onreadystatechange = function () {
                if (request.readyState !== 4) {
                    return;
                }

                debug('fetchJson status', request.status);

                if (request.status < 200 || request.status >= 300) {
                    return;
                }

                try {
                    callback(JSON.parse(request.responseText));
                } catch (error) {
                    if (typeof console !== 'undefined' && typeof console.error === 'function') {
                        console.error('[MysqlServer/logs] fetchJson parse error', {
                            error: error,
                            url: url,
                            response_start: String(request.responseText || '').slice(0, 240)
                        });
                    }
                }
            };
            request.send();
        }

        function getLevelClass(level) {
            var value = String(level || '').toUpperCase();
            if (value === 'ERROR' || value === 'OOM') {
                return 'mysql-logs-level-error';
            }
            if (value === 'WARNING' || value === 'WARN') {
                return 'mysql-logs-level-warning';
            }
            return 'mysql-logs-level-default';
        }

        function renderPagination(target, page, totalPages) {
            var html = '';
            var pageNumbers = buildPageNumbers(page, totalPages);
            var previousPage = null;

            if (!target) {
                return;
            }

            html += '<div class="mysql-logs-muted">Page ' + page + ' / ' + totalPages + '</div>';
            html += '<div class="mysql-logs-pagination-actions">';

            if (page > 1) {
                html += '<a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="1">First</a>';
                html += '<a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="' + (page - 1) + '">Previous</a>';
            }

            pageNumbers.forEach(function (pageNumber) {
                if (previousPage !== null && pageNumber > (previousPage + 1)) {
                    html += '<span class="mysql-logs-page-gap">...</span>';
                }
                html += '<a href="#" class="btn btn-default btn-sm mysql-logs-page-link ' + (pageNumber === page ? 'active' : '') + '" data-page="' + pageNumber + '">' + pageNumber + '</a>';
                previousPage = pageNumber;
            });

            if (page < totalPages) {
                html += '<a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="' + (page + 1) + '">Next</a>';
                html += '<a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="' + totalPages + '">Last</a>';
            }

            html += '</div>';
            target.innerHTML = html;
        }

        function renderLines(payloadLines) {
            var html = '';
            var startNumber;

            if (!linesBody) {
                return;
            }

            if (linesState) {
                linesState.textContent = 'Scope: ' + payloadLines.scope + (payloadLines.key ? ' / ' + payloadLines.key : '') + ' / Rows: ' + payloadLines.total_rows;
            }

            if (!payloadLines.lines || !payloadLines.lines.length) {
                linesBody.innerHTML = '<tr><td colspan="10" class="text-center mysql-logs-muted">No log line collected yet</td></tr>';
                renderPagination(linesPaginationTop, 1, 1);
                renderPagination(linesPaginationBottom, 1, 1);
                return;
            }

            startNumber = ((payloadLines.page - 1) * payloadLines.page_size) + 1;
            payloadLines.lines.forEach(function (line, index) {
                html += '<tr>';
                html += '<td>' + (startNumber + index) + '</td>';
                html += '<td>' + escapeHtml(line.event_time || '') + '</td>';
                html += '<td><span class="mysql-logs-level-badge ' + getLevelClass(line.level) + '">' + escapeHtml(line.level || '') + '</span></td>';
                html += '<td>' + escapeHtml(line.error_code || '') + '</td>';
                html += '<td>' + escapeHtml(line.process_name || '') + '</td>';
                html += '<td>' + escapeHtml(line.user_name || '') + '</td>';
                html += '<td>' + escapeHtml(line.host_name || '') + '</td>';
                html += '<td>' + escapeHtml(line.db_name || '') + '</td>';
                html += '<td style="white-space: pre-wrap; word-break: break-word;">' + escapeHtml(line.message || '') + '</td>';
                html += '<td style="white-space: pre-wrap; word-break: break-word;">' + escapeHtml(line.raw_line || '') + '</td>';
                html += '</tr>';
            });

            linesBody.innerHTML = html;
            renderPagination(linesPaginationTop, payloadLines.page, payloadLines.total_pages);
            renderPagination(linesPaginationBottom, payloadLines.page, payloadLines.total_pages);
        }

        function loadLines(scope, key, page) {
            var url;

            if (!payload || !payload.lines_url_base) {
                return;
            }

            currentLinesScope = scope;
            currentLinesKey = key || '';

            if (linesBody) {
                linesBody.innerHTML = '<tr><td colspan="10" class="text-center mysql-logs-muted">Loading...</td></tr>';
            }

            url = payload.lines_url_base + scope + '/';
            if (key) {
                url += key + '/';
            }
            url += 'ajax:true/';
            url += '?page=' + (page || 1);

            fetchJson(url, function (data) {
                renderLines(data);
            });
        }

        function render(title, data, onClick) {
            debug('render title', title);
            if (chart) {
                chart.destroy();
            }

            var config = makeConfig(title, data);
            config.options.onClick = onClick || null;
            chart = new Chart(context, config);
            debug('chart created', !!chart);
        }

        function renderDay() {
            loadLines('month', '', 1);
            render('Last 30 days', payload.day, function (_event, elements) {
                if (!elements || !elements.length) {
                    return;
                }

                var index = elements[0].index;
                var dayKey = (payload.day.labels || [])[index];
                if (!dayKey) {
                    return;
                }

                loadLines('day', dayKey, 1);
                fetchJson(payload.data_url_base + 'hour/' + dayKey + '/ajax:true', function (data) {
                    render(dayKey + ' - 24h', data, function (__event, hourElements) {
                        if (!hourElements || !hourElements.length) {
                            return;
                        }

                        var hourIndex = hourElements[0].index;
                        var hourLabel = (data.labels || [])[hourIndex];
                        if (!hourLabel) {
                            return;
                        }

                        loadLines('hour', dayKey + '_' + hourLabel.slice(0, 2), 1);
                        fetchJson(payload.data_url_base + 'minute/' + dayKey + '_' + hourLabel.slice(0, 2) + '/ajax:true', function (minuteData) {
                            render(dayKey + ' ' + hourLabel.slice(0, 2) + ':00 - 60m', minuteData, function (___event, minuteElements) {
                                if (!minuteElements || !minuteElements.length) {
                                    return;
                                }

                                var minuteIndex = minuteElements[0].index;
                                var minuteLabel = (minuteData.labels || [])[minuteIndex];
                                if (!minuteLabel) {
                                    return;
                                }

                                loadLines('minute', dayKey + '_' + hourLabel.slice(0, 2) + '_' + minuteLabel, 1);
                            });
                        });
                    });
                });
            });
        }

        function bindPagination(root) {
            if (!root) {
                return;
            }

            root.addEventListener('click', function (event) {
                var target = event.target;
                var page;

                if (!target || !target.getAttribute('data-page')) {
                    return;
                }

                event.preventDefault();
                page = parseInt(target.getAttribute('data-page'), 10);
                if (!page || page < 1) {
                    return;
                }

                loadLines(currentLinesScope, currentLinesKey, page);
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                renderDay();
            });
        }

        bindPagination(linesPaginationTop);
        bindPagination(linesPaginationBottom);

        try {
            renderDay();
        } catch (error) {
            if (typeof console !== 'undefined' && typeof console.error === 'function') {
                console.error('[MysqlServer/logs] chart error', error);
            }
        }
    });
    debug('after DOMContentLoaded listener registration', true);
})();
