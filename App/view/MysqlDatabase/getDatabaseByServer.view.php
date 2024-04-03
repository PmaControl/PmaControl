<?php

use \Glial\Html\Form\Form;


$options = array_merge(array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"), $data['options']);

Form::$select_display_msg = false;

echo Form::select("mysql_database", "id", $data['databases'], $_GET['mysql_database']['id'], $options);