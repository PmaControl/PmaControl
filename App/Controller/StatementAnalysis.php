<?php

namespace App\Controller;

use \Glial\Synapse\Controller;

use \Glial\Sgbd\Sgbd;

class StatementAnalysis extends Controller
{
    /*
        CREATE TABLE `ts_mysql_query` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_mysql_query` int(11) NOT NULL,
            `id_mysql_server` int(11) NOT NULL,
            `date` datetime NOT NULL,
            `count_star` bigint(20) NOT NULL,
            `sum_timer_wait` bigint(20) NOT NULL,
            `sum_lock_time` bigint(20) NOT NULL,
            `sum_errors` int(11) NOT NULL,
            `sum_warnings` int(11) NOT NULL,
            `sum_rows_affected` int(11) NOT NULL,
            `sum_rows_sent` int(11) NOT NULL,
            `sum_rows_examined` int(11) NOT NULL,
            `sum_created_tmp_disk_tables` int(11) NOT NULL,
            `sum_created_tmp_tables` int(11) NOT NULL,
            `sum_select_full_join` int(11) NOT NULL,
            `sum_select_full_range_join` int(11) NOT NULL,
            `sum_select_range` int(11) NOT NULL,
            `sum_select_range_check` int(11) NOT NULL,
            `sum_select_scan` int(11) NOT NULL,
            `sum_sort_merge_passes` int(11) NOT NULL,
            `sum_sort_range` int(11) NOT NULL,
            `sum_sort_rows` int(11) NOT NULL,
            `sum_sort_scan` int(11) NOT NULL,
            `sum_no_index_used` int(11) NOT NULL,
            `sum_no_good_index_used` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `id_mysql_query_2` (`id_mysql_query`,`date`,`id_mysql_server`),
            KEY `id_mysql_query` (`id_mysql_query`)
          ) ENGINE=ROCKSDB AUTO_INCREMENT=1889875 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    */
    
    public function index($param)
    {
        
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT distinct id_mysql_query FROM ts_mysql_query WHERE id_mysql_server=1 AND `date` > date_sub(now(), INTERVAL 1 DAY) AND date < now()";

        $res = $db->sql_query($sql);




    }



}