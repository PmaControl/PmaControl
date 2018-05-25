<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>


<div class="row-fluid">

<?php
echo '<div class="span2">';


echo '<div class="well" style="overflow : scroll;">';
echo '<h3>All servers</h3>';
echo '<h3>Array of servers</h3>';
echo '<h3>Servers</h3>';

echo '<ul>';
foreach($data['server'] as $server)
{
    echo '<li><a href="#phpmyadmin"><img src="http://www.easyphp.org/images_easyphp/favicon_phpmyadmin.png" height="16" width="16" /></a>'
    . ' <span title="'.$server['ip'].':'.$server['port'].'"><a href="">'.str_replace('_', '-',$server['name'])."</a> (".$server['ip'].")</span></li>";
}
echo '</ul>';

echo '</div>';
echo '</div>';
echo '<div class="span10">';

echo '<div class="well">';


echo '</div>';
echo '</div>';

?>
</div>
