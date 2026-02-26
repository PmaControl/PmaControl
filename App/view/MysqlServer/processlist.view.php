<?php

use \App\Library\Display;
use \Glial\Synapse\FactoryController;

function setColor($type)
{
    $hex = substr(sha1($type), 0, 2).substr(sha1($type), 20, 2).substr(md5($type), -2, 2);
    return hexToRgb($hex);
}

function hexToRgb($colorName)
{
    list($r, $g, $b) = array_map(
        function ($c) {
            return hexdec(str_pad($c, 2, $c));
        }, str_split(ltrim($colorName, '#'), strlen($colorName) > 4 ? 2 : 1)
    );

    return array($r, $g, $b);
}

function getrgba($label, $alpha)
{
    list($r, $g, $b) = setColor($label);
    return "rgba(".$r.", ".$g.", ".$b.", ".$alpha.")";
}

function highlight_mariadb_keywords(string $sql): string {
    // Liste des principaux mots-clés réservés MariaDB/MySQL (tu peux en ajouter si besoin)
    $keywords = [
        'SELECT','FROM','WHERE','INSERT','INTO','VALUES','UPDATE','SET','DELETE','JOIN',
        'LEFT','RIGHT','INNER','OUTER','ON','AS','AND','OR','NOT','NULL','IS','IN','EXISTS',
        'GROUP','BY','ORDER','HAVING','LIMIT','DISTINCT','CREATE','TABLE','ALTER','DROP',
        'DATABASE','INDEX','VIEW','TRIGGER','PROCEDURE','FUNCTION','PRIMARY','KEY','FOREIGN',
        'REFERENCES','AUTO_INCREMENT','DEFAULT','ENGINE','CHARSET','UNION','ALL','CASE','WHEN','THEN','ELSE','END'
    ];

    // Création du motif regex pour les mots complets insensibles à la casse
    $pattern = '/\b(' . implode('|', $keywords) . ')\b/i';

    // Remplacement par la mise en forme HTML
    return preg_replace_callback($pattern, function ($matches) {
        return '<b><span style="color:rgb(51, 122, 183)">' . strtoupper($matches[1]) . '</span></b>';
    }, $sql);
}



if (empty($_GET['ajax'])){


    ?>
    <div>
    <div style="float:left; padding-right:10px;">
        <?= FactoryController::addNode("MysqlServer", "menu", $param); ?>
        <?php


echo __("Refresh each :")."&nbsp;";
echo '<div class="btn-group">';
echo '<a onclick="setRefreshInterval(1000)" type="button" class="btn btn-primary">1 sec</a>';
echo '<a onclick="setRefreshInterval(2000)" type="button" class="btn btn-primary">2 sec</a>';
echo '<a onclick="setRefreshInterval(5000)" type="button" class="btn btn-primary">5 sec</a>';
echo '<a onclick="setRefreshInterval(10000)" type="button" class="btn btn-primary">10 sec</a>';
echo '<a onclick="stopRefresh()" type="button" class="btn btn-primary">Stop</a></div><br /><br />';
?>
    </div>
    </div>
    <div style="clear:both"></div>
    <br />

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __("Processlist").' : '.Display::srv($param[0]); ?></h3>
        </div>
    <?php
    echo '<div id="processlist">';
}

$connectionsBar = $data['connections_bar'] ?? [];
$maxConnections = max(0, (int)($connectionsBar['max_connections'] ?? 0));
$threadsRunning = max(0, (int)($connectionsBar['threads_running'] ?? 0));
$threadsConnected = max(0, (int)($connectionsBar['threads_connected'] ?? 0));
$maxUsedConnections = max(0, (int)($connectionsBar['max_used_connections'] ?? 0));

$runningPercent = (float)($connectionsBar['running_percent'] ?? 0);
$connectedPercent = (float)($connectionsBar['connected_percent'] ?? 0);
$maxUsedPercent = (float)($connectionsBar['max_used_percent'] ?? 0);

$runningPercent = max(0, min(100, $runningPercent));
$connectedPercent = max(0, min(100, $connectedPercent));
$maxUsedPercent = max(0, min(100, $maxUsedPercent));

