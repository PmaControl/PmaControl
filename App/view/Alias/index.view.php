<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Library\Display;

echo '<a href="'.LINK.'alias/updateAlias/" class="btn btn-primary" style="font-size:12px"><span class="glyphicon glyphicon-refresh" style="font-size:12px"></span> Get aliases</a>';
//echo ' ';
//echo '<a href="'.LINK.'mysql/add/" class="btn btn-primary" style="font-size:12px"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> Add a custom alias</a>';
echo '<br><br>';

echo '<table class="table table-condensed table-bordered table-striped" id="table">';

echo '<tr>';
echo '<th>#</th>';
echo '<th>DNS</th>';
echo '<th>Port</th>';
echo '<th>Destination</th>';
echo '<th>'.__('Linked to').'</th>';
echo '<th>'.__('Since').'</th>';
echo '</tr>';

$i = 0;

foreach ($data['alia_dns'] as $alias) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$alias['dns'].'</td>';
    echo '<td>'.$alias['port'].'</td>';
    echo '<td>'.$alias['destination'].'</td>';
    echo '<td>'.Display::srv($alias['id_mysql_server']);

    $date_start = explode(".", $alias['ROW_START'])[0];

    echo '<td>'.$date_start.'</td>';
    echo '</tr>';
}

echo '</table>';
