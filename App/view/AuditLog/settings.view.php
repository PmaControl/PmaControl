<?php
$id = (int) $data['id_user_main'];
$p  = $data['pref'];
$saved = (bool) $data['saved'];
?>
<style>
.al-card { background:#fff;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:14px; }
.al-card-head { padding:8px 12px;background:linear-gradient(135deg,#1e293b,#4c1d95);color:#fff;font-size:13px;font-weight:600;border-radius:6px 6px 0 0; }
.al-card-body { padding:14px;font-size:13px; }
.al-form-row { display:grid;grid-template-columns:220px 1fr;gap:10px 16px;margin-bottom:12px;align-items:center; }
.al-form-row label { color:#475569;font-size:12px; }
.al-form-row input[type=number], .al-form-row input[type=text] { padding:5px 8px;border:1px solid #cbd5e1;border-radius:3px;width:200px; }
.al-saved { background:#d1fae5;color:#065f46;padding:6px 12px;border-radius:3px;margin-bottom:10px; }
</style>

<div class="al-card">
  <div class="al-card-head">
    <i class="fa fa-cog"></i> <?= __('Audit settings — user #') ?><?= $id ?>
    &nbsp;<small><a href="<?= LINK ?>AuditLog/user/<?= $id ?>/" style="color:#cbd5e1">← <?= __('back to timeline') ?></a></small>
  </div>
  <div class="al-card-body">
<?php if ($saved): ?>
    <div class="al-saved"><?= __('Settings saved.') ?></div>
<?php endif; ?>
    <form method="post" action="<?= LINK ?>AuditLog/settings/<?= $id ?>/">
      <input type="hidden" name="<?= htmlspecialchars($data['csrf_field']) ?>" value="<?= htmlspecialchars($data['csrf_token']) ?>">

      <div class="al-form-row">
        <label for="extended_logging"><?= __('Extended request logging') ?></label>
        <div>
          <input type="checkbox" id="extended_logging" name="extended_logging" value="1"
            <?= !empty($p['extended_logging']) ? 'checked' : '' ?>>
          <small style="color:#94a3b8;display:block;margin-top:3px"><?= __('Captures full URI query strings, response sizes, referer chain. Off by default (privacy).') ?></small>
        </div>
      </div>

      <div class="al-form-row">
        <label for="retention_request_d"><?= __('Retention — request_log (days)') ?></label>
        <div>
          <input type="number" id="retention_request_d" name="retention_request_d" min="1" max="365"
            value="<?= htmlspecialchars((string) ($p['retention_request_d'] ?? '')) ?>" placeholder="14">
          <small style="color:#94a3b8;display:block;margin-top:3px"><?= __('Default 14 d. Override per user (rarely needed).') ?></small>
        </div>
      </div>

      <div class="al-form-row">
        <label for="retention_auth_d"><?= __('Retention — auth_event (days)') ?></label>
        <div>
          <input type="number" id="retention_auth_d" name="retention_auth_d" min="1" max="730"
            value="<?= htmlspecialchars((string) ($p['retention_auth_d'] ?? '')) ?>" placeholder="180">
          <small style="color:#94a3b8;display:block;margin-top:3px"><?= __('Default 180 d (CNIL-aligned for security logs).') ?></small>
        </div>
      </div>

      <div class="al-form-row">
        <label for="notes"><?= __('Operator notes') ?></label>
        <div>
          <input type="text" id="notes" name="notes" maxlength="255"
            value="<?= htmlspecialchars((string) ($p['notes'] ?? '')) ?>"
            placeholder="<?= __('e.g. legal hold #2026-Q2-014, do not purge') ?>" style="width:400px">
        </div>
      </div>

<?php if ($p && !empty($p['forget_completed_at'])): ?>
      <div class="al-form-row">
        <label><?= __('Forget completed at') ?></label>
        <div><code><?= htmlspecialchars((string) $p['forget_completed_at']) ?></code></div>
      </div>
<?php endif; ?>
<?php if ($p && !empty($p['last_export_at'])): ?>
      <div class="al-form-row">
        <label><?= __('Last export at') ?></label>
        <div><code><?= htmlspecialchars((string) $p['last_export_at']) ?></code></div>
      </div>
<?php endif; ?>

      <div style="margin-top:14px">
        <button class="btn-save" type="submit" style="background:#4c1d95;color:#fff;border:none;padding:6px 16px;border-radius:3px;font-weight:600">
          <?= __('Save') ?>
        </button>
        <a href="<?= LINK ?>AuditLog/exportUser/<?= $id ?>/" style="margin-left:14px">
          <?= __('Export this user (GDPR portability)') ?>
        </a>
        &nbsp;·&nbsp;
        <a href="<?= LINK ?>AuditLog/forgetUser/<?= $id ?>/" style="color:#b91c1c">
          <?= __('Forget this user (GDPR erasure)') ?>
        </a>
      </div>
    </form>
  </div>
</div>
