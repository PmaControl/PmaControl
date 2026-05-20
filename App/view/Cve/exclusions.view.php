<?php

use App\Library\Security\CsrfRender;

function cve_exclusions_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function cve_exclusions_severity_class($severity): string
{
    $severity = strtolower((string)$severity);
    return in_array($severity, ['critical', 'high', 'medium', 'low', 'none'], true) ? $severity : 'unknown';
}

function cve_exclusions_score(array $cve): string
{
    foreach (['cvss_v4_score', 'cvss_v3_score', 'cvss_v2_score'] as $field) {
        if ($cve[$field] !== null && $cve[$field] !== '') {
            return number_format((float)$cve[$field], 1);
        }
    }

    return 'n/a';
}

$stats = $data['stats'];
$query = (string)$data['query'];
$action = LINK.'cve/exclusions'.($query !== '' ? '?q='.urlencode($query) : '');
?>

<style>
.cve-exclusions { --ink:#172033; --muted:#667085; --line:#d8dee9; --panel:#fff; --soft:#f6f8fb; --critical:#9f1239; --high:#dc2626; --medium:#d97706; --low:#2563eb; --unknown:#64748b; --disabled:#111827; }
.cve-exclusions-hero { background:linear-gradient(135deg,#111827,#365314); color:#fff; border-radius:10px; padding:22px 24px; margin-bottom:16px; box-shadow:0 10px 24px rgba(15,23,42,.18); }
.cve-exclusions-hero h2 { margin:0 0 6px; font-weight:800; letter-spacing:.2px; }
.cve-exclusions-hero p { margin:0; color:#d9f99d; }
.cve-exclusions-kpis { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
.cve-exclusions-kpi { background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:14px; }
.cve-exclusions-kpi-value { font-size:26px; line-height:1; font-weight:800; color:var(--ink); }
.cve-exclusions-kpi-label { margin-top:6px; color:var(--muted); text-transform:uppercase; font-size:10px; letter-spacing:.08em; font-weight:700; }
.cve-exclusions-toolbar { background:var(--panel); border:1px solid var(--line); border-radius:8px; padding:12px; margin-bottom:16px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.cve-exclusions-toolbar .form-control { min-width:320px; max-width:520px; }
.cve-exclusions-table-wrap { background:#fff; border:1px solid var(--line); border-radius:8px; overflow:hidden; }
.cve-exclusions-table { margin-bottom:0; }
.cve-exclusions-table > thead > tr > th { background:#f8fafc; color:#334155; border-bottom:1px solid var(--line); font-size:12px; text-transform:uppercase; letter-spacing:.06em; }
.cve-exclusions-id { font-weight:800; font-size:15px; color:#0f172a; }
.cve-exclusions-title { margin-top:4px; color:#334155; max-width:620px; }
.cve-exclusions-meta { margin-top:6px; color:var(--muted); font-size:11px; }
.cve-exclusions-sev { display:inline-block; min-width:72px; text-align:center; border-radius:999px; padding:4px 8px; color:#fff; font-size:11px; font-weight:800; text-transform:uppercase; }
.cve-exclusions-sev.critical { background:var(--critical); }
.cve-exclusions-sev.high { background:var(--high); }
.cve-exclusions-sev.medium { background:var(--medium); }
.cve-exclusions-sev.low { background:var(--low); }
.cve-exclusions-sev.none, .cve-exclusions-sev.unknown { background:var(--unknown); }
.cve-exclusions-disabled { display:inline-block; margin-top:6px; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; border-radius:5px; padding:3px 6px; font-size:11px; font-weight:800; }
.cve-exclusions-enabled { display:inline-block; margin-top:6px; background:#ecfdf5; color:#047857; border:1px solid #bbf7d0; border-radius:5px; padding:3px 6px; font-size:11px; font-weight:800; }
.cve-exclusions-product-tag { display:inline-block; background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; border-radius:999px; padding:3px 8px; margin:2px; font-size:11px; font-weight:800; }
.cve-exclusions-products { color:#334155; margin-bottom:6px; }
.cve-exclusions-checkbox { width:20px; height:20px; }
.cve-exclusions-actions { padding:12px; background:#f8fafc; border-top:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
@media (max-width: 900px) {
    .cve-exclusions-kpis { grid-template-columns:1fr; }
    .cve-exclusions-toolbar .form-control { min-width:100%; }
    .cve-exclusions-table > thead { display:none; }
    .cve-exclusions-table, .cve-exclusions-table > tbody, .cve-exclusions-table > tbody > tr, .cve-exclusions-table > tbody > tr > td { display:block; width:100%; }
    .cve-exclusions-table > tbody > tr { border-bottom:1px solid var(--line); }
}
</style>

<div class="cve-exclusions">
    <div class="cve-exclusions-hero">
        <h2><i class="fa fa-ban" aria-hidden="true"></i> <?= __('CVE exclusions') ?></h2>
        <p><?= __('SuperAdmin list for hiding CVEs that are false positives for the MySQL-like backlog.') ?></p>
    </div>

    <?php if (empty($data['is_ready'])): ?>
        <div class="alert alert-warning">
            <strong><?= __('CVE exclusion table is not initialized.') ?></strong>
            <?= __('Run the CVE catalog/exclusion migrations before using this screen.') ?>
        </div>
    <?php else: ?>

    <div class="cve-exclusions-kpis">
        <div class="cve-exclusions-kpi">
            <div class="cve-exclusions-kpi-value"><?= (int)$stats['total_cves'] ?></div>
            <div class="cve-exclusions-kpi-label"><?= __('Catalog CVEs') ?></div>
        </div>
        <div class="cve-exclusions-kpi">
            <div class="cve-exclusions-kpi-value"><?= (int)$stats['disabled_cves'] ?></div>
            <div class="cve-exclusions-kpi-label"><?= __('Disabled') ?></div>
        </div>
        <div class="cve-exclusions-kpi">
            <div class="cve-exclusions-kpi-value"><?= (int)$stats['shown_cves'] ?></div>
            <div class="cve-exclusions-kpi-label"><?= __('Shown') ?></div>
        </div>
    </div>

    <form class="cve-exclusions-toolbar" method="get" action="<?= LINK ?>cve/exclusions">
        <input class="form-control" type="search" name="q" value="<?= cve_exclusions_h($query) ?>" placeholder="<?= __('Search CVE, product, title or source') ?>">
        <button class="btn btn-primary" type="submit">
            <i class="fa fa-search" aria-hidden="true"></i> <?= __('Search') ?>
        </button>
        <?php if ($query !== ''): ?>
            <a class="btn btn-default" href="<?= LINK ?>cve/exclusions"><?= __('Reset') ?></a>
        <?php endif; ?>
    </form>

    <form method="post" action="<?= cve_exclusions_h($action) ?>">
        <?= CsrfRender::hiddenInput($data, 'cve_exclusions') ?>
        <div class="cve-exclusions-table-wrap">
            <table class="table table-hover cve-exclusions-table">
                <thead>
                    <tr>
                        <th style="width:7%"><?= __('Disable') ?></th>
                        <th style="width:42%"><?= __('CVE') ?></th>
                        <th style="width:12%"><?= __('Risk') ?></th>
                        <th><?= __('Products and status') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data['rows'])): ?>
                    <tr>
                        <td colspan="4" class="text-muted"><?= __('No CVE found for this search.') ?></td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($data['rows'] as $row): ?>
                    <?php
                    $severity = cve_exclusions_severity_class($row['severity']);
                    $productCodes = array_filter(array_map('trim', explode(',', (string)($row['product_codes'] ?? ''))));
                    $isDisabled = !empty($row['is_disabled']);
                    ?>
                    <tr>
                        <td>
                            <input type="hidden" name="visible_cve_ids[]" value="<?= cve_exclusions_h($row['cve_id']) ?>">
                            <input class="cve-exclusions-checkbox" type="checkbox" name="disabled_cve_ids[]" value="<?= cve_exclusions_h($row['cve_id']) ?>" <?= $isDisabled ? 'checked' : '' ?>>
                        </td>
                        <td>
                            <div class="cve-exclusions-id">
                                <a href="<?= LINK ?>cve/show/<?= cve_exclusions_h(rawurlencode((string)$row['cve_id'])) ?>">
                                    <?= cve_exclusions_h($row['cve_id']) ?>
                                </a>
                            </div>
                            <div class="cve-exclusions-title"><?= cve_exclusions_h($row['title'] ?: $row['summary']) ?></div>
                            <div class="cve-exclusions-meta">
                                <?= __('Published') ?>: <?= cve_exclusions_h($row['published_at'] ?: 'n/a') ?>
                                &middot; <?= __('Updated') ?>: <?= cve_exclusions_h($row['last_modified_at'] ?: 'n/a') ?>
                            </div>
                        </td>
                        <td>
                            <span class="cve-exclusions-sev <?= $severity ?>"><?= cve_exclusions_h($row['severity']) ?></span>
                            <div class="cve-exclusions-meta">CVSS <?= cve_exclusions_h(cve_exclusions_score($row)) ?></div>
                        </td>
                        <td>
                            <div class="cve-exclusions-products">
                                <?= cve_exclusions_h($row['products'] ?: __('No product mapping')) ?>
                            </div>
                            <?php foreach ($productCodes as $productCode): ?>
                                <span class="cve-exclusions-product-tag"><?= cve_exclusions_h($productCode) ?></span>
                            <?php endforeach; ?>
                            <div class="cve-exclusions-meta">
                                <?= (int)$row['affected_count'] ?> <?= __('affected version mapping(s)') ?>
                            </div>
                            <?php if ($isDisabled): ?>
                                <div class="cve-exclusions-disabled">
                                    <?= __('Disabled') ?>
                                    <?php if (!empty($row['reason'])): ?>
                                        &middot; <?= cve_exclusions_h($row['reason']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="cve-exclusions-enabled"><?= __('Visible in CVE screens') ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cve-exclusions-actions">
                <span class="text-muted">
                    <?= __('Checked CVEs are hidden from inventory, server hover, home and server detail CVE views.') ?>
                </span>
                <button class="btn btn-danger" type="submit">
                    <i class="fa fa-save" aria-hidden="true"></i> <?= __('Save exclusions') ?>
                </button>
            </div>
        </div>
    </form>

    <?php endif; ?>
</div>
