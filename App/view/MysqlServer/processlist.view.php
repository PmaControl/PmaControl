<?php

use \App\Library\Display;


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


if (empty($_GET['ajax'])){


    ?>
    <div>
    <div style="float:left; padding-right:10px;"><?= \Glial\Synapse\FactoryController::addNode("MysqlServer", "menu", $param); ?></div>
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
        echo htmlentities(substr($line['query'], 0, 2048));
    }
    echo '</td>';
    echo '</tr>';
}

echo '</table>';


if (empty($_GET['ajax'])){
    echo '</div>';
    echo '</div>';

}

