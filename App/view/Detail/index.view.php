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
    
    //print_r($_GET);
    //echo $data['sql'];

    echo '&nbsp;-&nbsp;<form class="form-inline" style="display:inline" action="" method="post">';

    echo __("Server : ");
    echo ' ';
    \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("data-width" => "auto")));



    echo ' <button type="submit" class="btn btn-primary">'.__("Filter").'</button>';

    echo '</form>';
    ?>
</div>


<ul class="nav nav-tabs">
  <li><a href="#">phpMyAdmin</a></li>
  <li class="active"><a href="#"><?= __("Editor") ?></a></li>
  <li><a href="#"><?= __("Graph") ?></a></li>
  <li><a href="#"><?= __("Alert") ?></a></li>
  <li><a href="#"><?= __("Configuration") ?></a></li>
</ul>