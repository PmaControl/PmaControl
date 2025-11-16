<?php

namespace App\Controller;

use Exception;
use \Glial\Synapse\Controller;
use \App\Library\Extraction2;
use \App\Library\Debug;
use \App\Library\Mysql;
use \Glial\Sgbd\Sgbd;

class Digest extends Controller
{
    static $database = [];

    static $cache_dispatch = [];

    static $logger;

    public function before($param)
    {
        Debug::parseDebug($param);
        Debug::debug($param);
    }

    public static function integrate($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = $param[0] ?? "";
        $date = $param[1];

        if (empty($id_mysql_server)) {
            throw new \Exception("Impossible to get id_mysql_server : '".$id_mysql_server."'");
        }

        $queries = Extraction2::display(array("performance_schema::events_statements_summary_by_digest"), 
        array($id_mysql_server), array($date));

        if (empty($queries[$id_mysql_server]['events_statements_summary_by_digest'])) {
            //message of error
            return true;
        }

        $elems  = $queries[$id_mysql_server]['events_statements_summary_by_digest'];
        
        $register = self::insertDigest([$id_mysql_server,$date, $elems ]);

        $i = 0;

        $SQL = [];
        $keys = [];

        $data_to_insert = [];

        foreach($elems as $query)
        {
            $i++;

            $data_lower = $query;

            //SCHEMA_NAME
            $id_mysql_database = self::getIdDatabase(array($id_mysql_server, $query['schema_name']));

            if (empty($id_mysql_server))
            {
                throw new Exception("Couldn't find database  (id_mysql_server: $id_mysql_server) : ".implode(',', $data_lower));
                //$this->logger->warning("Couldn't find database  (id_mysql_server: $id_mysql_server) : ".implode(',', $data_lower));
            }

            $id_mysql_digest = $register[$data_lower['digest']];

            /*
            $result = array_filter($data_lower, function($value, $key) {
                return (strpos($key, 'sum') === 0 || strpos($key, 'count_star') === 0);
            }, ARRAY_FILTER_USE_BOTH);
            */

            $query['id_mysql_digest'] = $id_mysql_digest;
            $query['id_mysql_server'] = $id_mysql_server;
            $query['id_mysql_database'] = $id_mysql_database;
            
            $id_mysql_database__mysql_digest = self::getIdCacheDispatch($query);

            $insert = [
                    'date'                        => $date,
                    'id_mysql_database__mysql_digest' => $id_mysql_database__mysql_digest,
                    'count_star'                  => $query['count_star'],
                    'sum_timer_wait'              => $query['sum_timer_wait'],
                    'min_timer_wait'              => $query['min_timer_wait'],
                    'avg_timer_wait'              => $query['avg_timer_wait'],
                    'max_timer_wait'              => $query['max_timer_wait'],
                    'sum_lock_time'               => $query['sum_lock_time'],
                    'sum_errors'                  => $query['sum_errors'],
                    'sum_warnings'                => $query['sum_warnings'],
                    'sum_rows_affected'           => $query['sum_rows_affected'],
                    'sum_rows_sent'               => $query['sum_rows_sent'],
                    'sum_rows_examined'           => $query['sum_rows_examined'],
                    'sum_created_tmp_disk_tables' => $query['sum_created_tmp_disk_tables'],
                    'sum_created_tmp_tables'      => $query['sum_created_tmp_tables'],
                    'sum_select_full_join'        => $query['sum_select_full_join'],
                    'sum_select_full_range_join'  => $query['sum_select_full_range_join'],
                    'sum_select_range'            => $query['sum_select_range'],
                    'sum_select_range_check'      => $query['sum_select_range_check'],
                    'sum_select_scan'             => $query['sum_select_scan'],
                    'sum_sort_merge_passes'       => $query['sum_sort_merge_passes'],
                    'sum_sort_range'              => $query['sum_sort_range'],
                    'sum_sort_rows'               => $query['sum_sort_rows'],
                    'sum_sort_scan'               => $query['sum_sort_scan'],
                    'sum_no_index_used'           => $query['sum_no_index_used'],
                    'sum_no_good_index_used'      => $query['sum_no_good_index_used'],

                    // 👇 Champs potentiellement absents
                    'sum_cpu_time'                => $query['sum_cpu_time']                ?? null,
                    'max_controlled_memory'       => $query['max_controlled_memory']       ?? null,
                    'max_total_memory'            => $query['max_total_memory']            ?? null,
                    'count_secondary'             => $query['count_secondary']             ?? null,
                    'quantile_95'                 => $query['quantile_95']                 ?? null,
                    'quantile_99'                 => $query['quantile_99']                 ?? null,
                    'quantile_999'                => $query['quantile_999']                ?? null,
                    'query_sample_text'           => $query['query_sample_text']           ?? null,
                    'query_sample_seen'           => $query['query_sample_seen']           ?? null,
                    'query_sample_timer_wait'     => $query['query_sample_timer_wait']     ?? null,

                    'first_seen'                  => $query['first_seen'],
                    'last_seen'                   => $query['last_seen'],
                ];

            $data_to_insert[] = $insert;

        }

        $table['ts_mysql_digest_stat'] = $data_to_insert;


        $sqls = self::insert($table);

        // TODO a multithread
        foreach($sqls as $sql) {
            $db->sql_query($sql);
        }

        Debug::debug($sql, "QUERIES");
        //$db->sql_query($sql);

        //Debug::debug($sql);


    }

