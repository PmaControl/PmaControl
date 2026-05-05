<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Library\Available;
use App\Library\Mysql;
use App\Library\Display;

function slave_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function slave_random_hop_emoji()
{
    static $emojis = ['🎲', '🛰️', '🚇', '🪃', '🔀', '🌉', '🛸', '🎯'];

    return $emojis[array_rand($emojis)];
}

function slave_render_real_endpoint(array $slave, array $data)
{
    $localEndpoint = trim((string)$slave['master_host']).':'.(int)$slave['master_port'];
    $tunnel = $data['tunnel_details'][$localEndpoint] ?? null;

    if (empty($tunnel['remote'])) {
        return slave_h($localEndpoint);
    }

    $path = [];
    $hops = $tunnel['hops'] ?? [];
    $count = count($hops);

    for ($idx = 0; $idx < $count; $idx++) {
        $path[] = slave_h($hops[$idx]);

        if ($idx < $count - 1) {
            $path[] = slave_random_hop_emoji();
        }
    }

    return '<span data-html="true" data-toggle="tooltip" data-placement="top" title="'.implode(' ', $path).'">'.slave_h($tunnel['remote']).'</span>';
}

function slave_get_real_endpoint_from_local(string $host, int $port, array $data): string
{
    $localEndpoint = trim($host).':'.$port;
    $tunnel = $data['tunnel_details'][$localEndpoint] ?? null;

    if (!empty($tunnel['remote'])) {
        return (string)$tunnel['remote'];
    }

    return $localEndpoint;
}

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

echo '<table class="table table-condensed table-bordered table-striped" >'."\n";

echo '<tr>'."\n";
echo '<th>'.__("Top").'</th>'."\n";
//echo '<th>' . __("ID Serveur") . '</th>';
echo '<th>'.__("Master").'</th>'."\n";
echo '<th>'.__("Slave").'</th>'."\n";
echo '<th>'.__("Connection name").'</th>'."\n";
echo '<th colspan="2">'.__("Second behind master").'</th>'."\n";
echo '<th>'."Io running".'</th>'."\n";
echo '<th>'."Sql running".'</th>'."\n";
echo '<th>'."io error".'</th>'."\n";
echo '<th>'."Sql error".'</th>'."\n";
echo '<th>'."Replicate do db".'</th>'."\n";
echo '<th>'."Replicate ignore db".'</th>'."\n";
echo '<th>'.__("Date").'</th>'."\n";
echo '</tr>'."\n";

//debug($data);

$i = 0;

