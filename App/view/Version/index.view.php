<?php


use Glial\Html\Form\Form;
use App\Library\Format;


echo '<table class="table table-condensed table-bordered table-striped">';

echo '<tr>';

echo '<th>'.__('Top').'</th>';
echo '<th>'.__('ID').'</th>';
echo '<th>'.__('Date').'</th>';
echo '<th>'.__('Version').'</th>';
echo '<th>'.__('Build').'</th>';
echo '<th>'.__('Comment').'</th>';
echo '</tr>';



$i = 0;

if (!empty($data['version'])) {
    foreach ($data['version'] as $version) {

        $i++;
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td>'.$version['id'].'</td>';
        echo '<td>'.$version['date'].'</td>';
        echo '<td>'.$version['version'].'</td>';
        echo '<td>'.$version['build'].'</td>';
        echo '<td>'.$version['comment'].'</td>';

        echo '</tr>';
    }
}



echo '</table>';