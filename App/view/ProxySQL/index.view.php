<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


//debug($data['servers']);
echo '<div class="row" style="padding:10px; margin: 5px;">';
//echo '<div class="col-md-6">This is a list of SSH keys associated with your account. Remove any keys that you do not recognize.</div>';

echo '<div class="col-md-12" style="text-align:right">';
echo '<a href="'.LINK.'ssh/add" type="button" class="btn btn-success">'.__('New ProxySQL Server').'</a>';
echo '</div>';
echo '</div>';

if ( ! empty($proxysql))
{
    foreach ($data['proxysql'] as $proxysql) {
        echo '<div class="row" style="font-size:14px; border:#666 1px solid; padding:10px; margin: 10px 5px 0 5px; border-radius: 3px;">';

        echo '<div class="col-md-2 text-center">';
        echo '<img src="'.IMG.'icon/proxysql.png" height="64px" width="64px">';
        echo '</div>';

        echo '<div class="col-md-2">';
        echo "<b>".$proxysql['hostname'].":".$proxysql['port']."</b>";
        echo '</div>';

        echo '<div class="col-md-2">';
        echo $proxysql['login'];
        echo '</div>';

        echo '<div class="col-md-2">';
        echo $proxysql['password'];
        echo '</div>';

        echo '</div>';
    }
}