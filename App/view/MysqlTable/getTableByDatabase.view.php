<?php

use \Glial\Html\Form\Form;


$options = array_merge(array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"), $data['options']);

//Form::$select_display_msg = false;

echo Form::select("mysql_table", "id", $data['tables'], $_GET['mysql_table']['id'], $options);