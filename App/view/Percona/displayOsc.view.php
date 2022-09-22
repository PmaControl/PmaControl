<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<table class="table table-condensed table-bordered table-striped" id="table">';

echo '<tr>';
echo '<th>'.__('Top').'</th>';
echo '<th>'.__('Environment').'</th>';
echo '<th>'.__('Server').'</th>';
echo '<th>'.__('Database').'</th>';
echo '<th>'.__('Table').'</th>';
echo '<th>'.__('Rows').'</th>';

echo '<th>'.__('Data size').'</th>';
echo '<th>'.__('Index size').'</th>';
echo '<th>'.__('Free size').'</th>';
echo '<th>'.__('Creation date').'</th>';
echo '<th>'.__('Since').'</th>';
echo '</tr>';
$i = 0;
foreach ($data['ptosc'] as $table) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>';
    echo '<big><span class="label label-'.$table['class'].'">'.$table['libelle'].'</span></big>';
    echo '</td>';
    echo '<td>'.$table['display_name'].'</td>';
    echo '<td>'.$table['table_schema'].'</td>';
    echo '<td>'.$table['table_name'].'</td>';
    echo '<td>'.$table['table_rows'].'</td>';
    echo '<td>'.$table['data_length'].'</td>';
    echo '<td>'.$table['index_length'].'</td>';
    echo '<td>'.$table['data_free'].'</td>';
    echo '<td>'.$table['create_time'].'</td>';
    echo '<td><span class="label label-warning">'.$table['days'].' '.__('days').'</span></td>';
    echo '</tr>';
}



echo '</table>';
