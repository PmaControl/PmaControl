<?php
use App\Library\Display;

$t  = $data['totals_24h'];
$tu = $data['top_users'];
$tr = $data['top_routes'];
$ti = $data['top_ips'];
$af = $data['auth_failures'];
?>
<style>
.al-card { background:#fff;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:14px; }
.al-card-head { padding:8px 12px;background:linear-gradient(135deg,#1e293b,#4c1d95);color:#fff;font-size:13px;font-weight:600;border-radius:6px 6px 0 0; }
.al-card-body { padding:10px 12px; }
.al-stat { display:inline-block;padding:6px 14px;margin-right:10px;background:#f8fafc;border-radius:4px;font-size:12px; }
.al-stat strong { display:block;font-size:18px;color:#4c1d95; }
.al-table { width:100%;border-collapse:collapse;font-size:12px; }
.al-table th { background:#f1f5f9;text-align:left;padding:6px 8px;border-bottom:1px solid #cbd5e1; }
.al-table td { padding:5px 8px;border-bottom:1px solid #f1f5f9; }
.al-pill { display:inline-block;padding:1px 6px;border-radius:3px;font-size:10px;font-weight:600;color:#fff; }
.al-pill.robot { background:#92400e; }
.al-pill.fail { background:#b91c1c; }
.al-pill.warn { background:#b45309; }
.al-pill.ok   { background:#15803d; }
</style>

<div class="al-card">
  <div class="al-card-head"><i class="fa fa-shield"></i> <?= __('Audit Log') ?> — <?= __('last 24 hours') ?></div>
  <div class="al-card-body">
    <span class="al-stat"><strong><?= number_format($t['hits']) ?></strong><?= __('Requests') ?></span>
    <span class="al-stat"><strong><?= number_format($t['distinct_users']) ?></strong><?= __('Distinct users') ?></span>
    <span class="al-stat"><strong><?= number_format($t['distinct_ips']) ?></strong><?= __('Distinct IPs') ?></span>
    <span class="al-stat"><strong><?= number_format($t['robots']) ?></strong><?= __('Robot hits') ?></span>
    <span class="al-stat"><strong><?= number_format($t['auth_events']) ?></strong><?= __('Auth events') ?></span>
  </div>
</div>

<div class="al-card">
  <div class="al-card-head"><?= __('Activity histogram — last 24 h') ?></div>
  <div class="al-card-body">
    <div style="position:relative;height:140px"><canvas id="al-hist-hits"></canvas></div>
    <div style="position:relative;height:90px;margin-top:8px"><canvas id="al-hist-auth"></canvas></div>
  </div>
</div>
<script src="<?= JS ?>chart-4.5.1.umd.min.js"></script>
<script>
(function () {
  if (!window.Chart) return;
  var labels = <?= json_encode(array_map(static fn($b) => substr($b['ts'], 11, 5), $data['hits_per_hour'])) ?>;
  var human  = <?= json_encode(array_map(static fn($b) => $b['human'], $data['hits_per_hour'])) ?>;
  var robot  = <?= json_encode(array_map(static fn($b) => $b['robot'], $data['hits_per_hour'])) ?>;
  var ok     = <?= json_encode(array_map(static fn($b) => $b['ok'], $data['auth_per_hour'])) ?>;
  var ko     = <?= json_encode(array_map(static fn($b) => $b['ko'], $data['auth_per_hour'])) ?>;
  new Chart(document.getElementById('al-hist-hits'), {
    type: 'bar',
    data: { labels: labels, datasets: [
      { label: 'Human hits', data: human, backgroundColor: '#4c1d95' },
      { label: 'Robot hits', data: robot, backgroundColor: '#b45309' }
    ]},
    options: { responsive: true, maintainAspectRatio: false,
      scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
      plugins: { legend: { position: 'top', align: 'end' } } }
  });
  new Chart(document.getElementById('al-hist-auth'), {
    type: 'bar',
    data: { labels: labels, datasets: [
      { label: 'login_success', data: ok, backgroundColor: '#15803d' },
      { label: 'auth failures', data: ko, backgroundColor: '#b91c1c' }
    ]},
    options: { responsive: true, maintainAspectRatio: false,
      scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
      plugins: { legend: { position: 'top', align: 'end' } } }
  });
})();
</script>

<div class="al-card">
  <div class="al-card-head"><?= __('Top users (24 h)') ?></div>
  <div class="al-card-body">
    <table class="al-table">
      <tr><th><?= __('User') ?></th><th><?= __('Login') ?></th><th style="text-align:right"><?= __('Hits') ?></th><th style="text-align:right"><?= __('Distinct IPs') ?></th><th><?= __('Last seen') ?></th></tr>
<?php foreach ($tu as $u): ?>
      <tr>
        <td><a href="<?= LINK ?>AuditLog/user/<?= (int) $u['id_user_main'] ?>/">#<?= (int) $u['id_user_main'] ?></a></td>
        <td><a href="<?= LINK ?>AuditLog/user/<?= (int) $u['id_user_main'] ?>/"><?= htmlspecialchars((string) $u['login']) ?></a></td>
        <td style="text-align:right"><?= number_format((int) $u['hits']) ?></td>
        <td style="text-align:right"><?= (int) $u['distinct_ips'] ?></td>
        <td><?= htmlspecialchars((string) $u['last_seen']) ?></td>
      </tr>
<?php endforeach; ?>
<?php if (empty($tu)): ?>
      <tr><td colspan="5" style="color:#94a3b8"><?= __('No request_log row in the last 24 h. Drain not running? Check `bin/audit_drain.php` cron.') ?></td></tr>
<?php endif; ?>
    </table>
  </div>
</div>

<div class="al-card">
  <div class="al-card-head"><?= __('Top routes (24 h)') ?></div>
  <div class="al-card-body">
    <table class="al-table">
      <tr><th><?= __('Controller / Action') ?></th><th style="text-align:right"><?= __('Hits') ?></th><th style="text-align:right"><?= __('Avg ms') ?></th><th style="text-align:right"><?= __('Max ms') ?></th></tr>
<?php foreach ($tr as $r): ?>
      <tr>
        <td><code><?= htmlspecialchars((string) $r['controller']) ?>/<?= htmlspecialchars((string) $r['action']) ?></code></td>
        <td style="text-align:right"><?= number_format((int) $r['hits']) ?></td>
        <td style="text-align:right"><?= number_format((int) $r['avg_ms']) ?></td>
        <td style="text-align:right"><?= number_format((int) $r['max_ms']) ?></td>
      </tr>
<?php endforeach; ?>
    </table>
  </div>
</div>

<div class="al-card">
  <div class="al-card-head"><?= __('Top IPs (24 h)') ?></div>
  <div class="al-card-body">
    <table class="al-table">
      <tr><th><?= __('IP') ?></th><th style="text-align:right"><?= __('Hits') ?></th><th style="text-align:right"><?= __('Users') ?></th><th><?= __('Last seen') ?></th></tr>
<?php foreach ($ti as $r): ?>
      <tr>
        <td><a href="<?= LINK ?>AuditLog/ip/<?= htmlspecialchars((string) $r['ip']) ?>/"><code><?= htmlspecialchars((string) $r['ip']) ?></code></a></td>
        <td style="text-align:right"><?= number_format((int) $r['hits']) ?></td>
        <td style="text-align:right"><?= (int) $r['users'] ?></td>
        <td><?= htmlspecialchars((string) $r['last_seen']) ?></td>
      </tr>
<?php endforeach; ?>
    </table>
  </div>
</div>

<div class="al-card">
  <div class="al-card-head"><?= __('Recent auth failures (7 d)') ?></div>
  <div class="al-card-body">
    <table class="al-table">
      <tr><th><?= __('When') ?></th><th><?= __('User') ?></th><th><?= __('IP') ?></th><th><?= __('Event') ?></th><th><?= __('Detail') ?></th><th><?= __('Selector') ?></th></tr>
<?php foreach ($af as $e):
    $pillClass = in_array($e['event_type'], ['token_mismatch','fingerprint_mismatch','csrf_reject','rate_limit'], true) ? 'fail' : 'warn';
?>
      <tr>
        <td><?= htmlspecialchars((string) $e['date']) ?></td>
        <td>
<?php if (!empty($e['id_user_main'])): ?>
          <a href="<?= LINK ?>AuditLog/user/<?= (int) $e['id_user_main'] ?>/"><?= htmlspecialchars((string) ($e['login'] ?? '#' . (int) $e['id_user_main'])) ?></a>
<?php else: ?>
          <small style="color:#94a3b8">—</small>
<?php endif; ?>
        </td>
        <td><a href="<?= LINK ?>AuditLog/ip/<?= htmlspecialchars((string) $e['ip']) ?>/"><code><?= htmlspecialchars((string) $e['ip']) ?></code></a></td>
        <td><span class="al-pill <?= $pillClass ?>"><?= htmlspecialchars((string) $e['event_type']) ?></span></td>
        <td><small><?= htmlspecialchars((string) ($e['detail'] ?? '')) ?></small></td>
        <td><code><small><?= htmlspecialchars((string) ($e['selector_prefix'] ?? '')) ?></small></code></td>
      </tr>
<?php endforeach; ?>
    </table>
  </div>
</div>
