<?php

use App\Library\Display;
use Glial\Synapse\FactoryController;

$serverId = (int)($id_mysql_server ?? 0);
$serverLabel = Display::srv($serverId, true, LINK . 'MysqlServer/main/' . $serverId . '/');
$totalRows = (int)($summary['total_rows'] ?? 0);
$firstSeen = (string)($summary['min_event_time'] ?? '');
$lastSeen = (string)($summary['max_event_time'] ?? '');
$activeLabel = (string)($tabs[$current_type] ?? $current_type);
$currentSource = (string)($current_source ?? '');
$chartPayloadJson = json_encode($chart_payload ?? [], JSON_UNESCAPED_SLASHES);
$initialLinesPayload = is_array($initial_lines_payload ?? null) ? $initial_lines_payload : [];
$initialLines = is_array($initialLinesPayload['lines'] ?? null) ? $initialLinesPayload['lines'] : [];
$initialPage = max(1, (int)($initialLinesPayload['page'] ?? 1));
$initialTotalPages = max(1, (int)($initialLinesPayload['total_pages'] ?? 1));
$initialPageSize = max(1, (int)($initialLinesPayload['page_size'] ?? 100));
$pageNumbers = [];
for ($page = 1; $page <= min(10, $initialTotalPages); $page++) {
    $pageNumbers[$page] = true;
}
for ($page = max(1, $initialPage - 10); $page <= min($initialTotalPages, $initialPage + 10); $page++) {
    $pageNumbers[$page] = true;
}
for ($page = max(1, $initialTotalPages - 4); $page <= $initialTotalPages; $page++) {
    $pageNumbers[$page] = true;
}
$pageNumbers = array_keys($pageNumbers);
sort($pageNumbers, SORT_NUMERIC);
FactoryController::addNode("MysqlServer", "menu", [$serverId]);
?>

<script type="text/javascript">
window.mysqlServerLogsChartPayload = <?php echo $chartPayloadJson !== false ? $chartPayloadJson : '{}'; ?>;
if (typeof console !== 'undefined' && typeof console.log === 'function') {
    console.log('[MysqlServer/logs]', 'inline payload ready', window.mysqlServerLogsChartPayload);
}
</script>

<style>
.mysql-logs-shell {
    padding: 12px 10px 24px 0;
}

.mysql-logs-header {
    margin: 0 0 16px 0;
    padding: 16px 18px;
    border-radius: 6px;
    background: linear-gradient(135deg,#0f172a,#1e3a8a);
    color: #fff;
}

.mysql-logs-title {
    margin: 0 0 8px 0;
    font-size: 22px;
    line-height: 1.2;
}

.mysql-logs-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 0;
}

.mysql-logs-chip {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
    font-size: 12px;
}

.mysql-logs-summary-card {
    background:#fff;
    border:1px solid #d7dde6;
    border-left:5px solid #2563eb;
    border-radius:4px;
    padding:12px 14px;
    min-height:86px;
    margin-bottom: 16px;
}

