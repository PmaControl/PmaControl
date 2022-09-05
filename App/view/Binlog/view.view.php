<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Library\Display;
use App\Library\Format;
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Purge') ?></h3>
    </div>






    <?php
    \Glial\Synapse\FactoryController::addNode("Binlog", "add", array());

    echo '<table class="table table-bordered table-striped" id="table">';
    echo '<tr>';
    echo '<th>'.__('Top').'</th>';
    echo '<th>'.__('ID').'</th>';
    echo '<th>'.__('Oraganization').'</th>';
    echo '<th>'.__('Server').'</th>';

    echo '<th>'.__('Tags').'</th>';
    echo '<th>'.__('Size max allowed').'</th>';
    echo '<th>'.__('Binlog size by file').'</th>';
    echo '<th>'.__('Binlog number').'</th>';
    echo '<th>'.__('Size binlog used').'</th>';
    echo '<th>'.__('Percent').'</th>';

    echo '</tr>';

    $i     = 0;
    $style = '';

    foreach ($data['max_bin_log'] as $server) {

        $i++;
        echo '<tr>';

        echo '<td>'.$i.'</td>';
        echo '<td>'.$server['id_mysql_server'].'</td>';

        echo '<td>'.$server['organization'].'</td>';
        echo '<td>'.Display::server($server).'</td>';
        echo '<td>'.__('Tags').'</td>';
        echo '<td>'.Format::bytes($server['size_max'], 0).'</td>';
        echo '<td>'.count($data['extra'][$server['id_mysql_server']]['binary_logs']['file']).'</td>';
        echo '<td>'.$server['number_file_max'].'</td>';

        $total_size_used = array_sum($data['extra'][$server['id_mysql_server']]['binary_logs']['size']);

        echo '<td>'.Format::bytes($total_size_used).'</td>';

        $percent = round($total_size_used / $server['size_max'] * 100, 2);

        echo '<td>';
        ?>

        <div class="progress" style="margin-bottom:0">
            <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percent ?>%">
                <span style="color:#000000;"><?= $percent."&nbsp;%" ?></span>
                <span class="sr-only"><?= $percent ?>% <?= __('Complete') ?></span>
            </div>
        </div>

    <?php
    echo '</td>';

    echo '</tr>'."\n";
}


echo '</table>';
?>

</div>