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

            <h3 class="panel-title"><?= __('Add a leaf') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-6">

                    Title
                    <?= Form::input("menu", "title", array("class" => "form-control", "placeholder" => "Request the title of the page")) ?>
                </div>


                <div class="col-md-6">

                    URL 
                    <?= Form::input("menu", "url", array("class" => "form-control", "placeholder" => "Request the link of the page : '{LINK}user/connection/'")) ?>


                </div>

            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-6">

                    Icon 
                    <?= Form::input("menu", "icon", array("class" => "form-control", "placeholder" => "Request the link of icon to be dsplay on the left of title")) ?>
                </div>
                <div class="col-md-3">
                    Class 
                    <?= Form::input("menu", "class", array("class" => "form-control", "placeholder" => "Enter the class where is the page")) ?>
                </div>
                <div class="col-md-3">
                    Method 
                    <?= Form::input("menu", "method", array("class" => "form-control", "placeholder" => "Enter the method where is the page")) ?>
                </div>
            </div>

            <div class="row">&nbsp;</div>

            <div class="row">
                <div class="col-md-6">
                    <button class="btn btn-primary">Add leaf</button>
                </div>
                <div class="col-md-6">
                </div>
            </div>
        </div>
    </div>
</form>