.mysql-logs-summary-label {
    color:#6b7280;
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.mysql-logs-summary-value {
    font-size:28px;
    font-weight:700;
    line-height:1.2;
}

.mysql-logs-panel {
    background:#fff;
    border:1px solid #cfd8e3;
    border-radius:6px;
    overflow:hidden;
    margin-bottom:16px;
}

.mysql-logs-panel-head {
    background:#f8fafc;
    border-bottom:1px solid #e5e7eb;
    padding:10px 12px;
    font-weight:700;
}

.mysql-logs-panel-body {
    padding:12px;
}

.mysql-logs-tabs {
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin:0;
    padding:0;
    list-style:none;
}

.mysql-logs-tabs li a {
    display:inline-block;
    padding:8px 12px;
    border-radius:999px;
    background:#eef4fb;
    color:#1f2937;
    text-decoration:none;
    border:1px solid #dbe7f4;
}

.mysql-logs-tabs li.active a {
    background:#1d4ed8;
    border-color:#1d4ed8;
    color:#fff;
}

.mysql-logs-badge {
    display:inline-block;
    margin-left:6px;
    padding:2px 8px;
    border-radius:999px;
    background:rgba(255,255,255,.18);
    font-size:11px;
}

.mysql-logs-tabs li:not(.active) .mysql-logs-badge {
    background:#dbeafe;
}

.mysql-logs-table {
    margin-bottom:0;
    table-layout:fixed;
    border-collapse:separate;
    border-spacing:0;
    border:1px solid #e5e7eb;
}

.mysql-logs-table th {
    background:#eef4fb;
    color:#334155;
    border-bottom:1px solid #e5e7eb !important;
    font-size:11px;
    letter-spacing:.04em;
    text-transform:uppercase;
}

.mysql-logs-table td,
.mysql-logs-table th {
    word-break: break-word;
    vertical-align: top;
    padding: 2px !important;
    border-width: 1px !important;
    border-color: #e5e7eb !important;
}

.mysql-logs-table tbody tr:nth-child(odd) td {
    background:#fcfdff;
}

.mysql-logs-muted {
    color:#6b7280;
}

.mysql-logs-level-badge {
    display:inline-block;
    padding:3px 8px;
    border-radius:999px;
    font-size:11px;
    line-height:1.2;
    border:1px solid transparent;
    font-weight:700;
}

.mysql-logs-level-error {
    background:#fee2e2;
    color:#b91c1c;
    border-color:#fecaca;
}

.mysql-logs-level-warning {
    background:#fef3c7;
    color:#b45309;
    border-color:#fde68a;
}

.mysql-logs-level-default {
    background:#e0f2fe;
    color:#0369a1;
    border-color:#bae6fd;
}

.mysql-logs-pagination {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:12px;
}

.mysql-logs-pagination-actions {
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}

.mysql-logs-page-link.active {
    background:#1d4ed8;
    border-color:#1d4ed8;
    color:#fff;
    pointer-events:none;
}

.mysql-logs-page-gap {
    display:inline-flex;
    align-items:center;
    color:#6b7280;
    padding:0 4px;
}

.mysql-logs-chart-wrap {
    position: relative;
    width: 100%;
    height: 280px;
}

.mysql-logs-chart-toolbar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:10px;
}
</style>

