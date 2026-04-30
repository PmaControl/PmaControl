<?php

use App\Library\Display;
use App\Library\Html;

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
$filterIdMysqlServer = $data['filter_id_mysql_server'] ?? null;
$filterVariable = (string)($data['filter_variable'] ?? '');

if (!empty($filterIdMysqlServer)) {
    $filterVariableSegment = rawurlencode($filterVariable);
    $clearServerUrl = Html::escape(LINK.'variable/index/id_mysql_server:/variable:'.$filterVariableSegment);
    echo '<a href="'.$clearServerUrl.'" class="btn btn-warning active">Filter: '
        .Display::srv((int)$filterIdMysqlServer, true).'</a>';
}
echo ' ';

if ($filterVariable !== '') {
    $clearVariableUrl = Html::escape(LINK.'variable/index/variable:');
    echo '<a href="'.$clearVariableUrl.'" class="btn btn-warning active">Filter: '.Html::escape($filterVariable).'</a>';
    echo '<br><br>';
}
elseif (!empty($filterIdMysqlServer)) {
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

    $serverId = (int)($elem['id_mysql_server'] ?? 0);
    $variableName = (string)($elem['variable_name'] ?? '');
    $value = (string)($elem['value'] ?? '');
    $date = (string)($elem['date'] ?? '');
    $time = (string)($elem['time'] ?? '');
    $day = (string)($elem['day'] ?? '');

    if (strlen($value) > 100) {
        $value = substr($value, 0, 100).'...';
    }

    $serverUrl = Html::escape(LINK.'variable/index/id_mysql_server:'.$serverId);
    $variableUrl = Html::escape(
        LINK.'variable/index/id_mysql_server:'.$serverId.'/variable:'.rawurlencode($variableName)
    );

    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.Display::srv($serverId, true, $serverUrl).'</td>';
    echo '<td><a href="'.$variableUrl.'">'.Html::escape($variableName).'</a></td>';
    echo '<td>'.Html::escape($value).'</td>';

    echo '<td style="background: '.getrgba($date, 0.5).'"> '.Html::escape(__($day)).' '.Html::escape($date).'</td>';
    echo '<td style="background: '.getrgba($time, 0.5).'">'.Html::escape($time).'</td>';
    echo '</tr>';
}

echo '</table>';
