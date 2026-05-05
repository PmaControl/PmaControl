<?php

$payload = $data['payload'] ?? [];
$buckets = $payload['buckets'] ?? [];
$windows = $payload['windows'] ?? [];
$topRouters = $payload['top_routers'] ?? [];
$events = $payload['events'] ?? [];
$maxBucket = 0;
foreach ($buckets as $bucket) {
    $maxBucket = max($maxBucket, (int)($bucket['value'] ?? 0));
}

function kpi_flap_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function kpi_flap_bar_width(int $value, int $max): int
{
    if ($max <= 0) {
        return 0;
    }

    return max(2, min(100, (int)round(($value * 100) / $max)));
}

?>
<style>
.kpi-flapping { padding: 12px 10px 24px 10px; color: #1f2933; }
.kpi-flapping .kf-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.kpi-flapping .kf-title { font-size:20px; font-weight:700; }
.kpi-flapping .kf-subtitle { color:#6b7280; margin-top:3px; }
.kpi-flapping .kf-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.kpi-flapping .kf-metric { background:#fff; border:1px solid #d8e1ea; border-left:4px solid #2563eb; border-radius:6px; padding:12px; min-height:82px; }
.kpi-flapping .kf-label { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; }
.kpi-flapping .kf-value { margin-top:6px; font-size:18px; font-weight:700; word-break:break-word; }
.kpi-flapping .kf-panel { background:#fff; border:1px solid #d8e1ea; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.kpi-flapping .kf-panel h3 { margin:0; padding:10px 12px; font-size:14px; font-weight:700; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.kpi-flapping .kf-panel-body { padding:12px; }
.kpi-flapping .kf-table { margin-bottom:0; font-size:12px; }
.kpi-flapping .kf-table th { white-space:nowrap; background:#f8fafc; }
.kpi-flapping .kf-muted { color:#6b7280; }
.kpi-flapping .kf-warning { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
.kpi-flapping .kf-bar-track { width:100%; min-width:120px; height:14px; background:#e5e7eb; border-radius:3px; overflow:hidden; }
.kpi-flapping .kf-bar { height:14px; background:#d97706; }
.kpi-flapping .kf-pill { display:inline-block; min-width:64px; text-align:center; border-radius:14px; padding:4px 10px; font-size:12px; font-weight:700; color:#fff; background:#d97706; }
@media (max-width: 1000px) { .kpi-flapping .kf-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .kpi-flapping .kf-grid { grid-template-columns: 1fr; } }
</style>

<div class="kpi-flapping">
    <?php foreach (($payload['warnings'] ?? []) as $warning): ?>
        <div class="kf-warning"><?= kpi_flap_h($warning) ?></div>
    <?php endforeach; ?>

    <div class="kf-head">
        <div>
            <div class="kf-title">KPI flapping</div>
            <div class="kf-subtitle">
                event <?= kpi_flap_h($payload['event_type'] ?? 'kpi_flapping') ?>
                - seuil &gt; <?= (int)($payload['threshold'] ?? 2) ?>/min pendant <?= (int)($payload['duration_minutes'] ?? 5) ?> minutes
            </div>
        </div>
    </div>

    <div class="kf-grid">
        <div class="kf-metric">
            <div class="kf-label">Fenetre</div>
            <div class="kf-value">24h</div>
            <div class="kf-muted">depuis <?= kpi_flap_h($payload['since'] ?? '-') ?></div>
        </div>
        <div class="kf-metric">
            <div class="kf-label">Buckets KPI</div>
            <div class="kf-value"><?= count($buckets) ?></div>
        </div>
        <div class="kf-metric">
            <div class="kf-label">Windows flapping</div>
            <div class="kf-value"><?= count($windows) ?></div>
        </div>
        <div class="kf-metric">
            <div class="kf-label">Routers top</div>
            <div class="kf-value"><?= count($topRouters) ?></div>
        </div>
    </div>

    <div class="kf-panel">
        <h3>Top 10 MySQL Router instables 24h</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered kf-table">
                <thead>
                <tr>
                    <th>router</th>
                    <th>host</th>
                    <th>transitions</th>
                    <th>failures</th>
                    <th>attempts</th>
                    <th>last_seen_at</th>
                    <th>detail</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($topRouters)): ?>
                    <tr><td colspan="7" class="text-muted">No mysqlrouter transition found.</td></tr>
                <?php endif; ?>
                <?php foreach ($topRouters as $router): ?>
                    <tr>
                        <td><?= kpi_flap_h($router['display_name'] ?? '-') ?></td>
                        <td><?= kpi_flap_h(($router['hostname'] ?? '-').':'.($router['port'] ?? '-')) ?></td>
                        <td><?= (int)($router['transitions_total'] ?? 0) ?></td>
                        <td><?= (int)($router['failures_total'] ?? 0) ?></td>
                        <td><?= (int)($router['attempts_total'] ?? 0) ?></td>
                        <td><?= kpi_flap_h($router['last_seen_at'] ?? '-') ?></td>
                        <td><a class="btn btn-default btn-xs" href="<?= kpi_flap_h(LINK.($router['router_path'] ?? '#')) ?>">Router KPI</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="kf-panel">
        <h3>Histogramme global state_transitions_total</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-bordered kf-table">
                <thead><tr><th>bucket_start</th><th>transitions</th><th>bar</th></tr></thead>
                <tbody>
                <?php if (empty($buckets)): ?>
                    <tr><td colspan="3" class="text-muted">No kpi_minute bucket found.</td></tr>
                <?php endif; ?>
                <?php foreach ($buckets as $bucket): ?>
                    <?php $value = (int)($bucket['value'] ?? 0); ?>
                    <tr>
                        <td><?= kpi_flap_h($bucket['bucket_start'] ?? '-') ?></td>
                        <td><?= $value ?></td>
                        <td><div class="kf-bar-track"><div class="kf-bar" style="width:<?= kpi_flap_bar_width($value, $maxBucket) ?>%"></div></div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="kf-panel">
        <h3>Windows detectees</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered kf-table">
                <thead><tr><th>start</th><th>end</th><th>minutes</th><th>max/min</th><th>total</th></tr></thead>
                <tbody>
                <?php if (empty($windows)): ?>
                    <tr><td colspan="5" class="text-muted">No sustained flapping window detected.</td></tr>
                <?php endif; ?>
                <?php foreach ($windows as $window): ?>
                    <tr>
                        <td><?= kpi_flap_h($window['start'] ?? '-') ?></td>
                        <td><?= kpi_flap_h($window['end'] ?? '-') ?></td>
                        <td><?= (int)($window['minutes'] ?? 0) ?></td>
                        <td><span class="kf-pill"><?= (int)($window['max_value'] ?? 0) ?></span></td>
                        <td><?= (int)($window['total_transitions'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="kf-panel">
        <h3>event_log kpi_flapping</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered kf-table">
                <thead><tr><th>id</th><th>date_start</th><th>date_end</th><th>message</th></tr></thead>
                <tbody>
                <?php if (empty($events)): ?>
                    <tr><td colspan="4" class="text-muted">No kpi_flapping event found.</td></tr>
                <?php endif; ?>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= kpi_flap_h($event['id'] ?? '-') ?></td>
                        <td><?= kpi_flap_h($event['date_start'] ?? '-') ?></td>
                        <td><?= kpi_flap_h($event['date_end'] ?? 'open') ?></td>
                        <td><?= kpi_flap_h($event['message'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
