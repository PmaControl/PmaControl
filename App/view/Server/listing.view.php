<?php

use \Glial\Synapse\FactoryController;
use Glial\Html\Form\Form;
?>
<div class="well">
    <?php
    
    
    \Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
    
    echo '<br />';
    
    echo '<div>';
    echo ' <div class="btn-group" role="group" aria-label="Default button group">';


    unset($data['menu']['logs']);

    foreach ($data['menu'] as $key => $elem) {
        if ($_GET['path'] == $elem['path']) {
            $color = "btn-primary";
        } else {
            $color = "btn-default";
        }

        echo '<a href="' . $elem['path'] . '" type="button" class="btn ' . $color . '" style="font-size:12px">'
        . ' ' . $elem['icone'] . ' ' . $elem['name'] . '</a>';
    }
    echo '</div>';

    

    echo '<div style="float:right" class="btn-group" role="group" aria-label="Default button group">';
    echo '</div>';


    /*
    echo ' <div style="float:right" class="btn-group" role="group" aria-label="Default button group">';
    echo '&nbsp;<a href="' . LINK . 'Agent/stop/1" type="button" class="btn btn-primary" style="font-size:12px"> <span class="glyphicon glyphicon-stop" aria-hidden="true" style="font-size:12px"></span> Stop Daemon</a>';
    echo '<a href="' . LINK . 'Agent/start/1" type="button" class="btn btn-primary" style="font-size:12px"> <span class="glyphicon glyphicon-play" aria-hidden="true" style="font-size:12px"></span> Start Daemon</a>';

    if (empty($data['pid'])) {
        echo '<a href="' . LINK . 'Server/listing/logs" type="button" class="btn btn-warning" style="font-size:12px"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true" style="font-size:13px"></span> Stopped</a>';
    } else {
        $cmd = "ps -p " . $data['pid'];
        $alive = shell_exec($cmd);

        if (strpos($alive, $data['pid']) !== false) {
            echo '<a href="' . LINK . 'Server/listing/logs" type="button" class="btn btn-success" style="font-size:12px"><span class="glyphicon glyphicon-ok" aria-hidden="true" style="font-size:12px"></span> Running (PID : ' . $data['pid'] . ')</a>';
        } else {
            echo '<a href="' . LINK . 'Server/listing/logs" type="button" class="btn btn-danger" style="font-size:12px"><span class="glyphicon glyphicon-remove" aria-hidden="true" style="font-size:12px"></span> Error</a>';
        }
    }
    echo '</div>';
     * 
     */

    echo '</div>';
    
echo '</div>';




    $elems = explode('/', $_GET['path']);
    $method = end($elems);



    \Glial\Synapse\FactoryController::addNode("Server", $method, array());
    