<?php

if (empty($_GET['ajax'])) {
    echo '<div id="listener-index">';
}

echo '<table class="table table-condensed table-bordered table-striped">';
echo '<tr>';
echo '<th>'.__("Server").'</th>';
echo '<th>'.__("Type of data").'</th>';
//echo '<th>'.__("Queue number").'</th>';
//echo '<th>'.__("Queue msg").'</th>';
echo '<th>'.__("Date of last insertertion").'</th>';
echo '<th>'.__("Date of last treatment").'</th>';
echo '<th>'.__("Difference").'</th>';
echo '<th>'.__("File MD5").'</th>';
echo '</tr>';

foreach ($data['listener'] as $listener) {
    echo '<tr class="alternate">';
    echo '<td>'.$listener['display_name'].'</td>';
    echo '<td>'.$listener['file_name'].'</td>';
    echo '<td>'.$listener['date'].'</td>';
    echo '<td>'.$listener['last_date_listener'].'</td>';
    echo '<td>';
    echo $listener['diff_seconds'];
    echo '</td>';

    echo '<td>';

    if(! empty($data['md5'][$listener['id']][$listener['file_name']]))
    {
        $elem = $data['md5'][$listener['id']][$listener['file_name']];
        echo $elem['chmod']. " ". $elem['owner']. " ". $elem['group'] . " ". $elem['datetime'];
    }
    else{
        echo __("N/A");
    }

    echo '</td>';
    echo '</tr>';
}
echo '</table>';
