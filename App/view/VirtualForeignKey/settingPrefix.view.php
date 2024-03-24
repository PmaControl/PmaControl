<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use \App\Library\Display;


echo __ ("The goal there is to ignore the prefix of table to establish the mapping between tables.")."<br />";
echo __("example :")."<br />";

echo "MYTABLE.id_MYTABLE_2 => PREFIX_MYTABLE_2.id_MYTABLE_2<br/>";
echo __("To be able to match the system have to know witch prefix are used on your database")."<br />";


echo '<table class="table table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>#</th>';
echo '<th>'.__('Server').'</th>';
echo '<th>'.__('Database').'</th>';
echo '<th>'.__('Prefix').'</th>';
echo '</tr>';

$i = 0;
foreach ($data['prefix'] as $prefix) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td class="line-edit" data-name="name" data-pk="'.$prefix['id_mysql_server'].'" data-type="text" data-url="'.LINK.'tag/update" data-title="Enter Libelle">'.Display::srv($prefix['id_mysql_server']).'</td>';
    echo '<td class="line-edit" data-name="color" data-pk="'.$prefix['id'].'" data-type="text" data-url="'.LINK.'tag/update" data-title="Enter Color">'.$prefix['database_name'].'</td>';
    echo '<td class="line-edit" data-name="background" data-pk="'.$prefix['id'].'" data-type="text" data-url="'.LINK.'tag/update" data-title="Enter Color">'.$prefix['prefix'].'</td>';
    echo '</tr>'."\n";
}
echo '</table>';


//echo '<input id="demo" type="text" class="form-control" value="rgb(255, 128, 0)" />';



echo '<a href="'.LINK.'VirtualForeignKey/add" class="btn btn-primary">'.__("Add a prefix").'</a>';