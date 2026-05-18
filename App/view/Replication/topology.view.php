<?php
$edges = $data['edges'] ?? [];
$dot = (string)($data['dot'] ?? '');
?>

<style>
.repl-page { background:#fff; border:1px solid #e2e8f0; border-radius:6px; padding:16px; margin-bottom:16px; }
.repl-table { width:100%; border-collapse:collapse; font-size:13px; }
.repl-table th, .repl-table td { border-bottom:1px solid #e2e8f0; padding:8px; text-align:left; }
.repl-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-weight:700; font-size:12px; }
.repl-badge.ok { background:#dcfce7; color:#166534; }
.repl-badge.warn { background:#fef3c7; color:#92400e; }
.repl-badge.crit { background:#fee2e2; color:#991b1b; }
</style>

<div class="repl-page">
    <h3 style="margin-top:0"><i class="fa fa-sitemap"></i> <?= __('Replication topology') ?></h3>
    <?php if (empty($edges)): ?>
        <p style="color:#64748b"><?= __('No replication edges found in the latest collected slave metrics.') ?></p>
    <?php else: ?>
        <table class="repl-table">
            <thead>
                <tr>
                    <th><?= __('Source') ?></th>
                    <th><?= __('Replica') ?></th>
                    <th><?= __('Channel') ?></th>
                    <th><?= __('Lag') ?></th>
                    <th>SSL</th>
                    <th><?= __('Filters') ?></th>
                    <th><?= __('Status') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($edges as $edge): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$edge['source_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><a href="<?= LINK ?>slave/show/<?= (int)$edge['replica_id'] ?>/<?= urlencode((string)$edge['channel']) ?>/"><?= htmlspecialchars((string)$edge['replica_name'], ENT_QUOTES, 'UTF-8') ?></a></td>
                    <td><code><?= htmlspecialchars((string)$edge['channel'], ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td><?= (int)$edge['lag'] ?>s</td>
                    <td><?= !empty($edge['ssl']) ? 'on' : 'off' ?></td>
                    <td><?= !empty($edge['filters']) ? __('yes') : __('no') ?></td>
                    <td><span class="repl-badge <?= !empty($edge['healthy']) ? 'ok' : 'crit' ?>"><?= !empty($edge['healthy']) ? __('healthy') : __('attention') ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="repl-page">
    <h4 style="margin-top:0">Graphviz DOT</h4>
    <pre style="white-space:pre-wrap;background:#0f172a;color:#e2e8f0;padding:12px;border-radius:6px"><?= htmlspecialchars($dot, ENT_QUOTES, 'UTF-8') ?></pre>
</div>
