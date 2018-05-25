<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */




echo '<table class="table">';
echo '<tr>';
echo '<th>Top</th>';
echo '<th>Server Name</th>';
echo '<th>IP:Port</th>'
. '<th>count</th>';
echo '</tr>';





$i = 0;
foreach ($data['count'] as $line) {
    $i++;

    echo '<tr>';
    echo '<td>' . $i . '</td>';
    echo '<td><a href="'.LINK.'mysql/event/'.str_replace('_', '-', $line['name']).'">' . str_replace('_', '-', $line['name']) . '</a></td>'
    . '<td>' . $line['ip'] . ':' . $line['port'] . '</td>';

    echo '<td>' . $line['nb_event'] . '</td>';
    echo '</tr>';
}
echo '</table>';


echo '<br />';

$i = 0;
echo '<table class="table">';
echo '<tr>';
echo '<th>Top</th>';
echo '<th>Date</th>';
echo '<th>Server Name</th>'
. '<th>IP:Port</th>';
echo '<th>Message</th>';
echo '</tr>';

foreach ($data['output'] as $line) {
    $i++;

    echo '<tr class="'.$line['libelle'].'">';
    echo '<td>' . $i . '</td>';
    echo '<td>' . $line['date'] . '</td>';
    echo '<td>' . str_replace('_', '-', $line['name']) . '</td>'
    . '<td>' . $line['ip'] . ':' . $line['port'] . '</td>';

    echo '<td>' . $line['message'] . '</td>';
    echo '</tr>';
}
echo '</table>';
