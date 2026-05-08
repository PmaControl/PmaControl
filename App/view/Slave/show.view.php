<?php

use App\Library\Security\CsrfRender;

use Glial\Html\Form\Form;
use App\Library\Display;

// -- helpers ------------------------------------------------------------------

$slaveBinlogAnalysisStartCsrfField = (string)($data['slave_binlog_analysis_start_csrf_field'] ?? '_csrf_token');
$slaveBinlogAnalysisStartCsrfToken = (string)($data['slave_binlog_analysis_start_csrf_token'] ?? '');
$slaveSetupSourceCsrfField = CsrfRender::field($data, 'slave_setup_source');
$slaveSetupSourceCsrfToken = CsrfRender::token($data, 'slave_setup_source');

$isMariaDB = isset($data['server_type']) && stripos($data['server_type'], 'mariadb') !== false;
$isMySQLNewSyntax = !$isMariaDB && isset($data['server_version']) && version_compare((string)$data['server_version'], '8.0.22', '>=');

if (empty($data['replication_name'])) {
    $show = $isMySQLNewSyntax ? 'SHOW REPLICA STATUS;' : 'SHOW SLAVE STATUS;';
} elseif ($isMariaDB) {
    $show = 'SHOW SLAVE \''.$data['replication_name'].'\' STATUS;';
} elseif ($isMySQLNewSyntax) {
    $show = 'SHOW REPLICA STATUS FOR CHANNEL \''.$data['replication_name'].'\';';
} else {
    $show = 'SHOW SLAVE STATUS FOR CHANNEL \''.$data['replication_name'].'\';';
}

// Key variable extraction (MySQL 5/MariaDB + MySQL 8 compat)
$sv = $data['slave'];

$io_running     = $sv['Slave_IO_Running']  ?? $sv['Replica_IO_Running']  ?? null;
$sql_running    = $sv['Slave_SQL_Running'] ?? $sv['Replica_SQL_Running'] ?? null;
$io_state       = $sv['Slave_IO_State']    ?? $sv['Replica_IO_State']    ?? '';
$seconds_behind = $sv['Seconds_Behind_Master'] ?? $sv['Seconds_Behind_Source'] ?? null;
$master_host    = $sv['Master_Host']  ?? $sv['Source_Host']  ?? '';
$master_port    = $sv['Master_Port']  ?? $sv['Source_Port']  ?? '';
$master_log     = $sv['Master_Log_File']       ?? $sv['Source_Log_File']       ?? '';
$read_pos       = $sv['Read_Master_Log_Pos']   ?? $sv['Read_Source_Log_Pos']   ?? '';
$relay_log      = $sv['Relay_Master_Log_File'] ?? $sv['Relay_Source_Log_File'] ?? '';
$exec_pos       = $sv['Exec_Master_Log_Pos']   ?? $sv['Exec_Source_Log_Pos']   ?? '';
$last_sql_error = $sv['Last_SQL_Error']  ?? '';
$last_io_error  = $sv['Last_IO_Error']   ?? '';
$last_error     = $sv['Last_Error']      ?? '';
// GTID detection: MariaDB exposes Using_Gtid (No|Slave_Pos|Current_Pos),
// MySQL exposes Auto_Position (0|1).
$using_gtid_raw = $sv['Using_Gtid'] ?? null;
$auto_position  = $sv['Auto_Position'] ?? null;

if ($using_gtid_raw !== null) {
    // MariaDB
    $gtid_active  = ($using_gtid_raw !== 'No' && $using_gtid_raw !== '');
    $gtid_display = $using_gtid_raw;
} elseif ($auto_position !== null) {
    // MySQL
    $gtid_active  = ($auto_position === '1' || $auto_position === 1);
    $gtid_display = $gtid_active ? 'Enabled (Auto Position)' : 'Disabled';
} else {
    $gtid_active  = false;
    $gtid_display = '';
}

$has_error = ($last_sql_error !== '' || $last_io_error !== '' || $last_error !== '');

// Determine overall health
$both_stopped_global = ($io_running !== 'Yes' && $sql_running !== 'Yes');
$health = 'ok';
if ($both_stopped_global) $health = 'stopped';
elseif ($io_running !== 'Yes' || $sql_running !== 'Yes') $health = 'critical';
elseif ($has_error) $health = 'critical';
elseif ($seconds_behind === null || $seconds_behind === 'NULL') $health = 'critical';
elseif ((int)$seconds_behind > 60) $health = 'warning';
elseif ((int)$seconds_behind > 0) $health = 'behind';
?>

