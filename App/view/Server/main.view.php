<?php

use App\Library\Security\CsrfRender;

use Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;
use App\Library\Display;
use App\Library\Format;
use App\Library\Debug;

function human_time_diff_dec($date_start, $precision = 1) {
    $seconds = time() - strtotime($date_start);
    $seconds--;
    if ($seconds < 60) return round($seconds, $precision).'s';
    $minutes = $seconds / 60;
    if ($minutes < 60) return round($minutes, $precision).'m';
    $hours = $minutes / 60;
    if ($hours < 24) return round($hours, $precision).'h';
    return round($hours / 24, $precision).'d';
}

function isoToFlag(string $iso): string {
    $flag = '';
    $iso = strtoupper($iso);
    if (strlen($iso) === 2) {
        $flag .= mb_chr(127397 + ord($iso[0]));
        $flag .= mb_chr(127397 + ord($iso[1]));
    }
    return $flag;
}

function format_time_ps_with_label($ps, $precision = 2) {
    if (!is_numeric($ps)) return '<span class="sm-badge muted">-</span>';
    $units = ["ps" => 1, "ns" => 1_000, "µs" => 1_000_000, "ms" => 1_000_000_000, "s" => 1_000_000_000_000];
    foreach ($units as $unit => $factor) {
        if ($ps < $factor * 1000) { $formatted = round($ps / $factor, $precision).' '.$unit; break; }
    }
    if (!isset($formatted)) $formatted = round($ps / 1_000_000_000_000, $precision).' s';
    if ($ps <= 250_000_000) $cls = 'ok';
    elseif ($ps <= 1_000_000_000) $cls = 'good';
    elseif ($ps < 2_000_000_000) $cls = 'warn';
    else $cls = 'crit';
    return '<span class="sm-badge '.$cls.'">'.$formatted.'</span>';
}

function shorten_host_for_display($host, $max = 32) {
    $host = (string)$host;
    if ($host === '' || mb_strlen($host, 'UTF-8') <= $max) return $host;
    return mb_substr($host, 0, $max, 'UTF-8').'…';
}

function serverStatus($server, $extra, $isEffectiveMonitored) {
    if (!$isEffectiveMonitored) return 'unmonitored';

    // Detect error state first
    $hasError = empty($extra['mysql_available'])
             || $extra['mysql_available'] === "0"
             || !empty($extra['mysql_error']);

    // Acknowledged + error = acknowledged (green, error still shown on 2nd line)
    // Acknowledged + ok = acknowledged
    if ($server['is_acknowledged'] !== "0") return 'acknowledged';

    if ($hasError) return 'error';
    if ($extra['mysql_available'] === "2") return 'warning';
    if (!empty($extra['wsrep_on']) && $extra['wsrep_on'] === "ON" && ($extra['wsrep_cluster_status'] ?? 'Primary') !== "Primary") return 'warning';
    return 'ok';
}

function server_main_cve_severity_class($severity): string {
    $severity = strtolower((string)$severity);
    return in_array($severity, ['critical', 'high', 'medium', 'low', 'none'], true) ? $severity : 'unknown';
}

function server_main_cve_score($score): string {
    return is_numeric($score) ? number_format((float)$score, 1) : 'n/a';
}

function server_main_cve_short_title($title, $max = 92): string {
    $title = trim((string)$title);
    if ($title === '') return '';
    if (mb_strlen($title, 'UTF-8') <= $max) return $title;
    return mb_substr($title, 0, $max, 'UTF-8').'…';
}

// Count servers by status
$statusCounts = ['ok' => 0, 'warning' => 0, 'error' => 0, 'acknowledged' => 0, 'unmonitored' => 0];
if (!empty($data['servers'])) {
    foreach ($data['servers'] as $_s) {
        $_eff = !empty($_s['effective_is_monitored']) && (string)$_s['effective_is_monitored'] === "1";
        $_ex = $data['extra'][$_s['id']] ?? [];
        $statusCounts[serverStatus($_s, $_ex, $_eff)]++;
    }
}

$workerKillCsrfField = CsrfRender::field($data, 'worker_kill');
$workerKillCsrfToken = CsrfRender::token($data, 'worker_kill');
$serverAcknowledgeCsrfField = CsrfRender::field($data, 'server_acknowledge');
$serverAcknowledgeCsrfToken = CsrfRender::token($data, 'server_acknowledge');
$serverRetractCsrfField = CsrfRender::field($data, 'server_retract');
$serverRetractCsrfToken = CsrfRender::token($data, 'server_retract');
$galeraSetPrimaryCsrfField = CsrfRender::field($data, 'galera_set_primary');
$galeraSetPrimaryCsrfToken = CsrfRender::token($data, 'galera_set_primary');

if (empty($_GET['ajax'])):
?>
<style>
/* ================================================================
   SERVER MAIN — Modern monitoring dashboard
   ================================================================ */
