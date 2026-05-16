<?php
$checked = (int) $data['checked'];
$broken  = $data['broken'];
$ok      = (bool) $data['ok'];
?>
<style>
.al-card { background:#fff;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:14px; }
.al-card-head { padding:8px 12px;background:linear-gradient(135deg,#1e293b,#4c1d95);color:#fff;font-size:13px;font-weight:600;border-radius:6px 6px 0 0; }
.al-card-head.ok { background:linear-gradient(135deg,#1e293b,#15803d); }
.al-card-head.ko { background:linear-gradient(135deg,#1e293b,#b91c1c); }
.al-card-body { padding:12px;font-size:13px; }
.al-table { width:100%;border-collapse:collapse;font-size:11px; }
.al-table th, .al-table td { padding:5px 8px;border-bottom:1px solid #f1f5f9;text-align:left; }
.al-table th { background:#f1f5f9; }
</style>

<div class="al-card">
  <div class="al-card-head <?= $ok ? 'ok' : 'ko' ?>">
<?php if ($ok): ?>
    ✓ <?= __('Hash chain intact') ?>
<?php else: ?>
    ✗ <?= __('Chain broken') ?>
<?php endif; ?>
    &nbsp;—&nbsp; <?= number_format($checked) ?> <?= __('rows verified') ?>
  </div>
  <div class="al-card-body">
<?php if ($ok): ?>
    <p><?= __('Every <code>auth_event</code> row carries a <code>prev_hash</code> matching the previous row\'s <code>self_hash</code>, and every <code>self_hash</code> recomputes to the stored value. No tampered or deleted row detected.') ?></p>
<?php else: ?>
    <p style="color:#b91c1c"><strong><?= count($broken) ?></strong> <?= __('row(s) failed validation. First broken rows:') ?></p>
    <table class="al-table">
      <tr><th>id</th><th>date</th><th>event_type</th><th>reasons</th></tr>
<?php foreach ($broken as $b): ?>
      <tr>
        <td><?= (int) $b['id'] ?></td>
        <td><?= htmlspecialchars((string) $b['date']) ?></td>
        <td><?= htmlspecialchars((string) $b['event_type']) ?></td>
        <td><?php foreach ($b['reasons'] as $r): ?><div><code><?= htmlspecialchars($r) ?></code></div><?php endforeach; ?></td>
      </tr>
<?php endforeach; ?>
    </table>
<?php endif; ?>
  </div>
</div>
