<?php
use App\Library\Format;

$s = $data['servers'];
$d = $data['daemons'];
$r = $data['replication'];
$cvePark = is_array($data['cve_park'] ?? null) ? $data['cve_park'] : ['is_ready' => false, 'stats' => [], 'cves' => []];
$availPct = ($s['total'] > 0) ? round(($data['available'] / max(1, $data['available'] + $data['unavailable'])) * 100, 1) : 0;
$replOkPct = ($r['total'] > 0) ? round(($r['ok'] / $r['total']) * 100, 1) : 100;
$daemonOkPct = ($d['total'] > 0) ? round(($d['running'] / $d['total']) * 100, 0) : 0;

function hm_num($n) {
    if ($n >= 1e12) return round($n/1e12, 1).'T';
    if ($n >= 1e9) return round($n/1e9, 1).'B';
    if ($n >= 1e6) return round($n/1e6, 1).'M';
    if ($n >= 1e3) return round($n/1e3, 1).'K';
    return $n;
}

if (!function_exists('home_cve_severity_class')) {
    function home_cve_severity_class($severity): string {
        $severity = strtolower((string)$severity);

        return in_array($severity, ['critical', 'high', 'medium', 'low', 'none'], true) ? $severity : 'unknown';
    }
}

if (!function_exists('home_cve_score')) {
    function home_cve_score(array $cve): string {
        foreach (['cvss_v4_score', 'cvss_v3_score', 'cvss_v2_score', 'score'] as $field) {
            if (isset($cve[$field]) && is_numeric($cve[$field])) {
                return number_format((float)$cve[$field], 1);
            }
        }

        return 'n/a';
    }
}

if (!function_exists('home_cve_short_text')) {
    function home_cve_short_text($text, int $max = 96): string {
        $text = trim((string)$text);
        if (strlen($text) <= $max) {
            return $text;
        }

        return rtrim(substr($text, 0, $max - 1)).'…';
    }
}

$versionColors = ['MariaDB' => '#003545', 'MySQL' => '#e97b00', 'Percona' => '#c3281c', 'ProxySQL' => '#2a2a2a', 'MaxScale' => '#00b5e2', 'MySQL Router' => '#5a9e3f'];
?>

<style>
.hm { --bg: #f8fafc; --border: #e2e8f0; --surface: #fff; --head: linear-gradient(135deg,#0f172a,#1e3a8a);
      --ok: #10b981; --warn: #f59e0b; --crit: #ef4444; --muted: #94a3b8; --txt: #1e293b; --radius: 8px; }

.hm-grid { display: grid; gap: 16px; margin-bottom: 20px; }
.hm-grid-4 { grid-template-columns: repeat(4, 1fr); }
.hm-grid-3 { grid-template-columns: repeat(3, 1fr); }
.hm-grid-2 { grid-template-columns: repeat(2, 1fr); }
@media (max-width: 992px) { .hm-grid-4, .hm-grid-3 { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .hm-grid-4, .hm-grid-3, .hm-grid-2 { grid-template-columns: 1fr; } }

.hm-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
           overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.hm-card-head { background: var(--head); color: #fff; padding: 10px 16px; font-size: 13px; font-weight: 600;
                display: flex; align-items: center; justify-content: space-between; }
.hm-card-head i { margin-right: 8px; opacity: .7; }
.hm-card-head .hm-badge { background: rgba(255,255,255,.2); padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 400; }
.hm-card-body { padding: 16px; }

/* ── KPI tiles ── */
.hm-kpi { text-align: center; padding: 20px 16px; position: relative; }
.hm-kpi-value { font-size: 36px; font-weight: 800; color: var(--txt); line-height: 1; }
.hm-kpi-label { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); margin-top: 6px; font-weight: 600; }
.hm-kpi-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }
.hm-kpi-ring { width: 80px; height: 80px; margin: 0 auto 8px; position: relative; }
.hm-kpi-ring svg { transform: rotate(-90deg); }
.hm-kpi-ring-val { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
                    font-size: 18px; font-weight: 800; color: var(--txt); }

