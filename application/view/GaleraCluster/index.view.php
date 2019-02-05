<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo '<table class="table table-condensed table-bordered table-striped">';

echo '<tr>';
echo '<th>' . '#' . '</th>';
echo '<th>' . __('Node') . '</th>';
echo '<th>' . __('Hostname') . '</th>';


echo '<th>' . __('IP') . '</th>';

echo '<th>' . __('Version') . '</th>';
echo '<th>' . __('Date') . '</th>';
echo '<th>' . __('Time zone') . '</th>';
echo '<th>' . __('Cluster_status') . '</th>';
echo '<th>' . __('Local state') . '</th>';
echo '<th>' . __('Desync') . '</th>';
echo '</tr>';


$i = 1;
foreach ($data['galera'] as $cluster_name => $galera) {



    $j = 0;
    foreach ($galera as $node) {


        if ($j === 0) {
            
            $rowspan = count($galera);
            
            echo '<tr>';
            echo '<td class="pma-grey" rowspan="'.($rowspan+1).'"><b>' . $i . '<b></td>';
            
            echo '<td class="pma-grey" colspan="10">Cluster name : <b>' . $cluster_name . '</b> - Cluster size : <b>'.$rowspan.'</b></td>';
            echo '</tr>';
        }


        $j++;


        if (!empty($node['hostname'])) {
            echo '<tr>';
            echo '<td>' . $j . '</td>';
            
            if ($node['is_available'] === "1")
            {
                $class = "ok";
            }
            else
            {
                $class = "remove";
            }
            
            echo '<td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ' . $node['hostname'] . '</td>';
            echo '<td>' . $node['ip'] . ':' . $node['port'] . '</td>';
            echo '<td>' . $node['version'] . '</td>';
            echo '<td>' . $node['date'] . '</td>';
            echo '<td>' . $node['system_time_zone'] . '</td>';
            echo '<td>' . $node['wsrep_cluster_status'] . '</td>';
            echo '<td>' . $node['wsrep_local_state_comment'] . '</td>';
            echo '<td>' . $node['wsrep_desync'] . '</td>';
            echo '</tr>';
        }
        else
        {
                        echo '<tr>';
            echo '<td>' . $j . '</td>';

            echo '<td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ' . "Arbitrator" . '</td>';
            echo '<td>' . $node['ip'] . ':' . $node['port'] . '</td>';
            echo '<td colspan="7"></td>';
            
            echo '</tr>';
        }


        
    }

    $i++;
}
echo '</table>';
