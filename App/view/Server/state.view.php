<?php

$range = $data['range'] ?? [
    'preset' => '1h',
    'range_mode' => 'preset',
    'start_value' => '',
    'end_value' => '',
];

echo '<style>
.server-state-table { table-layout: fixed; }
.server-state-table th:nth-child(1) { width: 280px; }
.server-state-table th:nth-child(2) { width: 140px; }
.server-state-table th:nth-child(3) { width: 100px; }
.server-state-chart-wrap { width: 100%; height: 28px; }
.server-state-chart-wrap canvas { display:block; width:100% !important; height:28px !important; }
.server-state-status { display:inline-block; min-width:60px; font-weight:700; text-align:center; }
.server-state-status.up { color:#2ca25f; }
.server-state-status.down { color:#de2d26; }
.server-state-status.na { color:#7f7f7f; }
#server-state-root .loading { color:#666; padding:16px 0; }
</style>';

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

echo '<h2>'.__('Server State').'</h2>';
echo '<p>'.__('Green = mysql_available=1, Red = mysql_available=0, Grey = no value').'</p>';
echo '<div class="well">';
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
