# Variables `global_variable` non par defaut pour `id_mysql_server = 1`

Comparaison:

- source instance: `pmacontrol.global_variable`
- reference: `information_schema.SYSTEM_VARIABLES.DEFAULT_VALUE`
- filtre principal: `GLOBAL_VALUE_ORIGIN IN ('CONFIG','AUTO')`

Colonnes:

- `variable_name`
- `value` : valeur relevee dans `pmacontrol.global_variable`
- `default_value`
- `global_value`
- `origin`

Nombre de variables retenues: `97`

| variable_name | value | default_value | global_value | origin |
|---|---|---|---|---|
| back_log | 70 | 150 | 70 | AUTO |
| basedir | /usr |  | /usr | CONFIG |
| bind_address | 0.0.0.0 |  | 0.0.0.0 | CONFIG |
| binlog_expire_logs_seconds | 3600 | 0 | 3600 | CONFIG |
| binlog_format | ROW | MIXED | ROW | CONFIG |
| bulk_insert_buffer_size | 16777216 | 8388608 | 16777216 | CONFIG |
| character_sets_dir | /usr/share/mysql/charsets/ |  | /usr/share/mysql/charsets/ | AUTO |
| concurrent_insert | ALWAYS | AUTO | ALWAYS | CONFIG |
| event_scheduler | ON | OFF | ON | CONFIG |
| general_log_file | /srv/mysql/log/general.log |  | /srv/mysql/log/general.log | CONFIG |
| host_cache_size | 228 | 128 | 228 | AUTO |
| innodb_autoextend_increment | 1000 | 64 | 1000 | CONFIG |
| innodb_autoinc_lock_mode | 2 | 1 | 2 | CONFIG |
| innodb_buffer_pool_size | 1140850688 | 134217728 | 1140850688 | CONFIG |
| innodb_buffer_pool_size_auto_min | 1073741824 | 0 | 1073741824 | CONFIG |
| innodb_buffer_pool_size_max | 3221225472 | 0 | 3221225472 | CONFIG |
| innodb_flush_log_at_trx_commit | 2 | 1 | 2 | CONFIG |
| innodb_io_capacity | 2000 | 200 | 2000 | CONFIG |
| innodb_log_buffer_size | 8388608 | 16777216 | 8388608 | CONFIG |
| innodb_log_file_size | 994050048 | 100663296 | 994050048 | CONFIG |
| innodb_max_dirty_pages_pct | 70 | 90.000000 | 70.000000 | CONFIG |
| innodb_open_files | 2000 | 0 | 2000 | CONFIG |
| innodb_rollback_on_timeout | ON | OFF | ON | CONFIG |
| key_cache_segments | 64 | 0 | 64 | CONFIG |
| lc_messages_dir | /usr/share/mysql |  | /usr/share/mysql | CONFIG |
| log_error | /srv/mysql/log/error.log | 0 | /srv/mysql/log/error.log | CONFIG |
| log_slave_updates | ON | OFF | ON | CONFIG |
| log_slow_verbosity | query_plan |  | query_plan | CONFIG |
| long_query_time | 1 | 10.000000 | 1.000000 | CONFIG |
| lower_case_file_system | OFF | NULL | OFF | AUTO |
| max_allowed_packet | 268435456 | 16777216 | 268435456 | CONFIG |
| max_binlog_size | 104857600 | 1073741824 | 104857600 | CONFIG |
| max_connections | 100 | 151 | 100 | CONFIG |
| max_heap_table_size | 805306368 | 16777216 | 805306368 | CONFIG |
| max_relay_log_size | 104857600 | 1073741824 | 104857600 | AUTO |
| metadata_locks_cache_size | 10000 | 1024 | 10000 | CONFIG |
| myisam_recover_options | BACKUP | BACKUP,QUICK | BACKUP | CONFIG |
| myisam_sort_buffer_size | 536870912 | 134216704 | 536870912 | CONFIG |
| open_files_limit | 160139 | 0 | 160139 | AUTO |
| performance_schema | ON | OFF | ON | CONFIG |
| performance_schema_digests_size | 5000 | -1 | 5000 | AUTO |
| performance_schema_events_stages_history_long_size | 1000 | -1 | 1000 | AUTO |
| performance_schema_events_stages_history_size | 10 | -1 | 10 | AUTO |
| performance_schema_events_statements_history_long_size | 1000 | -1 | 1000 | AUTO |
| performance_schema_events_statements_history_size | 500 | -1 | 500 | CONFIG |
| performance_schema_events_transactions_history_long_size | 1000 | -1 | 1000 | AUTO |
| performance_schema_events_transactions_history_size | 10 | -1 | 10 | AUTO |
| performance_schema_events_waits_history_long_size | 1000 | -1 | 1000 | AUTO |
| performance_schema_events_waits_history_size | 10 | -1 | 10 | AUTO |
| performance_schema_session_connect_attrs_size | 512 | -1 | 512 | AUTO |
| pid_file | /var/run/mysqld/mysqld.pid |  | /var/run/mysqld/mysqld.pid | CONFIG |
| plugin_dir | /usr/lib/mysql/plugin/ |  | /usr/lib/mysql/plugin/ | CONFIG |
| port | 3306 | 0 | 3306 | CONFIG |
| query_cache_limit | 131072 | 1048576 | 131072 | CONFIG |
| query_cache_size | 0 | 1048576 | 0 | CONFIG |
| query_response_time_stats | ON | OFF | ON | CONFIG |
| read_buffer_size | 2097152 | 131072 | 2097152 | CONFIG |
| read_rnd_buffer_size | 1048576 | 262144 | 1048576 | CONFIG |
| relay_log | /srv/mysql/relaylog/relay-bin |  | /srv/mysql/relaylog/relay-bin | CONFIG |
| relay_log_info_file | /srv/mysql/relaylog/relay-bin.info |  | /srv/mysql/relaylog/relay-bin.info | CONFIG |
| report_host | ist-pmacontrol |  | ist-pmacontrol | CONFIG |
| report_port | 3306 | 0 | 3306 | AUTO |
| rocksdb_block_cache_size | 4294967296 | 536870912 | 4294967296 | CONFIG |
| rocksdb_flush_log_at_trx_commit | 2 | 1 | 2 | CONFIG |
| rocksdb_max_background_jobs | 8 | 2 | 8 | CONFIG |
| rocksdb_use_direct_io_for_flush_and_compaction | ON | OFF | ON | CONFIG |
| rocksdb_wal_recovery_mode | 2 | 1 | 2 | CONFIG |
| server_id | 394663081 | 1 | 394663081 | CONFIG |
| skip_name_resolve | ON | OFF | ON | CONFIG |
| slave_load_tmpdir | /srv/mysql/tmp |  | /srv/mysql/tmp | AUTO |
| slow_query_log | ON | OFF | ON | CONFIG |
| slow_query_log_file | /srv/mysql/log/mariadb-slow.log |  | /srv/mysql/log/mariadb-slow.log | CONFIG |
| socket | /var/run/mysqld/mysqld.sock |  | /var/run/mysqld/mysqld.sock | CONFIG |
| sort_buffer_size | 33554432 | 2097152 | 33554432 | CONFIG |
| sql_mode | NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION | STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION | NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION | CONFIG |
| sync_binlog | 10000 | 0 | 10000 | CONFIG |
| table_definition_cache | 10000 | 400 | 10000 | CONFIG |
| table_open_cache | 10000 | 2000 | 10000 | CONFIG |
| thread_cache_size | 100 | 256 | 100 | AUTO |
| thread_stack | 524288 | 299008 | 524288 | CONFIG |
| tmpdir | /srv/mysql/tmp |  | /srv/mysql/tmp | CONFIG |
| tmp_table_size | 805306368 | 16777216 | 805306368 | CONFIG |
| transaction_prealloc_size | 8192 | 4096 | 8192 | CONFIG |
| userstat | ON | OFF | ON | CONFIG |
| wait_timeout | 600 | 28800 | 600 | CONFIG |
| wsrep_cluster_address | gcomm:// |  | gcomm:// | CONFIG |
| wsrep_cluster_name | 68Koncept | my_wsrep_cluster | 68Koncept | CONFIG |
| wsrep_gtid_mode | ON | OFF | ON | CONFIG |
| wsrep_log_conflicts | ON | OFF | ON | CONFIG |
| wsrep_max_ws_rows | 131072 | 0 | 131072 | CONFIG |
| wsrep_max_ws_size | 1073741824 | 2147483647 | 1073741824 | CONFIG |
| wsrep_node_address | 10.68.68.111 |  | 10.68.68.111 | CONFIG |
| wsrep_provider | /usr/lib/galera/libgalera_smm.so | none | /usr/lib/galera/libgalera_smm.so | CONFIG |
| wsrep_provider_options | gcache.size = 20G |  | gcache.size = 20G | CONFIG |
| wsrep_slave_threads | 4 | 1 | 4 | CONFIG |
| wsrep_sst_auth | sst:QSEDWGRg133 |  | sst:QSEDWGRg133 | CONFIG |
| wsrep_sst_method | xtrabackup-v2 | rsync | xtrabackup-v2 | CONFIG |