foreach ($data['slave'] as $slaves) {


    foreach ($slaves as $connect_name => $slave) {

        if (!empty($data['info_server'][$slave['id_mysql_server']]['']['is_proxysql']) && $data['info_server'][$slave['id_mysql_server']]['']['is_proxysql'] === "1") {
            continue;
        }

        $i++;

        echo '<tr>'."\n";
        echo '<td>'.$i.'</td>'."\n";
        //echo '<td>' .  . '</td>';

        $uniq            = $slave['master_host'].':'.$slave['master_port'];
        $id_mysql_server = Mysql::getIdFromDns($uniq);
        $realEndpoint = slave_render_real_endpoint($slave, $data);

        $id_master = null;
        if (!empty($data['server']['master'][$uniq]['id'])) {
            $id_master = $data['server']['master'][$uniq]['id'];
        } elseif ($id_mysql_server) {
            $id_master = $id_mysql_server;
        }

        $class = "";
        if ($id_master !== null) {
            $masterAvailable = $data['info_server'][$id_master][''][Available::MYSQL_AVAILABLE] ?? null;
            if ($masterAvailable === "0" || ! Available::getMySQL($id_master)) {
                $class = "pma pma-danger";
            }
        }

        echo '<td class="'.$class.'">';

        //if (Mysql::getMaster($id_mysql_server))

        if ($id_mysql_server) {
            echo Display::srv($id_mysql_server, true, LINK.'MysqlServer/main/'.$id_mysql_server.'/');
        } else {

            //updateAlias
            echo $realEndpoint.' <a href="'.LINK.'Mysql/add/mysql_server:ip:'.$slave['master_host'].'/mysql_server:port:'.$slave['master_port'].'" type="button" class="btn btn-default btn-xs">Add this server to monitoring</a>';
        }

        echo '</td>'."\n";

        $class = "";
        if (! Available::getMySQL($slave['id_mysql_server'])) {
            $class = "pma pma-danger";
        }elseif(! Available::getMySQL($slave['id_mysql_server']))
        {
            $class = "pma pma-warning";
        }

        echo '<td class="'.$class.'">';

        echo Display::srv($slave['id_mysql_server'], true, LINK.'MysqlServer/main/'.$slave['id_mysql_server'].'/');
        echo '</td>'."\n";

        echo '<td>';

        if (empty($connect_name)) {
            $disp = '<i>['.__("default").']</i>';
        } else {
            $disp = $connect_name;
        }

        echo '<a href="'.LINK.'slave/show/'.$slave['id_mysql_server'].'/'.$connect_name.'/">'.$disp.'</a>';

        //echo " ".$data['server']['idgraph'][$slave['id_mysql_server']][$connect_name];
        echo '</td>'."\n";
        //echo '<td>'.$slave['seconds_behind_master'].'</td>';

        if ($slave['seconds_behind_master'] !== "0" && $slave['seconds_behind_master'] !== "") {
            $class = "pma pma-warning";
        } else {
            $class = "";
        }

        $nullDash = '<span style="color:#999" title="NULL">—</span>';

        echo '<td class="'.$class.'">';
        if ($slave['seconds_behind_master'] === "") {
            echo $nullDash;
        } else {
            echo $slave['seconds_behind_master'];
        }

        $maxVal = $data['graph'][$slave['id_mysql_server']][$connect_name]['max'] ?? null;
        $avgVal = $data['graph'][$slave['id_mysql_server']][$connect_name]['avg'] ?? null;
        $stdVal = $data['graph'][$slave['id_mysql_server']][$connect_name]['std'] ?? null;

        if ($maxVal !== null || $avgVal !== null || $stdVal !== null) {
            echo ' (max : '.($maxVal ?? $nullDash)
                .' / avg : '.($avgVal ?? $nullDash)
                .' / std : '.($stdVal ?? $nullDash).')';
        }
        echo '</td>'."\n";

        echo '<td class="'.$class.'">'."\n";
        echo ' <div style="width:160px; height:17px" class="display:inline">'."\n"
        .'<canvas width="160" height="17" style="width:160px;height:17px" id="myChart'.$slave['id_mysql_server'].crc32($connect_name).'"></canvas>'."\n"
        .'</div>'."\n";
        echo '</td>'."\n";

        $class = "";
        if ($slave['slave_io_running'] === "No") {
            $class = 'pma pma-primary';
        } else if ($slave['slave_io_running'] === "Connecting") {
            $class = 'pma pma-warning';
        }

        echo '<td class="'.$class.'">';
        echo $slave['slave_io_running'];
        echo '</td>'."\n";

        $class = "";
        if ($slave['slave_sql_running'] === "No") {
            $class = 'pma pma-primary';
        }

        echo '<td class="'.$class.'">';
        echo $slave['slave_sql_running'];
        echo '</td>'."\n";

        //echo '<td>'.$slave['last_io_error'].'</td>';

        $class = "";
        if ($slave['last_io_errno'] !== "0") {
            $class = 'pma pma-danger';
        }
        echo '<td  class="'.$class.'">';

        if (!empty($slave['last_io_error'])) {
            echo '<a href="#" data-toggle="tooltip" data-placement="right" title="'.$slave['last_io_error'].'">'.$slave['last_io_errno'].'</a>';
        }
        echo '</td>'."\n";

        $class = "";
        if ($slave['last_sql_errno'] !== "0") {
            $class = 'pma pma-danger';
        }
        echo '<td  class="'.$class.'">';

        if (!empty($slave['last_sql_error'])) {
            echo '<a href="#" data-toggle="tooltip" data-placement="right" title="'.$slave['last_sql_error'].'">'.$slave['last_sql_errno'].'</a>';
        }
        echo '</td>'."\n";

        echo '<td>';
        display_db($slave['replicate_do_db']);
        echo '</td>'."\n";

        echo '<td>';
        display_db($slave['replicate_ignore_db']);
        echo '</td>'."\n";

        echo '<td>'.$slave['date'].'</td>'."\n";
        echo '</tr>'."\n";
    }
}

echo '</table>'."\n";
