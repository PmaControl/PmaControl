<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo '<table class="table table-condensed table-bordered table-striped" id="table">';

echo '<tr>';
echo '<th>#</th>';
echo '<th>Environment</th>';
echo '<th>DNS</th>';
echo '<th>Port</th>';
echo '<th>Destination</th>';
echo '</tr>';

$i = 0;

foreach ($data['alia_dns'] as $alias) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>Environment</td>';
    echo '<td>'.$alias['dns'].'</td>';
    echo '<td>'.$alias['port'].'</td>';
    echo '<td>'.$alias['destination'].'</td>';
    echo '</tr>';
}

echo '</table>';


echo '';