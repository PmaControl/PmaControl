<?php
$servers = $data['servers'] ?? [];
$sourceId = (int)($data['source_id'] ?? 0);
$targetId = (int)($data['target_id'] ?? 0);
$result = $data['result'] ?? null;
?>

<style>
.gtid-page { background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:16px; margin-bottom:16px; }
.gtid-row { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; }
.gtid-row label { display:block; font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:4px; }
.gtid-row select { min-width:260px; padding:6px 8px; border:1px solid #cbd5e1; border-radius:4px; }
.gtid-result code { display:block; white-space:pre-wrap; background:#f8fafc; border:1px solid #e2e8f0; padding:8px; border-radius:4px; }
.gtid-result .danger { color:#991b1b; font-weight:700; }
</style>

<div class="gtid-page">
    <h3 style="margin-top:0"><i class="fa fa-random"></i> <?= __('GTID set diff') ?></h3>
    <form method="get" action="<?= LINK ?>Replication/gtidDiff/">
        <div class="gtid-row">
            <div>
                <label><?= __('Source server') ?></label>
                <select name="source_id">
                    <option value="0">--</option>
                    <?php foreach ($servers as $server): ?>
                    <option value="<?= (int)$server['id'] ?>"<?= (int)$server['id'] === $sourceId ? ' selected' : '' ?>>
                        <?= htmlspecialchars((string)$server['display_name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label><?= __('Compare against') ?></label>
                <select name="target_id">
                    <option value="0">--</option>
                    <?php foreach ($servers as $server): ?>
                    <option value="<?= (int)$server['id'] ?>"<?= (int)$server['id'] === $targetId ? ' selected' : '' ?>>
                        <?= htmlspecialchars((string)$server['display_name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-code-fork"></i> <?= __('Diff GTID sets') ?></button>
        </div>
    </form>
</div>

<?php if ($result): ?>
<div class="gtid-page gtid-result">
    <h4 style="margin-top:0"><?= __('Result') ?></h4>
    <?php if (empty($result['compatible'])): ?>
        <p class="danger"><?= __('Incompatible GTID families; compare MariaDB with MariaDB or MySQL with MySQL.') ?></p>
    <?php endif; ?>
    <p><b><?= __('Source executed') ?></b></p>
    <code><?= htmlspecialchars((string)$result['source'], ENT_QUOTES, 'UTF-8') ?></code>
    <p><b><?= __('Replica executed') ?></b></p>
    <code><?= htmlspecialchars((string)$result['target'], ENT_QUOTES, 'UTF-8') ?></code>
    <p><b><?= __('Only on Source') ?></b></p>
    <code><?= htmlspecialchars((string)$result['only_source'], ENT_QUOTES, 'UTF-8') ?></code>
    <p><b><?= __('Only on Replica') ?></b></p>
    <code><?= htmlspecialchars((string)$result['only_target'], ENT_QUOTES, 'UTF-8') ?></code>
    <p><b><?= __('Intersection') ?></b>: <?= (int)$result['intersection_count'] ?> GTIDs</p>
</div>
<?php endif; ?>
