<?php

use App\Library\Display;

//to move
function setColor($type)
{
    $hex = substr(sha1($type), 0, 2).substr(sha1($type), 20, 2).substr(md5($type), -2, 2);
    return hexToRgb($hex);
}

function hexToRgb($colorName)
{
    list($r, $g, $b) = array_map(
        function($c) {
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


echo '<table class="table table-condensed table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>'.__('Top').'</th>';
echo '<th>'.__('Server').'</th>';
echo '<th>'.__('Variable').'</th>';
echo '<th>'.__('Value').'</th>';
echo '<th>'.__('Date').'</th>';
echo '<th>'.__('Time').'</th>';
echo '</tr>';

$i = 0;
foreach ($data['variable'] as $elem) {
    $i++;

    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.Display::srv($elem['id_mysql_server'], true, LINK."variable/index/id_mysql_server:".$elem['id_mysql_server']).'</td>';
    echo '<td><a href="'.LINK.'variable/index/id_mysql_server:'.$elem['id_mysql_server'].'/variable:'.$elem['variable'].'">'.$elem['variable'].'</a></td>';
    echo '<td>'.$elem['value'].'</td>';

    echo '<td style="background: '.getrgba($elem['date'], 0.5).'"> '.__($elem['day']).' '.$elem['date'].'</td>';
    echo '<td style="background: '.getrgba($elem['time'], 0.5).'">'.$elem['time'].'</td>';
    echo '</tr>';
}


echo '</table>';
