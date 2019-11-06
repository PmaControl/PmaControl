<?php
use Glial\Html\Form\Form;
use Glial\I18n\I18n;
?>
<form action="" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?= __('Add a tag') ?></h3>
        </div>
        <div class="well">
            <div class="row">
                <div class="col-md-6">Name
                    <?=
                    Form::input("tag", "name", array("class" => "form-control", "placeholder" => __("Name of the tag")))
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">Color
                    <?php
                    echo Form::input("tag", "color", array("class" => "form-control", "placeholder" => __("Color of the text")));
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">Background
                    <?= Form::input("tag", "background", array("class" => "form-control", "placeholder" =>  __("Color of the background"))) ?>
                </div>
            </div>
            <br />
            <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Add</button>
        </div>
    </div>
</form>