/* ── Bar chart ── */
.hm-bar { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 12px; }
.hm-bar-label { min-width: 110px; color: #475569; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hm-bar-track { flex: 1; height: 20px; background: #f1f5f9; border-radius: 4px; overflow: hidden; position: relative; }
.hm-bar-fill { height: 100%; border-radius: 4px; transition: width .6s ease; min-width: 2px; }
.hm-bar-count { min-width: 30px; text-align: right; color: var(--muted); font-weight: 700; font-size: 13px; }

/* ── Status list ── */
.hm-status-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
.hm-status-row:last-child { border-bottom: none; }
.hm-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.hm-dot-ok { background: var(--ok); }
.hm-dot-warn { background: var(--warn); }
.hm-dot-crit { background: var(--crit); }
.hm-dot-stop { background: #3b82f6; }
.hm-status-name { flex: 1; color: #334155; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hm-status-detail { color: var(--muted); font-size: 11px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Version pills ── */
.hm-pill { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;
           margin: 2px 4px 2px 0; color: #fff; }

/* ── Daemon compact ── */
.hm-daemon-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 6px; }
.hm-daemon { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: 4px; font-size: 11px;
             background: #f8fafc; border: 1px solid var(--border); }
.hm-daemon-name { flex: 1; font-weight: 600; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hm-daemon-badge { padding: 1px 6px; border-radius: 8px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.hm-daemon-badge.running { background: #d1fae5; color: #065f46; }
.hm-daemon-badge.stopped { background: #dbeafe; color: #1e40af; }
.hm-daemon-badge.error { background: #fee2e2; color: #991b1b; }

/* ── Alert row ── */
.hm-alert { background: #fef2f2; border-left: 4px solid var(--crit); padding: 8px 14px; margin-bottom: 4px;
            border-radius: 0 4px 4px 0; font-size: 12px; color: #991b1b; display: flex; align-items: center; gap: 8px; }
.hm-alert-name { font-weight: 700; min-width: 160px; }
.hm-alert-msg { flex: 1; color: #7f1d1d; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── CVE park view ── */
.hm-cve-summary { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.hm-cve-badge { display:inline-flex; align-items:center; gap:4px; border-radius:999px; padding:2px 8px; font-size:10px; font-weight:800; text-transform:uppercase; background:rgba(255,255,255,.18); color:#fff; }
.hm-cve-scroll { max-height:460px; overflow:auto; }
.hm-cve-table { width:100%; margin:0; font-size:12px; }
.hm-cve-table th { position:sticky; top:0; z-index:1; background:#f8fafc; color:#475569; font-size:10px; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--border); padding:7px 8px; }
.hm-cve-table td { border-bottom:1px solid #f1f5f9; padding:8px; vertical-align:top; }
.hm-cve-id { color:#1e3a8a; font-weight:900; text-decoration:none; white-space:nowrap; }
.hm-cve-id:hover { color:#0f172a; text-decoration:underline; }
.hm-cve-title { color:#334155; font-size:11px; margin-top:2px; max-width:560px; }
.hm-cve-sev { display:inline-block; border-radius:999px; color:#fff; font-size:10px; font-weight:900; min-width:58px; text-align:center; padding:2px 6px; text-transform:uppercase; }
.hm-cve-sev.critical { background:#9f1239; }
.hm-cve-sev.high { background:#dc2626; }
.hm-cve-sev.medium { background:#d97706; }
.hm-cve-sev.low { background:#2563eb; }
.hm-cve-sev.none, .hm-cve-sev.unknown { background:#64748b; }
.hm-cve-product { display:inline-block; border-radius:999px; color:#fff; font-size:10px; font-weight:800; padding:2px 7px; margin:1px 2px 1px 0; text-decoration:none; }
.hm-cve-product:hover { color:#fff; opacity:.85; text-decoration:none; }
.hm-cve-count { display:inline-block; border-radius:999px; background:#e0f2fe; color:#075985; padding:2px 8px; font-size:11px; font-weight:900; white-space:nowrap; }
.hm-cve-empty { padding:16px; color:var(--muted); background:#f8fafc; border:1px dashed var(--border); border-radius:6px; }
</style>

<div class="hm">

<!-- ════════════════════════════════════════
     ROW 1: KPI RINGS
     ════════════════════════════════════════ -->
<div class="hm-grid hm-grid-4">

    <!-- Servers -->
    <div class="hm-card">
        <div class="hm-kpi">
            <div class="hm-kpi-value"><?= (int)$s['total'] ?></div>
            <div class="hm-kpi-label"><?= __('Servers') ?></div>
            <div class="hm-kpi-sub">
                <?= (int)$s['mysql'] ?> MySQL &middot; <?= (int)$s['proxy'] ?> Proxy &middot; <?= (int)$s['vip'] ?> VIP
            </div>
        </div>
    </div>

    <!-- Availability ring -->
    <div class="hm-card">
        <div class="hm-kpi">
            <?php
            $pct = $availPct;
            $color = $pct >= 99 ? 'var(--ok)' : ($pct >= 90 ? 'var(--warn)' : 'var(--crit)');
            $circ = 2 * M_PI * 34;
            $offset = $circ * (1 - $pct / 100);
            ?>
            <div class="hm-kpi-ring">
                <svg width="80" height="80" viewBox="0 0 80 80">
                    <circle cx="40" cy="40" r="34" fill="none" stroke="#f1f5f9" stroke-width="6"/>
                    <circle cx="40" cy="40" r="34" fill="none" stroke="<?= $color ?>" stroke-width="6"
                            stroke-dasharray="<?= round($circ, 1) ?>" stroke-dashoffset="<?= round($offset, 1) ?>"
                            stroke-linecap="round"/>
                </svg>
                <span class="hm-kpi-ring-val"><?= $pct ?>%</span>
            </div>
            <div class="hm-kpi-label"><?= __('Availability') ?></div>
            <div class="hm-kpi-sub">
                <?= $data['available'] ?> <span style="color:var(--ok)">up</span>
                <?php if ($data['unavailable'] > 0): ?>
                    &middot; <?= $data['unavailable'] ?> <span style="color:var(--crit)">down</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Replication ring -->
    <div class="hm-card">
        <div class="hm-kpi">
            <?php
            $pct = $replOkPct;
            $color = $pct >= 99 ? 'var(--ok)' : ($pct >= 80 ? 'var(--warn)' : 'var(--crit)');
            $offset = $circ * (1 - $pct / 100);
            ?>
            <div class="hm-kpi-ring">
                <svg width="80" height="80" viewBox="0 0 80 80">
                    <circle cx="40" cy="40" r="34" fill="none" stroke="#f1f5f9" stroke-width="6"/>
                    <circle cx="40" cy="40" r="34" fill="none" stroke="<?= $color ?>" stroke-width="6"
                            stroke-dasharray="<?= round($circ, 1) ?>" stroke-dashoffset="<?= round($offset, 1) ?>"
                            stroke-linecap="round"/>
                </svg>
                <span class="hm-kpi-ring-val"><?= $pct ?>%</span>
            </div>
            <div class="hm-kpi-label"><?= __('Replication') ?></div>
            <div class="hm-kpi-sub">
                <?= $r['total'] ?> <?= __('channels') ?>
                <?php if ($r['error']): ?>&middot; <span style="color:var(--crit)"><?= $r['error'] ?> err</span><?php endif; ?>
                <?php if ($r['lag']): ?>&middot; <span style="color:var(--warn)"><?= $r['lag'] ?> lag</span><?php endif; ?>
                <?php if ($r['stopped']): ?>&middot; <span style="color:#3b82f6"><?= $r['stopped'] ?> stop</span><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Daemons -->
    <div class="hm-card">
        <div class="hm-kpi">
            <div class="hm-kpi-value" style="color:<?= $d['error'] > 0 ? 'var(--crit)' : ($d['stopped'] > 0 ? 'var(--warn)' : 'var(--ok)') ?>">
                <?= $d['running'] ?><span style="font-size:16px;color:var(--muted)"> / <?= $d['total'] ?></span>
            </div>
            <div class="hm-kpi-label"><?= __('Daemons') ?></div>
            <div class="hm-kpi-sub">
                <?php if ($d['error']): ?><span style="color:var(--crit)"><?= $d['error'] ?> error</span> &middot; <?php endif; ?>
                <?php if ($d['stopped']): ?><span style="color:#3b82f6"><?= $d['stopped'] ?> stopped</span> &middot; <?php endif; ?>
                <a href="<?= LINK ?>daemon/index" style="color:#1e3a8a;font-weight:600"><?= __('manage') ?></a>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     ROW 2: ALERTS (if any servers down)
     ════════════════════════════════════════ -->
<?php if (!empty($data['unavailable_servers'])): ?>
<div class="hm-card" style="margin-bottom:16px;border-left:4px solid var(--crit)">
    <div class="hm-card-head" style="background:linear-gradient(135deg,#7f1d1d,#991b1b)">
        <span><i class="fa fa-exclamation-triangle"></i> <?= __('Servers down') ?> (<?= count($data['unavailable_servers']) ?>)</span>
        <a href="<?= LINK ?>server/main/show:all/" class="hm-badge" style="color:#fff;text-decoration:none"><?= __('View all') ?></a>
    </div>
    <div class="hm-card-body" style="padding:8px 16px">
        <?php foreach (array_slice($data['unavailable_servers'], 0, 8) as $srv): ?>
        <div class="hm-alert">
            <i class="fa fa-times-circle"></i>
            <span class="hm-alert-name">
                <a href="<?= LINK ?>MysqlServer/main/<?= (int)$srv['id'] ?>/pmacontrol" style="color:#991b1b"><?= htmlspecialchars($srv['name']) ?></a>
            </span>
            <span style="color:#94a3b8"><?= htmlspecialchars($srv['ip']) ?>:<?= (int)$srv['port'] ?></span>
            <span class="hm-alert-msg"><?= htmlspecialchars($srv['error']) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (count($data['unavailable_servers']) > 8): ?>
        <div style="text-align:center;padding:6px;color:var(--muted);font-size:12px">
            +<?= count($data['unavailable_servers']) - 8 ?> <?= __('more') ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($data['orphan_refreshes'])): ?>
<div class="hm-card" style="margin-bottom:16px;border-left:4px solid #f59e0b">
    <div class="hm-card-head" style="background:linear-gradient(135deg,#92400e,#b45309)">
        <span><i class="fa fa-hdd-o"></i> <?= __('Orphan refresh dumps') ?> (<?= count($data['orphan_refreshes']) ?>)</span>
        <span class="hm-badge" style="color:#fff"><?= Format::bytes((int) $data['orphan_refreshes_total_size']) ?></span>
    </div>
    <div class="hm-card-body" style="padding:8px 16px">
        <?php foreach ($data['orphan_refreshes'] as $orphan): ?>
        <div class="hm-alert" style="background:#fffbeb;border-left-color:#f59e0b">
            <i class="fa fa-archive" style="color:#b45309"></i>
            <span class="hm-alert-name" style="color:#92400e">
                <?= htmlspecialchars((string) $orphan['path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </span>
            <span class="hm-alert-msg" style="color:#92400e">
                <?= Format::bytes((int) $orphan['size_bytes']) ?>
                — <?= (int) round(((int) $orphan['age_seconds']) / 3600) ?> h
                <?php if (!empty($orphan['related_job_id'])): ?>
                    —
                    <a href="<?= LINK ?>job/index" style="color:#92400e;text-decoration:underline">
                        job #<?= (int) $orphan['related_job_id'] ?>
                    </a>
                    <?= htmlspecialchars((string) ($orphan['related_job_status'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                <?php endif; ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($data['stuck_analyses'])): ?>
<div class="hm-card" style="margin-bottom:16px">
    <div class="hm-card-head" style="background:linear-gradient(135deg,#7f1d1d,#991b1b)">
        <span><i class="fa fa-exclamation-circle"></i> <?= __('Stuck Binlog Analyses') ?> (<?= count($data['stuck_analyses']) ?>)</span>
    </div>
    <div class="hm-card-body" style="padding:0">
        <?php foreach ($data['stuck_analyses'] as $sa): ?>
        <div class="hm-alert">
            <span class="hm-alert-name">
                <a href="<?= LINK ?>slave/show/<?= (int)$sa['id_mysql_server'] ?>/"><?= htmlspecialchars($sa['display_name'] ?: $sa['ip']) ?></a>
            </span>
            <span class="hm-alert-msg">
                Analysis #<?= $sa['id'] ?> running since <?= $sa['minutes_ago'] ?> min
                (<?= $sa['time_start'] ?> &rarr; <?= $sa['time_end'] ?>)
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($cvePark['is_ready'])): ?>
<?php $cveStats = is_array($cvePark['stats'] ?? null) ? $cvePark['stats'] : []; ?>
<div class="hm-card" style="margin-bottom:16px;border-left:4px solid #9f1239">
    <div class="hm-card-head" style="background:linear-gradient(135deg,#111827,#9f1239)">
        <span>
            <i class="fa fa-shield"></i>
            <?= __('CVEs impacting the park') ?>
            <span class="hm-badge"><?= (int)($cveStats['total_cves'] ?? 0) ?></span>
        </span>
        <span class="hm-cve-summary">
            <span class="hm-cve-badge"><?= (int)($cveStats['impacted_servers'] ?? 0) ?> <?= __('servers') ?></span>
            <span class="hm-cve-badge"><?= (int)($cveStats['critical_cves'] ?? 0) ?> critical</span>
            <span class="hm-cve-badge"><?= (int)($cveStats['high_cves'] ?? 0) ?> high</span>
            <span class="hm-cve-badge"><?= (int)($cveStats['known_exploited_cves'] ?? 0) ?> KEV</span>
            <a href="<?= LINK ?>cve/index" class="hm-badge" style="color:#fff;text-decoration:none"><?= __('Inventory') ?></a>
        </span>
    </div>
    <div class="hm-card-body" style="padding:0">
        <?php if (empty($cvePark['cves'])): ?>
            <div style="padding:14px">
                <div class="hm-cve-empty">
                    <?= __('No active CVE impact is currently present in cve_server_cache.') ?>
                </div>
            </div>
        <?php else: ?>
            <div class="hm-cve-scroll">
                <table class="hm-cve-table">
                    <thead>
                        <tr>
                            <th><?= __('CVE') ?></th>
                            <th><?= __('Severity') ?></th>
                            <th><?= __('Products') ?></th>
                            <th><?= __('Servers') ?></th>
                            <th><?= __('Published') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cvePark['cves'] as $cve): ?>
                            <?php
                            $severityClass = home_cve_severity_class($cve['severity'] ?? 'unknown');
                            $products = is_array($cve['products'] ?? null) ? $cve['products'] : [];
                            ?>
                            <tr>
                                <td>
                                    <a class="hm-cve-id" href="<?= LINK ?>cve/index?q=<?= urlencode((string)$cve['cve_id']) ?>">
                                        <?= htmlspecialchars((string)$cve['cve_id'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                    <?php if (!empty($cve['known_exploited'])): ?>
                                        <span class="hm-cve-sev critical">KEV</span>
                                    <?php endif; ?>
                                    <div class="hm-cve-title">
                                        <?= htmlspecialchars(home_cve_short_text($cve['title'] ?: $cve['summary']), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="hm-cve-sev <?= $severityClass ?>"><?= htmlspecialchars((string)($cve['severity'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <div style="color:var(--muted);font-size:11px;margin-top:3px">CVSS <?= htmlspecialchars(home_cve_score($cve), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td>
                                    <?php foreach ($products as $product): ?>
                                        <a
                                            href="<?= LINK ?>cve/index/<?= htmlspecialchars((string)$product['product_code'], ENT_QUOTES, 'UTF-8') ?>"
                                            class="hm-cve-product"
                                            style="background:<?= htmlspecialchars((string)$product['color'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars((string)$product['product_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    <?php endforeach; ?>
                                </td>
                                <td><span class="hm-cve-count"><?= (int)($cve['impacted_servers'] ?? 0) ?></span></td>
                                <td>
                                    <?= htmlspecialchars((string)($cve['published_at'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($cve['last_calculated'])): ?>
                                        <div style="color:var(--muted);font-size:10px"><?= __('cache') ?> <?= htmlspecialchars((string)$cve['last_calculated'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════
     ROW 3: BREAKDOWN CHARTS
     ════════════════════════════════════════ -->
<div class="hm-grid hm-grid-3">

    <!-- Environments -->
    <div class="hm-card">
        <div class="hm-card-head">
            <span><i class="fa fa-tags"></i> <?= __('Environments') ?></span>
            <span class="hm-badge"><?= count($data['environments']) ?></span>
        </div>
        <div class="hm-card-body">
            <?php
            $maxEnv = max(array_column($data['environments'], 'cnt') ?: [1]);
            $envColors = ['danger' => '#ef4444', 'warning' => '#f59e0b', 'default' => '#94a3b8', 'info' => '#3b82f6', 'success' => '#10b981', 'primary' => '#6366f1'];
            foreach ($data['environments'] as $env): ?>
            <div class="hm-bar">
                <span class="hm-bar-label"><?= htmlspecialchars($env['name']) ?></span>
                <div class="hm-bar-track">
                    <div class="hm-bar-fill" style="width:<?= round($env['cnt']/$maxEnv*100) ?>%;background:<?= $envColors[$env['class']] ?? '#64748b' ?>"></div>
                </div>
                <span class="hm-bar-count"><?= $env['cnt'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Clients -->
    <div class="hm-card">
        <div class="hm-card-head">
            <span><i class="fa fa-building"></i> <?= __('Clients') ?></span>
            <span class="hm-badge"><?= count($data['clients']) ?></span>
        </div>
        <div class="hm-card-body">
            <?php
            $maxCli = max(array_column($data['clients'], 'cnt') ?: [1]);
            $cliColors = ['#1e3a8a','#3b82f6','#0ea5e9','#06b6d4','#14b8a6','#10b981','#84cc16','#eab308','#f97316','#ef4444'];
            foreach ($data['clients'] as $i => $cli): ?>
            <div class="hm-bar">
                <span class="hm-bar-label"><?= htmlspecialchars($cli['name']) ?></span>
                <div class="hm-bar-track">
                    <div class="hm-bar-fill" style="width:<?= round($cli['cnt']/$maxCli*100) ?>%;background:<?= $cliColors[$i % count($cliColors)] ?>"></div>
                </div>
                <span class="hm-bar-count"><?= $cli['cnt'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Versions -->
    <div class="hm-card">
        <div class="hm-card-head">
            <span><i class="fa fa-code-fork"></i> <?= __('Versions') ?></span>
            <span class="hm-badge"><?= hm_num($data['ts_rows']) ?> <?= __('datapoints') ?></span>
        </div>
        <div class="hm-card-body">
            <?php if (!empty($data['versions'])):
                $maxVer = max($data['versions']);
                foreach ($data['versions'] as $ver => $cnt):
                    $fork = explode(' ', $ver)[0];
                    $clr = $versionColors[$fork] ?? '#64748b';
            ?>
            <div class="hm-bar">
                <span class="hm-bar-label"><?= htmlspecialchars($ver) ?></span>
                <div class="hm-bar-track">
                    <div class="hm-bar-fill" style="width:<?= round($cnt/$maxVer*100) ?>%;background:<?= $clr ?>"></div>
                </div>
                <span class="hm-bar-count"><?= $cnt ?></span>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     ROW 4: DAEMONS COMPACT
     ════════════════════════════════════════ -->
<div class="hm-card" style="margin-bottom:16px">
    <div class="hm-card-head">
        <span><i class="fa fa-cogs"></i> <?= __('Daemons') ?></span>
        <a href="<?= LINK ?>daemon/index" class="hm-badge" style="color:#fff;text-decoration:none">
            <?= $d['running'] ?> / <?= $d['total'] ?> running
        </a>
    </div>
    <div class="hm-card-body">
        <div class="hm-daemon-grid">
            <?php foreach ($d['list'] as $daemon): ?>
            <div class="hm-daemon">
                <span class="hm-dot hm-dot-<?= $daemon['status'] === 'running' ? 'ok' : ($daemon['status'] === 'error' ? 'crit' : 'stop') ?>"></span>
                <span class="hm-daemon-name" title="<?= htmlspecialchars($daemon['name']) ?>"><?= htmlspecialchars($daemon['name']) ?></span>
                <span class="hm-daemon-badge <?= $daemon['status'] ?>"><?= $daemon['status'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     ROW 5: QUICK LINKS
     ════════════════════════════════════════ -->
<div class="hm-grid hm-grid-4">
    <a href="<?= LINK ?>server/main/" class="hm-card" style="text-decoration:none">
        <div class="hm-kpi" style="padding:14px">
            <div style="font-size:24px;color:#1e3a8a"><i class="fa fa-server"></i></div>
            <div class="hm-kpi-label"><?= __('Servers') ?></div>
        </div>
    </a>
    <a href="<?= LINK ?>slave/index/" class="hm-card" style="text-decoration:none">
        <div class="hm-kpi" style="padding:14px">
            <div style="font-size:24px;color:#1e3a8a"><i class="fa fa-sitemap"></i></div>
            <div class="hm-kpi-label"><?= __('Replication') ?></div>
        </div>
    </a>
    <a href="<?= LINK ?>cluster/index/" class="hm-card" style="text-decoration:none">
        <div class="hm-kpi" style="padding:14px">
            <div style="font-size:24px;color:#1e3a8a"><i class="fa fa-project-diagram fa-sitemap"></i></div>
            <div class="hm-kpi-label"><?= __('Architecture') ?></div>
        </div>
    </a>
    <a href="<?= LINK ?>daemon/index/" class="hm-card" style="text-decoration:none">
        <div class="hm-kpi" style="padding:14px">
            <div style="font-size:24px;color:#1e3a8a"><i class="fa fa-cogs"></i></div>
            <div class="hm-kpi-label"><?= __('Daemons') ?></div>
        </div>
    </a>
</div>

</div>
