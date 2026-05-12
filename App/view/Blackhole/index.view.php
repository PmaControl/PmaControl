<?php
/**
 * /Blackhole/index — Tools → BLACKHOLE.
 * List candidate servers, current relays, and recent conversion runs.
 * Issue #1212 (lots #1213-#1217).
 *
 * @var array $data
 */

use App\Library\Display;

$servers     = $data['servers'] ?? [];
$conversions = $data['conversions'] ?? [];

$relays    = array_values(array_filter($servers, static fn ($s) => (int) $s['is_binlog_relay'] === 1));
$candidates = array_values(array_filter($servers, static fn ($s) => (int) $s['is_binlog_relay'] === 0));
?>
<style>
.bh-card { border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 16px; }
.bh-card-head {
    background: linear-gradient(135deg, #0b0b1a, #4c1d95 60%, #f59e0b 110%);
    color: #fff; padding: 8px 14px; font-weight: 600;
    border-radius: 6px 6px 0 0;
    display: flex; align-items: center; gap: 8px;
}
.bh-card-body { padding: 8px 14px; }
.bh-table { width: 100%; border-collapse: collapse; }
.bh-table th, .bh-table td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.bh-table th { text-align: left; color: #64748b; font-weight: 600; }
.bh-pill {
    display: inline-block; padding: 2px 8px; border-radius: 10px;
    font-size: 11px; font-weight: 600; color: #fff;
}
.bh-pill.pending { background: #94a3b8; }
.bh-pill.running { background: #2563eb; }
.bh-pill.done    { background: #16a34a; }
.bh-pill.failed  { background: #dc2626; }
.bh-pill.relay   { background: #4c1d95; }
.bh-progress {
    background: #0f172a; color: #e2e8f0; font-family: monospace;
    font-size: 12px; padding: 10px; border-radius: 4px;
    max-height: 320px; overflow-y: auto; line-height: 1.6;
    display: none;
}
.bh-progress .step.running { color: #facc15; }
.bh-progress .step.done    { color: #4ade80; }
.bh-progress .step.error   { color: #f87171; }
.bh-btn-convert {
    background: #4c1d95; color: #fff; border: none; padding: 4px 10px;
    border-radius: 4px; cursor: pointer; font-size: 12px;
}
.bh-btn-convert:hover { background: #6d28d9; }
.bh-icon {
    width: 18px; height: 18px; vertical-align: -4px;
}
</style>

<div class="bh-card">
    <div class="bh-card-head">
        <svg class="bh-icon" viewBox="0 0 16 16" aria-hidden="true">
            <defs><radialGradient id="bh-head" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#000"/>
                <stop offset="55%" stop-color="#1a1a2e"/>
                <stop offset="75%" stop-color="#7e22ce"/>
                <stop offset="92%" stop-color="#f59e0b"/>
                <stop offset="100%" stop-color="rgba(245,158,11,0)"/>
            </radialGradient></defs>
            <circle cx="8" cy="8" r="7.5" fill="url(#bh-head)"/>
            <circle cx="8" cy="8" r="3.2" fill="#000"/>
        </svg>
        <span><?= __('BLACKHOLE binlog relay') ?></span>
        <span style="font-weight:400;font-size:12px;opacity:0.85;margin-left:8px">
            <?= __('Issue') ?> <a href="https://git.istosia.com/pmacontrol/pmacontrol/issues/1212" style="color:#fde68a" target="_blank">#1212</a>
        </span>
    </div>
    <div class="bh-card-body">
        <p style="color:#475569;margin:6px 0 10px">
            <?= __('Convert a supervised MySQL/MariaDB slave into a BLACKHOLE binlog relay. The node keeps replicating from its master, discards each row immediately, and re-emits the event in its own binlog for downstream consumers (slaves, PITR, CDC). See') ?>
            <a href="https://git.istosia.com/pmacontrol/pmacontrol/wiki/binlog_relay_blackhole" target="_blank"><?= __('docs/binlog_relay_blackhole.md') ?></a>.
        </p>
    </div>
</div>

<?php if (!empty($relays)): ?>
<div class="bh-card">
    <div class="bh-card-head" style="background:linear-gradient(135deg,#1f2937,#4c1d95)">
        <span><?= __('Active BLACKHOLE relays') ?> (<?= count($relays) ?>)</span>
    </div>
    <div class="bh-card-body">
        <table class="bh-table">
            <tr>
                <th><?= __('Server') ?></th>
                <th><?= __('Hostname') ?></th>
                <th><?= __('Status') ?></th>
                <th></th>
            </tr>
            <?php foreach ($relays as $s): ?>
                <tr>
                    <td><?= Display::srv((int) $s['id']) ?></td>
                    <td><code><?= htmlspecialchars($s['hostname'] ?: $s['ip']) ?></code></td>
                    <td><span class="bh-pill relay"><?= __('BLACKHOLE relay') ?></span></td>
                    <td><a href="<?= LINK ?>slave/show/<?= (int) $s['id'] ?>/" class="btn btn-xs btn-default"><?= __('Inspect') ?></a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="bh-card">
    <div class="bh-card-head">
        <span><?= __('Candidate servers') ?> (<?= count($candidates) ?>)</span>
    </div>
    <div class="bh-card-body">
        <?php if (empty($candidates)): ?>
            <p style="color:#64748b;margin:0"><?= __('No candidate. All non-relay servers are excluded (proxies / VIPs / deleted).') ?></p>
        <?php else: ?>
            <table class="bh-table">
                <tr>
                    <th><?= __('Server') ?></th>
                    <th><?= __('Hostname / IP') ?></th>
                    <th><?= __('Convert') ?></th>
                </tr>
                <?php foreach ($candidates as $s): ?>
                    <tr data-server-id="<?= (int) $s['id'] ?>">
                        <td><?= Display::srv((int) $s['id']) ?></td>
                        <td><code><?= htmlspecialchars($s['hostname'] ?: $s['ip']) ?></code></td>
                        <td>
                            <button class="bh-btn-convert" data-id="<?= (int) $s['id'] ?>" data-name="<?= htmlspecialchars($s['display_name'] ?: $s['name']) ?>" data-dry="0"><?= __('Convert to BLACKHOLE') ?></button>
                            <button class="bh-btn-convert" style="background:#475569" data-id="<?= (int) $s['id'] ?>" data-name="<?= htmlspecialchars($s['display_name'] ?: $s['name']) ?>" data-dry="1"><?= __('Dry-run') ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

<div id="bh-progress-card" class="bh-card" style="display:none">
    <div class="bh-card-head" style="background:linear-gradient(135deg,#1e293b,#4c1d95)">
        <span id="bh-progress-title"><?= __('Conversion in progress') ?></span>
        <span id="bh-progress-pill" class="bh-pill pending" style="margin-left:8px">pending</span>
    </div>
    <div class="bh-card-body">
        <div id="bh-progress-meta" style="color:#64748b;font-size:12px;margin-bottom:6px"></div>
        <div id="bh-progress-log" class="bh-progress"></div>
    </div>
</div>

<div class="bh-card">
    <div class="bh-card-head" style="background:linear-gradient(135deg,#374151,#0f172a)">
        <span><?= __('Recent conversions') ?> (<?= count($conversions) ?>)</span>
    </div>
    <div class="bh-card-body">
        <?php if (empty($conversions)): ?>
            <p style="color:#64748b;margin:0"><?= __('No conversion recorded yet.') ?></p>
        <?php else: ?>
            <table class="bh-table">
                <tr>
                    <th>#</th>
                    <th><?= __('Server') ?></th>
                    <th><?= __('Status') ?></th>
                    <th><?= __('Tables') ?></th>
                    <th><?= __('Started') ?></th>
                    <th><?= __('Completed') ?></th>
                    <th></th>
                </tr>
                <?php foreach ($conversions as $c): ?>
                    <tr>
                        <td>#<?= (int) $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['display_name'] ?: $c['server_name'] ?: ('id ' . (int) $c['id_mysql_server'])) ?><?= ((int) $c['dry_run'] === 1) ? ' <small style="color:#64748b">(dry-run)</small>' : '' ?></td>
                        <td><span class="bh-pill <?= htmlspecialchars($c['status']) ?>"><?= htmlspecialchars($c['status']) ?></span></td>
                        <td><?= (int) $c['tables_converted'] ?> / <?= (int) $c['tables_total'] ?></td>
                        <td><?= htmlspecialchars($c['created_at']) ?></td>
                        <td><?= htmlspecialchars($c['completed_at'] ?: '—') ?></td>
                        <td><a href="#" class="bh-show-log" data-id="<?= (int) $c['id'] ?>"><?= __('Log') ?></a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var CSRF_FIELD = <?= json_encode($data['csrf_field']) ?>;
    var CSRF_TOKEN = <?= json_encode($data['csrf_token']) ?>;
    var LINK = <?= json_encode(LINK) ?>;
    var pollTimer = null;

    function fmtPill(status) {
        var p = document.getElementById('bh-progress-pill');
        p.className = 'bh-pill ' + status;
        p.textContent = status;
    }

    function pollStatus(conversionId) {
        if (pollTimer) clearTimeout(pollTimer);
        fetch(LINK + 'Blackhole/status/' + conversionId + '/', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.error) { console.error(d.error); return; }
                document.getElementById('bh-progress-card').style.display = 'block';
                document.getElementById('bh-progress-title').textContent =
                    'Conversion #' + d.id + ' — ' + d.server_name + (d.dry_run ? ' (DRY-RUN)' : '');
                document.getElementById('bh-progress-meta').textContent =
                    'tables: ' + d.tables_converted + ' / ' + d.tables_total
                    + (d.error_message ? '  •  ERROR: ' + d.error_message : '');
                fmtPill(d.status);

                var log = document.getElementById('bh-progress-log');
                log.style.display = 'block';
                log.innerHTML = (d.progress || []).map(function (step) {
                    var t = step.time_start || '';
                    var s = step.status || 'running';
                    var msg = (step.message || '').replace(/[<>&]/g, function (c) {
                        return ({ '<': '&lt;', '>': '&gt;', '&': '&amp;' })[c];
                    });
                    return '<div class="step ' + s + '">[' + t + '] ' + msg + '</div>';
                }).join('');
                log.scrollTop = log.scrollHeight;

                if (d.status === 'pending' || d.status === 'running') {
                    pollTimer = setTimeout(function () { pollStatus(conversionId); }, 1000);
                }
            })
            .catch(function (e) { console.error(e); });
    }

    document.querySelectorAll('.bh-btn-convert').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-id');
            var name = btn.getAttribute('data-name');
            var dry = btn.getAttribute('data-dry') === '1';
            var msg = dry
                ? 'Dry-run BLACKHOLE conversion on "' + name + '"? No table will actually be altered.'
                : 'Convert "' + name + '" to a BLACKHOLE binlog relay?\n\n'
                  + 'This will STOP SLAVE, ALTER every non-system table to ENGINE=BLACKHOLE, '
                  + 'set read_only=ON, then START SLAVE. The action is logged but data tables become empty engines.';
            if (!confirm(msg)) return;

            var fd = new FormData();
            fd.append(CSRF_FIELD, CSRF_TOKEN);
            fd.append('dry_run', dry ? '1' : '0');

            fetch(LINK + 'Blackhole/startConvert/' + id + '/', {
                method: 'POST', credentials: 'same-origin', body: fd,
            })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.error) { alert('Error: ' + d.error); return; }
                    pollStatus(d.id);
                })
                .catch(function (e) { alert('Network error: ' + e.message); });
        });
    });

    document.querySelectorAll('.bh-show-log').forEach(function (a) {
        a.addEventListener('click', function (ev) {
            ev.preventDefault();
            pollStatus(a.getAttribute('data-id'));
        });
    });
})();
</script>
