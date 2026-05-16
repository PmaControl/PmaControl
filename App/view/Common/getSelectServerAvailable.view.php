<?php


use \Glial\Html\Form\Form;

// `selectpicker--server` is the hook used by pmacontrol.css to cap the
// rendered bootstrap-select button at 200px max-width (issue #1248).
$options = array_merge(array("data-live-search" => "true", "class" => "selectpicker selectpicker--server", "all_selectable" => "true"), $data['options']);


echo Form::Select($data['table'],$data['field'], $data['list_server'], $data['selected'] ?? "", $options);
