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
echo '<th>'.__('Linked to').'</th>';
echo '<th>'.__('Source').'</th>';
echo '<th>'.__('Since').'</th>';
echo '<th>'.__('Action').'</th>';
echo '</tr>';

$i = 0;

foreach ($data['alia_dns'] as $alias) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$alias['dns'].'</td>';
    echo '<td>'.$alias['port'].'</td>';
    echo '<td>'.Display::srv($alias['id_mysql_server']);

    if (!empty($alias['is_from_ssh']) && (int)$alias['is_from_ssh'] === 1) {
        echo '<td><span class="label label-info">SSH</span></td>';
    } else {
        echo '<td><span class="label label-default">Manual / Auto</span></td>';
    }

    $date_start = explode(".", $alias['ROW_START'])[0];

    echo '<td>'.$date_start.'</td>';

    $msg = strip_tags(__('Delete this alias?'));

    echo '<td class="text-center">'
        .'<a href="'.LINK.'alias/delete/'.$alias['id'].'" '
        .'class="btn btn-danger btn-xs" '
        .'onclick="return confirm(\''.$msg.'\');">'
        .'<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>'
        .'</a>'
        .'</td>';

    echo '</tr>';
}

echo '</table>';
