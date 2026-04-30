<?php

$payload = $payload ?? [];
$reports = $payload['reports'] ?? [];
$selected = $payload['selected_report'] ?? ['slug' => 'overview', 'title' => 'Overview', 'description' => ''];
$sections = $payload['sections'] ?? [];
$summaryCards = $payload['summary_cards'] ?? [];
$warnings = $payload['warnings'] ?? [];
$limit = (int)($payload['limit'] ?? 10);

if (!function_exists('reports_h')) {
    function reports_h($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

?>
<style>
.reports-shell { padding: 12px 10px 24px 10px; color:#1f2933; }
.reports-head { display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:flex-start; background:#fff; border:1px solid #d8e1ea; border-radius:6px; padding:14px 16px; margin-bottom:14px; }
.reports-title { margin:0; font-size:22px; font-weight:700; line-height:1.25; }
.reports-subtitle { margin-top:4px; color:#64748b; max-width:880px; }
.reports-toolbar { display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end; }
.reports-toolbar .form-group { margin-bottom:0; }
.reports-warning { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:8px 10px; border-radius:5px; margin-bottom:12px; }
.reports-grid { display:grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap:10px; margin-bottom:14px; }
.reports-card { background:#fff; border:1px solid #d8e1ea; border-left:4px solid #0f766e; border-radius:6px; min-height:86px; padding:10px 12px; }
.reports-card:nth-child(2n) { border-left-color:#2563eb; }
.reports-card:nth-child(3n) { border-left-color:#f59e0b; }
.reports-card-label { color:#64748b; font-size:11px; font-weight:700; text-transform:uppercase; }
.reports-card-value { margin-top:6px; font-size:20px; font-weight:700; word-break:break-word; }
.reports-card-note { margin-top:6px; color:#64748b; font-size:12px; }
.reports-section { background:#fff; border:1px solid #d8e1ea; border-radius:6px; overflow:hidden; margin-bottom:14px; }
.reports-section-head { padding:12px 14px; background:#f8fafc; border-bottom:1px solid #d8e1ea; }
.reports-section-title { margin:0; font-size:16px; font-weight:700; }
.reports-section-desc { margin-top:4px; color:#64748b; }
.reports-section-body { padding:12px; }
.reports-chart { border:1px solid #e5e7eb; border-radius:6px; margin-bottom:12px; overflow:hidden; }
.reports-chart-head { padding:9px 11px; background:#fbfdff; border-bottom:1px solid #e5e7eb; font-weight:700; }
.reports-chart-body { height:300px; padding:12px; }
.reports-table { margin-bottom:0; font-size:12px; }
.reports-table th { white-space:nowrap; background:#eef4fb; color:#334155; font-size:11px; text-transform:uppercase; }
.reports-table td, .reports-table th { padding:8px 10px !important; vertical-align:top !important; border-color:#e5e7eb !important; }
.reports-notes { margin:0; padding-left:18px; color:#475569; }
.reports-muted { color:#64748b; }
@media (max-width: 1200px) { .reports-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 700px) { .reports-grid { grid-template-columns: 1fr; } .reports-toolbar { width:100%; } .reports-toolbar .form-group { width:100%; } }
</style>

<div class="reports-shell">
    <?php foreach ($warnings as $warning): ?>
        <div class="reports-warning"><?= reports_h($warning) ?></div>
    <?php endforeach; ?>

    <div class="reports-head">
        <div>
            <h2 class="reports-title">Reports - <?= reports_h($selected['title'] ?? 'Overview') ?></h2>
            <div class="reports-subtitle"><?= reports_h($selected['description'] ?? '') ?></div>
            <div class="reports-subtitle">Generated at <?= reports_h($payload['generated_at'] ?? '-') ?></div>
        </div>

        <form method="get" class="reports-toolbar">
            <div class="form-group">
                <label for="reports-report">Report</label>
                <select id="reports-report" name="report" class="form-control">
                    <?php foreach ($reports as $report): ?>
                        <?php $slug = (string)($report['slug'] ?? ''); ?>
                        <option value="<?= reports_h($slug) ?>"<?= $slug === ($selected['slug'] ?? '') ? ' selected="selected"' : '' ?>>
                            <?= reports_h($report['title'] ?? $slug) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="reports-limit">Limit</label>
                <input id="reports-limit" type="number" min="1" max="50" name="limit" class="form-control" value="<?= reports_h($limit) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>
    </div>

    <?php if (!empty($summaryCards)): ?>
    <div class="reports-grid">
        <?php foreach ($summaryCards as $card): ?>
            <div class="reports-card">
                <div class="reports-card-label"><?= reports_h($card['label'] ?? '') ?></div>
                <div class="reports-card-value"><?= reports_h($card['value'] ?? '') ?></div>
                <div class="reports-card-note"><?= reports_h($card['note'] ?? '') ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php foreach ($sections as $section): ?>
        <div class="reports-section">
            <div class="reports-section-head">
                <h3 class="reports-section-title"><?= reports_h($section['title'] ?? '') ?></h3>
                <div class="reports-section-desc"><?= reports_h($section['description'] ?? '') ?></div>
            </div>
            <div class="reports-section-body">
                <?php foreach (($section['charts'] ?? []) as $chart): ?>
                    <div class="reports-chart">
                        <div class="reports-chart-head"><?= reports_h($chart['title'] ?? '') ?></div>
                        <div class="reports-chart-body">
                            <?php if (empty($chart['labels']) || empty($chart['datasets'])): ?>
                                <span class="reports-muted">No chart data available.</span>
                            <?php else: ?>
                                <canvas class="js-reports-chart" data-chart-id="<?= reports_h($chart['id'] ?? '') ?>"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php foreach (($section['tables'] ?? []) as $table): ?>
                    <div class="table-responsive">
                        <table class="table table-condensed table-striped table-bordered reports-table">
                            <caption><?= reports_h($table['title'] ?? '') ?></caption>
                            <thead>
                                <tr>
                                    <?php foreach (($table['columns'] ?? []) as $column): ?>
                                        <th><?= reports_h($column) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($table['rows'])): ?>
                                    <tr><td colspan="<?= max(1, count($table['columns'] ?? [])) ?>" class="reports-muted">No data available.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($table['rows'] as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $value): ?>
                                                <td><?= reports_h($value) ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>

                <?php if (!empty($section['notes'])): ?>
                    <ul class="reports-notes">
                        <?php foreach ($section['notes'] as $note): ?>
                            <li><?= reports_h($note) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
window.reportsPayload = <?php
echo json_encode(
    [
        'selected_report' => [
            'slug' => $selected['slug'] ?? '',
            'title' => $selected['title'] ?? '',
        ],
        'sections' => array_map(static function (array $section): array {
            return [
                'slug' => $section['slug'] ?? '',
                'title' => $section['title'] ?? '',
                'charts' => $section['charts'] ?? [],
            ];
        }, $sections),
    ],
    JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
);
?>;
</script>
