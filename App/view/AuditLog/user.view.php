<?php
use App\Library\Audit\UaIcon;
$u  = $data['user_row'];
$tl = $data['timeline'];
$t  = $data['totals'];
$id = $data['id_user_main'];
?>
<style>
.al-card { background:#fff;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:14px; }
.al-card-head { padding:8px 12px;background:linear-gradient(135deg,#1e293b,#4c1d95);color:#fff;font-size:13px;font-weight:600;border-radius:6px 6px 0 0; }
.al-card-body { padding:10px 12px; }
.al-stat { display:inline-block;padding:6px 14px;margin-right:10px;background:#f8fafc;border-radius:4px;font-size:12px; }
.al-stat strong { display:block;font-size:18px;color:#4c1d95; }
.al-table { width:100%;border-collapse:collapse;font-size:11px; }
.al-table th { background:#f1f5f9;text-align:left;padding:5px 8px;border-bottom:1px solid #cbd5e1; }
.al-table td { padding:4px 8px;border-bottom:1px solid #f1f5f9;vertical-align:top; }
.al-pill { display:inline-block;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:600;color:#fff; }
.al-pill.method-GET { background:#15803d; }
.al-pill.method-POST { background:#1d4ed8; }
.al-pill.method-PUT { background:#92400e; }
.al-pill.method-DELETE { background:#b91c1c; }
.al-pill.method-PATCH { background:#7c3aed; }
.al-pill.method-HEAD, .al-pill.method-OPTIONS { background:#64748b; }
.al-pill.status-2 { background:#15803d; }
.al-pill.status-3 { background:#0369a1; }
.al-pill.status-4 { background:#b45309; }
.al-pill.status-5 { background:#b91c1c; }
.al-pill.robot { background:#92400e; }
.al-uri { font-family:monospace;color:#475569;word-break:break-all;max-width:600px;display:inline-block; }
.audit-ua-icon { font-size:14px;margin-right:4px; }
</style>

<div class="al-card">
  <div class="al-card-head">
    <i class="fa fa-user"></i>
    User #<?= (int) $id ?>
<?php if ($u): ?>
    — <?= htmlspecialchars((string) ($u['login'] ?? '')) ?>
    (<?= htmlspecialchars((string) ($u['firstname'] ?? '')) ?> <?= htmlspecialchars((string) ($u['name'] ?? '')) ?>)
<?php endif; ?>
    &nbsp;<small><a href="<?= LINK ?>AuditLog/index" style="color:#cbd5e1">← <?= __('back to index') ?></a></small>
  </div>
  <div class="al-card-body">
    <span class="al-stat"><strong><?= number_format((int) $t['hits_24h']) ?></strong><?= __('Hits 24 h') ?></span>
    <span class="al-stat"><strong><?= number_format((int) $t['hits_7d']) ?></strong><?= __('Hits 7 d') ?></span>
    <span class="al-stat"><strong><?= (int) $t['distinct_ips_7d'] ?></strong><?= __('Distinct IPs 7 d') ?></span>
    <span class="al-stat"><strong><?= (int) $t['logins'] ?></strong><?= __('Logins 7 d') ?></span>
    <span class="al-stat"><strong><?= (int) $t['failures'] ?></strong><?= __('Auth failures 7 d') ?></span>
    <span class="al-stat"><strong style="color:#64748b;font-size:13px"><?= htmlspecialchars((string) ($t['last_seen'] ?? '—')) ?></strong><?= __('Last seen') ?></span>
  </div>
</div>

<div class="al-card">
  <div class="al-card-head"><?= __('Timeline') ?> — <?= count($tl) ?> <?= __('most recent requests') ?></div>
  <div class="al-card-body">
    <table class="al-table">
      <tr>
        <th><?= __('Date') ?></th>
        <th>Δ</th>
        <th><?= __('IP') ?></th>
        <th><?= __('Geo') ?></th>
        <th><?= __('Method') ?></th>
        <th><?= __('URI') ?></th>
        <th><?= __('Status') ?></th>
        <th style="text-align:right">php_ms</th>
        <th><?= __('Browser') ?></th>
        <th><?= __('OS') ?></th>
        <th></th>
      </tr>
<?php foreach ($tl as $r):
    $statusFirst = isset($r['status']) ? substr((string) $r['status'], 0, 1) : '';
    $methodClass = 'method-' . htmlspecialchars((string) ($r['method'] ?? 'GET'));
?>
      <tr>
        <td><?= htmlspecialchars((string) $r['date']) ?></td>
        <td><small style="color:#94a3b8"><?= $r['delta_s'] !== null ? '+' . (int) $r['delta_s'] . 's' : '' ?></small></td>
        <td><a href="<?= LINK ?>AuditLog/ip/<?= htmlspecialchars((string) $r['ip']) ?>/"><code><?= htmlspecialchars((string) $r['ip']) ?></code></a></td>
        <td><small><?php if (!empty($r['geo_country_iso'])): ?><?= htmlspecialchars((string) $r['geo_country_iso']) ?><?php if (!empty($r['geo_city'])): ?> · <?= htmlspecialchars((string) $r['geo_city']) ?><?php endif; ?><?php else: ?>—<?php endif; ?></small></td>
        <td><span class="al-pill <?= $methodClass ?>"><?= htmlspecialchars((string) ($r['method'] ?? '')) ?></span></td>
        <td><span class="al-uri"><?= htmlspecialchars((string) $r['uri']) ?></span></td>
        <td>
<?php if (isset($r['status'])): ?>
          <span class="al-pill status-<?= $statusFirst ?>"><?= (int) $r['status'] ?></span>
<?php endif; ?>
        </td>
        <td style="text-align:right"><?= isset($r['php_ms']) ? (int) $r['php_ms'] : '—' ?></td>
        <td><small><?= UaIcon::browserIcon($r['browser_family'] ?? null) ?><?= htmlspecialchars((string) ($r['browser_family'] ?? '—')) ?> <?= htmlspecialchars((string) ($r['browser_version'] ?? '')) ?></small></td>
        <td><small><?= UaIcon::osIcon($r['os_family'] ?? null) ?><?= htmlspecialchars((string) ($r['os_family'] ?? '—')) ?>
<?php if (!empty($r['is_robot'])): ?>
            <?= UaIcon::robotIcon($r['robot_family'] ?? null) ?>
<?php endif; ?>
        </small></td>
        <td><a href="<?= LINK ?>AuditLog/correlation/<?= htmlspecialchars((string) $r['request_uid']) ?>/" title="<?= htmlspecialchars((string) $r['request_uid']) ?>"><i class="fa fa-link"></i></a></td>
      </tr>
<?php endforeach; ?>
<?php if (empty($tl)): ?>
      <tr><td colspan="10" style="color:#94a3b8"><?= __('No request_log row for this user yet.') ?></td></tr>
<?php endif; ?>
    </table>
  </div>
</div>
