<?php

namespace App\Controller;

use App\Library\Extraction;
use App\Library\Extraction2;
use App\Library\Mysql;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;


class GaleraCluster extends Controller {

    use \App\Library\Galera;

    public function index($param) {

        $data['galera'] = $this->getInfoGalera($param);
        $segments = Extraction::display(array("wsrep_provider_options"));

        foreach($segments as $segment)
        {
            if (!empty($segment['']['wsrep_provider_options']))
            {
                preg_match('/ gmcast.segment\s?\=\s?([0-9]+)\;/', $segment['']['wsrep_provider_options'], $output_array);

                if (isset($output_array[1]))
                {
                    $data['segment'][$segment['']['id_mysql_server']] = $output_array[1];
                }
            }
        }
        
        $this->set('data', $data);
    }

    /*
     * 
     * meta function to call all sub function
     */

    public function getInfoGalera($param) {


        Debug::parseDebug($param);

        $this->mappingMaster();

        $this->getInfoServer($param);
        $galera = $this->getGaleraCluster($param);

        Debug::debug($this->galera_cluster);


        return $this->galera_cluster;
    }

    //https://www.percona.com/blog/2012/12/19/percona-xtradb-cluster-pxc-what-about-gra_-log-files/


    public function setNodeAsPrimary ($param)   {

        Debug::parseDebug($param);

        if (empty($param[0]) || !ctype_digit((string)$param[0])) {
            throw new \Exception("Usage: /GaleraCluster/setNodeAsPrimary/{id_mysql_server}");
        }

        $id_mysql_server = (int) $param[0];

        $state = Extraction2::display([
            'mysql_server::mysql_available',
            'wsrep_on',
            'wsrep_cluster_status',
            'wsrep_local_state_comment',
            'wsrep_ready',
            'wsrep_incoming_addresses',
        ], [$id_mysql_server]);

        $row = $state[$id_mysql_server] ?? [];

        $mysqlAvailable = (string)($row['mysql_available'] ?? $row['mysql_server::mysql_available'] ?? '1');
        $wsrepOn = strtoupper((string)($row['wsrep_on'] ?? ''));
        $clusterStatus = (string)($row['wsrep_cluster_status'] ?? '');
        $localStateComment = (string)($row['wsrep_local_state_comment'] ?? '');
        $wsrepReady = strtoupper((string)($row['wsrep_ready'] ?? ''));
        $incomingAddresses = (string)($row['wsrep_incoming_addresses'] ?? '');

        $isEligibleByStandardRule = (
            $mysqlAvailable === '1'
            && $wsrepOn === 'ON'
            && strcasecmp($clusterStatus, 'Primary') !== 0
            && strcasecmp($localStateComment, 'Synced') === 0
            && $wsrepReady === 'ON'
        );

        $isEligibleByEmergencyRule = false;

        if ($mysqlAvailable === '1') {
            $clusterNodeIds = [];

            if ($incomingAddresses !== '' && strpos($incomingAddresses, ':') !== false) {
                try {
                    $clusterNodeIds = Mysql::getIdMySQLFromGalera($incomingAddresses);
                } catch (\Throwable $e) {
                    $clusterNodeIds = [];
                }
            }

            $clusterNodeIds = array_values(array_unique(array_filter(array_map('intval', (array)$clusterNodeIds))));
            if (!in_array($id_mysql_server, $clusterNodeIds, true)) {
                $clusterNodeIds[] = $id_mysql_server;
            }

            $clusterState = Extraction2::display([
                'mysql_server::mysql_available',
                'wsrep_cluster_status',
                'wsrep_local_state_comment',
            ], $clusterNodeIds);

            $hasPrimarySyncedAvailableNode = false;

            foreach ($clusterNodeIds as $clusterNodeId) {
                $clusterRow = $clusterState[$clusterNodeId] ?? [];

                $clusterNodeAvailable = (string)($clusterRow['mysql_available'] ?? $clusterRow['mysql_server::mysql_available'] ?? '0');
                $clusterNodeStatus = (string)($clusterRow['wsrep_cluster_status'] ?? '');
                $clusterNodeState = (string)($clusterRow['wsrep_local_state_comment'] ?? '');

                if (
                    $clusterNodeAvailable === '1'
                    && strcasecmp($clusterNodeStatus, 'Primary') === 0
                    && strcasecmp($clusterNodeState, 'Synced') === 0
                ) {
                    $hasPrimarySyncedAvailableNode = true;
                    break;
                }
            }

            $isEligibleByEmergencyRule = !$hasPrimarySyncedAvailableNode;
        }

        $isEligible = $isEligibleByStandardRule || $isEligibleByEmergencyRule;

        if (!$isEligible) {
            if (function_exists('set_flash')) {
                set_flash('caution', 'Galera', __('SET PRIMARY allowed only for reachable Galera nodes with status Non-Primary + Synced + Ready=ON, unless no reachable node is currently Primary + Synced.'));
            }

            if (IS_CLI === false) {
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? LINK.'MysqlServer/main/'.$id_mysql_server.'/pmacontrol'));
                exit;
            }

            return;
        }

        $db  = Mysql::getDbLink($id_mysql_server);

        $sql = "SET GLOBAL wsrep_provider_options='pc.bootstrap=true';";
        
        try {
            // Write something to log
            $db->sql_query($sql);
        } catch (\Throwable $e) {
            $errorCode = (int) $e->getCode();
            $errorMessage = (string) $e->getMessage();

            $isReadOnlyWsrepProvider = (
                ($errorCode === 60 || stripos($errorMessage, 'read only variable') !== false)
                && stripos($errorMessage, 'wsrep_provider_options') !== false
            );

            if (function_exists('set_flash')) {
                if ($isReadOnlyWsrepProvider) {
                    set_flash(
                        'caution',
                        'Galera',
                        __('Cannot switch node to Primary dynamically: wsrep_provider_options is read-only on this server. Use galera_new_cluster / --wsrep-new-cluster on one node after quorum loss.')
                    );
                } else {
                    set_flash(
                        'error',
                        'Galera',
                        __('Unable to set node as Primary (pc.bootstrap=true). Please check server logs and privileges.')
                    );
                }
            }

            if (IS_CLI === false) {
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? LINK.'MysqlServer/main/'.$id_mysql_server.'/pmacontrol'));
                exit;
            }

            if (!$isReadOnlyWsrepProvider) {
                throw $e;
            }

            return;
        }

        
        if (function_exists('set_flash')) {
            set_flash('success', 'Galera', __('Node switched to Primary component (pc.bootstrap=true). Use only after quorum loss.'));
        }

        if (IS_CLI === false) {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? LINK.'MysqlServer/main/'.$id_mysql_server.'/pmacontrol'));
            exit;
        }
    }
}
