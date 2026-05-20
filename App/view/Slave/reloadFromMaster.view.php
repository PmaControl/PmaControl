<?php
/**
 * "Reload from master" preflight page (issue #1285).
 *
 * Renders two cards (physical / logical) side-by-side, each with the
 * preflight checklist (✓/✗/⚠ per condition) and a Start button enabled
 * only when the corresponding eligibility flag is true.
 */

use App\Library\ReloadFromMaster;
use App\Library\Security\CsrfRender;

if (!empty($data['error'])) {
    echo '<div class="alert alert-danger">'.htmlspecialchars($data['error']).'</div>';
    return;
}

$preflight     = $data['preflight'] ?? ['conditions' => [], 'physical_eligible' => false, 'logical_eligible' => false];
$conditions    = $preflight['conditions'] ?? [];
$physicalOk    = !empty($preflight['physical_eligible']);
$logicalOk     = !empty($preflight['logical_eligible']);
$mydumperOk    = !empty($preflight['mydumper_eligible']);
$idServer      = (int) ($data['id_mysql_server'] ?? 0);
$conn          = (string) ($data['connection_name'] ?? '');
$versions      = $data['versions'] ?? [];
$csrfHidden    = CsrfRender::hiddenInput($data, 'reload');

$hasAtRiskWarn = false;
foreach ($conditions as $c) {
    if (($c['key'] ?? '') === 'source_coverage' && ($c['status'] ?? '') === ReloadFromMaster::STATUS_WARN
        && stripos((string) ($c['message'] ?? ''), 'at_risk') !== false) {
        $hasAtRiskWarn = true;
    }
}

