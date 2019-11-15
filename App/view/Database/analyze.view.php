<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

?>

<form action="" method="POST">
    <?= Form::input("database", "analyze", array("type" => "hidden", "value"=>"1")); ?>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Automatic updating indexes statistics') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-3">
                    <?php
                    echo __("Server")."<br />";
                    \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("analyze", "id_mysql_server", array("data-width"=>"100%")));

                    echo '</div><div class="col-md-3">';
                    echo __("Database")."<br />";
                    $data['listdb1'] = array();
                    echo Form::select("analyze", "database", $data['listdb1'], "", array("data-live-search" => "true", "class" => "selectpicker","data-width"=>"100%", "multiple" => "multiple"));
                    echo '</div><div class="col-md-3">';
                    
                    
                    debug($_POST);
                    ?>
                </div>
            </div>


            <br />

            <button type="submit" class="btn btn-primary">Go</button>
        </div>
    </div>
</form>