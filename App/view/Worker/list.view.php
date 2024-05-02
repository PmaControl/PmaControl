<?php 

if (empty($_GET['ajax'])){
    echo '<div id="worker-index">';
}


echo '<table class="table table-condensed table-bordered table-striped">';

echo '<tr>';
echo '<th>'.__("Top").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.'PID'.'</th>';
echo '<th>'.__('Date').'</th>';
echo '<th>'.__("Working on").'</th>';
echo '</tr>';

$i=0;
foreach ($data['worker'] as $worker) {
    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$worker['name'].'</td>';
    echo '<td>'.$worker['pid'].'</td>';
    echo '<td>'.$worker['date_created'].'</td>';
    echo '<td>'.$worker['id_current'].'</td>';
    echo '</tr>';
}

echo '</table>';

if (empty($_GET['ajax'])){
    echo '</div>';
}