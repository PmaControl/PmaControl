<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo '<table class="table table-bordered table-striped" id="table">';


echo '<tr>';


echo '<th>' . __("Top") . '</th>';
echo '<th>' . __("Ressources") . '</th>';

foreach(end($data['export']) as $role => $_access)
{
    echo '<th>' .$role . '</th>';
}


echo '</tr>';

$i = 0;

foreach($data['export'] as $ressourse => $pair)
{
    echo '<tr>';
    $i++;
    echo '<td>'.$i.'</td>';
    echo '<td>'.$ressourse.'</td>';
    
    foreach($pair as $role => $access)
    {
        echo '<td>';
        
        if ($access)
        {
            echo '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
        }
        
        echo '</td>';
    }
    
    echo '</tr>';
    
    
    
}