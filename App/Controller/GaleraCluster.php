<?php

namespace App\Controller;

use App\Library\Extraction;
use App\Library\Extraction2;
use App\Library\Http\HttpResponse;
use App\Library\Mysql;
use App\Library\Security\RouteMutationRequest;
use App\Library\Security\SafeRedirect;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for galera cluster workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class GaleraCluster extends Controller {

    use \App\Library\Galera;

    public const SET_PRIMARY_CSRF_SCOPE = 'galera.set_primary';
    public const SET_PRIMARY_CONFIRM_VALUE = 'SET_PRIMARY';

/**
 * Render galera cluster state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/galeracluster/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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


/**
 * Handle galera cluster state through `setNodeAsPrimary`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for setNodeAsPrimary.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::setNodeAsPrimary()
 * @example /fr/galeracluster/setNodeAsPrimary
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function setNodeAsPrimary ($param)   {

        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateSetNodeAsPrimaryRequest(
            is_array($param) ? $param : [],
            $_POST ?? [],
            $_SERVER ?? [],
            $_SESSION ?? [],
            IS_CLI
        );
        if ($outcome['status'] !== 200) {
            HttpResponse::sendOutcome($outcome);
            return;
        }

        Debug::parseDebug($param);

        $id_mysql_server = $outcome['id_mysql_server'];

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
                header('Location: ' . self::redirectTarget($id_mysql_server, $_SERVER ?? []));
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
                header('Location: ' . self::redirectTarget($id_mysql_server, $_SERVER ?? []));
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
            header('Location: ' . self::redirectTarget($id_mysql_server, $_SERVER ?? []));
            exit;
        }
    }

    public static function evaluateSetNodeAsPrimaryRequest(
        array $param,
        array $post,
        array $server,
        array $session,
        bool $isCli = false
    ): array {
        if ($isCli) {
            $id = RouteMutationRequest::normalizeRequestedId($post, $param, 'id_mysql_server');
            if ($id === null) {
                return self::buildSetPrimaryOutcome(400, 'Invalid MySQL server id');
            }

            return self::buildSetPrimaryOutcome(200, '', [], $id);
        }

        $request = RouteMutationRequest::evaluate(
            $post,
            $server,
            $session,
            $param,
            self::SET_PRIMARY_CSRF_SCOPE,
            'Invalid MySQL server id',
            'id_mysql_server'
        );
        if ($request['status'] !== 200) {
            return self::buildSetPrimaryOutcome($request['status'], $request['body'], $request['headers']);
        }

        if ((string)($post['confirm_set_primary'] ?? '') !== self::SET_PRIMARY_CONFIRM_VALUE) {
            return self::buildSetPrimaryOutcome(400, 'Invalid Galera primary confirmation');
        }

        return self::buildSetPrimaryOutcome(200, '', [], $request['id']);
    }

    private static function buildSetPrimaryOutcome(
        int $statusCode,
        string $message,
        array $headers = [],
        ?int $idMysqlServer = null
    ): array {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'id_mysql_server' => $idMysqlServer,
        ];
    }

    private static function redirectTarget(int $idMysqlServer, array $server): string
    {
        return SafeRedirect::refererOrFallback(
            $server,
            LINK.'MysqlServer/main/'.$idMysqlServer.'/pmacontrol'
        );
    }
}
