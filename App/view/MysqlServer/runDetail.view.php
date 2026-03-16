<?php

use \Glial\Synapse\FactoryController;

function run_detail_render_json_tree($value, $path = 'root', $defaultOpen = false)
{
    if (!is_array($value)) {
        if (is_bool($value)) {
            return '<span class="run-json-scalar run-json-bool">' . ($value ? 'true' : 'false') . '</span>';
        }

        if ($value === null) {
            return '<span class="run-json-scalar run-json-null">null</span>';
        }

        if (is_numeric($value)) {
            return '<span class="run-json-scalar run-json-number">' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '</span>';
        }

        return '<span class="run-json-scalar run-json-string">"' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"</span>';
    }

    $isSequential = array_keys($value) === range(0, count($value) - 1);
    $count = count($value);
    $label = $isSequential ? '[' . $count . ']' : '{' . $count . '}';

    if ($count === 0) {
        return '<span class="run-json-empty">' . ($isSequential ? '[]' : '{}') . '</span>';
    }

    $html = '<details class="run-json-node"' . ($defaultOpen ? ' open' : '') . '>';
    $html .= '<summary><span class="run-json-bracket">' . $label . '</span></summary>';
    $html .= '<ul class="run-json-list">';

    foreach ($value as $key => $child) {
        $childPath = $path . '.' . (string) $key;
        $html .= '<li class="run-json-item">';
        $html .= '<span class="run-json-key">' . htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '<span class="run-json-sep">: </span>';
        $html .= run_detail_render_json_tree($child, $childPath, false);
        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</details>';

    return $html;
}

function run_detail_render_metric_value(array $metric)
{
    $value = (string) ($metric['metric_value'] ?? '');
    $metricType = strtoupper((string) ($metric['metric_type'] ?? ''));

    if ($metricType === 'TEXT') {
        $html = nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));

        if (!empty($metric['digest_text'])) {
            $html .= '<div class="run-detail-digest">' . htmlspecialchars((string) $metric['digest_text'], ENT_QUOTES, 'UTF-8') . '</div>';
        }

        return $html;
    }

    if ($metricType !== 'JSON') {
        $html = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        if (!empty($metric['digest_text'])) {
            $html .= '<div class="run-detail-digest">' . htmlspecialchars((string) $metric['digest_text'], ENT_QUOTES, 'UTF-8') . '</div>';
        }

        return $html;
    }

    $decoded = json_decode($value, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return '<div class="run-detail-json-invalid">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</div>';
    }

    return '<div class="run-detail-json">' . run_detail_render_json_tree($decoded, 'root', true) . '</div>';
}

$server = $data['server'] ?? [];
$sections = $data['sections'] ?? [];
$files = $data['files'] ?? [];
$date = (string) ($data['date'] ?? '');
$serverId = (int) ($server['id'] ?? 0);
$serverLabel = (string) ($server['display_name'] ?? $server['name'] ?? ('Server #' . $serverId));
$serverEndpoint = trim((string) (($server['ip'] ?? '') . ':' . ($server['port'] ?? '')), ':');
$metricSources = [];

foreach ($sections as $groups) {
    foreach ($groups as $metrics) {
        foreach ($metrics as $metric) {
            $sourceName = trim((string) ($metric['metric_source'] ?? ''));
            if ($sourceName !== '') {
                $metricSources[$sourceName] = true;
            }
        }
    }
}

$sourceCount = count($metricSources);

FactoryController::addNode("MysqlServer", "menu", [$serverId, '', $date]);
?>

<style>
.run-detail-shell {
    padding: 12px 10px 24px 0;
}

.run-detail-header {
    margin: 0 0 16px 0;
    padding: 16px 18px;
    border-radius: 6px;
    background: linear-gradient(135deg,#0f172a,#1e3a8a);
    color: #fff;
}

.run-detail-title {
    margin: 0 0 8px 0;
    font-size: 22px;
    line-height: 1.2;
}

.run-detail-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 0;
}

.run-detail-chip {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
    font-size: 12px;
}

.run-detail-summary-card {
    background:#fff;
    border:1px solid #d7dde6;
    border-left:5px solid #2563eb;
    border-radius:4px;
    padding:12px 14px;
    min-height:86px;
    margin-bottom: 16px;
}

@media (min-width: 992px) {
    .run-detail-fifth {
        width: 20%;
    }
}

