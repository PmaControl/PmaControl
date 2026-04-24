<?php

$totals = $data['totals'];

echo '<div style="margin-bottom:15px">';
echo '<b>Log file:</b> <code>'.htmlspecialchars($data['log_file']).'</code> ';
if (!$data['log_exists']) {
    echo '<span class="label label-warning">missing</span>';
} else {
    echo '<span class="muted">('.number_format($data['log_size']).' bytes)</span>';
}
echo '<br /><b>Pages with issues:</b> '.(int) $totals['pages'];
echo ' &nbsp; <b>Page occurrences:</b> '.(int) $totals['page_occurrences'];
echo ' &nbsp; <b>Orphan occurrences (no page context):</b> '.(int) $totals['orphan_occurrences'];
echo '</div>';

if (!$data['log_exists']) {
    echo '<div class="alert alert-warning">No error log found — nothing to parse.</div>';
    return;
}

if (empty($data['pages']) && empty($data['orphans'])) {
    echo '<div class="alert alert-success">No PHP warnings, notices or deprecations detected in the log.</div>';
    return;
}

$labelClass = [
    'Warning'     => 'label-warning',
    'Notice'      => 'label-info',
    'Deprecated'  => 'label-default',
    'Fatal error' => 'label-danger',
    'Parse error' => 'label-danger',
];

$renderRow = function ($item) use ($labelClass) {
    $cls = $labelClass[$item['level']] ?? 'label-default';
    echo '<tr>';
    echo '<td><span class="label '.$cls.'">'.htmlspecialchars($item['level']).'</span></td>';
    echo '<td>'.htmlspecialchars($item['msg']).'</td>';
    echo '<td><code>'.htmlspecialchars($item['file']).':'.(int) $item['line'].'</code></td>';
    echo '<td style="text-align:right">'.(int) $item['count'].'</td>';
    echo '<td><small class="muted">'.htmlspecialchars($item['last_seen']).'</small></td>';
    echo '</tr>';
};

foreach ($data['pages'] as $page => $items) {
    $total = array_sum(array_column($items, 'count'));
    echo '<h3 style="margin-top:25px">'.htmlspecialchars($page).' <small>('.count($items).' distinct, '.$total.' occurrences)</small></h3>';
    echo '<table class="display-tab table table-condensed" width="100%">';
    echo '<tr><th style="width:90px">Level</th><th>Message</th><th>Location</th><th style="width:70px; text-align:right">Count</th><th style="width:180px">Last seen</th></tr>';
    foreach ($items as $item) {
        $renderRow($item);
    }
    echo '</table>';
}

if (!empty($data['orphans'])) {
    echo '<h3 style="margin-top:25px">Orphans <small>(no page context — usually CLI / worker entries)</small></h3>';
    echo '<table class="display-tab table table-condensed" width="100%">';
    echo '<tr><th style="width:90px">Level</th><th>Message</th><th>Location</th><th style="width:70px; text-align:right">Count</th><th style="width:180px">Last seen</th></tr>';
    foreach ($data['orphans'] as $item) {
        $renderRow($item);
    }
    echo '</table>';
}
