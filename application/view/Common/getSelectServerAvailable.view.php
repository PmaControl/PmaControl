<?php


use \Glial\Html\Form\Form;


echo Form::Select($data['table'],$data['field'], $data['list_server'],"", array("data-live-search" => "true", "class" => "selectpicker", "data-width" => "auto"));


