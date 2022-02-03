<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

?>

<form action="<?= LINK ?>database/rename" method="POST">
    <?= Form::input("database", "rename", array("type" => "hidden", "value"=>"1")); ?>
    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('Rename database to') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-3">
                    <?php
                    echo __("Server")."<br />";

                    \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("rename", "id_mysql_server", array("data-width"=>"100%")));

                    echo '</div><div class="col-md-3">';

                    echo __("Database")."<br />";
                    $data['listdb1'] = array();

                    echo Form::select("rename", "database", $data['listdb1'], "", array("data-live-search" => "true", "class" => "selectpicker","data-width"=>"100%"));

                    echo '</div><div class="col-md-3">';

                    echo __('Rename database to');
                    echo Form::input("rename", "new_name", array("class" => "form-control"));

                    echo '</div><div class="col-md-3">';
                    echo '<div class="form-group"><br />
    <div class="checkbox checbox-switch switch-success">
        <label>
            '.Form::input("rename", "adjust_privileges", array("class" => "form-control", "type" => "checkbox", "checked" => "checked")).'
            <span></span>
            '.__('Adjust privileges').'
        </label>
    </div>
</div>';
                    ?>
                </div>
            </div>
            <br />
            <button type="submit" class="btn btn-primary">Go</button>
        </div>
    </div>
</form>