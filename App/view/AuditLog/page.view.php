<?php
$uid       = $data['page_uid_display'];
$root      = $data['root'];
$children  = $data['children'];
$all       = $data['all'];
$userLogin = $data['user_login'];

$first = $all[0]['date']               ?? null;
$last  = $all[count($all) - 1]['date'] ?? null;
$elapsed = ($first && $last) ? (strtotime($last) - strtotime($first)) : 0;
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
.al-tree-root { background:#eef2ff;font-weight:600; }
.al-tree-root td:first-child::before { content:"\f0c4  "; font-family:"FontAwesome";color:#4c1d95; }
.al-tree-child td:first-child { padding-left:32px;position:relative; }
.al-tree-child td:first-child::before { content:"↳ "; color:#94a3b8;margin-right:4px; }
.al-uri { font-family:monospace;color:#475569;word-break:break-all; }
.al-pill { display:inline-block;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:600;color:#fff; }
.al-pill.method-GET  { background:#15803d; }
.al-pill.method-POST { background:#1d4ed8; }
.al-pill.status-2 { background:#15803d; }
.al-pill.status-3 { background:#0369a1; }
.al-pill.status-4 { background:#b45309; }
.al-pill.status-5 { background:#b91c1c; }
</style>

<div class="al-card">
  <div class="al-card-head">
    <i class="fa fa-sitemap"></i>
    <?= __('Page render') ?> — <code style="color:#fde047"><?= htmlspecialchars($uid) ?></code>
    &nbsp;<small><a href="<?= LINK ?>AuditLog/index" style="color:#cbd5e1">← <?= __('back to index') ?></a></small>
  </div>
  <div class="al-card-body">
    <span class="al-stat"><strong><?= number_format(count($all)) ?></strong><?= __('Total requests') ?></span>
    <span class="al-stat"><strong><?= number_format(count($children)) ?></strong><?= __('AJAX children') ?></span>
    <span class="al-stat"><strong><?= htmlspecialchars((string) ($first ?? '—')) ?></strong><?= __('First seen') ?></span>
    <span class="al-stat"><strong><?= htmlspecialchars((string) ($last ?? '—')) ?></strong><?= __('Last seen') ?></span>
    <span class="al-stat"><strong><?= $elapsed ?> s</strong><?= __('Span') ?></span>
<?php if ($root): ?>
    <span class="al-stat"><strong><?= htmlspecialchars((string) $root['ip']) ?></strong><?= __('IP') ?></span>
<?php if ($userLogin !== null): ?>
    <span class="al-stat"><strong><a href="<?= LINK ?>AuditLog/user/<?= (int) $root['id_user_main'] ?>/"><?= htmlspecialchars($userLogin) ?></a></strong><?= __('User') ?></span>
<?php endif; ?>
<?php endif; ?>
  </div>
</div>

<div class="al-card">
  <div class="al-card-head"><?= __('Tree') ?></div>
  <div class="al-card-body">
    <table class="al-table">
      <tr>
        <th><?= __('Time') ?></th>
        <th><?= __('Method') ?></th>
        <th><?= __('URI') ?></th>
        <th style="text-align:right"><?= __('Status') ?></th>
        <th style="text-align:right"><?= __('PHP ms') ?></th>
      </tr>
<?php if ($root): ?>
      <tr class="al-tree-root">
        <td><?= htmlspecialchars((string) $root['date']) ?></td>
        <td><span class="al-pill method-<?= htmlspecialchars((string) $root['method']) ?>"><?= htmlspecialchars((string) $root['method']) ?></span></td>
        <td><span class="al-uri"><?= htmlspecialchars((string) $root['uri']) ?></span></td>
        <td style="text-align:right">
<?php if ($root['status'] !== null): $sc = (int) $root['status']; ?>
          <span class="al-pill status-<?= (int) ($sc / 100) ?>"><?= $sc ?></span>
<?php endif; ?>
        </td>
        <td style="text-align:right"><?= isset($root['php_ms']) ? (int) $root['php_ms'] : '—' ?></td>
      </tr>
<?php endif; ?>
<?php foreach ($children as $c): ?>
      <tr class="al-tree-child">
        <td><?= htmlspecialchars((string) $c['date']) ?></td>
        <td><span class="al-pill method-<?= htmlspecialchars((string) $c['method']) ?>"><?= htmlspecialchars((string) $c['method']) ?></span></td>
        <td><span class="al-uri"><?= htmlspecialchars((string) $c['uri']) ?></span></td>
        <td style="text-align:right">
<?php if ($c['status'] !== null): $sc = (int) $c['status']; ?>
          <span class="al-pill status-<?= (int) ($sc / 100) ?>"><?= $sc ?></span>
<?php endif; ?>
        </td>
        <td style="text-align:right"><?= isset($c['php_ms']) ? (int) $c['php_ms'] : '—' ?></td>
      </tr>
<?php endforeach; ?>
<?php if ($root === null && empty($children)): ?>
      <tr><td colspan="5" style="color:#94a3b8"><?= __('No rows match this page UID.') ?></td></tr>
<?php endif; ?>
    </table>
  </div>
</div>
