<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Backup\'s binlog') ?></h3>
    </div>

    <div class="well">
        <div class="row">


            <div class="col-md-2">
                <?= __("Storage Area"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-2">
                <?= __("Délais de rétention"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-5">

            </div>


            <div class="col-md-2">
                <?= __("Server"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-1"><br />
                <button type="submit" class="btn btn-primary">Go</button>
            </div>
        </div>
        <div class="row">
            &nbsp;
        </div>

        <div class="row">
            <div class="col-md-7">
            </div>
            <div class="col-md-2">
                <?= __("Tags"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-2">
                <?= __("Environment"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>


            <div class="col-md-1"><br />
                <button type="submit" class="btn btn-primary">Go</button>
            </div>
        </div>

    </div>
</div>
