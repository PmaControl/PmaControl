<?php
/**
 * /Blackhole/index — Tools → BLACKHOLE.
 * List candidate servers, current relays, and recent conversion runs.
 * Issue #1212 (lots #1213-#1217).
 *
 * @var array $data
 */

use App\Library\Display;

$servers           = $data['servers'] ?? [];
$idleTargets       = $data['idle_targets'] ?? [];
$masterCandidates  = $data['master_candidates'] ?? [];
$conversions       = $data['conversions'] ?? [];

// One row per (server, channel) — already filtered server-side to
// keep only servers that have a master and are NOT in a
// master↔master 2-cycle. Relays are kept regardless so the
// operator can see them in the upper card.
$relays     = array_values(array_filter($servers, static fn ($s) => (int) $s['is_binlog_relay'] === 1));
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
                <th><?= __('Channel') ?></th>
                <th><?= __('Upstream master') ?></th>
                <th><?= __('Downstream slaves') ?></th>
                <th><?= __('Hostname') ?></th>
                <th><?= __('Status') ?></th>
                <th></th>
            </tr>
            <?php foreach ($relays as $s): ?>
                <tr>
                    <td><?= Display::srv((int) $s['id']) ?></td>
                    <td><code><?= htmlspecialchars($s['connection_name'] !== '' ? $s['connection_name'] : '(default)') ?></code></td>
                    <td><?= (int) ($s['master_id'] ?? 0) > 0 ? Display::srv((int) $s['master_id']) : '<small style="color:#94a3b8">—</small>' ?></td>
                    <td><strong><?= (int) ($s['downstream_slaves'] ?? 0) ?></strong></td>
                    <td><code><?= htmlspecialchars($s['hostname'] ?: $s['ip']) ?></code></td>
                    <td><span class="bh-pill relay"><?= __('BLACKHOLE relay') ?></span></td>
                    <td>
                        <a href="<?= LINK ?>slave/show/<?= (int) $s['id'] ?>/<?= htmlspecialchars((string) $s['connection_name']) ?>/" class="btn btn-xs btn-default"><?= __('Inspect') ?></a>
                        <button class="bh-btn-convert" data-id="<?= (int) $s['id'] ?>" data-name="<?= htmlspecialchars($s['display_name'] ?: $s['name']) ?>" data-dry="0" title="<?= __('Re-run the conversion (idempotent: BLACKHOLE→BLACKHOLE ALTERs are no-ops, the SHOW VARIABLES guarantee re-asserts sql_log_bin = OFF)') ?>"><?= __('Re-run') ?></button>
                        <button class="bh-btn-convert" style="background:#475569" data-id="<?= (int) $s['id'] ?>" data-name="<?= htmlspecialchars($s['display_name'] ?: $s['name']) ?>" data-dry="1" title="<?= __('Walk through the pipeline without mutating anything: SHOW VARIABLES is queried, ALTER statements are only logged.') ?>"><?= __('Dry-run') ?></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="bh-card">
    <div class="bh-card-head">
        <span><?= __('Candidate servers') ?> (<?= count($candidates) ?>)</span>
        <span style="font-weight:400;font-size:12px;opacity:0.85;margin-left:8px"><?= __('only servers with an upstream master, excluding master↔master pairs') ?></span>
    </div>
    <div class="bh-card-body">
        <?php if (empty($candidates)): ?>
            <p style="color:#64748b;margin:0"><?= __('No candidate. All non-relay servers either have no master, are proxies / VIPs, or are part of a master↔master pair.') ?></p>
        <?php else: ?>
            <table class="bh-table">
                <tr>
                    <th><?= __('Server') ?></th>
                    <th><?= __('Channel') ?></th>
                    <th><?= __('Upstream master') ?></th>
                    <th><?= __('Downstream slaves') ?></th>
                    <th><?= __('Hostname / IP') ?></th>
                    <th><?= __('Convert') ?></th>
                </tr>
                <?php foreach ($candidates as $s): ?>
                    <tr data-server-id="<?= (int) $s['id'] ?>">
                        <td><?= Display::srv((int) $s['id']) ?></td>
                        <td><code><?= htmlspecialchars($s['connection_name'] !== '' ? $s['connection_name'] : '(default)') ?></code></td>
                        <td><?= (int) ($s['master_id'] ?? 0) > 0 ? Display::srv((int) $s['master_id']) : '<small style="color:#94a3b8">—</small>' ?></td>
                        <td>
                            <?php $d = (int) ($s['downstream_slaves'] ?? 0); ?>
                            <strong<?= $d > 0 ? ' style="color:#4c1d95"' : '' ?>><?= $d ?></strong>
                            <?php if ($d > 0): ?>
                                <small style="color:#64748b">(<?= __('would benefit from a relay') ?>)</small>
                            <?php endif; ?>
                        </td>
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

