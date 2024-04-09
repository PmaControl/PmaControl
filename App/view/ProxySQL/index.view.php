<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


//debug($data['proxysql']);
echo '<div class="row" style="padding:10px; margin: 5px;">';
//echo '<div class="col-md-6">This is a list of SSH keys associated with your account. Remove any keys that you do not recognize.</div>';

echo '<div class="col-md-12" style="text-align:right">';
echo '<a href="'.LINK.'ProxySQL/add" type="button" class="btn btn-success">'.__('New ProxySQL Server').'</a>';
echo '</div>';
echo '</div>';

if ( ! empty($data['proxysql']))
{
    foreach ($data['proxysql'] as $proxysql) {
        echo '<div class="row" style="font-size:14px; border:#666 1px solid; padding:10px; margin: 10px 5px 0 5px; border-radius: 3px;">';

        echo '<div class="col-md-1 text-center" style="display: flex; justify-content: center; align-items: center; ">';
        echo '<img src="'.IMG.'icon/proxysql.png" height="96px" width="96px">';
        echo '</div>';

        echo '<div class="col-md-2">';
        echo '<b><a href="">'.$proxysql['hostname'].":".$proxysql['port']."</a></b>";
        echo '</div>';

        echo '<div class="col-md-1">';
        echo $proxysql['login'];
        echo '</div>';

        echo '<div class="col-md-1">';
        echo $proxysql['password'];
        echo '</div>';

        echo '<div class="col-md-7">';


?>
        <div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Backend') ?></h3>
    </div><?php
        echo '<table class="table table-condensed table-bordered table-striped" id="table">';
        echo '<tr>';
        echo '<th>'.__("Hostgroup").'</th>';
        echo '<th>'.__("Hostname").'</th>';
        echo '<th>'.__("Status").'</th>';
        echo '</tr>';


        foreach($proxysql['servers'] as $server)
        {
            echo '<tr>';
            echo '<td><a href="">'.$server['hostgroup_id'].'</a></td>';
            echo '<td><a href="">'.$server['hostname'].':'.$server['port'].'</a></td>';
            
            switch($server['status'])
            {
                case 'SHUNNED': $class='danger';$class='primary'; break;
                case 'ONLINE': $class='success'; break;
            }


            echo '<td><big><span class="label label-'.$class.'">'.$server['status'].'</span></big></td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}