<?php

$payload = $data['payload'] ?? [];
$context = $payload['context'] ?? [];
$proc = $payload['proc'] ?? [];
$stack = $payload['stack_trace'] ?? [];
$kill = $payload['kill'] ?? ['available' => false, 'reason' => ''];
$executions = $payload['worker_executions'] ?? [];
$pid = (int)($payload['pid'] ?? 0);

function kpi_process_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function kpi_process_bool($value): string
{
    return !empty($value) ? 'yes' : 'no';
}

function kpi_process_ms($value): string
{
    return is_numeric($value) ? kpi_process_h($value).' ms' : '-';
}

function kpi_process_duration($value): string
{
    if (!is_numeric($value)) {
        return '-';
    }

    $seconds = max(0, (int)round((float)$value));
    $days = intdiv($seconds, 86400);
    $seconds %= 86400;
    $hours = intdiv($seconds, 3600);
    $seconds %= 3600;
    $minutes = intdiv($seconds, 60);
    $seconds %= 60;

    if ($days > 0) {
        return sprintf('%dd %02dh %02dm', $days, $hours, $minutes);
    }

    if ($hours > 0) {
        return sprintf('%dh %02dm %02ds', $hours, $minutes, $seconds);
    }

    return sprintf('%dm %02ds', $minutes, $seconds);
}

