<?php

use Glial\Html\Form\Form;
?>
<form action="" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Docker Host Parameters') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-12">
                    <b><?= __("Identification") ?></b><br><br>
                </div>
                <div class="col-md-6">
                    <?= __("Display name") ?>
                    <?=
                    Form::input("docker_server", "display_name",
                        array("class" => "form-control", "placeholder" => __("Name to identify this Docker host")))
                    ?>
                </div>
            </div>

            <div class="row"><br><br></div>

            <div class="row">
                <div class="col-md-12">
                    <b><?= __("Connection (SSH)") ?></b><br><br>
                </div>
                <div class="col-md-8">
                    <?= __("Hostname / IP") ?>
                    <?=
                    Form::input("docker_server", "hostname", array("class" => "form-control", "placeholder" => "example: 192.168.1.44"))
                    ?>
                </div>
                <div class="col-md-4"><?= __("SSH Port") ?>
                    <?=
                    Form::input("docker_server", "port", array("class" => "form-control", "placeholder" => "22"))
                    ?></div>
            </div>


            <div class="row"><br><br></div>

            <div class="row">
                <div class="col-md-12">
                    <b><?= __("Status") ?></b><br><br>
                </div>

                <div class="col-md-4">
                    <?= __("Active") ?><br>
                    <?=
                    Form::select("docker_server", "is_active",
                        array(
                            array("id" => "1", "libelle" => __("Yes")),
                            array("id" => "0", "libelle" => __("No"))
                        ),
                        "1",
                        array("class" => "form-control"))
                    ?>
                </div>
            </div>



        </div>
    </div>



<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __("SSH Key") ?></h3>
    </div>
    <div class="well">
        <div class="row">
            <div class="col-md-6">
                <?= __("Select the key") ?>
                <a href="#" data-toggle="popover" title="<?= __("SSH Key") ?>"
                   data-content="<?= __("Choose the SSH key used to connect to this Docker host.") ?>">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </a>
                <?= Form::select("docker_server", "id_ssh_key", $data['ssh_key'], "", array("class" => "form-control ac_input")) ?>
            </div>
            <div class="col-md-6"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <button type="submit" class="btn btn-success">
            <span class="glyphicon glyphicon-ok" style="font-size:12px"></span> <?= __("Save") ?>
        </button>
        <button type="reset" class="btn btn-danger">
            <span class="glyphicon glyphicon-remove" style="font-size:12px"></span> <?= __("Reset") ?>
        </button>
    </div>
</div>
</form>