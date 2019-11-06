<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
?>
<form action="" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Add a environment') ?></h3>
        </div>
        <div class="well">
            <div class="row">
                <div class="col-md-6">

                    Label
                    <?= Form::input("environment", "libelle", array("class" => "form-control", "placeholder" => "Request the label of the new environment")) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    Key
                    <?= Form::input("environment", "key", array("class" => "form-control", "placeholder" => "Request the key for this environment")) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    Class
                    <?= Form::select("environment", "class", $data['colors'],'',array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "100%")) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">

                    Letter
                    <?= Form::input("environment", "letter", array("class" => "form-control", "placeholder" => "Request Letter of this environment")) ?>
                </div>
            </div>
            <div class="row">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <button class="btn btn-primary">Add environment</button>
                </div>
                <div class="col-md-6">
                </div>
            </div>
        </div>
    </div>
</form>