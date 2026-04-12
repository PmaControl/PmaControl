<?php

use Glial\Html\Form\Form;
use App\Library\Display;

// -- helpers ------------------------------------------------------------------

if (empty($data['replication_name'])) {
    $show = 'SHOW SLAVE STATUS;';
} else {
    $show = 'SHOW SLAVE \''.$data['replication_name'].'\' STATUS;';
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
<?php if (!empty($data['all_connections']) && count($data['all_connections']) > 0): ?>
<div class="sv-tabs">
    <?php foreach ($data['all_connections'] as $conn):
        $cn = $conn['name'];
        $label = $cn !== '' ? $cn : __('default');
        $isActive = ($cn === $data['replication_name']);
        $lagLabel = '';
        if ($conn['lag'] !== null && $conn['lag'] !== 'NULL' && $conn['health'] !== 'stopped') {
            $lagLabel = ((int)$conn['lag'] === 0) ? '0s' : $conn['lag'].'s';
        }
    ?>
    <a class="sv-tab<?= $isActive ? ' active' : '' ?>"
       href="<?= LINK ?>slave/show/<?= $data['id_mysql_server'] ?>/<?= urlencode($cn) ?>/">
        <span class="sv-tab-dot <?= $conn['health'] ?>"></span>
        <?= htmlspecialchars($label) ?>
        <?php if ($lagLabel): ?><span class="sv-tab-lag"><?= $lagLabel ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>
    <a class="sv-tab sv-tab-add" href="<?= LINK ?>slave/show/<?= $data['id_mysql_server'] ?>/__new__/"
       title="<?= __('Add replication source') ?>">
        <i class="fa fa-plus"></i>
    </a>
</div>
<?php endif; ?>

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
                echo '<div class="sv-chart-wrap"><canvas id="myChart'.$slave['id_mysql_server'].crc32($slave['day']).'"></canvas></div>';
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
                        <?php if ($data['parallel_mode'] !== null):
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

<?php endif; /* end else __new__ */ ?>
