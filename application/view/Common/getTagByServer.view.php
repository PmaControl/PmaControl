<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Html\Form\Form;

$options = array_merge(array("data-live-search" => "true", "class" => "selectpicker", "multiple"=>"multiple"), $data['options']);

echo Form::Select($data['table'],$data['field'], $data['tag'],"",$options);
