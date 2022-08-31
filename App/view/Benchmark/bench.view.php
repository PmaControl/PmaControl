<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;


//Debug::debug($data);


if (version_compare(  $data['sysbench'],'0.5' ,">=") ) {
    ?>
    <div class="well">

    <?php
    //debug($_GET);
    //echo $data['sql'];

    echo '<form class="form-inline" action="" method="post">';
    echo ' <div class="form-group" role="group" aria-label="Default button group">';

    echo __("Server : ");

    \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-live-search" => "true", "class" => "selectpicker", "multiple" => "multiple", "style" => "z-index:1000", "data-width" => "auto")));
    echo ' ';
    //echo Form::select("mysql_server", "id", $data['servers'], "",
    //    array("data-live-search" => "true", "class" => "selectpicker", "multiple" => "multiple", "style" => "z-index:1000", "data-width" => "auto"));

    echo '<br>';




    echo '<br />';

    echo __('Thread :').' '.Form::select("benchmark_main", "threads", $data['treads'], "",
        array("data-live-search" => "true", "class" => "selectpicker", "multiple" => "multiple", "data-width" => "auto"));


    echo " ";
    echo __('Table :').' '.Form::select("benchmark_main", "tables_count", $data['tables_count'], "",
        array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));

    echo " ";
    echo __('Test mode :').' '.Form::select("benchmark_main", "mode", $data['test_mode'], "",
        array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));


    echo " ";
    echo __('Read only :').' '.Form::select("benchmark_main", "read_only", $data['read_only'], "",
        array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));

    echo " ";
    echo __('Max time only :').' '.Form::select("benchmark_main", "max_time", $data['max_time'], "",
        array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));


    echo ' <button type="submit" class="btn btn-primary">'.__("Run benchmark").'</button>';
    echo '<input type="hidden" name="benchmark" value="1">';
    echo '</div>';
    echo '</form>';
    ?>
    </div>

        <?php
    } else {
        ?>
    <div class="well">
        <h1>Information</h1>
        <p style="font-size: 15px">You need to install sysbench v0.5 or more before to use this tools (<?= $data['sysbench'] ?>)</p>

    </div>
    <?php
       
}