.run-detail-summary-label {
    color:#6b7280;
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.run-detail-summary-value {
    font-size:28px;
    font-weight:700;
    line-height:1.2;
}

.run-detail-panel {
    background:#fff;
    border:1px solid #cfd8e3;
    border-radius:6px;
    overflow:hidden;
    margin-bottom:16px;
}

.run-detail-panel-head {
    background:#f8fafc;
    border-bottom:1px solid #e5e7eb;
    padding:10px 12px;
    font-weight:700;
}

.run-detail-panel-body {
    padding:12px;
}

.run-section {
    background:#fff;
    border:1px solid #cfd8e3;
    border-radius:6px;
    overflow:hidden;
    margin-bottom:16px;
}

.run-section-title {
    margin:0;
    padding:14px 16px;
    background:linear-gradient(135deg,#0f172a,#1e3a8a);
    color:#fff;
    font-size:18px;
}

.run-subsection {
    margin: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
}

.run-subsection-head {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

.run-subsection-name {
    font-weight: 700;
}

.run-subsection-count {
    color: #6b7785;
    white-space: nowrap;
}

.run-detail-table {
    margin-bottom: 0;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #e5e7eb;
}

.run-detail-table th {
    background:#eef4fb;
    color:#334155;
    border-bottom:1px solid #e5e7eb !important;
    font-size:11px;
    letter-spacing:.04em;
    text-transform:uppercase;
}

.run-detail-table td,
.run-detail-table th {
    word-break: break-word;
    vertical-align: top;
    padding: 10px 12px !important;
    border-width: 1px !important;
    border-color: #e5e7eb !important;
}

.run-detail-table tbody tr:nth-child(odd) td {
    background: #fcfdff;
}

.run-detail-table tbody tr:hover td {
    background: #f5f9ff;
}

.run-detail-id {
    color:#64748b;
    font-weight:700;
}

.run-detail-metric-name {
    font-weight:700;
    color:#0f172a;
    margin-bottom:4px;
}

.run-detail-type-badge,
.run-detail-source-badge,
.run-detail-radical-badge {
    display:inline-block;
    padding:3px 8px;
    border-radius:999px;
    font-size:11px;
    line-height:1.2;
    border:1px solid transparent;
}

.run-detail-type-badge {
    background:#ede9fe;
    color:#6d28d9;
    border-color:#ddd6fe;
    font-weight:700;
}

.run-detail-source-badge {
    background:#ecfeff;
    color:#0f766e;
    border-color:#bae6fd;
}

.run-detail-radical-badge {
    background:#fff7ed;
    color:#b45309;
    border-color:#fed7aa;
}

.run-detail-value-cell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.run-detail-value-wrap {
    max-width: 100%;
    overflow: auto;
}

.run-detail-mono {
    font-family: monospace;
    font-size: 12px;
}

.run-detail-empty {
    padding: 16px;
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    color: #6b7785;
    background: #fcfdfe;
}

.run-detail-digest {
    margin-top: 6px;
    padding: 8px 10px;
    background: #f8fafc;
    border-left: 3px solid #94a3b8;
    font-family: monospace;
    font-size: 12px;
    white-space: pre-wrap;
}

.run-detail-json {
    font-family: monospace;
    font-size: 12px;
    line-height: 1.45;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:4px;
    padding:8px 10px;
}

.run-detail-json-invalid {
    white-space: pre-wrap;
    background:#fff7ed;
    border:1px solid #fed7aa;
    border-radius:4px;
    padding:8px 10px;
}

.run-json-node {
    margin: 2px 0 2px 0;
}

.run-json-node > summary {
    cursor: pointer;
    list-style: none;
    outline: none;
    color: #334155;
    font-weight: 700;
}

.run-json-node > summary::-webkit-details-marker {
    display: none;
}

.run-json-node > summary::before {
    content: "+";
    display: inline-block;
    width: 14px;
    color: #0f766e;
}

.run-json-node[open] > summary::before {
    content: "-";
}

.run-json-list {
    list-style: none;
    margin: 6px 0 0 18px;
    padding: 0;
    border-left: 1px dashed #cbd5e1;
}

.run-json-item {
    margin: 4px 0;
    padding-left: 10px;
}

.run-json-key {
    color: #b45309;
}

.run-json-sep,
.run-json-bracket,
.run-json-empty {
    color: #667788;
}

.run-json-string {
    color: #0f766e;
}

.run-json-number {
    color: #2563eb;
}

.run-json-bool {
    color: #7c3aed;
}

.run-json-null {
    color: #999;
    font-style: italic;
}
</style>

<div class="run-detail-shell">
    <div class="run-detail-header">
        <h1 class="run-detail-title"><?= htmlspecialchars($serverLabel, ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="run-detail-meta">
            <span class="run-detail-chip">Run date: <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="run-detail-chip">Server ID: <?= $serverId ?></span>
            <?php if ($serverEndpoint !== ''): ?>
                <span class="run-detail-chip">Endpoint: <?= htmlspecialchars($serverEndpoint, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <span class="run-detail-chip">Metrics: <?= (int) ($data['total_metrics'] ?? 0) ?></span>
        </div>
    </div>

    <div class="row" style="margin:0 -8px 0 -8px;">
        <div class="col-md-3 run-detail-fifth" style="padding:0 8px;">
            <div class="run-detail-summary-card" style="border-left-color:#0f766e;">
                <div class="run-detail-summary-label">Server ID</div>
                <div class="run-detail-summary-value"><?= $serverId ?></div>
            </div>
        </div>
        <div class="col-md-3 run-detail-fifth" style="padding:0 8px;">
            <div class="run-detail-summary-card" style="border-left-color:#2563eb;">
                <div class="run-detail-summary-label">Metrics</div>
                <div class="run-detail-summary-value"><?= (int) ($data['total_metrics'] ?? 0) ?></div>
            </div>
        </div>
        <div class="col-md-3 run-detail-fifth" style="padding:0 8px;">
            <div class="run-detail-summary-card" style="border-left-color:#7c3aed;">
                <div class="run-detail-summary-label">Sections</div>
                <div class="run-detail-summary-value"><?= count($sections) ?></div>
            </div>
        </div>
        <div class="col-md-3 run-detail-fifth" style="padding:0 8px;">
            <div class="run-detail-summary-card" style="border-left-color:#b45309;">
                <div class="run-detail-summary-label">Collected Files</div>
                <div class="run-detail-summary-value"><?= count($files) ?></div>
            </div>
        </div>
        <div class="col-md-3 run-detail-fifth" style="padding:0 8px;">
            <div class="run-detail-summary-card" style="border-left-color:#0ea5e9;">
                <div class="run-detail-summary-label">Sources</div>
                <div class="run-detail-summary-value"><?= $sourceCount ?></div>
            </div>
        </div>
    </div>

    <div class="row" style="margin:0 -8px 0 -8px;">
        <div class="col-md-4" style="padding:0 8px;">
            <div class="run-detail-panel">
                <div class="run-detail-panel-head">Run Runtime</div>
                <div class="run-detail-panel-body">
                    <div><b>Display name</b>: <?= htmlspecialchars($serverLabel, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if ($serverEndpoint !== ''): ?>
                        <div><b>Endpoint</b>: <?= htmlspecialchars($serverEndpoint, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <div><b>Run date</b>: <?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></div>
                    <div><b>Total metrics</b>: <?= (int) ($data['total_metrics'] ?? 0) ?></div>
                    <div style="margin-top:10px;">
                        <a class="btn btn-default btn-sm" href="<?= LINK ?>MysqlServer/main/<?= $serverId ?>">Back to MysqlServer main</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8" style="padding:0 8px;">
            <div class="run-detail-panel">
                <div class="run-detail-panel-head">Collected Files</div>
                <div class="run-detail-panel-body">
                    <?php if (!empty($files)): ?>
                        <ul style="margin:0;padding-left:18px;">
                            <?php foreach ($files as $file): ?>
                                <li style="margin-bottom:4px;word-break:break-word;"><?= htmlspecialchars((string) $file, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="run-detail-empty">No `ts_file` entry was found for this run.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($sections)): ?>
        <div class="run-detail-empty">No metrics were found for this server and this run date.</div>
    <?php endif; ?>

    <?php foreach ($sections as $sectionName => $groups): ?>
        <div class="run-section">
            <h2 class="run-section-title"><?= htmlspecialchars((string) $sectionName, ENT_QUOTES, 'UTF-8') ?></h2>

            <?php foreach ($groups as $groupName => $metrics): ?>
                <div class="run-subsection">
                    <div class="run-subsection-head">
                        <div class="run-subsection-name"><?= htmlspecialchars((string) $groupName, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="run-subsection-count"><?= count($metrics) ?> metrics</div>
                    </div>

                    <table class="table table-condensed table-bordered table-striped run-detail-table">
                        <thead>
                            <tr>
                                <th style="width:7%">ID</th>
                                <th style="width:21%">Metric</th>
                                <th style="width:10%">Type</th>
                                <th style="width:17%">Source</th>
                                <th style="width:15%">Radical</th>
                                <th style="width:30%">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metrics as $metric): ?>
                                <tr>
                                    <td class="run-detail-mono run-detail-id"><?= (int) ($metric['id_ts_variable'] ?? 0) ?></td>
                                    <td>
                                        <div class="run-detail-metric-name"><?= htmlspecialchars((string) ($metric['metric_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td>
                                        <span class="run-detail-type-badge"><?= htmlspecialchars((string) ($metric['metric_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                    <td>
                                        <span class="run-detail-source-badge"><?= htmlspecialchars((string) ($metric['metric_source'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                    <td>
                                        <span class="run-detail-radical-badge"><?= htmlspecialchars((string) ($metric['radical'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                    <td class="run-detail-mono run-detail-value-cell">
                                        <div class="run-detail-value-wrap">
                                            <?= run_detail_render_metric_value($metric) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
