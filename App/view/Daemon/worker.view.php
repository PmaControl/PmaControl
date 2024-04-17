<?php 

if (empty($_GET['ajax'])){
    echo '<div id="worker-index">';
}


echo '<table class="table table-condensed table-bordered table-striped">';
echo '<tr>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.'PID'.'</th>';
echo '<th>'.__('Date').'</th>';
echo '<th>'.__('Log').'</th>';
echo '<th>'.__("Working on").'</th>';
echo '</tr>';

foreach ($data['worker'] as $worker) {

    echo '<tr>';
    echo '<td>'.$worker['name'].'</td>';
    echo '<td>'.$worker['pid'].'</td>';
    echo '<td>'.$worker['date_created'].'</td>';
    echo '<td>'.$worker['filesize'].'</td>';
    echo '<td>'.$worker['id_proxysql'].'</td>';
    echo '</tr>';
}

echo '</table>';

if (empty($_GET['ajax'])){
    echo '</div>';
}