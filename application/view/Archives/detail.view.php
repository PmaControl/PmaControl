<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function display_status($val)
{
    $status['NOT_STARTED']          = "warning";
    $status['NOT STARTED']          = "warning";
    $status['STARTED']              = "success";
    $status['COMPLETED']            = "success";
    $status['ERROR']                = "danger";
    $status['RUNNING']              = "info";
    $status['STARTED']              = "info";
    $status['COMPLETED_WITH_ERROR'] = "primary";
    $status['FILE_NOT_FOUND']       = "danger";

    if (!empty($status[$val])) {
        return $status[$val];
    }

    return false;
}
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Archives", "menu", array());
echo '</div>';
?>

<div class="panel panel-primary">
    <div class="panel-heading">

        <h3 class="panel-title"><?= __('Progress') ?></h3>
    </div>

    <table class="table table-condensed table-bordered table-striped">
        <tr>
            <th><?= __("Progress") ?></th>
            <th><?= __("Date start") ?></th>
            <th><?= __("Date end") ?></th>
            <th><?= __("Total time") ?></th>
            <th><?= __("Status") ?></th>
        </tr>

        <tr>
            <td>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
                        60%
                    </div>
                </div>
            </td>
            <td><?= __("Status") ?></td>
        </tr>
    </table>
</div>


<div class="panel panel-primary">
    <div class="panel-heading">

        <h3 class="panel-title"><?= __('Statistics by table') ?></h3>
    </div>
    <?php
    echo '<table class="table table-condensed table-bordered table-striped">';


    echo '<tr>';
    echo '<th>'.__("Top").'</th>';
    echo '<th>'.__("ID").'</th>';
    echo '<th>'.__("Date start").'</th>';
    echo '<th>'.__("Date end").'</th>';
    echo '<th>'.__("File").'</th>';
    echo '<th>'.__("Time scp").'</th>';
    echo '<th>'.__("Time decrypt").'</th>';
    echo '<th>'.__("Time uncompress").'</th>';
    echo '<th>'.__("Time load").'</th>';
    echo '<th>'.__("Status").'</th>';
    echo '<th>'.__("Error message").'</th>';
    echo '</tr>';


    $i = 0;
    foreach ($data['details'] as $detail) {

        $i++;
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td>'.$detail['id'].'</td>';
        echo '<td>'.$detail['date_start'].'</td>';
        echo '<td>'.$detail['date_end'].'</td>';
        echo '<td>'.$detail['pathfile'].'</td>';
        echo '<td>'.$detail['time_to_transfert'].'</td>';
        echo '<td>'.$detail['time_to_decrypt'].'</td>';
        echo '<td>'.$detail['time_to_uncompress'].'</td>';
        echo '<td>'.$detail['time_to_mysql'].'</td>';

        echo '<td><span class="label label-'.display_status($detail['status']).'">'.$detail['status'].'</span></td>';
        echo '<td>'.$detail['error_msg'].'</td>';
        echo '</tr>';
    }
    ?>

</table>
</div>



<div class="panel panel-primary">
    <div class="panel-heading">

        <h3 class="panel-title"><?= __('Logs') ?></h3>
    </div>

    <?php

    function colorLevel($level)
    {
        $color = ($level === "DEBUG" || $level === "NOTICE") ? "PRIMARY" : $level;
        $color = ($level === "ERROR") ? "danger" : $level;

        return '<big><span class="label label-'.strtolower($color).'">'.$level.'</span></big>';
    }


    echo '<table class="table table-condensed table-bordered table-striped">';

    echo '<tr>';
    echo '<th>'.'#'.'</th>';
    echo '<th>'.'Pid'.'</th>';
    echo '<th>'.__('Date').'</th>';


    echo '<th>'.__('Level').'</th>';

    echo '<th>'.__('Type').'</th>';
    echo '<th>'.__('Message').'</th>';
    echo '</tr>';


    $i = 0;
    foreach ($data['logs'] as $log) {
        $i++;
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td style="background:rgb('.$log['background'][0].', '.$log['background'][1].', '.$log['background'][2].',0.7);'
        .'border-bottom:rgb('.$log['background'][0].', '.$log['background'][1].', '.$log['background'][2].',0.5) 1px solid; ">'.$log['pid'].'</td>';
        echo '<td>'.$log['date'].'</td>';

        echo '<td>'.colorLevel($log['level']).'</td>';
        echo '<td>'.$log['type'].'</td>';



        echo '<td>';
        echo $log['msg'];

        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    ?>


</div>