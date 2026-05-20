<?php
$uid = $data['uid'];
$r   = $data['request'];
$ae  = $data['auth_events'];
$sp  = $data['subprocess'];
$cm  = $data['client_metrics'];
?>
<style>
.al-card { background:#fff;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:14px; }
.al-card-head { padding:8px 12px;background:linear-gradient(135deg,#1e293b,#4c1d95);color:#fff;font-size:13px;font-weight:600;border-radius:6px 6px 0 0; }
.al-card-body { padding:10px 12px;font-size:12px; }
.al-kv { display:grid;grid-template-columns:160px 1fr;gap:4px 16px; }
.al-kv .k { color:#64748b;font-size:11px; }
.al-kv .v { font-family:monospace;font-size:12px;word-break:break-all; }
.al-table { width:100%;border-collapse:collapse;font-size:11px; }
.al-table th, .al-table td { padding:5px 8px;border-bottom:1px solid #f1f5f9;text-align:left; }
.al-table th { background:#f1f5f9; }
</style>

<div class="al-card">
  <div class="al-card-head"><i class="fa fa-link"></i> Request <?= substr(htmlspecialchars($uid), 0, 8) ?>…</div>
  <div class="al-card-body">
<?php if ($r): ?>
    <div class="al-kv">
      <div class="k">request_uid</div><div class="v"><?= htmlspecialchars($uid) ?></div>
      <div class="k">date</div><div class="v"><?= htmlspecialchars((string) $r['date']) ?></div>
      <div class="k">id_user_main</div><div class="v"><?= isset($r['id_user_main']) ? (int) $r['id_user_main'] : '—' ?></div>
      <div class="k">ip</div><div class="v"><?= htmlspecialchars((string) $r['ip']) ?></div>
      <div class="k">method · uri</div><div class="v"><strong><?= htmlspecialchars((string) $r['method']) ?></strong> <?= htmlspecialchars((string) $r['uri']) ?></div>
      <div class="k">status</div><div class="v"><?= (int) ($r['status'] ?? 0) ?></div>
      <div class="k">php_ms · duration_us</div><div class="v"><?= (int) ($r['php_ms'] ?? 0) ?> ms · <?= (int) ($r['duration_us'] ?? 0) ?> µs</div>
      <div class="k">controller · action</div><div class="v"><?= htmlspecialchars((string) ($r['controller'] ?? '')) ?> / <?= htmlspecialchars((string) ($r['action'] ?? '')) ?></div>
      <div class="k">user_role_class</div><div class="v"><?= htmlspecialchars((string) ($r['user_role_class'] ?? '—')) ?></div>
    </div>
<?php else: ?>
    <p style="color:#94a3b8"><?= __('No request_log row found for this uid (maybe not drained yet).') ?></p>
<?php endif; ?>
  </div>
</div>

<?php if (!empty($ae)): ?>
<div class="al-card">
  <div class="al-card-head"><?= __('Auth events on this request_uid') ?> (<?= count($ae) ?>)</div>
  <div class="al-card-body">
    <table class="al-table">
      <tr><th>date</th><th>event_type</th><th>id_user_main</th><th>ip</th><th>selector</th><th>detail</th></tr>
<?php foreach ($ae as $e): ?>
      <tr>
        <td><?= htmlspecialchars((string) $e['date']) ?></td>
        <td><?= htmlspecialchars((string) $e['event_type']) ?></td>
        <td><?= isset($e['id_user_main']) ? (int) $e['id_user_main'] : '—' ?></td>
        <td><?= htmlspecialchars((string) $e['ip']) ?></td>
        <td><code><?= htmlspecialchars((string) ($e['selector_prefix'] ?? '')) ?></code></td>
        <td><small><?= htmlspecialchars((string) ($e['detail'] ?? '')) ?></small></td>
      </tr>
<?php endforeach; ?>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($sp)): ?>
<div class="al-card">
  <div class="al-card-head"><?= __('Sub-processes triggered by this request') ?> (<?= count($sp) ?>)</div>
  <div class="al-card-body">
    <table class="al-table">
      <tr><th>date_start</th><th>label</th><th>command</th><th>pid</th><th>exit</th><th>status</th><th>ms</th></tr>
<?php foreach ($sp as $s): ?>
      <tr>
        <td><?= htmlspecialchars((string) $s['date_start']) ?></td>
        <td><?= htmlspecialchars((string) ($s['label'] ?? '')) ?></td>
        <td><code><?= htmlspecialchars(substr((string) $s['command'], 0, 200)) ?></code></td>
        <td><?= isset($s['pid']) ? (int) $s['pid'] : '—' ?></td>
        <td><?= isset($s['exit_code']) ? (int) $s['exit_code'] : '—' ?></td>
        <td><?= htmlspecialchars((string) $s['status']) ?></td>
        <td><?= isset($s['duration_ms']) ? (int) $s['duration_ms'] : '—' ?></td>
      </tr>
<?php endforeach; ?>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if ($cm): ?>
<div class="al-card">
  <div class="al-card-head"><?= __('Client-side metrics (collected after page load)') ?></div>
  <div class="al-card-body">
    <div class="al-kv">
<?php foreach ($cm as $k => $v): if ($v === null || $v === '') continue; ?>
      <div class="k"><?= htmlspecialchars($k) ?></div>
      <div class="v"><?= htmlspecialchars((string) $v) ?></div>
<?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>
