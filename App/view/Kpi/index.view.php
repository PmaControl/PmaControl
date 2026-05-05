<?php

$payload = $data['payload'] ?? [];
$realtime = $payload['realtime'] ?? [];
$latest = $realtime['latest'] ?? [];
$worker = $realtime['worker'] ?? [];
$daemon = $realtime['daemon'] ?? [];
$events = $realtime['events'] ?? [];
$series = $payload['series'] ?? [];
$top = $payload['top'] ?? ['tables' => []];

function kpi_dashboard_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function kpi_dashboard_value($value, string $suffix = ''): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    if (is_numeric($value)) {
        $number = (float)$value;
        $formatted = abs($number - round($number)) < 0.001
            ? (string)(int)round($number)
            : number_format($number, 2, '.', ' ');

        return kpi_dashboard_h($formatted.$suffix);
    }

    return kpi_dashboard_h($value);
}

function kpi_dashboard_metric(array $latest, string $metric, string $suffix = ''): string
{
    return kpi_dashboard_value($latest[$metric] ?? null, $suffix);
}

?>
<style>
.kpi-dashboard { padding: 12px 10px 24px 10px; color: #1f2933; }
.kpi-dashboard .kd-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.kpi-dashboard .kd-title { font-size:22px; font-weight:700; }
.kpi-dashboard .kd-subtitle { color:#64748b; margin-top:3px; }
.kpi-dashboard .kd-badge { display:inline-flex; align-items:center; gap:7px; border-radius:14px; background:#ecfdf5; color:#166534; padding:5px 10px; font-size:12px; font-weight:700; }
.kpi-dashboard .kd-badge-dot { width:8px; height:8px; border-radius:50%; background:#16a34a; box-shadow:0 0 0 4px rgba(22,163,74,.13); }
.kpi-dashboard .kd-warning { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
.kpi-dashboard .kd-grid { display:grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.kpi-dashboard .kd-metric { background:#fff; border:1px solid #d8e1ea; border-left:4px solid #2563eb; border-radius:6px; padding:12px; min-height:84px; }
.kpi-dashboard .kd-metric.warning { border-left-color:#d97706; }
.kpi-dashboard .kd-metric.danger { border-left-color:#dc2626; }
.kpi-dashboard .kd-label { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; }
.kpi-dashboard .kd-value { margin-top:6px; font-size:20px; font-weight:700; word-break:break-word; }
.kpi-dashboard .kd-panel { background:#fff; border:1px solid #d8e1ea; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.kpi-dashboard .kd-panel h3 { margin:0; padding:10px 12px; font-size:14px; font-weight:700; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.kpi-dashboard .kd-panel-body { padding:12px; }
.kpi-dashboard .kd-chart-grid { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.kpi-dashboard .kd-chart { height:260px; width:100%; }
.kpi-dashboard .kd-top-grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:12px; }
.kpi-dashboard .kd-table { margin-bottom:0; font-size:12px; }
.kpi-dashboard .kd-table th { white-space:nowrap; background:#f8fafc; }
.kpi-dashboard .kd-events { display:grid; grid-template-columns: minmax(180px, 260px) minmax(0, 1fr); gap:12px; align-items:start; }
.kpi-dashboard .kd-event-list { list-style:none; padding:0; margin:0; }
.kpi-dashboard .kd-event-list li { border-bottom:1px solid #e5e7eb; padding:7px 0; }
.kpi-dashboard .kd-event-list li:last-child { border-bottom:0; }
.kpi-dashboard .kd-event-type { font-weight:700; color:#334155; }
.kpi-dashboard .kd-muted { color:#6b7280; }
@media (max-width: 1200px) {
    .kpi-dashboard .kd-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .kpi-dashboard .kd-top-grid { grid-template-columns: 1fr; }
}
@media (max-width: 760px) {
    .kpi-dashboard .kd-grid, .kpi-dashboard .kd-chart-grid, .kpi-dashboard .kd-events { grid-template-columns: 1fr; }
}
</style>

<div class="kpi-dashboard">
    <?php foreach (($payload['warnings'] ?? []) as $warning): ?>
        <div class="kd-warning"><?= kpi_dashboard_h($warning) ?></div>
    <?php endforeach; ?>

    <div class="kd-head">
        <div>
            <div class="kd-title">KPI dashboard</div>
            <div class="kd-subtitle">
                Bucket <?= kpi_dashboard_h($latest['bucket_start'] ?? '-') ?>
                &middot; Series <?= kpi_dashboard_h(($series['range']['from'] ?? '-').' -> '.($series['range']['to'] ?? '-')) ?>
            </div>
        </div>
        <div class="kd-badge">
            <span class="kd-badge-dot"></span>
            <span data-kpi-field="generated_at"><?= kpi_dashboard_h($realtime['generated_at'] ?? '-') ?></span>
        </div>
    </div>

    <div class="kd-grid">
        <div class="kd-metric">
            <div class="kd-label">Attempts</div>
            <div class="kd-value" data-kpi-field="latest.aspirateur_attempts_total"><?= kpi_dashboard_metric($latest, 'aspirateur_attempts_total') ?></div>
        </div>
        <div class="kd-metric danger">
            <div class="kd-label">Failures</div>
            <div class="kd-value" data-kpi-field="latest.aspirateur_failures_total"><?= kpi_dashboard_metric($latest, 'aspirateur_failures_total') ?></div>
        </div>
        <div class="kd-metric warning">
            <div class="kd-label">Read only</div>
            <div class="kd-value" data-kpi-field="latest.aspirateur_readonly_total"><?= kpi_dashboard_metric($latest, 'aspirateur_readonly_total') ?></div>
        </div>
        <div class="kd-metric">
            <div class="kd-label">Worker busy</div>
            <div class="kd-value" data-kpi-field="latest.worker_busy_pct"><?= kpi_dashboard_metric($latest, 'worker_busy_pct', '%') ?></div>
        </div>
        <div class="kd-metric warning">
            <div class="kd-label">Daemon late</div>
            <div class="kd-value" data-kpi-field="latest.daemon_late_count"><?= kpi_dashboard_metric($latest, 'daemon_late_count') ?></div>
        </div>
        <div class="kd-metric danger">
            <div class="kd-label">Open events</div>
            <div class="kd-value" data-kpi-field="events.open_count"><?= kpi_dashboard_value($events['open_count'] ?? 0) ?></div>
        </div>
    </div>

    <div class="kd-panel">
        <h3>Realtime</h3>
        <div class="kd-panel-body kd-events">
            <div class="kd-grid" style="grid-template-columns:1fr; margin-bottom:0">
                <div class="kd-metric">
                    <div class="kd-label">Workers running</div>
                    <div class="kd-value" data-kpi-field="worker.running"><?= kpi_dashboard_value($worker['running'] ?? 0) ?></div>
                </div>
                <div class="kd-metric warning">
                    <div class="kd-label">Workers stuck</div>
                    <div class="kd-value" data-kpi-field="worker.stuck"><?= kpi_dashboard_value($worker['stuck'] ?? 0) ?></div>
                </div>
                <div class="kd-metric">
                    <div class="kd-label">Daemons running</div>
                    <div class="kd-value">
                        <span data-kpi-field="daemon.running"><?= kpi_dashboard_value($daemon['running'] ?? 0) ?></span>
                        / <span data-kpi-field="daemon.enabled"><?= kpi_dashboard_value($daemon['enabled'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
            <ul class="kd-event-list" data-kpi-events>
                <?php if (empty($events['recent'])): ?>
                    <li class="kd-muted">No recent events.</li>
                <?php endif; ?>
                <?php foreach (($events['recent'] ?? []) as $event): ?>
                    <li>
                        <div class="kd-event-type"><?= kpi_dashboard_h($event['type'] ?? '-') ?></div>
                        <div><?= kpi_dashboard_h($event['message'] ?? '-') ?></div>
                        <div class="kd-muted"><?= kpi_dashboard_h($event['date_start'] ?? '-') ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="kd-chart-grid">
        <?php foreach (($series['charts'] ?? []) as $chart): ?>
            <div class="kd-panel">
                <h3><?= kpi_dashboard_h($chart['title'] ?? '') ?></h3>
                <div class="kd-panel-body">
                    <canvas id="<?= kpi_dashboard_h($chart['canvas_id'] ?? '') ?>" class="kd-chart"></canvas>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="kd-top-grid">
        <?php foreach (($top['tables'] ?? []) as $table): ?>
            <div class="kd-panel">
                <h3><?= kpi_dashboard_h($table['title'] ?? '') ?></h3>
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-bordered kd-table">
                        <thead>
                        <tr>
                            <th>Server</th>
                            <th>Client</th>
                            <th>Environment</th>
                            <th>Value</th>
                            <th>Last seen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($table['rows'])): ?>
                            <tr><td colspan="5" class="text-muted">No data.</td></tr>
                        <?php endif; ?>
                        <?php foreach (($table['rows'] ?? []) as $row): ?>
                            <?php $metric = $table['metric'] ?? 'value'; ?>
                            <tr>
                                <td>
                                    <?php if (!empty($row['server_path'])): ?>
                                        <a href="<?= LINK.kpi_dashboard_h($row['server_path']) ?>"><?= kpi_dashboard_h($row['display_name'] ?? '-') ?></a>
                                    <?php else: ?>
                                        <?= kpi_dashboard_h($row['display_name'] ?? '-') ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= kpi_dashboard_h($row['client'] ?? '-') ?></td>
                                <td><?= kpi_dashboard_h($row['environment'] ?? '-') ?></td>
                                <td><?= kpi_dashboard_value($row[$metric] ?? $row['value'] ?? 0) ?></td>
                                <td><?= kpi_dashboard_h($row['last_seen_at'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
