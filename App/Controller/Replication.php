<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Extraction2;
use App\Library\GtidSet;
use App\Library\Mysql;
use App\Library\ReplicationTopologyDot;


/**
 * Class responsible for replication workflows.
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
class Replication extends Controller {

/**
 * Render replication state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/replication/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {
        $this->layout_name = 'default';
        $this->title = __("Replication");
        $this->ariane = " > " . $this->title;

        //$this->javascript = array("");
    }

/**
 * Handle replication state through `status`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for status.
 * @phpstan-return void
 * @psalm-return void
 * @see self::status()
 * @example /fr/replication/status
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function status() {
        $this->layout_name = 'default';
        $this->title = __("Status");


        $this->ariane = " > " . __("Replication") . " > " . $this->title;

        //$this->javascript = array("");
    }

/**
 * Handle replication state through `event`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for event.
 * @phpstan-return void
 * @psalm-return void
 * @see self::event()
 * @example /fr/replication/event
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function event() {
        $this->title = __("Events");
        $this->ariane = " > " . __("Replication") . " > " . $this->title;
    }

    public function topology()
    {
        $this->title = __("Replication topology");
        $this->ariane = " > " . __("Replication") . " > " . $this->title;

        $db = Sgbd::sql(DB_DEFAULT);
        $servers = [];
        $res = $db->sql_query("SELECT id, display_name, ip, port FROM mysql_server WHERE is_deleted = 0");
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $servers[(int)$row['id']] = $row;
        }

        $byEndpoint = [];
        foreach ($servers as $server) {
            $byEndpoint[(string)$server['ip'] . ':' . (int)$server['port']] = $server;
        }

        $slaveData = Extraction2::display([
            'slave::master_host',
            'slave::source_host',
            'slave::master_port',
            'slave::source_port',
            'slave::seconds_behind_master',
            'slave::seconds_behind_source',
            'slave::slave_io_running',
            'slave::replica_io_running',
            'slave::slave_sql_running',
            'slave::replica_sql_running',
            'slave::master_ssl_allowed',
            'slave::source_ssl_allowed',
            'slave::replicate_do_db',
            'slave::replicate_ignore_db',
            'slave::replicate_wild_do_table',
            'slave::replicate_wild_ignore_table',
        ]);

        $edges = [];
        foreach ($slaveData as $replicaId => $row) {
            if (!isset($servers[(int)$replicaId]) || empty($row['@slave'])) {
                continue;
            }
            foreach ($row['@slave'] as $channel => $status) {
                $host = (string)($status['master_host'] ?? $status['source_host'] ?? '');
                $port = (int)($status['master_port'] ?? $status['source_port'] ?? 3306);
                $source = $byEndpoint[$host . ':' . $port] ?? null;
                if (!$source) {
                    continue;
                }
                $lag = (int)($status['seconds_behind_source'] ?? $status['seconds_behind_master'] ?? 0);
                $io = (string)($status['slave_io_running'] ?? $status['replica_io_running'] ?? '') === 'Yes';
                $sql = (string)($status['slave_sql_running'] ?? $status['replica_sql_running'] ?? '') === 'Yes';
                $ssl = (string)($status['master_ssl_allowed'] ?? $status['source_ssl_allowed'] ?? '') === 'Yes';
                $filters = false;
                foreach (['replicate_do_db', 'replicate_ignore_db', 'replicate_wild_do_table', 'replicate_wild_ignore_table'] as $filter) {
                    $filters = $filters || trim((string)($status[$filter] ?? '')) !== '';
                }
                $edges[] = [
                    'source_id' => (int)$source['id'],
                    'source_name' => (string)$source['display_name'],
                    'replica_id' => (int)$replicaId,
                    'replica_name' => (string)$servers[(int)$replicaId]['display_name'],
                    'channel' => (string)$channel,
                    'lag' => $lag,
                    'healthy' => $io && $sql && $lag <= 30,
                    'ssl' => $ssl,
                    'filters' => $filters,
                ];
            }
        }

        $this->set('data', [
            'edges' => $edges,
            'dot' => ReplicationTopologyDot::build($edges),
        ]);
    }

    public function gtidDiff($param)
    {
        $this->title = __("GTID set diff");
        $this->ariane = " > " . __("Replication") . " > " . $this->title;

        $db = Sgbd::sql(DB_DEFAULT);
        $servers = [];
        $res = $db->sql_query("SELECT id, display_name, name FROM mysql_server WHERE is_deleted = 0 ORDER BY display_name");
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $servers[] = $row;
        }

        $sourceId = (int)($_GET['source_id'] ?? 0);
        $targetId = (int)($_GET['target_id'] ?? 0);
        $result = null;

        if ($sourceId > 0 && $targetId > 0) {
            $source = GtidSet::parse($this->readExecutedGtid($sourceId));
            $target = GtidSet::parse($this->readExecutedGtid($targetId));
            $compatible = $source->isCompatibleWith($target);
            $result = [
                'compatible' => $compatible,
                'source' => $source->toString(),
                'target' => $target->toString(),
                'only_source' => $compatible ? $source->subtract($target)->toString() : '(incompatible GTID families)',
                'only_target' => $compatible ? $target->subtract($source)->toString() : '(incompatible GTID families)',
                'intersection_count' => $compatible ? $source->intersect($target)->count() : 0,
            ];
        }

        $this->set('data', [
            'servers' => $servers,
            'source_id' => $sourceId,
            'target_id' => $targetId,
            'result' => $result,
        ]);
    }

    private function readExecutedGtid(int $serverId): string
    {
        $link = Mysql::getDbLink($serverId);
        foreach ([
            "SELECT @@GLOBAL.gtid_executed AS gtid",
            "SELECT @@GLOBAL.gtid_slave_pos AS gtid",
            "SELECT @@GLOBAL.gtid_binlog_pos AS gtid",
        ] as $sql) {
            $res = $link->sql_query_silent($sql);
            if ($res && ($row = $link->sql_fetch_array($res, MYSQLI_ASSOC))) {
                return (string)($row['gtid'] ?? '');
            }
        }

        return '';
    }

}
