<?php

use Glial\Html\Form\Form;
?>
<form action="" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Add a prefix') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-12">
                    <b><?=__("Informations")?>
                </b><br><br>
                </div>
                <div class="col-md-6">
                    <?= __("Server") ?>
                    
                    <?php
                    \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("foreign_key_remove_prefix", "id_mysql_server", array("data-width" => "100%")));
                    ?>
                </div>
            </div>

            <div class="row"><br><br></div>

            <div class="row">
                <div class="col-md-6"><?= __("Database") ?>
                <?php 
                $data['table_schema'] = array(); 
                    
                echo Form::select("foreign_key_remove_prefix", "database_name", $data['table_schema'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "100%"))
                    ?>
                </div>
            </div>

            <div class="row"><br><br></div>

            <div class="row">
                <div class="col-md-6"><?= __("Prefix") ?>
                    <?=
                    Form::input("foreign_key_remove_prefix", "prefix", array("type" => "input", "class" => "form-control", "placeholder" => "Prefix we should remove to generate virtual foreign keys"))
                    ?>
                </div>
            </div>

            <div class="row"><br><br></div>

            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-transfer" style="font-size:12px"></span> <?=__("Add a prefix")?></button>
                </div>
            </div>


        </div>
    </div>
</form>