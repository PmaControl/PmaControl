<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<table>';
echo '<tr>';
echo '<th>table</th>';
echo '<th>hb03_middletac01</th>';
echo '<th>preprod_mariatac01</th>';
echo '</tr>';
foreach ($data['cmp'] as $table => $gg) {
    echo '<tr>';
    echo '<td>'.$table.'</td>';
    echo '<td>'.$gg['hb03_middletac01'].'</td>';
    echo '<td>'.$gg['preprod_mariatac01'].'</td>';
    echo '</tr>';
}


echo '</table>';
