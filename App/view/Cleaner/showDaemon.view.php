<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



echo '<table class="table">';

echo "<tr>";
echo "<th>Top</th>";
echo "<th>Server</th>";
echo "<th>Name of cleaner</th>";
echo "<th>Date start</th>";
echo "<th>Date end</th>";
echo "<th>Time</th>";
echo "<th>item deleted</th>";
echo "</tr>";



$i=1;
foreach ($data['clean'] as $line) {
    
    $bold = "";
    if (!empty($line['item_deleted']))
    {
        $bold = "font-weight:700; background:#eee";
    }
    
    echo "<tr>";
    echo '<td style="'.$bold.'">'.$i.'</td>';
    echo '<td style="'.$bold.'">'.$line['id_mysql_server'].'</td>';
    echo '<td style="'.$bold.'">'.$line['name'].'</td>';
    echo '<td style="'.$bold.'">'.$line['date_start'].'</td>';
    echo '<td style="'.$bold.'">'.$line['date_end'].'</td>';
    echo '<td style="'.$bold.'">'.$line['time'].'</td>';
    echo '<td style="'.$bold.'">'.$line['item_deleted'].'</td>';
    echo "</tr>";
    
    $i++;
    
}


echo '</table>';
