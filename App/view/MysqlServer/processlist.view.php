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



echo '<table class="table table-condensed table-bordered table-striped" id="table" style="margin-bottom:0px">';
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
    echo '<tr class="pma-'.$line['class'].'">';
    echo '<td>'.Display::srv($line['id_mysql_server'], false).'</td>';
    echo '<td>'.$line['id'].'</td>';
    echo '<td>'.$line['user'].'</td>';
    echo '<td>'.$line['command'].'</td>';
    echo '<td>'.$line['state'].'</td>';
    echo '<td>'.$line['trx_state'].'</td>';
    echo '<td>'.$line['trx_rows_locked'].'</td>';
    echo '<td>'.$line['trx_rows_modified'].'</td>';
    echo '<td>'.$line['time'].'</td>';
    
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

