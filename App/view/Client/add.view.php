<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

echo '<form action="" method="POST">';


echo '<div class="form-group">';
echo '<label for="exampleInputEmail1">'.__("Name of the client").'</label>';
echo Form::input("client", "libelle", array("class"=>"form-control", "placeholder" => __("Name of the client")));
echo '<br />';
echo '<button type="submit" class="btn btn-primary">'.__("Add").'</button>';
echo '</div>';
echo '</form>';


