<?php


use \Glial\Html\Form\Form;

$options = array_merge(array("data-live-search" => "true", "class" => "selectpicker"), $data['options']);
echo Form::Select($data['table'],$data['field'], $data['list_server'],"",$options);
