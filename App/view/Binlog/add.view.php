<?php

use App\Library\Security\CsrfRender;

use Glial\Html\Form\Form;

$binlogAddCsrfField = CsrfRender::field($data, 'binlog_add');
$binlogAddCsrfToken = CsrfRender::token($data, 'binlog_add');
?>



<form action="" method="post">
    <input type="hidden" name="<?= $binlogAddCsrfField ?>" value="<?= $binlogAddCsrfToken ?>">


    <div class="well">
        <div class="row">
            <div class="col-md-4">
                <?= __("Serveur source"); ?>
<?php \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "100%"))); ?>
            </div>

            <div class="col-md-4">
                <?= __("Size maximum allowed on server to all binlogs"); ?>
<?= Form::input("binlog_max", "size", array("class" => "form-control")) ?>
            </div>

            <div class="col-md-2">
                <?= __("Max binlog size"); ?>
                <?= Form::input("variables", "max_binlog_size", array("class" => "form-control", "readonly" => "readonly")) ?>
<?= Form::input("variables", "file_binlog_size", array("class" => "form-control", "type" => "hidden")) ?>
            </div>

            <div class="col-md-2"><br />
                <button type="submit" class="btn btn-primary">Add purge</button>
            </div>
        </div>
    </div>


</form>
