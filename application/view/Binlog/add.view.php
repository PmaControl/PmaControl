<?php
use Glial\Html\Form\Form;
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Add an automate purge on binlog') ?></h3>
    </div>

    <div class="well">
        <div class="row">
            <div class="col-md-4">
                <?= __("Serveur source"); ?>
                <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("database", "id_mysql_server__from", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-4">
                <?= __("Size maximum allowed on server to all binlogs"); ?>
                <?= Form::input("binlog_max", "size", array("class" => "form-control")) ?>
            </div>

            <div class="col-md-2">
                <?= __("Max binlog size"); ?>
                <?= Form::input("binlog_max", "size", array("class" => "form-control")) ?>
            </div>

            <div class="col-md-2"><br />
                <button type="submit" class="btn btn-primary">Go</button>
            </div>
        </div>
    </div>
</div>