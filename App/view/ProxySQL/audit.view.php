<?php

\Glial\Synapse\FactoryController::addNode("ProxySQL", "menu", $data['param']);

$findings      = is_array($data['findings'] ?? null)        ? $data['findings'] : [];
$byCategory    = is_array($data['by_category'] ?? null)     ? $data['by_category'] : [];
$counts        = is_array($data['severity_counts'] ?? null) ? $data['severity_counts'] : [];
$auditError    = (string) ($data['audit_error'] ?? '');
$proxysqlId    = (string) ($data['id_proxysql_server'] ?? '');

$severityBadge = static function (string $sev): string {
    $cls = 'label label-default';
    if ($sev === 'critical') $cls = 'label label-danger';
    elseif ($sev === 'warning') $cls = 'label label-warning';
    elseif ($sev === 'info')    $cls = 'label label-default';
    return '<span class="' . $cls . '">' . htmlspecialchars($sev, ENT_QUOTES, 'UTF-8') . '</span>';
};

$categoryLabel = static function (string $cat): string {
    $labels = [
        'topology'    => 'Topology',
        'hostgroup'   => 'Hostgroup integrity',
        'user'        => 'User configuration',
        'monitor'     => 'Monitor settings',
        'query_rules' => 'Query rules',
        'cluster'     => 'ProxySQL cluster',
        'runtime'     => 'Runtime / drift',
        'meta'        => 'Audit framework',
    ];
    return $labels[$cat] ?? ucfirst($cat);
};

