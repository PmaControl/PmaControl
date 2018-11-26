<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function colorLevel($level) {

    switch ($level) {
        case 'NOTICE':
        case 'DEBUG':
            $color = "primary";
            break;

        case 'ERROR':
            $color = "danger";
            break;

        default :
            $color = $level;
            break;
    }



    return '<big><span class="label label-' . strtolower($color) . '">' . $level . '</span></big>';
}

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Cleaner", "menu", array($data['id_cleaner']));
echo '</div>';

echo '<table class="table table-condensed table-bordered table-striped">';

echo '<tr>';
echo '<th>' . '#' . '</th>';
echo '<th>' . 'Pid' . '</th>';
echo '<th>' . __('Date') . '</th>';


echo '<th>' . __('Level') . '</th>';

echo '<th>' . __('Type') . '</th>';
echo '<th>' . __('Message') . '</th>';
echo '</tr>';


$i = 0;
foreach ($data['logs'] as $log) {
    $i++;
    echo '<tr>';
    echo '<td>' . $i . '</td>';
    echo '<td style="background:rgb(' . $log['background'][0] . ', ' . $log['background'][1] . ', ' . $log['background'][2] . ',0.7);'
    . 'border-bottom:rgb(' . $log['background'][0] . ', ' . $log['background'][1] . ', ' . $log['background'][2] . ',0.5) 1px solid; ">' . $log['pid'] . '</td>';
    echo '<td>' . $log['date'] . '</td>';

    echo '<td>' . colorLevel($log['level']) . '</td>';
    echo '<td><a href="' . LINK . 'cleaner/logs/' . $data['id_cleaner'] . '/' . $log['type'] . '">' . $log['type'] . '</a></td>';



    echo '<td>';
    echo $log['msg'];

    echo '</td>';
    echo '</tr>';
}
echo '</table>';
