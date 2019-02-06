<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
?>




<?php
if (!empty($data['compte'])) {
    echo '<ul class="list-group">';

    foreach ($data['compte'] as $compte) {
        echo '<li class="list-group-item">'.$compte.'</li>';
    }
    echo '</ul>';
}
?>


<form action="<?= LINK ?>database/create" method="POST">
    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('Create database') ?></h3>
        </div>

        <div class="well">
            <div class="row">
                <div class="col-md-6">
<?php
echo __("Server")."<br />";

\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("database", "id_mysql_server", array("data-width" => "auto", "multiple" => "multiple")));

echo '</div>';
echo '</div>';


echo '<div class="row">';
echo '&nbsp;';
echo '</div>';


echo '<div class="row">';

echo '<div class="col-md-3">';


echo __("User name")."<br />";
$data['listdb1'] = array();


echo Form::input("database", "user", array("class" => "form-control"));


echo '</div>';

echo '<div class="col-md-1">';
echo '&nbsp;';

echo Form::input("database", "gg", array("class" => "form-control", "value" => "@", "readonly" => "readonly"));
echo '</div>';



echo '<div class="col-md-3">';

echo __('Hostname');
echo Form::input("database", "hostname", array("class" => "form-control"));


echo '</div><div class="col-md-3">';
echo __('Global privileges');
echo Form::select("database", "id_mysql_privilege", $data['mysql_privilege'], "",
    array("class" => "form-control", "class" => "selectpicker",
    "data-live-search" => "true", "data-width" => "100%", "multiple" => "multiple"));

echo '</div>';
echo '</div>';

echo '<div class="row">';
echo '&nbsp;';
echo '</div>';

echo '<div class="row">';



echo '<div class="col-md-12">';
echo __('Databases to create (separated by coma)');


echo Form::input("database", "name", array("class" => "form-control", "autocomplete" => "off", "width" => "auto"));
?>




                </div>
            </div>


            <br />

            <button type="submit" class="btn btn-primary">Go</button>
        </div>
    </div>
</form>