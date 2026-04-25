<!-- Split button -->
<div class="btn-group">
  
    <?php 

    $data = $data ?? array();
    $proxysql_list = is_array($data['proxysql'] ?? null) ? $data['proxysql'] : array();
    $id_proxysql_server = $data['id_proxysql_server'] ?? '';
    $selected_proxysql = $proxysql_list[$id_proxysql_server] ?? null;

    echo '<button type="button" class="btn btn-default">';
     echo '<img src="'.IMG.'icon/proxysql.png" height="18px" width="18px">';
     echo ' <span title="Production" class="label label-danger">P</span> <b>';
    if (is_array($selected_proxysql)) {
        echo $selected_proxysql['display_name'].'</b> ';
        echo $selected_proxysql['hostname'].':'.$selected_proxysql['port']
            .' v'.($selected_proxysql['version'] ?? 'N/A');
    } else {
        echo __('ProxySQL').'</b>';
        if ($id_proxysql_server !== '') {
            echo ' #'.htmlspecialchars((string) $id_proxysql_server, ENT_QUOTES, 'UTF-8');
        }
    }

     echo '</button>';
    ?>
   
  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span class="caret"></span>
    <span class="sr-only">Toggle Dropdown</span>
  </button>
  <ul class="dropdown-menu">
    <?php
    $current_config_tab = $data['current_config_tab'] ?? 'MYSQL_SERVERS';
    $menu_current = strtolower((string) ($data['param']['menu_current'] ?? 'config'));

    foreach($proxysql_list as $proxysql_server)
    {
        if ($menu_current === 'config') {
            $link = LINK.'ProxySQL/config/'.$proxysql_server['id'].'/'.$current_config_tab;
        } else {
            $link = LINK.'ProxySQL/'.$menu_current.'/'.$proxysql_server['id'];
        }
        echo '<li><a href="'.$link.'"><img src="'.IMG.'icon/proxysql.png" height="18px" width="18px"> ';
        echo '<span title="Production" class="label label-danger">P</span> <b>'
        .$proxysql_server['display_name'].'</b> '.$proxysql_server['hostname'].':'.$proxysql_server['port'].' v'.$proxysql_server['version'].'</a></li>';
    }
    
    ?>

  </ul>
</div>

<?php

//boutton
echo '<div class="btn-group" role="group" aria-label="Default button group">';

$menu_items = is_array($data['menu'] ?? null) ? $data['menu'] : array();
$menu_current = $data['param']['menu_current'] ?? '';

foreach($menu_items as $key => $menu)
{

  $active = '';
  if ($menu_current == $key)
  {
    $active = 'active';
  }
  echo '<a href="'.$menu['link'].'" type="button" class="btn btn-default '.$active.'">'.$menu['title'].'</a> ';
}


/*
echo '<a href="'.LINK.'ProxySQL/auto/'.$data['id_proxysql_server'].'" type="button" class="btn btn-default">'.__('Auto config').'</a> ';
echo '<a href="'.LINK.'ProxySQL/config/'.$data['id_proxysql_server'].'/MYSQL_SERVERS" type="button" class="active btn btn-default">'.__('Configuration').'</a> ';
echo '<a href="'.LINK.'ProxySQL/statistic/'.$data['id_proxysql_server'].'" type="button" class="btn btn-default">'.__('Statistics').'</a> ';
echo '<a href="'.LINK.'ProxySQL/monitor/'.$data['id_proxysql_server'].'" type="button" class="btn btn-default">'.__('Monitor').'</a> ';
echo '<a href="'.LINK.'ProxySQL/add" type="button" class="btn btn-default">'.__('Cluster').'</a> ';
echo '<a href="'.LINK.'ProxySQL/add" type="button" class="btn btn-default">'.__('Logs').'</a> ';
*/
echo '</div>';


/*

    <li><a href="#">Another action</a></li>
    <li><a href="#">Something else here</a></li>
    <li role="separator" class="divider"></li>
    <li><a href="#">Separated link</a></li>

    */
