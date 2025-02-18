<?php

if (empty($_GET['ajax'])) {

    echo '
    <div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">'.__('List of listeners').'</h3>
    </div>';

    echo '<div id="listener-index">';
}

echo '<table class="table table-condensed table-bordered table-striped" style="margin-bottom:0px">';
echo '<tr>';
echo '<th>'.__("ID MySQL Server").'</th>';
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
    echo '<td>'.$listener['id_mysql_server'].'</td>';
    echo '<td>'.$listener['display_name'].'</td>';
    echo '<td>'.$listener['file_name'].'</td>';
    echo '<td>'.$listener['date'].'</td>';
    echo '<td>'.$listener['last_date_listener'].'</td>';
    echo '<td>';
    echo $listener['diff_seconds'];
    echo '</td>';

    $elem = '';
    if (! empty($data['md5'][$listener['id']][$listener['file_name']]))
    {
        $elem = $data['md5'][$listener['id']][$listener['file_name']];
    }
    

    $background = '';
    if (! empty($elem))
    {
        if ($elem['owner'] !==  "www-data" && $elem['owner'] !==  "apache" ) {
            $background = "background:#d9534f; color:#fff; font-weight:700;";
        }    
    }
    
    echo '<td style="'.$background.'">';
    $background = '';
    if(! empty($elem))
    {
        echo $elem['chmod'];
        echo " ";
        echo $elem['owner'];
        echo " ". $elem['group'] . " ". $elem['datetime'];
    }
    else{
        echo __("N/A");
    }

    echo '</td>';
    echo '</tr>';
}
echo '</table>';


if (empty($_GET['ajax'])) {

    echo '</div>';
    echo '</div>';
}