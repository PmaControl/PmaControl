<?php

use \App\Library\Display;
use \App\Library\Html;

//debug($data['proxysql']);
echo '<div class="row" style="padding:10px; margin: 5px;">';
//echo '<div class="col-md-6">This is a list of SSH keys associated with your account. Remove any keys that you do not recognize.</div>';

echo '<div class="col-md-12" style="text-align:right">';
echo '<a href="'.LINK.'ProxySQL/add" type="button" class="btn btn-success">'.__('New ProxySQL Server').'</a>';
echo '</div>';
echo '</div>';


if ( ! empty($data['proxysql']))
{
    foreach ($data['proxysql'] as $id_proxysql => $proxysql) {
        echo '<div class="row" style="font-size:14px; border:#666 1px solid; padding:10px; margin: 10px 5px 0 5px; border-radius: 3px;">';

        $id_proxysql++;
        
        echo '<div class="col-md-5">';

        echo '<div class="row">';
        echo '<div class="col-md-1 text-center" style="display: flex; justify-content: center; align-items: center; ">';
        echo '<img src="'.IMG.'icon/proxysql.png" height="48px" width="48px">';
        echo '</div>';

        echo '<div class="col-md-5">';
        echo 'ProxySQL Admin <b><a href="">'.$proxysql['hostname'].":".$proxysql['port']."</a></b>";
        echo '</div>';

        echo '<div class="col-md-5">';
        echo "Login: ".$proxysql['login'];
 
        echo " - Password : ".$proxysql['password'];
        echo '</div>';
        
        echo '</div>';
        echo '<div class="row">&nbsp;</div>';
        echo '<div class="row">';
        //boutton
        echo '<a href="'.LINK.'ProxySQL/auto/'.$proxysql['id'].'" type="button" class="btn btn-default">'.__('Auto config').'</a> ';
        echo '<a href="'.LINK.'ProxySQL/config/'.$proxysql['id'].'" type="button" class="btn btn-danger">'.__('Configuration').'</a> ';
        echo '<a href="'.LINK.'ProxySQL/statistic/'.$proxysql['id'].'" type="button" class="btn btn-success">'.__('Statistics').'</a> ';
        echo '<a href="'.LINK.'ProxySQL/monitor/'.$proxysql['id'].'" type="button" class="btn btn-warning">'.__('Monitor').'</a> ';
        echo '<a href="'.LINK.'ProxySQL/add" type="button" class="btn btn-primary">'.__('Cluster').'</a> ';
        echo '<a href="'.LINK.'ProxySQL/add" type="button" class="btn btn-primary">'.__('Logs').'</a> ';
        echo '</div>';
        echo '<div class="row">&nbsp;</div>';
        

        //frontend

        if (! empty($proxysql['id_mysql_server']))
        {
            echo '<div class="row">';
            $thead = Html::thead(array(__("Hostname"), __("Status")));
            if (empty($proxysql['mysql_available'])) {
                $status = '<big><span class="label label-danger">'.$proxysql['mysql_error'].'</span></big>';
            }
            elseif ($proxysql['mysql_available'] === "1"){
                $status = '<big><span class="label label-success">'.__("ONLINE").'</span></big>';
            }

            $tbody = Html::tbody(array(Display::srv($proxysql['id_mysql_server']),$status));
            $body = Html::table(
                $thead,
                $tbody
            );
            echo Html::box(__('Frontend'),$body );
            echo '</div>';
        }

        if (!empty($data['proxysql_error']))
        {
            echo '<div class="row">';
            $keys = array_keys(end($data['proxysql_error']));

            $tbody = '';
            foreach($data['proxysql_error'] as $line)
            {
                $tbody .= Html::tbody($line);
            }

            echo Html::box(__('Error'),
                Html::table(
                    Html::thead($keys),
                    $tbody
                )
            );
            echo '</div>';
            //error
        }

        /******************** */



        /*********** */


        echo '</div>';

        echo '<div class="col-md-7">';




        //backend
        echo '<div class="panel panel-primary">';
        echo '<div class="panel-heading">';
        echo '<h3 class="panel-title">'.__('Backend').'</h3>';
        echo '</div>';

        echo '<table class="table table-condensed table-bordered table-striped" id="table">';
        echo '<tr>';
        echo '<th>'.__("Hostgroup").'</th>';
        echo '<th>'.__("Hostname").'</th>';
        echo '<th>'.__("Status").'</th>';
        echo '</tr>';


        foreach($proxysql['servers'] as $server)
        {
            echo '<tr>';
            echo '<td>'.$server['hostgroup_id'].'</td>';
            echo '<td><a href="">'.$server['hostname'].':'.$server['port'].'</a></td>';
            
            switch($server['status'])
            {
                case 'SHUNNED': $class='danger'; break;
                case 'ONLINE': $class='success'; break;
                case 'OFFLINE_SOFT': $class='warning'; break;
            }

            echo '<td><big><span class="label label-'.$class.'">'.__($server['status']).'</span></big></td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}