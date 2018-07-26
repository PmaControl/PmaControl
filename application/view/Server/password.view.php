<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;


echo '<form action="" method="post">';


echo Form::input('mysql_server', 'login', array('class'=>'form-control'));
echo Form::input('mysql_server', 'passwd', array('class'=>'form-control', 'type'=>'password'));


echo '<button type="submit" class="btn btn-primary">change password</button>';



echo '</form>';