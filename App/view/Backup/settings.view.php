<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Html\Form\Form;
use App\Library\Display;

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

if (empty($data['storage_area'])) {

    echo __("Before to schedule a backup, you must add an array of stockage :")." ";
    echo '<a href="'.LINK.'StorageArea/index/" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus" style="font-size:12px" aria-hidden="true"></span> Add an storage area</a>';
} else {

    echo '<form action="" method="post">';
    echo '<table class="table table-bordered table-striped" id="table">';

    echo '<tr>';

    echo '<th rowspan="2">'."#".'</th>';
    echo '<th rowspan="2">'.__("Server").'</th>';
    echo '<th rowspan="2">'.__("Database").'</th>';
    echo '<th rowspan="2">'.__("Storage area").'</th>';
    echo '<th rowspan="2">'.__("Tools").'</th>';
    echo '<th colspan="5">'.__("Shedule").'</th>';
    echo '<th rowspan="2" colspan="2">'.__("Actions").'</th>';
    echo '</tr>';

    echo '<tr>';

//echo '<th>' . __("Size") . '</th>';

    echo '<th>'.__("Minutes").'</th>';
    echo '<th>'.__("Hours").'</th>';
    echo '<th>'.__("Day of month").'</th>';
    echo '<th>'.__("Month").'</th>';
    echo '<th>'.__("Day of week").'</th>';
    echo '</tr>';

    Form::setIndice(true);

    $i = 0;
    foreach ($data['backup_list'] as $backup_list) {
        $i++;

        echo '<tr class="edit">';
        echo '<td>'.$i.'</td>';
        echo '<td>'.Display::server($backup_list).'</td>';
        echo '<td>';

        if (empty($backup_list['database'])) {
            echo "<i>ALL DATABASES</i>";
        } else {
            echo $backup_list['database'];
        }

        echo '</td>';
        echo '<td>'.$backup_list['bakcup_server'].'</td>';
        //echo '<td>' . 0 . '</td>';
        //echo '<td>'.$backup_list['nas'].'</td>';
        echo '<td>'.$backup_list['backup_type'].'</td>';
        echo '<td class="input">'.$backup_list['minute'].'</td>';
        echo '<td class="input">'.$backup_list['hour'].'</td>';
        echo '<td class="input">'.$backup_list['day_of_month'].'</td>';
        echo '<td class="input">'.$backup_list['month'].'</td>';
        echo '<td class="input">'.$backup_list['day_of_week'].'</td>';

        echo '<td>';

        if ($backup_list['is_active'] == "0") {
            $class = 'btn-warning';
            $icon  = 'glyphicon-stop';
            $text  = __("Desactived");
        } else {
            $class = 'btn-success';
            $icon  = 'glyphicon-play';
            $text  = __("Actived");
        }




        echo ' <a href="'.LINK.'backup/toggleShedule/'.$backup_list['id'].'" class="btn '.$class.' delete-item"><span class="glyphicon '.$icon.'" style="font-size:12px"></span> '.$text.'</a>';
        echo ' ';

        echo ' <a href="'.LINK.'backup/editShedule/'.$backup_list['id'].'" class="btn btn-primary delete-item"><span class="glyphicon glyphicon-edit" style="font-size:12px"></span> '.__('Edit').'</a>';
        echo ' ';
        echo '<a href="'.LINK.'backup/deleteShedule/'.$backup_list['id'].'" class="btn btn-danger delete-item"><span class="glyphicon glyphicon-trash" style="font-size:12px"></span> '.__("Delete").'</a>';
        echo '</td>';
        echo '</tr>';
    }




    /*
      echo '<tr id="tr-'.$i.'" class="blah">';
      echo '<td>#</td>';
      echo '<td>';

      \Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id",array("data-width"=>"auto")));
      //.Form::autocomplete("backup_database", "id_mysql_server", array("class" => "server form-control")).

      echo '</td>';
      //echo '<td>'.Form::autocomplete("backup_database", "id_mysql_server_2", array("class" => "ip form-control", "style" => "width:150px")).'</td>';
      echo '<td>';


      echo Form::select("backup_database", "id_mysql_database", $data['databases'], "", array("class" => "form-control", "data-live-search" => "true", "class" => "selectpicker","data-width"=>"100%"));

      echo '</td>';
      //echo '<td>' . 0 . '</td>';
      echo '<td>'.Form::select("backup_database", "id_backup_storage_area", $data['storage_area'], "", array("class" => "form-control")).'</td>';
      echo '<td>'.Form::select("backup_database", "id_backup_type", $data['type_backup'], "", array("class" => "form-control")).'</td>';
      echo '<td>'.Form::input("crontab", "minutes", array("class" => "form-control", "style" => "width:40px")).'</td>';
      echo '<td>'.Form::input("crontab", "hours", array("class" => "form-control", "style" => "width:40px")).'</td>';
      echo '<td>'.Form::input("crontab", "day_of_month", array("class" => "form-control", "style" => "width:40px")).'</td>';
      echo '<td>'.Form::input("crontab", "month", array("class" => "form-control", "style" => "width:40px")).'</td>';
      echo '<td>'.Form::input("crontab", "day_of_week", array("class" => "form-control", "style" => "width:40px")).'</td>';
      echo '<td>';
      echo '<a href="#" class="btn btn-danger delete-line"><span class="glyphicon glyphicon-trash" style="font-size:12px"></span> '.__("Delete").'</a>';
      echo '</td>';
      echo '</tr>';
     *
     */

    Form::setIndice(false);

    echo '</table>';

    echo '<a href="'.LINK.'backup/add" class="btn btn-primary"><span class="glyphicon glyphicon glyphicon-plus" style="font-size:12px"></span> '.__("Add a backup").'</a>';
    //echo '<a href="#" id="add" class="btn btn-primary"><span class="glyphicon glyphicon glyphicon-plus" style="font-size:12px"></span> '.__("Add a backup").'</a>';
    //echo ' - <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok" style="font-size:12px"></span> '.__("Save").'</button>';


    echo '</form>';
}