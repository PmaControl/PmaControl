<?php

function cve_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function cve_severity_class($severity): string
{
    $severity = strtolower((string)$severity);
    return in_array($severity, ['critical', 'high', 'medium', 'low', 'none'], true) ? $severity : 'unknown';
}

function cve_score(array $cve): string
{
    foreach (['cvss_v4_score', 'cvss_v3_score', 'cvss_v2_score'] as $field) {
        if ($cve[$field] !== null && $cve[$field] !== '') {
            return number_format((float)$cve[$field], 1);
        }
    }

    return 'n/a';
}

function cve_query_suffix(string $query): string
{
    return $query !== '' ? '?q='.urlencode($query) : '';
}

$stats = $data['stats'];
$filter = $data['filter'];
$query = (string)($data['query'] ?? '');
$filterPath = $filter !== 'all' ? '/'.rawurlencode((string)$filter) : '';
?>

<style>
.cve-plugin { --ink:#172033; --muted:#667085; --line:#d8dee9; --panel:#ffffff; --soft:#f6f8fb; --critical:#9f1239; --high:#dc2626; --medium:#d97706; --low:#2563eb; --unknown:#64748b; }
.cve-hero { background:linear-gradient(135deg,#111827,#164e63); color:#fff; border-radius:10px; padding:22px 24px; margin-bottom:16px; box-shadow:0 10px 24px rgba(15,23,42,.18); }
.cve-hero h2 { margin:0 0 6px; font-weight:800; letter-spacing:.2px; }
.cve-hero p { margin:0; color:#cbd5e1; }
.cve-kpis { display:grid; grid-template-columns:repeat(5,1fr); gap:10px; margin-bottom:16px; }
.cve-kpi { background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:14px; }
.cve-kpi-value { font-size:26px; line-height:1; font-weight:800; color:var(--ink); }
.cve-kpi-label { margin-top:6px; color:var(--muted); text-transform:uppercase; font-size:10px; letter-spacing:.08em; font-weight:700; }
.cve-filters { background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:12px; margin-bottom:16px; }
.cve-filter { display:inline-flex; align-items:center; gap:7px; border:1px solid var(--line); border-radius:999px; padding:6px 10px; margin:3px; color:#334155; background:#fff; text-decoration:none; font-size:12px; font-weight:700; }
.cve-filter:hover, .cve-filter:focus { text-decoration:none; background:#eef6ff; color:#0f172a; }
.cve-filter.active { background:#0f172a; color:#fff; border-color:#0f172a; }
.cve-filter .count { opacity:.72; font-weight:600; }
.cve-search { display:flex; gap:8px; margin-bottom:10px; }
.cve-search input { max-width:360px; }
.cve-table-wrap { background:#fff; border:1px solid var(--line); border-radius:8px; overflow:hidden; }
.cve-table { margin-bottom:0; }
.cve-table > thead > tr > th { background:#f8fafc; color:#334155; border-bottom:1px solid var(--line); font-size:12px; text-transform:uppercase; letter-spacing:.06em; }
.cve-id { font-weight:800; font-size:15px; color:#0f172a; }
.cve-title { margin-top:4px; color:#334155; max-width:620px; }
.cve-meta { margin-top:6px; color:var(--muted); font-size:11px; }
.cve-sev { display:inline-block; min-width:72px; text-align:center; border-radius:999px; padding:4px 8px; color:#fff; font-size:11px; font-weight:800; text-transform:uppercase; }
.cve-sev.critical { background:var(--critical); }
.cve-sev.high { background:var(--high); }
.cve-sev.medium { background:var(--medium); }
.cve-sev.low { background:var(--low); }
.cve-sev.none, .cve-sev.unknown { background:var(--unknown); }
.cve-score { margin-top:6px; color:#334155; font-weight:700; font-size:12px; }
.cve-kev { display:inline-block; margin-top:6px; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; border-radius:5px; padding:3px 6px; font-size:11px; font-weight:800; }
.cve-affected { display:grid; gap:7px; max-width:700px; }
.cve-aff-row { border:1px solid #e5e7eb; border-radius:7px; padding:8px; background:#fbfdff; }
.cve-product-tag { display:inline-flex; align-items:center; gap:5px; color:#fff; border-radius:999px; padding:3px 8px; font-size:11px; font-weight:800; text-decoration:none; }
.cve-product-tag:hover, .cve-product-tag:focus { color:#fff; text-decoration:none; filter:brightness(.92); }
.cve-version { margin-top:5px; font-size:12px; color:#1f2937; word-break:break-word; }
.cve-fixed { margin-top:3px; font-size:11px; color:#047857; font-weight:700; }
.cve-source { margin-top:4px; color:#64748b; font-size:11px; }
.cve-source a { color:#2563eb; }
.cve-more { color:#64748b; font-size:11px; font-weight:700; padding:5px; }
.cve-servers { margin-top:10px; display:flex; flex-direction:column; gap:5px; }
.cve-servers-title { color:#475569; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
.cve-server { display:inline-block; border:1px solid #e2e8f0; background:#f8fafc; border-radius:6px; padding:5px 7px; color:#0f172a; text-decoration:none; }
.cve-server:hover, .cve-server:focus { background:#eef2ff; color:#111827; text-decoration:none; }
.cve-server-name { font-weight:800; }
.cve-server-version { margin-left:5px; color:#1d4ed8; font-weight:700; }
.cve-server-endpoint { display:block; color:#64748b; font-size:11px; margin-top:1px; }
@media (max-width: 900px) {
    .cve-kpis { grid-template-columns:repeat(2,1fr); }
    .cve-table > thead { display:none; }
    .cve-table, .cve-table > tbody, .cve-table > tbody > tr, .cve-table > tbody > tr > td { display:block; width:100%; }
    .cve-table > tbody > tr { border-bottom:1px solid var(--line); }
}
</style>

<div class="cve-plugin">
    <div class="cve-hero">
        <h2><i class="fa fa-shield" aria-hidden="true"></i> <?= __('CVE Inventory') ?></h2>
        <p><?= __('Plugin view for MySQL-like CVEs, product filters and affected version ranges.') ?></p>
    </div>

    <?php if (empty($data['is_ready'])): ?>
        <div class="alert alert-warning">
            <strong><?= __('CVE catalog is not initialized.') ?></strong>
            <?= __('Run the CVE source migration and rebuild the catalog before using this screen.') ?>
            <br>
            <code>mysql pmacontrol &lt; sql/incremental_v2/20260506_cve_catalog_plugin.sql</code>
            <br>
            <code>php script/cve_catalog_rebuild.php --database=pmacontrol</code>
        </div>
    <?php else: ?>

    <div class="cve-kpis">
        <div class="cve-kpi">
            <div class="cve-kpi-value"><?= (int)$stats['total_cves'] ?></div>
            <div class="cve-kpi-label"><?= __('CVEs') ?></div>
        </div>
        <div class="cve-kpi">
            <div class="cve-kpi-value"><?= (int)$stats['critical_cves'] ?></div>
            <div class="cve-kpi-label"><?= __('Critical') ?></div>
        </div>
        <div class="cve-kpi">
            <div class="cve-kpi-value"><?= (int)$stats['high_cves'] ?></div>
            <div class="cve-kpi-label"><?= __('High') ?></div>
        </div>
        <div class="cve-kpi">
            <div class="cve-kpi-value"><?= (int)$stats['known_exploited_cves'] ?></div>
            <div class="cve-kpi-label"><?= __('Known Exploited') ?></div>
        </div>
        <div class="cve-kpi">
            <div class="cve-kpi-value"><?= (int)$stats['affected_versions'] ?></div>
            <div class="cve-kpi-label"><?= __('Affected Ranges') ?></div>
        </div>
    </div>

    <div class="cve-filters">
        <form class="cve-search" method="GET" action="<?= LINK ?>cve/index<?= cve_h($filterPath) ?>">
            <input class="form-control" type="search" name="q" value="<?= cve_h($query) ?>" placeholder="<?= __('Search CVE, title, version or source') ?>">
            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> <?= __('Search') ?></button>
            <?php if ($query !== ''): ?>
                <a class="btn btn-default" href="<?= LINK ?>cve/index<?= cve_h($filterPath) ?>"><?= __('Clear') ?></a>
            <?php endif; ?>
        </form>

        <a class="cve-filter <?= $filter === 'all' ? 'active' : '' ?>" href="<?= LINK ?>cve/index<?= cve_h(cve_query_suffix($query)) ?>">
            <i class="fa fa-list" aria-hidden="true"></i> <?= __('All') ?>
            <span class="count"><?= (int)$stats['total_cves'] ?></span>
        </a>
        <?php foreach ($data['products'] as $product): ?>
            <a class="cve-filter <?= $filter === $product['product_code'] ? 'active' : '' ?>"
               href="<?= LINK ?>cve/index/<?= cve_h($product['product_code']) ?><?= cve_h(cve_query_suffix($query)) ?>">
                <i class="<?= cve_h($product['icon_class']) ?>" aria-hidden="true" style="color:<?= cve_h($product['color']) ?>"></i>
                <?= cve_h($product['product_name']) ?>
                <span class="count"><?= (int)$product['cve_count'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="cve-table-wrap">
        <table class="table table-hover cve-table">
            <thead>
                <tr>
                    <th style="width:42%"><?= __('CVE') ?></th>
                    <th style="width:12%"><?= __('Risk') ?></th>
                    <th><?= __('Impacted products and versions') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($data['cves'])): ?>
                <tr>
                    <td colspan="3" class="text-muted"><?= __('No CVE found for this product filter.') ?></td>
                </tr>
            <?php endif; ?>
            <?php foreach ($data['cves'] as $cve): ?>
                <?php
                $severity = cve_severity_class($cve['severity']);
                $affected = $cve['affected'];
                $displayed = array_slice($affected, 0, 14);
                $remaining = max(0, count($affected) - count($displayed));
                ?>
                <tr>
                    <td>
                        <div class="cve-id">
                            <a href="https://nvd.nist.gov/vuln/detail/<?= cve_h($cve['cve_id']) ?>" target="_blank" rel="noopener noreferrer">
                                <?= cve_h($cve['cve_id']) ?>
                            </a>
                        </div>
                        <div class="cve-title"><?= cve_h($cve['title'] ?: $cve['summary']) ?></div>
                        <div class="cve-meta">
                            <?= __('Published') ?>: <?= cve_h($cve['published_at'] ?: 'n/a') ?>
                            &middot; <?= __('Updated') ?>: <?= cve_h($cve['last_modified_at'] ?: 'n/a') ?>
                        </div>
                        <?php
                        $impactedServers = is_array($cve['impacted_servers'] ?? null) ? $cve['impacted_servers'] : [];
                        $displayedServers = array_slice($impactedServers, 0, 10);
                        $remainingServers = max(0, count($impactedServers) - count($displayedServers));
                        ?>
                        <?php if ($displayedServers !== []): ?>
                            <div class="cve-servers">
                                <div class="cve-servers-title"><?= __('Impacted servers') ?></div>
                                <?php foreach ($displayedServers as $impactedServer): ?>
                                    <?php
                                    $impactedServerId = (int)($impactedServer['id_mysql_server'] ?? 0);
                                    $impactedServerName = trim((string)($impactedServer['display_name'] ?? ''));
                                    if ($impactedServerName === '') {
                                        $impactedServerName = 'server #'.$impactedServerId;
                                    }
                                    $impactedProduct = trim((string)($impactedServer['product_code'] ?? ''));
                                    $impactedVersion = trim((string)($impactedServer['server_version'] ?? ''));
                                    $impactedEndpoint = trim((string)($impactedServer['ip'] ?? ''));
                                    if (!empty($impactedServer['port'])) {
                                        $impactedEndpoint .= ':'.(int)$impactedServer['port'];
                                    }
                                    ?>
                                    <a class="cve-server" href="<?= LINK ?>MysqlServer/cve/<?= $impactedServerId ?>/pmacontrol">
                                        <span class="cve-server-name"><?= cve_h($impactedServerName) ?></span>
                                        <span class="cve-server-version"><?= cve_h(trim($impactedProduct.' '.($impactedVersion !== '' ? $impactedVersion : 'n/a'))) ?></span>
                                        <?php if ($impactedEndpoint !== ''): ?>
                                            <span class="cve-server-endpoint"><?= cve_h($impactedEndpoint) ?></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                                <?php if ($remainingServers > 0): ?>
                                    <div class="cve-more">+ <?= $remainingServers ?> <?= __('more impacted servers') ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="cve-sev <?= $severity ?>"><?= cve_h($cve['severity']) ?></span>
                        <div class="cve-score">CVSS <?= cve_h(cve_score($cve)) ?></div>
                        <?php if (!empty($cve['known_exploited'])): ?>
                            <span class="cve-kev">CISA KEV</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="cve-affected">
                            <?php foreach ($displayed as $impact): ?>
                                <div class="cve-aff-row">
                                    <a class="cve-product-tag"
                                       style="background:<?= cve_h($impact['color']) ?>"
                                       href="<?= LINK ?>cve/index/<?= cve_h($impact['product_code']) ?>">
                                        <i class="<?= cve_h($impact['icon_class']) ?>" aria-hidden="true"></i>
                                        <?= cve_h($impact['product_name']) ?>
                                    </a>
                                    <div class="cve-version">
                                        <?= cve_h($impact['version_text'] ?: __('Affected version range unavailable')) ?>
                                    </div>
                                    <?php if (!empty($impact['fixed_version'])): ?>
                                        <div class="cve-fixed"><?= __('Fixed') ?>: <?= cve_h($impact['fixed_version']) ?></div>
                                    <?php endif; ?>
                                    <div class="cve-source">
                                        <?= cve_h($impact['source_code']) ?>
                                        &middot; <?= cve_h($impact['match_method']) ?>
                                        &middot; <?= cve_h($impact['match_confidence']) ?>
                                        <?php if (!empty($impact['source_url'])): ?>
                                            &middot; <a href="<?= cve_h($impact['source_url']) ?>" target="_blank" rel="noopener noreferrer"><?= __('source') ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($remaining > 0): ?>
                                <div class="cve-more">+ <?= $remaining ?> <?= __('more affected ranges') ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php endif; ?>
</div>
