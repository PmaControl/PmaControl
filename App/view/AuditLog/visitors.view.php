<?php
$rows = $data['rows'];
?>
<style>
.al-card { background:#fff;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:14px; }
.al-card-head { padding:8px 12px;background:linear-gradient(135deg,#1e293b,#4c1d95);color:#fff;font-size:13px;font-weight:600;border-radius:6px 6px 0 0; }
.al-card-body { padding:10px 12px; }
.al-table { width:100%;border-collapse:collapse;font-size:11px; }
.al-table th { background:#f1f5f9;text-align:left;padding:5px 8px;border-bottom:1px solid #cbd5e1;position:sticky;top:0; }
.al-table td { padding:4px 8px;border-bottom:1px solid #f1f5f9;vertical-align:top; }
.al-uri { font-family:monospace;color:#475569;word-break:break-all; }
.al-pill { display:inline-block;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:600;color:#fff; }
.al-pill.method-GET  { background:#15803d; }
.al-pill.method-POST { background:#1d4ed8; }
.al-pill.status-2 { background:#15803d; }
.al-pill.status-3 { background:#0369a1; }
.al-pill.status-4 { background:#b45309; }
.al-pill.status-5 { background:#b91c1c; }
.al-mother { font-size:10px;color:#64748b;display:block;margin-top:2px; }
.al-mother::before { content:"↑ "; color:#94a3b8; }
</style>

<div class="al-card">
  <div class="al-card-head">
    <i class="fa fa-stream"></i>
    <?= __('Visitor feed — last 100 page renders (24 h)') ?>
    &nbsp;<small><a href="<?= LINK ?>AuditLog/index" style="color:#cbd5e1">← <?= __('back to index') ?></a></small>
  </div>
  <div class="al-card-body">
    <small style="color:#64748b"><?= __('All users mixed, chronological (latest first). Each row = one HTML page render with its AJAX descendants aggregated. The "Mother page" column is the previous page (resolved via Referer) that linked here.') ?></small>
    <table class="al-table">
      <tr>
        <th><?= __('Page UID') ?></th>
        <th><?= __('Route') ?></th>
        <th><?= __('Mother page') ?></th>
        <th><?= __('User') ?></th>
        <th><?= __('IP') ?></th>
        <th style="text-align:right"><?= __('AJAX') ?></th>
        <th><?= __('First seen') ?></th>
        <th><?= __('Last seen') ?></th>
      </tr>
<?php foreach ($rows as $r): ?>
      <tr>
        <td><code><a href="<?= LINK ?>AuditLog/page/<?= htmlspecialchars((string) $r['page_uid_hex']) ?>/"><?= htmlspecialchars(substr((string) $r['page_uid_hex'], 0, 8)) ?>…</a></code></td>
        <td>
          <span class="al-pill method-<?= htmlspecialchars((string) $r['method']) ?>"><?= htmlspecialchars((string) $r['method']) ?></span>
<?php if ($r['status'] !== null): $sc = (int) $r['status']; ?>
          <span class="al-pill status-<?= (int) ($sc / 100) ?>"><?= $sc ?></span>
<?php endif; ?>
          <span class="al-uri"><?= htmlspecialchars((string) $r['root_uri']) ?></span>
        </td>
        <td>
<?php if (!empty($r['mother_uid_hex'])): ?>
          <a href="<?= LINK ?>AuditLog/page/<?= htmlspecialchars((string) $r['mother_uid_hex']) ?>/" title="<?= htmlspecialchars((string) $r['mother_uri']) ?>"><code><?= htmlspecialchars(substr((string) $r['mother_uid_hex'], 0, 8)) ?>…</code></a>
          <span class="al-mother"><?= htmlspecialchars((string) $r['mother_uri']) ?></span>
<?php else: ?>
          <small style="color:#94a3b8">—</small>
<?php endif; ?>
        </td>
        <td>
<?php if (!empty($r['id_user_main'])): ?>
          <a href="<?= LINK ?>AuditLog/user/<?= (int) $r['id_user_main'] ?>/"><?= htmlspecialchars((string) ($r['login'] ?? '#' . (int) $r['id_user_main'])) ?></a>
<?php else: ?>
          <small style="color:#94a3b8">—</small>
<?php endif; ?>
        </td>
        <td><a href="<?= LINK ?>AuditLog/ip/<?= htmlspecialchars((string) $r['ip']) ?>/"><code><?= htmlspecialchars((string) $r['ip']) ?></code></a></td>
        <td style="text-align:right"><?= number_format((int) $r['ajax_hits']) ?></td>
        <td><?= htmlspecialchars((string) $r['first_seen']) ?></td>
        <td><?= htmlspecialchars((string) $r['last_seen']) ?></td>
      </tr>
<?php endforeach; ?>
<?php if (empty($rows)): ?>
      <tr><td colspan="8" style="color:#94a3b8"><?= __('No page render in the last 24 h carries a page_uid yet.') ?></td></tr>
<?php endif; ?>
    </table>
  </div>
</div>
