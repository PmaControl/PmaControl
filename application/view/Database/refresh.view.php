<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
?>





<form action="<?= LINK ?>database/create" method="POST">
    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('Refresh database from an other server') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-4">
                    <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("database", "id_mysql_server__from", array("data-width" => "100%"))); ?>
                </div>
                <div class="col-md-3">
                    <?=
                    Form::select("database", "list", $data['listdb1'], "",
                        array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "100%", "multiple" => "multiple"));
                    ?>
                </div>
            </div>
            <div class="row">
                &nbsp;
            </div>
            <div class="row">
                <div class="col-md-4">
                    <?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable",
                        array("database", "id_mysql_server__target", array("data-width" => "100%")));
                    ?>
                </div>
            </div>
            <div class="row">
                &nbsp;
            </div>

            <div class="row">
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Go</button>
                </div>
            </div>
        </div>
    </div>
</form>