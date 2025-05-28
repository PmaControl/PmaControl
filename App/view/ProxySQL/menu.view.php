<!-- Split button -->
<div class="btn-group">
  
    <?php 

    echo '<button type="button" class="btn btn-default">';
     echo '<img src="'.IMG.'icon/proxysql.png" height="18px" width="18px">';
     echo ' <span title="Production" class="label label-danger">P</span> <b>'
     .$data['proxysql'][$data['id_proxysql_server']]['display_name'].'</b> ';
     echo $data['proxysql'][$data['id_proxysql_server']]['hostname'].':'.$data['proxysql'][$data['id_proxysql_server']]['port']
     .' v'.$data['proxysql'][$data['id_proxysql_server']]['version'];

     echo '</button>';
    ?>
   
  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span class="caret"></span>
    <span class="sr-only">Toggle Dropdown</span>
  </button>
  <ul class="dropdown-menu">
    <?php
    foreach($data['proxysql'] as $proxysql_server)
    {
        $data['param'][0] = $proxysql_server['id'];
        $link = implode('/', $data['param']);
        echo '<li><a href="'.LINK.'ProxySQL/config/'.$link.'"><img src="'.IMG.'icon/proxysql.png" height="18px" width="18px"> ';
        echo '<span title="Production" class="label label-danger">P</span> <b>'
        .$proxysql_server['display_name'].'</b> '.$proxysql_server['hostname'].':'.$proxysql_server['port'].' v'.$proxysql_server['version'].'</a></li>';
    }
    
    ?>

  </ul>
</div>

<?php

//boutton
echo '<div class="btn-group" role="group" aria-label="Default button group">';

foreach($data['menu'] as $key => $menu)
{

  $active = '';
  if ($data['param']['menu_current'] == $key)
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