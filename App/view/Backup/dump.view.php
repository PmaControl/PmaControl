<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function human_filesize($bytes, $decimals = 2)
{
    $sz = 'KMGTP';
    $factor = floor((strlen($bytes) -1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " ".@$sz[$factor] . "o";
}


echo '<table class="table">';


echo '<tr>';
echo '<th>' . __('ID') . '</th>';
echo '<th>' . __('Server') . '</th>';
echo '<th>' . __('IP') . '</th>';
echo '<th>' . __('Database') . '</th>';

//echo '<th>' . __('Size') . '</th>';
echo '<th>' . __('Size') . '</th>';
echo '<th>' . __('Time') . '</th>';
echo '<th>' . __('Date start') . '</th>';
echo '<th>' . __('Date end') . '</th>';
echo '<th>' . __('Location') . '</th>';
echo '<th>' . __('Hash') . '</th>';
echo '<th>' . __('Master file') . '</th>';
echo '<th>' . __('Master position') . '</th>';
echo '<th>' . __('Slave file') . '</th>';
echo '<th>' . __('Slave position') . '</th>';
echo '<th>' . __('Donwload') . '</th>';
echo '<th>' . __('Load') . '</th>';
echo '</tr>';


$date = "";


foreach ($data['dump'] as $dump) {
    
    
    if (!empty($date) && $date != substr($dump['date_start'], 0,10))
    {
        echo '<tr>';
        echo '<td colspan="16" style="border-bottom:#333 1px solid; height:1px"  ></td>';
        echo '</tr>';
    }

    $date = substr($dump['date_start'], 0,10);
    
    
    echo '<tr>';
    echo '<td>' . $dump['id_dump'] . '</td>';
    echo '<td>' . str_replace('_', '-', $dump['server_name']) . '</td>';
    echo '<td>' . $dump['ip'] . '</td>';
    echo '<td><a href="'.LINK.'backup/graph/'.$dump['id_backup'].'">' . $dump['database'] . '</a></td>';

    echo '<td>' . human_filesize($dump['size_file'], 2) . '</td>';
    //echo '<td>' . human_filesize(shell_exec("stat -c %s ".$dump['file_name'])) . '</td>';
    echo '<td>' . $dump['time'] . '</td>';
    echo '<td>' . $dump['date_start'] . '</td>';
    echo '<td>' . $dump['date_end'] . '</td>';
    echo '<td><img src="' . IMG . 'country/type1/fr.gif" /> '.$dump['libelle'].' ('.$dump['ip_nas'].')</td>';

    echo '<td>' . $dump['md5'] . '</td>';
    
    $master = json_decode($dump['master_data'], true);
    
    echo '<td>' . $master['File']  . '</td>';
    echo '<td>' . $master['Position']  . '</td>';
    
        $slave = json_decode($dump['slave_data'], true);
    
    echo '<td>' . $slave[0]['Master_Log_File']  . '</td>';
    echo '<td>' . $slave[0]['Exec_Master_Log_Pos']  . '</td>';
    echo '<td>';

    $dispo = false;
    if (file_exists($dump['file_name'])) {
        echo '<a href="' . LINK . 'backup/getDump/' . $dump['id_dump'] . '"><span style="font-size:14px; color:#000" class="glyphicon glyphicon-cloud-download"></span></a>';
        $dispo = true;
    } elseif (file_exists($dump['file_name'] . ".gz")) {
        echo '<a href="' . LINK . 'backup/getDump/' . $dump['id_dump'] . '"><span style="font-size:14px; color:#000" class="glyphicon glyphicon-compressed"></span></a>';
        $dispo = true;
    }

    echo '</td>';


    echo '<td>';
    if ($dispo) {
        echo '<a href="' . LINK . 'backup/putDump/' . $dump['id_dump'] . '"><span style="font-size:14px; color:#000" class="glyphicon glyphicon-export"></span></a>';
    }echo '</td>';


    echo '</tr>';
}

echo '</table>';

