<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
?>



<form action="" method="POST">
    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('User account') ?></h3>
        </div>

        <div class="well">

            <div class="row">
                <div class="col-md-3">
<?= __("Host name"); ?>
                </div>
                <div class="col-md-3">
<?= Form::input('mysql_server', 'login', array('class' => 'form-control', 'value'=> $data['server']['display_name'], 'readonly'=>'readonly')); ?>
                </div>
            </div>


            <div class="row">
                <div class="col-md-3">
<?= __("User name"); ?>
                </div>
                <div class="col-md-3">
<?= Form::input('mysql_server', 'login', array('class' => 'form-control')); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
<?= __("Password"); ?>
                </div>
                <div class="col-md-3">
<?= Form::input('mysql_server', 'passwd', array('class' => 'form-control', 'type' => 'password')); ?>
                </div>
            </div>


            <div class="row">
                <div class="col-md-3">

                    <button type="submit" class="btn btn-primary"><?= __('Change password') ?></button>

                </div>
            </div>


        </div>
    </div>
</form>