    public static function insertDigest($param)
    {
        list($id_mysql_server,$date, $queries ) = $param;

        $digests = [];

        foreach ($queries as $row) {
            if (!empty($row['digest'])) {

                if (! isset($digests[$row['digest']])) {
                    $digests[$row['digest']] = $row['digest'];
                }
                
            }
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $nb_id = count($digests);

        $list = implode("','", $digests);

        $sql2 = "SELECT count(1) as cpt FROM mysql_digest WHERE digest IN ('".$list."')";
        //Debug::sql($sql2);
        $res2 = $db->sql_query($sql2);

        while ($ob2 = $db->sql_fetch_object($res2))
        {
            Debug::debug($nb_id, "NB_DIGEST");
            Debug::debug($ob2->cpt, "COUNT_DIGEST");

            if ($nb_id > $ob2->cpt) {
                $register = self::selectIdfromDigest([$digests]);
                $keys = array_keys($register);

                $sql3 = "INSERT IGNORE INTO mysql_digest (`digest`,`digest_text`, digest_text_md5) VALUES ";

                foreach($queries as $query) {
                    if (in_array($query['digest'], $keys)) {
                        continue;
                    }

                    $sql3 .= " (
                    '".$query['digest']."',
                    '".$db->sql_real_escape_string($query['digest_text'])."',
                    '".self::getHash(param: array($query['digest_text']))."'
                    ),";
                }

                $sql3 = substr($sql3,0,-1);

                //Debug::sql($sql3);
                $db->sql_query($sql3);
            }
        }

        $register = self::selectIdfromDigest([$digests]);

        return $register;
    }

    public static function selectIdfromDigest($param)
    {
        Debug::parseDebug($param);

        $tab_digests = $param[0];
        $db = Sgbd::sql(DB_DEFAULT);
        $list_digest = implode("','", $tab_digests);

        $sql = "SELECT id, digest FROM mysql_digest WHERE `digest` IN ('{$list_digest}');";
        $res = $db->sql_query($sql);

        $data = [];
        while ($ob = $db->sql_fetch_object($res)) {
            $data[$ob->digest] = $ob->id;
        }

        return $data;
    }



    public static function insertDispatch($param)
    {
        
        
        
        $db = Sgbd::sql(DB_DEFAULT);


    }


    public static function getIdDatabase($param)
    {
        Debug::parseDebug($param);
        $id_mysql_server = $param[0];
        $database = $param[1];

        if (!empty(self::$database[$id_mysql_server][$database]))
        {
            return self::$database[$id_mysql_server][$database];
        }
        else
        {
            $db = Sgbd::sql(DB_DEFAULT);

            if (empty($database))
            {
                $database = '';
            }

            $sql = "SELECT id from mysql_database where id_mysql_server=$id_mysql_server and schema_name='".$database."';";
            $res = $db->sql_query($sql);

            while($ob = $db->sql_fetch_object($res))
            {
                self::$database[$id_mysql_server][$database] = $ob->id;
                //Debug::debug($ob->id, "id_mysql_database");
                return $ob->id;
            }

            $sql ="INSERT INTO mysql_database SET schema_name='$database', id_mysql_server=$id_mysql_server";
            $db->sql_query($sql);
            return false;
        }
    }

    /*  
        Get the first id_mysql_server who got MySQL 8.4 and available server
    */

