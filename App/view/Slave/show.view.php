<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

//mise en forme sur 2 colones

$midle = ceil(count($data['slave']) / 2);
$part1 = array_slice($data['slave'], 0, $midle, true);
$part2 = array_slice($data['slave'], $midle, count($data['slave']), true);


$i = 0;
$j = 0;




if (empty($data['replication_name'])) {
    $show = 'SHOW SLAVE STATUS;';
} else {
    $show = 'SHOW SLAVE \''.$data['replication_name'].'\' STATUS;';
}
?>



<div class="panel panel-primary">
    <div class="panel-heading">

        <h3 class="panel-title"><?= __('Switch Master To') ?></h3>
    </div>

    <div class="well">
        <?php
        \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable",
            array("mysql_server", "id", array("data-width" => "auto", "mysql_server_specify" => $data['mysql_server_specify'])));
        ?>
        <button type="button" class="btn btn-primary">Master <span class="glyphicon glyphicon-arrow-right"></span> Slave</button>


        <?php
        \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_slave", "server", array("data-width" => "auto", "mysql_server_specify" => $data['id_slave'])));
        ?>

        <button type="button" class="btn btn-primary">Switch master to</button>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="panel panel-primary">
            <div class="panel-heading">

                <h3 class="panel-title"><?= __("Commands") ?></h3>




            </div>
            <div class="well">


                <!--
                <div style="float:right">
                    <table class="tab-slave">
                        <tr>
                            <td><a class="btn btn-success" href="#" role="button">START SLAVE IO_THREAD;</a></td>
                            <td><a class="btn btn-warning" href="#" role="button">STOP SLAVE IO_THREAD;</a></td>
                        </tr>

                        <tr>
                            <td><a class="btn btn-success" href="#" role="button">START SLAVE SQL_THREAD;</a></td>
                            <td><a class="btn btn-warning" href="#" role="button">STOP SLAVE SQL_THREAD;</a></td>
                        </tr>
                    </table>
                </div>
                -->
                <div>
                    <a class="btn btn-success" href="<?= LINK ?><?= $data['class'] ?>/startSlave/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/" role="button">START SLAVE</a>
                    <a class="btn btn-warning" href="<?= LINK ?><?= $data['class'] ?>/stopSlave/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/" role="button">STOP SLAVE</a>
                    <a class="btn btn-danger" href="<?= LINK ?><?= $data['class'] ?>/skipCounter/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/" role="button">SET GLOBAL sql_slave_skip_counter =1;</a>
                </div>

                <div style="clear:both"></div>
            </div>

        </div>


    </div>

    <div class="col-md-4">

        <?php
        if (!empty($data['db_on_master'])):
            ?>
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __("Rebuild") ?></h3>
                </div>

                <div class="well">
                    <a class="btn btn-warning" href="<?= LINK ?><?= $data['class'] ?>/reload/<?= $data['id_mysql_server'] ?>/<?= $data['replication_name'] ?>/" role="button"><?= __('Reload databases this server from Master') ?> (<?=
                        implode(',', $data['db_on_master'])
                        ?>)</a>
                </div>
            </div>
            <?php
        endif;
        ?>
    </div>
    <div class="col-md-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><?= __("Global transaction identifier") ?></h3>
            </div>
            <div class="well">
                <a class="btn btn-success" href="#" role="button"><?= __('Activate GTID') ?></a>
                <a class="btn btn-warning" href="#" role="button"><?= __('Desactivate GTID') ?></a>
            </div>
        </div>
    </div>

</div>


<div class="panel panel-primary">
    <div class="panel-heading">

        <h3 class="panel-title"><?= $show ?></h3>
    </div>



    <div class="row">
        <div class="col-md-6" style="padding-right: 0">

            <?php
            echo '<table class="table table-condensed table-bordered table-striped" style="margin-bottom:0">';
            echo '<tr>';
            echo '<th>'.__('Top').'</th>';
            echo '<th>'.__('Variables').'</th>';
            echo '<th>'.__('Values').'</th>';

            echo '</tr>';

            $i = 0;

            foreach ($part1 as $line => $value) {


                $i++;
                echo '<tr>';
                echo '<td>'.$i.'</td>';


                echo '<td><b>'.$line.'</b></td>';
                echo '<td>'.$value.'</td>';

                echo '</tr>';
            }
            echo '</table>';
            ?>
        </div>

        <div class="col-md-6" style="padding-left: 0">

            <?php
            echo '<table class="table table-condensed table-bordered table-striped" style="margin-bottom:0">';
            echo '<tr>';
            echo '<th style="border-left:0">'.__('Variables').'</th>';
            echo '<th>'.__('Values').'</th>';
            echo '</tr>';

            $i = 0;

            foreach ($part2 as $line => $value) {


                $i++;
                echo '<tr>';
                echo '<td style="border-left:0"><b>'.$line.'</b></td>';
                echo '<td>'.$value.'</td>';

                echo '</tr>';
            }
            echo '</table>';
            ?>
        </div>

    </div>

</div>
<div class="panel panel-primary" style="background:#f9f9f9">
    <div class="panel-heading">

        <h3 class="panel-title"><?= __("Second behind master") ?></h3>
    </div>

    <?php
    if (!empty($data['graph'])) {
        foreach ($data['graph'] as $slave) {
            echo '<div>&nbsp;</div>';
            echo '<canvas style="width: 100%; height: 150px;" id="myChart'.$slave['id_mysql_server'].crc32($slave['day']).'" height="150" width="1600"></canvas>';

            /*
              echo '<div>';
              echo '<span  class="right" style="color:#666; float:right">min : '.$slave['min']." - ";
              echo 'max : '.$slave['max']." - ";
              echo 'avg : '.round($slave['avg'],2)."&nbsp;&nbsp;&nbsp;</span>";
              echo '</div>';
              echo '<div class="clear"></div>'; */
        }
    }
    ?>
</div>

<!--
<div class="panel panel-primary" style="background:#f5f5f5">
    <div class="panel-heading">

        <h3 class="panel-title"><?= __("Repliacation load average") ?> (By Mark LEITH)</h3>
    </div>

</div>-->