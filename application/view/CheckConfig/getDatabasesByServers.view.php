<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



use Glial\Html\Form\Form;


echo Form::select("mysql_cluster", "database", $data['databases'], "", array( "data-live-search" => "true","class" => "selectpicker form-control"));