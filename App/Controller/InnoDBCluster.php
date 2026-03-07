<?php

namespace App\Controller;

use App\Library\Extraction2;
use Glial\Synapse\Controller;

class InnoDBCluster extends Controller
{
    public function index($param)
    {
        $groupNameFilter = isset($param[0]) ? urldecode((string)$param[0]) : '';

        $rows = Extraction2::display(array(
            'variables::hostname',
            'variables::port',
            'variables::version',
            'variables::group_replication_group_name',
            'variables::group_replication_single_primary_mode',
            'variables::group_replication_group_seeds',
            'variables::group_replication_local_address',
            'variables::server_uuid',
            'variables::super_read_only',
            'status::group_replication_primary_member',
            'status::group_replication_status',
            'mysql_available',
            'mysql_error',
        ));

        $clusters = array();

        foreach ($rows as $idMysqlServer => $row) {
            $groupName = trim((string)($row['group_replication_group_name'] ?? ''));
            if ($groupName === '') {
                continue;
            }

            if ($groupNameFilter !== '' && strcasecmp($groupNameFilter, $groupName) !== 0) {
                continue;
            }

            $clusters[$groupName][$idMysqlServer] = $row;
        }

        ksort($clusters);

        $this->set('data', array(
            'clusters' => $clusters,
            'group_name_filter' => $groupNameFilter,
        ));
    }
}
