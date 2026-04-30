<?php

use App\Library\Display;
use Glial\Synapse\FactoryController;

$payload = $payload ?? [];
$dashboard = $payload['dashboard'] ?? [];
$server = $payload['server'] ?? [];
$summaryCards = $payload['summary_cards'] ?? [];
$sections = $payload['sections'] ?? [];
$range = $payload['range'] ?? [];
$serverId = (int)($server['id'] ?? 0);

FactoryController::addNode('Pmm', 'menu', [$serverId, (string)($dashboard['slug'] ?? 'overview')]);
?>

<style>
.pmm-shell { padding: 12px 0 24px 0; }
.pmm-header {
    margin: 0 0 16px 0;
    padding: 18px 20px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0f172a, #1d4ed8);
    color: #fff;
}
.pmm-title { margin: 0 0 8px 0; font-size: 24px; line-height: 1.2; }
.pmm-subtitle { opacity: .88; max-width: 980px; }
.pmm-meta { display:flex; flex-wrap:wrap; gap:10px; margin-top:12px; }
.pmm-chip {
    display:inline-block; padding:6px 10px; border-radius:999px;
    background: rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.18); font-size:12px;
}
.pmm-range { background:#fff; border:1px solid #d7dde6; border-radius:6px; padding:12px 14px; margin-bottom:16px; }
.pmm-summary-grid { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
.pmm-summary-item { flex:1 1 calc(20% - 12px); min-width:180px; }
.pmm-card {
    background:#fff; border:1px solid #d7dde6; border-left:5px solid #2563eb; border-radius:4px;
    padding:12px 14px; min-height:90px; margin-bottom:16px;
}
.pmm-card-link {
    display:block; color:inherit; text-decoration:none;
}
.pmm-card-link:hover,
.pmm-card-link:focus {
    color:inherit; text-decoration:none;
}
.pmm-card-link .pmm-card {
    margin-bottom:0;
}
.pmm-card-link:hover .pmm-card {
    border-color:#93c5fd;
    box-shadow:0 6px 18px rgba(37, 99, 235, .10);
}
.pmm-card-label { color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
.pmm-card-value { font-size:26px; font-weight:700; line-height:1.2; }
.pmm-card-note { margin-top:8px; color:#64748b; font-size:12px; }
.pmm-section { background:#fff; border:1px solid #cfd8e3; border-radius:6px; overflow:hidden; margin-bottom:16px; }
.pmm-section-head { padding:14px 16px; background:linear-gradient(135deg,#0f172a,#1e3a8a); color:#fff; }
.pmm-section-title { margin:0; font-size:18px; }
.pmm-section-desc { margin-top:6px; opacity:.86; }
.pmm-section-body { padding:14px; }
.pmm-chart-card, .pmm-table-card {
    border:1px solid #e5e7eb; border-radius:6px; background:#fff; overflow:hidden; margin-bottom:14px;
}
.pmm-chart-head, .pmm-table-head {
    padding:10px 12px; background:#f8fafc; border-bottom:1px solid #e5e7eb;
}
.pmm-chart-title { margin:0; font-weight:700; }
.pmm-chart-meta { margin-top:6px; font-size:12px; color:#64748b; }
.pmm-chart-body { padding:12px; }
.pmm-table-head strong { display:block; }
.pmm-notes { margin:0; padding-left:18px; color:#475569; }
.pmm-table { margin-bottom:0; }
.pmm-table th { background:#eef4fb; color:#334155; font-size:11px; text-transform:uppercase; letter-spacing:.04em; }
.pmm-table td, .pmm-table th { padding:8px 10px !important; border-color:#e5e7eb !important; vertical-align:top; }
</style>

<div class="pmm-shell">
    <div class="pmm-header">
        <h2 class="pmm-title"><?php echo htmlspecialchars((string)($dashboard['title'] ?? 'PMM'), ENT_QUOTES, 'UTF-8'); ?></h2>
        <div class="pmm-subtitle"><?php echo htmlspecialchars((string)($dashboard['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="pmm-meta">
            <span class="pmm-chip">Server: <?php echo Display::srv($serverId, true); ?></span>
            <span class="pmm-chip">PMM dashboard: <?php echo htmlspecialchars((string)($dashboard['pmm_dashboard'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="pmm-chip">Source: <?php echo htmlspecialchars((string)($dashboard['pmm_source'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>

    <div class="pmm-range">
        <form method="get" class="form-inline" id="pmm-range-form">
            <div class="form-group" style="margin-right:10px">
                <label for="range" style="margin-right:6px">Preset</label>
                <select id="range" name="range" class="form-control">
                <?php foreach (['1h' => '1 hour', '6h' => '6 hours', '24h' => '24 hours'] as $value => $label) : ?>
                    <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"<?php echo (($range['preset'] ?? '24h') === $value) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-right:10px">
                <label for="start" style="margin-right:6px">Start</label>
                <input id="start" type="datetime-local" name="start" class="form-control" value="<?php echo htmlspecialchars((string)($range['start_value'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form-group" style="margin-right:10px">
                <label for="end" style="margin-right:6px">End</label>
                <input id="end" type="datetime-local" name="end" class="form-control" value="<?php echo htmlspecialchars((string)($range['end_value'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <input type="hidden" id="pmm-range-mode" name="range_mode" value="<?php echo htmlspecialchars((string)($range['range_mode'] ?? 'preset'), ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn btn-primary">Apply</button>
            <span class="help-block" style="display:inline-block; margin-left:12px">Custom range is limited to 24 hours max.</span>
        </form>
    </div>

    <?php if (!empty($summaryCards)) : ?>
    <div class="pmm-summary-grid">
        <?php foreach ($summaryCards as $card) : ?>
        <div class="pmm-summary-item">
            <?php if (!empty($card['link_url'])) : ?>
            <a class="pmm-card-link" href="<?php echo htmlspecialchars((string)$card['link_url'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <div class="pmm-card">
                <div class="pmm-card-label"><?php echo htmlspecialchars((string)($card['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="pmm-card-value"><?php echo htmlspecialchars((string)($card['value'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                <?php if (!empty($card['note'])) : ?>
                <div class="pmm-card-note"><?php echo htmlspecialchars((string)$card['note'], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
            <?php if (!empty($card['link_url'])) : ?>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php foreach ($sections as $section) : ?>
    <div class="pmm-section">
        <div class="pmm-section-head">
            <h3 class="pmm-section-title"><?php echo htmlspecialchars((string)($section['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
            <?php if (!empty($section['description'])) : ?>
            <div class="pmm-section-desc"><?php echo htmlspecialchars((string)$section['description'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
        </div>
        <div class="pmm-section-body">
            <?php if (!empty($section['cards'])) : ?>
            <div class="row">
                <?php foreach ($section['cards'] as $card) : ?>
                <div class="col-md-4 col-lg-3">
                    <div class="pmm-card">
                        <div class="pmm-card-label"><?php echo htmlspecialchars((string)($card['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="pmm-card-value"><?php echo htmlspecialchars((string)($card['value'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="pmm-card-note">
                            <?php echo htmlspecialchars((string)($card['source'] ?? ''), ENT_QUOTES, 'UTF-8'); ?><br>
                            <?php echo htmlspecialchars((string)($card['pmm_equivalent'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php foreach (($section['charts'] ?? []) as $chart) : ?>
            <div class="pmm-chart-card">
                <div class="pmm-chart-head">
                    <div class="pmm-chart-title"><?php echo htmlspecialchars((string)($chart['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="pmm-chart-meta">
                        PMM panel: <?php echo htmlspecialchars((string)($chart['meta']['pmm_panel'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        · Source: <?php echo htmlspecialchars((string)($chart['meta']['pmm_source'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        · PmaControl metrics: <?php echo htmlspecialchars((string)($chart['meta']['equivalent_metrics'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
                <div class="pmm-chart-body">
                    <?php if (empty($chart['labels']) || empty($chart['datasets'])) : ?>
                    <div class="text-muted">No historical series available for this panel on the selected range.</div>
                    <?php else : ?>
                    <div style="height:320px;">
                        <canvas class="js-pmm-chart" data-chart-id="<?php echo htmlspecialchars((string)($chart['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php foreach (($section['tables'] ?? []) as $table) : ?>
            <div class="pmm-table-card">
                <div class="pmm-table-head"><strong><?php echo htmlspecialchars((string)($table['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="table-responsive">
                    <table class="table table-condensed table-bordered table-striped pmm-table">
                        <thead>
                            <tr>
                            <?php foreach (($table['columns'] ?? []) as $column) : ?>
                                <th><?php echo htmlspecialchars((string)$column, ENT_QUOTES, 'UTF-8'); ?></th>
                            <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($table['rows'])) : ?>
                            <tr><td colspan="<?php echo max(1, count($table['columns'] ?? [])); ?>" class="text-muted">No data available.</td></tr>
                        <?php else : ?>
                            <?php foreach ($table['rows'] as $row) : ?>
                            <tr>
                                <?php foreach ($row as $value) : ?>
                                <td><?php echo is_string($value) && strpos($value, '<a ') !== false ? $value : htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (!empty($section['notes'])) : ?>
            <ul class="pmm-notes">
                <?php foreach ($section['notes'] as $note) : ?>
                <li><?php echo htmlspecialchars((string)$note, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
window.pmmDashboardPayload = <?php
echo json_encode(
    [
        'dashboard' => [
            'slug' => $dashboard['slug'] ?? '',
            'title' => $dashboard['title'] ?? '',
        ],
        'sections' => array_map(static function (array $section): array {
            return [
                'title' => $section['title'] ?? '',
                'charts' => $section['charts'] ?? [],
            ];
        }, $sections),
    ],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
?>;

(function () {
    var mode = document.getElementById('pmm-range-mode');
    var preset = document.getElementById('range');
    var start = document.getElementById('start');
    var end = document.getElementById('end');

    if (!mode || !preset || !start || !end) {
        return;
    }

    preset.addEventListener('change', function () {
        mode.value = 'preset';
    });

    start.addEventListener('change', function () {
        mode.value = 'custom';
    });

    end.addEventListener('change', function () {
        mode.value = 'custom';
    });
})();
</script>