?>
<style>
.sv-audit-toolbar { display:flex; align-items:center; gap:12px; padding:12px 0; flex-wrap:wrap; }
.sv-audit-counter { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#475569; }
.sv-audit-counter b { font-size:14px; }
.sv-audit-search { font-size:13px; padding:4px 8px; width:240px; }
.sv-audit-card  { background:#fff; border:1px solid #e2e8f0; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.sv-audit-head  { background:#0f172a; color:#fff; padding:8px 12px; font-size:11px; text-transform:uppercase; letter-spacing:.5px; }
.sv-audit-row   { padding:10px 12px; border-bottom:1px solid #e2e8f0; }
.sv-audit-row:last-child { border-bottom:none; }
.sv-audit-row.severity-critical { background:rgba(239,68,68,0.05); }
.sv-audit-row.severity-warning  { background:rgba(245,158,11,0.05); }
.sv-audit-row.severity-info     { background:transparent; }
.sv-audit-title { font-weight:600; color:#1e293b; }
.sv-audit-evidence { font-family:monospace; font-size:11px; color:#475569; background:#f8fafc; padding:6px 8px; border-radius:4px; margin:4px 0; white-space:pre-wrap; }
.sv-audit-fix      { font-size:12px; color:#0f766e; margin-top:4px; }
.sv-audit-empty    { padding:24px; text-align:center; color:#94a3b8; }
.sv-audit-error    { padding:12px; background:rgba(239,68,68,0.1); color:#991b1b; border-radius:4px; margin:12px 0; }
</style>

<?php if ($auditError !== ''): ?>
    <div class="sv-audit-error">
        <i class="fa fa-exclamation-triangle"></i>
        <?= __('Audit could not run') ?>: <code><?= htmlspecialchars($auditError, ENT_QUOTES, 'UTF-8') ?></code>
    </div>
<?php endif; ?>

<div class="sv-audit-toolbar">
    <div class="sv-audit-counter">
        <span class="label label-danger"><i class="fa fa-exclamation-circle"></i></span>
        <b><?= (int) ($counts['critical'] ?? 0) ?></b> <?= __('critical') ?>
    </div>
    <div class="sv-audit-counter">
        <span class="label label-warning"><i class="fa fa-exclamation"></i></span>
        <b><?= (int) ($counts['warning'] ?? 0) ?></b> <?= __('warning') ?>
    </div>
    <div class="sv-audit-counter">
        <span class="label label-default"><i class="fa fa-info-circle"></i></span>
        <b><?= (int) ($counts['info'] ?? 0) ?></b> <?= __('info') ?>
    </div>

    <input type="text" class="form-control sv-audit-search" id="sv-audit-search"
        placeholder="<?= __('Filter…') ?>"
        title="<?= __('Filters by category, title, evidence or fix.') ?>">

    <button type="button" class="btn btn-default btn-sm" id="sv-audit-export">
        <i class="fa fa-clipboard"></i> <?= __('Copy as JSON') ?>
    </button>

    <div style="margin-left:auto; font-size:11px; color:#94a3b8">
        <?= count($findings) ?> <?= __('finding(s)') ?> · ProxySQL #<?= htmlspecialchars($proxysqlId, ENT_QUOTES, 'UTF-8') ?>
    </div>
</div>

<?php if (empty($findings)): ?>
    <div class="sv-audit-empty">
        <i class="fa fa-check-circle" style="font-size:24px; color:#10b981"></i>
        <p style="margin-top:8px"><?= __('No audit findings.') ?></p>
        <p style="font-size:12px"><?= __('All registered checks ran without surfacing a misconfiguration.') ?></p>
    </div>
<?php else: ?>
    <div id="sv-audit-results">
    <?php foreach ($byCategory as $cat => $rows): ?>
        <div class="sv-audit-card" data-category="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>">
            <div class="sv-audit-head">
                <i class="fa fa-folder-open"></i> <?= htmlspecialchars($categoryLabel($cat), ENT_QUOTES, 'UTF-8') ?>
                <span style="float:right; opacity:.7"><?= count($rows) ?></span>
            </div>
            <?php foreach ($rows as $f): ?>
                <?php $sev = (string) ($f['severity'] ?? 'info'); ?>
                <div class="sv-audit-row severity-<?= htmlspecialchars($sev, ENT_QUOTES, 'UTF-8') ?>"
                     data-severity="<?= htmlspecialchars($sev, ENT_QUOTES, 'UTF-8') ?>"
                     data-search="<?= htmlspecialchars(strtolower(($f['title'] ?? '').' '.($f['evidence'] ?? '').' '.($f['fix'] ?? '').' '.$cat), ENT_QUOTES, 'UTF-8') ?>">
                    <div>
                        <?= $severityBadge($sev) ?>
                        <span class="sv-audit-title"><?= htmlspecialchars((string) ($f['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if (!empty($f['ticket'])): ?>
                            <small style="color:#64748b">— <?= htmlspecialchars((string) $f['ticket'], ENT_QUOTES, 'UTF-8') ?></small>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($f['evidence'])): ?>
                        <div class="sv-audit-evidence"><?= htmlspecialchars((string) $f['evidence'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($f['fix'])): ?>
                        <div class="sv-audit-fix">
                            <i class="fa fa-wrench"></i>
                            <?= htmlspecialchars((string) $f['fix'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
(function() {
    var input = document.getElementById('sv-audit-search');
    if (input) {
        input.addEventListener('input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('.sv-audit-row').forEach(function(row) {
                var hay = row.getAttribute('data-search') || '';
                row.style.display = (q === '' || hay.indexOf(q) !== -1) ? '' : 'none';
            });
            // Hide cards where every row was filtered out.
            document.querySelectorAll('.sv-audit-card').forEach(function(card) {
                var any = card.querySelector('.sv-audit-row[style=""], .sv-audit-row:not([style])');
                card.style.display = any ? '' : 'none';
            });
        });
    }

    var exportBtn = document.getElementById('sv-audit-export');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            var payload = <?= json_encode([
                'id_proxysql_server' => (int) $proxysqlId,
                'severity_counts'    => $counts,
                'findings'           => $findings,
            ], JSON_UNESCAPED_SLASHES) ?>;
            var json = JSON.stringify(payload, null, 2);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(json).then(function() {
                    exportBtn.innerHTML = '<i class="fa fa-check"></i> <?= __('Copied') ?>';
                    setTimeout(function() {
                        exportBtn.innerHTML = '<i class="fa fa-clipboard"></i> <?= __('Copy as JSON') ?>';
                    }, 1500);
                });
            } else {
                // Fallback: open a textarea for manual copy
                var ta = document.createElement('textarea');
                ta.value = json;
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); } catch (e) {}
                ta.remove();
            }
        });
    }
})();
</script>
