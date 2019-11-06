<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



// DEPRECATED
namespace App\Library;

/*
 * class pour gérer les grosses volumétrie via TokuDB et Spider
 */

/*
 * CREATE TABLE `sharding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;



  ALTER TABLE `sharding` ADD UNIQUE(`prefix`);
 */

trait Decoupage {

    var $table_bame = "sharding";
    var $spider = true;
    var $type_data = array("int" => "bigint(20) unsigned", "double" => "double", "text" => "text");
    var $engine_bigdata = "TokuDB";

    /*
     * bigint : 0
     * double or negative int : 1
     * test : 2
     * 
     */

    public function OnAddServer($param) {
        $this->view = false;
        Debug::parseDebug($param);

        $id_server = $param[0];
        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->tryRocksDb();

        $sql = "SELECT * FROM sharding";
        $res2 = $db->sql_query($sql);

        while ($ob2 = $db->sql_fetch_object($res2)) {

            Debug::debug(array($ob2->prefix, $ob2->table_link));

            $this->buildRootTable($ob2->prefix, $ob2->table_link);


            if ($this->spider) {
                $this->buildTablePartition($ob2->prefix, $ob2->table_link, $id_server);
            }
//$id_servers[] = $ob->id;
        }
    }

    public function OnRemServer($param) {

        $fields = array("int", "double", "text");

        $id_server = $param[0];
        $db = $this->di['db']->sql(DB_DEFAULT);

        $tables = $db->getListTable('table');

        $sql = "SELECT * FROM sharding";
        $res2 = $db->sql_query($sql);

        while ($ob2 = $db->sql_fetch_object($res2)) {

            foreach ($fields as $field) {
                $sql = "DROP TABLE IF EXISTS `" . $ob2->prefix . "_value_" . $field . "__" . $id_server . "`;";
                Debug::debug(\SqlFormatter::highlight($sql));
                $db->sql_query($sql);
            }
        }
    }

    public function install() {
        
    }

    public function buildRootTable($name_table, $table_link) {

        $this->buildTableName($name_table);
        $this->buildTableLink($name_table, $table_link);
    }

    public function buildTableName($name_table) {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "CREATE TABLE IF NOT EXISTS `" . $name_table . "_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;";

        Debug::debug(\SqlFormatter::highlight($sql));
        $db->sql_query($sql);
    }

    public function buildTableLink($name_table, $table_link) {
        $db = $this->di['db']->sql(DB_DEFAULT);
        $link_to = explode('__', $table_link)[0];
        $sql = "CREATE TABLE IF NOT EXISTS `link__" . $name_table . "__" . $link_to . "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
`id_" . $name_table . "_name` int(11) NOT NULL,
`id_" . $link_to . "` int(11) NOT NULL,
    `type` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;";

        Debug::debug(\SqlFormatter::highlight($sql));
        $db->sql_query($sql);
    }

    public function buildTablePartition($name_table, $table_link, $id_server) {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $link_to = explode('__', $table_link)[0];

        foreach ($this->type_data as $name => $length) {

            $sql = " CREATE TABLE IF NOT EXISTS `" . $name_table . "_value_" . $name . "__" . $id_server . "` (";

            // $sql .= "`id` int(20) NOT NULL AUTO_INCREMENT,";
            $sql .= "`id_" . $link_to . "` int(11) NOT NULL,";
            $sql .= "`id_" . $name_table . "_name` int(11) NOT NULL,";
            $sql .= "`date` datetime NOT NULL,
  `value` " . $length . " NOT NULL,";

            //$sql .= "PRIMARY KEY (`id`),";
            //$sql .= "UNIQUE KEY `id_" . $link_to . "_" . $id_server . "` (`id_" . $link_to . "`,`id_" . $name_table . "_name`,`date`),";
            
            $sql .= "PRIMARY KEY `id_" . $link_to . "_" . $id_server . "` (`id_" . $link_to . "`,`id_" . $name_table . "_name`,`date`),";

            //$sql .= "KEY `id_" . $link_to . "__" . $id_server . "` (`id_" . $link_to . "`,`id_" . $name_table . "_name`),";
            $sql .= "KEY `date_" . $id_server . "` (`date`,`id_" . $link_to . "`,`id_" . $name_table . "_name`)";
            //$sql .= "KEY `id_" . $name_table . "_name__" . $id_server . "` (`id_" . $name_table . "_name`)";
            $sql .= ") ENGINE=" . $this->engine_bigdata . " AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;";

            Debug::debug(\SqlFormatter::highlight($sql));
            $db->sql_query($sql);
        }
    }

    public function buildMainTable($prefix, $table_link, $id_servers) {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $link_to = explode('__', $table_link)[0];

        foreach ($this->type_data as $name => $length) {

            $partitions = array();
            foreach ($id_servers as $id_server) {

                $partion = " PARTITION pt" . $id_server . " VALUES IN (" . $id_server . ") ";

                if ($this->spider) {
                    $partion .= "COMMENT = 'host \"\", database \"\", user \"\", password \"\", table \"" . $prefix . "_value_" . $name . "__" . $id_server . "\"' ENGINE = SPIDER";
                    //$partion .= "COMMENT = 'database \"pmacontrol\", table \"" . $prefix . "_value_" . $name . "__" . $id_server . "\"' ENGINE = SPIDER";
//$partion .= "COMMENT = 'srv \"backend4\" table \"" . $prefix . "_value_" . $name . "__" . $id_server . "\"' ENGINE = SPIDER ";
                }

                $partitions[] = $partion;
            }

            Debug::debug($partitions);

            $sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "_value_" . $name . "` ";

            $sql .= "( 
`id_" . $link_to . "` int(11) NOT NULL,
`id_" . $prefix . "_name` int(11) NOT NULL,
`date` datetime NOT NULL,
`value` " . $length . " NOT NULL,
PRIMARY KEY `id_" . $link_to . "_" . $prefix . "_" . $name . "` (`id_" . $link_to . "`,`id_" . $prefix . "_name`,`date`),
KEY `id_" . $link_to . "_" . $prefix . "__" . $name . "` (`id_" . $link_to . "`,`id_" . $prefix . "_name`),
KEY `date_" . $name . "_" . $prefix . "` (`date`,`id_" . $link_to . "`,`id_" . $prefix . "_name`),
KEY `id_" . $prefix . "_name_" . $prefix . "_" . $name . "` (`id_" . $prefix . "_name`)";

            $sql .= " ) ";
            $sql .= "  DEFAULT CHARSET=latin1 ";
            if ($this->spider) {
                $sql .= " ENGINE=SPIDER COMMENT='wrapper \"mysql\"' ";
            } else {
                $sql .= " ENGINE=" . $this->engine_bigdata . " ";
            }
            $sql .= " PARTITION BY LIST (`id_" . $link_to . "`)
(
    " . implode(",\n    ", $partitions) . "
);";

            if ($this->spider) {
                $sql10 = "DROP TABLE IF EXISTS `" . $prefix . "_value_" . $name . "`;";
                Debug::debug(\SqlFormatter::highlight($sql10));

                $db->sql_query($sql10);
            }

            Debug::debug(\SqlFormatter::highlight($sql));
            $db->sql_query($sql);
        }
    }

