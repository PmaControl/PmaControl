<?php

use Glial\Html\Form\Form;
?>

<form action="" method="post">

    <div class="well">



        <div class="row">
            <div class="col-md-12">
                <h3><?= __("Add a new MySQL server to monitoring") ?></h3>
            </div>
        </div>


        <div class="row">
            <div class="col-md-4"><?= __("IP") ?></div>
            <div class="col-md-4"><?=
                Form::input("mysql_server", "ip", array("class" => "form-control", "placeholder" => "IP of mysql server"))
                ?></div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Port") ?></div>
            <div class="col-md-4"><?=
                Form::input("mysql_server", "port", array("class" => "form-control", "placeholder" => "port of mysql server"))
                ?></div>
            <div class="col-md-4"></div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <h3><?= __("MySQL's account") ?></h3>
            </div>
        </div>


        <div class="row">
            <div class="col-md-4"><?= __("Login") ?></div>
            <div class="col-md-4"><?=
                Form::input("mysql_server", "login", array("class" => "form-control", "placeholder" => "login mysql server"))
                ?></div>
            <div class="col-md-4"></div>
        </div>



        <div class="row">
            <div class="col-md-4"><?= __("Password") ?></div>
            <div class="col-md-4"><?=
                Form::input("mysql_server", "password", array("class" => "form-control", "placeholder" => "Password of mysql server"))
                ?></div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3><?= __("SSH's account") ?></h3>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Type") ?></div>
            <div class="col-md-4"><?=
                Form::select("mysql_server", "type",
                    array(array("id" => "1", "libelle" => "Login / Password"), array("id" => "2", "libelle" => "SSH keys"))
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

        <div class="row">
            <div class="col-md-12">
                <h3><?= __("Others") ?></h3>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Tags") ?></div>
            <div class="col-md-4"><?=
                Form::select("mysql_server", "tags",
                    array(array("id" => "1", "libelle" => "Login / Password"), array("id" => "2", "libelle" => "SSH keys"))
                    , "", array("class" => "form-control"))
                ?></div>
            <div class="col-md-4"></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Environement") ?></div>
            <div class="col-md-4"><?=
                Form::select("mysql_server", "id_environement", $data['environment']
                    , "", array("class" => "form-control"))
                ?></div>
            <div class="col-md-4"></div>
        </div>


        <div class="row">
            <div class="col-md-4"><?= __("Clients") ?></div>
            <div class="col-md-4"><?=
                Form::select("mysql_server", "id_client", $data['client'], "", array("class" => "form-control"))
                ?></div>
            <div class="col-md-4"></div>
        </div>





        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok" style="font-size:12px"></span> Save</button>
                <button type="reset" class="btn btn-danger"><span class="glyphicon glyphicon-remove" style="font-size:12px"></span> Reset</button>
            </div>
        </div>
    </div>


</form>