<?php


use App\Library\Available;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo '<table class="table table-condensed table-bordered table-striped">';

$formatIncomingAddresses = function ($incomingRaw) {
    $parts = array_values(array_filter(array_map('trim', explode(',', (string)$incomingRaw)), static function ($item) {
        return $item !== '';
    }));

    if (empty($parts)) {
        return '';
    }

    $html = '<ul style="margin:0; padding-left:16px;">';
    foreach ($parts as $part) {
        $html .= '<li>' . htmlspecialchars($part, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    $html .= '</ul>';

    return $html;
};

echo '<tr>';
echo '<th>' . '#' . '</th>';
echo '<th>' . __('Node') . '</th>';
echo '<th>' . __('Server ID') . '</th>';
echo '<th>' . __('Hostname') . '</th>';


echo '<th>' . __('IP') . '</th>';

echo '<th>' . __('Version') . '</th>';
echo '<th>' . __('Segment') . '</th>';
echo '<th>' . __('Date') . '</th>';
echo '<th>' . __('Time zone') . '</th>';
echo '<th>' . __('Cluster_status') . '</th>';
echo '<th>' . __('Local state') . '</th>';
echo '<th>' . __('Desync') . '</th>';
echo '<th>' . __('Node UUID') . '</th>';
echo '<th>' . __('Cluster UUID') . '</th>';
echo '<th>' . __('Incoming addresses') . '</th>';
echo '</tr>';

//debug($data['galera']);
$i = 1;
foreach ($data['galera'] as $cluster_name => $galera) {

    $realNodesCount = 0;
    $normalizedIncomingByNode = [];
    foreach ($galera as $tmpNode) {
        if (empty($tmpNode['hostname'])) {
            continue;
        }

        $realNodesCount++;
        $incomingParts = array_values(array_filter(array_map('trim', explode(',', (string)($tmpNode['wsrep_incoming_addresses'] ?? ''))), static function ($item) {
            return $item !== '';
        }));
        $normalizedIncomingByNode[] = implode(',', $incomingParts);
    }

    $allIncomingIdentical = false;
    $sharedIncomingValue = '';
    if (!empty($normalizedIncomingByNode)) {
        $uniqueIncomingValues = array_values(array_unique($normalizedIncomingByNode));
        $allIncomingIdentical = count($uniqueIncomingValues) === 1;
        $sharedIncomingValue = $uniqueIncomingValues[0] ?? '';
    }

    $sharedIncomingPrinted = false;

    $j = 0;
    foreach ($galera as $node) {
        if ($j === 0) {
            
            $rowspan = count($galera);
            
            echo '<tr>';
            echo '<td class="pma-grey" rowspan="'.($rowspan+1).'"><b>' . $i . '<b></td>';
            
            $cluster_display_name = explode('~', $cluster_name)[0];
            $cluster_discriminant = explode('~', $cluster_name)[1] ?? '';

            echo '<td class="pma-grey" colspan="14">'
                .'Cluster name : <b>' . $cluster_display_name . '</b>'
                .' - Cluster size : <b>'.$rowspan.'</b>'
                .'</td>';
            echo '</tr>';
        }

        $j++;

        if (!empty($node['hostname'])) {
            $isMysqlAvailable = Available::getMySQL($node['id_mysql_server']);
            $rowClass = '';

            if (!$isMysqlAvailable) {
                $rowClass = 'danger';
            } elseif (strcasecmp((string)($node['wsrep_cluster_status'] ?? ''), 'Primary') !== 0) {
                $rowClass = 'warning';
            }

            $trClass = $rowClass !== '' ? ' class="'.$rowClass.'"' : '';

            echo '<tr' . $trClass . '>';
            echo '<td>' . $j . '</td>';
            echo '<td>' . $node['id_mysql_server'] . '</td>';
            
            $class = $isMysqlAvailable ? "ok" : "remove";

            echo '<td><span class="glyphicon glyphicon-'.$class.'" aria-hidden="true"></span> ' . $node['hostname'] . '</td>';
            echo '<td>' . $node['ip'] . ':' . $node['port'] . '</td>';
            echo '<td>' . $node['version'] . '</td>';

            $segment = '';
            if (isset($data['segment'][$node['id_mysql_server']])) {
                $segment = $data['segment'][$node['id_mysql_server']]; 
            }

            echo '<td>' . $segment . '</td>';
            echo '<td>' . $node['date'] . '</td>';
            echo '<td>' . $node['system_time_zone'] . '</td>';
            echo '<td>' . $node['wsrep_cluster_status'] . '</td>';
            echo '<td>' . $node['wsrep_local_state_comment'] . '</td>';
            echo '<td>' . $node['wsrep_desync'] . '</td>';
            echo '<td>' . $cluster_discriminant . '</td>';
            echo '<td>' . ($node['wsrep_cluster_state_uuid'] ?? '') . '</td>';

            if ($allIncomingIdentical) {
                if (!$sharedIncomingPrinted) {
                    $rowspanIncoming = max(1, (int)$realNodesCount);
                    echo '<td rowspan="' . $rowspanIncoming . '">' . $formatIncomingAddresses($sharedIncomingValue) . '</td>';
                    $sharedIncomingPrinted = true;
                }
            } else {
                echo '<td>' . $formatIncomingAddresses($node['wsrep_incoming_addresses'] ?? '') . '</td>';
            }

            echo '</tr>';
        }
        else
        {
            echo '<tr>';
            echo '<td>' . $j . '</td>';
            echo '<td>' . $node['id_mysql_server'] . '</td>';

            echo '<td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ' . "Arbitrator" . '</td>';
            echo '<td>' . $node['ip'] . ':' . $node['port'] . '</td>';
            echo '<td colspan="10"></td>';
            
            echo '</tr>';
        }

    }

    $i++;
}
echo '</table>';