echo '<style>
.processlist-conn-wrap{padding:8px 10px 0 10px}
.processlist-conn-bar{position:relative;width:100%;height:30px;background:#eef2f6;border:1px solid #d9e1ea;border-radius:4px;overflow:hidden}
.processlist-conn-layer{position:absolute;left:0;top:0;height:100%;display:flex;align-items:center;justify-content:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#fff;font-weight:600;font-size:11px;padding:0 6px;text-shadow:0 1px 1px rgba(0,0,0,.35)}
.processlist-conn-max-used{justify-content:flex-end;background:rgba(91,192,222,.95);z-index:1}
.processlist-conn-connected{justify-content:flex-end;background:rgba(240,173,78,.95);z-index:2}
.processlist-conn-running{background:rgba(217,83,79,.95);z-index:3}
.processlist-conn-legend{display:flex;gap:10px;align-items:center;flex-wrap:wrap;font-size:12px;margin:0 0 5px 0}
.processlist-conn-dot{display:inline-block;width:10px;height:10px;border-radius:50%;vertical-align:middle;margin-right:4px}
.processlist-conn-max{margin-left:auto;font-weight:700}
</style>';

echo '<div class="processlist-conn-wrap">';
echo '<div class="processlist-conn-legend">';
echo '<span><span class="processlist-conn-dot" style="background:#d9534f"></span>Threads running: '.$threadsRunning.'</span>';
echo '<span><span class="processlist-conn-dot" style="background:#f0ad4e"></span>Threads connected: '.$threadsConnected.'</span>';
echo '<span><span class="processlist-conn-dot" style="background:#5bc0de"></span>Max used: '.$maxUsedConnections.'</span>';
echo '<span class="processlist-conn-max">Max connexion: '.$maxConnections.' = 100%</span>';
echo '</div>';

echo '<div class="processlist-conn-bar">';
echo '<div class="processlist-conn-layer processlist-conn-max-used" style="width:'.$maxUsedPercent.'%">Max used '.$maxUsedConnections.'</div>';
echo '<div class="processlist-conn-layer processlist-conn-connected" style="width:'.$connectedPercent.'%">Connected '.$threadsConnected.'</div>';
echo '<div class="processlist-conn-layer processlist-conn-running" style="width:'.$runningPercent.'%">Running '.$threadsRunning.'</div>';
echo '</div>';
echo '</div>';



echo '<table class="table table-condensed table-bordered table-striped" id="table" style="margin-bottom:0px; margin-top:10px">';
echo '<tr>';
echo '<th width="5%">'.__("Server").'</th>';
echo '<th width="5%">'.__("Thread ID").'</th>';
echo '<th width="10%">'.__("Username").'</th>';
echo '<th width="5%">'.__("Command").'</th>';
echo '<th width="5%">'.__("State").'</th>';
echo '<th width="5%">'.__("TRX State").'</th>';
echo '<th width="5%">'.__("R-Lock").'</th>';
echo '<th width="5%">'.__("R-Mod").'</th>';
echo '<th width="5%">'.__("Time").'</th>';
echo '<th>'.__("Query").'</th>';
echo '</tr>';

$i = 0;

foreach($data['processlist'] as $line){
    $rowClass = $line['class'] ?? '';
    echo '<tr class="pma-'.$rowClass.'">';
    echo '<td>'.Display::srv($line['id_mysql_server'] ?? '', false).'</td>';
    echo '<td>'.($line['id'] ?? '').'</td>';
    echo '<td>'.($line['user'] ?? '').'</td>';
    echo '<td>'.($line['command'] ?? '').'</td>';
    echo '<td>'.($line['state'] ?? '').'</td>';
    echo '<td>'.($line['trx_state'] ?? 'N/A').'</td>';
    echo '<td>'.($line['trx_rows_locked'] ?? 'N/A').'</td>';
    echo '<td>'.($line['trx_rows_modified'] ?? 'N/A').'</td>';
    echo '<td>'.($line['time'] ?? 0).'</td>';
    
    echo '<td>';
    if (! empty($line['query']))
    {
        //echo $line['query'];
        echo highlight_mariadb_keywords(htmlentities(substr($line['query'], 0, 2048)));
    }
    echo '</td>';
    echo '</tr>';
}

echo '</table>';


if (empty($_GET['ajax'])){
    echo '</div>';
    echo '</div>';

}

