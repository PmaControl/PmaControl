<?php

$payload = $data['payload'] ?? [];
$server = $payload['server'] ?? [];
$transitions = $payload['transitions'] ?? [];
$history = $payload['history'] ?? ['available' => false, 'message' => ''];
$eventLog = $payload['event_log'] ?? ['available' => false, 'message' => ''];

function kpi_ro_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function kpi_ro_bool($value): string
{
    return ((int)$value === 1) ? 'yes' : 'no';
}

function kpi_ro_reason_value(array $reason, string $key): string
{
    $value = $reason[$key] ?? null;
    if ($value === null || $value === '') {
        return '-';
    }

    return (string)$value;
}

?>
<style>
.kpi-readonly { padding: 12px 10px 24px 10px; color: #1f2933; }
.kpi-readonly .kr-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.kpi-readonly .kr-title { font-size:20px; font-weight:700; }
.kpi-readonly .kr-subtitle { color:#6b7280; margin-top:3px; word-break:break-word; }
.kpi-readonly .kr-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.kpi-readonly .kr-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.kpi-readonly .kr-metric { background:#fff; border:1px solid #d8e1ea; border-left:4px solid #2563eb; border-radius:6px; padding:12px; min-height:82px; }
.kpi-readonly .kr-label { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; }
.kpi-readonly .kr-value { margin-top:6px; font-size:18px; font-weight:700; word-break:break-word; }
.kpi-readonly .kr-panel { background:#fff; border:1px solid #d8e1ea; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.kpi-readonly .kr-panel h3 { margin:0; padding:10px 12px; font-size:14px; font-weight:700; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.kpi-readonly .kr-panel-body { padding:12px; }
.kpi-readonly .kr-table { margin-bottom:0; font-size:12px; }
.kpi-readonly .kr-table th { white-space:nowrap; background:#f8fafc; }
.kpi-readonly .kr-muted { color:#6b7280; }
.kpi-readonly .kr-warning { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
.kpi-readonly .kr-info { background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
.kpi-readonly .kr-pill { display:inline-block; min-width:64px; text-align:center; border-radius:14px; padding:4px 10px; font-size:12px; font-weight:700; color:#fff; }
.kpi-readonly .kr-pill.readonly { background:#2563eb; }
.kpi-readonly .kr-pill.yes { background:#d97706; }
.kpi-readonly .kr-pill.no { background:#64748b; }
.kpi-readonly .kr-pill.ok { background:#15803d; }
.kpi-readonly .kr-pill.warn { background:#b45309; }
.kpi-readonly .kr-code { font-family: Menlo, Monaco, Consolas, "Courier New", monospace; white-space:pre-wrap; word-break:break-word; max-width:520px; }
.kpi-readonly .kr-events { margin:0; padding-left:16px; }
.kpi-readonly .kr-events li { margin-bottom:4px; }
@media (max-width: 1000px) { .kpi-readonly .kr-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .kpi-readonly .kr-grid { grid-template-columns: 1fr; } }
</style>

<div class="kpi-readonly">
    <?php foreach (($payload['warnings'] ?? []) as $warning): ?>
        <div class="kr-warning"><?= kpi_ro_h($warning) ?></div>
    <?php endforeach; ?>

    <?php if (empty($history['available']) && !empty($history['message'])): ?>
        <div class="kr-warning"><?= kpi_ro_h($history['message']) ?></div>
    <?php endif; ?>

    <?php if (empty($eventLog['available']) && !empty($eventLog['message'])): ?>
        <div class="kr-info"><?= kpi_ro_h($eventLog['message']) ?></div>
    <?php endif; ?>

    <div class="kr-head">
        <div>
            <div class="kr-title">read_only transitions - <?= kpi_ro_h($server['display_name'] ?? $server['name'] ?? 'Server') ?></div>
            <div class="kr-subtitle">
                <?= kpi_ro_h($server['client'] ?? '-') ?> / <?= kpi_ro_h($server['environment'] ?? '-') ?>
                - <?= kpi_ro_h($server['ip'] ?? '-') ?>:<?= kpi_ro_h($server['port'] ?? '-') ?>
            </div>
        </div>
        <div class="kr-actions">
            <a class="btn btn-default btn-sm" href="<?= kpi_ro_h(LINK.'kpi/server/'.(int)($payload['server_id'] ?? 0)) ?>">
                <i class="fa fa-line-chart"></i> KPI server
            </a>
            <a class="btn btn-default btn-sm" href="<?= kpi_ro_h(LINK.'server/main') ?>">
                <i class="fa fa-server"></i> Servers
            </a>
        </div>
    </div>

    <div class="kr-grid">
        <div class="kr-metric">
            <div class="kr-label">Fenetre</div>
            <div class="kr-value">24h</div>
            <div class="kr-muted">depuis <?= kpi_ro_h($payload['since'] ?? '-') ?></div>
        </div>
        <div class="kr-metric">
            <div class="kr-label">Transitions read_only</div>
            <div class="kr-value"><?= (int)($payload['transition_count'] ?? 0) ?></div>
            <div class="kr-muted">limite <?= (int)($payload['transition_limit'] ?? 0) ?></div>
        </div>
        <div class="kr-metric">
            <div class="kr-label">event_log candidats</div>
            <div class="kr-value"><?= kpi_ro_h($eventLog['candidate_count'] ?? '-') ?></div>
            <div class="kr-muted">fenetre +/- 5 min</div>
        </div>
        <div class="kr-metric">
            <div class="kr-label">Genere</div>
            <div class="kr-value"><?= kpi_ro_h($payload['generated_at'] ?? '-') ?></div>
        </div>
    </div>

    <div class="kr-panel">
        <h3>Transitions aspirateur_attempt result=2 / triggered_state_change=1</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered kr-table">
                <thead>
                <tr>
                    <th>started_at</th>
                    <th>worker_pid</th>
                    <th>kind / phase</th>
                    <th>read_only</th>
                    <th>super_read_only</th>
                    <th>transient</th>
                    <th>error_message</th>
                    <th>ts context</th>
                    <th>event_log +/-5min</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($transitions)): ?>
                    <tr><td colspan="9" class="text-muted">No read_only transition found in the last 24 hours.</td></tr>
                <?php endif; ?>
                <?php foreach ($transitions as $transition): ?>
                    <?php $reason = $transition['readonly_reason'] ?? []; ?>
                    <?php $readOnlyValue = kpi_ro_reason_value($reason, 'read_only'); ?>
                    <?php $superReadOnlyValue = kpi_ro_reason_value($reason, 'super_read_only'); ?>
                    <tr>
                        <td><?= kpi_ro_h($transition['started_at'] ?? '-') ?></td>
                        <td><?= kpi_ro_h($transition['worker_pid'] ?? '-') ?></td>
                        <td><?= kpi_ro_h($transition['kind'] ?? '-') ?> / <?= kpi_ro_h($transition['phase'] ?? '-') ?></td>
                        <td>
                            <?php if ($readOnlyValue === '-'): ?>
                                <span class="kr-muted">-</span>
                            <?php else: ?>
                                <span class="kr-pill readonly"><?= kpi_ro_h($readOnlyValue) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($superReadOnlyValue === '-'): ?>
                                <span class="kr-muted">-</span>
                            <?php else: ?>
                                <span class="kr-pill readonly"><?= kpi_ro_h($superReadOnlyValue) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><span class="kr-pill <?= ((int)($transition['transient'] ?? 0) === 1) ? 'yes' : 'no' ?>"><?= kpi_ro_h(kpi_ro_bool($transition['transient'] ?? 0)) ?></span></td>
                        <td>
                            <div><?= kpi_ro_h($transition['error_message'] ?? '-') ?></div>
                            <?php if (!empty($reason['raw'])): ?>
                                <details>
                                    <summary class="kr-muted">set_readonly_reason</summary>
                                    <div class="kr-code"><?= kpi_ro_h($reason['raw']) ?></div>
                                </details>
                            <?php elseif (!empty($reason['message'])): ?>
                                <div class="kr-muted"><?= kpi_ro_h($reason['message']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($transition['context_path'])): ?>
                                <a class="btn btn-default btn-xs" href="<?= kpi_ro_h(LINK.$transition['context_path']) ?>">
                                    <i class="fa fa-search"></i> runDetail
                                </a>
                            <?php else: ?>
                                <span class="kr-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (empty($transition['events'])): ?>
                                <span class="kr-muted">No correlated event.</span>
                            <?php else: ?>
                                <ul class="kr-events">
                                    <?php foreach ($transition['events'] as $event): ?>
                                        <li>
                                            #<?= kpi_ro_h($event['id'] ?? '-') ?>
                                            <?= kpi_ro_h($event['type'] ?? '-') ?>
                                            <span class="kr-muted"><?= kpi_ro_h($event['date_start'] ?? '-') ?></span>
                                            <div class="kr-code"><?= kpi_ro_h($event['message'] ?? '') ?></div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="kr-panel">
        <h3>Correlation history_action</h3>
        <div class="kr-panel-body">
            <?php if (!empty($history['available'])): ?>
                <span class="kr-pill ok">available</span>
            <?php else: ?>
                <span class="kr-pill warn">degraded</span>
            <?php endif; ?>
            <span class="kr-muted"><?= kpi_ro_h($history['message'] ?? '') ?></span>
        </div>
    </div>
</div>
