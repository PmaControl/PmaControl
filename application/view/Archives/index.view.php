<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function human_filesize($bytes, $decimals = 2)
{
    $sz     = ' KMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
}

use \Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';


echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Archives", "menu", array());
echo '</div>';




if (!empty($data['cleaner'])) {
    echo '<canvas style="width: 100%; height: 100%;" id="myChart" height="250" width="1600"></canvas>';



    echo '<table class="table table-condensed table-bordered table-striped">';


    echo '<tr>';
    echo '<th>'.__("Top").'</th>';
    echo '<th>'.__("Archive from cleaner").'</th>';
    echo '<th>'.__("Source").'</th>';
    echo '<th>'.__("Files").'</th>';
    echo '<th>'.__("Size").'</th>';
    echo '<th>'.__("Compressed").'</th>';
    echo '<th>'.__("Actions").'</th>';
    echo '</tr>';





    $i = 0;


    Form::setIndice(true);
    foreach ($data['cleaner'] as $cleaner) {

        $i++;

        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td><a href="'.LINK.'Cleaner/index/'.$cleaner[0].'">'.$cleaner[5].'</a></td>';
        echo '<td><a href="'.LINK.'Server/listing/id/mysql_server:id:'.$cleaner[8].'/status_name:id:166/status_value_int:date:6 hour/status_value_int:derivate:1">'.$cleaner[4].'</a> > '.$cleaner[6].' > '.$cleaner[7].'</td>';
        echo '<td><a href="'.LINK.'Archives/file_available/'.$cleaner[0].'">'.$cleaner[3].'</a></td>';
        echo '<td>'.human_filesize($cleaner[1]).'</td>';
        echo '<td>'.human_filesize($cleaner[2]).'</td>';
        echo '<td>';

        echo '<form method="post" action="'.LINK.'archives/restore">';

        echo '<input type="hidden" name="id_cleaner_main" value="'.$cleaner[0].'" />';
        echo 'Servers : ';


        \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable",
            array("mysql_server", "id", array("data-live-search" => "true", "class" => "selectpicker server", "data-width" => "auto")));


        //echo Form::select("mysql_server", "id", $data['list_server'], "", array("data-live-search" => "true", "class" => "selectpicker server", "data-width" => "auto"));
        echo ' - Database : ';

        echo Form::select("mysql_server", "database", array(), $data['databases'], array("data-live-search" => "true", "class" => "selectpicker database"));


        echo ' <button type="submit" class="btn btn-primary">Load</button>';
        echo "</form>";

        echo '</td>';


        echo '</tr>';
    }
    Form::setIndice(false);

    echo '</table>';
}