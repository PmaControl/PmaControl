<?php

$range = $data['range'] ?? [
    'preset' => '1h',
    'range_mode' => 'preset',
    'start_value' => '',
    'end_value' => '',
];

echo '<style>
.server-state-page { padding:12px 10px 0 10px; }
.server-state-summary-row { display:flex; flex-wrap:nowrap; gap:12px; margin-bottom:12px; }
.server-state-summary-col { flex:1 1 0; min-width:0; }
.server-state-table { table-layout: fixed; margin-bottom:0; background:#fff; }
.server-state-table th:nth-child(1) { width: 280px; }
.server-state-table th:nth-child(2) { width: 140px; }
.server-state-table th:nth-child(3) { width: 100px; }
.server-state-table thead th { background:#f8fafc; color:#334155; border-bottom:1px solid #d8e1ea; }
.server-state-chart-wrap { width: 100%; height: 28px; }
.server-state-chart-wrap canvas { display:block; width:100% !important; height:28px !important; }
.server-state-status { display:inline-block; min-width:60px; font-weight:700; text-align:center; }
.server-state-status.up { color:#2ca25f; }
.server-state-status.readonly { color:#2563eb; }
.server-state-status.down { color:#de2d26; }
.server-state-status.na { color:#7f7f7f; }
#server-state-root .loading { color:#666; padding:16px 0; }
.server-state-summary-card { background:#fff;border:1px solid #d7dde6;border-left:5px solid #2563eb;border-radius:4px;padding:12px 14px;min-height:86px;margin-bottom:12px; }
.server-state-summary-label { color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.04em; }
.server-state-summary-value { font-size:28px;font-weight:700;line-height:1.2; }
.server-state-panel { background:#fff;border:1px solid #cfd8e3;border-radius:6px;overflow:hidden;margin-top:16px; }
.server-state-panel-head { background:linear-gradient(135deg,#0f172a,#1e3a8a);color:#fff;padding:16px 18px; }
.server-state-panel-title { font-size:22px;font-weight:700; }
.server-state-panel-subtitle { opacity:.85;margin-top:4px; }
.server-state-panel-body { padding:16px 12px 8px 12px; }
.server-state-filter-box { margin:12px 10px 0 10px; }
@media (max-width: 1200px) {
    .server-state-summary-row { flex-wrap:wrap; }
    .server-state-summary-col { flex:1 1 calc(50% - 12px); }
}
@media (max-width: 768px) {
    .server-state-summary-col { flex:1 1 100%; }
}
</style>';

echo '<div class="well server-state-filter-box">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

echo '<div class="server-state-page">';
echo '<div class="well" style="margin-top:16px;">';
echo '<form method="get" class="form-inline" id="server-state-range-form">';
echo '<div class="form-group" style="margin-right:10px">';
echo '<label for="range" style="margin-right:6px">Preset</label>';
echo '<select id="range" name="range" class="form-control">';
foreach (['1h' => '1 hour', '6h' => '6 hours', '24h' => '24 hours'] as $value => $label) {
    $selected = (($range['preset'] ?? '1h') === $value) ? ' selected="selected"' : '';
    echo '<option value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'"'.$selected.'>'.$label.'</option>';
}
echo '</select>';
echo '</div>';
echo '<div class="form-group" style="margin-right:10px">';
echo '<label for="start" style="margin-right:6px">Start</label>';
echo '<input id="start" type="datetime-local" name="start" class="form-control" value="'.htmlspecialchars((string) ($range['start_value'] ?? ''), ENT_QUOTES, 'UTF-8').'">';
echo '</div>';
echo '<div class="form-group" style="margin-right:10px">';
echo '<label for="end" style="margin-right:6px">End</label>';
echo '<input id="end" type="datetime-local" name="end" class="form-control" value="'.htmlspecialchars((string) ($range['end_value'] ?? ''), ENT_QUOTES, 'UTF-8').'">';
echo '</div>';
echo '<input type="hidden" id="server-state-range-mode" name="range_mode" value="'.htmlspecialchars((string) ($range['range_mode'] ?? 'preset'), ENT_QUOTES, 'UTF-8').'">';
echo '<button type="submit" class="btn btn-primary">Apply</button>';
echo '<span class="help-block" style="display:inline-block; margin-left:12px">Custom range is limited to 24 hours max.</span>';
echo '</form>';
echo '</div>';
echo '<div id="server-state-root"><div class="loading">Loading...</div></div>';
echo '</div>';
echo '<div class="panel panel-default" style="margin-top:16px;">';
echo '<div class="panel-heading"><strong>How It Works</strong></div>';
echo '<div class="panel-body">';
echo '<ul style="margin-bottom:0;">';
echo '<li>Each vertical bar represents one 10-second bucket.</li>';
echo '<li>The live screen refreshes every 5 seconds.</li>';
echo '<li>To avoid reading a bucket that is still being filled, the live window is shifted by 5 seconds before choosing the current bucket.</li>';
echo '<li>If at least one <code>mysql_available = 0</code> is present inside a 10-second bucket, that bucket is rendered in red.</li>';
echo '<li>If no <code>0</code> exists and at least one <code>mysql_available = 2</code> exists, that bucket is rendered in blue and interpreted as <code>READ ONLY</code>.</li>';
echo '<li>If no <code>0</code> or <code>2</code> exists and at least one <code>mysql_available = 1</code> exists, that bucket is rendered in green.</li>';
echo '<li>If no value is collected during the whole 10-second bucket, that bucket is rendered in grey.</li>';
echo '<li>When the latest bucket is still missing but the current status is already known, the screen uses the current status for that last bucket to avoid a false grey segment caused by ingestion lag.</li>';
echo '<li>Preset ranges stay live. Custom date ranges are static and limited to 24 hours.</li>';
echo '</ul>';
echo '</div>';
echo '</div>';
echo '<script>
(function () {
    var mode = document.getElementById("server-state-range-mode");
    var preset = document.getElementById("range");
    var start = document.getElementById("start");
    var end = document.getElementById("end");

    if (!mode || !preset || !start || !end) {
        return;
    }

    preset.addEventListener("change", function () {
        mode.value = "preset";
    });

    start.addEventListener("change", function () {
        mode.value = "custom";
    });

    end.addEventListener("change", function () {
        mode.value = "custom";
    });
})();
</script>';
