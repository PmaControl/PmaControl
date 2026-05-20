/*
 * /Job/index — live progress poller for long-running reload jobs.
 *
 * Each table row with status=RUNNING and a [data-role=job-progress-bar]
 * is polled every 2s through /Job/progress/<id>/ajax:true/. When the
 * status flips to SUCCESS / ERROR / INTERRUPTED, the poller is removed
 * and the page is reloaded once so the new "Restart" buttons appear.
 *
 * CSP-safe: no inline event handlers, the script wires itself on
 * DOMContentLoaded.
 */
(function () {
    'use strict';

    var POLL_INTERVAL_MS = 2000;
    var TERMINAL_STATUSES = ['SUCCESS', 'ERROR', 'INTERRUPTED', 'WARNING'];

    function init() {
        var rows = document.querySelectorAll('tr[data-job-id][data-job-status="RUNNING"]');
        if (!rows.length) {
            return;
        }
        var base = (typeof GLIAL_LINK === 'string' && GLIAL_LINK) ? GLIAL_LINK : '/';
        rows.forEach(function (row) {
            var id = row.getAttribute('data-job-id');
            if (!id) {
                return;
            }
            var url = base + 'Job/progress/' + encodeURIComponent(id) + '/ajax:true/';
            var timer = setInterval(function () { poll(row, url, timer); }, POLL_INTERVAL_MS);
        });
    }

    function poll(row, url, timer) {
        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) {
            if (!r.ok) {
                throw new Error('HTTP ' + r.status);
            }
            return r.json();
        }).then(function (data) {
            if (!data) {
                return;
            }
            updateRow(row, data);
            if (TERMINAL_STATUSES.indexOf(data.status) !== -1) {
                clearInterval(timer);
                // Defer the page reload so the operator sees the bar
                // hit 100% before the row layout changes (Restart
                // buttons appear).
                setTimeout(function () { window.location.reload(); }, 800);
            }
        }).catch(function () {
            // Silent on transient errors — keep polling.
        });
    }

    function updateRow(row, data) {
        var pct = data.progress_percent;
        var bar = row.querySelector('[data-role="job-progress-bar"]');
        var label = row.querySelector('[data-role="job-progress-label"]');
        if (bar && typeof pct === 'number') {
            bar.style.width = pct + '%';
            bar.setAttribute('aria-valuenow', String(pct));
        }
        if (label && typeof pct === 'number') {
            label.textContent = pct + '%';
        }
        var badge = row.querySelector('[data-role="job-status-badge"]');
        if (badge && typeof data.status === 'string') {
            badge.textContent = data.status;
        }
        if (typeof data.status === 'string') {
            row.setAttribute('data-job-status', data.status);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
