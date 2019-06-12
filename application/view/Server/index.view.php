<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function format($bytes, $decimals = 2)
{
    $sz     = ' KMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
}

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';



echo '<table class="table table-condensed table-bordered table-striped">';

echo '<tr>';

echo '<th>'.__('Server').'</th>';
echo '<th>'.__('Handler_read_rnd_next ').'</th>';
echo '<th>'.__('Handler_read_rnd').'</th>';
echo '<th>'.__('Handler_read_first').'</th>';
echo '<th>'.__('Handler_read_next').'</th>';
echo '<th>'.__('Handler_read_key').'</th>';
echo '<th>'.__('Handler_read_prev').'</th>';
echo '<th>'.__('Usage').'</th>';
echo '<th>'.__('Percent').'</th>';
echo '</tr>';


if (!empty($data['status'])) {
    foreach ($data['status'] as $id_mysql_server => $var) {

        $variable = $var[''];

        echo '<tr>';

        echo '<td>'.$data['mysql'][$id_mysql_server]['link'].'</td>';
        echo '<td>'.format($variable['handler_read_rnd_next']).'</td>';
        echo '<td>'.format($variable['handler_read_rnd']).'</td>';
        echo '<td>'.format($variable['handler_read_first']).'</td>';
        echo '<td>'.format($variable['handler_read_next']).'</td>';
        echo '<td>'.format($variable['handler_read_key']).'</td>';
        echo '<td>'.format($variable['handler_read_prev']).'</td>';
        
        $percent = 1 - (($variable['handler_read_rnd_next'] + $variable['handler_read_rnd']) /
            ($variable['handler_read_rnd_next'] + $variable['handler_read_rnd'] + $variable['handler_read_first'] + $variable['handler_read_next']
            + $variable['handler_read_key'] + $variable['handler_read_prev'] ));

        echo '<td>'.round($percent * 100, 2).'%</td>';


        $percent = round($percent * 100);

        if ($percent >= 80) {
            $color = "progress-bar-success";
        } elseif ($percent >= 45) {
            $color = "progress-bar-warning";
        } else {
            $color = "progress-bar-danger";
        }


        echo '<td>';
        echo '<div class="progress" style="margin-bottom:0">
  <div class="progress-bar '.$color.'" role="progressbar" aria-valuenow="'.$percent.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$percent.'%">
    <span class="sr-only">'.$percent.'% Complete (success)</span>
  </div>
</div>';
        echo '</td>';


        echo '</tr>';
    }
}

echo '</table>';


echo '<div class="well">';
echo '<b>'.__('Usage of index is calculed as follow :').'</b>';

echo '<br /><br />';
echo '1-((Handler_read_rnd_next + Handler_read_rnd)/<br />'
.'(Handler_read_rnd_next + Handler_read_rnd + Handler_read_first + Handler_read_next + Handler_read_key + Handler_read_prev))<br />';

echo '</div>';
