<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Search into binlog') ?></h3>
    </div>

    <div class="well">
        <div class="row">
            <div class="col-md-2">
                <?= __("Server"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-1">
                <?= __("Database"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>


            <div class="col-md-2">
                <?= __("Value to search"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-2">
                <?= __("From binlog file"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-1">
                <br />
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-2">
                <?= __("To binlog file"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>


            <div class="col-md-1">
                <br />
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>



            <div class="col-md-1">
                <br />
                <button type="submit" class="btn btn-primary">By position</button>
            </div>
        </div>
        <div class="row">&nbsp;
        </div>
        <div class="row">

            <div class="col-md-5">


            </div>
            <div class="col-md-3">
                <?= __("From"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>
            <div class="col-md-3">
                <?= __("To"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>
            <div class="col-md-1">
                <br />
                <button type="submit" class="btn btn-primary">By date</button>
            </div>

        </div>
    </div>

</div>