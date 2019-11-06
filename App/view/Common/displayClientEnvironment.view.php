<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;


Form::$select_display_msg = false;


echo '<div>';
echo '<form style="display:inline" action="" method="post">';
echo __("Client") . " : ";
echo Form::select("client", "libelle", $data['client'], "", array("data-live-search" => "true", "class" => "selectpicker",  "data-actions-box"=> "true", "multiple"=>"multiple"));
echo " - " . __("Environment") . " : ";
echo Form::select("environment", "libelle", $data['environment'],"", array("data-live-search" => "true", "class" => "selectpicker", "data-actions-box"=> "true", "multiple"=>"multiple"));
echo ' <button type="submit" class="btn btn-primary">' . __("Filter") . '</button>';

Form::$select_display_msg = true;

echo '<input type="hidden" name="client_environment" value="1" />';
echo '</form>';
echo '</div>';


//"multiple" => "multiple",