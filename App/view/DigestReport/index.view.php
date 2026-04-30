<?php

use App\Library\Security\CsrfRender;

$data = $data ?? [];
$reports = $data['reports'] ?? [];
$groups = $data['groups'] ?? [];
$schedules = $data['schedules'] ?? [];
$frequencies = $data['frequencies'] ?? ['daily', 'weekly'];
$daysOfWeek = $data['days_of_week'] ?? [];
$warnings = $data['warnings'] ?? [];
$csrfInput = CsrfRender::hiddenInput($data, 'digest_report_save');

if (!function_exists('digest_report_h')) {
    function digest_report_h($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('digest_report_selected')) {
    function digest_report_selected($left, $right): string
    {
        return (string)$left === (string)$right ? ' selected="selected"' : '';
    }
}

?>
<style>
.digest-report-shell { padding:12px 10px 24px 10px; color:#1f2933; }
.digest-report-head { display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:flex-start; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.digest-report-title { margin:0; font-size:22px; font-weight:700; line-height:1.25; }
.digest-report-muted { color:#64748b; }
.digest-report-alert { padding:9px 11px; border-radius:5px; margin-bottom:12px; border:1px solid #d8e1ea; background:#f8fafc; }
.digest-report-alert-error { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
.digest-report-alert-success { background:#ecfdf5; border-color:#bbf7d0; color:#166534; }
.digest-report-alert-warning { background:#fff7ed; border-color:#fed7aa; color:#9a3412; }
.digest-report-section { background:#fff; border:1px solid #d8e1ea; border-radius:6px; overflow:hidden; margin-bottom:14px; }
.digest-report-section-head { padding:12px 14px; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.digest-report-section-title { margin:0; font-size:16px; font-weight:700; }
.digest-report-section-body { padding:12px; }
.digest-report-table { margin-bottom:0; font-size:12px; }
.digest-report-table th { white-space:nowrap; background:#eef4fb; color:#334155; font-size:11px; text-transform:uppercase; }
.digest-report-table td, .digest-report-table th { padding:8px 10px !important; vertical-align:top !important; border-color:#e5e7eb !important; }
.digest-report-form-grid { display:grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap:10px; align-items:end; }
.digest-report-form-grid .form-group { margin-bottom:0; }
.digest-report-command { font-family:monospace; background:#0f172a; color:#e2e8f0; border-radius:5px; padding:8px 10px; word-break:break-all; }
@media (max-width: 1100px) { .digest-report-form-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 700px) { .digest-report-form-grid { grid-template-columns: 1fr; } }
</style>

<div class="digest-report-shell">
    <?php if (!empty($data['flash_message'])): ?>
        <div class="digest-report-alert digest-report-alert-<?= digest_report_h($data['flash_type'] ?? 'warning') ?>">
            <?= digest_report_h($data['flash_message']) ?>
        </div>
    <?php endif; ?>

    <?php foreach ($warnings as $warning): ?>
        <div class="digest-report-alert digest-report-alert-warning"><?= digest_report_h($warning) ?></div>
    <?php endforeach; ?>

    <div class="digest-report-head">
        <div>
            <h2 class="digest-report-title">Digest reports</h2>
            <div class="digest-report-muted">Scheduled Performance Schema digest summaries sent to active users by group.</div>
        </div>
        <div>
            <div class="digest-report-muted">Static cron command</div>
            <div class="digest-report-command"><?= digest_report_h($data['cron_command'] ?? '') ?></div>
        </div>
    </div>

    <div class="digest-report-section">
        <div class="digest-report-section-head">
            <h3 class="digest-report-section-title">Schedules</h3>
        </div>
        <div class="digest-report-section-body table-responsive">
            <table class="table table-condensed table-striped table-bordered digest-report-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Report</th>
                        <th>Group</th>
                        <th>Frequency</th>
                        <th>Next run</th>
                        <th>Last status</th>
                        <th>Recipients</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schedules)): ?>
                        <tr><td colspan="8" class="digest-report-muted">No digest report schedule configured.</td></tr>
                    <?php else: ?>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?= digest_report_h($schedule['name'] ?? '') ?></td>
                                <td><?= digest_report_h($schedule['report_slug'] ?? '') ?></td>
                                <td><?= digest_report_h($schedule['group_name'] ?? '') ?></td>
                                <td><?= digest_report_h($schedule['frequency'] ?? '') ?> <?= (int)($schedule['is_active'] ?? 0) === 1 ? '' : '(disabled)' ?></td>
                                <td><?= digest_report_h($schedule['next_run_at'] ?? '-') ?></td>
                                <td><?= digest_report_h($schedule['last_status'] ?? '-') ?></td>
                                <td><?= digest_report_h($schedule['recipient_count'] ?? '0') ?></td>
                                <td>
                                    <form method="post" class="digest-report-inline-form">
                                        <?= $csrfInput ?>
                                        <input type="hidden" name="digest_report[id]" value="<?= digest_report_h($schedule['id'] ?? 0) ?>">
                                        <input type="hidden" name="digest_report[name]" value="<?= digest_report_h($schedule['name'] ?? '') ?>">
                                        <input type="hidden" name="digest_report[report_slug]" value="<?= digest_report_h($schedule['report_slug'] ?? 'digest_summary') ?>">
                                        <input type="hidden" name="digest_report[id_group]" value="<?= digest_report_h($schedule['id_group'] ?? 0) ?>">
                                        <input type="hidden" name="digest_report[frequency]" value="<?= digest_report_h($schedule['frequency'] ?? 'daily') ?>">
                                        <input type="hidden" name="digest_report[time_of_day]" value="<?= digest_report_h($schedule['time_of_day'] ?? '07:00:00') ?>">
                                        <input type="hidden" name="digest_report[day_of_week]" value="<?= digest_report_h($schedule['day_of_week'] ?? 1) ?>">
                                        <input type="hidden" name="digest_report[limit_rows]" value="<?= digest_report_h($schedule['limit_rows'] ?? ($data['default_limit'] ?? 5)) ?>">
                                        <?php if ((int)($schedule['is_active'] ?? 0) !== 1): ?>
                                            <input type="hidden" name="digest_report[is_active]" value="1">
                                            <button type="submit" class="btn btn-success btn-xs">Enable</button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-warning btn-xs">Disable</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="digest-report-section">
        <div class="digest-report-section-head">
            <h3 class="digest-report-section-title">New or updated schedule</h3>
        </div>
        <div class="digest-report-section-body">
            <form method="post">
                <?= $csrfInput ?>
                <div class="digest-report-form-grid">
                    <div class="form-group">
                        <label for="digest-report-name">Name</label>
                        <input id="digest-report-name" type="text" name="digest_report[name]" class="form-control" maxlength="100" value="Daily digest">
                    </div>
                    <div class="form-group">
                        <label for="digest-report-report">Report</label>
                        <select id="digest-report-report" name="digest_report[report_slug]" class="form-control">
                            <?php foreach ($reports as $report): ?>
                                <option value="<?= digest_report_h($report['slug'] ?? '') ?>"><?= digest_report_h($report['title'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="digest-report-group">Group</label>
                        <select id="digest-report-group" name="digest_report[id_group]" class="form-control">
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= digest_report_h($group['id'] ?? '') ?>"><?= digest_report_h($group['name'] ?? ('#' . ($group['id'] ?? ''))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="digest-report-frequency">Frequency</label>
                        <select id="digest-report-frequency" name="digest_report[frequency]" class="form-control">
                            <?php foreach ($frequencies as $frequency): ?>
                                <option value="<?= digest_report_h($frequency) ?>"><?= digest_report_h($frequency) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="digest-report-time">Time</label>
                        <input id="digest-report-time" type="time" name="digest_report[time_of_day]" class="form-control" value="07:00">
                    </div>
                    <div class="form-group">
                        <label for="digest-report-day">Weekly day</label>
                        <select id="digest-report-day" name="digest_report[day_of_week]" class="form-control">
                            <?php foreach ($daysOfWeek as $day => $label): ?>
                                <option value="<?= digest_report_h($day) ?>"<?= digest_report_selected($day, 1) ?>><?= digest_report_h($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="digest-report-limit">Top rows</label>
                        <input id="digest-report-limit" type="number" min="1" max="<?= digest_report_h($data['max_limit'] ?? 20) ?>" name="digest_report[limit_rows]" class="form-control" value="<?= digest_report_h($data['default_limit'] ?? 5) ?>">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="digest_report[is_active]" value="1">
                            Active
                        </label>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save schedule</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
