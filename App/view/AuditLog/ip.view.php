<?php
$ip   = $data['ip'];
$rows = $data['rows'];
?>
<style>
.al-card { background:#fff;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:14px; }
.al-card-head { padding:8px 12px;background:linear-gradient(135deg,#1e293b,#4c1d95);color:#fff;font-size:13px;font-weight:600;border-radius:6px 6px 0 0; }
.al-card-body { padding:10px 12px; }
.al-table { width:100%;border-collapse:collapse;font-size:11px; }
.al-table th, .al-table td { padding:5px 8px;border-bottom:1px solid #f1f5f9;text-align:left; }
.al-table th { background:#f1f5f9; }
.al-pill { display:inline-block;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:600;color:#fff; }
.al-pill.robot { background:#92400e; }
</style>

<div class="al-card">
  <div class="al-card-head">
    <i class="fa fa-globe"></i> IP <?= htmlspecialchars($ip) ?>
    &nbsp;<small><a href="<?= LINK ?>AuditLog/index" style="color:#cbd5e1">← <?= __('back to index') ?></a></small>
  </div>
  <div class="al-card-body">
    <table class="al-table">
      <tr><th>date</th><th>user</th><th>method</th><th>uri</th><th>status</th><th>browser</th><th>os</th><th></th></tr>
<?php foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars((string) $r['date']) ?></td>
        <td>
<?php if (!empty($r['id_user_main'])): ?>
          <a href="<?= LINK ?>AuditLog/user/<?= (int) $r['id_user_main'] ?>/"><?= htmlspecialchars((string) ($r['user_login'] ?? '#' . (int) $r['id_user_main'])) ?></a>
<?php else: ?>
          <small style="color:#94a3b8">—</small>
<?php endif; ?>
        </td>
        <td><?= htmlspecialchars((string) $r['method']) ?></td>
        <td><code style="word-break:break-all"><?= htmlspecialchars(substr((string) $r['uri'], 0, 200)) ?></code></td>
        <td><?= isset($r['status']) ? (int) $r['status'] : '' ?></td>
        <td><?= htmlspecialchars((string) ($r['browser_family'] ?? '')) ?> <?= htmlspecialchars((string) ($r['browser_version'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string) ($r['os_family'] ?? '')) ?>
<?php if (!empty($r['is_robot'])): ?>
            <span class="al-pill robot"><?= htmlspecialchars((string) ($r['robot_family'] ?? 'robot')) ?></span>
<?php endif; ?>
        </td>
        <td><a href="<?= LINK ?>AuditLog/correlation/<?= htmlspecialchars((string) $r['request_uid']) ?>/" title="<?= htmlspecialchars((string) $r['request_uid']) ?>"><i class="fa fa-link"></i></a></td>
      </tr>
<?php endforeach; ?>
<?php if (empty($rows)): ?>
      <tr><td colspan="8" style="color:#94a3b8"><?= __('No request_log row for this IP.') ?></td></tr>
<?php endif; ?>
    </table>
  </div>
</div>
