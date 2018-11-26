<?php

function secondsToTime($seconds) {
    $dtF = new DateTime("@0");
    $dtT = new DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

echo '<table class="table table-bordered table-striped" id="table">';


echo '<tr>';

echo '<th>' . __("Top") . '</th>';
echo '<th>' . __("ID") . '</th>';
echo '<th>' . __("Name") . '</th>';
echo '<th>' . __("IP") . '</th>';
echo '<th>' . __("Port") . '</th>';
echo '<th>' . __("User connected") . '</th>';
echo '<th>' . __("Select") . ' ' . __("by second") . '</th>';
echo '<th>' . __("Insert") . '</th>';
echo '<th>' . __("Update") . '</th>';
echo '<th>' . __("Replace") . '</th>';
echo '<th>' . __("Delete") . '</th>';
echo '<th>' . __("Begin") . '</th>';
echo '<th>' . __("Commit") . '</th>';
echo '<th>' . __("Rollback") . '</th>';
echo '<th style="width:200px">' . __("Percent") . '</th>';
echo '<th>' . __("OLTP / OLAP") . '</th>';
echo '<th>' . __("Uptime") . '</th>';
echo '</tr>';

$total['connected'] = 0;
$total['select'] = 0;
$total['insert'] = 0;
$total['update'] = 0;
$total['delete'] = 0;
$total['uptime'] = 0;
$total['replace'] = 0;
$total['commit'] = 0;
$total['rollback'] = 0;
$total['begin'] = 0;

$i = 0;
foreach ($data['servers'] as $id_mysql_server => $gg) {

    $server = $gg[''];

    $i++;

    $style = "";
    echo '<tr>';
    echo '<td style="' . $style . '">' . $i . '</td>';
    echo '<td style="' . $style . '">' . $id_mysql_server . '</td>';
    echo '<td style="' . $style . '">' . $data['mysql'][$id_mysql_server]['link'] . '</td>';
    echo '<td style="' . $style . '">' . $data['mysql'][$id_mysql_server]['ip'] . '</td>';
    echo '<td style="' . $style . '">' . $data['mysql'][$id_mysql_server]['port'] . '</td>';
    echo '<td style="' . $style . '">' . $server['threads_connected'] . '</td>';
    echo '<td style="' . $style . '">' . round($server['com_select'] / $server['uptime'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($server['com_insert'] / $server['uptime'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($server['com_update'] / $server['uptime'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($server['com_replace'] / $server['uptime'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($server['com_delete'] / $server['uptime'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($server['com_begin'] / $server['uptime'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($server['com_commit'] / $server['uptime'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($server['com_rollback'] / $server['uptime'], 2) . '</td>';
    echo '<td style="' . $style . '">';


    if ($server['com_delete'] + $server['com_select'] + $server['com_insert'] + $server['com_update'] != 0) {
        $percent = $server['com_select'] / ($server['com_delete'] + $server['com_select'] + $server['com_insert'] + $server['com_update']) * 100;
    } else {
        $percent = "N/A";
    }



    echo '<div class="progress" style="margin-bottom:0">

  <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width: '
    . $percent . '%">
    <span class="sr-only">' . $percent . '% Complete (success)</span>
  </div>
  
</div>';
    echo '</td>';
    echo '<td style="' . $style . '">';

    echo round($percent, 2) . ' %</td>';
    echo '<td style="' . $style . '">' . secondsToTime($server['uptime']) . '</td>';
    echo '</tr>';

    $total['connected'] += $server['threads_connected'];
    $total['select'] += $server['com_select'] / $server['uptime'];
    $total['insert'] += $server['com_insert'] / $server['uptime'];
    $total['update'] += $server['com_update'] / $server['uptime'];
    $total['delete'] += $server['com_delete'] / $server['uptime'];
    $total['replace'] += $server['com_replace'] / $server['uptime'];
    $total['begin'] += $server['com_begin'] / $server['uptime'];
    $total['commit'] += $server['com_commit'] / $server['uptime'];
    $total['rollback'] += $server['com_rollback'] / $server['uptime'];
    $total['uptime'] += $server['uptime'];
}

if ($i > 0) {

    echo '<tr>';
    echo '<td style="' . $style . '" colspan="5">' . __('Total') . '</td>';
    echo '<td style="' . $style . '">' . round($total['connected'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($total['select'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($total['insert'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($total['update'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($total['replace'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($total['delete'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($total['begin'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($total['commit'], 2) . '</td>';
    echo '<td style="' . $style . '">' . round($total['rollback'], 2) . '</td>';
    echo '<td style="' . $style . '"></td>';
    echo '<td style="' . $style . '">' . round($total['select'] / ($total['delete'] + $total['select'] + $total['insert'] + $total['update']) * 100, 2) . ' %</td>';
    echo '<td style="' . $style . '">' . secondsToTime($total['uptime']) . '</td>';
    echo '</tr>';
}
echo '</table>';

