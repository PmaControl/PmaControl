<?php

$payload = $data['payload'] ?? [];
$daemon = $payload['daemon'] ?? [];
$status = $payload['status'] ?? ['label' => 'UNKNOWN', 'class' => 'unknown'];
$lastCycle = $payload['last_cycle'] ?? null;
$runs = $payload['runs'] ?? [];
$workerQueue = $payload['worker_queue'] ?? null;
$durationSummary = $payload['duration_series']['summary'] ?? [];
$delaySummary = $payload['delay_series']['summary'] ?? [];

function kpi_daemon_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function kpi_daemon_ms($value): string
{
    if (!is_numeric($value)) {
        return '-';
    }

    return kpi_daemon_h(round((float)$value, 2)).' ms';
}

function kpi_daemon_bool_label($value): string
{
    return ((int)$value === 1) ? 'yes' : 'no';
}

?>
<style>
.kpi-daemon { padding: 12px 10px 24px 10px; color: #1f2933; }
.kpi-daemon .kd-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.kpi-daemon .kd-title { font-size:20px; font-weight:700; }
.kpi-daemon .kd-subtitle { color:#6b7280; margin-top:3px; word-break:break-word; }
.kpi-daemon .kd-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.kpi-daemon .kd-pill { display:inline-block; min-width:86px; text-align:center; border-radius:14px; padding:4px 10px; font-size:12px; font-weight:700; color:#fff; }
.kpi-daemon .kd-pill.up { background:#15803d; }
.kpi-daemon .kd-pill.readonly { background:#2563eb; }
.kpi-daemon .kd-pill.down { background:#dc2626; }
.kpi-daemon .kd-pill.unknown { background:#6b7280; }
.kpi-daemon .kd-grid { display:grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.kpi-daemon .kd-metric { background:#fff; border:1px solid #d8e1ea; border-left:4px solid #2563eb; border-radius:6px; padding:12px; min-height:82px; }
.kpi-daemon .kd-label { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; }
.kpi-daemon .kd-value { margin-top:6px; font-size:18px; font-weight:700; word-break:break-word; }
.kpi-daemon .kd-panel { background:#fff; border:1px solid #d8e1ea; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.kpi-daemon .kd-panel h3 { margin:0; padding:10px 12px; font-size:14px; font-weight:700; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.kpi-daemon .kd-panel-body { padding:12px; }
.kpi-daemon .kd-chart { height:180px; width:100%; }
.kpi-daemon .kd-table { margin-bottom:0; font-size:12px; }
.kpi-daemon .kd-table th { white-space:nowrap; background:#f8fafc; }
.kpi-daemon .kd-muted { color:#6b7280; }
.kpi-daemon .kd-warning { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
@media (max-width: 1200px) { .kpi-daemon .kd-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 760px) { .kpi-daemon .kd-grid { grid-template-columns: 1fr; } }
</style>

<div class="kpi-daemon">
    <?php foreach (($payload['warnings'] ?? []) as $warning): ?>
        <div class="kd-warning"><?= kpi_daemon_h($warning) ?></div>
    <?php endforeach; ?>

    <div class="kd-head">
        <div>
            <div class="kd-title"><?= kpi_daemon_h($daemon['name'] ?? 'Daemon') ?></div>
            <div class="kd-subtitle">
                #<?= kpi_daemon_h($daemon['id'] ?? '-') ?>
                &middot; date <?= kpi_daemon_h($daemon['date'] ?? '-') ?>
                &middot; <?= kpi_daemon_h($daemon['class'] ?? '-') ?>/<?= kpi_daemon_h($daemon['method'] ?? '-') ?>
                <?= kpi_daemon_h($daemon['params'] ?? '') ?>
            </div>
        </div>
        <div class="kd-actions">
            <span class="kd-pill <?= kpi_daemon_h($status['class'] ?? 'unknown') ?>"><?= kpi_daemon_h($status['label'] ?? 'UNKNOWN') ?></span>
            <a class="btn btn-default btn-sm" href="<?= kpi_daemon_h(LINK.'daemon/index') ?>">
                <i class="fa fa-calendar"></i> Daemons
            </a>
            <?php if (!empty($payload['worker_link'])): ?>
                <a class="btn btn-default btn-sm" href="<?= kpi_daemon_h(LINK.$payload['worker_link']) ?>">
                    <i class="fa fa-cogs"></i> Workers
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="kd-grid">
        <div class="kd-metric">
            <div class="kd-label">PID courant</div>
            <div class="kd-value"><?= kpi_daemon_h($daemon['pid'] ?? '-') ?></div>
        </div>
        <div class="kd-metric">
            <div class="kd-label">refresh / max delay</div>
            <div class="kd-value"><?= kpi_daemon_h($daemon['refresh_time'] ?? '-') ?>s / <?= kpi_daemon_h($daemon['max_delay'] ?? '-') ?>s</div>
        </div>
        <div class="kd-metric">
            <div class="kd-label">Dernier cycle</div>
            <div class="kd-value"><?= kpi_daemon_h($lastCycle['cycle_started_at'] ?? '-') ?></div>
            <?php if (!empty($lastCycle)): ?>
                <div style="margin-top:6px"><span class="kd-pill <?= kpi_daemon_h($lastCycle['status_class'] ?? 'unknown') ?>"><?= kpi_daemon_h($lastCycle['status'] ?? '-') ?></span></div>
            <?php endif; ?>
        </div>
        <div class="kd-metric">
            <div class="kd-label">Duree / retard</div>
            <div class="kd-value"><?= kpi_daemon_ms($lastCycle['duration_ms'] ?? null) ?> / <?= kpi_daemon_ms($lastCycle['delay_ms'] ?? null) ?></div>
        </div>
        <div class="kd-metric">
            <div class="kd-label">Restart 24h</div>
            <div class="kd-value"><?= (int)($payload['restart_count_24h'] ?? 0) ?></div>
        </div>
    </div>

    <div class="kd-panel">
        <h3>cycle_duration_ms 24h</h3>
        <div class="kd-panel-body">
            <canvas id="kpi-daemon-duration" class="kd-chart"></canvas>
            <div class="kd-muted" style="margin-top:8px">
                points <?= (int)($durationSummary['count'] ?? 0) ?>
                &middot; min <?= kpi_daemon_ms($durationSummary['min'] ?? null) ?>
                &middot; max <?= kpi_daemon_ms($durationSummary['max'] ?? null) ?>
                &middot; avg <?= kpi_daemon_ms($durationSummary['avg'] ?? null) ?>
            </div>
        </div>
    </div>

    <div class="kd-panel">
        <h3>delay_ms 24h</h3>
        <div class="kd-panel-body">
            <canvas id="kpi-daemon-delay" class="kd-chart"></canvas>
            <div class="kd-muted" style="margin-top:8px">
                seuil <?= kpi_daemon_ms($payload['max_delay_ms'] ?? null) ?>
                &middot; points <?= (int)($delaySummary['count'] ?? 0) ?>
                &middot; max <?= kpi_daemon_ms($delaySummary['max'] ?? null) ?>
            </div>
        </div>
    </div>

    <?php if (!empty($workerQueue)): ?>
        <div class="kd-panel">
            <h3>Workers generes</h3>
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-bordered kd-table">
                    <thead>
                    <tr>
                        <th>id</th>
                        <th>name</th>
                        <th>nb_worker</th>
                        <th>queue_number</th>
                        <th>worker</th>
                        <th>table</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?= kpi_daemon_h($workerQueue['id'] ?? '-') ?></td>
                        <td><?= kpi_daemon_h($workerQueue['name'] ?? '-') ?></td>
                        <td><?= kpi_daemon_h($workerQueue['nb_worker'] ?? '-') ?></td>
                        <td><?= kpi_daemon_h($workerQueue['queue_number'] ?? '-') ?></td>
                        <td><?= kpi_daemon_h($workerQueue['worker_class'] ?? '-') ?>/<?= kpi_daemon_h($workerQueue['worker_method'] ?? '-') ?></td>
                        <td><?= kpi_daemon_h($workerQueue['table'] ?? '-') ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="kd-panel">
        <h3>100 derniers daemon_run</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered kd-table">
                <thead>
                <tr>
                    <th>id</th>
                    <th>started</th>
                    <th>ended</th>
                    <th>duration</th>
                    <th>delay</th>
                    <th>status</th>
                    <th>pid</th>
                    <th>skipped</th>
                    <th>over max</th>
                    <th>rss_kb</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($runs)): ?>
                    <tr><td colspan="10" class="text-muted">No daemon_run rows found.</td></tr>
                <?php endif; ?>
                <?php foreach ($runs as $run): ?>
                    <tr>
                        <td><?= kpi_daemon_h($run['id'] ?? '-') ?></td>
                        <td><?= kpi_daemon_h($run['cycle_started_at'] ?? '-') ?></td>
                        <td><?= kpi_daemon_h($run['cycle_ended_at'] ?? '-') ?></td>
                        <td><?= kpi_daemon_ms($run['duration_ms'] ?? null) ?></td>
                        <td><?= kpi_daemon_ms($run['delay_ms'] ?? null) ?></td>
                        <td><span class="kd-pill <?= kpi_daemon_h($run['status_class'] ?? 'unknown') ?>"><?= kpi_daemon_h($run['status'] ?? '-') ?></span></td>
                        <td><?= kpi_daemon_h($run['pid'] ?? '-') ?></td>
                        <td><?= kpi_daemon_h(kpi_daemon_bool_label($run['skipped'] ?? 0)) ?></td>
                        <td><?= kpi_daemon_h(kpi_daemon_bool_label($run['over_max_delay'] ?? 0)) ?></td>
                        <td><?= kpi_daemon_h($run['rss_kb'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
