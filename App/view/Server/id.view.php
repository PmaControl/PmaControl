<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;
?>
<div class="well">

    <?php
    \Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
    echo '<br >';
    //print_r($_GET);
    //echo $data['sql'];

    echo '<form class="form-inline" action="" method="post">';
    echo ' <div class="form-group" role="group" aria-label="Default button group">';

    echo __("Server : ");
    echo ' ';

    \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "auto","all_selectable"=> "true")));

    //echo Form::select("mysql_server", "id", $data['servers'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));
    echo ' ';

    echo Form::select("ts_variable", "name", $data['status'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));
    echo ' ';

    echo Form::select("ts_variable", "date", $data['interval'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));

    echo ' Derivate : ';

    echo Form::select("ts_variable", "derivate", $data['derivate'], "", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));


    echo ' <button type="submit" class="btn btn-primary">'.__("Filter").'</button>';

    echo '</div>';
    echo '</form>';
    ?>  
</div>


<!--
<div style="height:600px; width:1600px">
<canvas id="myChart" height="500" width="1600"></canvas>
</div>
-->
<?php
if (!empty($data['fields_required'])) {

    echo '<div class="well" style="border-left-color: #'.'b85c5c'.';   border-left-width: 10px;"><p><b>'.'Error'.'</b></p>';
    echo 'Please request all fields !';
    echo '</div>';



    //\Glial\Synapse\FactoryController::addNode("Error", "message", array(__('Error'), __('Please request all fields !'), '5cb85c'));
} else {

    echo '<canvas style="width: 100%; height: 450px;" id="myChart" height="450" width="1600"></canvas>';
}



