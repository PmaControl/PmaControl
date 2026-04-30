<?php

use App\Library\Security\CsrfRender;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

$clientAddCsrfField = CsrfRender::field($data, 'client_add');
$clientAddCsrfToken = CsrfRender::token($data, 'client_add');

echo '<form action="'.LINK.'client/add" method="post">';
echo '<input type="hidden" name="'.$clientAddCsrfField.'" value="'.$clientAddCsrfToken.'" />';


echo '<div class="form-group">';
echo '<label for="exampleInputEmail1">'.__("Name of the client").'</label>';
echo Form::input("client", "libelle", array("class"=>"form-control", "placeholder" => __("Name of the client")));
echo '<br />';
echo '<button type="submit" class="btn btn-primary">'.__("Add").'</button>';
echo '</div>';
echo '</form>';

