<?php
echo '<table class="table table-condensed table-bordered table-striped" id="table">';


echo '<tr>';

echo '<th>'.__("Top").'</th>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Available SSH").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.__("Hostname").'</th>';
echo '<th>'.__("IP").'</th>';


echo '<th>'.__("Operations system").'</th>';
echo '<th>'.__("Product name").'</th>';
echo '<th>'.__("Arch").'</th>';
echo '<th>'.__("Kernel").'</th>';
echo '<th>'.__("Processor").'</th>';
echo '<th>'."Mhz".'</th>';

echo '<th>'.__("Memory").'</th>';
echo '<th>Swapiness</th>';
//echo '<th title="0.75*CPU*GHZ + 0.5 Memory Go">' . __("Indice") . '</th>';
echo '</tr>';


$i = 0;


foreach ($data['servers'] as $server) {
    $i++;


    /*
      if (empty($server['operating_system'])) {
      continue;
      } */

    $style = "";
    if (empty($server['ssh_available']) && $server['is_monitored'] == "1") {
        $style = 'background-color:#d9534f; color:#FFFFFF';
        
    }


    echo '<tr>';
    echo '<td style="'.$style.'">'.$i.'</td>';
    echo '<td style="'.$style.'">'.$server['id'].'</td>';
    echo '<td style="'.$style.'">';
    echo '<span class="glyphicon '.($server['ssh_available'] == 1 ? "glyphicon-ok" : "glyphicon-remove").'" aria-hidden="true"></span>';
    echo '</td>';
    echo '<td style="'.$style.'">'.str_replace('_', '-', $server['name']).'</td>';
    echo '<td style="'.$style.'">'.$server['hostname'].'</td>';
    echo '<td style="'.$style.'">'.$server['ip'].'</td>';
    echo '<td style="'.$style.'">';

    if (!empty($server['distributor'])) {
        echo '<img src="'.IMG.'/os/'.strtolower($server['distributor']).'.png" alt="['.$server['distributor'].']" title="'.$server['distributor'].'" style="width:16px;height:16px;vertical-align:middle;"> ';
    }

    
    echo $server['operating_system'].'</td>';
    echo '<td style="'.$style.'">'.$server['product_name'].'</td>';
    $class = ("i686" == $server['arch']) ? "error" : "";
    echo '<td style="'.$style.'" class="'.$class.'">'.$server['arch'].'</td>';
    echo '<td style="'.$style.'">'.$server['kernel'].'</td>';
    echo '<td style="'.$style.'">'.$server['processor'].'</td>';
    echo '<td style="'.$style.'">'.$server['cpu_mhz'].'</td>';
    echo '<td style="'.$style.'">'.round($server['memory_kb'] / 1024 / 1024, 2).' Go</td>';
    echo '<td style="'.$style.'">'.$server['swappiness'].' </td>';
    //echo '<td style="' . $style . '">' . round(0.75 * $server['processor'] * ($server['cpu_mhz'] / 1024) + 0.5 * ($server['memory_kb'] / 1024 / 1024), 2) . '</td>';


    echo '</tr>';
}

echo '</table>';