    public function buildTableHistory($prefix, $table_link, $id_servers) {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $link_to = explode('__', $table_link)[0];


        /*
          $sql10 = "DROP TABLE IF EXISTS `" . $prefix . "_max_date` ";
          Debug::debug(SqlFormatter::highlight($sql10));
          $db->sql_query($sql10);
         */

        $sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "_max_date` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_" . $link_to . "` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `date_previous` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_" . $prefix . "_" . $link_to . "_3` (`id_" . $link_to . "`),
  UNIQUE KEY `idx_" . $prefix . "_" . $link_to . "_1` (`id_" . $link_to . "`,`date`, `date_previous`),
  CONSTRAINT `idx_" . $prefix . "_" . $link_to . "_2` FOREIGN KEY (`id_" . $link_to . "`) REFERENCES `" . $link_to . "` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;";

        Debug::debug(\SqlFormatter::format($sql));
        $db->sql_query($sql);



//insertion une ligne par serveur

        foreach ($id_servers as $id_server) {
            $sql = "INSERT IGNORE INTO  `" . $prefix . "_max_date` (`id_" . $link_to . "`) VALUES (" . $id_server . ");";
            Debug::debug(\SqlFormatter::highlight($sql));
            $db->sql_query($sql);
        }
    }

    function getIdservers() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server";
        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            $id_servers[] = $ob->id;
        }

        return $id_servers;
    }

    function tryRocksDb() {

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "select count(1) as cpt from information_schema.engines where engine in ('ROCKSDB') and (SUPPORT = 'YES' OR SUPPORT = 'DEFAULT');";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if ($ob->cpt == "1") {
                $this->engine_bigdata = "ROCKSDB";
            }
        }
    }

    public function rebuildAll($param) {
        $this->view = false;
        Debug::parseDebug($param);

        $this->dropAll($param);
        $this->init($param);
    }

    public function init($param) {

        Debug::parseDebug($param);
        
        $db = $this->di['db']->sql(DB_DEFAULT);
        $id_servers = $this->getIdservers();

        foreach ($id_servers as $id_server) {
            $this->OnAddServer(array($id_server));
        }

        $sql = "SELECT * FROM sharding";
        $res2 = $db->sql_query($sql);

        while ($ob2 = $db->sql_fetch_object($res2)) {
            $this->buildMainTable($ob2->prefix, $ob2->table_link, $id_servers);
            $this->buildTableHistory($ob2->prefix, $ob2->table_link, $id_servers);
        }

        $this->init_variables();
    }

    public function dropAll($param) {

        $this->view = false;
        Debug::parseDebug($param);

        $db = $this->di['db']->sql(DB_DEFAULT);

        $id_servers = $this->getIdservers();

        foreach ($id_servers as $id_server) {
            $this->OnRemServer(array($id_server));
        }


        $sql = "SELECT * FROM sharding;";
        Debug::debug(\SqlFormatter::highlight($sql));
        $res2 = $db->sql_query($sql);

        $fields = array("int", "double", "text");

        while ($ob2 = $db->sql_fetch_object($res2)) {

            $sql = "DROP TABLE IF EXISTS `" . $ob2->prefix . "_name`;";
            Debug::debug(\SqlFormatter::highlight($sql));
            $db->sql_query($sql);

            $sql = "DROP TABLE IF EXISTS `" . $ob2->prefix . "_max_date`;";
            Debug::debug(\SqlFormatter::highlight($sql));
            $db->sql_query($sql);


            foreach ($fields as $field) {
                $sql = "DROP TABLE IF EXISTS `" . $ob2->prefix . "_value_" . $field . "`;";
                Debug::debug(\SqlFormatter::highlight($sql));
                $db->sql_query($sql);
            }
        }
    }

    public function init_variables() {
        /*
         * bigint : 0
         * double or negative int : 1
         * text : 2
         * 
         */


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "TRUNCATE TABLE `variables_name`";
        
        $db->sql_query($sql);
        
        $sql = "INSERT INTO `variables_name` VALUES (1,'aria_block_size',0),
(2,'aria_checkpoint_interval',0),
(4,'aria_checkpoint_log_activity',0),
(5,'aria_encrypt_tables',2),
(9,'aria_force_start_after_recovery_failures',0),
(11,'aria_group_commit',2),
(12,'aria_group_commit_interval',0),
(13,'aria_log_file_size',0),
(14,'aria_log_purge_type',2),
(16,'aria_max_sort_file_size',0),
(18,'aria_page_checksum',2),
(19,'aria_pagecache_age_threshold',0),
(20,'aria_pagecache_buffer_size',0),
(22,'aria_pagecache_division_limit',0),
(23,'aria_pagecache_file_hash_size',0),
(25,'aria_recover_options',2),
(27,'aria_repair_threads',0),
(28,'aria_sort_buffer_size',0),
(30,'aria_stats_method',2),
(32,'aria_sync_log_dir',2),
(34,'aria_used_for_temp_tables',2),
(35,'auto_increment_increment',0),
(37,'auto_increment_offset',0),
(39,'autocommit',2),
(40,'aria_recover',2),
(41,'automatic_sp_privileges',2),
(43,'back_log',0),
(45,'basedir',2),
(47,'big_tables',2),
(49,'binlog_annotate_row_events',2),
(51,'binlog_cache_size',0),
(52,'binlog_checksum',2),
(54,'binlog_commit_wait_count',0),
(56,'binlog_commit_wait_usec',0),
(58,'binlog_direct_non_transactional_updates',2),
(60,'binlog_format',2),
(62,'binlog_optimize_thread_scheduling',2),
(64,'binlog_row_image',2),
(65,'binlog_stmt_cache_size',0),
(67,'bulk_insert_buffer_size',0),
(69,'character_set_client',2),
(71,'character_set_connection',2),
(73,'character_set_database',2),
(75,'character_set_filesystem',2),
(76,'character_set_results',2),
(78,'character_set_server',2),
(79,'character_set_system',2),
(81,'character_sets_dir',2),
(83,'check_constraint_checks',2),
(84,'collation_connection',2),
(86,'collation_database',2),
(88,'collation_server',2),
(89,'completion_type',2),
(91,'concurrent_insert',2),
(93,'connect_timeout',0),
(95,'datadir',2),
(96,'date_format',2),
(98,'datetime_format',2),
(100,'deadlock_search_depth_long',0),
(102,'deadlock_search_depth_short',0),
(103,'deadlock_timeout_long',0),
(105,'deadlock_timeout_short',0),
(107,'debug_no_thread_alarm',2),
(109,'default_regex_flags',2),
(110,'default_storage_engine',2),
(112,'default_tmp_storage_engine',2),
(114,'default_week_format',0),
(116,'delay_key_write',2),
(117,'delayed_insert_limit',0),
(119,'delayed_insert_timeout',0),
(121,'delayed_queue_size',0),
(123,'div_precision_increment',0),
(124,'encrypt_binlog',2),
(125,'connect_class_path',2),
(126,'encrypt_tmp_disk_tables',2),
(127,'connect_conv_size',0),
(128,'encrypt_tmp_files',2),
(129,'connect_exact_info',2),
(130,'enforce_storage_engine',2),
(131,'connect_indx_map',2),
(132,'connect_java_wrapper',2),
(133,'connect_json_grp_size',0),
(134,'connect_jvm_path',2),
(136,'connect_type_conv',2),
(137,'connect_use_tempfile',2),
(138,'event_scheduler',2),
(139,'connect_work_size',0),
(140,'connect_xtrace',0),
(142,'expensive_subquery_limit',0),
(144,'expire_logs_days',0),
(147,'explicit_defaults_for_timestamp',2),
(148,'extra_max_connections',0),
(149,'extra_port',0),
(151,'flush',2),
(152,'flush_time',0),
(153,'foreign_key_checks',2),
(154,'ft_boolean_syntax',2),
(155,'ft_max_word_len',0),
(156,'ft_min_word_len',0),
(157,'ft_query_expansion_limit',0),
(158,'ft_stopword_file',2),
(159,'general_log',2),
(160,'general_log_file',2),
(162,'group_concat_max_len',0),
(163,'gtid_binlog_pos',2),
(164,'gtid_binlog_state',2),
(165,'gtid_current_pos',2),
(166,'gtid_domain_id',0),
(167,'gtid_ignore_duplicates',2),
(168,'gtid_slave_pos',2),
(169,'gtid_strict_mode',2),
(170,'have_compress',2),
(171,'have_crypt',2),
(172,'have_dynamic_loading',2),
(173,'have_geometry',2),
(174,'have_openssl',2),
(175,'have_profiling',2),
(176,'have_query_cache',2),
(177,'have_rtree_keys',2),
(178,'have_ssl',2),
(179,'have_symlink',2),
(180,'histogram_size',0),
(181,'histogram_type',2),
(182,'host_cache_size',0),
(183,'hostname',2),
(184,'ignore_builtin_innodb',2),
(185,'ignore_db_dirs',2),
(186,'init_connect',2),
(187,'init_file',2),
(188,'init_slave',2),
(189,'innodb_adaptive_flushing',2),
(190,'innodb_adaptive_flushing_lwm',1),
(192,'innodb_adaptive_hash_index',2),
(193,'innodb_adaptive_hash_index_partitions',0),
(196,'innodb_adaptive_hash_index_parts',0),
(197,'innodb_adaptive_max_sleep_delay',0),
(198,'innodb_autoextend_increment',0),
(199,'innodb_autoinc_lock_mode',0),
(200,'innodb_background_scrub_data_check_interval',0),
(201,'innodb_background_scrub_data_compressed',2),
(202,'innodb_background_scrub_data_interval',0),
(203,'innodb_background_scrub_data_uncompressed',2),
(204,'innodb_buf_dump_status_frequency',0),
(205,'innodb_buffer_pool_chunk_size',0),
(206,'innodb_buffer_pool_dump_at_shutdown',2),
(207,'innodb_buffer_pool_dump_now',2),
(208,'innodb_buffer_pool_dump_pct',0),
(209,'innodb_buffer_pool_filename',2),
(210,'innodb_buffer_pool_instances',0),
(212,'innodb_buffer_pool_load_abort',2),
(214,'innodb_buffer_pool_load_at_startup',2),
(216,'innodb_buffer_pool_load_now',2),
(218,'innodb_buffer_pool_populate',2),
(220,'innodb_buffer_pool_size',0),
(221,'innodb_change_buffer_max_size',0),
(223,'innodb_change_buffering',2),
(224,'innodb_checksum_algorithm',2),
(226,'innodb_checksums',2),
(234,'innodb_cleaner_lsn_age_factor',2),
(236,'innodb_cmp_per_index_enabled',2),
(247,'innodb_commit_concurrency',0),
(249,'innodb_compression_algorithm',2),
(252,'innodb_compression_default',2),
(254,'innodb_compression_failure_threshold_pct',0),
(256,'innodb_compression_level',0),
(259,'innodb_compression_pad_pct_max',0),
(261,'innodb_concurrency_tickets',0),
(263,'innodb_corrupt_table_action',2),
(265,'innodb_data_file_path',2),
(267,'innodb_data_home_dir',2),
(274,'innodb_deadlock_detect',2),
(277,'innodb_default_encryption_key_id',0),
(279,'innodb_default_row_format',2),
(281,'innodb_defragment',2),
(283,'innodb_defragment_fill_factor',1),
(285,'innodb_defragment_fill_factor_n_recs',0),
(288,'innodb_defragment_frequency',0),
(290,'innodb_defragment_n_pages',0),
(292,'innodb_defragment_stats_accuracy',0),
(294,'innodb_disable_sort_file_cache',2),
(296,'innodb_disallow_writes',2),
(299,'innodb_doublewrite',2),
(300,'innodb_empty_free_list_algorithm',2),
(302,'innodb_encrypt_log',2),
(303,'innodb_encrypt_tables',2),
(304,'innodb_encryption_rotate_key_age',0),
(305,'innodb_encryption_rotation_iops',0),
(306,'innodb_encryption_threads',0),
(307,'innodb_fake_changes',2),
(308,'innodb_fast_shutdown',0),
(309,'innodb_fatal_semaphore_wait_threshold',0),
(310,'innodb_file_format',2),
(311,'innodb_file_format_check',2),
(313,'innodb_file_format_max',2),
(314,'innodb_file_per_table',2),
(315,'innodb_fill_factor',0),
(317,'innodb_flush_log_at_timeout',0),
(318,'innodb_flush_log_at_trx_commit',0),
(319,'innodb_flush_method',2),
(320,'innodb_flush_neighbors',0),
(321,'innodb_flush_sync',2),
(323,'innodb_flushing_avg_loops',0),
(324,'innodb_force_load_corrupted',2),
(325,'innodb_force_primary_key',2),
(326,'innodb_force_recovery',0),
(327,'innodb_foreground_preflush',2),
(328,'innodb_ft_aux_table',2),
(329,'innodb_ft_cache_size',0),
(330,'innodb_ft_enable_diag_print',2),
(331,'innodb_ft_enable_stopword',2),
(332,'innodb_ft_max_token_size',0),
(333,'innodb_ft_min_token_size',0),
(334,'innodb_ft_num_word_optimize',0),
(335,'innodb_ft_result_cache_limit',0),
(336,'innodb_ft_server_stopword_table',2),
(337,'innodb_ft_sort_pll_degree',0),
(338,'innodb_ft_total_cache_size',0),
(339,'innodb_ft_user_stopword_table',2),
(340,'innodb_additional_mem_pool_size',0),
(341,'innodb_idle_flush_pct',0),
(342,'innodb_immediate_scrub_data_uncompressed',2),
(343,'innodb_instrument_semaphores',2),
(344,'innodb_io_capacity',0),
(345,'innodb_io_capacity_max',0),
(346,'innodb_kill_idle_transaction',0),
(347,'innodb_api_bk_commit_interval',0),
(348,'innodb_large_prefix',2),
(349,'innodb_lock_schedule_algorithm',2),
(350,'innodb_lock_wait_timeout',0),
(351,'innodb_locking_fake_changes',2),
(352,'innodb_locks_unsafe_for_binlog',2),
(353,'innodb_log_arch_dir',2),
(354,'innodb_log_arch_expire_sec',0),
(355,'innodb_log_archive',2),
(356,'innodb_api_disable_rowlock',2),
(357,'innodb_log_block_size',0),
(358,'innodb_api_enable_binlog',2),
(359,'innodb_log_buffer_size',0),
(360,'innodb_api_enable_mdl',2),
(361,'innodb_log_checksum_algorithm',2),
(362,'innodb_api_trx_level',0),
(363,'innodb_log_checksums',2),
(365,'innodb_log_compressed_pages',2),
(367,'innodb_log_file_size',0),
(369,'innodb_log_files_in_group',0),
(371,'innodb_log_group_home_dir',2),
(372,'innodb_log_write_ahead_size',0),
(373,'innodb_lru_scan_depth',0),
(374,'innodb_max_bitmap_file_size',0),
(376,'innodb_max_changed_pages',0),
(377,'innodb_max_dirty_pages_pct',1),
(378,'innodb_max_dirty_pages_pct_lwm',1),
(379,'innodb_max_purge_lag',0),
(380,'innodb_max_purge_lag_delay',0),
(381,'innodb_max_undo_log_size',0),
(383,'innodb_mirrored_log_groups',0),
(384,'innodb_monitor_disable',2),
(386,'innodb_monitor_enable',2),
(387,'innodb_monitor_reset',2),
(389,'innodb_monitor_reset_all',2),
(391,'innodb_mtflush_threads',0),
(393,'innodb_numa_interleave',2),
(395,'innodb_old_blocks_pct',0),
(397,'innodb_old_blocks_time',0),
(399,'innodb_online_alter_log_max_size',0),
(400,'innodb_open_files',0),
(402,'innodb_optimize_fulltext_only',2),
(404,'innodb_page_cleaners',0),
(406,'innodb_page_size',0),
(408,'innodb_prefix_index_cluster_optimization',2),
(410,'innodb_print_all_deadlocks',2),
(412,'innodb_purge_batch_size',0),
(414,'innodb_purge_rseg_truncate_frequency',0),
(416,'innodb_purge_threads',0),
(418,'innodb_random_read_ahead',2),
(420,'innodb_read_ahead_threshold',0),
(422,'innodb_read_io_threads',0),
(424,'innodb_read_only',2),
(426,'innodb_replication_delay',0),
(428,'innodb_rollback_on_timeout',2),
(430,'innodb_rollback_segments',0),
(432,'innodb_sched_priority_cleaner',0),
(434,'innodb_scrub_log',2),
(436,'innodb_scrub_log_speed',0),
(438,'innodb_show_locks_held',0),
(440,'innodb_show_verbose_locks',0),
(442,'innodb_sort_buffer_size',0),
(445,'innodb_spin_wait_delay',0),
(447,'innodb_stats_auto_recalc',2),
(448,'innodb_stats_include_delete_marked',2),
(450,'innodb_stats_method',2),
(451,'innodb_stats_modified_counter',0),
(452,'innodb_stats_on_metadata',2),
(453,'innodb_stats_persistent',2),
(455,'innodb_stats_persistent_sample_pages',0),
(457,'innodb_stats_sample_pages',0),
(459,'innodb_stats_traditional',2),
(461,'innodb_stats_transient_sample_pages',0),
(462,'innodb_status_output',2),
(464,'innodb_status_output_locks',2),
(466,'innodb_strict_mode',2),
(467,'innodb_support_xa',2),
(469,'innodb_sync_array_size',0),
(471,'innodb_sync_spin_loops',0),
(473,'innodb_table_locks',2),
(474,'innodb_temp_data_file_path',2),
(475,'innodb_thread_concurrency',0),
(476,'innodb_thread_sleep_delay',0),
(479,'innodb_tmpdir',2),
(481,'innodb_track_changed_pages',2),
(483,'innodb_track_redo_log_now',2),
(485,'innodb_undo_directory',2),
(487,'innodb_undo_log_truncate',2),
(489,'innodb_undo_logs',0),
(491,'innodb_undo_tablespaces',0),
(492,'innodb_use_atomic_writes',2),
(493,'innodb_use_fallocate',2),
(495,'innodb_use_global_flush_log_at_trx_commit',2),
(498,'innodb_use_mtflush',2),
(500,'innodb_use_native_aio',2),
(502,'innodb_use_stacktrace',2),
(505,'innodb_use_trim',2),
(508,'innodb_version',2),
(509,'innodb_write_io_threads',0),
(511,'interactive_timeout',0),
(514,'join_buffer_size',0),
(516,'join_buffer_space_limit',0),
(518,'join_cache_level',0),
(522,'keep_files_on_create',2),
(524,'key_buffer_size',0),
(526,'key_cache_age_threshold',0),
(527,'key_cache_block_size',0),
(528,'key_cache_division_limit',0),
(530,'key_cache_file_hash_size',0),
(531,'key_cache_segments',0),
(533,'large_files_support',2),
(535,'large_page_size',0),
(537,'large_pages',2),
(549,'lc_messages',2),
(551,'lc_messages_dir',2),
(553,'lc_time_names',2),
(555,'license',2),
(556,'local_infile',2),
(558,'lock_wait_timeout',0),
(560,'locked_in_memory',2),
(561,'log_bin',2),
(562,'log_bin_basename',2),
(563,'log_bin_compress',2),
(565,'log_bin_compress_min_len',0),
(567,'log_bin_index',2),
(569,'log_bin_trust_function_creators',2),
(570,'log_error',2),
(572,'log_output',2),
(574,'log_queries_not_using_indexes',2),
(576,'log_slave_updates',2),
(577,'log_slow_admin_statements',2),
(579,'log_slow_filter',2),
(581,'log_slow_rate_limit',0),
(583,'log_slow_slave_statements',2),
(584,'log_slow_verbosity',2),
(585,'log_tc_size',0),
(586,'log_warnings',0),
(587,'long_query_time',1),
(590,'low_priority_updates',2),
(592,'lower_case_file_system',2),
(593,'lower_case_table_names',0),
(594,'master_verify_checksum',2),
(596,'max_allowed_packet',0),
(597,'max_binlog_cache_size',1),
(598,'max_binlog_size',0),
(600,'max_binlog_stmt_cache_size',1),
(601,'max_connect_errors',0),
(603,'max_connections',0),
(605,'max_delayed_threads',0),
(607,'max_digest_length',0),
(609,'max_error_count',0),
(610,'max_heap_table_size',0),
(612,'max_insert_delayed_threads',0),
(614,'max_join_size',1),
(616,'max_length_for_sort_data',0),
(618,'max_long_data_size',0),
(621,'max_prepared_stmt_count',0),
(623,'max_recursive_iterations',0),
(625,'max_relay_log_size',0),
(628,'max_seeks_for_key',0),
(629,'max_session_mem_used',0),
(632,'max_sort_length',0),
(634,'max_sp_recursion_depth',0),
(635,'max_statement_time',1),
(637,'max_tmp_tables',0),
(640,'max_user_connections',0),
(642,'max_write_lock_count',0),
(645,'metadata_locks_cache_size',0),
(647,'metadata_locks_hash_instances',0),
(648,'innodb_simulate_comp_failures',0),
(649,'min_examined_row_limit',0),
(651,'mrr_buffer_size',0),
(653,'multi_range_count',0),
(656,'myisam_block_size',0),
(659,'myisam_data_pointer_size',0),
(660,'myisam_max_sort_file_size',0),
(663,'myisam_mmap_size',1),
(664,'myisam_recover_options',2),
(666,'myisam_repair_threads',0),
(668,'myisam_sort_buffer_size',0),
(669,'myisam_stats_method',2),
(670,'myisam_use_mmap',2),
(671,'mysql56_temporal_format',2),
(672,'net_buffer_length',0),
(673,'net_read_timeout',0),
(674,'net_retry_count',0),
(675,'net_write_timeout',0),
(676,'old',2),
(677,'old_alter_table',2),
(678,'old_mode',2),
(679,'old_passwords',2),
(680,'open_files_limit',0),
(681,'optimizer_prune_level',0),
(682,'optimizer_search_depth',0),
(683,'optimizer_selectivity_sampling_limit',0),
(684,'optimizer_switch',2),
(685,'optimizer_use_condition_selectivity',0),
(686,'performance_schema',2),
(687,'performance_schema_accounts_size',1),
(688,'performance_schema_digests_size',1),
(689,'performance_schema_events_stages_history_long_size',1),
(690,'performance_schema_events_stages_history_size',1),
(691,'performance_schema_events_statements_history_long_size',1),
(692,'performance_schema_events_statements_history_size',1),
(693,'performance_schema_events_waits_history_long_size',1),
(694,'performance_schema_events_waits_history_size',1),
(695,'performance_schema_hosts_size',1),
(696,'performance_schema_max_cond_classes',1),
(697,'performance_schema_max_cond_instances',1),
(698,'performance_schema_max_digest_length',1),
(699,'performance_schema_max_file_classes',1),
(700,'performance_schema_max_file_handles',1),
(701,'performance_schema_max_file_instances',1),
(702,'performance_schema_max_mutex_classes',1),
(703,'performance_schema_max_mutex_instances',1),
(704,'performance_schema_max_rwlock_classes',1),
(705,'performance_schema_max_rwlock_instances',1),
(706,'performance_schema_max_socket_classes',1),
(707,'performance_schema_max_socket_instances',1),
(708,'performance_schema_max_stage_classes',1),
(709,'performance_schema_max_statement_classes',1),
(710,'performance_schema_max_table_handles',1),
(711,'performance_schema_max_table_instances',1),
(712,'performance_schema_max_thread_classes',1),
(713,'performance_schema_max_thread_instances',1),
(714,'performance_schema_session_connect_attrs_size',1),
(715,'performance_schema_setup_actors_size',1),
(716,'performance_schema_setup_objects_size',1),
(717,'performance_schema_users_size',1),
(718,'pid_file',2),
(719,'plugin_dir',2),
(720,'plugin_maturity',2),
(721,'port',0),
(722,'preload_buffer_size',0),
(723,'profiling',2),
(724,'profiling_history_size',0),
(725,'progress_report_time',0),
(726,'protocol_version',0),
(727,'query_alloc_block_size',0),
(728,'query_cache_limit',0),
(729,'query_cache_min_res_unit',0),
(730,'query_cache_size',0),
(731,'query_cache_strip_comments',2),
(732,'query_cache_type',2),
(733,'query_cache_wlock_invalidate',2),
(734,'query_prealloc_size',0),
(735,'range_alloc_block_size',0),
(737,'read_binlog_speed_limit',0),
(738,'read_buffer_size',0),
(740,'read_only',2),
(741,'read_rnd_buffer_size',0),
(743,'relay_log',2),
(745,'relay_log_basename',2),
(747,'relay_log_index',2),
(748,'relay_log_info_file',2),
(752,'relay_log_purge',2),
(753,'relay_log_recovery',2),
(755,'relay_log_space_limit',0),
(757,'replicate_annotate_row_events',2),
(759,'replicate_do_db',2),
(761,'replicate_do_table',2),
(763,'replicate_events_marked_for_skip',2),
(765,'replicate_ignore_db',2),
(766,'replicate_ignore_table',2),
(768,'replicate_wild_do_table',2),
(771,'replicate_wild_ignore_table',2),
(772,'report_host',2),
(774,'report_password',2),
(776,'report_port',0),
(779,'report_user',2),
(780,'rocksdb_access_hint_on_compaction_start',0),
(782,'innodb_use_sys_malloc',2),
(783,'rocksdb_advise_random_on_open',2),
(785,'rocksdb_allow_concurrent_memtable_write',2),
(787,'rocksdb_allow_mmap_reads',2),
(789,'rocksdb_allow_mmap_writes',2),
(791,'rocksdb_blind_delete_primary_key',2),
(794,'rocksdb_block_cache_size',0),
(796,'rocksdb_block_restart_interval',0),
(798,'rocksdb_block_size',0),
(800,'rocksdb_block_size_deviation',0),
(802,'rocksdb_bulk_load',2),
(804,'rocksdb_bulk_load_size',0),
(805,'rocksdb_bytes_per_sync',0),
(807,'rocksdb_cache_index_and_filter_blocks',2),
(809,'rocksdb_checksums_pct',0),
(811,'rocksdb_collect_sst_properties',2),
(813,'rocksdb_commit_in_the_middle',2),
(816,'rocksdb_compact_cf',2),
(818,'rocksdb_compaction_readahead_size',0),
(821,'rocksdb_compaction_sequential_deletes',0),
(823,'rocksdb_compaction_sequential_deletes_count_sd',2),
(824,'rocksdb_compaction_sequential_deletes_file_size',0),
(827,'rocksdb_compaction_sequential_deletes_window',0),
(830,'rocksdb_create_checkpoint',2),
(832,'rocksdb_create_if_missing',2),
(834,'rocksdb_create_missing_column_families',2),
(836,'rocksdb_datadir',2),
(837,'rocksdb_db_write_buffer_size',0),
(839,'rocksdb_deadlock_detect',2),
(841,'rocksdb_debug_optimizer_no_zero_cardinality',2),
(843,'rocksdb_debug_ttl_read_filter_ts',0),
(844,'rocksdb_debug_ttl_rec_ts',0),
(846,'rocksdb_debug_ttl_snapshot_ts',0),
(849,'rocksdb_default_cf_options',2),
(851,'rocksdb_delayed_write_rate',0),
(852,'rocksdb_delete_obsolete_files_period_micros',0),
(854,'rocksdb_enable_2pc',2),
(857,'rocksdb_enable_bulk_load_api',2),
(859,'rocksdb_enable_thread_tracking',2),
(860,'rocksdb_enable_ttl',2),
(862,'rocksdb_enable_ttl_read_filtering',2),
(864,'rocksdb_enable_write_thread_adaptive_yield',2),
(870,'rocksdb_error_if_exists',2),
(871,'rocksdb_flush_log_at_trx_commit',0),
(874,'rocksdb_flush_memtable_on_analyze',2),
(877,'rocksdb_force_compute_memtable_stats',2),
(879,'rocksdb_force_flush_memtable_and_lzero_now',2),
(882,'rocksdb_force_flush_memtable_now',2),
(883,'rocksdb_force_index_records_in_range',0),
(885,'rocksdb_hash_index_allow_collision',2),
(887,'rocksdb_index_type',2),
(889,'rocksdb_info_log_level',2),
(892,'rocksdb_io_write_timeout',0),
(894,'rocksdb_is_fd_close_on_exec',2),
(896,'rocksdb_keep_log_file_num',0),
(897,'rocksdb_lock_scanned_rows',2),
(900,'rocksdb_lock_wait_timeout',0),
(902,'rocksdb_log_file_time_to_roll',0),
(905,'rocksdb_manifest_preallocation_size',0),
(907,'rocksdb_master_skip_tx_api',2),
(908,'rocksdb_max_background_jobs',0),
(910,'rocksdb_max_log_file_size',0),
(911,'rocksdb_max_manifest_file_size',1),
(915,'rocksdb_max_open_files',1),
(918,'rocksdb_max_row_locks',0),
(921,'rocksdb_max_subcompactions',0),
(924,'rocksdb_max_total_wal_size',0),
(927,'rocksdb_merge_buf_size',0),
(929,'rocksdb_merge_combine_read_size',0),
(930,'rocksdb_new_table_reader_for_compaction_inputs',2),
(933,'rocksdb_no_block_cache',2),
(935,'rocksdb_override_cf_options',2),
(937,'rocksdb_paranoid_checks',2),
(938,'rocksdb_pause_background_work',2),
(939,'rocksdb_perf_context_level',0),
(940,'rocksdb_persistent_cache_path',2),
(943,'rocksdb_persistent_cache_size_mb',0),
(944,'rocksdb_pin_l0_filter_and_index_blocks_in_cache',2),
(947,'rocksdb_print_snapshot_conflict_queries',2),
(949,'rocksdb_rate_limiter_bytes_per_sec',0),
(952,'rocksdb_read_free_rpl_tables',2),
(954,'rocksdb_records_in_range',0),
(956,'rocksdb_reset_stats',2),
(958,'rocksdb_seconds_between_stat_computes',0),
(961,'rocksdb_signal_drop_index_thread',2),
(963,'rocksdb_skip_bloom_filter_on_read',2),
(965,'rocksdb_skip_fill_cache',2),
(967,'rocksdb_skip_unique_check_tables',2),
(969,'rocksdb_sst_mgr_rate_bytes_per_sec',0),
(971,'rocksdb_stats_dump_period_sec',0),
(973,'rocksdb_store_row_debug_checksums',2),
(975,'rocksdb_strict_collation_check',2),
(977,'rocksdb_strict_collation_exceptions',2),
(979,'rocksdb_supported_compression_types',2),
(981,'rocksdb_table_cache_numshardbits',0),
(983,'rocksdb_table_stats_sampling_pct',0),
(985,'rocksdb_tmpdir',2),
(988,'rocksdb_trace_sst_api',2),
(990,'rocksdb_unsafe_for_binlog',2),
(992,'rocksdb_update_cf_options',2),
(995,'rocksdb_use_adaptive_mutex',2),
(997,'rocksdb_use_direct_io_for_flush_and_compaction',2),
(999,'rocksdb_use_direct_reads',2),
(1001,'rocksdb_use_fsync',2),
(1003,'rocksdb_validate_tables',0),
(1005,'rocksdb_verify_row_debug_checksums',2),
(1010,'rocksdb_wal_bytes_per_sync',0),
(1012,'rocksdb_wal_dir',2),
(1014,'rocksdb_wal_recovery_mode',0),
(1016,'rocksdb_wal_size_limit_mb',0),
(1018,'rocksdb_wal_ttl_seconds',0),
(1020,'rocksdb_whole_key_filtering',2),
(1023,'rocksdb_write_batch_max_bytes',0),
(1024,'rocksdb_write_disable_wal',2),
(1026,'rocksdb_write_ignore_missing_column_families',2),
(1029,'rowid_merge_buff_size',0),
(1031,'secure_auth',2),
(1034,'secure_file_priv',2),
(1035,'server_id',0),
(1037,'session_track_schema',2),
(1039,'session_track_state_change',2),
(1041,'session_track_system_variables',2),
(1042,'session_track_transaction_info',2),
(1044,'skip_external_locking',2),
(1045,'skip_name_resolve',2),
(1047,'skip_networking',2),
(1048,'skip_show_database',2),
(1050,'slave_compressed_protocol',2),
(1051,'slave_ddl_exec_mode',2),
(1053,'slave_domain_parallel_threads',0),
(1055,'slave_exec_mode',2),
(1057,'slave_load_tmpdir',2),
(1060,'slave_max_allowed_packet',0),
(1063,'slave_net_timeout',0),
(1065,'slave_parallel_max_queued',0),
(1066,'slave_parallel_mode',2),
(1068,'slave_parallel_threads',0),
(1070,'slave_parallel_workers',0),
(1072,'slave_run_triggers_for_rbr',2),
(1076,'slave_skip_errors',2),
(1078,'slave_sql_verify_checksum',2),
(1080,'slave_transaction_retries',0),
(1081,'slave_type_conversions',2),
(1083,'slow_launch_time',0),
(1084,'slow_query_log',2),
(1086,'slow_query_log_file',2),
(1088,'socket',2),
(1090,'sort_buffer_size',0),
(1093,'spider_auto_increment_mode',1),
(1094,'spider_bgs_first_read',1),
(1095,'spider_bgs_mode',1),
(1096,'spider_bgs_second_read',1),
(1099,'spider_bka_engine',2),
(1101,'spider_bka_mode',1),
(1103,'spider_bka_table_name_type',1),
(1104,'spider_block_size',0),
(1106,'spider_bulk_size',1),
(1108,'spider_bulk_update_mode',1),
(1110,'spider_bulk_update_size',1),
(1113,'spider_casual_read',1),
(1115,'spider_conn_recycle_mode',0),
(1116,'spider_conn_recycle_strict',0),
(1119,'spider_connect_error_interval',0),
(1121,'spider_connect_mutex',2),
(1123,'spider_connect_retry_count',0),
(1124,'spider_connect_retry_interval',0),
(1128,'spider_connect_timeout',1),
(1130,'spider_crd_bg_mode',1),
(1131,'spider_crd_interval',1),
(1135,'spider_crd_mode',1),
(1137,'spider_crd_sync',1),
(1140,'spider_crd_type',1),
(1141,'spider_crd_weight',1),
(1144,'spider_delete_all_rows_type',1),
(1145,'spider_direct_dup_insert',1),
(1147,'spider_direct_order_limit',1),
(1148,'spider_dry_access',2),
(1151,'spider_error_read_mode',1),
(1153,'spider_error_write_mode',1),
(1154,'spider_first_read',1),
(1156,'spider_force_commit',0),
(1159,'spider_general_log',2),
(1161,'sql_auto_is_null',2),
(1162,'spider_init_sql_alloc_size',1),
(1163,'sql_big_selects',2),
(1164,'sql_buffer_result',2),
(1165,'sql_log_bin',2),
(1166,'spider_internal_limit',1),
(1167,'sql_log_off',2),
(1168,'spider_internal_offset',1),
(1169,'sql_mode',2),
(1170,'spider_internal_optimize',1),
(1171,'spider_internal_optimize_local',1),
(1172,'sql_notes',2),
(1173,'sql_quote_show_create',2),
(1174,'spider_internal_sql_log_off',2),
(1175,'sql_safe_updates',2),
(1176,'sql_select_limit',1),
(1177,'spider_internal_unlock',2),
(1178,'sql_slave_skip_counter',0),
(1179,'spider_internal_xa',2),
(1180,'sql_warnings',2),
(1181,'spider_internal_xa_id_type',0),
(1182,'spider_internal_xa_snapshot',0),
(1183,'ssl_ca',2),
(1184,'ssl_capath',2),
(1185,'spider_local_lock_table',2),
(1186,'ssl_cert',2),
(1187,'ssl_cipher',2),
(1188,'spider_lock_exchange',2),
(1189,'ssl_crl',2),
(1190,'ssl_crlpath',2),
(1191,'spider_log_result_error_with_sql',0),
(1192,'ssl_key',2),
(1193,'storage_engine',2),
(1194,'spider_log_result_errors',0),
(1195,'stored_program_cache',0),
(1196,'spider_low_mem_read',1),
(1197,'strict_password_validation',2),
(1198,'spider_max_order',1),
(1199,'sync_binlog',0),
(1200,'sync_frm',2),
(1201,'spider_multi_split_read',1),
(1202,'spider_net_read_timeout',1),
(1203,'sync_master_info',0),
(1204,'sync_relay_log',0),
(1205,'spider_net_write_timeout',1),
(1206,'sync_relay_log_info',0),
(1207,'system_time_zone',2),
(1208,'spider_ping_interval_at_trx_start',0),
(1209,'spider_quick_mode',1),
(1210,'table_definition_cache',0),
(1211,'table_open_cache',0),
(1212,'spider_quick_page_size',1),
(1213,'thread_cache_size',0),
(1214,'spider_read_only_mode',1),
(1215,'thread_concurrency',0),
(1216,'thread_handling',2),
(1217,'thread_pool_idle_timeout',0),
(1218,'spider_remote_access_charset',2),
(1219,'thread_pool_max_threads',0),
(1220,'thread_pool_oversubscribe',0),
(1221,'thread_pool_size',0),
(1222,'spider_remote_autocommit',1),
(1223,'thread_pool_stall_limit',0),
(1224,'thread_stack',0),
(1225,'spider_remote_default_database',2),
(1226,'time_format',2),
(1227,'spider_remote_sql_log_off',1),
(1228,'time_zone',2),
(1229,'timed_mutexes',2),
(1230,'spider_remote_time_zone',2),
(1231,'tmp_table_size',0),
(1232,'tmpdir',2),
(1233,'spider_remote_trx_isolation',1),
(1234,'transaction_alloc_block_size',0),
(1235,'transaction_prealloc_size',0),
(1236,'spider_reset_sql_alloc',1),
(1237,'tx_isolation',2),
(1238,'spider_same_server_link',2),
(1239,'tx_read_only',2),
(1240,'unique_checks',2),
(1241,'updatable_views_with_limit',2),
(1242,'spider_second_read',1),
(1243,'use_stat_tables',2),
(1244,'spider_select_column_mode',1),
(1245,'userstat',2),
(1246,'version',2),
(1247,'version_comment',2),
(1248,'spider_selupd_lock_mode',1),
(1249,'version_compile_machine',2),
(1250,'spider_semi_split_read',1),
(1251,'version_compile_os',2),
(1252,'version_malloc_library',2),
(1253,'spider_semi_split_read_limit',1),
(1254,'version_ssl_library',2),
(1255,'wait_timeout',0),
(1256,'spider_semi_table_lock',0),
(1257,'wsrep_osu_method',2),
(1258,'wsrep_auto_increment_control',2),
(1259,'spider_semi_table_lock_connection',1),
(1260,'wsrep_causal_reads',2),
(1261,'spider_semi_trx',2),
(1262,'wsrep_certify_nonpk',2),
(1263,'spider_semi_trx_isolation',1),
(1264,'wsrep_cluster_address',2),
(1265,'wsrep_cluster_name',2),
(1266,'spider_skip_default_condition',1),
(1267,'spider_split_read',1),
(1268,'wsrep_convert_lock_to_trx',2),
(1269,'wsrep_data_home_dir',2),
(1270,'spider_sts_bg_mode',1),
(1271,'spider_sts_interval',1),
(1272,'wsrep_dbug_option',2),
(1273,'spider_sts_mode',1),
(1274,'wsrep_debug',2),
(1275,'spider_sts_sync',1),
(1276,'wsrep_desync',2),
(1277,'spider_support_xa',2),
(1278,'wsrep_dirty_reads',2),
(1279,'spider_sync_autocommit',2),
(1280,'wsrep_drupal_282555_workaround',2),
(1281,'spider_sync_time_zone',2),
(1282,'wsrep_forced_binlog_format',2),
(1283,'wsrep_gtid_domain_id',0),
(1284,'wsrep_gtid_mode',2),
(1285,'spider_sync_trx_isolation',2),
(1286,'spider_table_init_error_interval',0),
(1287,'wsrep_load_data_splitting',2),
(1288,'spider_udf_ct_bulk_insert_interval',1),
(1289,'wsrep_log_conflicts',2),
(1290,'wsrep_max_ws_rows',0),
(1291,'wsrep_max_ws_size',0),
(1292,'spider_udf_ct_bulk_insert_rows',1),
(1293,'spider_udf_ds_bulk_insert_rows',1),
(1294,'wsrep_mysql_replication_bundle',0),
(1295,'wsrep_node_address',2),
(1296,'wsrep_node_incoming_address',2),
(1297,'spider_udf_ds_table_loop_mode',1),
(1298,'wsrep_node_name',2),
(1299,'wsrep_notify_cmd',2),
(1300,'wsrep_on',2),
(1301,'spider_udf_ds_use_real_table',1),
(1302,'wsrep_patch_version',2),
(1303,'wsrep_provider',2),
(1304,'spider_udf_table_lock_mutex_count',0),
(1305,'wsrep_provider_options',2),
(1306,'wsrep_recover',2),
(1307,'spider_udf_table_mon_mutex_count',0),
(1308,'wsrep_replicate_myisam',2),
(1309,'wsrep_restart_slave',2),
(1310,'wsrep_retry_autocommit',0),
(1311,'spider_use_all_conns_snapshot',2),
(1312,'wsrep_slave_fk_checks',2),
(1313,'spider_use_consistent_snapshot',2),
(1314,'wsrep_slave_uk_checks',2),
(1315,'wsrep_slave_threads',0),
(1316,'wsrep_sst_auth',2),
(1317,'spider_use_default_database',2),
(1318,'wsrep_sst_donor',2),
(1319,'spider_use_flash_logs',2),
(1320,'wsrep_sst_donor_rejects_queries',2),
(1321,'wsrep_sst_method',2),
(1322,'wsrep_sst_receive_address',2),
(1323,'spider_use_handler',1),
(1324,'wsrep_start_position',2),
(1325,'wsrep_sync_wait',0),
(1326,'spider_use_pushdown_udf',1),
(1327,'spider_use_snapshot_with_flush_tables',0),
(1328,'spider_use_table_charset',1),
(1329,'spider_version',2),
(1349,'standard_compliant_cte',2),
(1361,'table_open_cache_instances',0),
(1368,'thread_pool_prio_kickup_timer',0),
(1369,'thread_pool_priority',2),
(1376,'tmp_disk_table_size',1),
(1377,'tmp_memory_table_size',0),
(1380,'tokudb_alter_print_error',2),
(1381,'tokudb_analyze_delete_fraction',1),
(1382,'tokudb_analyze_in_background',2),
(1383,'tokudb_analyze_mode',2),
(1384,'tokudb_analyze_throttle',0),
(1385,'tokudb_analyze_time',0),
(1386,'tokudb_auto_analyze',0),
(1387,'tokudb_block_size',0),
(1388,'tokudb_bulk_fetch',2),
(1389,'tokudb_cache_size',0),
(1390,'tokudb_cachetable_pool_threads',0),
(1391,'tokudb_cardinality_scale_percent',0),
(1392,'tokudb_check_jemalloc',2),
(1393,'tokudb_checkpoint_lock',2),
(1394,'tokudb_checkpoint_on_flush_logs',2),
(1395,'tokudb_checkpoint_pool_threads',0),
(1396,'tokudb_checkpointing_period',0),
(1397,'tokudb_cleaner_iterations',0),
(1398,'tokudb_cleaner_period',0),
(1399,'tokudb_client_pool_threads',0),
(1400,'tokudb_commit_sync',2),
(1401,'tokudb_compress_buffers_before_eviction',2),
(1402,'tokudb_create_index_online',2),
(1403,'tokudb_data_dir',2),
(1404,'tokudb_debug',0),
(1405,'tokudb_dir_per_db',2),
(1406,'tokudb_directio',2),
(1407,'tokudb_disable_hot_alter',2),
(1408,'tokudb_disable_prefetching',2),
(1409,'tokudb_disable_slow_alter',2),
(1410,'tokudb_empty_scan',2),
(1411,'tokudb_enable_partial_eviction',2),
(1412,'tokudb_fanout',0),
(1413,'tokudb_fs_reserve_percent',0),
(1414,'tokudb_fsync_log_period',0),
(1415,'tokudb_hide_default_row_format',2),
(1416,'tokudb_killed_time',0),
(1417,'tokudb_last_lock_timeout',2),
(1418,'tokudb_load_save_space',2),
(1419,'tokudb_loader_memory_size',0),
(1420,'tokudb_lock_timeout',0),
(1421,'tokudb_lock_timeout_debug',0),
(1422,'tokudb_log_dir',2),
(1423,'tokudb_max_lock_memory',0),
(1424,'tokudb_optimize_index_fraction',1),
(1425,'tokudb_optimize_index_name',2),
(1426,'tokudb_optimize_throttle',0),
(1427,'tokudb_pk_insert_mode',0),
(1428,'tokudb_prelock_empty',2),
(1429,'tokudb_read_block_size',0),
(1430,'tokudb_read_buf_size',0),
(1431,'tokudb_read_status_frequency',0),
(1432,'tokudb_row_format',2),
(1433,'tokudb_rpl_check_readonly',2),
(1434,'tokudb_rpl_lookup_rows',2),
(1435,'tokudb_rpl_lookup_rows_delay',0),
(1436,'tokudb_rpl_unique_checks',2),
(1437,'tokudb_rpl_unique_checks_delay',0),
(1438,'tokudb_strip_frm_data',2),
(1439,'tokudb_support_xa',2),
(1440,'tokudb_tmp_dir',2),
(1441,'tokudb_version',2),
(1442,'tokudb_write_status_frequency',0),
(1501,'engine_condition_pushdown',2),
(1502,'have_csv',2),
(1503,'have_innodb',2),
(1504,'have_ndbcluster',2),
(1505,'have_partitioning',2),
(1506,'innodb_adaptive_flushing_method',2),
(1507,'innodb_blocking_buffer_pool_restore',2),
(1508,'innodb_buffer_pool_restore_at_startup',0),
(1509,'innodb_buffer_pool_shm_checksum',2),
(1510,'innodb_buffer_pool_shm_key',0),
(1511,'innodb_changed_pages_limit',0),
(1512,'innodb_checkpoint_age_target',0),
(1513,'innodb_dict_size_limit',0),
(1514,'innodb_doublewrite_file',2),
(1515,'innodb_fast_checksum',2),
(1516,'innodb_flush_neighbor_pages',2),
(1517,'innodb_ibuf_accel_rate',0),
(1518,'innodb_ibuf_active_contract',0),
(1519,'innodb_ibuf_max_size',0),
(1520,'innodb_import_table_from_xtrabackup',0),
(1521,'innodb_lazy_drop_table',0),
(1522,'innodb_merge_sort_block_size',0),
(1523,'innodb_read_ahead',2),
(1524,'innodb_recovery_stats',2),
(1525,'innodb_recovery_update_relay_log',2),
(1526,'innodb_stats_auto_update',0),
(1527,'innodb_stats_update_need_lock',0),
(1528,'innodb_thread_concurrency_timer_based',2),
(1529,'innodb_use_sys_stats_table',2),
(1530,'log',2),
(1531,'log_slow_queries',2),
(1532,'rpl_recovery_rank',0),
(1533,'sql_big_tables',2),
(1534,'sql_low_priority_updates',2),
(1535,'sql_max_join_size',1),
(1536,'error_count',0),
(1537,'have_community_features',2),
(1538,'identity',0),
(1539,'insert_id',0),
(1540,'language',2),
(1541,'last_insert_id',0),
(1542,'log_bin_trust_routine_creators',2),
(1543,'new',2),
(1544,'pseudo_thread_id',0),
(1545,'rand_seed1',2),
(1546,'rand_seed2',2),
(1547,'sql_log_update',2),
(1548,'table_lock_wait_timeout',0),
(1549,'table_type',2),
(1550,'timestamp',0),
(1551,'warning_count',0),
(1552,'server_audit_events',2),
(1553,'server_audit_excl_users',2),
(1554,'server_audit_file_path',2),
(1555,'server_audit_file_rotate_now',2),
(1556,'server_audit_file_rotate_size',0),
(1557,'server_audit_file_rotations',0),
(1558,'server_audit_incl_users',2),
(1559,'server_audit_logging',2),
(1560,'server_audit_mode',0),
(1561,'server_audit_output_type',2),
(1562,'server_audit_syslog_facility',2),
(1563,'server_audit_syslog_ident',2),
(1564,'server_audit_syslog_info',2),
(1565,'server_audit_syslog_priority',2),
(1566,'innodb_file_io_threads',0),
(1567,'innodb_use_legacy_cardinality_algorithm',2);";
        $db->sql_query($sql);
    }

}
