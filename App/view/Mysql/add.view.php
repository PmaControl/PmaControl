<?php

use Glial\Html\Form\Form;
?>
<form action="" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Parameters') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-12">
                    <b>Name</b><br><br>
                </div>
                <div class="col-md-6">
                    <?= __("Connection name") ?>
                    <?=
                    Form::input("mysql_server", "display_name",
                        array("class" => "form-control", "placeholder" => __("Type a name for the connection, if you let empty we will take 'select @@hostname'")))
                    ?>
                </div>
            </div>

            <div class="row"><br><br></div>

            <div class="row">
                <div class="col-md-12">
                    <b>Parameters</b><br><br>
                </div>
                <div class="col-md-8">
                    <?= __("IP") ?>
                    <?=
                    Form::input("mysql_server", "ip", array("class" => "form-control", "placeholder" => "IP of mysql server"))
                    ?>
                </div>
                <div class="col-md-4"><?= __("Port") ?>
                    <?=
                    Form::input("mysql_server", "port", array("class" => "form-control", "placeholder" => "port of mysql server"))
                    ?></div>
            </div>

            <div class="row">
                <div class="col-md-6"><?= __("Login") ?>
                    <?=
                    Form::input("mysql_server", "login", array("class" => "form-control", "placeholder" => "login mysql server : root ?"))
                    ?></div>

                <div class="col-md-6"><?= __("Password") ?>
                    <?=
                    Form::input("mysql_server", "password", array("type" => "password", "class" => "form-control", "placeholder" => "Password of mysql server"))
                    ?></div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <br><br><b>Others</b><br><br>
                </div>

                <div class="col-md-4"><?= __("Clients") ?>
                    <?=
                    Form::select("mysql_server", "id_client", $data['client'], "", array("class" => "form-control"))
                    ?></div>
                <div class="col-md-4"><?= __("Environement") ?>
                    <?=
                    Form::select("mysql_server", "id_environement", $data['environment']
                        , "", array("class" => "form-control"))
                    ?></div>
                <!--
                <div class="col-md-4"><?= __("Tags") ?><?=
                Form::select("mysql_server", "tags", array(array("id" => "1", "libelle" => "Login / Password"), array("id" => "2", "libelle" => "SSH keys"))
                    , "", array("class" => "form-control"))
                ?></div>
                -->
            </div>
            <div class="row">
                <br >
                <div class="col-md-12">
                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok" style="font-size:12px"></span> Save</button>
                    <button type="reset" class="btn btn-danger"><span class="glyphicon glyphicon-remove" style="font-size:12px"></span> Reset</button>
                </div>
            </div>
        </div>
    </div>
</form>


<!--
<div class="row">
    <div class="col-md-12">
        <h3><?= __("SSH's account") ?></h3>
    </div>
</div>

<div class="row">
    <div class="col-md-4"><?= __("Type") ?></div>
    <div class="col-md-4"><?=
Form::select("mysql_server", "type", array(array("id" => "1", "libelle" => "Login / Password"), array("id" => "2", "libelle" => "SSH keys"))
    , "", array("class" => "form-control"))
?></div>
    <div class="col-md-4"></div>
</div>


<div class="row">
    <div class="col-md-4"><?= __("Login") ?></div>
    <div class="col-md-4"><?=
Form::input("mysql_server", "ssh_login", array("class" => "form-control", "placeholder" => "login mysql server"))
?></div>
    <div class="col-md-4"></div>
</div>
<div class="row">
    <div class="col-md-4"><?= __("Password") ?></div>
    <div class="col-md-4"><?=
Form::input("mysql_server", "ssh_password", array("class" => "form-control", "placeholder" => "Password of mysql server"))
?></div>
    <div class="col-md-4"></div>
</div>


<div class="row">
    <div class="col-md-4"><?= __("Public key") ?></div>
    <div class="col-md-4"><?=
Form::input("mysql_server", "public_key", array("class" => "form-control", "placeholder" => "login mysql server"))
?></div>
    <div class="col-md-4"></div>
</div>
<div class="row">
    <div class="col-md-4"><?= __("Private key") ?></div>
    <div class="col-md-4"><?=
Form::input("mysql_server", "private_key", array("class" => "form-control", "placeholder" => "Password of mysql server"))
?></div>
    <div class="col-md-4"></div>
</div>

<div class="row">
    <div class="col-md-4"><?= __("Path for temp backup") ?></div>
    <div class="col-md-4"><?=
Form::input("mysql_server", "path", array("class" => "form-control", "placeholder" => "Path where will be stored the backup before to transfert to storage area"))
?></div>
    <div class="col-md-4"></div>
</div>

!-->
