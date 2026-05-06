<?php

use Glial\Synapse\FactoryController;

FactoryController::addNode("MysqlServer", "menu", $param);

if (!function_exists('mysqlserver_cve_severity_class')) {
    function mysqlserver_cve_severity_class($severity): string
    {
        $severity = strtolower((string)$severity);

        return in_array($severity, ['critical', 'high', 'medium', 'low', 'none'], true) ? $severity : 'unknown';
    }
}

if (!function_exists('mysqlserver_cve_score')) {
    function mysqlserver_cve_score(array $cve): string
    {
        foreach (['cvss_v4_score', 'cvss_v3_score', 'cvss_v2_score', 'score'] as $field) {
            if (isset($cve[$field]) && is_numeric($cve[$field])) {
                return number_format((float)$cve[$field], 1);
            }
        }

        return 'n/a';
    }
}

if (!function_exists('mysqlserver_cve_short_text')) {
    function mysqlserver_cve_short_text($text, int $max = 280): string
    {
        $text = trim((string)$text);
        if (strlen($text) <= $max) {
            return $text;
        }

        return rtrim(substr($text, 0, $max - 1)).'…';
    }
}

$server = $data['server'] ?? [];
$context = $data['context'] ?? null;
$summary = $data['summary'] ?? [];
$cves = is_array($data['cves'] ?? null) ? $data['cves'] : [];
$critical = 0;
$high = 0;
$knownExploited = 0;

foreach ($cves as $cve) {
    if (($cve['severity'] ?? '') === 'critical') {
        $critical++;
    }
    if (($cve['severity'] ?? '') === 'high') {
        $high++;
    }
    if (!empty($cve['known_exploited'])) {
        $knownExploited++;
    }
}
?>