?>
<style>
.kpi-process { padding: 12px 10px 24px 10px; color: #1f2933; }
.kpi-process .kp-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.kpi-process .kp-title { font-size:20px; font-weight:700; }
.kpi-process .kp-subtitle { color:#6b7280; margin-top:3px; word-break:break-word; }
.kpi-process .kp-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.kpi-process .kp-pill { display:inline-block; min-width:78px; text-align:center; border-radius:14px; padding:4px 10px; font-size:12px; font-weight:700; color:#fff; }
.kpi-process .kp-pill.up { background:#15803d; }
.kpi-process .kp-pill.down { background:#dc2626; }
.kpi-process .kp-pill.unknown { background:#6b7280; }
.kpi-process .kp-grid { display:grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.kpi-process .kp-metric { background:#fff; border:1px solid #d8e1ea; border-left:4px solid #2563eb; border-radius:6px; padding:12px; min-height:82px; }
.kpi-process .kp-label { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; }
.kpi-process .kp-value { margin-top:6px; font-size:18px; font-weight:700; word-break:break-word; }
.kpi-process .kp-panel { background:#fff; border:1px solid #d8e1ea; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.kpi-process .kp-panel h3 { margin:0; padding:10px 12px; font-size:14px; font-weight:700; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.kpi-process .kp-panel-body { padding:12px; }
.kpi-process .kp-table { margin-bottom:0; font-size:12px; }
.kpi-process .kp-table th { white-space:nowrap; background:#f8fafc; }
.kpi-process .kp-muted { color:#6b7280; }
.kpi-process .kp-warning { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
.kpi-process pre { white-space:pre-wrap; word-break:break-word; margin:0; }
@media (max-width: 1200px) { .kpi-process .kp-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 760px) { .kpi-process .kp-grid { grid-template-columns: 1fr; } }
</style>

<div class="kpi-process">
    <?php foreach (($payload['warnings'] ?? []) as $warning): ?>
        <div class="kp-warning"><?= kpi_process_h($warning) ?></div>
    <?php endforeach; ?>

    <div class="kp-head">
        <div>
            <div class="kp-title">PID <?= kpi_process_h($pid) ?></div>
            <div class="kp-subtitle">
                <?= kpi_process_h($context['label'] ?? 'Unknown') ?>
                &middot; <?= kpi_process_h($context['name'] ?? '-') ?>
                &middot; generated <?= kpi_process_h($payload['generated_at'] ?? '-') ?>
            </div>
        </div>
        <div class="kp-actions">
            <span class="kp-pill <?= !empty($proc['alive']) ? 'up' : 'down' ?>"><?= !empty($proc['alive']) ? 'ALIVE' : 'DEAD' ?></span>
            <?php if (!empty($kill['available'])): ?>
                <form method="post" action="<?= kpi_process_h(LINK.'kpi/process/'.$pid.'/kill') ?>" onsubmit="return confirm('Mark this worker for safe kill?');" style="margin:0">
                    <input type="hidden" name="<?= kpi_process_h($data['kill_csrf_field'] ?? '_csrf_token') ?>" value="<?= kpi_process_h($data['kill_csrf_token'] ?? '') ?>">
                    <input type="hidden" name="pid" value="<?= kpi_process_h($pid) ?>">
                    <input type="hidden" name="start_time_ticks" value="<?= kpi_process_h($proc['start_time_ticks'] ?? '') ?>">
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-stop"></i> Mark safe kill</button>
                </form>
            <?php else: ?>
                <span class="btn btn-default btn-sm disabled" aria-disabled="true" title="<?= kpi_process_h($kill['reason'] ?? '') ?>">
                    <i class="fa fa-stop"></i> Mark safe kill
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="kp-grid">
        <div class="kp-metric"><div class="kp-label">Type</div><div class="kp-value"><?= kpi_process_h($context['type'] ?? '-') ?></div></div>
        <div class="kp-metric"><div class="kp-label">Started at</div><div class="kp-value"><?= kpi_process_h($context['started_at'] ?? '-') ?></div></div>
        <div class="kp-metric"><div class="kp-label">Uptime</div><div class="kp-value"><?= kpi_process_h(kpi_process_duration($proc['uptime_seconds'] ?? null)) ?></div></div>
        <div class="kp-metric"><div class="kp-label">CPU time</div><div class="kp-value"><?= kpi_process_h($proc['cpu_seconds'] ?? '-') ?>s</div></div>
        <div class="kp-metric"><div class="kp-label">RSS / peak</div><div class="kp-value"><?= kpi_process_h($proc['rss_kb'] ?? '-') ?> / <?= kpi_process_h($proc['rss_peak_kb'] ?? '-') ?> KB</div></div>
        <div class="kp-metric"><div class="kp-label">FD / threads</div><div class="kp-value"><?= kpi_process_h($proc['fd_count'] ?? '-') ?> / <?= kpi_process_h($proc['threads'] ?? '-') ?></div></div>
    </div>

    <?php if (!empty($payload['not_found'])): ?>
        <div class="kp-panel"><h3>Not found</h3><div class="kp-panel-body">No matching process was found in pmacontrol or /proc.</div></div>
    <?php endif; ?>

    <div class="kp-panel">
        <h3>Context</h3>
        <div class="kp-panel-body">
            <table class="table table-condensed table-bordered kp-table">
                <tbody>
                <tr><th>class::method</th><td><?= kpi_process_h($context['class'] ?? '-') ?>::<?= kpi_process_h($context['method'] ?? '-') ?></td></tr>
                <tr><th>params</th><td><?= kpi_process_h($context['params'] ?? '-') ?></td></tr>
                <tr><th>state / ppid</th><td><?= kpi_process_h($proc['state'] ?? '-') ?> / <?= kpi_process_h($proc['ppid'] ?? '-') ?></td></tr>
                <tr><th>can signal</th><td><?= kpi_process_h(kpi_process_bool($proc['can_signal'] ?? false)) ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="kp-panel">
        <h3>/proc/<?= kpi_process_h($pid) ?>/cmdline</h3>
        <div class="kp-panel-body"><pre><?= kpi_process_h($proc['cmdline'] ?? '') ?></pre></div>
    </div>

    <div class="kp-panel">
        <h3>Stack trace</h3>
        <div class="kp-panel-body">
            <?php if (!empty($stack['available'])): ?>
                <pre><?= kpi_process_h($stack['output'] ?? '') ?></pre>
            <?php else: ?>
                <span class="kp-muted"><?= kpi_process_h($stack['reason'] ?? 'disabled') ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (($context['type'] ?? '') === 'worker'): ?>
        <div class="kp-panel">
            <h3>worker_execution</h3>
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-bordered kp-table">
                    <thead>
                    <tr>
                        <th>id</th><th>server</th><th>started</th><th>end</th><th>time</th><th>status</th><th>attempt</th><th>db queries</th><th>error</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($executions)): ?>
                        <tr><td colspan="9" class="text-muted">No worker_execution rows found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($executions as $execution): ?>
                        <tr>
                            <td><?= kpi_process_h($execution['id'] ?? '-') ?></td>
                            <td><?= kpi_process_h($execution['id_mysql_server'] ?? '-') ?></td>
                            <td><?= kpi_process_h($execution['date_started'] ?? '-') ?></td>
                            <td><?= kpi_process_h($execution['date_end'] ?? '-') ?></td>
                            <td><?= kpi_process_ms($execution['execution_time'] ?? null) ?></td>
                            <td><?= kpi_process_h($execution['status'] ?? '-') ?></td>
                            <td><?= kpi_process_h($execution['attempt_n'] ?? '-') ?></td>
                            <td><?= kpi_process_h($execution['db_queries_count'] ?? '-') ?> / <?= kpi_process_ms($execution['db_queries_time_ms'] ?? null) ?></td>
                            <td><?= kpi_process_h(($execution['error_class'] ?? '').' '.($execution['error_message'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