<div class="bh-card">
    <div class="bh-card-head" style="background:linear-gradient(135deg,#0c4a6e,#4c1d95)">
        <span><i class="fa fa-plus-circle"></i> <?= __('Create new BLACKHOLE relay from scratch') ?></span>
        <span style="font-weight:400;font-size:12px;opacity:0.85;margin-left:8px"><?= __('schema-only dump from master → restore → BLACKHOLE → CHANGE MASTER TO → START SLAVE') ?></span>
    </div>
    <div class="bh-card-body">
        <p style="color:#475569;margin:6px 0 10px;font-size:13px">
            <?= __('Pick an empty target server and an upstream master. The pipeline refuses if the target has any non-system tables or any non-monitoring sessions. mysqldump runs schema-only (the relay never stores rows). Replication restarts from the master\'s current binlog position.') ?>
        </p>
        <table class="bh-table" style="margin-bottom:8px">
            <tr>
                <td style="width:25%"><label><strong><?= __('Target (empty server)') ?></strong></label></td>
                <td>
                    <select id="bh-gf-target" style="min-width:320px;padding:4px 6px">
                        <option value=""><?= __('— select an empty server —') ?></option>
                        <?php foreach ($idleTargets as $s): ?>
                            <option value="<?= (int) $s['id'] ?>"><?= htmlspecialchars(($s['display_name'] ?: $s['name']) . ' (' . ($s['hostname'] ?: $s['ip']) . ':' . (int) $s['port'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span id="bh-gf-target-status" style="margin-left:10px;font-size:12px"></span>
                </td>
            </tr>
            <tr>
                <td><label><strong><?= __('Upstream master') ?></strong></label></td>
                <td>
                    <select id="bh-gf-master" style="min-width:320px;padding:4px 6px">
                        <option value=""><?= __('— select a master —') ?></option>
                        <?php foreach ($masterCandidates as $s): ?>
                            <option value="<?= (int) $s['id'] ?>"><?= htmlspecialchars(($s['display_name'] ?: $s['name']) . ' (' . ($s['hostname'] ?: $s['ip']) . ':' . (int) $s['port'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label><?= __('Replication user (optional)') ?></label></td>
                <td>
                    <input id="bh-gf-repl-user" type="text" placeholder="<?= __('defaults to the master\'s mysql_server.login') ?>" style="min-width:320px;padding:4px 6px">
                    <small style="display:block;color:#94a3b8;margin-top:4px"><?= __('Must already exist on the master with REPLICATION SLAVE. Password is reused from the master\'s mysql_server entry.') ?></small>
                </td>
            </tr>
        </table>
        <button id="bh-gf-start" class="bh-btn-gf" disabled style="background:#4c1d95;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:12px;opacity:0.5"><?= __('Provision relay') ?></button>
        <button id="bh-gf-dry" class="bh-btn-gf" disabled style="background:#475569;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:12px;opacity:0.5"><?= __('Dry-run') ?></button>
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
        <span style="font-weight:400;font-size:12px;opacity:0.85;margin-left:8px">
            <?= __('also visible on') ?> <a href="<?= LINK ?>job/index" style="color:#fde68a">/job/index</a> — <?= __('centralized log + restart button') ?>
        </span>
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

    // Every AJAX call from this page MUST set X-Requested-With so
    // PersistentAuthSession::detectAjax() recognises it as AJAX and
    // skips the persistent-auth cookie rotation. Without this, every
    // poll (1 Hz) rotates the remember-me token and a follow-up
    // navigation lands with a verifier outside the 10 s grace window
    // → token_mismatch revoke + logout.
    var AJAX_HEADERS = { 'X-Requested-With': 'XMLHttpRequest' };
    function bhFetch(url, init) {
        init = init || {};
        init.credentials = init.credentials || 'same-origin';
        init.headers = Object.assign({}, init.headers || {}, AJAX_HEADERS);
        return fetch(url, init);
    }

    function fmtPill(status) {
        var p = document.getElementById('bh-progress-pill');
        p.className = 'bh-pill ' + status;
        p.textContent = status;
    }

    function pollStatus(conversionId) {
        if (pollTimer) clearTimeout(pollTimer);
        bhFetch(LINK + 'Blackhole/status/' + conversionId + '/ajax:true/')
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

            bhFetch(LINK + 'Blackhole/startConvert/' + id + '/ajax:true/', {
                method: 'POST', body: fd,
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

    // ---- Greenfield form ----
    var GF_TOKEN  = <?= json_encode($data['greenfield_token']) ?>;
    var gfTarget  = document.getElementById('bh-gf-target');
    var gfMaster  = document.getElementById('bh-gf-master');
    var gfUser    = document.getElementById('bh-gf-repl-user');
    var gfStart   = document.getElementById('bh-gf-start');
    var gfDry     = document.getElementById('bh-gf-dry');
    var gfStatus  = document.getElementById('bh-gf-target-status');

    function refreshGfButtons() {
        var ok = gfTarget.value && gfMaster.value && gfTarget.value !== gfMaster.value;
        [gfStart, gfDry].forEach(function (b) {
            b.disabled = !ok;
            b.style.opacity = ok ? '1' : '0.5';
            b.style.cursor  = ok ? 'pointer' : 'not-allowed';
        });
    }
    gfTarget.addEventListener('change', function () {
        refreshGfButtons();
        gfStatus.innerHTML = '';
        if (!gfTarget.value) return;
        gfStatus.innerHTML = '<span style="color:#94a3b8">checking…</span>';
        bhFetch(LINK + 'Blackhole/probeIdle/' + gfTarget.value + '/ajax:true/')
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.idle === true) {
                    gfStatus.innerHTML = '<span style="color:#16a34a">✓ idle — ok to provision</span>';
                } else {
                    gfStatus.innerHTML = '<span style="color:#dc2626">✗ ' + (d.reason || d.error || 'not idle').replace(/[<>&]/g, function (c) { return ({'<':'&lt;','>':'&gt;','&':'&amp;'})[c]; }) + '</span>';
                }
            })
            .catch(function (e) {
                gfStatus.innerHTML = '<span style="color:#dc2626">probe failed: ' + e.message + '</span>';
            });
    });
    gfMaster.addEventListener('change', refreshGfButtons);

    function startGreenfield(dry) {
        var targetId = gfTarget.value;
        var masterId = gfMaster.value;
        var targetTxt = gfTarget.options[gfTarget.selectedIndex].textContent;
        var masterTxt = gfMaster.options[gfMaster.selectedIndex].textContent;
        var msg = dry
            ? 'Dry-run greenfield provisioning of "' + targetTxt + '" replicating from "' + masterTxt + '"? Nothing will be mutated.'
            : 'Provision a fresh BLACKHOLE relay on "' + targetTxt + '" replicating from "' + masterTxt + '"?\n\n'
              + 'This will: 1) verify the target is idle, 2) mysqldump --no-data --master-data=2 the master schema into the target, '
              + '3) ALTER every table to ENGINE=BLACKHOLE, 4) CHANGE MASTER TO + START SLAVE.\n\n'
              + 'The target\'s previous schema (if any) will be overwritten.';
        if (!confirm(msg)) return;

        var fd = new FormData();
        fd.append(CSRF_FIELD, GF_TOKEN);
        fd.append('dry_run', dry ? '1' : '0');
        if (gfUser.value.trim()) fd.append('replication_user', gfUser.value.trim());

        bhFetch(LINK + 'Blackhole/startGreenfield/' + targetId + '/' + masterId + '/ajax:true/', {
            method: 'POST', body: fd,
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.error) { alert('Error: ' + d.error); return; }
                pollStatus(d.id);
            })
            .catch(function (e) { alert('Network error: ' + e.message); });
    }
    gfStart.addEventListener('click', function () { if (!gfStart.disabled) startGreenfield(false); });
    gfDry.addEventListener('click',   function () { if (!gfDry.disabled)   startGreenfield(true);  });
})();
</script>
