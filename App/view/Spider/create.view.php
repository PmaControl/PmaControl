<?php
use Glial\Html\Form\Form;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());

echo '<br />';
echo 'Server : ';
\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array());

echo ' - Database : ';
echo Form::select("spider", "database", array(), $data['databases'], array("data-live-search" => "true", "class" => "selectpicker"));

echo ' - Table : ';
echo Form::select("spider", "table", array(), $data['tables'], array("data-live-search" => "true", "class" => "selectpicker"));

echo ' <button type="submit" class="btn btn-primary">'.__("Edit partitioning").'</button>';
echo '</div>';