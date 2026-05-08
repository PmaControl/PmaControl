<?php

if (!function_exists('cve_detail_h')) {
    function cve_detail_h($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('cve_detail_severity_class')) {
    function cve_detail_severity_class($severity): string
    {
        $severity = strtolower((string)$severity);

        return in_array($severity, ['critical', 'high', 'medium', 'low', 'none'], true) ? $severity : 'unknown';
    }
}

if (!function_exists('cve_detail_score')) {
    function cve_detail_score(array $cve): string
    {
        foreach (['cvss_v4_score', 'cvss_v3_score', 'cvss_v2_score'] as $field) {
            if (isset($cve[$field]) && $cve[$field] !== null && $cve[$field] !== '') {
                return number_format((float)$cve[$field], 1);
            }
        }

        return 'n/a';
    }
}

if (!function_exists('cve_detail_pretty_json')) {
    function cve_detail_pretty_json($json): string
    {
        $json = trim((string)$json);
        if ($json === '') {
            return '';
        }

        $decoded = json_decode($json, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return $json;
        }

        return (string)json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('cve_detail_version_range')) {
    function cve_detail_version_range(array $row): string
    {
        $parts = [];
        if (!empty($row['version_start_including'])) {
            $parts[] = '>= '.$row['version_start_including'];
        }
        if (!empty($row['version_start_excluding'])) {
            $parts[] = '> '.$row['version_start_excluding'];
        }
        if (!empty($row['version_end_including'])) {
            $parts[] = '<= '.$row['version_end_including'];
        }
        if (!empty($row['version_end_excluding'])) {
            $parts[] = '< '.$row['version_end_excluding'];
        }
        if ($parts !== []) {
            return implode(' and ', array_map('strval', $parts));
        }

        $versionText = trim((string)($row['version_text'] ?? ''));

        return $versionText !== '' ? $versionText : 'Affected version range unavailable';
    }
}

if (!function_exists('cve_detail_source_link')) {
    function cve_detail_source_link(array $row): string
    {
        foreach (['source_url', 'advisory_url'] as $field) {
            if (!empty($row[$field]) && is_scalar($row[$field])) {
                return (string)$row[$field];
            }
        }

        return '';
    }
}

$cve = is_array($data['cve'] ?? null) ? $data['cve'] : [];
$affectedRows = is_array($data['affected'] ?? null) ? $data['affected'] : [];
$impactedServers = is_array($data['impacted_servers'] ?? null) ? $data['impacted_servers'] : [];
$sourceRows = is_array($data['source_rows'] ?? null) ? $data['source_rows'] : [];
$references = is_array($cve['references'] ?? null) ? $cve['references'] : [];
$sourceCodes = is_array($cve['source_codes'] ?? null) ? $cve['source_codes'] : [];
$cwes = is_array($cve['cwes'] ?? null) ? $cve['cwes'] : [];
$severityClass = cve_detail_severity_class($cve['severity'] ?? 'unknown');
$exploitReferences = [];

foreach ($references as $reference) {
    $needle = strtolower((string)($reference['url'] ?? '').' '.(string)($reference['label'] ?? ''));
    foreach (['exploit', 'metasploit', 'packetstorm', '0day', 'poc', 'github.com'] as $marker) {
        if (str_contains($needle, $marker)) {
            $exploitReferences[] = $reference;
            break;
        }
    }
}
?>

<style>
.cved { --ink:#111827; --muted:#64748b; --line:#d7dee8; --soft:#f8fafc; --panel:#fff; --critical:#9f1239; --high:#dc2626; --medium:#d97706; --low:#2563eb; --unknown:#64748b; }
.cved-head { background:linear-gradient(135deg,#0b1220,#12334d); color:#fff; border-radius:10px; padding:22px 24px; margin:0 0 16px; box-shadow:0 10px 26px rgba(15,23,42,.18); }
.cved-head-top { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; }
.cved-title { margin:0; font-size:30px; font-weight:900; letter-spacing:.2px; }
.cved-sub { margin-top:8px; color:#dbeafe; line-height:1.45; white-space:pre-wrap; }
.cved-actions { display:flex; flex-wrap:wrap; gap:8px; justify-content:flex-end; }
.cved-pill { display:inline-flex; align-items:center; gap:5px; border-radius:999px; padding:4px 9px; color:#fff; font-size:11px; font-weight:900; text-transform:uppercase; }
.cved-pill.critical { background:var(--critical); }
.cved-pill.high { background:var(--high); }
.cved-pill.medium { background:var(--medium); }
.cved-pill.low { background:var(--low); }
.cved-pill.none, .cved-pill.unknown { background:var(--unknown); }
.cved-pill.kev { background:#111827; border:1px solid rgba(255,255,255,.22); }
.cved-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
.cved-kpi { background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:14px; }
.cved-kpi-value { font-size:24px; font-weight:900; color:var(--ink); line-height:1; }
.cved-kpi-label { margin-top:6px; color:var(--muted); font-size:10px; text-transform:uppercase; letter-spacing:.07em; font-weight:800; }
.cved-card { background:var(--panel); border:1px solid var(--line); border-radius:9px; margin-bottom:14px; overflow:hidden; box-shadow:0 1px 3px rgba(15,23,42,.05); }
.cved-card-head { padding:12px 14px; border-bottom:1px solid var(--line); background:#f8fafc; display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
.cved-card-title { margin:0; font-size:16px; font-weight:900; color:var(--ink); }
.cved-card-sub { margin-top:3px; color:var(--muted); font-size:12px; }
.cved-card-body { padding:14px; }
.cved-text { color:#1f2937; line-height:1.55; white-space:pre-wrap; }
.cved-table { width:100%; margin:0; font-size:12px; }
.cved-table th { background:#f8fafc; color:#475569; border-bottom:1px solid var(--line); font-size:10px; text-transform:uppercase; letter-spacing:.05em; padding:7px; vertical-align:top; }
.cved-table td { border-bottom:1px solid #edf2f7; padding:7px; vertical-align:top; }
.cved-tag { display:inline-flex; align-items:center; gap:5px; color:#fff; border-radius:999px; padding:3px 8px; font-size:11px; font-weight:900; text-decoration:none; }
.cved-tag:hover { color:#fff; text-decoration:none; filter:brightness(.93); }
.cved-ref-list { display:flex; flex-wrap:wrap; gap:7px; }
.cved-ref { background:#eef2ff; color:#3730a3; border-radius:6px; padding:5px 8px; font-size:12px; font-weight:800; text-decoration:none; max-width:100%; overflow-wrap:anywhere; }
.cved-ref:hover { background:#dbeafe; color:#111827; text-decoration:none; }
.cved-server { display:block; color:#0f172a; text-decoration:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:6px 8px; }
.cved-server:hover { background:#eef2ff; color:#111827; text-decoration:none; }
.cved-server-name { display:block; font-weight:900; }
.cved-server-meta { display:block; color:#475569; margin-top:2px; font-size:11px; }
.cved-source-title { display:flex; justify-content:space-between; gap:10px; align-items:center; margin-bottom:8px; color:#111827; font-weight:900; }
.cved-raw { background:#07111f; color:#d8f3ff; border-radius:7px; padding:10px; overflow:auto; max-height:620px; font-size:11px; line-height:1.4; white-space:pre; }
.cved-details { margin-top:9px; }
.cved-details summary { cursor:pointer; color:#0f172a; font-weight:900; }
.cved-empty { color:var(--muted); background:#f8fafc; border:1px dashed var(--line); border-radius:8px; padding:15px; }
.cved-warning { border:1px solid #fed7aa; background:#fff7ed; color:#9a3412; border-radius:8px; padding:12px; margin-bottom:14px; }
@media (max-width: 960px) {
    .cved-head-top { display:block; }
    .cved-actions { justify-content:flex-start; margin-top:12px; }
    .cved-grid { grid-template-columns:repeat(2,1fr); }
    .cved-table, .cved-table thead, .cved-table tbody, .cved-table tr, .cved-table th, .cved-table td { display:block; width:100%; }
    .cved-table thead { display:none; }
    .cved-table tr { border-bottom:1px solid var(--line); }
}
@media (max-width: 520px) { .cved-grid { grid-template-columns:1fr; } }
</style>

<div class="cved">
    <?php if (empty($data['is_ready'])): ?>
        <div class="cved-empty">
            <strong><?= __('CVE catalog is not initialized.') ?></strong>
            <?= __('Run the CVE migrations and rebuild the catalog before opening a detailed CVE page.') ?>
        </div>
    <?php elseif (!empty($data['not_found'])): ?>
        <div class="cved-empty">
            <strong><?= __('CVE not found.') ?></strong>
            <?= __('The requested CVE does not exist in the local catalog.') ?>
        </div>
    <?php else: ?>
        <div class="cved-head">
            <div class="cved-head-top">
                <div>
                    <h2 class="cved-title"><?= cve_detail_h($cve['cve_id'] ?? '') ?></h2>
                    <div style="margin-top:8px">
                        <span class="cved-pill <?= $severityClass ?>"><?= cve_detail_h($cve['severity'] ?? 'unknown') ?></span>
                        <span class="cved-pill <?= $severityClass ?>">CVSS <?= cve_detail_h(cve_detail_score($cve)) ?></span>
                        <?php if (!empty($cve['known_exploited'])): ?><span class="cved-pill kev">CISA KEV</span><?php endif; ?>
                        <?php if (!empty($cve['known_ransomware_campaign_use'])): ?><span class="cved-pill kev"><?= cve_detail_h($cve['known_ransomware_campaign_use']) ?></span><?php endif; ?>
                    </div>
                    <?php if (!empty($cve['title'])): ?>
                        <div class="cved-sub"><?= cve_detail_h($cve['title']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="cved-actions">
                    <a class="btn btn-default btn-sm" href="<?= LINK ?>cve/index">
                        <i class="fa fa-database"></i> <?= __('CVE inventory') ?>
                    </a>
                    <a class="btn btn-default btn-sm" href="https://nvd.nist.gov/vuln/detail/<?= cve_detail_h($cve['cve_id']) ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fa fa-external-link"></i> NVD
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($cve['is_disabled'])): ?>
            <div class="cved-warning">
                <strong><?= __('CVE disabled in PmaControl UI.') ?></strong>
                <?= __('This CVE is excluded from normal inventory results but the full record remains available here.') ?>
                <?php if (!empty($cve['reason'])): ?> <?= cve_detail_h($cve['reason']) ?><?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="cved-grid">
            <div class="cved-kpi">
                <div class="cved-kpi-value"><?= count($affectedRows) ?></div>
                <div class="cved-kpi-label"><?= __('Affected ranges') ?></div>
            </div>
            <div class="cved-kpi">
                <div class="cved-kpi-value"><?= count($impactedServers) ?></div>
                <div class="cved-kpi-label"><?= __('Impacted servers') ?></div>
            </div>
            <div class="cved-kpi">
                <div class="cved-kpi-value"><?= count($references) ?></div>
                <div class="cved-kpi-label"><?= __('References') ?></div>
            </div>
            <div class="cved-kpi">
                <div class="cved-kpi-value"><?= count($sourceRows) ?></div>
                <div class="cved-kpi-label"><?= __('Source feeds') ?></div>
            </div>
        </div>

        <div class="cved-card">
            <div class="cved-card-head">
                <div>
                    <h3 class="cved-card-title"><?= __('Complete description') ?></h3>
                    <div class="cved-card-sub"><?= __('Full catalog text, not truncated.') ?></div>
                </div>
            </div>
            <div class="cved-card-body">
                <div class="cved-text"><?= cve_detail_h($cve['summary'] ?: ($cve['title'] ?? '')) ?></div>
            </div>
        </div>

        <div class="cved-card">
            <div class="cved-card-head">
                <div>
                    <h3 class="cved-card-title"><?= __('Exploitability and reproduction evidence') ?></h3>
                    <div class="cved-card-sub"><?= __('Known exploitation, exploitation references and concrete affected prerequisites.') ?></div>
                </div>
            </div>
            <div class="cved-card-body">
                <table class="cved-table">
                    <tbody>
                        <tr>
                            <th><?= __('Known exploited') ?></th>
                            <td><?= !empty($cve['known_exploited']) ? 'yes' : 'no' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Known ransomware campaign use') ?></th>
                            <td><?= cve_detail_h($cve['known_ransomware_campaign_use'] ?: 'unknown') ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Reproduction prerequisites') ?></th>
                            <td>
                                <?= __('The concrete reproduction precondition stored by PmaControl is an affected product/version range plus a matching impacted server version. Full ranges and impacted servers are listed below.') ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Step-by-step PoC') ?></th>
                            <td>
                                <?= __('No step-by-step exploit procedure is stored in the normalized catalog. When upstream feeds include exploit or PoC data, it is shown in the source payloads and references below without truncation.') ?>
                            </td>
                        </tr>
                        <?php if ($exploitReferences !== []): ?>
                            <tr>
                                <th><?= __('Potential exploit or PoC references') ?></th>
                                <td>
                                    <div class="cved-ref-list">
                                        <?php foreach ($exploitReferences as $reference): ?>
                                            <a class="cved-ref" href="<?= cve_detail_h($reference['url'] ?? '') ?>" target="_blank" rel="noopener noreferrer">
                                                <?= cve_detail_h($reference['label'] ?? $reference['url'] ?? '') ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="cved-card">
            <div class="cved-card-head">
                <div>
                    <h3 class="cved-card-title"><?= __('Impacted servers') ?></h3>
                    <div class="cved-card-sub"><?= __('Every active cached server/version match for this CVE.') ?></div>
                </div>
            </div>
            <div class="cved-card-body">
                <?php if ($impactedServers === []): ?>
                    <div class="cved-empty"><?= __('No active impacted server is currently cached for this CVE.') ?></div>
                <?php else: ?>
                    <table class="cved-table">
                        <thead>
                            <tr>
                                <th><?= __('Server') ?></th>
                                <th><?= __('Endpoint') ?></th>
                                <th><?= __('Product/version') ?></th>
                                <th><?= __('Match') ?></th>
                                <th><?= __('Calculated') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($impactedServers as $server): ?>
                                <?php
                                $serverId = (int)($server['id_mysql_server'] ?? 0);
                                $endpoint = trim((string)($server['ip'] ?? ''));
                                if (!empty($server['port'])) {
                                    $endpoint .= ':'.(int)$server['port'];
                                }
                                ?>
                                <tr>
                                    <td>
                                        <a class="cved-server" href="<?= LINK ?>MysqlServer/cve/<?= $serverId ?>/pmacontrol">
                                            <span class="cved-server-name"><?= cve_detail_h($server['display_name'] ?? 'server #'.$serverId) ?></span>
                                            <span class="cved-server-meta">#<?= $serverId ?></span>
                                        </a>
                                    </td>
                                    <td><?= cve_detail_h($endpoint ?: 'n/a') ?></td>
                                    <td><?= cve_detail_h(trim((string)($server['product_code'] ?? '').' '.(string)($server['server_version'] ?? ''))) ?></td>
                                    <td><?= cve_detail_h((string)($server['match_method'] ?? 'n/a').'/'.(string)($server['match_confidence'] ?? 'n/a')) ?></td>
                                    <td><?= cve_detail_h($server['date_calculated'] ?? 'n/a') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="cved-card">
            <div class="cved-card-head">
                <div>
                    <h3 class="cved-card-title"><?= __('Affected products and versions') ?></h3>
                    <div class="cved-card-sub"><?= __('All current normalized ranges, fixed versions and raw match evidence.') ?></div>
                </div>
            </div>
            <div class="cved-card-body">
                <?php if ($affectedRows === []): ?>
                    <div class="cved-empty"><?= __('No affected version range is currently attached to this CVE.') ?></div>
                <?php else: ?>
                    <table class="cved-table">
                        <thead>
                            <tr>
                                <th><?= __('Product') ?></th>
                                <th><?= __('Affected range') ?></th>
                                <th><?= __('Fixed version') ?></th>
                                <th><?= __('Source') ?></th>
                                <th><?= __('Match') ?></th>
                                <th><?= __('Raw evidence') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($affectedRows as $row): ?>
                                <?php $sourceLink = cve_detail_source_link($row); ?>
                                <tr>
                                    <td>
                                        <a class="cved-tag" style="background:<?= cve_detail_h($row['color'] ?? '#334155') ?>" href="<?= LINK ?>cve/index/<?= cve_detail_h($row['product_code'] ?? '') ?>">
                                            <i class="<?= cve_detail_h($row['icon_class'] ?? 'fa fa-shield') ?>" aria-hidden="true"></i>
                                            <?= cve_detail_h($row['product_name'] ?? $row['product_code'] ?? '') ?>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?= cve_detail_h(cve_detail_version_range($row)) ?></strong>
                                        <?php if (!empty($row['version_text'])): ?>
                                            <div class="text-muted"><?= cve_detail_h($row['version_text']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= cve_detail_h($row['fixed_version'] ?: 'n/a') ?></td>
                                    <td>
                                        <?php if ($sourceLink !== ''): ?>
                                            <a href="<?= cve_detail_h($sourceLink) ?>" target="_blank" rel="noopener noreferrer"><?= cve_detail_h($row['source_code'] ?? 'source') ?></a>
                                        <?php else: ?>
                                            <?= cve_detail_h($row['source_code'] ?? 'n/a') ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= cve_detail_h((string)($row['match_method'] ?? 'n/a').'/'.(string)($row['match_confidence'] ?? 'n/a')) ?></td>
                                    <td>
                                        <?php if (!empty($row['raw_match_json'])): ?>
                                            <details class="cved-details" open>
                                                <summary><?= __('raw_match_json') ?></summary>
                                                <pre class="cved-raw"><?= cve_detail_h(cve_detail_pretty_json($row['raw_match_json'])) ?></pre>
                                            </details>
                                        <?php else: ?>
                                            <span class="text-muted"><?= __('n/a') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="cved-card">
            <div class="cved-card-head">
                <div>
                    <h3 class="cved-card-title"><?= __('References') ?></h3>
                    <div class="cved-card-sub"><?= __('All reference URLs imported in the catalog, without the server-detail limit.') ?></div>
                </div>
            </div>
            <div class="cved-card-body">
                <?php if ($references === []): ?>
                    <div class="cved-empty"><?= __('No reference URL is available in the local catalog.') ?></div>
                <?php else: ?>
                    <div class="cved-ref-list">
                        <?php foreach ($references as $reference): ?>
                            <a class="cved-ref" href="<?= cve_detail_h($reference['url'] ?? '') ?>" target="_blank" rel="noopener noreferrer">
                                <?= cve_detail_h($reference['label'] ?? $reference['url'] ?? '') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="cved-card">
            <div class="cved-card-head">
                <div>
                    <h3 class="cved-card-title"><?= __('Catalog metadata') ?></h3>
                    <div class="cved-card-sub"><?= __('CWE, source list, dates, CVSS scores and normalized catalog JSON.') ?></div>
                </div>
            </div>
            <div class="cved-card-body">
                <table class="cved-table">
                    <tbody>
                        <tr><th><?= __('Published') ?></th><td><?= cve_detail_h($cve['published_at'] ?: 'n/a') ?></td></tr>
                        <tr><th><?= __('Last modified') ?></th><td><?= cve_detail_h($cve['last_modified_at'] ?: 'n/a') ?></td></tr>
                        <tr><th><?= __('CVSS v2') ?></th><td><?= cve_detail_h($cve['cvss_v2_score'] ?: 'n/a') ?></td></tr>
                        <tr><th><?= __('CVSS v3') ?></th><td><?= cve_detail_h($cve['cvss_v3_score'] ?: 'n/a') ?></td></tr>
                        <tr><th><?= __('CVSS v4') ?></th><td><?= cve_detail_h($cve['cvss_v4_score'] ?: 'n/a') ?></td></tr>
                        <tr><th><?= __('CWE') ?></th><td><?= cve_detail_h($cwes !== [] ? implode(', ', $cwes) : 'n/a') ?></td></tr>
                        <tr><th><?= __('Sources') ?></th><td><?= cve_detail_h($sourceCodes !== [] ? implode(', ', $sourceCodes) : 'n/a') ?></td></tr>
                    </tbody>
                </table>
                <?php foreach (['cwe_json', 'references_json', 'source_codes_json'] as $field): ?>
                    <?php if (!empty($cve[$field])): ?>
                        <details class="cved-details" open>
                            <summary><?= cve_detail_h($field) ?></summary>
                            <pre class="cved-raw"><?= cve_detail_h(cve_detail_pretty_json($cve[$field])) ?></pre>
                        </details>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="cved-card">
            <div class="cved-card-head">
                <div>
                    <h3 class="cved-card-title"><?= __('Raw source payloads') ?></h3>
                    <div class="cved-card-sub"><?= __('Complete current rows from each imported source feed that matches this CVE.') ?></div>
                </div>
            </div>
            <div class="cved-card-body">
                <?php if ($sourceRows === []): ?>
                    <div class="cved-empty"><?= __('No raw source-feed row is available for this CVE in the local database.') ?></div>
                <?php else: ?>
                    <?php foreach ($sourceRows as $source): ?>
                        <div class="cved-source-title">
                            <span><?= cve_detail_h($source['source'] ?? '') ?></span>
                            <code><?= cve_detail_h($source['table'] ?? '') ?></code>
                        </div>
                        <?php foreach ($source['rows'] as $row): ?>
                            <table class="cved-table" style="margin-bottom:8px">
                                <tbody>
                                    <?php foreach ($row as $field => $value): ?>
                                        <?php if ($field === 'raw_json'): ?>
                                            <?php continue; ?>
                                        <?php endif; ?>
                                        <tr>
                                            <th><?= cve_detail_h($field) ?></th>
                                            <td><?= cve_detail_h($value === null || $value === '' ? 'n/a' : $value) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if (!empty($row['raw_json'])): ?>
                                <details class="cved-details" open>
                                    <summary><?= __('raw_json') ?></summary>
                                    <pre class="cved-raw"><?= cve_detail_h(cve_detail_pretty_json($row['raw_json'])) ?></pre>
                                </details>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
