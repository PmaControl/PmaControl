<?php

\Glial\Synapse\FactoryController::addNode("StorageArea", "menu");


function format($bytes, $decimals = 2)
{
    $sz     = 'KMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))." ".@$sz[$factor]."o";
}
echo '<table class="table table-bordered table-striped">';

echo '<tr>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.__("IP").'</th>';
echo '<th>'.__("Location").'</th>';
echo '<th>'.__("Path").'</th>';
echo '<th>'.__("Size").'</th>';
echo '<th>'.__("Used").'</th>';
echo '<th>'.__("Available").'</th>';
echo '<th>'.__("Percent").'</th>';
echo '<th>'.__("Space used").'</th>';
echo '<th>'.__("Tools").'</th>';

echo '</tr>';




foreach ($data['storage'] as $storage) {
    echo '<tr>';
    echo '<td>'.$storage['id_backup_storage_area'].'</td>';
    echo '<td>'.$storage['name'].'</td>';
    echo '<td>'.$storage['ip'].':'.$storage['port'].'</td>';
    echo '<td><img class="country" src="'.IMG.'country/type1/'.strtolower($storage['iso']).'.gif" widtd="18" height="12"> '.$storage['city'].'</td>';
    echo '<td>'.$storage['path'].'</td>';


    if (empty($data['space']) || empty($data['space'][$storage['id_backup_storage_area']])) {
        echo '<td style="background:#FCF8E3" colspan="5">'.__("N/A").' ('.__("The daemon didn't checked this storage area or the daemon is stoped").')</td>';
    } else {
        echo '<td>'.format($data['space'][$storage['id_backup_storage_area']]['size']).'</td>';
        echo '<td>'.format($data['space'][$storage['id_backup_storage_area']]['used']).'</td>';
        echo '<td>'.format($data['space'][$storage['id_backup_storage_area']]['available']).'</td>';

        echo '<td>'.$data['space'][$storage['id_backup_storage_area']]['percent']."%".'</td>';

        $percent = $data['space'][$storage['id_backup_storage_area']]['percent'];
        $percent_backup = floor($data['space'][$storage['id_backup_storage_area']]['backup'] / $data['space'][$storage['id_backup_storage_area']]['size'] * 100);
        $percent_other  = $percent - $percent_backup;


        echo '<td>';
        echo '<div class="progress" style="margin-bottom:0">

  <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$data['space'][$storage['id_backup_storage_area']]['percent'].'" aria-valuemin="0" aria-valuemax="100" style="width: '.$percent_other.'%">
    <span class="sr-only">'.$data['space'][$storage['id_backup_storage_area']]['percent'].'% Complete (success)</span>
  </div>
  <div class="progress-bar progress-bar-warning progress-bar-striped" style="width: '.$percent_backup.'%">
    <span class="sr-only">'.$percent_backup.'% Complete (warning)</span>
  </div>
</div>';

        echo '</td>';
        
    }
    echo '<td><a href="'.LINK.'StorageArea/delete/'.$storage['id_backup_storage_area'].'" class="btn btn-danger delete-line"><span class="glyphicon glyphicon-trash" style="font-size:12px"></span> '.__("Delete").'</a></td>';
    echo '</tr>';
}




echo "</table>";


$percent_backup = "66";

echo 'This part correspond to the part used by the backups on the partition : <div class="progress" style="margin-bottom:0; width:200px">
        
  <div class="progress-bar progress-bar-warning progress-bar-striped" style="width: '.$percent_backup.'%">
    <span class="sr-only">20% Complete (warning)</span>
  </div>
</div>';
