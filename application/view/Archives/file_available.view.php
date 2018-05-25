<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function human_filesize($bytes, $decimals = 2) {
    $sz = ' KMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$sz[$factor] . "o";
}

use \Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;

echo '<div class="well">';



\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());


echo '</div>';


echo '<table class="table table-condensed table-bordered table-striped">';


echo '<tr>';
echo '<th>' . __("Top") . '</th>';
echo '<th>' . __("ID") . '</th>';
echo '<th>' . __("Date") . '</th>';
echo '<th>' . __("MD5") . '</th>';
echo '<th>' . __("Size") . '</th>';
echo '<th title="Compressed and Crypted">' . "C&C" . '</th>';
echo '<th>' . __("Time to compress") . '</th>';
echo '<th>' . __("Time to crypt") . '</th>';
echo '<th>' . __("Time to send") . '</th>';
echo '<th>' . __("Storage area") . '</th>';
echo '</tr>';


$i = 0;
foreach ($data['archive'] as $cleaner) {

    $i++;

    echo '<tr>';
    echo '<td>' . $i . '</td>';
    echo '<td>' . $cleaner[0] . '</td>';
    echo '<td>' . $cleaner[3] . '</td>';
    echo '<td>' . $cleaner[1] . '</td>';
    echo '<td>' . human_filesize($cleaner[2]) . '</td>';
    echo '<td>' . human_filesize($cleaner[6]) . '</td>';
    echo '<td>' . $cleaner[7] . ' sec</td>';
    echo '<td>' . $cleaner[8] . ' sec</td>';
    echo '<td>' . $cleaner[9] . ' sec</td>';
    echo '<td>';

    echo $cleaner[4].":".$cleaner[5];

    echo '</td>';
    echo '</tr>';
}


echo '</table>';


/*
echo 'Servers : ';
echo Form::select("mysql_server", "id", $data['list_server'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));
echo ' - Database : ';
echo Form::select("mysql_server", "id", $data['list_server'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));
echo ' <button type="button" class="btn btn-primary">Load</button>';
*/