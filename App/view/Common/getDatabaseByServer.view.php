<?php

use \Glial\Html\Form\Form;

if (!empty($data['ajax']) && $data['ajax']) {
    Form::setAjax(true);
    
    $data['table'] = "";
    $data['field'] = "";
    
}

$options = array_merge(array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"), $data['options']);

Form::$select_display_msg = false;

echo Form::select($data['table'], $data['field'], $data['databases'], "", $options);