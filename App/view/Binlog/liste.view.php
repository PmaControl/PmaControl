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
        <h3 class="panel-title"><?= __('Listing') ?></h3>
    </div>

    <?php
    echo '<table class="table table-bordered table-striped" id="table">';
    echo '<tr>';
    echo '<th>'.__('Top').'</th>';
    echo '<th>'.__('ID').'</th>';
    echo '<th>'.__('Oraganization').'</th>';
    echo '<th>'.__('Server').'</th>';
    echo '<th>'.__('Tags').'</th>';
    echo '<th>'.__('First file').'</th>';
    echo '<th>'.__('Last file').'</th>';
    echo '<th>'.__('Expire logs day').'</th>';
    echo '<th>'.__('Nb file').'</th>';
    echo '<th>'.__('Total Size').'</th>';
    echo '<th>'.__('Percent').'</th>';

    echo '</tr>';

    $i     = 0;
    $style = '';
    $total = 0;

    foreach ($data['server'] as $server) {

        $i++;
        echo '<tr>';

        echo '<td>'.$i.'</td>';
        echo '<td>'.$server['id_mysql_server'].'</td>';

        echo '<td>'.$server['organization'].'</td>';
        echo '<td>'.Display::srv($server['id_mysql_server']);

        //automatic purge
        if (!empty($server['size_max'])) {
            echo ' <span class="label label-warning">'.__("Automatic purge").'</span>';
        }

        echo '</td>';
        echo '<td>'.__('Tags').'</td>';

        if (!empty($server['mysql_binlog']['binlog_file_first'])) {

            echo '<td>'.$server['mysql_binlog']['binlog_file_first'].'</td>';
            echo '<td>'.$server['mysql_binlog']['binlog_file_last'].'</td>';
            echo '<td>'.$server['mysql_binlog']['expire_logs_days'].'</td>';
            echo '<td>'.$server['mysql_binlog']['binlog_nb_files'].'</td>';
            echo '<td>'.Format::bytes($server['mysql_binlog']['binlog_total_size']).'</td>';

            $percent = round($server['mysql_binlog']['binlog_total_size'] / $data['max_size'] * 100, 2);
            $total   += $server['mysql_binlog']['binlog_total_size'];

            echo '<td>';
            ?>

            <div class="progress" style="margin-bottom:0">
                <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percent ?>%">
                    <span style="color:#000000;"><?= Format::bytes($server['mysql_binlog']['binlog_total_size']) ?></span>
                    <span class="sr-only"><?= $percent ?>% Complete</span>
                </div>
            </div>

            <?php
            echo '</td>';
        } else {
            echo '<td colspan="6" style="background:#e0e0e0; text-align:center;" >'.__("You are not using binary logging").'</td>';
        }

        echo '</tr>'."\n";
    }

    echo '<tr>'."\n";
    echo '<td colspan="11" style="background:#e0e0e0; text-align:center;" >'.__("Total size used by all binlog : ").Format::bytes($total).'</td>';
    echo '</tr>'."\n";
    echo '</table>';
    ?>


</div>