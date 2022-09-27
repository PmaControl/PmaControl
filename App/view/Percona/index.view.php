<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


debug($data);
echo '<table>';

echo '<tr>';
echo '<th>'.__('Environment').'</th>';
echo '<th>'.__('Server').'</th>';
echo '<th>'.__('Database').'</th>';
echo '<th>'.__('Table').'</th>';
echo '<th>'.__('Data size').'</th>';
echo '<th>'.__('Index size').'</th>';
echo '<th>'.__('Free size').'</th>';
echo '<th>'.__('Creation date').'</th>';
echo '</tr>';

foreach ($data as $table) {


    echo '<tr>';
    echo '<td>'.$table['libelle'].'</td>';
    echo '<td>'.$table['display_name'].'</td>';
    echo '<td>'.$table['table_schema'].'</td>';
    echo '<td>'.$table['table_name'].'</td>';
    echo '<td>'.$table['data_length'].'</td>';
    echo '<td>'.$table['index_length'].'</td>';
    echo '<td>'.$table['data_free'].'</td>';
    echo '<td>'.$table['create_time'].'</td>';
    echo '</tr>';
}



echo '</table>';
