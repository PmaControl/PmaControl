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
if (!empty($_GET['id_mysql_server'])) {

    if (empty($_GET['variable'])) {
        $_GET['variable'] = '';
    }

    echo '<a href="'.LINK.'variable/index/id_mysql_server:/variable:'.$_GET['variable'].'" class="btn btn-warning active">Filter: '.Display::srv($_GET['id_mysql_server'], true).'</a>';
    
}
echo ' ';

if (! empty($_GET['variable'])) {
    echo '<a href="'.LINK.'variable/index/variable:" class="btn btn-warning active">Filter: '.$_GET['variable'].'</a>';
    echo '<br><br>';
}
elseif(!empty($_GET['id_mysql_server'])) {
    echo '<br><br>';
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

    if (strlen($elem['value']) > 100)
    {
        $elem['value'] = substr($elem['value'], 0,100).'...';
    }


    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.Display::srv($elem['id_mysql_server'], true, LINK."variable/index/id_mysql_server:".$elem['id_mysql_server']).'</td>';
    echo '<td><a href="'.LINK.'variable/index/id_mysql_server:'.$elem['id_mysql_server'].'/variable:'.$elem['variable_name'].'">'.$elem['variable_name'].'</a></td>';
    echo '<td>'.$elem['value'].'</td>';

    echo '<td style="background: '.getrgba($elem['date'], 0.5).'"> '.__($elem['day']).' '.$elem['date'].'</td>';
    echo '<td style="background: '.getrgba($elem['time'], 0.5).'">'.$elem['time'].'</td>';
    echo '</tr>';
}

echo '</table>';
