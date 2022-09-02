<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Library\Mysql;
use App\Library\Display;

function display_db($dbs)
{
    if (empty($dbs)) {
        $nb = 0;
    } else {
        $nb = count(explode(",", $dbs));

        echo '<span  data-html="true" data-toggle="tooltip" data-placement="right" title="'.str_replace(',', '<br />', $dbs).'">';

        if ($nb >= 5) {
            echo $nb." * ";
            $nb = 1;
        }

        echo str_repeat('<i class="fa fa-database fa-lg" style="font-size:14px"></i> ', $nb);
        echo '</span>';
    }
}
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());

//have to put this in an other place and only if required
//\Glial\Synapse\FactoryController::addNode("Alias", "updateAlias", array());



echo '</div>';

echo '<table class="table table-condensed table-bordered table-striped" >';

echo '<tr>';
echo '<th>'.__("Top").'</th>';
//echo '<th>' . __("ID Serveur") . '</th>';
echo '<th>'.__("Master").'</th>';
echo '<th>'.__("Slave").'</th>';
echo '<th>'.__("Connection name").'</th>';
echo '<th colspan="2">'.__("Second behind master").'</th>';
echo '<th>'."Io running".'</th>';
echo '<th>'."Sql running".'</th>';
echo '<th>'."io error".'</th>';
echo '<th>'."Sql error".'</th>';
echo '<th>'."Replicate do db".'</th>';
echo '<th>'."Replicate ignore db".'</th>';
echo '<th>'.__("Date").'</th>';
echo '</tr>';

//debug($data['hostname']);

$i = 0;

debug($data['graph']);

foreach ($data['slave'] as $slaves) {


    foreach ($slaves as $connect_name => $slave) {

        if (!empty($data['info_server'][$slave['id_mysql_server']]['']['is_proxysql']) && $data['info_server'][$slave['id_mysql_server']]['']['is_proxysql'] === "1") {
            continue;
        }

        $i++;

        echo '<tr>';
        echo '<td>'.$i.'</td>';
        //echo '<td>' .  . '</td>';

        $class = "";
        if (!empty($data['server']['master'][$slave['master_host'].':'.$slave['master_port']])) {
            $master = $data['server']['master'][$slave['master_host'].':'.$slave['master_port']];

            if ($master['is_available'] === "0") {
                $class = "pma pma-danger";
            }
        }

        echo '<td class="'.$class.'">';

        //if (Mysql::getMaster($id_mysql_server))

        $uniq            = $slave['master_host'].':'.$slave['master_port'];
        $id_mysql_server = Mysql::getIdFromDns($uniq);

        if ($id_mysql_server) {

            echo Display::srv($id_mysql_server);
        } else {

            //updateAlias
            echo $slave['master_host'].':'.$slave['master_port'].' <a href="'.LINK.'Mysql/add/mysql_server:ip:'.$slave['master_host'].'/mysql_server:port:'.$slave['master_port'].'" type="button" class="btn btn-default btn-xs">Add this server to monitoring</a>';
        }

        echo '</td>';

        $class = "";
        if (empty($data['server']['master'][$slave['id_mysql_server']]['is_available'])) {

            $class = "pma pma-danger";
        }

        echo '<td class="'.$class.'">';

        $s_env = $data['server']['slave'][$slave['id_mysql_server']]['environment'];

        echo '<span data-toggle="tooltip" data-placement="right" title="'.$s_env.'" class="label label-'.$data['server']['slave'][$slave['id_mysql_server']]['class'].'">'
        .substr($s_env, 0, 1).'</span> ';
        //echo $data['server']['slave'][]['display_name'];
        //        echo $slave['id_mysql_server'];
        echo '<a href="">'.$data['info_server'][$slave['id_mysql_server']]['']['hostname'].'</a>';
        echo ' ('.$data['server']['slave'][$slave['id_mysql_server']]['ip'].')';
        echo '</td>';

        echo '<td>';

        if (empty($connect_name)) {
            $disp = '<i>['.__("default").']</i>';
        } else {
            $disp = $connect_name;
        }

        echo '<a href="'.LINK.'slave/show/'.$slave['id_mysql_server'].'/'.$connect_name.'/">'.$disp.'</a>';

        echo " ".$data['server']['idgraph'][$slave['id_mysql_server']][$connect_name];
        echo '</td>';
        //echo '<td>'.$slave['seconds_behind_master'].'</td>';

        if ($slave['seconds_behind_master'] !== "0") {
            $class = "pma pma-warning";
        } else {
            $class = "";
        }

        echo '<td class="'.$class.'">';
        if ($slave['seconds_behind_master'] === "") {
            $slave['seconds_behind_master'] = "N/A";
        }

        echo $slave['seconds_behind_master']."";

        if (!isset($data['graph'][$slave['id_mysql_server']]['max'])) {
            $data['graph'][$slave['id_mysql_server']]['max'] = "n/a";
        }

        echo ' (max : '.$data['graph'][$slave['id_mysql_server']]['max'].')';
        echo '</td>';

        echo '<td class="'.$class.'">';
        echo ' <div style="width:160px; height:17px" class="display:inline">'
        .'<canvas width="160" height="17" style="width:160px;height:17px" id="myChart'.$slave['id_mysql_server'].crc32($connect_name).'"></canvas></div>';
        echo '</td>';

        $class = "";
        if ($slave['slave_io_running'] === "No") {
            $class = 'pma pma-primary';
        } else if ($slave['slave_io_running'] === "Connecting") {
            $class = 'pma pma-warning';
        }

        echo '<td class="'.$class.'">';
        echo $slave['slave_io_running'];
        echo '</td>';

        $class = "";
        if ($slave['slave_sql_running'] === "No") {
            $class = 'pma pma-primary';
        }

        echo '<td class="'.$class.'">';
        echo $slave['slave_sql_running'];
        echo '</td>';

        //echo '<td>'.$slave['last_io_error'].'</td>';

        $class = "";
        if ($slave['last_io_errno'] !== "0") {
            $class = 'pma pma-danger';
        }
        echo '<td  class="'.$class.'">';

        if (!empty($slave['last_io_error'])) {
            echo '<a href="#" data-toggle="tooltip" data-placement="right" title="'.$slave['last_io_error'].'">'.$slave['last_io_errno'].'</a>';
        }
        echo '</td>';

        $class = "";
        if ($slave['last_sql_errno'] !== "0") {
            $class = 'pma pma-danger';
        }
        echo '<td  class="'.$class.'">';

        if (!empty($slave['last_sql_error'])) {
            echo '<a href="#" data-toggle="tooltip" data-placement="right" title="'.$slave['last_sql_error'].'">'.$slave['last_sql_errno'].'</a>';
        }
        echo '</td>';

        echo '<td>';
        display_db($slave['replicate_do_db']);
        echo '</td>';

        echo '<td>';
        display_db($slave['replicate_ignore_db']);
        echo '</td>';

        echo '<td>'.$slave['date'].'</td>';
        echo '</tr>';
    }
}

echo '</table>';