<!-- Scoped styles for slave/show -->
<style>
:root { --clr-bg: #f8fafc; --clr-border: #e2e8f0; --clr-head: #1e293b; --clr-head-txt: #f1f5f9;
        --clr-ok: #10b981; --clr-warn: #f59e0b; --clr-crit: #ef4444; --clr-muted: #94a3b8;
        --clr-surface: #fff; --radius: 6px; }

.sv-card { background: var(--clr-surface); border: 1px solid var(--clr-border); border-radius: var(--radius);
           margin-bottom: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.sv-card-head { background: linear-gradient(135deg, #0f172a, #1e3a8a); color: #fff; padding: 10px 16px;
                font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: space-between; }
.sv-card-head i.fa { margin-right: 8px; opacity: .7; }
.sv-card-body { padding: 16px; }
.sv-card-body-flush { padding: 0; }

/* ---------- chart wrappers ---------- */
.sv-chart-wrap { position: relative; height: 180px; margin-bottom: 4px; }
.sv-chart-wrap canvas { position: absolute; top: 0; left: 0; width: 100% !important; height: 100% !important; }

/* ---------- health strip ---------- */
.sv-health-strip { height: 4px; }
.sv-health-strip.ok    { background: var(--clr-ok); }
.sv-health-strip.behind { background: var(--clr-ok); }
.sv-health-strip.warning { background: var(--clr-warn); }
.sv-health-strip.critical { background: var(--clr-crit); }
.sv-health-strip.stopped  { background: #3b82f6; }

/* ---------- metric tiles ---------- */
.sv-metrics { display: flex; flex-wrap: wrap; gap: 0; }
.sv-metric { flex: 1 1 50%; padding: 14px 16px; border-bottom: 1px solid var(--clr-border);
             border-right: 1px solid var(--clr-border); box-sizing: border-box; }
.sv-metric:nth-child(even) { border-right: none; }
.sv-metric-label { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--clr-muted); margin-bottom: 4px; }
.sv-metric-value { font-size: 16px; font-weight: 700; color: #1e293b; }
.sv-metric-value code { font-size: 12px; font-weight: 400; background: #f1f5f9; padding: 2px 6px; border-radius: 3px; }
.sv-metric-value small { font-size: 12px; font-weight: 400; color: var(--clr-muted); }
.sv-metric.full-width { flex: 1 1 100%; border-right: none; }

/* status dot */
.sv-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; vertical-align: middle; }
.sv-dot.ok   { background: var(--clr-ok); box-shadow: 0 0 6px rgba(16,185,129,.4); }
.sv-dot.fail { background: var(--clr-crit); box-shadow: 0 0 6px rgba(239,68,68,.4); }
.sv-dot.info { background: #3b82f6; box-shadow: 0 0 6px rgba(59,130,246,.4); }

/* pulsing halo — ring that expands and fades out */
.sv-dot.halo { position: relative; }
.sv-dot.halo::after {
    content: "";
    position: absolute;
    top: 50%; left: 50%;
    width: 100%; height: 100%;
    border-radius: 50%;
    border: 4px solid var(--clr-crit);
    transform: translate(-50%, -50%);
    animation: sv-halo-ring 2s ease-out infinite;
}
@keyframes sv-halo-ring {
    0%   { width: 100%; height: 100%; opacity: 1; }
    100% { width: 300%; height: 300%; opacity: 0; }
}

/* lag badge */
.sv-lag { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 15px; font-weight: 700; }
.sv-lag.ok      { background: #d1fae5; color: #065f46; }
.sv-lag.behind  { background: #d1fae5; color: #065f46; }
.sv-lag.warning { background: #fef3c7; color: #92400e; }
.sv-lag.critical{ background: #fee2e2; color: #991b1b; }
.sv-lag.stopped { background: #dbeafe; color: #1e40af; }

/* ---------- action groups ---------- */
.sv-action-group { margin-bottom: 14px; }
.sv-action-group:last-child { margin-bottom: 0; }
.sv-action-group-title { font-size: 11px; text-transform: uppercase; letter-spacing: .5px;
                         color: var(--clr-muted); margin-bottom: 8px; font-weight: 600; }
.sv-action-group .btn { margin-right: 4px; margin-bottom: 4px; }

/* ---------- error banner ---------- */
.sv-error-banner { background: #fef2f2; border-left: 4px solid var(--clr-crit); padding: 10px 14px;
                   color: #991b1b; font-size: 13px; margin: 0; }

/* ---------- collapsible sections ---------- */
.sv-collapse-head { background: #f1f5f9; color: #475569; padding: 10px 16px; font-size: 13px;
                    font-weight: 600; cursor: pointer; border: 1px solid var(--clr-border);
                    border-radius: var(--radius); margin-bottom: 16px; transition: border-radius .2s; }
.sv-collapse-head[aria-expanded="true"] { border-radius: var(--radius) var(--radius) 0 0; margin-bottom: 0; }
.sv-collapse-head:hover { background: #e2e8f0; }
.sv-collapse-head .fa { margin-right: 8px; transition: transform .2s; font-size: 11px; }
.sv-collapse-head[aria-expanded="true"] .fa-chevron-right { transform: rotate(90deg); }
.sv-collapse-head .badge { background: #cbd5e1; color: #475569; font-size: 11px; margin-left: 8px; }
.sv-collapse-body { border: 1px solid var(--clr-border); border-top: none;
                    border-radius: 0 0 var(--radius) var(--radius); margin-bottom: 16px; overflow: hidden; }

/* ---------- SHOW SLAVE STATUS variable explorer ---------- */
.sv-vars { margin-bottom: 16px; }
.sv-vars-head { background: linear-gradient(135deg, #0f172a, #1e3a8a); color: #fff;
                padding: 12px 16px; border-radius: var(--radius) var(--radius) 0 0;
                display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.sv-vars-head-title { font-size: 14px; font-weight: 600; }
.sv-vars-head-title i { margin-right: 8px; opacity: .7; }
.sv-vars-head-title .badge { background: rgba(255,255,255,.2); font-size: 11px; margin-left: 6px; font-weight: 400; }
.sv-vars-search { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25);
                  color: #fff; padding: 5px 12px 5px 30px; border-radius: 14px; font-size: 12px;
                  width: 220px; outline: none; transition: all .2s; }
.sv-vars-search:focus { background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.5); width: 260px; }
.sv-vars-search::placeholder { color: rgba(255,255,255,.5); }
.sv-vars-search-wrap { position: relative; }
.sv-vars-search-wrap .fa-search { position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
                                   color: rgba(255,255,255,.4); font-size: 12px; pointer-events: none; }
.sv-vars-body { border: 1px solid var(--clr-border); border-top: none;
                border-radius: 0 0 var(--radius) var(--radius); background: #fff; max-height: 520px; overflow-y: auto; }
.sv-vars-cat { border-bottom: 1px solid var(--clr-border); }
.sv-vars-cat:last-child { border-bottom: none; }
.sv-vars-cat-head { background: #f8fafc; padding: 6px 16px; font-size: 11px; font-weight: 700;
                    text-transform: uppercase; letter-spacing: .5px; color: #64748b;
                    position: sticky; top: 0; z-index: 1; border-bottom: 1px solid var(--clr-border); }
.sv-vars-cat-head .sv-vars-cat-count { font-weight: 400; opacity: .7; margin-left: 4px; }
.sv-vars-row { display: flex; padding: 4px 16px; font-size: 12px; border-bottom: 1px solid #f1f5f9; align-items: baseline; }
.sv-vars-row:last-child { border-bottom: none; }
.sv-vars-row:hover { background: #f8fafc; }
.sv-vars-row.sv-hidden { display: none; }
.sv-vars-name { width: 45%; color: #334155; font-weight: 600; padding-right: 8px; word-break: break-all; }
.sv-vars-name .sv-alias { color: #94a3b8; font-weight: 400; }
.sv-vars-val  { width: 55%; color: #475569; word-break: break-all; font-family: monospace; font-size: 11px; }
.sv-vars-val.sv-val-empty { color: #cbd5e1; font-style: italic; }
.sv-vars-val.sv-val-yes { color: #059669; font-weight: 600; }
.sv-vars-val.sv-val-no  { color: #dc2626; font-weight: 600; }
.sv-vars-val.sv-val-num { color: #1e40af; }
.sv-vars-val.sv-val-err { color: #dc2626; background: #fef2f2; padding: 2px 6px; border-radius: 3px; }
.sv-vars-no-match { padding: 24px; text-align: center; color: var(--clr-muted); font-size: 13px; display: none; }
.sv-vars-body::-webkit-scrollbar { width: 6px; }
.sv-vars-body::-webkit-scrollbar-track { background: #f1f5f9; }
.sv-vars-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
.sv-vars-body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* ---------- load-more button ---------- */
.sv-btn-load { background: transparent; border: 1px dashed var(--clr-border); color: var(--clr-muted);
               padding: 4px 14px; border-radius: 14px; font-size: 12px; transition: all .15s; }
.sv-btn-load:hover, .sv-btn-load:focus { background: var(--clr-head); color: #fff; border-color: var(--clr-head); }

/* ---------- source tabs ---------- */
.sv-tabs { display: flex; flex-wrap: wrap; gap: 0; margin-bottom: 16px; border-bottom: 2px solid var(--clr-border);
           background: var(--clr-surface); border-radius: var(--radius) var(--radius) 0 0;
           box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.sv-tab { padding: 10px 18px; font-size: 13px; font-weight: 600; color: #64748b; cursor: pointer;
          border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all .15s;
          text-decoration: none; display: flex; align-items: center; gap: 6px; }
.sv-tab:hover { color: #1e293b; background: #f8fafc; text-decoration: none; }
.sv-tab.active { color: #1e3a8a; border-bottom-color: #1e3a8a; background: #f0f4ff; }
.sv-tab .sv-tab-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.sv-tab .sv-tab-dot.ok { background: var(--clr-ok); }
.sv-tab .sv-tab-dot.behind { background: var(--clr-ok); }
.sv-tab .sv-tab-dot.warning { background: var(--clr-warn); }
.sv-tab .sv-tab-dot.critical { background: var(--clr-crit); }
.sv-tab .sv-tab-dot.stopped { background: #3b82f6; }
.sv-tab .sv-tab-lag { font-size: 11px; font-weight: 400; color: var(--clr-muted); }
.sv-tab-add { color: var(--clr-muted); font-size: 15px; padding: 10px 14px; }
.sv-tab-add:hover { color: var(--clr-ok); }

/* ---------- new source form ---------- */
.sv-new-source { max-width: 700px; }
.sv-new-source .form-group { margin-bottom: 12px; }
.sv-new-source label { font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase;
                        letter-spacing: .3px; margin-bottom: 4px; display: block; }
.sv-new-source .form-control { font-size: 13px; }
.sv-new-source .sv-form-row { display: flex; gap: 12px; }
.sv-new-source .sv-form-row > * { flex: 1; }

</style>


<!-- ============================================================
     0) SOURCE TABS (multi-source replication)
     ============================================================ -->
<div class="sv-tabs">
    <?php if (!empty($data['all_connections'])): ?>
    <?php foreach ($data['all_connections'] as $conn):
        $cn = $conn['name'];
        $label = $cn !== '' ? $cn : __('default');
        $isActive = ($cn === $data['replication_name']);
        $lagLabel = '';
        if ($conn['lag'] !== null && $conn['lag'] !== 'NULL' && $conn['health'] !== 'stopped') {
            $lagLabel = ((int)$conn['lag'] === 0) ? '0s' : (int)$conn['lag'].'s';
        }
    ?>
    <a class="sv-tab<?= $isActive ? ' active' : '' ?>"
       href="<?= LINK ?>slave/show/<?= $data['id_mysql_server'] ?>/<?= urlencode($cn) ?>/">
        <span class="sv-tab-dot <?= $conn['health'] ?>"></span>
        <?= htmlspecialchars($label) ?>
        <?php if ($lagLabel): ?><span class="sv-tab-lag"><?= $lagLabel ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
    <a class="sv-tab sv-tab-add<?= (($data['replication_name'] ?? '') === '__new__') ? ' active' : '' ?>"
       href="<?= LINK ?>slave/show/<?= $data['id_mysql_server'] ?>/__new__/"
       title="<?= __('Add replication source') ?>">
        <i class="fa fa-plus"></i>
    </a>
</div>

<?php if (($data['replication_name'] ?? '') === '__new__'): ?>
<!-- ============================================================
     NEW SOURCE FORM
     ============================================================ -->
<div class="sv-card">
    <div class="sv-card-head">
        <span><i class="fa fa-plus-circle"></i> <?= __('Set up new replication source') ?></span>
    </div>
    <div class="sv-card-body">
        <form class="sv-new-source" method="POST"
              action="<?= LINK ?>slave/setupSource/<?= $data['id_mysql_server'] ?>/">
            <input type="hidden" name="<?= $slaveSetupSourceCsrfField ?>" value="<?= $slaveSetupSourceCsrfToken ?>">

            <!-- Step 1: Source server -->
            <fieldset style="border:1px solid var(--clr-border);border-radius:var(--radius);padding:16px;margin-bottom:16px">
                <legend style="font-size:12px;font-weight:700;color:#1e3a8a;text-transform:uppercase;letter-spacing:.5px;padding:0 8px;width:auto">
                    <i class="fa fa-server"></i> <?= __('Source server') ?>
                </legend>

                <div class="form-group" style="margin-bottom:12px">
                    <label><?= __('Master server') ?></label>
                    <select name="master_server_id" id="sv-master-server-select" class="form-control selectpicker"
                            data-live-search="true" data-size="10" data-width="100%"
                            data-style="btn-default">
                        <option value="" data-host="" data-port=""><?= __('-- Select a server --') ?></option>
                        <?php
                        $availableServers = $data['available_servers'] ?? [];
                        $currentEnv = '';
                        foreach ($availableServers as $srv):
                            if ($srv['environment'] !== $currentEnv):
                                if ($currentEnv !== '') echo '</optgroup>';
                                $currentEnv = $srv['environment'];
                                echo '<optgroup label="'.htmlspecialchars($currentEnv).'">';
                            endif;
                        ?>
                        <option value="<?= (int)$srv['id'] ?>"
                                data-host="<?= htmlspecialchars($srv['ip']) ?>"
                                data-port="<?= htmlspecialchars($srv['port']) ?>"
                                data-subtext="<?= htmlspecialchars($srv['ip'].':'.$srv['port']) ?>">
                            <?= htmlspecialchars($srv['display_name'] ?: $srv['ip']) ?>
                        </option>
                        <?php endforeach;
                        if ($currentEnv !== '') echo '</optgroup>';
                        ?>
                    </select>
                </div>

                <div class="sv-form-row">
                    <div class="form-group">
                        <label><?= __('Master host') ?> <small style="text-transform:none;font-weight:400">(<?= __('auto-filled from selection') ?>)</small></label>
                        <input type="text" name="master_host" id="sv-master-host" class="form-control" required
                               placeholder="10.68.68.180">
                    </div>
                    <div class="form-group">
                        <label><?= __('Master port') ?></label>
                        <input type="number" name="master_port" id="sv-master-port" class="form-control" value="3306" min="1" max="65535">
                    </div>
                </div>
            </fieldset>

            <!-- Step 2: Connection -->
            <fieldset style="border:1px solid var(--clr-border);border-radius:var(--radius);padding:16px;margin-bottom:16px">
                <legend style="font-size:12px;font-weight:700;color:#1e3a8a;text-transform:uppercase;letter-spacing:.5px;padding:0 8px;width:auto">
                    <i class="fa fa-plug"></i> <?= __('Connection') ?>
                </legend>

                <div class="sv-form-row">
                    <div class="form-group">
                        <label><?= __('Connection name') ?></label>
                        <input type="text" name="connection_name" class="form-control" required
                               placeholder="e.g. production_fr" pattern="[a-zA-Z0-9_\-]+">
                    </div>
                    <div class="form-group">
                        <label><?= __('Replication user') ?></label>
                        <input type="text" name="master_user" class="form-control" required placeholder="repl">
                    </div>
                    <div class="form-group">
                        <label><?= __('Password') ?></label>
                        <input type="password" name="master_password" class="form-control" required>
                    </div>
                </div>
            </fieldset>

            <!-- Step 3: Options -->
            <fieldset style="border:1px solid var(--clr-border);border-radius:var(--radius);padding:16px;margin-bottom:16px">
                <legend style="font-size:12px;font-weight:700;color:#1e3a8a;text-transform:uppercase;letter-spacing:.5px;padding:0 8px;width:auto">
                    <i class="fa fa-sliders"></i> <?= __('Options') ?>
                </legend>

                <div class="sv-form-row">
                    <div class="form-group">
                        <label><?= __('Replicate database') ?> <small style="text-transform:none;font-weight:400">(<?= __('optional, comma-separated') ?>)</small></label>
                        <input type="text" name="replicate_do_db" class="form-control" placeholder="db1,db2">
                    </div>
                    <div class="form-group">
                        <label><?= __('Replicate rewrite') ?> <small style="text-transform:none;font-weight:400">(<?= __('optional') ?> source->target)</small></label>
                        <input type="text" name="replicate_rewrite_db" class="form-control" placeholder="production->production_fr">
                    </div>
                </div>

                <div class="sv-form-row" style="margin-top:8px">
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                            <input type="checkbox" name="use_gtid" value="1" checked> <?= __('Use GTID') ?>
                        </label>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                            <input type="checkbox" name="use_ssl" value="1" checked> <?= __('Use SSL') ?>
                        </label>
                    </div>
                </div>
            </fieldset>

            <div style="display:flex;gap:8px;align-items:center">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-play"></i> <?= __('Create & Start replication') ?>
                </button>
                <a class="btn btn-default" href="<?= LINK ?>slave/show/<?= $data['id_mysql_server'] ?>/">
                    <?= __('Cancel') ?>
                </a>
            </div>
        </form>
    </div>
</div>
<script>
$(document).ready(function() {
    var $select = $('#sv-master-server-select');
    if ($.fn.selectpicker) $select.selectpicker();
    $select.on('changed.bs.select change', function() {
        var $opt = $(this).find('option:selected');
        var host = $opt.data('host') || '';
        var port = $opt.data('port') || '';
        if (host) $('#sv-master-host').val(host);
        if (port) $('#sv-master-port').val(port);
    });
});
</script>
<?php else: ?>
<!-- ============================================================
     1) REPLICATION LAG GRAPHS
     ============================================================ -->
<div class="sv-card">
    <div class="sv-health-strip <?= $health ?>"></div>
    <div class="sv-card-head">
        <span><i class="fa fa-area-chart"></i> <?= __("Replication lag") ?></span>
        <?php
        $oldestDay = null;
        if (!empty($data['graph'])) {
            $days = array_keys($data['graph']);
            sort($days);
            $oldestDay = $days[0];
        }
        ?>
        <button type="button" class="sv-btn-load" id="btn-load-more-days"
                data-server="<?= $data['id_mysql_server'] ?>"
                data-replication="<?= htmlspecialchars($data['replication_name'] ?? '') ?>"
                data-oldest="<?= $oldestDay ?>">
            <i class="fa fa-plus"></i> <?= __('Load previous day') ?>
        </button>
    </div>
    <div class="sv-card-body" id="slave-graphs-container">
        <?php
        if (!empty($data['graph'])) {
            foreach ($data['graph'] as $slave) {
                echo '<div class="sv-chart-wrap"><canvas id="myChart'.$slave['id_mysql_server'].crc32(($slave['connection_name'] ?? '').$slave['day']).'"></canvas></div>';
            }
        } else {
            echo '<div style="text-align:center;padding:24px;color:var(--clr-muted)"><i class="fa fa-line-chart"></i> '.__('No replication lag data available').'</div>';
        }
        ?>
    </div>
</div>


<!-- ============================================================
     2) STATUS  +  ACTIONS
     ============================================================ -->
<div class="row">

    <!-- ---- Status ---- -->
    <div class="col-md-6">
        <div class="sv-card">
            <div class="sv-card-head">
                <span><i class="fa fa-heartbeat"></i> <?= __("Replication status") ?></span>
            </div>
            <?php if (!empty($sv)): ?>
            <div class="sv-card-body-flush">
                <div class="sv-metrics">
                    <?php
                    $io_dot  = ($io_running === 'Yes')  ? 'ok' : ($both_stopped_global ? 'info' : 'fail');
                    $sql_dot = ($sql_running === 'Yes') ? 'ok' : ($both_stopped_global ? 'info' : 'fail');
                    ?>
                    <div class="sv-metric">
                        <div class="sv-metric-label">IO Thread</div>
                        <div class="sv-metric-value" id="sv-live-io">
                            <?= Display::statusDot($io_dot) ?>
                            <?= $io_running ?>
                            <?php if ($io_state): ?>
                                <small>&mdash; <?= htmlspecialchars($io_state) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="sv-metric">
                        <div class="sv-metric-label">SQL Thread</div>
                        <div class="sv-metric-value" id="sv-live-sql">
                            <?= Display::statusDot($sql_dot) ?>
                            <?= $sql_running ?>
                        </div>
                    </div>
                    <div class="sv-metric">
                        <div class="sv-metric-label"><?= __("Source") ?></div>
                        <div class="sv-metric-value"><?= htmlspecialchars($master_host) ?>:<?= htmlspecialchars($master_port) ?></div>
                    </div>
                    <div class="sv-metric">
                        <div class="sv-metric-label"><?= __("Lag") ?></div>
                        <div class="sv-metric-value">
                            <?php
                            $lagText = Display::humanDuration($seconds_behind);
                            $lagClass = $health;
                            ?>
                            <span class="sv-lag <?= $lagClass ?>" id="sv-live-lag"><?= $lagText ?></span>
                        </div>
                    </div>
                    <div class="sv-metric">
                        <div class="sv-metric-label">Binlog read <small>(IO)</small></div>
                        <div class="sv-metric-value"><code><?= htmlspecialchars($master_log) ?></code> <small>pos <?= $read_pos ?></small></div>
                    </div>
                    <div class="sv-metric">
                        <div class="sv-metric-label">Relay exec <small>(SQL)</small></div>
                        <div class="sv-metric-value"><code><?= htmlspecialchars($relay_log) ?></code> <small>pos <?= $exec_pos ?></small></div>
                    </div>
                    <?php if (!empty($data['binlog_gap'])): ?>
                    <div class="sv-metric full-width">
                        <div class="sv-metric-label"><?= __("Relay gap") ?> <small>(<?= __('lag last hour') ?> →)</small></div>
                        <div class="sv-metric-value" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                            <div>
                            <?php
                            $gap = $data['binlog_gap'];
                            $gb = $gap['bytes'];
                            if ($gb >= 1073741824) {
                                $size_str = round($gb / 1073741824, 2).' GB';
                            } elseif ($gb >= 1048576) {
                                $size_str = round($gb / 1048576, 1).' MB';
                            } elseif ($gb >= 1024) {
                                $size_str = round($gb / 1024, 1).' KB';
                            } else {
                                $size_str = $gb.' B';
                            }
                            $gap_class = '';
                            if ($gb === 0) {
                                $gap_class = 'color:var(--clr-ok)';
                            } elseif ($gb < 104857600) {
                                $gap_class = 'color:var(--clr-warn)';
                            } else {
                                $gap_class = 'color:var(--clr-crit)';
                            }
                            ?>
                            <span style="<?= $gap_class ?>;font-weight:700"><?= !empty($gap['estimate']) ? '~' : '' ?><?= $size_str ?></span>
                            <?php if ($gap['files'] > 0): ?>
                                <small>&mdash; <?= $gap['files'] ?> <?= __('binlog file(s) behind') ?></small>
                            <?php else: ?>
                                <small>&mdash; <?= __('same file') ?></small>
                            <?php endif; ?>
                            <?php if (!empty($gap['estimate'])): ?>
                                <small style="color:var(--clr-muted)">(<?= __('estimate') ?>)</small>
                            <?php endif; ?>
                            </div>
                            <?php if (!empty($data['sparkline'])): ?>
                            <canvas id="sv-sparkline" style="flex:1;min-width:120px;height:32px"></canvas>
                            <div id="sv-eta" style="font-size:12px;white-space:nowrap"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($gtid_display !== ''): ?>
                    <div class="sv-metric">
                        <div class="sv-metric-label">GTID</div>
                        <div class="sv-metric-value"><?= htmlspecialchars($gtid_display) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php
                    $ssl_allowed = $sv['Master_SSL_Allowed'] ?? $sv['Source_SSL_Allowed'] ?? $sv['Source_TLS_Version'] ?? '';
                    $ssl_cipher  = $sv['Master_SSL_Cipher'] ?? $sv['Source_SSL_Cipher'] ?? '';
                    ?>
                    <div class="sv-metric">
                        <div class="sv-metric-label">SSL</div>
                        <div class="sv-metric-value">
                            <?php
                            if ($ssl_allowed === 'Yes' || !empty($ssl_cipher)) {
                                echo Display::statusDot('ok').'Yes';
                                if ($ssl_cipher) echo ' <small>('.htmlspecialchars($ssl_cipher).')</small>';
                            } elseif ($ssl_allowed === 'Ignored') {
                                echo '<span style="color:var(--clr-warn)">Ignored</span>';
                            } else {
                                echo Display::statusDot('fail').'No';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php if ($has_error):
                    $errors = [];
                    if ($last_io_error !== '')  $errors[] = ['IO', $last_io_error];
                    if ($last_sql_error !== '') $errors[] = ['SQL', $last_sql_error];
                    if (empty($errors) && $last_error !== '') $errors[] = ['', $last_error];
                    foreach ($errors as $err): ?>
                <div class="sv-error-banner">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?php if ($err[0]): ?><b><?= $err[0] ?>:</b> <?php endif; ?>
                    <?= htmlspecialchars($err[1]) ?>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <?php else: ?>
            <div class="sv-card-body">
                <span style="color:var(--clr-crit)"><i class="fa fa-exclamation-circle"></i> <?= __("Server unreachable") ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ---- Actions ---- -->
    <div class="col-md-6">
        <div class="sv-card">
            <div class="sv-card-head">
                <span><i class="fa fa-cogs"></i> <?= __("Actions") ?></span>
            </div>
            <div class="sv-card-body">

                <div class="sv-action-group">
                    <div class="sv-action-group-title"><?= __("Topology") ?></div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <?php
                        \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable",
                            array("mysql_server", "id", array("data-width" => "auto", "mysql_server_specify" => $data['mysql_server_specify'])));
                        ?>
                        <span class="glyphicon glyphicon-arrow-right" style="color:var(--clr-muted)"></span>
                        <?php
                        \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable",
                            array("mysql_slave", "server", array("data-width" => "auto", "mysql_server_specify" => $data['id_slave'])));
                        ?>
                        <button type="button" class="btn btn-primary btn-sm">
                            <i class="fa fa-exchange"></i> Switch
                        </button>
                    </div>
                </div>

                <div class="sv-action-group">
                    <div class="sv-action-group-title"><?= __("Replication") ?></div>
                    <a class="btn btn-success" href="<?= LINK ?><?= $data['class'] ?>/startSlave/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/">
                        <i class="fa fa-play"></i> START
                    </a>
                    <a class="btn btn-warning" href="<?= LINK ?><?= $data['class'] ?>/stopSlave/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/">
                        <i class="fa fa-stop"></i> STOP
                    </a>
                    <a class="btn btn-danger btn-sm" href="<?= LINK ?><?= $data['class'] ?>/skipCounter/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/"
                       onclick="return confirm('<?= __('Skip one transaction. Are you sure?') ?>')">
                        <i class="fa fa-forward"></i> SKIP
                    </a>
                </div>

                <div class="sv-action-group">
                    <div class="sv-action-group-title">GTID</div>
                    <?php if ($gtid_active): ?>
                        <a class="btn btn-default btn-sm disabled" disabled><i class="fa fa-check"></i> <?= __('Activate') ?></a>
                        <a class="btn btn-default btn-sm" href="<?= LINK ?><?= $data['class'] ?>/deactivateGtid/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/">
                            <i class="fa fa-times" style="color:var(--clr-crit)"></i> <?= __('Deactivate') ?>
                        </a>
                    <?php else: ?>
                        <a class="btn btn-default btn-sm" href="<?= LINK ?><?= $data['class'] ?>/activateGtid/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/">
                            <i class="fa fa-check" style="color:var(--clr-ok)"></i> <?= __('Activate') ?>
                        </a>
                        <a class="btn btn-default btn-sm disabled" disabled><i class="fa fa-times"></i> <?= __('Deactivate') ?></a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($data['db_on_master'])): ?>
                <div class="sv-action-group">
                    <div class="sv-action-group-title"><?= __("Rebuild") ?></div>
                    <a class="btn btn-warning btn-sm" href="<?= LINK ?><?= $data['class'] ?>/reload/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/"
                       onclick="return confirm('<?= __('Reload from master. Are you sure?') ?>')">
                        <i class="fa fa-refresh"></i> <?= __('Reload from Master') ?>
                        <small>(<?= implode(',', $data['db_on_master']) ?>)</small>
                    </a>
                </div>
                <?php endif; ?>

                <div class="sv-action-group">
                    <div class="sv-action-group-title">
                        <?= __("Parallel threads") ?>
                        <small style="text-transform:none;letter-spacing:0;font-weight:400;color:#94a3b8">
                            &mdash; <?= __('current') ?>: <b style="color:#1e40af"><?= $data['parallel_threads'] ?? 0 ?></b>
                            <?php if (!empty($data['cpu_count'])): ?>
                                | CPU: <?= $data['cpu_count'] ?> (max: <?= min(50, $data['cpu_count'] * 2) ?>)
                            <?php endif; ?>
                        </small>
                    </div>
                    <?php
                    $maxThreads = 50;
                    if (!empty($data['cpu_count']) && $data['cpu_count'] > 0) {
                        $maxThreads = min(50, $data['cpu_count'] * 2);
                    }
                    $currentThreads = $data['parallel_threads'] ?? 0;
                    ?>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <input type="number" id="sv-parallel-threads" min="0" max="<?= $maxThreads ?>" value="<?= $currentThreads ?>"
                               style="width:70px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;text-align:center">
                        <input type="range" id="sv-parallel-range" min="0" max="<?= $maxThreads ?>" value="<?= $currentThreads ?>"
                               style="flex:1;min-width:100px;accent-color:#1e3a8a">
                        <?php if (($data['parallel_mode'] ?? null) !== null):
                            $modes = ['conservative', 'optimistic', 'aggressive', 'minimal', 'none'];
                        ?>
                        <select id="sv-parallel-mode" style="padding:4px 6px;border:1px solid #cbd5e1;border-radius:4px;font-size:12px">
                            <?php foreach ($modes as $m): ?>
                            <option value="<?= $m ?>"<?= ($data['parallel_mode'] === $m) ? ' selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                        <a class="btn btn-primary btn-sm" id="sv-parallel-apply" href="#"
                           data-base-threads="<?= LINK ?><?= $data['class'] ?>/setParallelThreads/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/">
                            <i class="fa fa-check"></i> <?= __('Apply') ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>


<!-- ============================================================
     3) SHOW SLAVE STATUS  (categorized explorer with search)
     ============================================================ -->
<?php
// Categorize SHOW SLAVE STATUS variables
$varCategories = [
    'Thread Status' => [
        'Slave_IO_Running','Replica_IO_Running','Slave_SQL_Running','Replica_SQL_Running',
        'Slave_IO_State','Replica_IO_State','Slave_SQL_Running_State','Replica_SQL_Running_State',
        'Seconds_Behind_Master','Seconds_Behind_Source','SQL_Delay','SQL_Remaining_Delay',
    ],
    'Source Connection' => [
        'Master_Host','Source_Host','Master_User','Source_User','Master_Port','Source_Port',
        'Master_Server_Id','Source_Server_Id','Master_UUID','Source_UUID',
        'Master_Bind','Source_Bind','Master_SSL_Allowed','Source_SSL_Allowed',
        'Master_SSL_CA_File','Master_SSL_CA_Path','Master_SSL_Cert','Master_SSL_Cipher',
        'Master_SSL_Key','Master_SSL_Crl','Master_SSL_Crlpath','Master_SSL_Verify_Server_Cert',
        'Master_TLS_Version','Source_TLS_Version','Master_public_key_path',
        'Connect_Retry','Master_Retry_Count','Source_Retry_Count',
        'Auto_Position',
    ],
    'Binlog Position' => [
        'Master_Log_File','Source_Log_File','Read_Master_Log_Pos','Read_Source_Log_Pos',
        'Relay_Master_Log_File','Relay_Source_Log_File','Exec_Master_Log_Pos','Exec_Source_Log_Pos',
        'Relay_Log_File','Relay_Log_Pos','Relay_Log_Space',
        'Until_Condition','Until_Log_File','Until_Log_Pos',
    ],
    'GTID' => [
        'Using_Gtid','Gtid_IO_Pos','Gtid_Slave_Pos',
        'Retrieved_Gtid_Set','Executed_Gtid_Set',
    ],
    'Filters' => [
        'Replicate_Do_DB','Replicate_Ignore_DB','Replicate_Do_Table','Replicate_Ignore_Table',
        'Replicate_Wild_Do_Table','Replicate_Wild_Ignore_Table',
        'Replicate_Ignore_Server_Ids','Replicate_Do_Domain_Ids','Replicate_Ignore_Domain_Ids',
        'Channel_Name','Connection_name',
    ],
    'Errors' => [
        'Last_Errno','Last_Error','Last_IO_Errno','Last_IO_Error',
        'Last_SQL_Errno','Last_SQL_Error',
    ],
    'Parallel Replication' => [
        'Parallel_Mode','Slave_DDL_Groups','Slave_Non_Transactional_Groups','Slave_Transactional_Groups',
    ],
];

// Alias pairs: old name => new name (Master_/Slave_ => Source_/Replica_)
$aliasPairs = [
    'Slave_IO_Running'       => 'Replica_IO_Running',
    'Slave_SQL_Running'      => 'Replica_SQL_Running',
    'Slave_IO_State'         => 'Replica_IO_State',
    'Slave_SQL_Running_State'=> 'Replica_SQL_Running_State',
    'Seconds_Behind_Master'  => 'Seconds_Behind_Source',
    'Master_Host'            => 'Source_Host',
    'Master_User'            => 'Source_User',
    'Master_Port'            => 'Source_Port',
    'Master_Server_Id'       => 'Source_Server_Id',
    'Master_UUID'            => 'Source_UUID',
    'Master_Bind'            => 'Source_Bind',
    'Master_SSL_Allowed'     => 'Source_SSL_Allowed',
    'Master_TLS_Version'     => 'Source_TLS_Version',
    'Master_Retry_Count'     => 'Source_Retry_Count',
    'Master_Log_File'        => 'Source_Log_File',
    'Read_Master_Log_Pos'    => 'Read_Source_Log_Pos',
    'Relay_Master_Log_File'  => 'Relay_Source_Log_File',
    'Exec_Master_Log_Pos'    => 'Exec_Source_Log_Pos',
    'Last_IO_Errno'          => 'Last_IO_Errno',
    'Last_IO_Error'          => 'Last_IO_Error',
    'Last_SQL_Errno'         => 'Last_SQL_Errno',
    'Last_SQL_Error'         => 'Last_SQL_Error',
];
// Build reverse map too
$aliasReverse = array_flip($aliasPairs);

// Merge aliases: if both exist with same value, combine into "Old / New" => value
// and mark the second one as consumed
$mergedSlave = [];
$consumed = [];
foreach ($data['slave'] as $k => $v) {
    if (isset($consumed[$k])) continue;

    $alias = $aliasPairs[$k] ?? ($aliasReverse[$k] ?? null);
    if ($alias && array_key_exists($alias, $data['slave']) && (string)$data['slave'][$alias] === (string)$v) {
        // Same value: merge names
        $mergedSlave[$k.' / '.$alias] = $v;
        $consumed[$alias] = true;
    } else {
        $mergedSlave[$k] = $v;
    }
}

// Assign each variable to a category; unknowns go to "Other"
$categorized = [];
$assigned = [];
foreach ($varCategories as $cat => $keys) {
    $categorized[$cat] = [];
    foreach ($keys as $k) {
        // Check both exact key and merged key (contains $k)
        foreach ($mergedSlave as $mk => $mv) {
            $names = explode(' / ', $mk);
            if (in_array($k, $names) && !isset($assigned[$mk])) {
                $categorized[$cat][$mk] = $mv;
                $assigned[$mk] = true;
            }
        }
    }
    if (empty($categorized[$cat])) {
        unset($categorized[$cat]);
    }
}
$other = [];
foreach ($mergedSlave as $k => $v) {
    if (!isset($assigned[$k])) {
        $other[$k] = $v;
    }
}
if (!empty($other)) {
    $categorized['Other'] = $other;
}

$catIcons = [
    'Thread Status'        => 'fa-heartbeat',
    'Source Connection'     => 'fa-plug',
    'Binlog Position'      => 'fa-database',
    'GTID'                 => 'fa-link',
    'Filters'              => 'fa-filter',
    'Errors'               => 'fa-exclamation-triangle',
    'Parallel Replication'  => 'fa-code-fork',
    'Other'                => 'fa-ellipsis-h',
];
?>

<div class="sv-vars">
    <div class="sv-vars-head">
        <span class="sv-vars-head-title">
            <i class="fa fa-list-alt"></i> <?= $show ?>
            <span class="badge"><?= count($mergedSlave) ?></span>
        </span>
        <div class="sv-vars-search-wrap">
            <i class="fa fa-search"></i>
            <input type="text" class="sv-vars-search" id="sv-vars-filter" placeholder="<?= __('Search variables...') ?>" autocomplete="off">
        </div>
    </div>
    <div class="sv-vars-body" id="sv-vars-body">
        <?php foreach ($categorized as $catName => $vars): ?>
        <div class="sv-vars-cat" data-cat="<?= htmlspecialchars($catName) ?>">
            <div class="sv-vars-cat-head">
                <i class="fa <?= $catIcons[$catName] ?? 'fa-circle-o' ?>"></i>
                <?= htmlspecialchars($catName) ?>
                <span class="sv-vars-cat-count">(<?= count($vars) ?>)</span>
            </div>
            <?php foreach ($vars as $varName => $varValue):
                $valStr = (string)$varValue;
                $valClass = 'sv-vars-val';
                if ($valStr === '') {
                    $valClass .= ' sv-val-empty';
                    $valStr = '(empty)';
                } elseif ($valStr === 'Yes') {
                    $valClass .= ' sv-val-yes';
                } elseif ($valStr === 'No') {
                    $valClass .= ' sv-val-no';
                } elseif (is_numeric($valStr) && strlen($valStr) < 15) {
                    $valClass .= ' sv-val-num';
                }
                // Highlight errors
                if (stripos($varName, 'Error') !== false && $valStr !== '' && $valStr !== '(empty)' && $valStr !== '0') {
                    $valClass .= ' sv-val-err';
                }
            ?>
            <div class="sv-vars-row" data-var="<?= htmlspecialchars(strtolower($varName)) ?>" data-val="<?= htmlspecialchars(strtolower($valStr)) ?>">
                <div class="sv-vars-name"><?php
                    if (strpos($varName, ' / ') !== false) {
                        $parts = explode(' / ', $varName);
                        echo htmlspecialchars($parts[0]).' <span class="sv-alias">/ '.htmlspecialchars($parts[1]).'</span>';
                    } else {
                        echo htmlspecialchars($varName);
                    }
                ?></div>
                <div class="<?= $valClass ?>"><?= htmlspecialchars($valStr) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <div class="sv-vars-no-match" id="sv-vars-no-match">
            <i class="fa fa-search"></i> <?= __('No matching variables') ?>
        </div>
    </div>
</div>

<!-- ============================================================
     4) BINLOG ANALYSIS TAB
     ============================================================ -->
<div class="sv-card" id="sv-binlog-analysis-card">
    <div class="sv-health-strip ok"></div>
    <div class="sv-card-head">
        <span><i class="fa fa-bar-chart"></i> <?= __("Binlog Analysis") ?></span>
        <span style="font-size:11px;opacity:.7"><?= __("Select a time range on the lag chart above, or enter manually") ?></span>
    </div>
    <div class="sv-card-body">

        <!-- Time range selector -->
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px">
            <div>
                <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;display:block;margin-bottom:2px"><?= __('Start') ?></label>
                <input type="datetime-local" id="sv-ba-start" step="1" class="form-control" style="font-size:13px;width:220px;padding:4px 8px">
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;display:block;margin-bottom:2px"><?= __('End') ?></label>
                <input type="datetime-local" id="sv-ba-end" step="1" class="form-control" style="font-size:13px;width:220px;padding:4px 8px">
            </div>
            <div style="align-self:flex-end">
                <button type="button" class="btn btn-primary btn-sm" id="sv-ba-launch" disabled>
                    <i class="fa fa-rocket"></i> <?= __("Analyze Binlogs") ?>
                </button>
            </div>
            <div style="align-self:flex-end" id="sv-ba-status-wrap" style="display:none">
                <span id="sv-ba-status-badge" class="label label-default" style="font-size:12px"></span>
            </div>
        </div>

        <!-- Live progress log -->
        <div id="sv-ba-progress" style="display:none;margin-bottom:16px;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden">
            <div style="background:#0f172a;color:#fff;padding:6px 12px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">
                <i class="fa fa-terminal"></i> <?= __("Analysis Progress") ?>
            </div>
            <div id="sv-ba-progress-log" style="background:#1e293b;color:#e2e8f0;padding:8px 12px;font-family:monospace;font-size:11px;max-height:300px;overflow-y:auto;line-height:1.7"></div>
        </div>

        <!-- Past analyses -->
        <div id="sv-ba-history" style="margin-bottom:16px"></div>

        <!-- Results container (hidden until loaded) -->
        <div id="sv-ba-results" style="display:none">

            <!-- Zoom controls -->
            <div style="margin-bottom:8px;text-align:right">
                <button class="btn btn-default btn-xs" id="sv-ba-reset-zoom" title="<?= __('Reset zoom') ?>">
                    <i class="fa fa-search-minus"></i> <?= __('Reset zoom') ?>
                </button>
                <small style="color:#94a3b8;margin-left:8px"><?= __('Drag to zoom, scroll to zoom, double-click to reset') ?></small>
            </div>

            <!-- Binlog file timeline -->
            <div id="sv-ba-file-timeline-wrap" style="display:none;position:relative;height:92px;margin-bottom:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px">
                <canvas id="sv-ba-file-timeline"></canvas>
                <div id="sv-ba-file-tooltip" style="display:none;position:absolute;z-index:4;pointer-events:none;background:#0f172a;color:#fff;border-radius:4px;padding:6px 8px;font-size:11px;line-height:1.45;box-shadow:0 6px 18px rgba(15,23,42,.25);max-width:320px"></div>
            </div>

            <!-- Chart -->
            <div style="position:relative;height:250px;margin-bottom:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px">
                <canvas id="sv-ba-chart"></canvas>
            </div>

            <!-- Parallelism chart -->
            <div style="position:relative;height:360px;margin-bottom:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px">
                <canvas id="sv-ba-parallel-chart"></canvas>
            </div>

            <!-- Treemaps: Databases (left) + Tables (right) -->
            <div style="display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap">
                <div style="flex:1;min-width:280px">
                    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">
                        <i class="fa fa-database"></i> <?= __('DML by Database') ?>
                    </div>
                    <div style="position:relative;height:440px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:4px">
                        <canvas id="sv-ba-treemap-db"></canvas>
                    </div>
                </div>
                <div style="flex:1;min-width:280px">
                    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">
                        <i class="fa fa-table"></i> <?= __('DML by Table') ?>
                    </div>
                    <div style="position:relative;height:440px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:4px">
                        <canvas id="sv-ba-treemap-tbl"></canvas>
                    </div>
                </div>
            </div>

            <!-- Stats summary -->
            <div class="row" style="margin-bottom:16px">
                <div class="col-md-6">
                    <table class="table table-condensed table-bordered" style="font-size:12px;margin-bottom:8px">
                        <thead><tr><th colspan="2" style="background:#0f172a;color:#fff;font-size:11px;text-transform:uppercase"><i class="fa fa-info-circle"></i> <?= __("General Info") ?></th></tr></thead>
                        <tbody id="sv-ba-info-table"></tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-condensed table-bordered" style="font-size:12px;margin-bottom:8px">
                        <thead><tr><th colspan="2" style="background:#0f172a;color:#fff;font-size:11px;text-transform:uppercase"><i class="fa fa-database"></i> <?= __("DML Volume") ?></th></tr></thead>
                        <tbody id="sv-ba-dml-table"></tbody>
                    </table>
                </div>
            </div>

            <!-- Replication risk factors -->
            <div class="sv-collapse-head" id="sv-ba-risk-head" aria-expanded="true" onclick="var e=this.getAttribute('aria-expanded')==='true'?'false':'true';this.setAttribute('aria-expanded',e);document.getElementById('sv-ba-risk-body').style.display=e==='true'?'':'none'">
                <i class="fa fa-chevron-right"></i> <?= __("Replication Risk Factors") ?>
            </div>
            <div class="sv-collapse-body" id="sv-ba-risk-body" style="padding:16px">
                <div id="sv-ba-risks"></div>
            </div>

            <!-- Top tables -->
            <div class="sv-collapse-head" id="sv-ba-tables-head" aria-expanded="false" onclick="var e=this.getAttribute('aria-expanded')==='true'?'false':'true';this.setAttribute('aria-expanded',e);document.getElementById('sv-ba-tables-body').style.display=e==='true'?'':'none'">
                <i class="fa fa-chevron-right"></i> <?= __("Top Tables") ?> <span class="badge" id="sv-ba-tables-count">0</span>
            </div>
            <div class="sv-collapse-body" id="sv-ba-tables-body" style="display:none;padding:0">
                <table class="table table-condensed table-striped" style="font-size:11px;margin:0">
                    <thead><tr><th><?= __("Table") ?></th><th class="text-right">INSERT</th><th class="text-right">UPDATE</th><th class="text-right">DELETE</th><th class="text-right"><?= __("Total") ?></th></tr></thead>
                    <tbody id="sv-ba-tables-tbody"></tbody>
                </table>
            </div>

            <!-- DDL Details -->
            <div class="sv-collapse-head" id="sv-ba-ddl-head" aria-expanded="true" style="display:none" onclick="var e=this.getAttribute('aria-expanded')==='true'?'false':'true';this.setAttribute('aria-expanded',e);document.getElementById('sv-ba-ddl-body').style.display=e==='true'?'':'none'">
                <i class="fa fa-chevron-right"></i> <?= __("DDL Statements") ?> <span class="badge" id="sv-ba-ddl-count">0</span>
            </div>
            <div class="sv-collapse-body" id="sv-ba-ddl-body" style="padding:0">
                <table class="table table-condensed table-striped" style="font-size:11px;margin:0">
                    <thead><tr><th><?= __("Datetime") ?></th><th><?= __("Type") ?></th><th><?= __("Database") ?></th><th><?= __("Table") ?></th><th><?= __("Statement") ?></th></tr></thead>
                    <tbody id="sv-ba-ddl-tbody"></tbody>
                </table>
            </div>

            <!-- Recommendations -->
            <div class="sv-collapse-head" id="sv-ba-recs-head" aria-expanded="true" onclick="var e=this.getAttribute('aria-expanded')==='true'?'false':'true';this.setAttribute('aria-expanded',e);document.getElementById('sv-ba-recs-body').style.display=e==='true'?'':'none'">
                <i class="fa fa-chevron-right"></i> <?= __("Recommendations") ?> <span class="badge" id="sv-ba-recs-count">0</span>
            </div>
            <div class="sv-collapse-body" id="sv-ba-recs-body" style="padding:16px">
                <ul id="sv-ba-recs-list" style="margin:0;padding-left:20px"></ul>
            </div>

        </div><!-- /sv-ba-results -->

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var serverId = <?= (int) $data['id_mysql_server'] ?>;
    var replicationName = <?= json_encode($data['replication_name'] ?? '') ?>;
    var masterId = <?= (int) ($data['master_id'] ?? 0) ?>;
    var LINK = <?= json_encode(LINK) ?>;
    var baChart = null, baParallelChart = null, baTimelineChart = null;
    var _baVolData = null, _baTopTables = null, _baAnalysis = null;
    var pollTimer = null;

    // ---- Enable launch button when both dates are set ----
    function checkDates() {
        var s = document.getElementById('sv-ba-start').value;
        var e = document.getElementById('sv-ba-end').value;
        document.getElementById('sv-ba-launch').disabled = !(s && e);
    }
    document.getElementById('sv-ba-start').addEventListener('change', checkDates);
    document.getElementById('sv-ba-end').addEventListener('change', checkDates);

    // ---- Intercept Chart.js drag selection on lag charts ----
    // Wired both at initial render and after each "Load previous day"
    // prepends a new chart (#818). Idempotent via a sentinel attribute.
    function attachLagDragSelect(canvas) {
        if (!canvas || canvas.dataset.dragSelectBound === '1') return;
        var chart = Chart.getChart(canvas);
        if (!chart) return;
        canvas.dataset.dragSelectBound = '1';

        var dragStart = null;
        var selBox = document.createElement('div');
        selBox.style.cssText = 'position:absolute;background:rgba(30,58,138,0.15);border:1px dashed #1e3a8a;pointer-events:none;display:none;top:0;bottom:0;z-index:10';
        canvas.parentElement.style.position = 'relative';
        canvas.parentElement.appendChild(selBox);

        canvas.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return;
            var rect = canvas.getBoundingClientRect();
            dragStart = { x: e.clientX - rect.left, clientX: e.clientX };
            selBox.style.display = 'block';
            selBox.style.left = dragStart.x + 'px';
            selBox.style.width = '0px';
        });

        canvas.addEventListener('mousemove', function(e) {
            if (!dragStart) return;
            var rect = canvas.getBoundingClientRect();
            var curX = e.clientX - rect.left;
            var left = Math.min(dragStart.x, curX);
            var width = Math.abs(curX - dragStart.x);
            selBox.style.left = left + 'px';
            selBox.style.width = width + 'px';
        });

        canvas.addEventListener('mouseup', function(e) {
            if (!dragStart) return;
            var rect = canvas.getBoundingClientRect();
            var endX = e.clientX - rect.left;

            var xScale = chart.scales.x;
            var startVal = xScale.getValueForPixel(Math.min(dragStart.x, endX));
            var endVal = xScale.getValueForPixel(Math.max(dragStart.x, endX));

            dragStart = null;
            selBox.style.display = 'none';

            if (Math.abs(endX - parseFloat(selBox.style.left)) < 5) return; // too small

            var startDate = new Date(startVal);
            var endDate = new Date(endVal);

            // Format for datetime-local input: YYYY-MM-DDTHH:MM:SS
            function pad(n) { return n < 10 ? '0'+n : n; }
            function fmtDT(d) {
                return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate())
                    +'T'+pad(d.getHours())+':'+pad(d.getMinutes())+':'+pad(d.getSeconds());
            }

            document.getElementById('sv-ba-start').value = fmtDT(startDate);
            document.getElementById('sv-ba-end').value = fmtDT(endDate);
            checkDates();

            // Scroll to the analysis card
            document.getElementById('sv-binlog-analysis-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        canvas.addEventListener('mouseleave', function() {
            if (dragStart) {
                dragStart = null;
                selBox.style.display = 'none';
            }
        });
    }

    function attachLagDragSelectAll() {
        document.querySelectorAll('.sv-chart-wrap canvas').forEach(attachLagDragSelect);
    }
    // Expose so the "Load previous day" handler in Slave.php can re-arm
    // newly-prepended charts after `$.globalEval` instantiates them.
    window.attachLagDragSelectAll = attachLagDragSelectAll;

    setTimeout(attachLagDragSelectAll, 1000);

    // ---- Launch analysis ----
    document.getElementById('sv-ba-launch').addEventListener('click', function() {
        var btn = this;
        var startVal = document.getElementById('sv-ba-start').value;
        var endVal = document.getElementById('sv-ba-end').value;
        if (!startVal || !endVal) return;

        // Convert datetime-local to MySQL format
        var timeStart = startVal.replace('T', ' ');
        var timeEnd = endVal.replace('T', ' ');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Launching...';

        var formData = new URLSearchParams();
        formData.append('connection_name', replicationName);
        formData.append('time_start', timeStart);
        formData.append('time_end', timeEnd);
        formData.append(<?= json_encode($slaveBinlogAnalysisStartCsrfField) ?>, <?= json_encode($slaveBinlogAnalysisStartCsrfToken) ?>);

        fetch(LINK + 'slave/startBinlogAnalysis/' + serverId + '/ajax:true/', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (resp.error) {
                alert(resp.error);
                resetLaunchBtn();
                return;
            }
            pollAnalysis(resp.id);
        })
        .catch(function(err) {
            alert('Failed to start analysis: ' + err);
            resetLaunchBtn();
        });
    });

    // ---- Poll analysis status with live progress ----
    function pollAnalysis(id) {
        var statusWrap = document.getElementById('sv-ba-status-wrap');
        var statusBadge = document.getElementById('sv-ba-status-badge');
        var progressWrap = document.getElementById('sv-ba-progress');
        var progressLog = document.getElementById('sv-ba-progress-log');
        statusWrap.style.display = 'inline-block';
        progressWrap.style.display = 'block';
        progressLog.innerHTML = '<span style="color:#94a3b8"><i class="fa fa-spinner fa-spin"></i> Waiting for analysis to start...</span>';

        function renderProgress(steps) {
            if (!steps || !steps.length) return;
            var html = '';
            steps.forEach(function(s) {
                var icon = '', color = '';
                if (s.status === 'done') {
                    icon = '<span style="color:#10b981">&#10003;</span>';
                    color = '#a7f3d0';
                } else if (s.status === 'error') {
                    icon = '<span style="color:#ef4444">&#10007;</span>';
                    color = '#fca5a5';
                } else {
                    icon = '<span style="color:#f59e0b"><i class="fa fa-spinner fa-spin"></i></span>';
                    color = '#fde68a';
                }
                html += '<div style="color:' + color + '">'
                    + '<span style="color:#64748b;margin-right:6px">[' + s.time + ']</span>'
                    + icon + ' <b>' + escHtml(s.phase) + '</b> — ' + escHtml(s.message)
                    + '</div>';
            });
            progressLog.innerHTML = html;
            progressLog.scrollTop = progressLog.scrollHeight;
        }

        function poll() {
            fetch(LINK + 'slave/binlogAnalysisResult/' + id + '/ajax:true/').then(function(r){return r.json()}).then(function(data) {
                if (data.error) {
                    statusBadge.className = 'label label-danger';
                    statusBadge.textContent = 'Error: ' + data.error;
                    resetLaunchBtn();
                    return;
                }

                // Render progress steps
                if (data.progress) {
                    renderProgress(data.progress);
                }

                if (data.status === 'pending' || data.status === 'running') {
                    statusBadge.className = 'label label-warning';
                    var stepCount = (data.progress || []).length;
                    statusBadge.textContent = data.status === 'pending' ? 'Pending...' : 'Running... (step ' + stepCount + ')';
                    pollTimer = setTimeout(poll, 2000);
                } else if (data.status === 'done') {
                    statusBadge.className = 'label label-success';
                    statusBadge.textContent = 'Done!';
                    renderResults(data);
                    resetLaunchBtn();
                    loadHistory();
                } else if (data.status === 'error') {
                    statusBadge.className = 'label label-danger';
                    statusBadge.textContent = 'Error';
                    resetLaunchBtn();
                }
            });
        }
        poll();
    }

    function resetLaunchBtn() {
        var btn = document.getElementById('sv-ba-launch');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-rocket"></i> Analyze Binlogs';
    }

    // ---- Render results ----
    function renderResults(d) {
        var container = document.getElementById('sv-ba-results');
        container.style.display = 'block';

        // Store refs first so the window-aware paint can read them.
        _baVolData = d.volume_per_second || [];
        _baTopTables = d.top_tables || [];
        _baAnalysis = d;

        // Window-dependent panels (General Info, DML Volume, Risk Factors,
        // Top tables HTML, treemaps) — re-rendered on every zoom (#826).
        // Risk factor 3 (large txn) is global — paintWindowedPanels reads
        // it from `d` directly and tags the section "(full window)".
        paintWindowedPanels(computeWindowAggregates(d, null), d);

        // DDL Details
        var ddls = d.ddl_details || [];
        var ddlHead = document.getElementById('sv-ba-ddl-head');
        if (ddls.length > 0) {
            ddlHead.style.display = '';
            document.getElementById('sv-ba-ddl-count').textContent = ddls.length;
            var ddlHtml = '';
            ddls.forEach(function(dd) {
                var tp = (dd.type || '').toUpperCase();
                var rowStyle = '';
                var typeColor = '#334155';
                if (tp.indexOf('ALTER') >= 0) { rowStyle = 'background:rgba(245,158,11,0.12)'; typeColor = '#b45309'; }
                else if (tp.indexOf('DROP') >= 0) { rowStyle = 'background:rgba(239,68,68,0.1)'; typeColor = '#dc2626'; }
                else if (tp.indexOf('CREATE') >= 0) { typeColor = '#059669'; }
                else if (tp.indexOf('TRUNCATE') >= 0) { rowStyle = 'background:rgba(239,68,68,0.06)'; typeColor = '#ef4444'; }

                var dt = dd.datetime || '';
                var dtDisplay = dt ? dt.substring(11, 19) || dt : '';
                var dtFull = dt;

                ddlHtml += '<tr style="' + rowStyle + '">'
                    + '<td style="white-space:nowrap;color:#64748b"><small title="' + escHtml(dtFull) + '">' + escHtml(dtDisplay) + '</small></td>'
                    + '<td style="color:' + typeColor + ';font-weight:700">' + escHtml(tp) + '</td>'
                    + '<td><code>' + escHtml(dd.database || '') + '</code></td>'
                    + '<td><code>' + escHtml(dd.table || '') + '</code></td>'
                    + '<td style="font-size:10px;max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#64748b" title="' + escHtml(dd.statement || '') + '">'
                    + escHtml(dd.statement || '') + '</td></tr>';
            });
            document.getElementById('sv-ba-ddl-tbody').innerHTML = ddlHtml;
        } else {
            ddlHead.style.display = 'none';
        }

        // Recommendations
        var recs = d.recommendations || [];
        document.getElementById('sv-ba-recs-count').textContent = recs.length;
        var recsHtml = '';
        recs.forEach(function(r) {
            recsHtml += '<li style="margin-bottom:6px">' + escHtml(r) + '</li>';
        });
        document.getElementById('sv-ba-recs-list').innerHTML = recsHtml;

        // Chart
        renderChart(d.volume_per_second || [], d.lag_data || []);
        renderParallelismChart(d.volume_per_second || [], d);
        renderBinlogTimeline(d.binlog_file_ranges || []);
    }

    // ---- Window-aware aggregates + render (issue #826) ------------------
    //
    // `win = null` means "full window" — use the totals already stored in
    // the analysis row. With a {xMin,xMax} object (zoom in ms) we
    // recompute every metric we can derive from `volume_per_second` and
    // `binlog_file_ranges[].tables`. Risk factor 3 (large transactions)
    // and DDL details have no per-time data; we leave those at their
    // global values and tag risk 3 with "(full window)" when zoomed.
    function computeWindowAggregates(d, win) {
        // Fast path — no zoom, just return what the backend already gave us.
        if (!win || !Number.isFinite(win.xMin) || !Number.isFinite(win.xMax)) {
            return {
                window: null,
                period_start: d.time_start,
                period_end: d.time_end,
                duration_seconds: parseInt(d.duration_seconds) || 0,
                size_bytes: parseInt(d.total_size_bytes) || 0,
                total_transactions: parseInt(d.total_transactions) || 0,
                total_inserts: parseInt(d.total_inserts) || 0,
                total_updates: parseInt(d.total_updates) || 0,
                total_deletes: parseInt(d.total_deletes) || 0,
                peak_txn_per_sec: parseInt(d.peak_txn_per_sec) || 0,
                min_txn_per_sec:  parseInt(d.min_txn_per_sec)  || 0,
                avg_txn_per_sec:  parseFloat(d.avg_txn_per_sec) || 0,
                sequential_pct:   parseFloat(d.sequential_pct)  || 0,
                max_parallelism:  parseInt(d.max_parallelism)   || 0,
                parallelism_distribution: d.parallelism_distribution || {},
                databases_count:  parseInt(d.databases_count)   || 0,
                top_tables: d.top_tables || []
            };
        }

        var xMin = win.xMin, xMax = win.xMax;

        // Pass 1: per-second volume → size, transactions, txn-rate stats
        var sizeBytes = 0, totalTxn = 0;
        var peakTxn = 0, minTxn = Number.POSITIVE_INFINITY, secondsCount = 0;
        var sumTxn = 0, secondsWithSingleTxn = 0, maxPar = 0;
        var distrib = {};
        (d.volume_per_second || []).forEach(function(p) {
            var ms = parseTimelineTs(p.ts);
            if (ms === null || ms < xMin || ms > xMax) return;
            secondsCount++;
            sizeBytes += (parseInt(p.bytes) || 0);
            var txn = parseInt(p.txn) || 0;
            totalTxn += txn;
            sumTxn += txn;
            if (txn > peakTxn) peakTxn = txn;
            if (txn < minTxn) minTxn = txn;
            if (txn > maxPar) maxPar = txn;
            if (txn === 1) secondsWithSingleTxn++;
            // Bucket distribution into the same buckets as the backend
            // emits (1, 2-5, 6-10, 11-50, 51-100, 100+).
            var bucket;
            if (txn <= 1) bucket = '1';
            else if (txn <= 5) bucket = '2-5';
            else if (txn <= 10) bucket = '6-10';
            else if (txn <= 50) bucket = '11-50';
            else if (txn <= 100) bucket = '51-100';
            else bucket = '100+';
            distrib[bucket] = (distrib[bucket] || 0) + 1;
        });
        if (minTxn === Number.POSITIVE_INFINITY) minTxn = 0;
        var avgTxn = secondsCount > 0 ? (sumTxn / secondsCount) : 0;
        var sequentialPct = secondsCount > 0
            ? Math.round((secondsWithSingleTxn / secondsCount) * 1000) / 10
            : 0;

        // Pass 2: per-file table breakdown → DML totals + top tables + db count
        var ranges = d.binlog_file_ranges || [];
        var tableMerge = {};
        var dbSet = {};
        var inserts = 0, updates = 0, deletes = 0;
        ranges.forEach(function(fr) {
            var fStart = parseTimelineTs(fr.start);
            var fEnd = parseTimelineTs(fr.end);
            if (fStart === null || fEnd === null) return;
            if (fEnd < xMin || fStart > xMax) return; // no overlap
            (fr.tables || []).forEach(function(t) {
                var ti = parseInt(t.inserts) || 0;
                var tu = parseInt(t.updates) || 0;
                var td = parseInt(t.deletes) || 0;
                inserts += ti; updates += tu; deletes += td;
                if (!tableMerge[t.table]) {
                    tableMerge[t.table] = { table: t.table, inserts: 0, updates: 0, deletes: 0 };
                }
                tableMerge[t.table].inserts += ti;
                tableMerge[t.table].updates += tu;
                tableMerge[t.table].deletes += td;
                var m = String(t.table || '').match(/^`([^`]+)`\./);
                if (m) dbSet[m[1]] = true;
            });
        });
        var topTables = Object.keys(tableMerge).map(function(k) { return tableMerge[k]; })
            .filter(function(r) { return (r.inserts + r.updates + r.deletes) > 0; })
            .sort(function(a, b) {
                return (b.inserts + b.updates + b.deletes) - (a.inserts + a.updates + a.deletes);
            });

        function fmtTs(ms) {
            var dt = new Date(ms);
            function pad(n) { return n < 10 ? '0' + n : '' + n; }
            return dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate())
                + ' ' + pad(dt.getHours()) + ':' + pad(dt.getMinutes()) + ':' + pad(dt.getSeconds());
        }

        return {
            window: { xMin: xMin, xMax: xMax },
            period_start: fmtTs(xMin),
            period_end:   fmtTs(xMax),
            duration_seconds: Math.max(1, Math.round((xMax - xMin) / 1000)),
            size_bytes: sizeBytes,
            total_transactions: totalTxn,
            total_inserts: inserts,
            total_updates: updates,
            total_deletes: deletes,
            peak_txn_per_sec: peakTxn,
            min_txn_per_sec:  minTxn,
            avg_txn_per_sec:  Math.round(avgTxn * 10) / 10,
            sequential_pct:   sequentialPct,
            max_parallelism:  maxPar,
            parallelism_distribution: distrib,
            databases_count:  Object.keys(dbSet).length,
            top_tables: topTables
        };
    }

    function paintWindowedPanels(agg, d) {
        var zoomed = !!agg.window;
        var zoomTag = zoomed ? ' <small style="color:#7c3aed">(zoomed)</small>' : '';
        var totalRows = agg.total_inserts + agg.total_updates + agg.total_deletes;
        var sizeMb = (agg.size_bytes / 1048576).toFixed(1);

        // General Info table
        var infoHtml = '<tr><td><b>Server</b></td><td>' + (d.mysql_version || '?') + ', server_id=' + (d.server_id || '?') + '</td></tr>'
            + '<tr><td><b>Size</b></td><td>' + sizeMb + ' MB' + zoomTag + '</td></tr>'
            + '<tr><td><b>Period</b></td><td>' + agg.period_start + ' &rarr; ' + agg.period_end + ' (' + agg.duration_seconds + 's)' + zoomTag + '</td></tr>'
            + '<tr><td><b>Transactions</b></td><td>' + numberFmt(agg.total_transactions) + zoomTag + '</td></tr>'
            + '<tr><td><b>DDL</b></td><td>' + (d.total_ddl > 0
                ? d.total_ddl + ' statement(s) <small style="color:#94a3b8">(full window)</small> &mdash; <a href="#sv-ba-ddl-head" onclick="document.getElementById(\'sv-ba-ddl-head\').click()">see details</a>'
                : 'None &mdash; 100% DML row-based') + '</td></tr>';
        document.getElementById('sv-ba-info-table').innerHTML = infoHtml;

        // DML Volume table
        var dmlHtml = '<tr><td>INSERT</td><td class="text-right"><b>' + numberFmt(agg.total_inserts) + '</b></td></tr>'
            + '<tr><td>UPDATE</td><td class="text-right"><b>' + numberFmt(agg.total_updates) + '</b></td></tr>'
            + '<tr><td>DELETE</td><td class="text-right"><b>' + numberFmt(agg.total_deletes) + '</b></td></tr>'
            + '<tr style="background:#f0f4ff"><td><b>Total' + zoomTag + '</b></td><td class="text-right"><b>' + numberFmt(totalRows) + ' rows in ' + agg.duration_seconds + 's</b></td></tr>';
        document.getElementById('sv-ba-dml-table').innerHTML = dmlHtml;

        // Risk Factors — 1, 2, 4 are window-derived; 3 stays global.
        var distribStr = Object.keys(agg.parallelism_distribution).map(function(k) {
            return agg.parallelism_distribution[k] + ' groups of ' + k;
        }).join(', ') || '<em style="color:#94a3b8">none</em>';
        var risksHtml = '';
        risksHtml += '<div style="margin-bottom:12px"><b>1. Write Throughput</b>' + zoomTag + '<ul style="margin:4px 0 0 20px">'
            + '<li>Peak: <b>' + numberFmt(agg.peak_txn_per_sec) + ' txn/s</b></li>'
            + '<li>Avg: <b>' + agg.avg_txn_per_sec + ' txn/s</b></li>'
            + '<li>~' + numberFmt(Math.round(totalRows / Math.max(1, agg.duration_seconds))) + ' row changes/s</li></ul></div>';
        risksHtml += '<div style="margin-bottom:12px"><b>2. MTS Parallelism</b>' + zoomTag + '<ul style="margin:4px 0 0 20px">'
            + '<li>Txn/s: min=<b>' + agg.min_txn_per_sec + '</b> avg=<b>' + agg.avg_txn_per_sec + '</b> max=<b>' + agg.peak_txn_per_sec + '</b></li>'
            + '<li><b>' + agg.sequential_pct + '%</b> of seconds with single txn (no parallelism possible)</li>'
            + '<li>Max parallelism: <b>' + agg.max_parallelism + ' txn/s</b> in a single second</li>'
            + '<li>Distribution: ' + distribStr + '</li></ul></div>';
        // Risk 3 is global — no per-txn timestamps available.
        risksHtml += '<div style="margin-bottom:12px"><b>3. Large Transactions</b> <small style="color:#94a3b8">(full window)</small><ul style="margin:4px 0 0 20px">'
            + '<li>' + d.large_txn_500k + ' txn &gt; 500 KB, ' + d.large_txn_100k + ' txn &gt; 100 KB</li>'
            + '<li>Max: <b>' + Math.round(d.max_txn_size_bytes / 1024) + ' KB</b></li></ul></div>';
        risksHtml += '<div><b>4. Multi-database</b>' + zoomTag + ': <b>' + agg.databases_count + '</b> databases modified</div>';
        document.getElementById('sv-ba-risks').innerHTML = risksHtml;

        // Top tables HTML table
        document.getElementById('sv-ba-tables-count').textContent = agg.top_tables.length;
        var tbodyHtml = '';
        agg.top_tables.forEach(function(t) {
            var total = (t.inserts || 0) + (t.updates || 0) + (t.deletes || 0);
            tbodyHtml += '<tr><td><code>' + escHtml(t.table) + '</code></td>'
                + '<td class="text-right">' + numberFmt(t.inserts) + '</td>'
                + '<td class="text-right">' + numberFmt(t.updates) + '</td>'
                + '<td class="text-right">' + numberFmt(t.deletes) + '</td>'
                + '<td class="text-right"><b>' + numberFmt(total) + '</b></td></tr>';
        });
        document.getElementById('sv-ba-tables-tbody').innerHTML = tbodyHtml;

        // Treemaps (DML by Database / DML by Table)
        renderTreemaps(agg.top_tables);
    }

    // ---- Treemaps: databases + tables ----
    var baTreemapDb = null, baTreemapTbl = null;

    function renderTreemaps(topTables) {
        if (!topTables || !topTables.length) return;

        // Parse `db`.`table` → extract db and table
        var dbTotals = {};
        var tblTotals = {};

        topTables.forEach(function(t) {
            var m = t.table.match(/^`([^`]+)`\.`([^`]+)`$/);
            var db = m ? m[1] : 'unknown';
            var tbl = m ? m[2] : t.table;
            var total = (t.inserts || 0) + (t.updates || 0) + (t.deletes || 0);

            // Aggregate by database
            if (!dbTotals[db]) dbTotals[db] = { inserts: 0, updates: 0, deletes: 0, total: 0 };
            dbTotals[db].inserts += (t.inserts || 0);
            dbTotals[db].updates += (t.updates || 0);
            dbTotals[db].deletes += (t.deletes || 0);
            dbTotals[db].total += total;

            // Aggregate by table name (merge across databases)
            if (!tblTotals[tbl]) tblTotals[tbl] = { inserts: 0, updates: 0, deletes: 0, total: 0 };
            tblTotals[tbl].inserts += (t.inserts || 0);
            tblTotals[tbl].updates += (t.updates || 0);
            tblTotals[tbl].deletes += (t.deletes || 0);
            tblTotals[tbl].total += total;
        });

        // Convert to arrays sorted by total desc
        var dbData = Object.keys(dbTotals).map(function(k) {
            return { label: k, value: dbTotals[k].total, i: dbTotals[k].inserts, u: dbTotals[k].updates, d: dbTotals[k].deletes };
        }).sort(function(a,b) { return b.value - a.value; });

        var tblData = Object.keys(tblTotals).map(function(k) {
            return { label: k, value: tblTotals[k].total, i: tblTotals[k].inserts, u: tblTotals[k].updates, d: tblTotals[k].deletes };
        }).sort(function(a,b) { return b.value - a.value; });

        // Color palette
        var colors = ['#1e3a8a','#2563eb','#3b82f6','#60a5fa','#93c5fd','#0f766e','#14b8a6','#5eead4',
                      '#7c3aed','#a78bfa','#c084fc','#db2777','#f472b6','#ea580c','#f97316','#fbbf24',
                      '#84cc16','#22c55e','#06b6d4','#6366f1'];

        function buildTreemap(canvasId, data, existing, colorSmall, colorLarge) {
            var canvas = document.getElementById(canvasId);
            if (existing) existing.destroy();
            if (!data.length) return null;

            return new Chart(canvas, {
                type: 'treemap',
                data: {
                    datasets: [{
                        tree: data,
                        key: 'value',
                        labels: { display: true, formatter: function(ctx) { return ctx.raw._data ? ctx.raw._data.label : ''; }, font: { size: 11, weight: 'bold' }, color: '#fff', overflow: 'hidden' },
                        captions: { display: false },
                        backgroundColor: function(ctx) {
                            if (!ctx.raw || !ctx.raw._data) return '#cbd5e1';
                            var n = data.length;
                            var idx = 0;
                            for (var j = 0; j < n; j++) {
                                if (data[j].label === ctx.raw._data.label) { idx = j; break; }
                            }
                            var ratio = n > 1 ? idx / (n - 1) : 0;
                            var r = Math.round(colorSmall[0] + ratio * (colorLarge[0] - colorSmall[0]));
                            var g = Math.round(colorSmall[1] + ratio * (colorLarge[1] - colorSmall[1]));
                            var b = Math.round(colorSmall[2] + ratio * (colorLarge[2] - colorSmall[2]));
                            return 'rgb(' + r + ',' + g + ',' + b + ')';
                        },
                        borderColor: '#fff',
                        borderWidth: 1,
                        spacing: 0,
                        borderColor: 'rgba(0,0,0,0.3)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function(ctx) { return ctx[0].raw._data ? ctx[0].raw._data.label : ''; },
                                label: function(ctx) {
                                    var d = ctx.raw._data;
                                    if (!d) return '';
                                    return [
                                        'Total: ' + numberFmt(d.value) + ' rows',
                                        'INSERT: ' + numberFmt(d.i),
                                        'UPDATE: ' + numberFmt(d.u),
                                        'DELETE: ' + numberFmt(d.d)
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }

        // Databases: deep blue (largest) → light green (smallest)
        baTreemapDb = buildTreemap('sv-ba-treemap-db', dbData, baTreemapDb, [30,58,138], [134,239,172]);
        // Tables: dark red (largest) → light amber (smallest)
        baTreemapTbl = buildTreemap('sv-ba-treemap-tbl', tblData, baTreemapTbl, [153,27,27], [253,224,138]);
    }

    // ---- Chart.js bar+line chart ----
    function renderChart(volData, lagData) {
        var canvas = document.getElementById('sv-ba-chart');
        if (baChart) { baChart.destroy(); baChart = null; }

        // Use {x,y} format for all datasets so timestamps align independently
        var volXY = volData.map(function(v) { return { x: v.ts, y: parseFloat((v.bytes / 1024).toFixed(1)) }; });

        var datasets = [
            {
                type: 'bar',
                label: 'Volume (KB/s)',
                data: volXY,
                backgroundColor: 'rgba(33,150,243,0.6)',
                borderColor: 'rgba(33,150,243,0.8)',
                borderWidth: 1,
                yAxisID: 'y',
                order: 2
            }
        ];

        var hasLag = lagData && lagData.length > 0;
        if (hasLag) {
            datasets.push({
                type: 'line',
                label: 'Replication Lag (s)',
                data: lagData.map(function(v) { return { x: v.ts, y: v.lag }; }),
                borderColor: '#dc2626',
                backgroundColor: 'rgba(220,38,38,0.08)',
                fill: true,
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.2,
                yAxisID: 'yLag',
                order: 1
            });
        }

        var scales = {
            x: {
                type: 'time',
                time: {
                    parser: 'YYYY-MM-DD HH:mm:ss',
                    tooltipFormat: 'YYYY-MM-DD HH:mm:ss',
                    displayFormats: { second: 'HH:mm:ss', minute: 'HH:mm' }
                },
                title: { display: true, text: 'Time' },
                ticks: { maxRotation: 45, font: { size: 9 } }
            },
            y: {
                type: 'linear',
                position: 'left',
                title: { display: true, text: 'KB/s', color: '#2196F3' },
                ticks: { color: '#2196F3' },
                beginAtZero: true
            }
        };

        if (hasLag) {
            scales.yLag = {
                type: 'linear',
                position: 'right',
                title: { display: true, text: 'Lag (s)', color: '#dc2626' },
                ticks: { color: '#dc2626' },
                grid: { drawOnChartArea: false },
                beginAtZero: true
            };
        }

        baChart = new Chart(canvas.getContext('2d'), {
            data: { datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'nearest', axis: 'x', intersect: false },
                plugins: {
                    title: {
                        display: true,
                        text: hasLag ? 'Binlog Volume + Replication Lag' : 'Binlog Volume (KB/s)',
                        padding: 4,
                        font: { size: 13, weight: 'bold' }
                    },
                    legend: { display: hasLag, position: 'top', labels: { font: { size: 11 } } }
                },
                scales: scales,
                plugins: {
                    zoom: {
                        zoom: {
                            drag: { enabled: true, backgroundColor: 'rgba(33,150,243,0.15)', borderColor: '#2196F3', borderWidth: 1 },
                            mode: 'x',
                            onZoomComplete: function(ctx) { syncZoom(ctx.chart, baParallelChart); syncZoom(ctx.chart, baTimelineChart); rebuildPanelsFromZoom(ctx.chart); }
                        }
                    }
                }
            }
        });
    }

    function syncZoom(source, target) {
        if (!target) return;
        var srcX = source.scales.x;
        if (!srcX) return;
        target.options.scales.x.min = srcX.min;
        target.options.scales.x.max = srcX.max;
        target.update('none');
    }

    // ---- Parallelism chart (txn/s with min/avg/max lines) ----
    function renderParallelismChart(volData, d) {
        var canvas = document.getElementById('sv-ba-parallel-chart');
        if (baParallelChart) { baParallelChart.destroy(); baParallelChart = null; }
        if (!volData || !volData.length) return;

        var labels = volData.map(function(v) { return v.ts; });
        var txnCounts = volData.map(function(v) { return v.txn; });
        var avg = parseFloat(d.avg_txn_per_sec) || 0;
        var min = parseInt(d.min_txn_per_sec) || 0;
        var peak = parseInt(d.peak_txn_per_sec) || 0;

        var trMaster = d.threads_running_master || [];
        var trSlave = d.threads_running_slave || [];

        var datasets = [
            {
                type: 'bar',
                label: 'Txn/s',
                data: txnCounts,
                backgroundColor: txnCounts.map(function(v) {
                    if (v <= 1) return 'rgba(239,68,68,0.6)';
                    if (v <= avg) return 'rgba(251,191,36,0.6)';
                    return 'rgba(16,185,129,0.6)';
                }),
                borderWidth: 0,
                yAxisID: 'y',
                order: 3
            },
            {
                type: 'line',
                label: 'Avg (' + avg.toFixed(1) + ')',
                data: txnCounts.map(function() { return avg; }),
                borderColor: '#f59e0b',
                borderWidth: 2,
                borderDash: [6, 3],
                pointRadius: 0,
                fill: false,
                yAxisID: 'y',
                order: 2
            }
        ];

        if (trMaster.length > 0) {
            datasets.push({
                type: 'line',
                label: 'Threads Running (master)',
                data: trMaster.map(function(v) { return { x: v.ts, y: v.value }; }),
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124,58,237,0.05)',
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.3,
                fill: false,
                yAxisID: 'yTr',
                order: 1
            });
        }

        if (trSlave.length > 0) {
            datasets.push({
                type: 'line',
                label: 'Threads Running (slave)',
                data: trSlave.map(function(v) { return { x: v.ts, y: v.value }; }),
                borderColor: '#0891b2',
                backgroundColor: 'rgba(8,145,178,0.05)',
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.3,
                fill: false,
                yAxisID: 'yTr',
                order: 1
            });
        }

        var hasTr = trMaster.length > 0 || trSlave.length > 0;
        var scales = {
            x: {
                type: 'time',
                time: { parser: 'YYYY-MM-DD HH:mm:ss', tooltipFormat: 'HH:mm:ss', displayFormats: { second: 'HH:mm:ss', minute: 'HH:mm' } },
                ticks: { maxRotation: 45, font: { size: 8 } }
            },
            y: { position: 'left', beginAtZero: true, title: { display: true, text: 'Txn/s' } }
        };

        if (hasTr) {
            scales.yTr = {
                position: 'right',
                beginAtZero: true,
                title: { display: true, text: 'Threads Running', color: '#7c3aed' },
                ticks: { color: '#7c3aed' },
                grid: { drawOnChartArea: false }
            };
        }

        baParallelChart = new Chart(canvas.getContext('2d'), {
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    title: { display: true, text: 'Parallelism — Txn/s + Threads Running (min=' + min + ' avg=' + avg.toFixed(1) + ' max=' + peak + ')', font: { size: 12, weight: 'bold' }, padding: 4 },
                    legend: { display: true, position: 'top', labels: { font: { size: 10 } } },
                    zoom: {
                        zoom: {
                            drag: { enabled: true, backgroundColor: 'rgba(16,185,129,0.15)', borderColor: '#10b981', borderWidth: 1 },
                            mode: 'x',
                            onZoomComplete: function(ctx) { syncZoom(ctx.chart, baChart); syncZoom(ctx.chart, baTimelineChart); rebuildPanelsFromZoom(ctx.chart); }
                        }
                    }
                },
                scales: scales
            }
        });
    }

    // ---- Binlog file timeline (custom Gantt-style bars) ----
    function parseTimelineTs(ts) {
        var value = String(ts || '').replace(' ', 'T');
        var parsed = new Date(value).getTime();
        return Number.isFinite(parsed) ? parsed : null;
    }

    function timelineColor(ratio) {
        var start = [16, 185, 129];
        var end = [124, 58, 237];
        var pct = Math.max(0, Math.min(1, ratio || 0));
        var rgb = start.map(function(v, i) { return Math.round(v + ((end[i] - v) * pct)); });
        return 'rgb(' + rgb.join(',') + ')';
    }

    function shortBinlogName(name) {
        var label = String(name || '');
        var suffix = label.match(/(\d+)$/);
        if (suffix) return suffix[1];
        return label.length > 18 ? label.substr(label.length - 18) : label;
    }

    function buildTimelineTooltip(fr) {
        var duration = Math.max(0, Math.round((fr.endMs - fr.startMs) / 1000));
        var sizeMb = ((parseInt(fr.size || 0) || 0) / 1048576).toFixed(1);
        var totals = { inserts: 0, updates: 0, deletes: 0 };
        (fr.tables || []).forEach(function(t) {
            totals.inserts += parseInt(t.inserts || 0) || 0;
            totals.updates += parseInt(t.updates || 0) || 0;
            totals.deletes += parseInt(t.deletes || 0) || 0;
        });

        return '<b>' + escHtml(fr.name || '') + '</b>'
            + '<br>' + escHtml(fr.start || '') + ' &rarr; ' + escHtml(fr.end || '')
            + '<br>Duration: ' + numberFmt(duration) + 's'
            + '<br>Size: ' + sizeMb + ' MB'
            + '<br>DML: I=' + numberFmt(totals.inserts) + ' U=' + numberFmt(totals.updates) + ' D=' + numberFmt(totals.deletes);
    }

    var binlogTimelinePlugin = {
        id: 'binlogFileTimeline',
        afterDatasetsDraw: function(chart, args, opts) {
            opts = opts || {};
            var ranges = opts.ranges || [];
            var xScale = chart.scales && chart.scales.x;
            if (!ranges.length || !xScale) return;

            var ctx = chart.ctx;
            var area = chart.chartArea;
            var laneHeight = Math.min(24, (area.bottom - area.top) / ranges.length);
            var barHeight = Math.max(2, Math.min(18, laneHeight - 2));
            var maxDurationMs = Math.max(1, opts.maxDurationMs || 1);
            chart.$binlogTimelineHitboxes = [];

            ctx.save();
            ctx.font = '10px Arial';
            ctx.textBaseline = 'middle';

            ranges.forEach(function(fr, idx) {
                var x1 = xScale.getPixelForValue(fr.startMs);
                var x2 = xScale.getPixelForValue(fr.endMs);
                if (!Number.isFinite(x1) || !Number.isFinite(x2)) return;

                var left = Math.max(area.left, Math.min(x1, x2));
                var right = Math.min(area.right, Math.max(x1, x2));
                if (right < area.left || left > area.right) return;

                var width = Math.max(2, right - left);
                var top = area.top + (idx * laneHeight) + ((laneHeight - barHeight) / 2);
                var ratio = Math.max(0, Math.min(1, (fr.endMs - fr.startMs) / maxDurationMs));

                ctx.fillStyle = timelineColor(ratio);
                ctx.fillRect(left, top, width, barHeight);
                ctx.strokeStyle = 'rgba(15,23,42,.18)';
                ctx.strokeRect(left, top, width, barHeight);

                if (width > 38 && barHeight >= 9) {
                    ctx.fillStyle = '#ffffff';
                    ctx.fillText(shortBinlogName(fr.name), left + 4, top + (barHeight / 2), width - 8);
                }

                chart.$binlogTimelineHitboxes.push({
                    x1: left,
                    x2: left + width,
                    y1: top,
                    y2: top + barHeight,
                    range: fr
                });
            });

            ctx.restore();
        }
    };

    function bindTimelineTooltip(canvas) {
        var tooltip = document.getElementById('sv-ba-file-tooltip');
        if (!canvas || !tooltip) return;

        canvas.onmousemove = function(e) {
            if (!baTimelineChart || !baTimelineChart.$binlogTimelineHitboxes) return;
            var rect = canvas.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var hit = baTimelineChart.$binlogTimelineHitboxes.find(function(box) {
                return x >= box.x1 && x <= box.x2 && y >= box.y1 && y <= box.y2;
            });

            if (!hit) {
                tooltip.style.display = 'none';
                return;
            }

            tooltip.innerHTML = buildTimelineTooltip(hit.range);
            tooltip.style.left = Math.max(4, Math.min(rect.width - 320, x + 12)) + 'px';
            tooltip.style.top = Math.max(4, y - 10) + 'px';
            tooltip.style.display = 'block';
        };

        canvas.onmouseleave = function() {
            tooltip.style.display = 'none';
        };
    }

    function renderBinlogTimeline(ranges) {
        var wrap = document.getElementById('sv-ba-file-timeline-wrap');
        var canvas = document.getElementById('sv-ba-file-timeline');
        if (baTimelineChart) { baTimelineChart.destroy(); baTimelineChart = null; }
        if (!wrap || !canvas) return;

        var normalized = (ranges || []).map(function(fr) {
            var startMs = parseTimelineTs(fr.start);
            var endMs = parseTimelineTs(fr.end);
            if (startMs === null || endMs === null) return null;
            if (endMs < startMs) endMs = startMs;
            return Object.assign({}, fr, { startMs: startMs, endMs: endMs });
        }).filter(function(fr) {
            return fr !== null;
        });

        if (!normalized.length) {
            var tooltip = document.getElementById('sv-ba-file-tooltip');
            if (tooltip) { tooltip.style.display = 'none'; }
            wrap.style.display = 'none';
            return;
        }

        wrap.style.display = '';
        wrap.style.height = Math.min(360, Math.max(92, (normalized.length * 14) + 36)) + 'px';
        var maxDurationMs = normalized.reduce(function(max, fr) {
            return Math.max(max, fr.endMs - fr.startMs);
        }, 1);
        var domain = normalized.reduce(function(acc, fr) {
            return {
                min: Math.min(acc.min, fr.startMs),
                max: Math.max(acc.max, fr.endMs)
            };
        }, { min: normalized[0].startMs, max: normalized[0].endMs });

        baTimelineChart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                datasets: [{
                    data: [
                        { x: domain.min, y: 0 },
                        { x: domain.max, y: 1 }
                    ],
                    pointRadius: 0,
                    borderWidth: 0,
                    showLine: false
                }]
            },
            plugins: [binlogTimelinePlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                interaction: { mode: 'nearest', axis: 'x', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false },
                    title: { display: true, text: 'Binlog Files Timeline', font: { size: 12, weight: 'bold' }, padding: 4 },
                    binlogFileTimeline: { ranges: normalized, maxDurationMs: maxDurationMs },
                    zoom: {
                        zoom: {
                            drag: { enabled: true, backgroundColor: 'rgba(124,58,237,0.12)', borderColor: '#7c3aed', borderWidth: 1 },
                            mode: 'x',
                            onZoomComplete: function(ctx) {
                                syncZoom(ctx.chart, baChart);
                                syncZoom(ctx.chart, baParallelChart);
                                rebuildPanelsFromZoom(ctx.chart);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        min: domain.min,
                        max: domain.max,
                        time: { parser: 'YYYY-MM-DD HH:mm:ss', tooltipFormat: 'YYYY-MM-DD HH:mm:ss', displayFormats: { second: 'HH:mm:ss', minute: 'HH:mm' } },
                        ticks: { maxRotation: 45, font: { size: 8 } },
                        grid: { color: 'rgba(148,163,184,.25)' }
                    },
                    y: { display: false, min: 0, max: 1 }
                }
            }
        });

        if (baChart && baChart.scales && baChart.scales.x) {
            syncZoom(baChart, baTimelineChart);
        }
        bindTimelineTooltip(canvas);
    }

    // ---- Rebuild every window-dependent panel from the zoom (#826) ----
    // Called on each chart's onZoomComplete and from the Reset Zoom button
    // (with `chart = null` to mean "full window"). Renders General Info,
    // DML Volume, Risk Factors, top-tables HTML and both treemaps.
    function rebuildPanelsFromZoom(chart) {
        if (!_baAnalysis) return;
        var win = (chart && chart.scales && chart.scales.x)
            ? { xMin: chart.scales.x.min, xMax: chart.scales.x.max }
            : null;
        // If the analysis has no per-file table breakdown, the partial
        // recompute would zero out DML totals and top tables. Fall back to
        // full-window so the panels stay populated.
        var ranges = _baAnalysis.binlog_file_ranges || [];
        if (win && (!ranges.length || !ranges[0].tables)) {
            win = null;
        }
        paintWindowedPanels(computeWindowAggregates(_baAnalysis, win), _baAnalysis);
    }

    // ---- Load past analyses ----
    function loadHistory() {
        fetch(LINK + 'slave/binlogAnalysisList/' + serverId + '/ajax:true/?connection_name='
            + encodeURIComponent(replicationName || '')).then(function(r){return r.json()}).then(function(list) {
            if (!list || !list.length) {
                document.getElementById('sv-ba-history').innerHTML = '';
                return;
            }
            var html = '<div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px">'
                + '<?= __("Past Analyses") ?></div>'
                + '<div style="display:flex;flex-wrap:wrap;gap:6px">';
            list.forEach(function(a) {
                var sizeMb = (a.total_size_bytes / 1048576).toFixed(1);
                var statusCls = a.status === 'done' ? 'success' : (a.status === 'error' ? 'danger' : 'warning');
                // "YYYY-MM-DD HH:MM → HH:MM" — same date once; full datetime on both
                // sides only when the analysis spans midnight (#822 follow-up).
                var sd = a.time_start.substr(0, 10), ed = a.time_end.substr(0, 10);
                var sh = a.time_start.substr(11, 5), eh = a.time_end.substr(11, 5);
                var rangeLabel = sd === ed
                    ? sd + ' ' + sh + '&rarr;' + eh
                    : sd + ' ' + sh + '&rarr;' + ed + ' ' + eh;
                html += '<button class="btn btn-xs btn-default sv-ba-history-btn" data-id="' + a.id + '" data-status="' + a.status + '">'
                    + '<span class="label label-' + statusCls + '">' + a.status + '</span> '
                    + rangeLabel
                    + ' <small>(' + sizeMb + 'MB, ' + numberFmt(a.total_transactions) + ' txn)</small>'
                    + '</button>';
            });
            html += '</div>';
            document.getElementById('sv-ba-history').innerHTML = html;

            // Bind click via event delegation
            document.getElementById('sv-ba-history').addEventListener('click', function(e) {
                var btn = e.target.closest('.sv-ba-history-btn');
                if (!btn) return;
                var id = btn.getAttribute('data-id');
                var st = btn.getAttribute('data-status');
                if (st === 'done') {
                    fetch(LINK + 'slave/binlogAnalysisResult/' + id + '/ajax:true/').then(function(r){return r.json()}).then(function(data) {
                        if (!data.error) renderResults(data);
                    });
                } else if (st === 'pending' || st === 'running') {
                    pollAnalysis(id);
                }
            });
        });
    }

    // ---- Helpers ----
    function numberFmt(n) {
        return parseInt(n || 0).toLocaleString();
    }
    function escHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    // ---- Reset zoom button ----
    document.getElementById('sv-ba-reset-zoom').addEventListener('click', function() {
        if (baChart) { baChart.resetZoom(); }
        if (baParallelChart) { baParallelChart.resetZoom(); }
        if (baTimelineChart) { baTimelineChart.resetZoom(); }
        rebuildPanelsFromZoom(null);
    });

    // ---- Init: load history on page load ----
    loadHistory();

});
</script>

<?php endif; /* end else __new__ */ ?>