<div class="mysql-logs-shell">
    <div class="mysql-logs-header">
        <h1 class="mysql-logs-title"><?php echo __('Logs'); ?></h1>
        <div class="mysql-logs-meta">
            <span class="mysql-logs-chip"><?php echo $serverLabel; ?></span>
            <span class="mysql-logs-chip"><?php echo __('Active tab'); ?>: <?php echo htmlspecialchars($activeLabel, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>

    <div class="mysql-logs-panel">
        <div class="mysql-logs-panel-head"><?php echo __('Log Types'); ?></div>
        <div class="mysql-logs-panel-body">
            <ul class="mysql-logs-tabs">
                <?php foreach ($tabs as $type => $label) : ?>
                    <li class="<?php echo $type === $current_type ? 'active' : ''; ?>">
                        <a href="<?php echo LINK . 'MysqlServer/logs/' . $serverId . '/' . $type . '/'; ?>">
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ($type === $current_type && $currentSource !== '') : ?>
                                <small style="opacity:.85"><?php echo htmlspecialchars($currentSource, ENT_QUOTES, 'UTF-8'); ?></small>
                            <?php endif; ?>
                            <span class="mysql-logs-badge"><?php echo (int)($counts[$type] ?? 0); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="mysql-logs-panel">
        <div class="mysql-logs-panel-head"><?php echo __('Monthly Activity'); ?></div>
        <div class="mysql-logs-panel-body">
            <div class="mysql-logs-chart-toolbar">
                <div class="mysql-logs-muted"><?php echo __('One bar per day on the last 30 days. Click a day for hourly details, then click an hour for minutes.'); ?></div>
                <button type="button" id="mysql-logs-chart-reset" class="btn btn-default btn-sm"><?php echo __('Back to month'); ?></button>
            </div>
            <div class="mysql-logs-chart-wrap">
                <canvas id="mysql-logs-chart"></canvas>
            </div>
        </div>
    </div>

    <div class="mysql-logs-panel">
        <div class="mysql-logs-panel-head"><?php echo __('Log Lines'); ?></div>
        <div class="mysql-logs-panel-body">
            <div id="mysql-logs-lines-state" class="mysql-logs-muted" style="margin-bottom:10px;">
                <?php echo __('Scope'); ?>: month / <?php echo __('Rows'); ?>: <?php echo (int)($initialLinesPayload['total_rows'] ?? 0); ?>
            </div>
            <div id="mysql-logs-lines-pagination-top" class="mysql-logs-pagination">
                <div class="mysql-logs-muted"><?php echo __('Page'); ?> <?php echo $initialPage; ?> / <?php echo $initialTotalPages; ?></div>
                <div class="mysql-logs-pagination-actions">
                    <?php if ($initialPage > 1) : ?>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="1">First</a>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="<?php echo $initialPage - 1; ?>">Previous</a>
                    <?php endif; ?>
                    <?php $previousPage = null; ?>
                    <?php foreach ($pageNumbers as $page) : ?>
                        <?php if ($previousPage !== null && $page > ($previousPage + 1)) : ?>
                            <span class="mysql-logs-page-gap">...</span>
                        <?php endif; ?>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link <?php echo $page === $initialPage ? 'active' : ''; ?>" data-page="<?php echo $page; ?>"><?php echo $page; ?></a>
                        <?php $previousPage = $page; ?>
                    <?php endforeach; ?>
                    <?php if ($initialPage < $initialTotalPages) : ?>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="<?php echo $initialPage + 1; ?>">Next</a>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="<?php echo $initialTotalPages; ?>">Last</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mysql-logs-table">
                    <thead>
                        <tr>
                            <th style="width:70px;">#</th>
                            <th style="width:160px;"><?php echo __('Date'); ?></th>
                            <th style="width:120px;"><?php echo __('Level'); ?></th>
                            <th style="width:90px;"><?php echo __('Code'); ?></th>
                            <th style="width:120px;"><?php echo __('Process'); ?></th>
                            <th style="width:120px;"><?php echo __('User'); ?></th>
                            <th style="width:140px;"><?php echo __('Host'); ?></th>
                            <th style="width:120px;"><?php echo __('DB'); ?></th>
                            <th><?php echo __('Message'); ?></th>
                            <th><?php echo __('Raw'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="mysql-logs-lines-body">
                        <?php if (empty($initialLines)) : ?>
                            <tr>
                                <td colspan="10" class="text-center mysql-logs-muted"><?php echo __('No log line collected yet'); ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($initialLines as $index => $line) : ?>
                                <?php
                                $level = strtoupper((string)($line['level'] ?? ''));
                                $levelClass = 'mysql-logs-level-default';
                                if ($level === 'ERROR' || $level === 'OOM') {
                                    $levelClass = 'mysql-logs-level-error';
                                } elseif ($level === 'WARNING' || $level === 'WARN') {
                                    $levelClass = 'mysql-logs-level-warning';
                                }
                                $lineNumber = (($initialPage - 1) * $initialPageSize) + $index + 1;
                                ?>
                                <tr>
                                    <td><?php echo $lineNumber; ?></td>
                                    <td><?php echo htmlspecialchars((string)($line['event_time'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="mysql-logs-level-badge <?php echo $levelClass; ?>"><?php echo htmlspecialchars((string)($line['level'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><?php echo htmlspecialchars((string)($line['error_code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($line['process_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($line['user_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($line['host_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($line['db_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="white-space: pre-wrap; word-break: break-word;"><?php echo htmlspecialchars((string)($line['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="white-space: pre-wrap; word-break: break-word;"><?php echo htmlspecialchars((string)($line['raw_line'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div id="mysql-logs-lines-pagination-bottom" class="mysql-logs-pagination" style="margin-top:12px; margin-bottom:0;">
                <div class="mysql-logs-muted"><?php echo __('Page'); ?> <?php echo $initialPage; ?> / <?php echo $initialTotalPages; ?></div>
                <div class="mysql-logs-pagination-actions">
                    <?php if ($initialPage > 1) : ?>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="1">First</a>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="<?php echo $initialPage - 1; ?>">Previous</a>
                    <?php endif; ?>
                    <?php $previousPage = null; ?>
                    <?php foreach ($pageNumbers as $page) : ?>
                        <?php if ($previousPage !== null && $page > ($previousPage + 1)) : ?>
                            <span class="mysql-logs-page-gap">...</span>
                        <?php endif; ?>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link <?php echo $page === $initialPage ? 'active' : ''; ?>" data-page="<?php echo $page; ?>"><?php echo $page; ?></a>
                        <?php $previousPage = $page; ?>
                    <?php endforeach; ?>
                    <?php if ($initialPage < $initialTotalPages) : ?>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="<?php echo $initialPage + 1; ?>">Next</a>
                        <a href="#" class="btn btn-default btn-sm mysql-logs-page-link" data-page="<?php echo $initialTotalPages; ?>">Last</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