:root { --sm-grad: linear-gradient(135deg, #0f172a, #1e3a8a); --sm-border: #e2e8f0;
        --sm-ok: #10b981; --sm-warn: #f59e0b; --sm-crit: #ef4444; --sm-info: #3b82f6;
        --sm-muted: #94a3b8; --sm-surface: #fff; --sm-r: 6px; --sm-text: #1e293b; }

/* ---------- toolbar ---------- */
.sm-toolbar { background: var(--sm-surface); border: 1px solid var(--sm-border); border-radius: var(--sm-r);
              padding: 8px 14px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
              box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.sm-toolbar .btn { font-size: 11px; padding: 4px 10px; }
.sm-toolbar select { font-size: 11px; padding: 3px 6px; border: 1px solid #cbd5e1; border-radius: 4px;
                     background: #fff; color: var(--sm-text); }
.sm-sep { width: 1px; height: 22px; background: var(--sm-border); flex-shrink: 0; }
.sm-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .4px; color: var(--sm-muted); font-weight: 600; }
.sm-spacer { flex: 1; }

/* ---------- card ---------- */
.sm-card { background: var(--sm-surface); border: 1px solid var(--sm-border); border-radius: var(--sm-r);
           overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 16px; }
.sm-head { background: var(--sm-grad); color: #fff; padding: 10px 16px;
           display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.sm-head-title { font-size: 14px; font-weight: 600; }
.sm-head-title i { margin-right: 8px; opacity: .7; }
.sm-head .sm-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px;
                    font-weight: 600; margin-left: 4px; }
.sm-pill.ok   { background: rgba(16,185,129,.25); color: #a7f3d0; }
.sm-pill.warn { background: rgba(245,158,11,.25); color: #fde68a; }
.sm-pill.crit { background: rgba(239,68,68,.3); color: #fecaca; }
.sm-pill.ack  { background: rgba(16,185,129,.15); color: #6ee7b7; }
.sm-pill.off  { background: rgba(59,130,246,.2); color: #93c5fd; }

/* search in header */
.sm-search { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25);
             color: #fff; padding: 4px 12px 4px 28px; border-radius: 14px; font-size: 12px;
             width: 200px; outline: none; transition: all .15s; }
.sm-search:focus { background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.5); width: 250px; }
.sm-search::placeholder { color: rgba(255,255,255,.45); }
.sm-search-wrap { position: relative; }
.sm-search-wrap .fa-search { position: absolute; left: 9px; top: 50%; transform: translateY(-50%);
                              color: rgba(255,255,255,.35); font-size: 11px; pointer-events: none; }

/* ---------- table ---------- */
.sm-wrap { overflow-x: auto; overflow-y: auto; max-height: calc(100vh - 220px); }
.sm-wrap::-webkit-scrollbar { width: 6px; }
.sm-wrap::-webkit-scrollbar-track { background: #f1f5f9; }
.sm-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
.sm-wrap::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.sm-t { width: 100%; border-collapse: collapse; font-size: 12px; }
.sm-t thead { position: sticky; top: 0; z-index: 2; }
.sm-t th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase;
           letter-spacing: .4px; padding: 6px 10px; border-bottom: 2px solid var(--sm-border);
           white-space: nowrap; user-select: none; }
.sm-t td { padding: 4px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
           transition: background .12s; }
.sm-t tbody tr { transition: transform .1s; }
.sm-t tbody tr:hover td { background: #f0f4ff; }
.sm-t tbody tr:last-child td { border-bottom: none; }

/* status stripe left */
.sm-t td.sm-status { width: 4px; padding: 0; position: relative; }
.sm-t td.sm-status::after { content: ''; position: absolute; top: 0; bottom: 0; left: 0; width: 4px; }
.sm-row-ok td.sm-status::after { background: var(--sm-ok); }
.sm-row-warn td.sm-status::after { background: var(--sm-warn); }
.sm-row-err td.sm-status::after { background: var(--sm-crit); }
.sm-row-ack td.sm-status::after { background: var(--sm-ok); }
.sm-row-off td.sm-status::after { background: var(--sm-info); }
.sm-row-proc td.sm-status::after { background: var(--sm-warn); }

/* subtle row tints */
.sm-row-err td { background: #fef2f2; }
.sm-row-err:hover td { background: #fee2e2 !important; }
.sm-row-warn td { background: #fffbeb; }
.sm-row-warn:hover td { background: #fef3c7 !important; }
.sm-row-ack td { background: #f0fdf4; }
.sm-row-ack:hover td { background: #dcfce7 !important; }
.sm-row-off td { background: #eff6ff; }
.sm-row-off:hover td { background: #dbeafe !important; }
.sm-row-proc td { background: #fff7ed; }
.sm-row-proc:hover td { background: #ffedd5 !important; }

/* hidden rows for search filter */
.sm-hidden { display: none !important; }

/* ---------- cells ---------- */
.sm-srv { min-width: 180px; }
.sm-srv-name { font-weight: 600; color: var(--sm-text); font-size: 13px; }
.sm-srv-name a { color: inherit; text-decoration: none; transition: color .12s; }
.sm-srv-name a:hover { color: #1e3a8a; }
.sm-env { font-size: 9px; padding: 1px 6px; border-radius: 3px; font-weight: 600; }
.sm-org { font-size: 11px; color: var(--sm-muted); }
.sm-tag { font-size: 9px; padding: 1px 5px; border-radius: 3px; }
.sm-env-cell { white-space: nowrap; }
.sm-tag-cell { min-width: 110px; max-width: 220px; }
.sm-tag-empty { color: var(--sm-muted); font-size: 10px; }

.sm-host { font-family: 'SFMono-Regular',Consolas,monospace; font-size: 11px; white-space: nowrap; color: #475569; }
.sm-host a { color: inherit; text-decoration: none; }
.sm-ro { font-size: 8px; padding: 1px 4px; border-radius: 2px; background: var(--sm-ok); color: #fff; font-weight: 700; vertical-align: middle; }

.sm-ver { white-space: nowrap; font-size: 11px; color: #475569; }
.sm-ver img { vertical-align: middle; }

.sm-cve-cell { position: relative; white-space: nowrap; min-width: 64px; }
.sm-cve-trigger { display: inline-flex; align-items: center; gap: 4px; border: 1px solid #cbd5e1;
                  border-radius: 999px; padding: 2px 7px; font-size: 10px; font-weight: 800;
                  background: #f8fafc; color: #334155; cursor: pointer; text-decoration: none; }
.sm-cve-trigger:hover, .sm-cve-trigger:focus { color: inherit; text-decoration: none; }
.sm-cve-trigger.critical { background: #fee2e2; border-color: #fecaca; color: #9f1239; }
.sm-cve-trigger.high { background: #fee2e2; border-color: #fecaca; color: #b91c1c; }
.sm-cve-trigger.medium { background: #fef3c7; border-color: #fde68a; color: #92400e; }
.sm-cve-trigger.low { background: #dbeafe; border-color: #bfdbfe; color: #1e40af; }
.sm-cve-trigger.none, .sm-cve-trigger.unknown { background: #f1f5f9; border-color: #e2e8f0; color: #64748b; }
.sm-cve-kev-dot { display:inline-block; width:6px; height:6px; border-radius:50%; background:#dc2626; box-shadow:0 0 0 2px #fee2e2; }
.sm-cve-pop { display: none; position: absolute; left: 0; top: 100%; width: 420px; max-width: 52vw;
              background: #0f172a; color: #e5e7eb; border: 1px solid #334155; border-radius: 8px;
              box-shadow: 0 18px 40px rgba(15,23,42,.35); padding: 10px; z-index: 30; white-space: normal; }
.sm-cve-cell:hover .sm-cve-pop, .sm-cve-cell:focus-within .sm-cve-pop { display: block; }
.sm-cve-pop-title { display:flex; justify-content:space-between; gap:8px; color:#fff; font-weight:800; margin-bottom:7px; }
.sm-cve-pop-sub { color:#94a3b8; font-size:10px; font-weight:600; }
.sm-cve-item { border-top:1px solid #1e293b; padding:7px 0; }
.sm-cve-item:first-of-type { border-top:0; }
.sm-cve-id { font-weight:800; color:#bfdbfe; text-decoration:none; }
.sm-cve-id:hover { color:#fff; text-decoration:underline; }
.sm-cve-sev { display:inline-block; min-width:54px; text-align:center; border-radius:999px; padding:1px 5px;
              color:#fff; font-size:9px; font-weight:800; text-transform:uppercase; margin-left:4px; }
.sm-cve-sev.critical { background:#9f1239; }
.sm-cve-sev.high { background:#dc2626; }
.sm-cve-sev.medium { background:#d97706; }
.sm-cve-sev.low { background:#2563eb; }
.sm-cve-sev.none, .sm-cve-sev.unknown { background:#64748b; }
.sm-cve-item-title { color:#cbd5e1; font-size:10px; margin-top:3px; line-height:1.35; }
.sm-cve-item-meta { color:#94a3b8; font-size:9px; margin-top:3px; }
.sm-cve-more { color:#cbd5e1; border-top:1px solid #1e293b; padding-top:7px; font-size:10px; font-weight:700; }
.sm-cve-empty { color: var(--sm-muted); font-size: 10px; }

.sm-badge { display: inline-block; font-size: 10px; padding: 1px 6px; border-radius: 3px; font-weight: 600; }
.sm-badge.ok   { background: #d1fae5; color: #065f46; }
.sm-badge.good { background: #dbeafe; color: #1e40af; }
.sm-badge.warn { background: #fef3c7; color: #92400e; }
.sm-badge.crit { background: #fee2e2; color: #991b1b; }
.sm-badge.muted { background: #f1f5f9; color: #94a3b8; }
.sm-badge.info { background: #dbeafe; color: #1e40af; }

.sm-detail-row td { padding: 0 10px 4px 38px; border-bottom: 1px solid #f1f5f9; font-size: 11px; background: #fff; }
.sm-detail-line { display: flex; align-items: center; justify-content: space-between; gap: 8px; min-height: 22px; }
.sm-detail-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; flex-wrap: wrap; margin-left: auto; }
.sm-detail-actions .btn { font-size: 10px; padding: 2px 6px; margin-right: 0; }
.sm-detail-actions form { display: inline-flex; margin: 0; }
.sm-detail-placeholder { flex: 1; min-width: 12px; }
.sm-proc-label { display: inline-flex; align-items: center; font-size: 10px; padding: 1px 6px; border-radius: 3px;
                 font-weight: 700; background: #fed7aa; color: #9a3412; }
.sm-kill-worker { background: #c2410c; border-color: #9a3412; color: #fff; font-weight: 700; }
.sm-kill-worker:hover, .sm-kill-worker:focus { background: #9a3412; border-color: #7c2d12; color: #fff; }
.sm-err-row td { background: #fef2f2; box-shadow: inset 4px 0 0 var(--sm-crit); }
.sm-err-row td .sm-err-text { color: #991b1b; display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
.sm-err-row td .sm-err-text i { color: var(--sm-crit); font-size: 10px; }
.sm-detail-row td .label { font-size: 9px; }
.sm-row-ack + .sm-err-row td { background: #f0fdf4; box-shadow: inset 4px 0 0 var(--sm-ok); }
.sm-row-ack + .sm-err-row .sm-err-text { color: #065f46; }
.sm-row-ack + .sm-err-row .sm-err-text i { color: var(--sm-ok); }
.sm-row-warn + .sm-err-row td { background: #fffbeb; box-shadow: inset 4px 0 0 var(--sm-warn); }
.sm-row-warn + .sm-err-row .sm-err-text { color: #92400e; }
.sm-row-warn + .sm-err-row .sm-err-text i { color: var(--sm-warn); }
.sm-row-off + .sm-err-row td { background: #eff6ff; box-shadow: inset 4px 0 0 var(--sm-info); }
.sm-row-off + .sm-err-row .sm-err-text { color: #1e40af; }
.sm-row-off + .sm-err-row .sm-err-text i { color: var(--sm-info); }
.sm-row-proc + .sm-detail-row td { background: #fff7ed; box-shadow: inset 4px 0 0 var(--sm-warn); }
.sm-t tbody tr:has(+ .sm-detail-row) td { border-bottom: none; }

/* general log switch compact */
.sm-org-cell { font-size: 11px; color: #475569; white-space: nowrap; }

/* ping cell */
.sm-ping { white-space: nowrap; }

/* no data */
.sm-empty { text-align: center; padding: 40px; color: var(--sm-muted); }

/* status dot (reuse from Display) */
.sv-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; vertical-align: middle; }
.sv-dot.ok   { background: var(--sm-ok); box-shadow: 0 0 6px rgba(16,185,129,.4); }
.sv-dot.fail { background: var(--sm-crit); box-shadow: 0 0 6px rgba(239,68,68,.4); }
.sv-dot.info { background: var(--sm-info); box-shadow: 0 0 6px rgba(59,130,246,.4); }
.sv-dot.warn { background: var(--sm-warn); box-shadow: 0 0 6px rgba(245,158,11,.4); }
.sv-dot.halo { position: relative; }
.sv-dot.halo::after { content: ""; position: absolute; top: 50%; left: 50%; width: 100%; height: 100%;
                       border-radius: 50%; border: 3px solid var(--sm-crit);
                       transform: translate(-50%,-50%); animation: sm-halo 2s ease-out infinite; }
.sv-dot.warn.halo::after { border-color: var(--sm-warn); }
@keyframes sm-halo { 0% { width:100%;height:100%;opacity:1; } 100% { width:280%;height:280%;opacity:0; } }
</style>

<?php
    echo '<div class="well" style="margin-bottom:10px;padding:6px 12px">';
    FactoryController::addNode("Common", "displayClientEnvironment", array());
    echo '</div>';

    $showMode = $data['show_mode'] ?? 'monitored';
    $typeFilter = $data['type_filter'] ?? 'all';
    $typeFilters = $data['type_filters'] ?? ['all' => __('All types')];
    $encodedTypeFilter = rawurlencode((string)$typeFilter);
    $showToggleUrl = LINK.'server/main/?show='.($showMode === 'all' ? 'monitored' : 'all').'&type='.$encodedTypeFilter;
    $typeBaseUrl = LINK.'server/main/?show='.rawurlencode((string)$showMode).'&type=';
?>

<div class="sm-toolbar">
    <a href="<?= $showToggleUrl ?>" class="btn <?= $showMode === 'all' ? 'btn-default' : 'btn-info' ?>">
        <i class="fa fa-eye"></i> <?= $showMode === 'all' ? __('Monitored only') : __('All servers') ?>
    </a>
    <div class="sm-sep"></div>
    <span class="sm-lbl"><?= __('Type') ?></span>
    <select id="sm-type-filter">
        <?php foreach ($typeFilters as $typeKey => $typeLabel): ?>
        <option value="<?= htmlspecialchars((string)$typeKey, ENT_QUOTES) ?>"<?= (strtolower((string)$typeKey) === strtolower((string)$typeFilter)) ? ' selected' : '' ?>><?= htmlspecialchars((string)$typeLabel, ENT_QUOTES) ?></option>
        <?php endforeach; ?>
    </select>
    <script>document.getElementById("sm-type-filter").addEventListener("change",function(){window.location.href="<?= htmlspecialchars($typeBaseUrl, ENT_QUOTES) ?>"+encodeURIComponent(this.value);});</script>
    <div class="sm-sep"></div>
    <span class="sm-lbl"><?= __('Refresh') ?></span>
    <div class="btn-group">
        <a onclick="setRefreshInterval(1000)" class="btn btn-primary btn-xs">1s</a>
        <a onclick="setRefreshInterval(2000)" class="btn btn-primary btn-xs">2s</a>
        <a onclick="setRefreshInterval(5000)" class="btn btn-primary btn-xs">5s</a>
        <a onclick="setRefreshInterval(10000)" class="btn btn-primary btn-xs">10s</a>
        <a onclick="stopRefresh()" class="btn btn-default btn-xs"><i class="fa fa-pause"></i></a>
    </div>
    <div class="sm-spacer"></div>
    <a href="<?= LINK ?>mysql/add/" class="btn btn-primary btn-xs"><i class="fa fa-plus"></i> <?= __('Add server') ?></a>
</div>

<div class="sm-card">
    <div class="sm-head">
        <span class="sm-head-title">
            <i class="fa fa-server"></i> <?= __('Servers') ?>
            <?php if ($statusCounts['ok']): ?><span class="sm-pill ok"><?= $statusCounts['ok'] ?> ok</span><?php endif; ?>
            <?php if ($statusCounts['warning']): ?><span class="sm-pill warn"><?= $statusCounts['warning'] ?> warn</span><?php endif; ?>
            <?php if ($statusCounts['error']): ?><span class="sm-pill crit"><?= $statusCounts['error'] ?> err</span><?php endif; ?>
            <?php if ($statusCounts['acknowledged']): ?><span class="sm-pill ack"><?= $statusCounts['acknowledged'] ?> ack</span><?php endif; ?>
            <?php if ($statusCounts['unmonitored']): ?><span class="sm-pill off"><?= $statusCounts['unmonitored'] ?> off</span><?php endif; ?>
        </span>
        <div class="sm-search-wrap">
            <i class="fa fa-search"></i>
            <input type="text" class="sm-search" id="sm-filter" placeholder="<?= __('Filter servers...') ?>" autocomplete="off">
        </div>
    </div>
    <div class="sm-wrap">
    <div id="servermain">
<?php endif; /* end if !ajax */ ?>

<table class="sm-t">
<thead>
<tr>
    <th style="width:4px;padding:0"></th>
    <th></th>
    <th class="sm-srv"><?= __("Server") ?></th>
    <th><?= __("CVE") ?></th>
    <th><?= __("Environment") ?></th>
    <th><?= __("Tag") ?></th>
    <th><?= __("Host") ?></th>
    <th><?= __("Version") ?></th>
    <th><?= __("Organization") ?></th>
    <th><?= __("Latency") ?></th>
    <th><?= __("Seen") ?></th>
    <th><?= __("Ping") ?></th>
</tr>
</thead>
<tbody>
<?php
if (empty($data['servers'])) {
    echo '<tr><td colspan="12" class="sm-empty"><i class="fa fa-server"></i> '.__('No servers found').'</td></tr>';
} else {
    foreach ($data['servers'] as $server) {
        $isEffectiveMonitored = !empty($server['effective_is_monitored']) && (string)$server['effective_is_monitored'] === "1";
        $extra = $data['extra'][$server['id']] ?? [];
        $serverProcessing = $data['processing'][$server['id']] ?? null;
        $serverTags = $data['tag'][$server['id']] ?? [];
        $tagSearchParts = [];
        foreach ($serverTags as $tag) {
            $tagSearchParts[] = (string)($tag['name'] ?? '');
        }
        $isVipServer = !empty($server['is_vip']) && (string)$server['is_vip'] === "1";
        $isProxyServer = !empty($server['is_proxy']) && (string)$server['is_proxy'] === "1";
        $isProxyLikeServer = $isProxyServer || (!empty($extra['is_proxysql']) && (string)$extra['is_proxysql'] === "1");
        $status = serverStatus($server, $extra, $isEffectiveMonitored);
        $cveImpact = $data['cve_impacts'][(int)$server['id']] ?? null;

        $rowClass = '';
        if ($status === 'error') $rowClass = 'sm-row-err';
        elseif ($status === 'warning') $rowClass = 'sm-row-warn';
        elseif ($status === 'acknowledged') $rowClass = 'sm-row-ack';
        elseif ($status === 'unmonitored') $rowClass = 'sm-row-off';
        if (!empty($serverProcessing)) $rowClass = trim($rowClass.' sm-row-proc');

        // Build searchable text
        $searchText = strtolower($server['display_name'].' '.$server['client'].' '.$server['environment'].' '.implode(' ', $tagSearchParts).' '.$server['ip'].' '.($extra['version'] ?? ''));
?>
<tr class="<?= $rowClass ?>" data-search="<?= htmlspecialchars($searchText, ENT_QUOTES) ?>">
    <td class="sm-status"></td>

    <!-- Dot -->
    <td style="width:20px;text-align:center">
        <?php
        if (!empty($serverProcessing)) echo '<span class="sv-dot warn halo"></span>';
        elseif ($status === 'ok') echo '<span class="sv-dot ok"></span>';
        elseif ($status === 'warning') echo '<span class="sv-dot warn"></span>';
        elseif ($status === 'error') echo '<span class="sv-dot fail halo"></span>';
        elseif ($status === 'acknowledged') echo '<span class="sv-dot ok"></span>';
        elseif ($status === 'unmonitored') echo '<span class="sv-dot info"></span>';
        ?>
    </td>

    <!-- Server -->
    <td class="sm-srv">
        <div class="sm-srv-name">
            <a href="<?= LINK ?>MysqlServer/main/<?= $server['id'] ?>/pmacontrol"><?= htmlspecialchars($server['display_name']) ?></a>
            <?php if (!empty($extra['read_only']) && $extra['read_only'] === "ON"): ?>
                <span class="sm-ro">R/O</span>
            <?php endif; ?>
        </div>
    </td>

    <!-- CVE -->
    <td class="sm-cve-cell">
        <?php if (!empty($cveImpact) && !empty($cveImpact['count'])): ?>
            <?php
            $cveSeverity = server_main_cve_severity_class($cveImpact['max_severity'] ?? 'unknown');
            $cveCount = (int)$cveImpact['count'];
            $cveKnownExploited = (int)($cveImpact['known_exploited'] ?? 0);
            $cveItems = $cveImpact['items'] ?? [];
            ?>
            <a class="sm-cve-trigger <?= $cveSeverity ?>" href="<?= LINK ?>MysqlServer/cve/<?= (int)$server['id'] ?>/pmacontrol" tabindex="0" title="<?= __('Open server CVE tab') ?>">
                <i class="fa fa-shield" aria-hidden="true"></i>
                <?= $cveCount ?>
                <?php if ($cveKnownExploited > 0): ?><span class="sm-cve-kev-dot" title="CISA KEV"></span><?php endif; ?>
            </a>
            <div class="sm-cve-pop">
                <div class="sm-cve-pop-title">
                    <span><?= $cveCount ?> <?= __('matching CVEs') ?></span>
                    <span class="sm-cve-pop-sub"><?= htmlspecialchars((string)($cveImpact['product_code'] ?? ''), ENT_QUOTES) ?> <?= htmlspecialchars((string)($cveImpact['server_version'] ?? ''), ENT_QUOTES) ?></span>
                </div>
                <?php foreach ($cveItems as $item): ?>
                    <?php $itemSeverity = server_main_cve_severity_class($item['severity'] ?? 'unknown'); ?>
                    <div class="sm-cve-item">
                        <div>
                            <a class="sm-cve-id" href="https://nvd.nist.gov/vuln/detail/<?= htmlspecialchars((string)$item['cve_id'], ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer">
                                <?= htmlspecialchars((string)$item['cve_id'], ENT_QUOTES) ?>
                            </a>
                            <span class="sm-cve-sev <?= $itemSeverity ?>"><?= htmlspecialchars((string)($item['severity'] ?? 'unknown'), ENT_QUOTES) ?></span>
                            <?php if (!empty($item['known_exploited'])): ?><span class="sm-badge crit">KEV</span><?php endif; ?>
                        </div>
                        <?php if (!empty($item['title'])): ?>
                            <div class="sm-cve-item-title"><?= htmlspecialchars(server_main_cve_short_title($item['title']), ENT_QUOTES) ?></div>
                        <?php endif; ?>
                        <div class="sm-cve-item-meta">
                            CVSS <?= htmlspecialchars(server_main_cve_score($item['score'] ?? null), ENT_QUOTES) ?>
                            · <?= htmlspecialchars((string)($item['match_method'] ?? ''), ENT_QUOTES) ?>
                            · <?= htmlspecialchars((string)($item['match_confidence'] ?? ''), ENT_QUOTES) ?>
                            <?php if (!empty($item['published_at'])): ?> · <?= htmlspecialchars((string)$item['published_at'], ENT_QUOTES) ?><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($cveCount > count($cveItems)): ?>
                    <div class="sm-cve-more">+ <?= $cveCount - count($cveItems) ?> <?= __('more CVEs') ?></div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <span class="sm-cve-empty">-</span>
        <?php endif; ?>
    </td>

    <!-- Environment -->
    <td class="sm-env-cell">
        <span class="sm-env label label-<?= htmlspecialchars((string)$server['class'], ENT_QUOTES) ?>"><?= htmlspecialchars((string)$server['environment'], ENT_QUOTES) ?></span>
    </td>

    <!-- Tag -->
    <td class="sm-tag-cell">
        <?php if (!empty($serverTags)): ?>
            <?php foreach ($serverTags as $tag): ?>
                <span class="sm-tag" style="color:<?= htmlspecialchars((string)$tag['color'], ENT_QUOTES) ?>;background:<?= htmlspecialchars((string)$tag['background'], ENT_QUOTES) ?>"><?= htmlspecialchars((string)$tag['name'], ENT_QUOTES) ?></span>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="sm-tag-empty">-</span>
        <?php endif; ?>
    </td>

    <!-- Host -->
    <td class="sm-host">
        <?php
        $flag = '';
        if (!empty($data['geoip'][$server['ip']])) {
            $flag = isoToFlag($data['geoip'][$server['ip']]);
        }
        $localEndpoint = trim((string)$server['ip']).':'.trim((string)$server['port']);
        $tunnelInfo = Display::getTunnelInfoForEndpoint((string)$server['ip'], (int)$server['port']);
        $remoteEndpoint = $tunnelInfo['remote'] ?? $localEndpoint;
        $endpointTitle = $tunnelInfo['info'] ?? $localEndpoint;

        if ($remoteEndpoint !== '' && $remoteEndpoint !== $localEndpoint) {
            echo '<span data-info="'.htmlspecialchars($endpointTitle, ENT_QUOTES).'" class="text-muted">&#x1F500; '.htmlspecialchars($remoteEndpoint).'</span>';
        } else {
            if ($flag) echo $flag.' ';
            echo '<span data-info="'.htmlspecialchars($endpointTitle, ENT_QUOTES).'">'.htmlspecialchars(shorten_host_for_display($server['ip'], 22)).':'.$server['port'].'</span>';
        }
        if (!empty($server['is_ssl']) && $server['is_ssl'] === "1") echo ' &#x1F512;';
        ?>
    </td>

    <!-- Version -->
    <td class="sm-ver">
        <?php
        $is_proxysql = (empty($extra['is_proxysql'])) ? 0 : $extra['is_proxysql'];
        if ($isVipServer) {
            echo '<img title="VIP" alt="VIP" height="13" width="13" src="'.IMG.'/icon/vip.svg"/> <small>VIP</small>';
        } elseif (!empty($extra['version'])) {
            echo Format::mysqlVersion($extra['version'], $extra['version_comment'] ?? '', $is_proxysql);
        }
        if (!$isVipServer && !empty($extra['wsrep_on']) && $extra['wsrep_on'] === "ON") {
            echo ' <img title="Galera" height="11" width="11" src="'.IMG.'/icon/logo.svg"/>';
        }
        ?>
    </td>

    <!-- Organization -->
    <td class="sm-org-cell"><?= htmlspecialchars($server['client']) ?></td>

    <!-- Latency -->
    <td>
        <?php if ($isVipServer || $isProxyLikeServer): ?>
            <span class="sm-badge muted">n/a</span>
        <?php elseif (!empty($extra['avg_latency'])): ?>
            <?= format_time_ps_with_label($extra['avg_latency'], 1) ?>
        <?php endif; ?>
    </td>

    <!-- Last seen -->
    <td>
        <?php if (!empty($data['last_date'][$server['id']]['date'])):
            $seen = human_time_diff_dec($data['last_date'][$server['id']]['date'], 1);
            $subTime = time() - strtotime($data['last_date'][$server['id']]['date']);
            $seenCls = '';
            if ($subTime >= 86400) $seenCls = 'crit';
            elseif ($subTime >= 3600) $seenCls = 'warn';
            elseif ($subTime >= 60) $seenCls = 'warn';
        ?>
            <?php if ($seenCls): ?>
                <span class="sm-badge <?= $seenCls ?>"><?= $seen ?></span>
            <?php else: ?>
                <?= $seen ?>
            <?php endif; ?>
        <?php endif; ?>
    </td>

    <!-- Ping -->
    <td class="sm-ping">
        <?php if (!empty($extra['mysql_ping'])) echo Format::ping($extra['mysql_ping']); ?>
    </td>
</tr>
<?php
        // Detail row: error message on the left, status/actions on the right.
        $errMsg = '';
        if (isset($extra['mysql_available']) && $extra['mysql_available'] === "0") {
            $errMsg .= htmlspecialchars((string)($extra['mysql_error'] ?? ''), ENT_QUOTES, 'UTF-8');
            if (!empty($extra['date'])) $errMsg .= ' <span class="sm-badge info" style="margin-left:4px"><i class="fa fa-clock-o"></i> '.Display::humanDuration(time() - strtotime($extra['date'])).' ago <small style="opacity:.7">('.$extra['date'].')</small></span>';
        }
        if (!empty($extra['mysql_available']) && $extra['mysql_available'] === "2" && !empty($extra['mysql_error'])) {
            $errMsg .= htmlspecialchars((string)$extra['mysql_error'], ENT_QUOTES, 'UTF-8');
        }
        if (!empty($extra['wsrep_on']) && $extra['wsrep_on'] === "ON" && ($extra['wsrep_cluster_status'] ?? 'Primary') !== "Primary") {
            $errMsg .= ($errMsg ? ' | ' : '').'Galera node is '.$extra['wsrep_cluster_status'];
        }
        $hasStatusActions = !empty($serverProcessing)
            || (!empty($extra['mysql_available']) && $extra['mysql_available'] === "2" && empty($extra['mysql_error']))
            || (!empty($extra['wsrep_on']) && $extra['wsrep_on'] === "ON" && ($extra['wsrep_cluster_status'] ?? 'Primary') !== "Primary")
            || (empty($extra['mysql_available']) && $isEffectiveMonitored && $server['is_acknowledged'] === "0")
            || ($server['is_acknowledged'] !== "0");
        if ($errMsg || $hasStatusActions):
?>
<tr class="sm-detail-row<?= $errMsg ? ' sm-err-row' : '' ?>" data-search="<?= htmlspecialchars($searchText, ENT_QUOTES) ?>">
    <td colspan="12">
        <div class="sm-detail-line">
            <?php if ($errMsg): ?>
                <div class="sm-err-text"><i class="fa fa-exclamation-triangle"></i> <?= $errMsg ?></div>
            <?php else: ?>
                <div class="sm-detail-placeholder"></div>
            <?php endif; ?>
            <?php if ($hasStatusActions): ?>
                <div class="sm-detail-actions">
                    <?php if (!empty($serverProcessing)): ?>
                        <span class="sm-proc-label"><span class="sv-dot warn halo"></span><?= __("Processing") ?> <?= htmlspecialchars((string)$serverProcessing['time'], ENT_QUOTES) ?>s</span>
                        <?php if (!empty($serverProcessing['pid'])): ?>
                            <form method="post" action="<?= LINK ?>worker/killServerWorker/<?= (int)$server['id'] ?>" data-confirm="<?= htmlspecialchars(__('Kill this worker?'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm(this.getAttribute('data-confirm'));">
                                <input type="hidden" name="<?= $workerKillCsrfField ?>" value="<?= $workerKillCsrfToken ?>">
                                <input type="hidden" name="id_mysql_server" value="<?= (int)$server['id'] ?>">
                                <input type="hidden" name="worker_type" value="mysql">
                                <input type="hidden" name="pid" value="<?= (int)$serverProcessing['pid'] ?>">
                                <button type="submit" class="btn btn-warning btn-xs sm-kill-worker"><i class="fa fa-bolt"></i> <?= __('Kill worker') ?></button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!empty($extra['mysql_available']) && $extra['mysql_available'] === "2" && empty($extra['mysql_error'])): ?>
                        <span class="sm-badge warn">R/O</span>
                    <?php endif; ?>
                    <?php if (!empty($extra['wsrep_on']) && $extra['wsrep_on'] === "ON" && ($extra['wsrep_cluster_status'] ?? 'Primary') !== "Primary"): ?>
                        <form method="post" action="<?= LINK ?>GaleraCluster/setNodeAsPrimary/<?= (int)$server['id'] ?>" data-confirm="<?= htmlspecialchars(__('Confirm SET PRIMARY on this Galera node? Use only after quorum loss.'), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm(this.getAttribute('data-confirm'));">
                            <input type="hidden" name="<?= $galeraSetPrimaryCsrfField ?>" value="<?= $galeraSetPrimaryCsrfToken ?>">
                            <input type="hidden" name="id_mysql_server" value="<?= (int)$server['id'] ?>">
                            <input type="hidden" name="confirm_set_primary" value="SET_PRIMARY">
                            <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-play"></i> PRIMARY</button>
                        </form>
                    <?php endif; ?>
                    <?php if (empty($extra['mysql_available']) && $isEffectiveMonitored && $server['is_acknowledged'] === "0"): ?>
                        <form method="post" action="<?= LINK ?>server/acknowledge/<?= (int)$server['id'] ?>">
                            <input type="hidden" name="<?= $serverAcknowledgeCsrfField ?>" value="<?= $serverAcknowledgeCsrfToken ?>">
                            <input type="hidden" name="id_server" value="<?= (int)$server['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-xs"><i class="fa fa-star"></i> ACK</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($server['is_acknowledged'] !== "0"): ?>
                        <form method="post" action="<?= LINK ?>server/retract/<?= (int)$server['id'] ?>">
                            <input type="hidden" name="<?= $serverRetractCsrfField ?>" value="<?= $serverRetractCsrfToken ?>">
                            <input type="hidden" name="id_server" value="<?= (int)$server['id'] ?>">
                            <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-star-o"></i> Retract</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </td>
</tr>
<?php endif; ?>
<?php
    }
}
?>
</tbody>
</table>

<?php if (empty($_GET['ajax'])): ?>
    </div>
    </div>
</div>

<script>
// Inline search filter
(function() {
    var timer = null;
    var input = document.getElementById("sm-filter");
    if (!input) return;
    input.addEventListener("keyup", function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            var q = input.value.toLowerCase().trim();
            var rows = document.querySelectorAll(".sm-t tbody tr[data-search]");
            rows.forEach(function(row) {
                var text = row.getAttribute("data-search") || "";
                row.classList.toggle("sm-hidden", q !== "" && text.indexOf(q) === -1);
            });
        }, 120);
    });
})();
</script>
<?php endif; ?>
