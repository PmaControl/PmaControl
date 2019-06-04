<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function display_status($val)
{
    $status['NOT_STARTED']          = "warning";
    $status['STARTED']              = "success";
    $status['COMPLETED']            = "success";
    $status['ERROR']                = "danger";
    $status['RUNNING']              = "info";
    $status['COMPLETED_WITH_ERROR'] = "primary";

    if (!empty($status[$val])) {
        return $status[$val];
    }

    return false;
}

function human_filesize($bytes, $decimals = 2)
{
    $sz     = ' KMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
}
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Archives", "menu", array());
echo '</div>';



echo '<table class="table table-condensed table-bordered table-striped">';


echo '<tr>';
echo '<th rowspan="2">'.__("Top").'</th>';
echo '<th rowspan="2">'.__("ID").'</th>';
echo '<th rowspan="2">'.__("Owner").'</th>';
echo '<th rowspan="2">'.__("Cleaner").'</th>';
echo '<th rowspan="2">'.__("Table").'</th>';
echo '<th colspan="2">'.__("Source").'</th>';
echo '<th colspan="2">'.__("Destination").'</th>';
echo '<th rowspan="2">'.__("Date start").'</th>';
echo '<th rowspan="2">'.__("Date end").'</th>';
echo '<th rowspan="2">'.__("Progression").'</th>';
echo '<th rowspan="2">'.__("Time").'</th>';
echo '<th rowspan="2">'.__("Status").'</th>';
echo '<th rowspan="2">'.__("Details").'</th>';
echo '</tr>';

echo '<tr>';
echo '<th>'.__("Server").'</th>';
echo '<th>'.__("Database").'</th>';
echo '<th>'.__("Server").'</th>';
echo '<th>'.__("Database").'</th>';


echo '</tr>';

$i = 0;



foreach ($data['history'] as $ob) {

    $i++;

    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$ob->id.'</td>';
    echo '<td><img class="country" src="/pmacontrol/image/country/type1/'.$ob->iso.'.gif" width="18" height="12"> <a href="'.LINK.'user/profil/'.$ob->id_user_main.'/">'.$ob->firstname.' '.$ob->name.'</a></td>';
    echo '<td><a href="'.LINK.'Cleaner/index/">'.$ob->libelle.'</a></td>';
    echo '<td>'.$ob->main_table.'</td>';
    echo '<td>';
    
    echo App\Library\Display::srv($ob->id_mysql_server_src);
//    <a href="'.LINK.'Server/listing/id/mysql_server:id:'.$ob->id_mysql_server.'/ts_variable:name:com_select/ts_variable:date:6 hour/ts_variable:derivate:1">'.$ob->src.'</a>

        echo '</td>';
    echo '<td><a href="'.LINK.'Server/listing"> '.$ob->db_src.'</td>';
    echo '<td>';

     echo App\Library\Display::srv($ob->id_mysql_server);
    //<a href="'.LINK.'Server/listing/id/mysql_server:id:'.$ob->id_mysql_server.'/ts_variable:name:com_select/ts_variable:date:6 hour/ts_variable:derivate:1">'.$ob->display_name.'</a>
    echo '</td>';
    echo '<td><a href="'.LINK.'Server/listing"> '.$ob->database.'</td>';

    echo '<td>'.$ob->date_start.'</td>';
    echo '<td>'.$ob->date_end.'</td>';
    echo '<td>'.$ob->progression.' %</td>';
    echo '<td>'.$ob->duration.' sec</td>';

    $spin = '';

    if ($ob->status === "RUNNING") {
        $spin = '<i class="fa fa-spinner fa-pulse fa-fw"></i> ';
    }


    echo '<td><span class="label label-'.display_status($ob->status).'">'.$spin.$ob->status.'</span></td>';
    echo '<td><a href="'.LINK.'archives/detail/'.$ob->id.'">details<a/></td>';


    echo '</tr>';
}

echo '</table>';
