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
        ], [$id_mysql_server]);

        $row = $state[$id_mysql_server] ?? [];

        $mysqlAvailable = (string)($row['mysql_available'] ?? $row['mysql_server::mysql_available'] ?? '1');
        $wsrepOn = strtoupper((string)($row['wsrep_on'] ?? ''));
        $clusterStatus = (string)($row['wsrep_cluster_status'] ?? '');
        $localStateComment = (string)($row['wsrep_local_state_comment'] ?? '');
        $wsrepReady = strtoupper((string)($row['wsrep_ready'] ?? ''));

        $isEligible = (
            $mysqlAvailable === '1'
            && $wsrepOn === 'ON'
            && strcasecmp($clusterStatus, 'Primary') !== 0
            && strcasecmp($localStateComment, 'Synced') === 0
            && $wsrepReady === 'ON'
        );

        if (!$isEligible) {
            if (function_exists('set_flash')) {
                set_flash('warning', 'Galera', __('SET PRIMARY allowed only for reachable Galera nodes with status Non-Primary + Synced + Ready=ON.'));
            }

            if (IS_CLI === false) {
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? LINK.'MysqlServer/main/'.$id_mysql_server.'/pmacontrol'));
                exit;
            }

            return;
        }

        $db  = Mysql::getDbLink($id_mysql_server);

        $sql = "SET GLOBAL wsrep_provider_options='pc.bootstrap=true';";
        
        // Write something to log
        $db->sql_query($sql);

        
        if (function_exists('set_flash')) {
            set_flash('success', 'Galera', __('Node switched to Primary component (pc.bootstrap=true). Use only after quorum loss.'));
        }

        if (IS_CLI === false) {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? LINK.'MysqlServer/main/'.$id_mysql_server.'/pmacontrol'));
            exit;
        }
    }
}
