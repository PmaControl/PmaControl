<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
?>

<form class="form2" action="<?= LINK ?>export/test_dechiffrement" enctype="multipart/form-data" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Import configuration') ?></h3>
        </div>

        <div class="well">



            <div class="row">&nbsp;
            </div>

            <div class="row">
                <div class="col-md-6">File to import
<?= Form::input("export", "file", array("type" => "file")) ?>
                </div>

                <div class="col-md-6">Password
<?=
Form::input("export", "password", array("class" => "form-control", "type" => "password", "placeholder" => "Password to decrypt the export file"))
?>
                </div>


            </div>

            <br />
            <button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-import"></span> Import</button> (Be carrefull the identical value will be overwriten)


        </div>

    </div>
</form>
<?php


echo "<pre>".json_encode(json_decode($data['json']), JSON_PRETTY_PRINT) ."</pre>";