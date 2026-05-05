<?php

$payload = $data['payload'] ?? [];
$server = $payload['server'] ?? [];
$status = $payload['status'] ?? ['label' => 'UNKNOWN', 'class' => 'unknown'];
$timeline = $payload['timeline'] ?? ['summary' => []];
$attempts = $payload['attempts'] ?? [];
$variableDiff = $payload['variable_diff'] ?? ['available' => false, 'rows' => [], 'message' => ''];

function kpi_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function kpi_ping($value): string
{
    if (!is_numeric($value)) {
        return '-';
    }

    return kpi_h(round(((float)$value) * 1000, 2)).' ms';
}

?>
<style>
.kpi-server { padding: 12px 10px 24px 10px; color: #1f2933; }
.kpi-server .ks-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.kpi-server .ks-title { font-size:20px; font-weight:700; }
.kpi-server .ks-subtitle { color:#6b7280; margin-top:3px; }
.kpi-server .ks-actions { display:flex; gap:8px; align-items:center; }
.kpi-server .ks-pill { display:inline-block; min-width:86px; text-align:center; border-radius:14px; padding:4px 10px; font-size:12px; font-weight:700; color:#fff; }
.kpi-server .ks-pill.up { background:#15803d; }
.kpi-server .ks-pill.readonly { background:#2563eb; }
.kpi-server .ks-pill.down { background:#dc2626; }
.kpi-server .ks-pill.unknown { background:#6b7280; }
.kpi-server .ks-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:12px; margin-bottom:14px; }
.kpi-server .ks-metric { background:#fff; border:1px solid #d8e1ea; border-left:4px solid #2563eb; border-radius:6px; padding:12px; min-height:82px; }
.kpi-server .ks-label { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; }
.kpi-server .ks-value { margin-top:6px; font-size:18px; font-weight:700; word-break:break-word; }
.kpi-server .ks-panel { background:#fff; border:1px solid #d8e1ea; border-radius:6px; margin-bottom:14px; overflow:hidden; }
.kpi-server .ks-panel h3 { margin:0; padding:10px 12px; font-size:14px; font-weight:700; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.kpi-server .ks-panel-body { padding:12px; }
.kpi-server .ks-chart-timeline { height:34px; width:100%; }
.kpi-server .ks-chart-ping { height:160px; width:100%; }
.kpi-server .ks-table { margin-bottom:0; font-size:12px; }
.kpi-server .ks-table th { white-space:nowrap; background:#f8fafc; }
.kpi-server .ks-muted { color:#6b7280; }
.kpi-server .ks-warning { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
@media (max-width: 1000px) { .kpi-server .ks-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .kpi-server .ks-grid { grid-template-columns: 1fr; } }
</style>

<div class="kpi-server">
    <?php foreach (($payload['warnings'] ?? []) as $warning): ?>
        <div class="ks-warning"><?= kpi_h($warning) ?></div>
    <?php endforeach; ?>

    <div class="ks-head">
        <div>
            <div class="ks-title"><?= kpi_h($server['display_name'] ?? $server['name'] ?? 'Server') ?></div>
            <div class="ks-subtitle">
                <?= kpi_h($server['client'] ?? '-') ?> / <?= kpi_h($server['environment'] ?? '-') ?>
                · <?= kpi_h($server['ip'] ?? '-') ?>:<?= kpi_h($server['port'] ?? '-') ?>
            </div>
        </div>
        <div class="ks-actions">
            <span class="ks-pill <?= kpi_h($status['class'] ?? 'unknown') ?>"><?= kpi_h($status['label'] ?? 'UNKNOWN') ?></span>
            <?php if (!empty($payload['postmortem_available'])): ?>
                <a class="btn btn-default btn-sm" href="<?= LINK.kpi_h($payload['postmortem_path'] ?? '#') ?>">
                    <i class="fa fa-file-text-o"></i> Post-mortem
                </a>
            <?php else: ?>
                <span class="btn btn-default btn-sm disabled" aria-disabled="true" title="Requires postmortem issue #484">
                    <i class="fa fa-file-text-o"></i> Post-mortem
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="ks-grid">
        <div class="ks-metric">
            <div class="ks-label">Depuis</div>
            <div class="ks-value"><?= kpi_h($status['since'] ?? '-') ?></div>
        </div>
        <div class="ks-metric">
            <div class="ks-label">Derniere collecte OK</div>
            <div class="ks-value"><?= kpi_h($payload['last_success_at'] ?? '-') ?></div>
        </div>
        <div class="ks-metric">
            <div class="ks-label">Dernier mysql_error</div>
            <div class="ks-value"><?= kpi_h(($payload['last_error']['error_message'] ?? '') ?: '-') ?></div>
        </div>
        <div class="ks-metric">
            <div class="ks-label">Tentatives 24h</div>
            <div class="ks-value"><?= count($attempts) ?></div>
        </div>
    </div>

    <div class="ks-panel">
        <h3>Timeline 24h</h3>
        <div class="ks-panel-body">
            <canvas id="kpi-server-timeline" class="ks-chart-timeline"></canvas>
            <div class="ks-muted" style="margin-top:8px">
                UP <?= (int)($timeline['summary']['up'] ?? 0) ?> · READ ONLY <?= (int)($timeline['summary']['readonly'] ?? 0) ?> · DOWN <?= (int)($timeline['summary']['down'] ?? 0) ?> · N/A <?= (int)($timeline['summary']['unknown'] ?? 0) ?>
            </div>
        </div>
    </div>

    <div class="ks-panel">
        <h3>Ping par kind</h3>
        <div class="ks-panel-body">
            <canvas id="kpi-server-ping" class="ks-chart-ping"></canvas>
        </div>
    </div>

    <div class="ks-panel">
        <h3>aspirateur_attempt 24h</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered ks-table">
                <thead>
                <tr>
                    <th>started_at</th>
                    <th>kind</th>
                    <th>phase</th>
                    <th>result</th>
                    <th>ping</th>
                    <th>error_class</th>
                    <th>error_message</th>
                    <th>worker_pid</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($attempts)): ?>
                    <tr><td colspan="8" class="text-muted">No attempts found.</td></tr>
                <?php endif; ?>
                <?php foreach ($attempts as $attempt): ?>
                    <tr>
                        <td><?= kpi_h($attempt['started_at'] ?? '-') ?></td>
                        <td><?= kpi_h($attempt['kind'] ?? '-') ?></td>
                        <td><?= kpi_h($attempt['phase'] ?? '-') ?></td>
                        <td><span class="ks-pill <?= kpi_h($attempt['result_class'] ?? 'unknown') ?>"><?= kpi_h($attempt['result_label'] ?? '-') ?></span></td>
                        <td><?= kpi_ping($attempt['ping_seconds'] ?? null) ?></td>
                        <td><?= kpi_h($attempt['error_class'] ?? '-') ?></td>
                        <td><?= kpi_h($attempt['error_message'] ?? '-') ?></td>
                        <td><?= kpi_h($attempt['worker_pid'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="ks-panel">
        <h3>Diff global_variable aujourd'hui vs J-1</h3>
        <div class="table-responsive">
            <table class="table table-condensed table-striped table-bordered ks-table">
                <thead>
                <tr>
                    <th>variable_name</th>
                    <th>J-1</th>
                    <th>Maintenant</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($variableDiff['available'])): ?>
                    <tr><td colspan="3" class="text-muted"><?= kpi_h($variableDiff['message'] ?? 'Snapshot unavailable.') ?></td></tr>
                <?php elseif (empty($variableDiff['rows'])): ?>
                    <tr><td colspan="3" class="text-muted">No diff found.</td></tr>
                <?php endif; ?>
                <?php foreach (($variableDiff['rows'] ?? []) as $row): ?>
                    <tr>
                        <td><?= kpi_h($row['variable_name'] ?? '') ?></td>
                        <td><?= kpi_h($row['value_yesterday'] ?? 'NULL') ?></td>
                        <td><?= kpi_h($row['value_today'] ?? 'NULL') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!empty($variableDiff['has_more'])): ?>
                    <tr><td colspan="3" class="text-muted">More diff rows hidden.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
