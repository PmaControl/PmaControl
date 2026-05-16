<?php
$id   = (int) $data['id_user_main'];
$kind = $data['kind'];
$csrfField = $data['csrf_field'];
$csrfToken = $data['csrf_token'];
?>
<!doctype html>
<html lang="<?= htmlspecialchars(\Glial\I18n\I18n::Get()) ?>">
<head>
<meta charset="utf-8">
<title>Audit — <?= htmlspecialchars($kind) ?> user #<?= $id ?></title>
<style>
body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; padding:32px; max-width:680px;margin:0 auto;background:#f8fafc; }
.card { background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:24px; }
h1 { margin-top:0;color:#1e293b; }
.warn { background:#fef3c7;border-left:4px solid #d97706;padding:10px 14px;color:#92400e;margin:14px 0; }
.danger { background:#fee2e2;border-left:4px solid #b91c1c;padding:10px 14px;color:#7f1d1d;margin:14px 0; }
.btn { padding:8px 18px;border:none;border-radius:4px;font-weight:600;cursor:pointer; }
.btn-primary { background:#4c1d95;color:#fff; }
.btn-danger  { background:#b91c1c;color:#fff; }
.btn-cancel  { background:#e2e8f0;color:#475569; }
</style>
</head>
<body>
<div class="card">
<?php if ($kind === 'export'): ?>
  <h1>Export user #<?= $id ?></h1>
  <div class="warn">
    <strong>GDPR right-to-portability.</strong> This generates a CSV bundle of every
    <code>request_log</code>, <code>auth_event</code>, <code>subprocess_log</code> and
    <code>audit_client_metrics</code> row attributable to this user. The download timestamps
    are recorded in <code>audit_subject_preference.last_export_at</code>.
  </div>
<?php else: ?>
  <h1>Forget user #<?= $id ?></h1>
  <div class="danger">
    <strong>GDPR right-to-erasure.</strong> This pseudonymises every audit row for this user:
    <ul>
      <li><code>request_log.ip → 0.0.0.0</code>, <code>user_agent_hash NULL</code>, <code>referer NULL</code></li>
      <li><code>auth_event.ip → 0.0.0.0</code>, <code>user_agent_hash NULL</code>, <code>detail NULL</code></li>
      <li><code>audit_client_metrics</code> rows deleted entirely (high-entropy data)</li>
    </ul>
    Action is recorded in <code>audit_subject_preference.forget_completed_at</code>.
    <strong>This is irreversible</strong> — the audit chain stays consistent (auth_event hash chain
    re-walks fine since hashed fields don't include `detail`/`ip`).
  </div>
<?php endif; ?>
  <form method="post" action="<?= LINK ?>AuditLog/<?= $kind === 'export' ? 'exportUser' : 'forgetUser' ?>/<?= $id ?>/">
    <input type="hidden" name="<?= htmlspecialchars($csrfField) ?>" value="<?= htmlspecialchars($csrfToken) ?>">
    <button class="btn <?= $kind === 'export' ? 'btn-primary' : 'btn-danger' ?>" type="submit">
      <?= $kind === 'export' ? 'Download CSV' : 'Erase audit data' ?>
    </button>
    <a class="btn btn-cancel" href="<?= LINK ?>AuditLog/user/<?= $id ?>/">Cancel</a>
  </form>
</div>
</body>
</html>
