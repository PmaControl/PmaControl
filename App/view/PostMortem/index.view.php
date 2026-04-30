<?php

$payload = $data['payload'] ?? [];
$server = $payload['server'] ?? [];
$status = $payload['status'] ?? ['label' => 'UNKNOWN', 'class' => 'unknown'];
$window = $payload['window'] ?? ['start' => '-', 'end' => '-'];
$timeline = $payload['timeline'] ?? ['summary' => []];
$attempts = $payload['attempts'] ?? [];
$variableDiff = $payload['variable_diff'] ?? ['available' => false, 'rows' => [], 'message' => ''];

if (!function_exists('pm_h')) {
    function pm_h($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('pm_ping')) {
    function pm_ping($value): string
    {
        if (!is_numeric($value)) {
            return '-';
        }

        return pm_h(round(((float)$value) * 1000, 2)).' ms';
    }
}

?>
<style>
.postmortem { padding: 12px 10px 24px 10px; color: #1f2933; }
.postmortem .pm-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.postmortem .pm-title { font-size:20px; font-weight:700; }
.postmortem .pm-subtitle { color:#6b7280; margin-top:3px; }
.postmortem .pm-pill { display:inline-block; min-width:86px; text-align:center; border-radius:14px; padding:4px 10px; font-size:12px; font-weight:700; color:#fff; }
.postmortem .pm-pill.up { background:#15803d; }
.postmortem .pm-pill.readonly { background:#2563eb; }
.postmortem .pm-pill.down { background:#dc2626; }
.postmortem .pm-pill.unknown { background:#6b7280; }
.postmortem .pm-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.postmortem .pm-metric { background:#fff; border:1px solid #d8e1ea; border-left:4px solid #0f766e; border-radius:6px; padding:12px; min-height:82px; }
.postmortem .pm-label { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; }
.postmortem .pm-value { margin-top:6px; font-size:18px; font-weight:700; word-break:break-word; }
.postmortem .pm-panel { background:#fff; border:1px solid #d8e1ea; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.postmortem .pm-panel h3 { margin:0; padding:10px 12px; font-size:14px; font-weight:700; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.postmortem .pm-panel-body { padding:12px; }
.postmortem .pm-table { margin-bottom:0; font-size:12px; }
.postmortem .pm-table th { white-space:nowrap; background:#f8fafc; }
.postmortem .pm-muted { color:#6b7280; }
.postmortem .pm-warning { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
@media (max-width: 1000px) { .postmortem .pm-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .postmortem .pm-grid { grid-template-columns: 1fr; } }
</style>

<div class="postmortem">
    <?php foreach (($payload['warnings'] ?? []) as $warning): ?>
        <div class="pm-warning"><?= pm_h($warning) ?></div>
    <?php endforeach; ?>

    <div class="pm-head">
        <div>
            <div class="pm-title">Post-mortem incident</div>
            <div class="pm-subtitle">
                <?= pm_h($server['display_name'] ?? $server['name'] ?? 'No server selected') ?>
                <?php if (!empty($server)): ?>
                    · <?= pm_h($server['client'] ?? '-') ?> / <?= pm_h($server['environment'] ?? '-') ?>
                    · <?= pm_h($server['ip'] ?? '-') ?>:<?= pm_h($server['port'] ?? '-') ?>
                <?php endif; ?>
            </div>
        </div>
        <span class="pm-pill <?= pm_h($status['class'] ?? 'unknown') ?>"><?= pm_h($status['label'] ?? 'UNKNOWN') ?></span>
    </div>

    <div class="pm-grid">
        <div class="pm-metric">
            <div class="pm-label">Window start</div>
            <div class="pm-value"><?= pm_h($window['start'] ?? '-') ?></div>
        </div>
        <div class="pm-metric">
            <div class="pm-label">Window end</div>
            <div class="pm-value"><?= pm_h($window['end'] ?? '-') ?></div>
        </div>
        <div class="pm-metric">
            <div class="pm-label">Status since</div>
            <div class="pm-value"><?= pm_h($status['since'] ?? '-') ?></div>
        </div>
        <div class="pm-metric">
            <div class="pm-label">Last success</div>
            <div class="pm-value"><?= pm_h($payload['last_success_at'] ?? '-') ?></div>
        </div>
    </div>

    <div class="pm-panel">
        <h3>Availability summary</h3>
        <div class="pm-panel-body">
            <?php $summary = $timeline['summary'] ?? []; ?>
            <table class="table table-condensed pm-table">
                <thead><tr><th>UP</th><th>READ ONLY</th><th>DOWN</th><th>UNKNOWN</th><th>Generated at</th></tr></thead>
                <tbody>
                <tr>
                    <td><?= pm_h($summary['up'] ?? 0) ?></td>
                    <td><?= pm_h($summary['readonly'] ?? 0) ?></td>
                    <td><?= pm_h($summary['down'] ?? 0) ?></td>
                    <td><?= pm_h($summary['unknown'] ?? 0) ?></td>
                    <td><?= pm_h($payload['generated_at'] ?? '-') ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pm-panel">
        <h3>Last error</h3>
        <div class="pm-panel-body">
            <?php $lastError = $payload['last_error'] ?? null; ?>
            <?php if (!empty($lastError)): ?>
                <strong><?= pm_h($lastError['error_class'] ?? 'error') ?></strong>
                <span class="pm-muted"><?= pm_h($lastError['started_at'] ?? '') ?></span>
                <pre><?= pm_h($lastError['error_message'] ?? '') ?></pre>
            <?php else: ?>
                <span class="pm-muted">No recent error recorded.</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="pm-panel">
        <h3>Recent aspirateur attempts</h3>
        <table class="table table-condensed table-striped pm-table">
            <thead>
                <tr><th>Date</th><th>Kind</th><th>Phase</th><th>Result</th><th>Ping</th><th>Worker PID</th><th>Error</th></tr>
            </thead>
            <tbody>
            <?php if (empty($attempts)): ?>
                <tr><td colspan="7" class="pm-muted">No attempt recorded for this server.</td></tr>
            <?php else: ?>
                <?php foreach ($attempts as $attempt): ?>
                    <tr>
                        <td><?= pm_h($attempt['started_at'] ?? '-') ?></td>
                        <td><?= pm_h($attempt['kind'] ?? '-') ?></td>
                        <td><?= pm_h($attempt['phase'] ?? '-') ?></td>
                        <td><?= pm_h($attempt['result_label'] ?? $attempt['result'] ?? '-') ?></td>
                        <td><?= pm_ping($attempt['ping_seconds'] ?? null) ?></td>
                        <td><?= pm_h($attempt['worker_pid'] ?? '-') ?></td>
                        <td><?= pm_h($attempt['error_message'] ?? $attempt['error_class'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pm-panel">
        <h3>global_variable today vs yesterday</h3>
        <?php if (empty($variableDiff['available'])): ?>
            <div class="pm-panel-body pm-muted"><?= pm_h($variableDiff['message'] ?? 'global_variable diff unavailable.') ?></div>
        <?php else: ?>
            <table class="table table-condensed table-striped pm-table">
                <thead><tr><th>Variable</th><th>Current</th><th>Yesterday baseline</th></tr></thead>
                <tbody>
                <?php foreach (($variableDiff['rows'] ?? []) as $row): ?>
                    <tr>
                        <td><?= pm_h($row['variable_name'] ?? '') ?></td>
                        <td><?= pm_h($row['value_today'] ?? '') ?></td>
                        <td><?= pm_h($row['value_yesterday'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($variableDiff['rows'])): ?>
                    <tr><td colspan="3" class="pm-muted">No configuration diff detected.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
