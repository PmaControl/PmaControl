<?php

use Glial\Html\Form\Form;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * login
 * password
 * sudo su -
 * 
 * login public key => generate auto ?
 * login private key
 * private key
 *
 * select server
 *
 */
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

echo '<form action="" method="post" class="form-inline">';
echo '<div class="well">';


echo '<div class="form-group">';
echo "Login SSH : ";
echo Form::input("mysql_server", "login_ssh", array("class"=>"form-control", "style"=>"width:200px"));

echo " - Password SSH : ";
echo Form::input("mysql_server", "password_ssh", array("class"=>"form-control", "style"=>"width:200px", "type"=>"password"));

echo " - KEY private : ";

echo '<textarea name="mysql_server[key_ssh]" class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>';

echo ' <button type="submit" class="btn btn-primary">Deploy</button>';
echo '</div>';

echo '</div>';


echo '<table class="table table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th><input id="checkAll" type="checkbox" onClick="toggle(this)" /> '.__("Deploy").'</th>';
echo '<th>'.__('Top').'</th>';
echo '<th>'.__('ID').'</th>';
echo '<th>'.__('MySQL').'</th>';
echo '<th>'.__('SSH').'</th>';
echo '<th>'.__('Client').'</th>';
echo '<th>'.__('Environment').'</th>';

echo '<th>'.__('Tags').'</th>';
echo '<th>'.__('Name').'</th>';
echo '<th>'.__('IP').'</th>';
echo '<th>'.__('Port').'</th>';

echo '</tr>';

$i     = 0;
$style = '';

Form::setIndice(true);

foreach ($data['servers'] as $server) {

    $i++;
    echo '<tr>';

    $checked = $server['is_monitored'] == 1 ? 'checked="checked"' : '';
    $checked = '';

    echo '<td style="'.$style.'">'
    .'<input type="checkbox" name="mysql_server['.($i - 1).'][is_monitored]" '.$checked.' />'.'</td>';

    echo '<td>'.$i.'</td>';
    echo '<td>'.$server['id'];
    //print_r($server);
    echo '<input type="hidden" name="id['.($i - 1).']" value="'.$server['id'].'" />';

    echo '</td>';

    echo '<td style="'.$style.'">';
    echo '<span class="glyphicon '.(empty($server['error']) ? "glyphicon-ok" : "glyphicon-remove").'" aria-hidden="true"></span>';
    echo '</td>';

    echo '<td style="'.$style.'">';
    echo '<span class="glyphicon '.(empty($server['ssh_available']) ? "glyphicon-remove" : "glyphicon-ok").'" aria-hidden="true"></span>';
    echo '</td>';


    echo '<td>'.$server['name'].'</td>';
    echo '<td>'.$server['name'].'</td>';


    echo '<td>'.__('Tags').'</td>';
    echo '<td>'.$server['name'].'</td>';


    echo '<td>'.$server['ip'].'</td>';
    echo '<td>'.$server['port'].'</td>';
    echo '</tr>'."\n";
}

Form::setIndice(false);

echo '</table>';

echo '<input type="hidden" name="settings" value="1" />';
echo '<button type="submit" class="btn btn-primary">'.__("Deploy").'</button>';
echo '</form>';
