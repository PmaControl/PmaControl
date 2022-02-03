<?php

use Glial\Html\Form\Form;

if (empty($_GET['ssh']['password'])) {
    $_GET['ssh']['password'] = 22;
}
?>



<form action="" method="post">

    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title">ProxySQL : <?= __('Admin credentials') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-6">

                    <?= __("Hostname") ?>
                    <?= Form::input("proxysql_main", "hostname", array("class" => "form-control", "placeholder" => "Enter hostname of ProxySQL")) ?>
                </div>
                <div class="col-md-3">
                    <?= __("Port") ?>
                    <?= Form::input("proxysql_main", "port", array("class" => "form-control", "placeholder" => "Enter port of ProySQL Admin (default : 6032)")) ?>
                </div>

            </div>

            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-4">
                    <?= __("Login") ?>
                    <?= Form::input("proxysql_main", "login", array("class" => "form-control", "placeholder" => "Enter login (credentials admin)")) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= __("Password") ?>
                    <?= Form::input("proxysql_main", "password", array("class" => "form-control", "placeholder" => "Enter password (credentials admin)")) ?>
                </div>
            </div>

        </div>
    </div>



    <button class="btn btn-primary">Deploy</button>

</form>
