<?php

use App\Library\Display;
use App\Library\EngineMemoryBreakdown;

$idMysqlServer = (int) ($data['id_mysql_server'] ?? 0);
$sections = $data['sections'] ?? [];
$summary = $data['summary'] ?? [];
$recap = $data['recap'] ?? [];
$processMemoryChart = $data['process_memory_chart'] ?? ['labels' => [], 'datasets' => [], 'title' => ''];
$processMemoryRange = $data['process_memory_range'] ?? [
    'preset' => '6h',
    'range_mode' => 'preset',
    'start_value' => '',
    'end_value' => '',
];

echo '<div class="well">';
echo '<strong>Server:</strong> '.Display::srv($idMysqlServer, false);
echo '</div>';

echo '<div class="panel panel-default">';
echo '<div class="panel-heading"><strong>Summary</strong></div>';
echo '<div class="panel-body" style="padding:0">';
echo '<table class="table table-condensed table-striped" style="margin:0">';
echo '<tbody>';
foreach ($summary as $label => $value) {
    echo '<tr>';
    echo '<td style="width:40%">'.$label.'</td>';
    echo '<td style="width:60%; text-align:right">'.htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8').'</td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
echo '</div>';
echo '</div>';

echo '<div class="alert alert-info" role="alert">';
echo 'This screen separates the memory view into storage-engine families. ';
echo 'It mixes native engine counters, configured memory caps and Performance Schema runtime allocations when available.';
echo '</div>';

echo '<div class="well">';
echo '<form method="get" class="form-inline" id="engine-memory-range-form">';
echo '<div class="form-group" style="margin-right:10px">';
echo '<label for="range" style="margin-right:6px">Preset</label>';
echo '<select id="range" name="range" class="form-control">';
foreach (['1h' => '1 hour', '6h' => '6 hours', '24h' => '24 hours'] as $value => $label) {
    $selected = (($processMemoryRange['preset'] ?? '6h') === $value) ? ' selected="selected"' : '';
    echo '<option value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'"'.$selected.'>'.$label.'</option>';
}
echo '</select>';
echo '</div>';
echo '<div class="form-group" style="margin-right:10px">';
echo '<label for="start" style="margin-right:6px">Start</label>';
echo '<input id="start" type="datetime-local" name="start" class="form-control" value="'.htmlspecialchars((string) ($processMemoryRange['start_value'] ?? ''), ENT_QUOTES, 'UTF-8').'">';
echo '</div>';
echo '<div class="form-group" style="margin-right:10px">';
echo '<label for="end" style="margin-right:6px">End</label>';
echo '<input id="end" type="datetime-local" name="end" class="form-control" value="'.htmlspecialchars((string) ($processMemoryRange['end_value'] ?? ''), ENT_QUOTES, 'UTF-8').'">';
echo '</div>';
echo '<input type="hidden" id="engine-memory-range-mode" name="range_mode" value="'.htmlspecialchars((string) ($processMemoryRange['range_mode'] ?? 'preset'), ENT_QUOTES, 'UTF-8').'">';
echo '<button type="submit" class="btn btn-primary">Apply</button>';
echo '<span class="help-block" style="display:inline-block; margin-left:12px">Custom range is limited to 24 hours max.</span>';
echo '</form>';
echo '</div>';
echo '<script>
(function () {
    var mode = document.getElementById("engine-memory-range-mode");
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

echo '<div class="panel panel-default">';
echo '<div class="panel-heading">';
echo '<strong>'.htmlspecialchars((string) ($processMemoryChart['title'] ?? 'Process memory'), ENT_QUOTES, 'UTF-8').'</strong>';
echo '</div>';
echo '<div class="panel-body">';

if (empty($processMemoryChart['labels']) || empty($processMemoryChart['datasets'])) {
    echo '<div class="text-muted">No process memory history available for the last 6 hours.</div>';
} else {
    echo '<div style="height:320px">';
    echo '<canvas id="engine-memory-process-chart"></canvas>';
    echo '</div>';
}

echo '</div>';
echo '</div>';

foreach ($sections as $section) {
    $title = (string) ($section['title'] ?? '');
    $meta = $section['meta'] ?? [];
    $metrics = $section['metrics'] ?? [];
    $events = $section['performance_schema_events'] ?? [];
    $notes = $section['notes'] ?? [];

    echo '<div class="panel panel-default">';
    echo '<div class="panel-heading"><strong>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</strong></div>';
    echo '<div class="panel-body">';

    echo '<table class="table table-condensed table-bordered">';
    echo '<thead><tr><th style="width:25%">Support</th><th style="width:15%">Enabled</th><th style="width:20%">Plugin status</th><th style="width:20%">Plugin version</th><th style="width:20%">P_S memory</th></tr></thead>';
    echo '<tbody><tr>';
    echo '<td>'.htmlspecialchars((string) ($meta['supported'] ?? 'n/a'), ENT_QUOTES, 'UTF-8').'</td>';
    echo '<td>'.(!empty($meta['enabled']) ? 'Yes' : 'No').'</td>';
    echo '<td>'.htmlspecialchars((string) ($meta['plugin_status'] ?? 'n/a'), ENT_QUOTES, 'UTF-8').'</td>';
    echo '<td>'.htmlspecialchars((string) ($meta['plugin_version'] ?? 'n/a'), ENT_QUOTES, 'UTF-8').'</td>';
    echo '<td>'.htmlspecialchars((string) ($meta['performance_schema_memory'] ?? 'n/a'), ENT_QUOTES, 'UTF-8').'</td>';
    echo '</tr></tbody>';
    echo '</table>';

    if (!empty($notes)) {
        echo '<ul>';
        foreach ($notes as $note) {
            echo '<li>'.htmlspecialchars((string) $note, ENT_QUOTES, 'UTF-8').'</li>';
        }
        echo '</ul>';
    }

    echo '<table class="table table-condensed table-striped table-bordered">';
    echo '<thead><tr><th style="width:35%">Metric</th><th style="width:15%">Source</th><th style="width:25%">Value</th><th style="width:25%">Raw value</th></tr></thead>';
    echo '<tbody>';

    if (empty($metrics)) {
        echo '<tr><td colspan="4" class="text-muted">No collected metric for this engine.</td></tr>';
    } else {
        foreach ($metrics as $metric) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars((string) ($metric['name'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
            echo '<td>'.htmlspecialchars((string) ($metric['source'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
            echo '<td>'.htmlspecialchars((string) ($metric['display_value'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
            echo '<td>'.htmlspecialchars((string) ($metric['raw_value_display'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';

    echo '<table class="table table-condensed table-bordered">';
    echo '<thead><tr><th colspan="2">Performance Schema top allocations</th></tr></thead>';
    echo '<tbody>';
    if (empty($events)) {
        echo '<tr><td colspan="2" class="text-muted">No Performance Schema memory event for this engine.</td></tr>';
    } else {
        foreach ($events as $event) {
            echo '<tr>';
            echo '<td style="width:75%">'.htmlspecialchars((string) ($event['event_name'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
            echo '<td style="width:25%; text-align:right">'.htmlspecialchars(EngineMemoryBreakdown::formatBytes($event['bytes'] ?? null), ENT_QUOTES, 'UTF-8').'</td>';
            echo '</tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';

    echo '</div>';
    echo '</div>';
}

echo '<div class="panel panel-primary">';
echo '<div class="panel-heading"><strong>MariaDB recap</strong></div>';
echo '<div class="panel-body" style="padding:0">';
echo '<table class="table table-condensed table-bordered table-striped" style="margin:0">';
echo '<thead>';
echo '<tr>';
echo '<th style="width:22%">Component</th>';
echo '<th style="width:14%">Bytes</th>';
echo '<th style="width:12%">Nature</th>';
echo '<th style="width:20%">Source</th>';
echo '<th style="width:32%">Note</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if (empty($recap)) {
    echo '<tr><td colspan="5" class="text-muted">No recap data available.</td></tr>';
} else {
    foreach ($recap as $row) {
        echo '<tr>';
        echo '<td>'.htmlspecialchars((string) ($row['component'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td style="text-align:right">'.htmlspecialchars((string) ($row['display_bytes'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td>'.htmlspecialchars((string) ($row['nature'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td>'.htmlspecialchars((string) ($row['source'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td>'.htmlspecialchars((string) ($row['note'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
        echo '</tr>';
    }
}

echo '</tbody>';
echo '</table>';
echo '</div>';
echo '</div>';
