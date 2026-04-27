<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

$clientAddCsrfField = htmlspecialchars((string) ($data['client_add_csrf_field'] ?? '_csrf_token'), ENT_QUOTES, 'UTF-8');
$clientAddCsrfToken = htmlspecialchars((string) ($data['client_add_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8');

echo '<form action="'.LINK.'client/add" method="post">';
echo '<input type="hidden" name="'.$clientAddCsrfField.'" value="'.$clientAddCsrfToken.'" />';


echo '<div class="form-group">';
echo '<label for="exampleInputEmail1">'.__("Name of the client").'</label>';
echo Form::input("client", "libelle", array("class"=>"form-control", "placeholder" => __("Name of the client")));
echo '<br />';
echo '<button type="submit" class="btn btn-primary">'.__("Add").'</button>';
echo '</div>';
echo '</form>';