    static public function getIdMySql84($param)
    {
        Debug::parseDebug($param);
        $res = Extraction2::display(array('version','mysql_server::mysql_available'));

        //Debug::debug($res);

        foreach($res as $id_mysql_server => $server)
        {
            $pos = strpos($server['version'], "8.4.");

            if ($pos !== false) {
                if ($server['mysql_available'] === "1" ) {

                    Debug::debug($id_mysql_server);
                    return $id_mysql_server;
                }
            }
        }
        return null;
    }


    static public function getDigest($param)
    {
        Debug::parseDebug($param);
        $query = $param[0];

        if (in_array($query, array("SHOW SLAVE STATUS")))
        {
            return NULL;
        }
        
        $id_mysql_server = self::getIdMySql84($param);

        if ($id_mysql_server === false) {
            return null;
        }

        $db = Mysql::getDbLink($id_mysql_server);

        // replace ? by any value, to make query valid
        $query = str_replace('LIMIT ?', 'LIMIT 1', $query);
        $query = str_replace('?', '"34"', $query);

        $sql = "SELECT STATEMENT_DIGEST('".$db->sql_real_escape_string($query)."');";
        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_NUM))
        {
            Debug::debug($arr);
            return $arr[0];
        }

        return null;
    }


    static public function getHash($param)
    {
        Debug::parseDebug($param);
        $query = $param[0];

        return md5($query);
    }




    static public function getIdCacheDispatch($query)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        if (count(self::$cache_dispatch) === 0)
        {
            
            $sql = "SELECT id, CONCAT(id_mysql_server,'-',id_mysql_database,'-', id_mysql_digest) as uuid 
            FROM mysql_database__mysql_digest";
            Debug::sql($sql);
            $res = $db->sql_query($sql);

            while($ob = $db->sql_fetch_object($res, MYSQLI_ASSOC)) {
                self::$cache_dispatch[$ob->uuid] = $ob->id;
            }

            Debug::debug(count(self::$cache_dispatch), "GET CACHE ENTRY");
        }

        $uuid = $query['id_mysql_server']."-".$query['id_mysql_database']."-".$query['id_mysql_digest'];

        
        if (empty(self::$cache_dispatch[$uuid]))
        {
            $insert = [
                'mysql_database__mysql_digest' => [
                    'id_mysql_digest'   => $query['id_mysql_digest'],
                    'id_mysql_server'   => $query['id_mysql_server'],
                    'id_mysql_database' => $query['id_mysql_database'],
                    'schema_name'       => $query['schema_name'],
                    'password'          => $query['first_seen'],
                ]
            ];

            Debug::debug($insert);

            $id_mysql_database__mysql_digest = $db->sql_save($insert);

            if (empty($id_mysql_database__mysql_digest))
            {
                throw new Exception("ERROR id_mysql_database__mysql_digest : $id_mysql_database__mysql_digest");
            }

            self::$cache_dispatch[$uuid] = $id_mysql_database__mysql_digest;
        }

        return self::$cache_dispatch[$uuid];
    }

    public static function insert(array $tables, int $batchSize = 1000) : array
    {
        $sql_list = [];

        foreach ($tables as $table_name => $rows) {

            if (empty($rows) || !is_array($rows)) {
                continue;
            }

            // 1. déterminer les colonnes valides selon la 1ère ligne
            $first_row = reset($rows);
            $columns = [];

            foreach ($first_row as $col => $val) {
                if ($val !== null && $val !== '') {
                    $columns[] = $col;
                }
            }

            $total = count($rows);
            $num_batches = ceil($total / $batchSize);

            // 2. traiter par batch
            for ($b = 0; $b < $num_batches; $b++) {

                $offset = $b * $batchSize;
                $batch_rows = array_slice($rows, $offset, $batchSize);

                $sql_values = [];

                foreach ($batch_rows as $row) {
                    $line = [];

                    foreach ($columns as $col) {

                        if (!isset($row[$col]) || $row[$col] === null || $row[$col] === '') {
                            $line[] = "DEFAULT";
                        } else {
                            $v = addslashes($row[$col]);
                            $line[] = "'$v'";
                        }
                    }

                    $sql_values[] = "(" . implode(", ", $line) . ")";
                }

                // 3. construire la requête SQL finale pour CE batch
                $sql = "INSERT IGNORE INTO `$table_name` (`" . implode("`,`", $columns) . "`) VALUES\n"
                    . implode(",\n", $sql_values) . ";";

                $sql_list[] = $sql;
            }
        }

        return $sql_list;
    }
}