<style>
.mscve { --border:#e2e8f0; --muted:#64748b; --txt:#0f172a; --surface:#fff; --critical:#9f1239; --high:#dc2626; --medium:#d97706; --low:#2563eb; }
.mscve-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin:18px 0 14px; }
.mscve-title { margin:0; font-size:22px; font-weight:800; color:var(--txt); }
.mscve-sub { color:var(--muted); font-size:12px; margin-top:4px; }
.mscve-actions { display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; }
.mscve-kpis { display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-bottom:16px; }
.mscve-kpi { background:var(--surface); border:1px solid var(--border); border-radius:8px; padding:14px; box-shadow:0 1px 3px rgba(15,23,42,.06); }
.mscve-kpi-value { font-size:28px; font-weight:900; color:var(--txt); line-height:1; }
.mscve-kpi-label { margin-top:5px; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; font-size:10px; font-weight:800; }
.mscve-card { background:var(--surface); border:1px solid var(--border); border-radius:8px; margin-bottom:12px; overflow:hidden; box-shadow:0 1px 3px rgba(15,23,42,.05); }
.mscve-card-head { display:flex; gap:10px; justify-content:space-between; align-items:flex-start; padding:12px 14px; border-bottom:1px solid var(--border); background:#f8fafc; }
.mscve-id { font-size:15px; font-weight:900; color:#1e3a8a; text-decoration:none; }
.mscve-id:hover { color:#0f172a; text-decoration:underline; }
.mscve-title-small { margin-top:3px; color:#334155; font-weight:700; }
.mscve-card-body { padding:12px 14px; }
.mscve-risk { color:#334155; line-height:1.45; margin-bottom:10px; }
.mscve-meta { display:flex; flex-wrap:wrap; gap:6px; align-items:center; color:var(--muted); font-size:11px; }
.mscve-pill { display:inline-block; border-radius:999px; padding:2px 8px; color:#fff; font-size:10px; font-weight:900; text-transform:uppercase; }
.mscve-pill.critical { background:var(--critical); }
.mscve-pill.high { background:var(--high); }
.mscve-pill.medium { background:var(--medium); }
.mscve-pill.low { background:var(--low); }
.mscve-pill.none, .mscve-pill.unknown { background:#64748b; }
.mscve-pill.kev { background:#111827; }
.mscve-product { display:inline-block; border-radius:999px; padding:2px 8px; color:#fff; font-size:10px; font-weight:800; }
.mscve-table { width:100%; margin:8px 0 0; font-size:12px; }
.mscve-table th { color:#475569; font-size:10px; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--border); padding:5px; }
.mscve-table td { border-bottom:1px solid #f1f5f9; padding:6px 5px; vertical-align:top; }
.mscve-links { margin-top:8px; display:flex; flex-wrap:wrap; gap:6px; }
.mscve-link { background:#eef2ff; color:#3730a3; border-radius:6px; padding:3px 7px; font-size:11px; font-weight:700; text-decoration:none; }
.mscve-link:hover { background:#e0e7ff; color:#111827; text-decoration:none; }
.mscve-empty { background:#f8fafc; border:1px dashed var(--border); border-radius:8px; padding:18px; color:var(--muted); }
@media (max-width: 900px) {
    .mscve-head { display:block; }
    .mscve-actions { justify-content:flex-start; margin-top:10px; }
    .mscve-kpis { grid-template-columns:repeat(2, 1fr); }
}
@media (max-width: 520px) { .mscve-kpis { grid-template-columns:1fr; } }
</style>

<div class="mscve">
    <div class="mscve-head">
        <div>
            <h2 class="mscve-title">
                <i class="fa fa-shield" aria-hidden="true"></i>
                <?= __('Server CVEs') ?>
            </h2>
            <div class="mscve-sub">
                <?= htmlspecialchars((string)($server['display_name'] ?? 'server #'.(int)$data['id_mysql_server']), ENT_QUOTES, 'UTF-8') ?>
                · <?= htmlspecialchars((string)($server['ip'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?>:<?= (int)($server['port'] ?? 0) ?>
                <?php if (is_array($context)): ?>
                    · <?= htmlspecialchars((string)$context['product_code'], ENT_QUOTES, 'UTF-8') ?>
                    <?= htmlspecialchars((string)$context['version'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="mscve-actions">
            <a class="btn btn-default btn-sm" href="<?= LINK ?>server/main/">
                <i class="fa fa-list"></i> <?= __('Server list') ?>
            </a>
            <a class="btn btn-default btn-sm" href="<?= LINK ?>cve/index">
                <i class="fa fa-database"></i> <?= __('CVE inventory') ?>
            </a>
        </div>
    </div>

    <?php if (empty($data['is_ready'])): ?>
        <div class="mscve-empty">
            <?= __('CVE catalog tables are not available yet. Run the CVE catalog migration and rebuild first.') ?>
        </div>
    <?php elseif (!is_array($context)): ?>
        <div class="mscve-empty">
            <?= __('No supported product/version context was detected for this server. VIPs and unsupported components are not matched.') ?>
        </div>
    <?php else: ?>
        <div class="mscve-kpis">
            <div class="mscve-kpi">
                <div class="mscve-kpi-value"><?= count($cves) ?></div>
                <div class="mscve-kpi-label"><?= __('Matching CVEs') ?></div>
            </div>
            <div class="mscve-kpi">
                <div class="mscve-kpi-value" style="color:var(--critical)"><?= $critical ?></div>
                <div class="mscve-kpi-label"><?= __('Critical') ?></div>
            </div>
            <div class="mscve-kpi">
                <div class="mscve-kpi-value" style="color:var(--high)"><?= $high ?></div>
                <div class="mscve-kpi-label"><?= __('High') ?></div>
            </div>
            <div class="mscve-kpi">
                <div class="mscve-kpi-value" style="color:#111827"><?= $knownExploited ?></div>
                <div class="mscve-kpi-label"><?= __('CISA KEV') ?></div>
            </div>
        </div>

        <?php if ($cves === []): ?>
            <div class="mscve-empty">
                <?= __('No active CVE impact is currently cached for this server/version.') ?>
            </div>
        <?php endif; ?>

        <?php foreach ($cves as $cve): ?>
            <?php
            $severityClass = mysqlserver_cve_severity_class($cve['severity'] ?? 'unknown');
            $references = is_array($cve['references'] ?? null) ? $cve['references'] : [];
            $affectedRows = is_array($cve['affected'] ?? null) ? $cve['affected'] : [];
            ?>
            <div class="mscve-card">
                <div class="mscve-card-head">
                    <div>
                        <a class="mscve-id" href="https://nvd.nist.gov/vuln/detail/<?= htmlspecialchars((string)$cve['cve_id'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                            <?= htmlspecialchars((string)$cve['cve_id'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <?php if (!empty($cve['title'])): ?>
                            <div class="mscve-title-small"><?= htmlspecialchars(mysqlserver_cve_short_text($cve['title'], 180), ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mscve-meta">
                        <span class="mscve-pill <?= $severityClass ?>"><?= htmlspecialchars((string)($cve['severity'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="mscve-pill <?= $severityClass ?>">CVSS <?= htmlspecialchars(mysqlserver_cve_score($cve), ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if (!empty($cve['known_exploited'])): ?><span class="mscve-pill kev">KEV</span><?php endif; ?>
                    </div>
                </div>
                <div class="mscve-card-body">
                    <div class="mscve-risk">
                        <strong><?= __('Risk / summary') ?>:</strong>
                        <?= htmlspecialchars(mysqlserver_cve_short_text($cve['summary'] ?: $cve['title'], 500), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="mscve-meta">
                        <span class="mscve-product" style="background:<?= htmlspecialchars((string)($cve['color'] ?? '#334155'), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string)($cve['product_name'] ?? $cve['product_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span><?= __('Published') ?>: <?= htmlspecialchars((string)($cve['published_at'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= __('Modified') ?>: <?= htmlspecialchars((string)($cve['last_modified_at'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= __('Match') ?>: <?= htmlspecialchars((string)($cve['server_match_method'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars((string)($cve['server_match_confidence'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                    <?php if ($affectedRows !== []): ?>
                        <table class="mscve-table">
                            <thead>
                                <tr>
                                    <th><?= __('Affected versions') ?></th>
                                    <th><?= __('Fixed in') ?></th>
                                    <th><?= __('Source') ?></th>
                                    <th><?= __('Confidence') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($affectedRows as $affected): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string)($affected['version_text'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string)($affected['fixed_version'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?php if (!empty($affected['source_url'])): ?>
                                                <a href="<?= htmlspecialchars((string)$affected['source_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                                                    <?= htmlspecialchars((string)($affected['source_code'] ?? 'source'), ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            <?php else: ?>
                                                <?= htmlspecialchars((string)($affected['source_code'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars((string)($affected['match_method'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars((string)($affected['match_confidence'] ?? 'n/a'), ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php if ($references !== []): ?>
                        <div class="mscve-links">
                            <?php foreach ($references as $ref): ?>
                                <a class="mscve-link" href="<?= htmlspecialchars((string)$ref['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                                    <?= htmlspecialchars(mysqlserver_cve_short_text($ref['label'], 42), ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
