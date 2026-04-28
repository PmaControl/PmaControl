<?php

$payload = $data['payload'] ?? [];
$router = $payload['router'] ?? [];
$servers = $payload['linked_servers'] ?? [];
$buckets = $payload['buckets'] ?? [];
$windows = $payload['windows'] ?? [];
$attempts = $payload['attempts'] ?? [];
$maxBucket = 0;
foreach ($buckets as $bucket) {
    $maxBucket = max($maxBucket, (int)($bucket['value'] ?? 0));
}

function kpi_router_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function kpi_router_bool($value): string
{
    return ((int)$value === 1) ? 'yes' : 'no';
}

function kpi_router_ms($value): string
{
    if (!is_numeric($value)) {
        return '-';
    }

    return kpi_router_h(round(((float)$value) * 1000, 2)).' ms';
}

function kpi_router_bar_width(int $value, int $max): int
{
    if ($max <= 0) {
        return 0;
    }

    return max(2, min(100, (int)round(($value * 100) / $max)));
}

?>
<style>
.kpi-router { padding: 12px 10px 24px 10px; color: #1f2933; }
.kpi-router .kr-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.kpi-router .kr-title { font-size:20px; font-weight:700; }
.kpi-router .kr-subtitle { color:#6b7280; margin-top:3px; word-break:break-word; }
.kpi-router .kr-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.kpi-router .kr-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.kpi-router .kr-metric { background:#fff; border:1px solid #d8e1ea; border-left:4px solid #2563eb; border-radius:6px; padding:12px; min-height:82px; }
.kpi-router .kr-label { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; }
.kpi-router .kr-value { margin-top:6px; font-size:18px; font-weight:700; word-break:break-word; }
.kpi-router .kr-panel { background:#fff; border:1px solid #d8e1ea; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.kpi-router .kr-panel h3 { margin:0; padding:10px 12px; font-size:14px; font-weight:700; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.kpi-router .kr-table { margin-bottom:0; font-size:12px; }
.kpi-router .kr-table th { white-space:nowrap; background:#f8fafc; }
.kpi-router .kr-muted { color:#6b7280; }
.kpi-router .kr-warning { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
.kpi-router .kr-bar-track { width:100%; min-width:120px; height:14px; background:#e5e7eb; border-radius:3px; overflow:hidden; }
.kpi-router .kr-bar { height:14px; background:#d97706; }
.kpi-router .kr-pill { display:inline-block; min-width:64px; text-align:center; border-radius:14px; padding:4px 10px; font-size:12px; font-weight:700; color:#fff; }
.kpi-router .kr-pill.up { background:#15803d; }
.kpi-router .kr-pill.readonly { background:#2563eb; }
.kpi-router .kr-pill.down { background:#dc2626; }
.kpi-router .kr-pill.unknown { background:#6b7280; }
.kpi-router .kr-pill.warn { background:#d97706; }
.kpi-router .kr-code { font-family: Menlo, Monaco, Consolas, "Courier New", monospace; white-space:pre-wrap; word-break:break-word; max-width:520px; }
@media (max-width: 1000px) { .kpi-router .kr-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .kpi-router .kr-grid { grid-template-columns: 1fr; } }
</style>

<div class="kpi-router">
    <?php foreach (($payload['warnings'] ?? []) as $warning): ?>
        <div class="kr-warning"><?= kpi_router_h($warning) ?></div>
    <?php endforeach; ?>

    <div class="kr-head">
        <div>
            <div class="kr-title"><?= kpi_router_h($router['display_name'] ?? 'MySQL Router') ?></div>
            <div class="kr-subtitle">
                #<?= (int)($payload['router_id'] ?? 0) ?>
                - <?= kpi_router_h(($router['hostname'] ?? '-').':'.($router['port'] ?? '-')) ?>
                - SSL <?= kpi_router_h(kpi_router_bool($router['is_ssl'] ?? 0)) ?>
            </div>
        </div>
        <div class="kr-actions">
            <a class="btn btn-default btn-sm" href="<?= kpi_router_h(LINK.'Kpi/flapping') ?>">Flapping global</a>
            <a class="btn btn-default btn-sm" href="<?= kpi_router_h(LINK.'MysqlRouter/index') ?>">MySQL Router</a>
        </div>
    </div>

    <div class="kr-grid">
        <div class="kr-metric">
            <div class="kr-label">Fenetre</div>
            <div class="kr-value">24h</div>
            <div class="kr-muted">depuis <?= kpi_router_h($payload['since'] ?? '-') ?></div>
        </div>
        <div class="kr-metric">
            <div class="kr-label">Buckets</div>
            <div class="kr-value"><?= count($buckets) ?></div>
        </div>
        <div class="kr-metric">
            <div class="kr-label">Windows flapping</div>
            <div class="kr-value"><?= count($windows) ?></div>
            <div class="kr-muted">&gt; <?= (int)($payload['threshold'] ?? 2) ?>/min pendant <?= (int)($payload['duration_minutes'] ?? 5) ?> min</div>
        </div>
        <div class="kr-metric">
            <div class="kr-label">Attempts detail</div>
            <div class="kr-value"><?= count($attempts) ?></div>
            <div class="kr-muted">limite <?= (int)($payload['attempt_limit'] ?? 0) ?></div>
        </div>
    </div>

    <div class="kr-panel">
        <h3>Serveurs MySQL associes / logs SSH</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered kr-table">
                <thead><tr><th>server</th><th>client/env</th><th>endpoint</th><th>logs</th></tr></thead>
                <tbody>
                <?php if (empty($servers)): ?>
                    <tr><td colspan="4" class="text-muted">No linked MySQL server found.</td></tr>
                <?php endif; ?>
                <?php foreach ($servers as $server): ?>
                    <tr>
                        <td><?= kpi_router_h($server['display_name'] ?? $server['name'] ?? '-') ?></td>
                        <td><?= kpi_router_h(($server['client'] ?? '-').'/'.($server['environment'] ?? '-')) ?></td>
                        <td><?= kpi_router_h(($server['ip'] ?? '-').':'.($server['port'] ?? '-')) ?></td>
                        <td>
                            <?php if (!empty($server['logs_path'])): ?>
                                <a class="btn btn-default btn-xs" href="<?= kpi_router_h(LINK.$server['logs_path']) ?>">Error log</a>
                            <?php else: ?>
                                <span class="kr-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="kr-panel">
        <h3>Histogramme mysqlrouter transitions/minute</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-bordered kr-table">
                <thead><tr><th>bucket_start</th><th>transitions</th><th>attempts</th><th>failures</th><th>readonly</th><th>bar</th></tr></thead>
                <tbody>
                <?php if (empty($buckets)): ?>
                    <tr><td colspan="6" class="text-muted">No mysqlrouter bucket found.</td></tr>
                <?php endif; ?>
                <?php foreach ($buckets as $bucket): ?>
                    <?php $value = (int)($bucket['value'] ?? 0); ?>
                    <tr>
                        <td><?= kpi_router_h($bucket['bucket_start'] ?? '-') ?></td>
                        <td><?= $value ?></td>
                        <td><?= kpi_router_h($bucket['attempts_total'] ?? '-') ?></td>
                        <td><?= kpi_router_h($bucket['failures_total'] ?? '-') ?></td>
                        <td><?= kpi_router_h($bucket['readonly_total'] ?? '-') ?></td>
                        <td><div class="kr-bar-track"><div class="kr-bar" style="width:<?= kpi_router_bar_width($value, $maxBucket) ?>%"></div></div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="kr-panel">
        <h3>Windows flapping routeur</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered kr-table">
                <thead><tr><th>start</th><th>end</th><th>minutes</th><th>max/min</th><th>total</th></tr></thead>
                <tbody>
                <?php if (empty($windows)): ?>
                    <tr><td colspan="5" class="text-muted">No sustained router flapping window detected.</td></tr>
                <?php endif; ?>
                <?php foreach ($windows as $window): ?>
                    <tr>
                        <td><?= kpi_router_h($window['start'] ?? '-') ?></td>
                        <td><?= kpi_router_h($window['end'] ?? '-') ?></td>
                        <td><?= (int)($window['minutes'] ?? 0) ?></td>
                        <td><span class="kr-pill warn"><?= (int)($window['max_value'] ?? 0) ?></span></td>
                        <td><?= (int)($window['total_transitions'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="kr-panel">
        <h3>aspirateur_attempt mysqlrouter 24h</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered kr-table">
                <thead>
                <tr>
                    <th>started_at</th>
                    <th>mysql_server</th>
                    <th>result</th>
                    <th>transition</th>
                    <th>ping</th>
                    <th>error_code</th>
                    <th>error_message</th>
                    <th>worker_pid</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($attempts)): ?>
                    <tr><td colspan="8" class="text-muted">No mysqlrouter attempts found.</td></tr>
                <?php endif; ?>
                <?php foreach ($attempts as $attempt): ?>
                    <?php $errorCode = $attempt['error_code'] ?? ['code' => '-', 'class' => 'other']; ?>
                    <tr>
                        <td><?= kpi_router_h($attempt['started_at'] ?? '-') ?></td>
                        <td><?= kpi_router_h($attempt['id_mysql_server'] ?? '-') ?></td>
                        <td><span class="kr-pill <?= kpi_router_h($attempt['result_class'] ?? 'unknown') ?>"><?= kpi_router_h($attempt['result_label'] ?? '-') ?></span></td>
                        <td><?= kpi_router_h(kpi_router_bool($attempt['triggered_state_change'] ?? 0)) ?></td>
                        <td><?= kpi_router_ms($attempt['ping_seconds'] ?? null) ?></td>
                        <td><span class="kr-pill warn"><?= kpi_router_h($errorCode['code'] ?? '-') ?></span></td>
                        <td><div class="kr-code"><?= kpi_router_h($attempt['error_message'] ?? '-') ?></div></td>
                        <td><?= kpi_router_h($attempt['worker_pid'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
