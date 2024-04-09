<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

echo '<table>';
echo '<tr>';
echo '<th>'.__("Top").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.__("Hostname").'</th>';
echo '<th>'.__("Port").'</th>';
echo '<th>'.__("Login").'</th>';
echo '<th>'.__("Password").'</th>';
echo '</tr>';


foreach($data['proxysql'] as $proxysql)
{
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$proxysql['displa_name'].'</td>';
    echo '<td>'.$proxysql['hostname'].'</td>';
    echo '<td>'.$proxysql['login'].'</td>';
    echo '<td>'.$proxysql['password'].'</td>';
    echo '</tr>';

}

echo '</table>';


echo '<a href="'.LINK.'ProxySQL/add" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> ddAdd a ProxySQL (Admin)</a>';