function svReloadStatusIcon(string $status): string
{
    switch ($status) {
        case ReloadFromMaster::STATUS_OK:
            return '<i class="fa fa-check" style="color:#16a34a"></i>';
        case ReloadFromMaster::STATUS_WARN:
            return '<i class="fa fa-exclamation-triangle" style="color:#d97706"></i>';
        case ReloadFromMaster::STATUS_FAIL:
        default:
            return '<i class="fa fa-times" style="color:#dc2626"></i>';
    }
}
?>
<style>
.sv-reload-cards { display:flex; gap:24px; flex-wrap:wrap; margin-top:16px; }
.sv-reload-card  { flex:1 1 460px; border:1px solid #cbd5e1; border-radius:6px; background:#fff; padding:18px; }
.sv-reload-card h3 { margin:0 0 4px; font-size:1.1rem; color:#1e293b; }
.sv-reload-card .sv-reload-subtitle { color:#64748b; font-size:0.875rem; margin-bottom:12px; }
.sv-reload-checklist { list-style:none; padding:0; margin:0 0 16px; }
.sv-reload-checklist li { padding:6px 0; border-bottom:1px dotted #e5e7eb; font-size:0.92rem; }
.sv-reload-checklist li:last-child { border-bottom:0; }
.sv-reload-msg { color:#475569; margin-left:6px; }
.sv-reload-btn-disabled { opacity:0.55; cursor:not-allowed; }
.sv-reload-ack { display:block; margin:8px 0 12px; color:#7c2d12; font-size:0.88rem; }
</style>

<h2><i class="fa fa-refresh"></i> <?= __('Reload from master') ?>
    <small style="color:#64748b">— <?= htmlspecialchars($data['slave']['display_name'] ?? $data['slave']['name'] ?? ('#'.$idServer)) ?>
        <?php if ($conn !== ''): ?>· <?= htmlspecialchars($conn) ?><?php endif; ?>
    </small>
</h2>

<p style="color:#475569;max-width:900px">
    <?= __('Two restoration procedures are offered. The physical path is fast but requires a major-version match between master and slave; the logical path is slower but always compatible. Both procedures wipe the slave datadir, so the slave MUST be flagged out-of-service first.') ?>
</p>

<p style="font-size:0.9rem;color:#64748b">
    <?= __('Detected versions') ?>:
    <b><?= htmlspecialchars((string) ($versions['master_type'] ?? '?')) ?>
      <?= htmlspecialchars((string) ($versions['master'] ?? '?')) ?></b>
    (master)
    →
    <b><?= htmlspecialchars((string) ($versions['slave_type'] ?? '?')) ?>
      <?= htmlspecialchars((string) ($versions['slave'] ?? '?')) ?></b>
    (slave)
</p>

<div class="sv-reload-cards">

    <!-- ============ PHYSICAL ============ -->
    <div class="sv-reload-card">
        <h3><i class="fa fa-database"></i> <?= __('Physical (xtrabackup / mariadb-backup)') ?></h3>
        <div class="sv-reload-subtitle"><?= __('Fast, hot streaming. Requires matching major version on master and slave.') ?></div>

        <ul class="sv-reload-checklist">
            <?php foreach ($conditions as $c):
                $status = (string) ($c['status'] ?? '');
            ?>
                <li>
                    <?= svReloadStatusIcon($status) ?>
                    <span class="sv-reload-msg"><?= htmlspecialchars((string) ($c['message'] ?? '')) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <form method="post" action="<?= LINK ?>Slave/reloadFromMasterStart/<?= $idServer ?>/<?= urlencode($conn) ?>/" style="margin:0">
            <?= $csrfHidden ?>
            <input type="hidden" name="procedure" value="physical">
            <?php if ($hasAtRiskWarn): ?>
                <label class="sv-reload-ack">
                    <input type="checkbox" name="acknowledge_at_risk" value="1" required>
                    <?= __('I acknowledge that ReplicationSourceCoverage reports the replica position as at_risk on the master binlogs.') ?>
                </label>
            <?php endif; ?>
            <button type="submit" class="btn btn-warning <?= $physicalOk ? '' : 'sv-reload-btn-disabled' ?>"
                <?= $physicalOk ? '' : 'disabled' ?>
                onclick="return confirm('<?= __('Wipe slave datadir and stream master backup over SSH. Continue?') ?>');">
                <i class="fa fa-bolt"></i> <?= __('Start physical reload') ?>
            </button>
        </form>
    </div>

    <!-- ============ LOGICAL ============ -->
    <div class="sv-reload-card">
        <h3><i class="fa fa-file-text-o"></i> <?= __('Logical (mariadb-dump --master-data)') ?></h3>
        <div class="sv-reload-subtitle"><?= __('Slower but compatible across major versions and MySQL ↔ MariaDB.') ?></div>

        <ul class="sv-reload-checklist">
            <?php foreach ($conditions as $c):
                $status = (string) ($c['status'] ?? '');
                // For the logical card, downgrade version_match fail to a
                // strikethrough rather than a hard ✗ so the operator sees
                // it does NOT apply here.
                $key = (string) ($c['key'] ?? '');
                $isVersionFail = $key === 'version_match' && $status === ReloadFromMaster::STATUS_FAIL;
            ?>
                <li <?= $isVersionFail ? 'style="opacity:0.5;text-decoration:line-through"' : '' ?>>
                    <?= svReloadStatusIcon($isVersionFail ? ReloadFromMaster::STATUS_WARN : $status) ?>
                    <span class="sv-reload-msg"><?= htmlspecialchars((string) ($c['message'] ?? '')) ?></span>
                    <?php if ($isVersionFail): ?>
                        <em style="color:#94a3b8;font-size:0.85rem">(<?= __('not required for logical') ?>)</em>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <form method="post" action="<?= LINK ?>Slave/reloadFromMasterStart/<?= $idServer ?>/<?= urlencode($conn) ?>/" style="margin:0">
            <?= $csrfHidden ?>
            <input type="hidden" name="procedure" value="logical">
            <?php if ($hasAtRiskWarn): ?>
                <label class="sv-reload-ack">
                    <input type="checkbox" name="acknowledge_at_risk" value="1" required>
                    <?= __('I acknowledge that ReplicationSourceCoverage reports the replica position as at_risk.') ?>
                </label>
            <?php endif; ?>
            <button type="submit" class="btn btn-warning <?= $logicalOk ? '' : 'sv-reload-btn-disabled' ?>"
                <?= $logicalOk ? '' : 'disabled' ?>
                onclick="return confirm('<?= __('Wipe slave datadir and stream mariadb-dump over SSH. Continue?') ?>');">
                <i class="fa fa-cloud-download"></i> <?= __('Start logical reload') ?>
            </button>
        </form>
    </div>

    <!-- ============ MYDUMPER (parallel) ============ -->
    <div class="sv-reload-card">
        <h3><i class="fa fa-bolt"></i> <?= __('mydumper / myloader (parallel)') ?></h3>
        <div class="sv-reload-subtitle"><?= __('Parallel multi-threaded dump/load, streamed via SSH. Faster than mariadb-dump on multi-database workloads. Requires mydumper on master and myloader on slave (probed by the first step).') ?></div>

        <ul class="sv-reload-checklist">
            <?php foreach ($conditions as $c):
                $status = (string) ($c['status'] ?? '');
                $key = (string) ($c['key'] ?? '');
                // Like logical: cross-version is fine for mydumper.
                $isVersionFail = $key === 'version_match' && $status === ReloadFromMaster::STATUS_FAIL;
            ?>
                <li <?= $isVersionFail ? 'style="opacity:0.5;text-decoration:line-through"' : '' ?>>
                    <?= svReloadStatusIcon($isVersionFail ? ReloadFromMaster::STATUS_WARN : $status) ?>
                    <span class="sv-reload-msg"><?= htmlspecialchars((string) ($c['message'] ?? '')) ?></span>
                    <?php if ($isVersionFail): ?>
                        <em style="color:#94a3b8;font-size:0.85rem">(<?= __('not required for mydumper') ?>)</em>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            <li>
                <?= svReloadStatusIcon(ReloadFromMaster::STATUS_WARN) ?>
                <span class="sv-reload-msg"><?= __('mydumper/myloader binaries presence is verified by the first job step (probe_binaries) — install on both sides if missing.') ?></span>
            </li>
        </ul>

        <form method="post" action="<?= LINK ?>Slave/reloadFromMasterStart/<?= $idServer ?>/<?= urlencode($conn) ?>/" style="margin:0">
            <?= $csrfHidden ?>
            <input type="hidden" name="procedure" value="mydumper">

            <fieldset style="border:1px solid #e2e8f0;border-radius:4px;padding:6px 10px;margin:6px 0;font-size:12px">
                <legend style="font-size:11px;color:#475569;padding:0 4px;width:auto"><?= __('Intermediate landing') ?></legend>
<?php
$mydumperSAs = $data['mydumper_storage_areas'] ?? [];
?>
                <label style="display:block;margin:3px 0"><input type="radio" name="landing_storage" value="slave_tmp" checked> <?= __('Slave /tmp (direct stream, requires dump-size free disk on slave)') ?></label>
                <label style="display:block;margin:3px 0"><input type="radio" name="landing_storage" value="pmacontrol_local"> <?= __('PmaControl host') ?> <code>DIRECTORY_BACKUP</code> = <code>/srv/backup</code> <small style="color:#64748b">(<?= __('decouples master/slave, adds an I/O round-trip') ?>)</small></label>
<?php foreach ($mydumperSAs as $sa): ?>
                <label style="display:block;margin:3px 0"><input type="radio" name="landing_storage" value="sa:<?= (int) $sa['id'] ?>"> <?= __('Remote storage area') ?> #<?= (int) $sa['id'] ?> &mdash; <strong><?= htmlspecialchars((string) $sa['libelle']) ?></strong> <code><?= htmlspecialchars((string) $sa['ip']) ?>:<?= (int) $sa['port'] ?></code> <code><?= htmlspecialchars((string) $sa['path']) ?></code></label>
<?php endforeach; ?>
            </fieldset>

            <fieldset style="border:1px solid #e2e8f0;border-radius:4px;padding:6px 10px;margin:6px 0;font-size:12px">
                <legend style="font-size:11px;color:#475569;padding:0 4px;width:auto"><?= __('Compression') ?></legend>
                <select name="compression" style="font-family:monospace;padding:2px 4px">
                    <option value="lz4" selected><?= __('lz4 (fastest compress + decompress — recommended)') ?></option>
                    <option value="zstd"><?= __('zstd (balanced)') ?></option>
                    <option value="gzip"><?= __('gzip (legacy, universal)') ?></option>
                    <option value="xz"><?= __('xz (densest, slowest)') ?></option>
                    <option value="none"><?= __('none (raw tar)') ?></option>
                </select>
                <small style="color:#64748b;display:block;margin-top:4px"><?= __('Binary must be present on both master and slave (probe_binaries step verifies)') ?></small>
            </fieldset>

            <?php if ($hasAtRiskWarn): ?>
                <label class="sv-reload-ack">
                    <input type="checkbox" name="acknowledge_at_risk" value="1" required>
                    <?= __('I acknowledge that ReplicationSourceCoverage reports the replica position as at_risk.') ?>
                </label>
            <?php endif; ?>
            <button type="submit" class="btn btn-warning <?= $mydumperOk ? '' : 'sv-reload-btn-disabled' ?>"
                <?= $mydumperOk ? '' : 'disabled' ?>
                onclick="return confirm('<?= __('Wipe slave tables and stream mydumper over SSH. Continue?') ?>');">
                <i class="fa fa-bolt"></i> <?= __('Start mydumper reload') ?>
            </button>
        </form>
    </div>

</div>

<p style="margin-top:24px;font-size:0.85rem;color:#64748b">
    <?= __('Streaming runs master → slave over SSH. The probe shown above is performed by the PmaControl host as proxy. Confirm the master can also ssh to the slave with the same key before launching.') ?>
    <a href="<?= LINK ?>slave/show/<?= $idServer ?>/<?= urlencode($conn) ?>/"><?= __('Back to slave/show') ?></a>
</p>
