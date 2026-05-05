<?php

namespace App\Controller;

use App\Library\Extraction2;
use Glial\Synapse\Controller;
use Glial\Sgbd\Sgbd;

class InnoDBCluster extends Controller
{
    public function index($param)
    {
        $this->title = '<i class="fa fa-database"></i> '.__('Group Replication');

        $groupNameFilter = isset($param[0]) ? urldecode((string)$param[0]) : '';

        $rows = Extraction2::display(array(
            'variables::hostname',
            'variables::port',
            'variables::version',
            'variables::version_comment',
            'variables::server_uuid',
            'variables::group_replication_group_name',
            'variables::group_replication_single_primary_mode',
            'variables::group_replication_group_seeds',
            'variables::group_replication_local_address',
            'variables::group_replication_start_on_boot',
            'variables::group_replication_bootstrap_group',
            'variables::group_replication_consistency',
            'variables::group_replication_autorejoin_tries',
            'variables::group_replication_member_expel_timeout',
            'variables::group_replication_recovery_use_ssl',
            'variables::group_replication_ssl_mode',
            'variables::group_replication_exit_state_action',
            'variables::group_replication_flow_control_mode',
            'variables::group_replication_communication_stack',
            'variables::group_replication_paxos_single_leader',
            'variables::group_replication_view_change_uuid',
            'variables::group_replication_ip_allowlist',
            'variables::report_host',
            'variables::report_port',
            'variables::super_read_only',
            'read_only',
            'mysql_available',
            'mysql_error',
            'mysql_ping',
            'gr_member_role',
            'gr_member_state',
        ));

        // Enrich with display_name from mysql_server
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id, display_name, ip, port FROM mysql_server WHERE is_deleted = 0";
        $res = $db->sql_query($sql);
        $serverMap = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $serverMap[(int)$row['id']] = $row;
        }

        $clusters = [];

        foreach ($rows as $idMysqlServer => $row) {
            $groupName = trim((string)($row['group_replication_group_name'] ?? ''));
            if ($groupName === '') {
                continue;
            }

            if ($groupNameFilter !== '' && strcasecmp($groupNameFilter, $groupName) !== 0) {
                continue;
            }

            $row['id_mysql_server'] = $idMysqlServer;
            $row['display_name'] = $serverMap[$idMysqlServer]['display_name'] ?? '';
            $row['ip'] = $serverMap[$idMysqlServer]['ip'] ?? '';
            $row['server_port'] = $serverMap[$idMysqlServer]['port'] ?? '';

            $clusters[$groupName][$idMysqlServer] = $row;
        }

        ksort($clusters);

        $this->set('data', [
            'clusters' => $clusters,
            'group_name_filter' => $groupNameFilter,
        ]);
    }
}
