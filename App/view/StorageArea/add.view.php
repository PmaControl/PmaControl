<?php

use Glial\Html\Form\Form;

\Glial\Synapse\FactoryController::addNode("StorageArea", "menu", array($data['menu']));
?>

<form action="" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __("Main") ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-4">

                    <?= __("Name") ?> <a href="#" data-toggle="popover" title="<?= __("Name") ?>" data-content="<ul><li><?= __("Specify root if you have root credentials.") ?></li><li><?= __("If you use sudo ro execute system commands, specify the username that you wish to use here. The user must exists on all nodes.") ?></li></ul>">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </a>
                    <?= Form::input("backup_storage_area", "libelle", array("class" => "form-control", "placeholder" => "Enter the name of storage area")) ?>
                </div>
                <div class="col-md-4">
                    <?= __("IP") ?> <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?= Form::input("backup_storage_area", "ip", array("class" => "form-control", "placeholder" => "Enter IP, i.e.: 10.10.1.1")) ?>
                </div>
                <div class="col-md-4">
                    <?= __("Port") ?> <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?= Form::input("backup_storage_area", "port", array("class" => "form-control", "placeholder" => "Enter the port (default: 22)")) ?>
                </div>
            </div>

            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-6">
                    <?= __("Path") ?>
                    <?= Form::input("backup_storage_area", "path", array("class" => "form-control", "placeholder" => "Enter the path where the data will be stored : /srv/backup/mysql")) ?>
                </div>
                <div class="col-md-3">
                </div>
                <div class="col-md-3">
                </div>
            </div>
        </div>
    </div>



    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __("Location") ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-6">
                    <?= __("Contry") ?> <a href="#" data-toggle="popover" title="<?= __("Name") ?>" data-content="<ul><li><?= __("Specify root if you have root credentials.") ?></li><li><?= __("If you use sudo ro execute system commands, specify the username that you wish to use here. The user must exists on all nodes.") ?></li></ul>">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </a>
                    <?= Form::select("backup_storage_area", "id_geolocalisation_country", $data['geolocalisation_country'], "", array("class" => "form-control ac_input")) ?>
                </div>
                <div class="col-md-6">
                    <?= __("City") ?> <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?= autocomplete("backup_storage_area", "id_geolocalisation_city", "form-control") ?>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __("Key SSH") ?></h3>
        </div>
        <div class="well">
            <div class="row">
                <div class="col-md-6">
                    <?= __("Select the key") ?> <a href="#" data-toggle="popover" title="<?= __("Name") ?>" data-content="<ul><li><?= __("Specify root if you have root credentials.") ?></li><li><?= __("If you use sudo ro execute system commands, specify the username that you wish to use here. The user must exists on all nodes.") ?></li></ul>">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </a>
                    <?= Form::select("backup_storage_area", "id_ssh_key", $data['ssh_key'], "", array("class" => "form-control ac_input")) ?>
                </div>
                <div class="col-md-6">
                </div>
            </div>
        </div>
    </div>

    <?php
    echo "<div class=\"form-actions\" style=\"margin:0\"><input class=\"btn btn-primary\" type=\"submit\" value=\"" . __("Validate") . "\" />&nbsp;&nbsp;&nbsp;"
    . "<input class=\"btn btn-danger\" type=\"reset\" value=\"" . __("Delete") . "\" /></div>";
    echo "</div>";
    ?>
</form>