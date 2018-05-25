<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$th = array_keys($data['tab']);


echo '<table class="table">';

echo '<tr>';
echo '<th>Server</th>';


foreach ($th as $title) {
    echo '<th>' . $title . '</th>';
}


echo '</tr>';


for ($i = 1; $i < 4; $i++) {
    echo '<tr>';

    echo '<td>iways-db-node-au-0' . $i . '</td>';
    
    
    foreach ($data['tab'] as $key => $elem) {
        
        $val = empty($data['tab'][$key][$i]) ? 0 : $data['tab'][$key][$i];
                
        echo '<td>' . $val . '</td>';
    }



    echo '</tr>';
}


echo '</table>';
