<?php

namespace App\Controller;

use App\Controller\ProxySQL;
use App\Controller\MaxScale;
use App\Library\Available;
use \Glial\Synapse\Controller;
use Fuz\Component\SharedMemory\Storage\StorageFile;
use Fuz\Component\SharedMemory\SharedMemory;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;
use \App\Library\Ssh;
use App\Library\System;
use App\Library\Mysql;
use App\Library\Proxy;
use App\Library\EngineV4;
use \Glial\Sgbd\Sgbd;
use \App\Library\Extraction;
use \App\Library\Extraction2;
use \App\Library\Microsecond;
/*

https://www.phpclasses.org/package/12231-PHP-Display-bar-charts-in-CLI-console-from-datasets.html
=> add CLI graph to see current step*
https://github.com/nunomaduro/termwind



SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT COUNT ( * ) 
FROM `information_schema` . `events` WHERE DEFINER = ? AND ( `EVENT_SCHEMA` != ? OR ( `EVENT_SCHEMA` = ? AND `EVENT_NAME` NOT IN (...) ) )

*************************** 1. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT ? 
*************************** 2. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT COUNT ( * ) FROM `information_schema` . `PLUGINS` WHERE `PLUGIN_NAME` = ? 
*************************** 3. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT COUNT ( * ) FROM `information_schema` . `TABLES` WHERE `TABLE_SCHEMA` = ? AND TABLE_NAME = ? 
*************************** 4. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT VALUE FROM `mysql` . `rds_heartbeat2` 
*************************** 5. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT NOW ( ) - INTERVAL `variable_value` SQL_TSI_SECOND `MySQL_Started` FROM `information_schema` . `global_status` WHERE `variable_name` = ? 
*************************** 6. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT COUNT ( * ) FROM `mysql` . `rds_replication_status` WHERE ACTION LIKE ? 
*************************** 7. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT COUNT ( * ) FROM `mysql` . `rds_history` WHERE ACTION = ? ORDER BY `action_timestamp` LIMIT ? 
*************************** 8. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT COUNT ( * ) FROM `mysql` . `rds_replication_status` WHERE MASTER_HOST IS NOT NULL AND MASTER_PORT IS NOT NULL ORDER BY `action_timestamp` LIMIT ? 
*************************** 9. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR FLUSH LOGS 
*************************** 10. row ***************************
query_mariadb: SET MAX_STATEMENT_TIME = ? 
*************************** 11. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR INSERT INTO `mysql` . `rds_heartbeat2` ( ID , VALUE ) VALUES (...) ON DUPLICATE KEY UPDATE VALUE = ? 
*************************** 12. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR COMMIT 
*************************** 13. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR USE MYSQL 
*************************** 14. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT NAME , VALUE FROM `mysql` . `rds_configuration` 
*************************** 15. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR PURGE BINARY LOGS TO ? 
*************************** 16. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT `info` FROM `information_schema` . `processlist` WHERE SYSTEM_USER = ? AND HOST LIKE ? 
*************************** 17. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT COUNT ( * ) FROM `information_schema` . `routines` WHERE `ROUTINE_SCHEMA` != ? AND DEFINER = ? 
*************************** 18. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT COUNT ( * ) FROM `information_schema` . `global_variables` WHERE `variable_name` = ? AND `variable_value` != ? AND ( SELECT `variable_value` FROM `information_schema` . `global_variables` WHERE `variable_name` = ? ) NOT IN (...) 
*************************** 19. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT COUNT ( * ) FROM `information_schema` . `events` WHERE DEFINER = ? AND ( `EVENT_SCHEMA` != ? OR ( `EVENT_SCHEMA` = ? AND `EVENT_NAME` NOT IN (...) ) ) 
*************************** 20. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT `lower` ( `plugin_name` ) FROM `information_schema` . `plugins` 
*************************** 21. row ***************************
query_mariadb: SET STATEMENT MAX_STATEMENT_TIME = ? FOR SELECT `mysql` . `rds_version` ( )




 *  => systemctl is-active mysql
 *
 * MySQL [(none)]> select @@version_comment limit 1;
  +-------------------+
  | @@version_comment |
  +-------------------+
  | (ProxySQL)        |
  +-------------------+
  1 row in set (0.000 sec)
  SELECT /*!40001 SQL_NO_CACHE * / * FROM
 *
 * 
 * 
 * 
 SELECT
t.THREAD_ID AS id,
t.PROCESSLIST_USER AS user,
t.PROCESSLIST_HOST AS host,
CONVERT (
CAST( CONVERT ( uvt.VARIABLE_VALUE USING latin1 ) AS BINARY ) USING utf8
) AS replica_uuid
FROM
`performance_schema`.threads AS t JOIN
`performance_schema`.user_variables_by_thread AS uvt ON t.THREAD_ID = uvt.THREAD_ID
WHERE
t.PROCESSLIST_COMMAND LIKE 'Binlog Dump%'
AND uvt.VARIABLE_NAME = 'slave_uuid'

SELECT
            COUNT(*) AS count
        FROM
            performance_schema.file_instances
        WHERE
            file_name LIKE '%innodb_redo/%' AND
            file_name NOT LIKE '%_tmp'


SELECT
            NAME,
            COUNT
        FROM
            information_schema.INNODB_METRICS
        WHERE
            name IN ('adaptive_hash_searches', 'adaptive_hash_searches_btree', 'trx_rseg_history_len')

+------------------------------+-------+
| NAME                         | COUNT |
+------------------------------+-------+
| trx_rseg_history_len         |     0 |
| adaptive_hash_searches       |     0 |
| adaptive_hash_searches_btree |     0 |
+------------------------------+-------+
3 rows in set (0,001 sec)
    


        SELECT
            t.THREAD_ID AS id,
            t.PROCESSLIST_USER AS user,
            t.PROCESSLIST_HOST AS host,
            CONVERT (
                CAST( CONVERT ( uvt.VARIABLE_VALUE USING latin1 ) AS BINARY ) USING utf8
            ) AS replica_uuid
        FROM
            `performance_schema`.threads AS t JOIN
            `performance_schema`.user_variables_by_thread AS uvt ON t.THREAD_ID = uvt.THREAD_ID
        WHERE
            t.PROCESSLIST_COMMAND LIKE 'Binlog Dump%'
            AND uvt.VARIABLE_NAME = 'slave_uuid';



SELECT
CONVERT(SUM(SUM_NUMBER_OF_BYTES_READ), UNSIGNED) AS io_read,
CONVERT(SUM(SUM_NUMBER_OF_BYTES_WRITE), UNSIGNED) AS io_write
FROM
`performance_schema`.`file_summary_by_event_name`
WHERE
`performance_schema`.`file_summary_by_event_name`.`EVENT_NAME` LIKE 'wait/io/file/%' AND
`performance_schema`.`file_summary_by_event_name`.`COUNT_STAR` > 0

+------------+-------------+
| io_read    | io_write    |
+------------+-------------+
| 1146072309 | 22420098241 |
+------------+-------------+
1 row in set (0,002 sec)

 */

// https://github.com/php-amqplib/php-amqplib
//each minute ?
//require ROOT."/application/library/Filter.php";
//https://blog.programster.org/php-multithreading-pool-example

/*

DEBUG :

delte md5 before or not ?
pmacontrol Aspirateur tryMysqlConnection name server_6788e895e8bf2 11 1 --debug
pmacontrol Integrate IntegrateAll --debug
pmacontrol Listener checkAll --debug
*/


/*
UPDATE `ts_variable` AS tv
JOIN `ts_type_override` AS tovr
ON tv.`name` = tovr.`name` AND tv.`from` = tovr.`from`
SET 
    tv.`type` = tovr.`type`,
    tv.`is_derived` = tovr.`is_derived`,
    tv.`is_dynamic` = tovr.`is_dynamic`;



PHPMYADMIN :

SELECT `CHARACTER_SET_NAME` AS `Charset`, `DEFAULT_COLLATE_NAME` AS `Default collation`, `DESCRIPTION` AS `Description`, `MAXLEN` AS `Maxlen` FROM `information_schema`.`CHARACTER_SETS`;
+----------+---------------------+-----------------------------+--------+
| Charset  | Default collation   | Description                 | Maxlen |
+----------+---------------------+-----------------------------+--------+
| big5     | big5_chinese_ci     | Big5 Traditional Chinese    |      2 |
| dec8     | dec8_swedish_ci     | DEC West European           |      1 |
| cp850    | cp850_general_ci    | DOS West European           |      1 |
| hp8      | hp8_english_ci      | HP West European            |      1 |
| koi8r    | koi8r_general_ci    | KOI8-R Relcom Russian       |      1 |
| latin1   | latin1_swedish_ci   | cp1252 West European        |      1 |
| latin2   | latin2_general_ci   | ISO 8859-2 Central European |      1 |
| swe7     | swe7_swedish_ci     | 7bit Swedish                |      1 |
| ascii    | ascii_general_ci    | US ASCII                    |      1 |
| ujis     | ujis_japanese_ci    | EUC-JP Japanese             |      3 |
| sjis     | sjis_japanese_ci    | Shift-JIS Japanese          |      2 |
| hebrew   | hebrew_general_ci   | ISO 8859-8 Hebrew           |      1 |
| tis620   | tis620_thai_ci      | TIS620 Thai                 |      1 |
| euckr    | euckr_korean_ci     | EUC-KR Korean               |      2 |
| koi8u    | koi8u_general_ci    | KOI8-U Ukrainian            |      1 |
| gb2312   | gb2312_chinese_ci   | GB2312 Simplified Chinese   |      2 |
| greek    | greek_general_ci    | ISO 8859-7 Greek            |      1 |
| cp1250   | cp1250_general_ci   | Windows Central European    |      1 |
| gbk      | gbk_chinese_ci      | GBK Simplified Chinese      |      2 |
| latin5   | latin5_turkish_ci   | ISO 8859-9 Turkish          |      1 |
| armscii8 | armscii8_general_ci | ARMSCII-8 Armenian          |      1 |
| utf8mb3  | utf8mb3_general_ci  | UTF-8 Unicode               |      3 |
| ucs2     | ucs2_general_ci     | UCS-2 Unicode               |      2 |
| cp866    | cp866_general_ci    | DOS Russian                 |      1 |
| keybcs2  | keybcs2_general_ci  | DOS Kamenicky Czech-Slovak  |      1 |
| macce    | macce_general_ci    | Mac Central European        |      1 |
| macroman | macroman_general_ci | Mac West European           |      1 |
| cp852    | cp852_general_ci    | DOS Central European        |      1 |
| latin7   | latin7_general_ci   | ISO 8859-13 Baltic          |      1 |
| utf8mb4  | utf8mb4_general_ci  | UTF-8 Unicode               |      4 |
| cp1251   | cp1251_general_ci   | Windows Cyrillic            |      1 |
| utf16    | utf16_general_ci    | UTF-16 Unicode              |      4 |
| utf16le  | utf16le_general_ci  | UTF-16LE Unicode            |      4 |
| cp1256   | cp1256_general_ci   | Windows Arabic              |      1 |
| cp1257   | cp1257_general_ci   | Windows Baltic              |      1 |
| utf32    | utf32_general_ci    | UTF-32 Unicode              |      4 |
| binary   | binary              | Binary pseudo charset       |      1 |
| geostd8  | geostd8_general_ci  | GEOSTD8 Georgian            |      1 |
| cp932    | cp932_japanese_ci   | SJIS for Windows Japanese   |      2 |
| eucjpms  | eucjpms_japanese_ci | UJIS for Windows Japanese   |      3 |
+----------+---------------------+-----------------------------+--------+
40 rows in set (0,000 sec)

SELECT `COLLATION_NAME` AS `Collation`, `CHARACTER_SET_NAME` AS `Charset`, `ID` AS `Id`, `IS_DEFAULT` AS `Default`, `IS_COMPILED` AS `Compiled`, `SORTLEN` AS `Sortlen` FROM `information_schema`.`COLLATIONS`;


MariaDB [performance_schema]> SHOW SESSION VARIABLES LIKE 'character_set_server';
+----------------------+---------+
| Variable_name        | Value   |
+----------------------+---------+
| character_set_server | utf8mb4 |
+----------------------+---------+
1 row in set (0,001 sec)

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'
SET lc_messages = 'fr_FR'

(SELECT DISTINCT `User`, `Host` FROM `mysql`.`user` ) UNION (SELECT DISTINCT `User`, `Host` FROM `mysql`.`db` ) UNION (SELECT DISTINCT `User`, `Host` FROM `mysql`.`tables_priv` ) UNION (SELECT DISTINCT `User`, `Host` FROM `mysql`.`columns_priv` ) UNION (SELECT DISTINCT `User`, `Host` 
FROM `mysql`.`procs_priv` ) ORDER BY `User` ASC, `Host` ASC


SELECT * FROM `mysql`.`user` WHERE `User` = ? AND `Host` = ?
SELECT * FROM `mysql`.`global_priv` WHERE `User` = ? AND `Host` = ?

ALTER USER 'mariadb.sys'@'localhost' ACCOUNT LOCK

SHOW BINLOG EVENTS LIMIT 0, 25

SELECT * FROM information_schema.PLUGINS ORDER BY PLUGIN_TYPE, PLUGIN_NAME

SELECT *, `TABLE_SCHEMA`       AS `Db`, `TABLE_NAME`         AS `Name`, `TABLE_TYPE`         AS `TABLE_TYPE`, `ENGINE`             AS `Engine`, `ENGINE`             AS `Type`, `VERSION`            AS `Version`, `ROW_FORMAT`         AS `Row_format`, `TABLE_ROWS`         AS `Rows`, `AVG_ROW_LENGTH`     AS `Avg_row_length`, `DATA_LENGTH`        AS `Data_length`, `MAX_DATA_LENGTH`    AS `Max_data_length`, `INDEX_LENGTH`       AS `Index_length`, `DATA_FREE`          AS `Data_free`, `AUTO_INCREMENT`     AS `Auto_increment`, `CREATE_TIME`        AS `Create_time`, `UPDATE_TIME`        AS `Update_time`, `CHECK_TIME`         AS `Check_time`, `TABLE_COLLATION`    AS `Collation`, `CHECKSUM`           AS `Checksum`, `CREATE_OPTIONS`     AS `Create_options`, `TABLE_COMMENT`      AS `Comment` FROM `information_schema`.`TABLES` t WHERE `TABLE_SCHEMA`  IN ('sakila2')  ORDER BY Name ASC;


*/

class Aspirateur extends Controller
{
    //use \App\Library\Filter;

    static $timestamp_config_file = "";

    //log with monolog
    var $logger;
    //store if table exist or not to prevent ask each time
    static $cache = array();


    static $primary_key = array();


    static $time_ns = 0;

    /*
     * (PmaControl 0.8)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description log error & start / stop daemon
     * @access public
     */

    public function before($param)
    {
        $monolog       = new Logger("Aspirateur");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;


        self::$primary_key['main']['global_variables']['pk'] = "variable_name";
        self::$primary_key['main']['global_variables']['val'] = "variable_value";
        self::$primary_key['main']['runtime_global_variables']['pk'] = "variable_name";
        self::$primary_key['main']['runtime_global_variables']['val'] = "variable_value";
    }

    /**
     * (PmaControl 0.8)<br/>
     * @example ./glial aspirateur tryMysqlConnection name id_mysql_server
     * @author Aurélien LEQUOY, <aurelien@esysteme.com>
     * @return boolean Success
     * @package Controller
     * @since 0.8 First time this was introduced.
     * @description try to connect successfully on MySQL, if any one error in process even in PHP it throw a new Exception.
     * @access public
     */
    public function tryMysqlConnection($param)
    {   
        Debug::parseDebug($param);
        $this->view = false;

        $name_server = $param[0] ?? null;
        $id_mysql_server   = $param[1] ?? null;
        $refresh = $param[2] ?? null;


        if (!$name_server || !$id_mysql_server) {
            throw new \Exception(
                "Paramètre manquant : name_server et id_mysql_server sont obligatoires",
                1001
            );
        }

        if (!is_int((int)$refresh)) {
            throw new \Exception(
                "Paramètre refresh doit être un entier",
                1002
            );
        }

        // Vérifier que le serveur existe dans le fichier de config
        $configServers = parse_ini_file(CONFIG.'db.config.ini.php', true);
        $serverFound = false;
        foreach ($configServers as $section => $values) {
            if ($section === $name_server) {
                $serverFound = true;
                break;
            }
        }


        Debug::checkPoint('Init');


        // to make it in cache one time for all
        // To know if we use a proxy like PROXYSQL / MAXSCALE
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT is_proxy FROM mysql_server WHERE id=".$id_mysql_server;
        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res)) {
            $IS_PROXY = $ob->is_proxy;
        }
        $db->sql_close();
        //end of case of HA proxy & Maxscale

        Debug::checkPoint('After getting proxy');
        $pid = getmypid();

        $time_start   = microtime(true);

        try{
            $error_msg='';
            $mysql_tested = Sgbd::sql($name_server);
        }
        catch(\Exception $e){
            $error_msg = $e->getMessage();
            Debug::debug($error_msg, "Error_MSG");
            $this->logger->emergency($error_msg." id_mysql_server:$id_mysql_server");
        }
        finally{
            $ping = microtime(true) - $time_start;
            $available = empty($error_msg) ? 1 : 0;

            Debug::debug([$id_mysql_server, $ping, $error_msg, $available], "REPONSE MYSQL");
            $this->setService($id_mysql_server, $ping, $error_msg, $available, 'mysql');
            if ($available === 0) {
                //$mysql_tested->sql_close();
                return false;
            }

            // only if REAL server => should make test if Galera if select 1 => not ready to use too
            if (empty($IS_PROXY)) {


            }
            else{
                // need try one case if hostgroup 2 ok but hostgroup 1 ko
                try{
                    // hack to force read to switch back online after shunned in case of no query on proxy (reader)
                    $mysql_tested->sql_query("SELECT 1;");

                    $error_filter='';
                    $sql ="BEGIN;";
                    $mysql_tested->sql_query($sql);
                    $sql ="COMMIT;";
                    $mysql_tested->sql_query($sql);
                }
                catch(\Exception $e){
                    $error_ori = $e->getMessage();
                    preg_match('/ERROR:(.*)}/', $error_ori, $output_array);
                    if (!empty($output_array[1])) {
                        $error_filter = $output_array[1];
                    }
                    else {
                        $error_filter =$error_ori;
                    }
                    $this->logger->emergency("[ERROR][WORKER:".$pid."] id_mysql_server:$id_mysql_server ==> $error_filter");
                }
                finally
                {
                    $available = empty($error_ori) ? 1 : 0;
                    $this->setService($id_mysql_server, $ping, $error_filter, $available, 'mysql');

                    if ($available === 0) {
                        $mysql_tested->sql_close();
                        return false;
                    }
                }
            }

            $this->logger->info("[WORKER:".$pid."] id_mysql_server:".$id_mysql_server." - is_available : ".$available." - ping : ".round($ping,6));

            // VERY important else we got error and we kill the worker and have to restart with a new one
            if ($available === 0) {
                return false;
            }
        }

        // traitement SHOW GLOBAL VARIABLES
        $var['variables'] = $mysql_tested->getVariables();
        Debug::checkPoint('After global variables');


        // CAS PROXYSQL detected new
        if (!empty($var['variables']['is_proxysql']) && $var['variables']['is_proxysql'] == "1") {

            $data = array();
            $db  = Sgbd::sql(DB_DEFAULT);
            $sql ="SELECT id FROM mysql_server WHERE id=".$id_mysql_server." AND `is_proxy`!=1";
            $res = $db->sql_query($sql);
            while ($ob = $db->sql_fetch_object($res)){
                $sql = "UPDATE `mysql_server` SET `is_proxy`=1 WHERE `id`=".$id_mysql_server." AND `is_proxy`!=1;";
                Debug::sql($sql);
                $db->sql_query($sql);
                $this->logger->notice("We discover a new ProxySQL : id_mysql_server:".$ob->id_mysql_server);
            }

            // TO DO => fix with new name of array
            $version = Extraction2::display(array("proxysql_runtime::global_variables"), array($id_mysql_server));
            
            $var_temp = array();
            $var_temp['variables']['is_proxy']     = "1";
            $var_temp['variables']['is_proxysql']     =  $var['variables']['is_proxysql'];

            if (! empty($version[$id_mysql_server]['global_variables']['admin-version']))
            {
                $version_proxysql = $version[$id_mysql_server]['global_variables']['admin-version'];
            }
            else{
                $version_proxysql = "N/A";
            }

            $var_temp['variables']['version']         = $version_proxysql;
            $var_temp['variables']['version_comment'] = "ProxySQL";


            Debug::debug($var_temp,"VERSION_PROXYSQL");
            
            $this->exportData($id_mysql_server,"mysql_global_variable", $var_temp);
            return true;
        } 

        // cas maxscale
        if (empty($var['variables']['is_proxysql']) && $IS_PROXY == "1")
        {

            $var_temp = array();
            $var_temp['variables']['is_proxy']     = "1";
            $var_temp['variables']['is_maxscale']     = "1";

            $var_temp['variables']['version']         = MaxScale::getVersion(array($id_mysql_server));
            $var_temp['variables']['version_comment'] = "MaxScale";

            $this->exportData($id_mysql_server,"mysql_global_variable", $var_temp);
            return true;
        }

        //we delete variable who change each time and put in on status
        $remove_var = array('gtid_binlog_pos', 'gtid_binlog_state', 'gtid_current_pos','gtid_slave_pos', 'timestamp', 'gtid_executed');
        
        
        $data = array();
        foreach($remove_var as $var_to_remove){
            if (!empty($var['variables'][$var_to_remove])) {
                
                $data['gtid'][$var_to_remove] = $var['variables'][$var_to_remove];
                
                unset($var['variables'][$var_to_remove]);
            }
        }

        //if ((time()+$id_mysql_server)%3 === 0)
        //{
            $this->exportData($id_mysql_server,"mysql_global_variable", $var);
        //}

        //if ((time()+$id_mysql_server)%3 === 0)
        //{
            $this->exportData($id_mysql_server,"mysql_variable_gtid", $data, false );
        //}

        //get SHOW GLOBAL STATUS
        Debug::debug("apres Variables");
        
        $data = array();
        $data['status'] = $mysql_tested->getStatus();
        

        $slave  = $mysql_tested->isSlave();
        if (($slave) != 0) {
            $data['slave'] = $slave;
        }
        Debug::debug($data['slave'], "SLAVE");


        $this->exportData($id_mysql_server, "mysql_global", $data, false);
        

        /*
        if ((time()+$id_mysql_server)%(10*$refresh) < $refresh)
        {
            $data = array();
            $data['mysql_meta_data_lock']['meta_data_lock'] = $this->getLockingQueries(array($id_mysql_server));
            $this->exportData($id_mysql_server, "mysql_meta_data_lock", $data);
        }*/

        //SHOW SLAVE HOSTS; => add in glial
        $data = array();
        $data['mysql_processlist']['processlist'] = json_encode($this->getProcesslist($mysql_tested));
        $this->exportData($id_mysql_server, "mysql_processlist", $data);


        // toutes les 10 secs si refresh =1 (toutes les 10* $refresh)
        if ((time()+$id_mysql_server)%(10*$refresh) < $refresh)
        {
            if ($var['variables']['log_bin'] === "ON") {
                $data = array();
                $data['mysql_binlog'] = $this->binaryLog(array($id_mysql_server));
                $data['master_status'] = $mysql_tested->isMaster();
                //Debug::debug($data);
                $this->exportData($id_mysql_server, "mysql_binlog", $data);
            }
        }

        Debug::debug("apres la récupération de la liste des binlogs");
        Debug::checkPoint('apres query');
    
        if ((time()+$id_mysql_server)%(10*$refresh) < $refresh)
        {
            $data = array();
            $data['innodb_metrics'] = $this->getInnodbMetrics($name_server);
            $this->exportData($id_mysql_server, "mysql_innodb_metrics", $data, false);
        }


        // if performance_schema == ON
        if ($var['variables']['performance_schema'] == "ON") {

            /*
            if ((time()+$id_mysql_server)%(20*$refresh) < $refresh)
            {
                $data = array();
                $data['mysql_latency'] = $this->getMysqlLatencyByQuery($name_server);

                Debug::debug($data);
                $this->exportData($id_mysql_server, "mysql_statistics", $data);
            }*/

                /*
            $data = array();
            $data['performance_schema']['memory_summary_global_by_event_name'] = json_encode($this->getPsMemory($name_server));
            Debug::debug($data);
            $this->exportData($id_mysql_server, "ps_memory_summary_global_by_event_name", $data, true);
            */

            $this->runEachMinuteAtBalancedSecond($id_mysql_server, 60,'digest', function($id) {

                // Ici, on utilise $mysql_tested EXISTANT => aucune reconnection MySQL
                $data = [];
                $data = $this->getDigest([$id]);
                
                // FROM::Metric
                $performance_schema['performance_schema']['events_statements_summary_by_digest'] = json_encode($data);
                $this->exportData($id, "performance_schema", $performance_schema, false);

                $this->logger->emergency("[DIGEST][$id] executed at second (crc32 offset)");
            });

            
            $this->runEachMinuteAtBalancedSecond($id_mysql_server,10, 'avg_latency', function($id) {

                $data = array();
                $start = hrtime(as_number: true);
                $data['summary_by_digest'] = Digest::getSum([$id]);
                $duration_us = round((hrtime(true) - $start) / 1000);
                $data['summary_by_digest']['sum_duration'] = $duration_us;

                $this->exportData($id, "ps_sum_summary_by_digest", $data);

            });

            
            //duplicate 2025-11-08 15:41:57-2397-96-2181 PK ts_value_digest_int
            

            /*
            if ((time()+$id_mysql_server)%(30*$refresh) < $refresh)
            {
               $data = array();
               $data['digest'] = $this->getDigest([$id_mysql_server]);
               $this->exportData($id_mysql_server, "performance_schema__digest", $data, false);
            }*/
/****/
            /*
            $data = array();
            $data['velocity'] = $this->getVelocity($name_server);
            $this->exportData($id_mysql_server, "mysql_velocity", $data, false);
            */



          
        }

        /*************************************** list Database  */

        if ((time()+$id_mysql_server)%(10*$refresh) < $refresh)
        {
            $data = array();
            $data['mysql_database']['database'] = json_encode($this->getSchema($id_mysql_server));
            $this->exportData($id_mysql_server, "mysql_schemata", $data);
        }
        /*************************************** List table by DB  */

        //to know if we grab statistics on databases & tables
        if ((time()+$id_mysql_server)%(3600*$refresh) < $refresh) {
            $data = array();
            $data['mysql_table']['mysql_table'] = json_encode($this->getDatabase($mysql_tested));
            $this->exportData($id_mysql_server, "mysql_table", $data);
        }

        /******************************************** plugin */

        if ((time()+$id_mysql_server)%(3600*$refresh) < $refresh) {

            $data = array();
            $elems = $this->getElemFromTable(array($id_mysql_server, "information_schema", "plugins"));
            if ($elems != false )
            {
                $data['information_schema']['plugins'] = json_encode($elems);
                $this->exportData($id_mysql_server, "information_schema__plugins", $data);
            }
        }

        /****************************************************************** */

        $data = array();
        $elems = $this->getElemFromTable(array($id_mysql_server, "information_schema", "metadata_lock_info"));
        if ($elems != false )
        {
            $data['information_schema']['metadata_lock_info'] = json_encode($elems);
            $this->exportData($id_mysql_server, "information_schema__metadata_lock_info", $data);
        }

        /****************************************************************** */

        /*
        $data = array();
        $elems = $this->getElemFromTable(array($id_mysql_server, "sys", "innodb_lock_waits"));
        if ($elems != false &&  !in_array($id_mysql_server, array(72,73,74,75)))
        {
            $data['sys']['innodb_lock_waits'] = json_encode($elems);
            $this->exportData($id_mysql_server, "sys__innodb_lock_waits", $data);
        }*/

        /****************************************************************** */




        $mysql_tested->sql_close();

        Debug::debugShowTime();

        return true;
    }

    public function allocate_shared_storage($name, $separator = EngineV4::SEPERATOR)
    {
        //storage shared
        Debug::debug($name, 'create file');

        $shared_file   = EngineV4::PATH_PIVOT_FILE.time().$separator.$name;
        $storage       = new StorageFile($shared_file); // to export in config ?
        $SHARED_MEMORY = new SharedMemory($storage);
        return $SHARED_MEMORY;
    }

    public function trySshConnection($param)
    {
        $this->view      = false;
        
        $name_server = $param[0];
        $id_mysql_server   = $param[1];
        $refresh = $param[2];

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id, a.ip,c.user,c.private_key FROM `mysql_server` a
        INNER JOIN `link__mysql_server__ssh_key` b ON a.id = b.id_mysql_server
        INNER JOIN `ssh_key` c on c.id = b.id_ssh_key
        where a.id=".$id_mysql_server." AND b.`active` = 1 LIMIT 1;";

        Debug::sql($sql);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $id_mysql_server = $ob->id;

            $ssh = false;
            try{
                $error_msg='';
                $time_start = microtime(true);
                $ssh        = Ssh::ssh($id_mysql_server);
            }
            catch(\Exception $e){
                $error_msg = $e->getMessage();
                $this->logger->warning($error_msg." id_ssh_server:$id_mysql_server");
            }
            finally{
                $ping = microtime(true) - $time_start;
                $available = empty($error_msg) ? 1 : 0;
                
                $this->setService($id_mysql_server, $ping, $error_msg, $available, "ssh");
                $this->logger->info("id_ssh_server:".$id_mysql_server." - is_available : ".$available." - ping : ".round($ping,6));
    
                // VERY important else we got error and we kill the worker and have to restart with a new one
                if ($available === 0) {
                    //return false;
                }
            }

            $ssh_available = 0;

            if (!empty($ssh) && $ssh !== false ) {
                $ssh_available = 1;
   
                $stats['ssh_stats']    = $this->getStats($ssh);
                $hardware['ssh_hardware'] = $this->getHardware($ssh);

                //liberation de la connexion ssh https://github.com/phpseclib/phpseclib/issues/1194
                $ssh->disconnect();
                unset($ssh);

                Debug::debug($stats);
                Debug::debug($hardware);

                $this->exportData($id_mysql_server, "ssh_hardware", $hardware);
                $this->exportData($id_mysql_server, "ssh_stats", $stats, false);

            } else {
                Debug::debug("Can't connect to ssh");
                //error connection ssh
            }
            
            $ret['ssh_server']['ssh_available'] = $ssh_available;
            $ret['ssh_server']['ping'] = $ping;
            $this->exportData($id_mysql_server, "ssh_server", $ret, false);
        }


        $db->sql_close();
    }

    private function getHardware($ssh)
    {

        //$hardware['memory']           = $ssh->exec("grep MemTotal /proc/meminfo | awk '{print $2}'") or die("error");
        //$hardware['cpu_thread_count'] = trim($ssh->exec("cat /proc/cpuinfo | grep processor | wc -l"));
        $hardware['cpu_thread_count'] = trim($ssh->exec("nproc"));
        

        $brut_memory = $ssh->exec("cat /proc/meminfo | grep MemTotal");
        preg_match("/[0-9]+/", $brut_memory, $memory);

        $mem    = $memory[0];
        $memory = sprintf('%.2f', $memory[0] / 1024 / 1024)." Go";

        $hardware['memory'] = $memory;

        $freq_brut = $ssh->exec("cat /proc/cpuinfo | grep 'cpu MHz'");
        preg_match("/[0-9]+\.[0-9]+/", $freq_brut, $freq);

// $freq[0] can be empty !!!

        if (empty($freq[0])) {

//case of raspberry pi
            $freq_2 = 0;
            $freq_2 = trim($ssh->exec("cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_max_freq"));

            if (!empty($freq_2)) {
                $hardware['cpu_frequency'] = round($freq_2 / 1000000, 2)." GHz";
            } else {
                $hardware['cpu_frequency'] = $freq_brut;
            }
        } else {
            $hardware['cpu_frequency'] = sprintf('%.2f', ($freq[0] / 1000))." GHz";
        }
        $os          = trim($ssh->exec("lsb_release -ds 2> /dev/null"));
        $distributor = trim($ssh->exec("lsb_release -si 2> /dev/null"));
        $codename    = trim($ssh->exec("lsb_release -cs 2> /dev/null"));

        if ($distributor === "RedHatEnterpriseServer") {
            $distributor = "RedHat";
        }

        if (empty($os)) {
            $os          = trim($ssh->exec("cat /etc/centos-release 2> /dev/null"));
            $distributor = trim("Centos");
        }

        if (empty($os)) {
            $version = trim($ssh->exec("cat /etc/debian_version 2> /dev/null"));
            if (!empty($version)) {
                $distributor = trim("Debian");

                $ver = explode(".", $version)[0];

                switch ($ver) {
                    case "4": $codename = "Etch";
                        break;
                    case "5": $codename = "Lenny";
                        break;
                    case "6": $codename = "Squeeze";
                        break;
                    case "7": $codename = "Wheezy";
                        break;
                    case "8": $codename = "Jessie";
                        break;
                    case "9": $codename = "Stretch";
                        break;
                    case "10": $codename = "Buster";
                        break;
                    case "11": $codename = "Bookworm";
                        break;
                    case "12": $codename = "Bullseye";
                        break;
                }

                $os = trim("Debian GNU/Linux ".$version." (".$codename.")");
            }
        }

        $hardware['distributor']  = trim($distributor);
        $hardware['os']           = trim($os);
        $hardware['codename']     = trim($codename);
        $hardware['product_name'] = trim($ssh->exec("sudo dmidecode -s system-product-name 2> /dev/null"));
        if (empty($hardware['product_name']))
        {
            $hardware['product_name'] = trim($ssh->exec("dmidecode -s system-product-name 2> /dev/null"));
        }

        $hardware['arch']         = trim($ssh->exec("uname -m"));
        $hardware['kernel']       = trim($ssh->exec("uname -r"));
        $hardware['hostname']     = trim($ssh->exec("hostname"));
        $hardware['swapiness']    = trim($ssh->exec("cat /proc/sys/vm/swappiness"));

        return $hardware;
    }

    public function getStats($ssh)
    {
        $stats = array();

        
// récupération de l'uptime et du load average
/*
        $uptime = $ssh->exec("uptime");

        $output_array = array();
        preg_match("/averages?:\s*([0-9]+[\.|\,][0-9]+)[\s|\.\,]\s+([0-9]+[\.|\,][0-9]+)[\s|\.\,]\s+([0-9]+[\.|\,][0-9]+)/", $uptime, $output_array);

        if (!empty($output_array[1])) {
            $stats['load_average_5_sec']  = str_replace(',', '.', $output_array[1]);
            $stats['load_average_5_min']  = str_replace(',', '.', $output_array[2]);
            $stats['load_average_15_min'] = str_replace(',', '.', $output_array[3]);
        }
        preg_match("/([0-9]+)\s+user/", $uptime, $output_array);
        if (!empty($output_array[1])) {
            $stats['user_connected'] = $output_array[1];
        }

        preg_match("/up\s+([0-9]+\s[a-z]+),/", $uptime, $output_array);
        if (!empty($output_array[1])) {
            $stats['uptime'] = $output_array[1];
        }    
        
        /**************** */
        //uptime v2

        $load_average = explode(" ",trim($ssh->exec("cat /proc/loadavg")));

        $stats['load_average_1_min']  = $load_average[0];
        $stats['load_average_5_min']  = $load_average[1];
        $stats['load_average_15_min'] = $load_average[2];
        
        $membrut = trim($ssh->exec("free -b"));
        $stats   = $this->getSwap($membrut);

//on exclu les montage nfs
        $dd = trim($ssh->exec("df -l"));

        $lines = explode("\n", $dd);
        $items = array('Filesystem', 'Size', 'Used', 'Avail', 'Use%', 'Mounted on');
        unset($lines[0]);

        $tmp = array();
        foreach ($lines as $line) {

            $elems          = preg_split('/\s+/', $line);
            $tmp[$elems[5]] = $elems;
        }

        $stats['disks'] = json_encode($tmp);

        $ips = trim($ssh->exec("ip addr | grep 'state UP' -A2 | awk '{print $2}' | cut -f1 -d'/' | grep -Eo '([0-9]*\.){3}[0-9]*'"));

        $stats['ips'] = json_encode(explode("\n", $ips));



        // top -bn1 | grep "Cpu(s)"
        $cpus         = trim($ssh->exec("grep 'cpu' /proc/stat"));
        //$cpus = trim(shell_exec("grep 'cpu' /proc/stat"));

        $cpu_lines = explode("\n", $cpus);

        $i = 0;
        foreach ($cpu_lines as $line) {

            $elems = preg_split('/\s+/', $line);

            //debug($elems);
            //system + user + idle
            if ($i === 0) {
                $stats['cpu_usage'] = (($elems[1] + $elems[3]) * 100) / ($elems[1] + $elems[3] + $elems[4]);
            } else {
                $cpu[$elems[0]] = ($elems[1] + $elems[3]) * 100 / ($elems[1] + $elems[3] + $elems[4]);
            }
            $i++;
        }
        $stats['cpu_detail'] = json_encode($cpu);



        // memory mysql or mariadb
        
        $result = $ssh->exec("ps -eo comm,rss --no-headers");

        if (!empty($result)) {
            $result = trim($result);
        }

        //Debug::debug($result, "RESULT");

        $memories = explode("\n", $result);

        $totalMemory = 0;
        foreach ($memories as $line) {
            list($cmd, $rss) = preg_split('/\s+/', trim($line), 2);
            $rss = (int) $rss;
        
            if (empty($rss)){
                continue;
            }
        
            if (!isset($processes[$cmd])) {
                $processes[$cmd] = 0;
            }
        
            $processes[$cmd] += $rss;
            $totalMemory += $rss;
        }

        arsort($processes);

        $stats['memory_mysqld'] = 0;
        if (! empty($processes["mariadbd"])) {
            $stats['memory_mysqld'] = $processes["mariadbd"];
        }else if (! empty($processes["mysqld"])) {
            $stats['memory_mysqld'] = $processes["mysqld"];
        }

        $stats['memory_detail_kb'] = json_encode($processes);


        /* io wait */

        /*
          $cpu_user = trim($ssh->exec("iostat -c | tail -2 | head -n 1"));
          $cpu_user = trim(shell_exec("iostat -c | tail -2 | head -n 1"));

          $titles = array('cpu_user%', 'cpu_nice%', 'cpu_system%', 'cpu_iowait%', 'cpu_steal%', 'cpu_idle%');

          $elems = preg_split('/\s+/', $cpu_user);
          $i     = 0;
          foreach ($titles as $title) {

          $stats[$title] = $elems[$i];
          $i++;
          }
        */

        /*
          $tmp_mem = trim($ssh->exec("ps aux | grep 'mysqld ' | grep -v grep | awk '{print $5,$6}'"));

          $mem = explode("\n", $tmp_mem);
          $mysql = explode(' ', end($mem));

          $stats['mysqld_mem_physical'] = $mysql[1];
          $stats['mysqld_mem_virtual'] = $mysql[0];
         */

        $cmd_ipv4 = "ip -4 addr show | grep -oP '(?<=inet\s)\d+(\.\d+){3}'";
        //sans docker
        $cmd_ipv4 = "ip -4 addr show | awk '/inet / {print $2}' | cut -d/ -f1 | grep -vE '^(127\.0\.0\.1|172\.17\.)'";


        $cmd_ipv6 = "ip -6 addr show | grep -oP '(?<=inet6\s)[a-fA-F0-9:]+'";


//ifconfig


        return $stats;
    }


    public function binaryLog($param)
    {
        Debug::parseDebug($param);
        $id_mysql_server = $param[0];

        $mysql_tested = Mysql::getDbLink($id_mysql_server);

        if ($mysql_tested->testAccess()) {

            // If BACKUP STAGE BLOCK_COMMIT detectednot lunch 
            // TO DO => case of mariadb backup need to stop that process

            $sql = "SHOW BINARY LOGS;";
            $res = $mysql_tested->sql_query($sql);
            if ($res) {

                if ($mysql_tested->sql_num_rows($res) > 0) {

                    $files = array();
                    $sizes = array();

                    while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_NUM)) {

                        $files[] = $arr[0];
                        $sizes[] = $arr[1];
                    }

                    $data['binlog_file_first'] = $files[0];
                    $data['binlog_file_last']  = end($files);
                    $data['binlog_files']      = json_encode($files);
                    $data['binlog_sizes']      = json_encode($sizes);
                    $data['binlog_total_size'] = array_sum($sizes);
                    $data['binlog_nb_files']   = count($files);

                    Debug::debug($data);
                    return $data;
                }
            }
        }
        return false;
    }

    public function getArbitrator()
    {
// cat error.log | grep -oE 'tcp://[0-9]+.[0-9]+.[0-9]+.[0-9]+:4567' | sort -d | uniq -c | grep -v '0.0.0.0'
// et retirer les IP presente dans la table alias et la table mysql_server
    }



    public function getDatabase($mysql_tested)
    {
//$grants = $this->getGrants();

        $sql2 = 'select * from information_schema.SCHEMATA;';
        $res2 = $mysql_tested->sql_query($sql2);

        $db_number = $mysql_tested->sql_num_rows($res2);

        if ($db_number > 100) {
            Debug::sql("Too much DBs");
            return false;
        }

        // TEMPORARY => from MariaDB 10.3
        if ($mysql_tested->checkVersion(array("MariaDB", "10.3"), array("Percona", "5.7"), array("MySQL", "5.7")))
        {
            $sql = "select TABLE_CATALOG , TABLE_SCHEMA , TABLE_NAME, TABLE_TYPE , ENGINE,  ROW_FORMAT,
         TABLE_COLLATION, CREATE_OPTIONS, TABLE_COMMENT, TEMPORARY
         from information_schema.TABLES";
        }
        else
        {
            $sql = "select TABLE_CATALOG , TABLE_SCHEMA , TABLE_NAME, TABLE_TYPE , ENGINE,  ROW_FORMAT,
         TABLE_COLLATION, CREATE_OPTIONS, TABLE_COMMENT
         from information_schema.TABLES";
        }
        
        Debug::sql($sql);

        $res = $mysql_tested->sql_query($sql);
        if ($res) {
            if ($mysql_tested->sql_num_rows($res) > 0) {
                $dbs = array();

                while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    $dbs[] = array_change_key_case($arr);
                }

                return $dbs;
            }
        }
        return false;
    }

    public function getSwap($membrut)
    {
        Debug::debug($membrut);

        $lines  = explode("\n", $membrut);
        unset($lines[0]);
        $titles = array('memory', 'swap');
        $items  = array('total', 'used', 'free', 'shared', 'buff/cache', 'available');

        $i = 1;
        foreach ($titles as $title) {
// to remove Mem: and Swap:  (or other text in other language)
            $value = trim(explode(':', $lines[$i])[1]);
            $elems = preg_split('/\s+/', $value);

            $j = 0;
            foreach ($elems as $elem) {
                $stats[$title.'_'.$items[$j]] = $elem;
                $j++;
            }
            $i++;
        }

        Debug::debug($stats);

        return $stats;
    }
    /*
     * Récuperation des tables : (dans le cadre d'un serveur Galera 4
     * - wsrep_cluster
     * - wsrep_cluster_members
     * - wsrep_streaming_log
     *
     */

    public function getWsrep($param = array())
    {
        Debug::parseDebug($param);

        if (empty($param[0])) {
            echo "INVALID";
        } else {
            $name_server = $param[0];
        }

        $mysql_tested = Sgbd::sql($name_server);
        $sql1         = "SELECT * FROM `mysql`.`wsrep_cluster`;";
        $res1         = $mysql_tested->sql_query($sql1);
        if ($res1) {

            $data['wsrep_cluster'] = array();
            if ($mysql_tested->sql_num_rows($res1) > 0) {

//should be only one line
                while ($arr1 = $mysql_tested->sql_fetch_array($res1, MYSQLI_ASSOC)) {
                    $data['wsrep_cluster'][] = $arr1;
                }
            }
        }
        $data['wsrep_cluster'] = json_encode($data['wsrep_cluster']);

        $sql2 = "SELECT * FROM `mysql`.`wsrep_cluster_members`;";
        $res2 = $mysql_tested->sql_query($sql2);
        if ($res2) {

            $data['wsrep_cluster_members'] = array();
            if ($mysql_tested->sql_num_rows($res2) > 0) {

                while ($arr2 = $mysql_tested->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                    $data['wsrep_cluster_members'][] = $arr2;
                }
            }
        }
        $data['wsrep_cluster_members'] = json_encode($data['wsrep_cluster_members']);

        $sql3 = "SELECT * FROM `mysql`.`wsrep_streaming_log`;";
        $res3 = $mysql_tested->sql_query($sql3);
        if ($res3) {

            $data['wsrep_streaming_log'] = array();
            if ($mysql_tested->sql_num_rows($res3) > 0) {

                while ($arr3 = $mysql_tested->sql_fetch_array($res3, MYSQLI_ASSOC)) {
                    $data['wsrep_streaming_log'][] = $arr3;
                }
            }
        }
        $data['wsrep_streaming_log'] = json_encode($data['wsrep_streaming_log']);

        Debug::debug($data);

        return $data;

//$sql2 = "SELECT * FROM wsrep_cluster_members;";
    }

    public function getLockingQueries($param = array())
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "select /* pmacontrol */ count(1) as cpt FROM INFORMATION_SCHEMA.PLUGINS where PLUGIN_NAME='METADATA_LOCK_INFO' and PLUGIN_STATUS='ACTIVE';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            if ($ob->cpt === "1") {
                $sql2 = "select concat('KILL ',C.ID,';') as `kill` , C.INFO as `query_locking`, GROUP_CONCAT(P.INFO) as `query_locked`, GROUP_CONCAT(P.ID ) as `id_locked`
FROM INFORMATION_SCHEMA.PROCESSLIST P, INFORMATION_SCHEMA.METADATA_LOCK_INFO M
INNER JOIN INFORMATION_SCHEMA.PROCESSLIST C ON M.THREAD_ID = C.ID
WHERE LOCATE(lcase(M.LOCK_TYPE), lcase(P.STATE))>0 and M.THREAD_ID != P.ID
GROUP BY C.ID, C.INFO;";

                $res2 = $db->sql_query($sql2);
                $tmp  = array();

                $tmp['query_locking_count'] = $db->sql_num_rows($res2);

                while ($arr2 = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {

                    $tmp['query_locking_detail'][] = json_encode($arr2, JSON_PRETTY_PRINT);
                }

                Debug::debug($tmp);

                return $tmp;
            } else {
                Debug::debug("METADATA_LOCK_INFO is not installed");
            }
        }
    }

    public function getProxySQL($param = array())
    {
        Debug::parseDebug($param);

        $id_proxysql_server = $param[0];
    }



    public function testProxy($param)
    {
        Debug::parseDebug($param);

        $db = Proxy::getDbLink(1);

        $sql = "select * from stats_mysql_connection_pool;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            Debug::debug($ob);
        }
    }


    /*
     * available = 0 : server down
     * available = 1 : server operationel
     * available = 2 : waiting answer
     * 
     */
    public function setService($id_mysql_server, $ping, $error_msg, $available, $type)
    {
        if (! in_array($type, array('mysql', 'ssh', 'proxysql', 'maxscale', 'maxscale_service'))) {
            die('error');
        }

        $service                              = array();
        $service[$type.'_server'][$type.'_available'] = $available;
        $service[$type.'_server'][$type.'_ping']      = round($ping, 6);
        $service[$type.'_server'][$type.'_error']     = $error_msg;
        $this->exportData($id_mysql_server,$type.'_server',$service,false);
    }


    public function debug($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = Extraction::display(array(  "ssh_server::ssh_available"));
        Debug::debug($id_mysql_server, "ssh available");

        $mysql = Extraction::display(array( "mysql_server::mysql_available"), array(1));
        Debug::debug($mysql, "mysql available");
    }


    public function getSchema($id_mysql_server)
    {
        $mysql_tested = Mysql::getDbLink($id_mysql_server);
        $schemas = array();
        if ($mysql_tested->testAccess()) {

            //$this->logger->debug("We import schema list from id_mysql_server : ".$id_mysql_server);
            $sql = "SELECT * FROM information_schema.schemata";

            $res = $mysql_tested->sql_query($sql);

            while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $schemas[] = array_change_key_case($arr);
            }
        }
        
        return $schemas;
    }
    /*
    *
    *   chack if data as been modified if yes, we will import

    */



    // Dans le cas des worker pour eviter de les relancer on recharge la configuation des serveurs MySQL, lorsque un nouveau server est ajouté.
    public function keepConfigFile($param)
    {
        Debug::parseDebug($param);
        $file = CONFIG."db.config.ini.php";

        $ts = filemtime($file);
        if (empty(self::$timestamp_config_file)) {
            $this->logger->debug("[WORKER:".$param['pid']."] We set TS for DB config");
            self::$timestamp_config_file = $ts;
        }
        else if (self::$timestamp_config_file != $ts) {
            
            $db_config = parse_ini_file($file, true);
            Sgbd::setConfig($db_config);
            $this->logger->notice("[WORKER:".$param['pid']."] We imported new config for DB");
            self::$timestamp_config_file = $ts;
        }
        Debug::debug($ts, "timestamp");
    }



    public function testproxysql($param)
    {
        $db = Sgbd::sql('server_6612a9fcb1641');

        $db->sql_query("SHOW VARIABLES");

        Debug::parseDebug($param);

        //Mysql::test2("127.0.0.1", 6033, "stnduser", "stnduser");
    }

    /*
    type => key/value, json 
    */

    
    public function exportData($id_mysql_server, $ts_file, array $data, $check_data = true, $separator=EngineV4::SEPERATOR)
    {
        Debug::debug("exportData $id_mysql_server, $ts_file, ".count($data)."");

        //better there than in integrate
        $data = array_change_key_case($data);

        if (! is_array($data)) {
            trigger_error("PMATRL-347 : data must be an array", E_USER_ERROR);
            throw new \Exception("data must be an array !");
        }
        
        $import = true;
        if ($check_data === true){
            $import = false;
            if (($this->isDataModified($id_mysql_server, $ts_file, $data) === true) ){
                $import = true;
            }
        }

        if ($import === true) {
            //$this->logger->debug("[IMPORT] $ts_file we import DB to shared memory [id_mysql_server:".$id_mysql_server."]");
            //$ts_in_µs = Microsecond::timestamp();
            $ts  = time();

            $tmp[$ts][$id_mysql_server] = $data;

            $memory = $this->allocate_shared_storage($ts_file, $separator);
            $memory->{$id_mysql_server}     = $tmp;
        }
    }


    public function isDataModified($id_mysql_server, $ts_file, $data)
    {
        self::isValidStruc($data);
        $json = json_encode($data);
        //return true;
        $md5      = md5($json);

        $file_md5 = EngineV4::getFileMd5($ts_file, $id_mysql_server);
        
        $export = false;

        Debug::debug($md5, "NEW MD5 $file_md5");

        if (file_exists($file_md5)) {
            $cmp_md5 = file_get_contents($file_md5);
            Debug::debug($cmp_md5, "OLD MD5 $file_md5");

            if ($cmp_md5 != $md5) {
                $export = true;

                file_put_contents($file_md5, $md5);
            }
        } else {
            if (!is_writable(dirname($file_md5))) {
                Throw new \Exception('PMACTRL-858 : Cannot write file in directory : '.dirname($file_md5).'');
            } 
            file_put_contents($file_md5, $md5);
            $export = true;
        }

        return $export;
    }

    public function test2($param)
    {
        Debug::parseDebug($param);
        $id_mysql_server = 1;

        $data['mysql_database'] = json_encode($this->getSchema($id_mysql_server));
        $this->exportData($id_mysql_server, "mysql_schemata", $data);
    }


    /*
    public function import($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        
        $files = glob(EngineV4::PATH_PIVOT_FILE."*");

        $list = array();
        foreach($files as $file)
        {
            $file_name = pathinfo($file)['basename'];
            Debug::debug($file_name, 'file name');

            $ts_file = explode(EngineV4::SEPERATOR, $file_name)[1];

            if (! isset($list[$ts_file])) {
                $list[$ts_file] = 0;
            }
            $list[$ts_file]++;
        }

        foreach ($list as $ts_file => $val)
        {
            if ($val >= 4)
            {
                $sql = "INSERT IGNORE INTO ts_file (file_name) VALUES ('".$ts_file."')";
                $db->sql_query($sql);

                Mysql::addMaxDate();
                \App\Controller\Control::updateLinkVariableServeur();
            }
        }

        Debug::debug($list);
    }*/

    /*
    only used for command line
    */

    /* deprecated
    function updateChown()
    {
        foreach(self::$file_created as $i => $shared_file) {
            $this->Chown($shared_file);
            unset(self::$file_created[$i]);
        }
    }*/

    /*
    only used for command line
    the goal is to prevent file with root
    */
    function after($param)
    {
        // need test if root
        //$this->updateChown(); => t1 pourquoi j'ai pas mis ca la avant :D
        if (\posix_geteuid() === 0) {

            shell_exec("chown www-data:www-data -R ".EngineV4::PATH_PIVOT_FILE);
            shell_exec("chown www-data:www-data -R ".EngineV4::PATH_MD5);
            shell_exec("chown www-data:www-data -R ".EngineV4::PATH_PID);
            shell_exec("chown www-data:www-data -R ".EngineV4::PATH_LOCK);

            Debug::debug("Time to wait all PIVOT_FILE to be created to change chown because we are root");
            usleep(50000);
            
            //case debian
            shell_exec("chown www-data:www-data -R ".EngineV4::PATH_PIVOT_FILE);
            shell_exec("chown www-data:www-data -R ".EngineV4::PATH_MD5);
            shell_exec("chown www-data:www-data -R ".EngineV4::PATH_PID);
            shell_exec("chown www-data:www-data -R ".EngineV4::PATH_LOCK);
            
            //case redhat like
        }

    }

    static function isValidStruc($array) {
        // Vérifier si le tableau est un tableau à deux niveaux
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // Parcourir le sous-tableau pour vérifier que toutes les valeurs sont des chaînes
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        if (isset($subValue['Seconds_Behind_Master'])){
                            return true;
                        }

                        if (isset($subValue['seconds_behind_master'])){
                            return true;
                        }

                        if (isset($subValue['Digest'])){
                            return true;
                        }

                        //Debug::debug($subValue, "NOT A STRING");
                        trigger_error("PMATRL-478 : Wrong level of DATA (One rank of Array too much) check : $key", E_USER_ERROR);
                        throw new \Exception("Wrong level of DATA (One rank of Array too much) check : $key");
                    }
                }
            } else {
                trigger_error("PMATRL-83 : Wrong level of DATA (One rank of Array missing) check : $key", E_USER_ERROR);
                throw new \Exception("Wrong level of DATA (One rank of Array missing) check : $key");
            }
        }
        return true; // Tout est vérifié, retourner true
    }


    public function getMysqlLatencyByQuery($name_server)
    {
        

        $db = Sgbd::sql($name_server);
        
        $data = array();
        /*
        //to be friendly with all version, we take all, value who not move will be not imported anymore that's all
        $sql = "SELECT `s2`.`avg_us` AS `avg_us` 
        from (
            (select count(0) AS `cnt`,round(`performance_schema`.`events_statements_summary_by_digest`.`AVG_TIMER_WAIT` / 1000000,0) AS `avg_us` 
            from `performance_schema`.`events_statements_summary_by_digest` 
            group by round(`performance_schema`.`events_statements_summary_by_digest`.`AVG_TIMER_WAIT` / 1000000,0)) `s1` 
            join 
            (select count(0) AS `cnt`,round(`performance_schema`.`events_statements_summary_by_digest`.`AVG_TIMER_WAIT` / 1000000,0) AS `avg_us` 
            from `performance_schema`.`events_statements_summary_by_digest` 
            group by round(`performance_schema`.`events_statements_summary_by_digest`.`AVG_TIMER_WAIT` / 1000000,0)) `s2` 
            on(`s1`.`avg_us` <= `s2`.`avg_us`)
        ) 
        group by `s2`.`avg_us` 
        having ifnull(sum(`s1`.`cnt`) / nullif((select count(0) from `performance_schema`.`events_statements_summary_by_digest`),0),0) > 0.95
        order by ifnull(sum(`s1`.`cnt`) / nullif((select count(0) from `performance_schema`.`events_statements_summary_by_digest`),0),0) 
        limit 1";

        $res = $db->sql_query($sql);

        $data = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            
            $data['query_latency_µs_95'] =  $arr['avg_us'];
        }*/

        // upgrade because : ERROR 1690 (22003): BIGINT UNSIGNED value is out of range (in case of lot queries !)
        $sql = "SELECT ROUND( (SUM(CAST(COUNT_STAR AS DECIMAL(30,0)) * CAST(AVG_TIMER_WAIT AS DECIMAL(30,0)))) / SUM(COUNT_STAR) / 1000000, 0 ) AS time_average
        FROM  `performance_schema`.`events_statements_summary_by_digest`
        WHERE LAST_SEEN > (NOW() - INTERVAL 1 MINUTE);";

        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['query_latency_1m'] = $arr['time_average'];
        }

        $sql = "SELECT ROUND( (SUM(CAST(COUNT_STAR AS DECIMAL(30,0)) * CAST(AVG_TIMER_WAIT AS DECIMAL(30,0)))) / SUM(COUNT_STAR) / 1000000, 0 ) AS time_average
        FROM  `performance_schema`.`events_statements_summary_by_digest`
        WHERE LAST_SEEN > (NOW() - INTERVAL 10 MINUTE);";

        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['query_latency_10m'] = $arr['time_average'];
        }

        $sql = "SELECT ROUND( (SUM(CAST(COUNT_STAR AS DECIMAL(30,0)) * CAST(AVG_TIMER_WAIT AS DECIMAL(30,0)))) / SUM(COUNT_STAR) / 1000000, 0 ) AS time_average
        FROM  `performance_schema`.`events_statements_summary_by_digest`
        WHERE LAST_SEEN > (NOW() - INTERVAL 1 HOUR);";

        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['query_latency_1h'] = $arr['time_average'];
        }

        $sql = "SELECT ROUND( (SUM(CAST(COUNT_STAR AS DECIMAL(30,0)) * CAST(AVG_TIMER_WAIT AS DECIMAL(30,0)))) / SUM(COUNT_STAR) / 1000000, 0 ) AS time_average
        FROM  `performance_schema`.`events_statements_summary_by_digest`
        WHERE LAST_SEEN > (NOW() - INTERVAL 24 HOUR);";

        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['query_latency_24h'] = $arr['time_average'];
        }

        return $data;
    }

    public function getInnodbMetrics($name_server)
    {
        $db = Sgbd::sql($name_server);
        $sql = "SELECT * FROM `INFORMATION_SCHEMA`.`INNODB_METRICS`;";

        $res = $db->sql_query($sql);
        $data = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            if (empty($arr['ENABLED'])) {
                continue;
            }
            $arr = array_change_key_case($arr);
            $key = $arr['name'];
            unset($arr['name']);
            $data[$key] = json_encode($arr);

        }
        return $data;

    }


    public function tryProxySqlConnection($param)
    {
        Debug::parseDebug($param);
        $this->view = false;

        
        //$this->logger->notice("##################".json_encode($param));
        
        $name_server = $param[0];
        $id_proxysql_server   = $param[1]; // or split ?
        $refresh = $param[2] ?? "";
        //$id_mysql_server = ;

        if (empty($id_proxysql_server)) {
            throw new \Exception(__function__.' should have id_proxysql_server in parameter');
        }

        Debug::debug($param, "NAME_SERVER");

        try{
            $error_msg='';
            $time_start = microtime(true);
            $db = Sgbd::sql("proxysql_".$id_proxysql_server);  // need try catch there
            $db->sql_select_db("main");
        }
        catch(\Exception $e){
            $error_msg = $e->getMessage();
            $this->logger->warning($error_msg." id_proxysql_server:$id_proxysql_server");
        }
        finally{
            $ping = microtime(true) - $time_start;
            $available = empty($error_msg) ? 1 : 0;

            $id_mysql_server = ProxySQL::getIdMysqlServer(array($id_proxysql_server));

            if (!empty($id_mysql_server))
            {
                $this->setService($id_mysql_server, $ping, $error_msg, $available, "proxysql");
            }
            
            // VERY important else we got error and we kill the worker and have to restart with a new one
            if ($available === 0) {
                return false;
            }
        }

        //Debug::debug($db, "DB");

        if (empty($id_mysql_server))
        {

            /* get id_mysql_server associed to the front_end */
            $default = Sgbd::sql(DB_DEFAULT);
            $sql = "SELECT * FROM proxysql_server WHERE id=".$id_proxysql_server;
            $res = $default->sql_query($sql);
            while($ob = $db->sql_fetch_object($res))
            {
                $id_mysql_server = $ob->id_mysql_server;
                $ip_proxysql_server = $ob->hostname;
            }

            $default->sql_close();
            /*********************** */
            
            // cas ou on a pas de SERVER mysql associé on force l'auto dsicovery
            //$id_mysql_server

            if (empty($id_mysql_server))
            {
                //auto discovery mysql to add server to mysql_server
                $sql = "SELECT * FROM global_variables WHERE variable_name IN ('mysql-monitor_username', 'mysql-monitor_password', 'mysql-interfaces');";
                $res = $db->sql_query($sql);
                while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
                {
                    Debug::debug($arr, "global variable PROXYSQL");
                    
                    switch($arr['variable_name'])
                    {
                        case 'mysql-interfaces':
                            $port = explode(":",$arr['variable_value'])[1];
                            break;

                        case 'mysql-monitor_username':
                            $user = $arr['variable_value'];
                            break;

                        case 'mysql-monitor_password':
                            $password = $arr['variable_value'];
                            break;
                    }
                }

                try {
                    $ret = Mysql::testMySQL(array($ip_proxysql_server,$port,$user, $password  ));

                }
                catch(\Exception $e) {


                    Debug::debug($e->getMessage(), "dfgdgf");
                    
                    $sql = "SELECT DISTINCT hostgroup_id,hostname,port FROM runtime_mysql_servers;";
                    $res = $db->sql_query($sql);
                    Debug::sql($sql);

                    while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
                    {
                        Debug::debug($arr, "list of mysql_server");

                        $mysql_server_hostname = $arr['hostname'];
                        $mysql_server_port = $arr['port'];
                        $hostgroup_id = $arr['hostgroup_id'];

                        $name_server = Mysql::getNameMysqlServerFromIpPort($mysql_server_hostname,$mysql_server_port);

                        $mysql_to_link = Sgbd::sql($name_server);

                        $sql3 = "SELECT password as password FROM mysql.user WHERE user='$user'";
                        $res3 = $mysql_to_link->sql_query($sql3);
                        while ($ob = $mysql_to_link->sql_fetch_object($res3)){
                            Debug::debug($ob, "password");
                            // il faut recupérer le bon
                            $password_hash = $ob->password;
                        }
                    
                        $sql2 = "SELECT count(1) as cpt FROM runtime_mysql_users WHERE username= '$user'";
                        Debug::sql($sql2);
                        $res2 = $db->sql_query($sql2);

                        while($ob2 = $db->sql_fetch_object($res2))
                        {
                            //il faut stocker memory somewhere 
                            // made update
                            // restore it

                            //uniquement si l'user n'est pas presént pour eviter des effet de bord
                            if ($ob2->cpt == "0") {

                                $sql5 = "LOAD MYSQL USERS FROM DISK;";
                                Debug::sql($sql5);
                                $db->sql_query($sql5);

                                $sql4 = "INSERT INTO mysql_users(username,password,default_hostgroup,default_schema) 
                                VALUES ('".$user."','".$password_hash."',".$hostgroup_id.",'mysql');";
                                Debug::sql($sql4);
                                $db->sql_query($sql4);

                                $sql6 = "LOAD MYSQL USERS TO RUNTIME;";
                                Debug::sql($sql6);
                                $db->sql_query($sql6);


                                //try connection
                                $ret = Mysql::testMySQL(array($mysql_server_hostname,$port,$user, $password  ));

                                if ($ret === true)
                                {
                                    $data = array();

                                    $data['fqdn'] = $mysql_server_hostname;
                                    $data['login'] = $user;
                                    $data['password']= $password;
                                    $data['port'] = $port;
                                    
                                    
                                    Mysql::addMysqlServer($data );
                                    
                                    $sql7 = "SAVE MYSQL USERS TO DISK;";
                                    $db->sql_query($sql7);

                                    ProxySQL::associate(array($id_proxysql_server ));
                                }
                            }
                        }
                    }
                }

                ProxySQL::associate(array($id_proxysql_server ));
            }
        }

        // Proxy have a 6033 routed to a server
        if (!empty($id_mysql_server))
        {

            $sql = "show tables like 'runtime%';";
            Debug::sql($sql);
            $res = $db->sql_query($sql);
            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
            {
                $table_name = $arr['tables'];

                
                Debug::debug($table_name, "TABLE NAME");

                $short_name = str_replace('runtime_', '', $table_name);

                $export = $this->getElemFromTable(array($name_server, "main", $table_name));

                if (count($export) > 0)
                {
                    $data = array();
                    $data['proxysql_runtime'][$short_name] = json_encode($export, JSON_PRETTY_PRINT);

                    Debug::debug($data['proxysql_runtime'][$short_name], $table_name);
                    

                    $this->exportData($id_mysql_server, "proxysql_".$table_name, $data, true);
                }
                
            }

            if ((time()+$id_mysql_server)%1 === 0)
            {
                $data = array();
                Debug::debug($name_server);
                $data['proxysql_connect_error']['proxysql_connect_error'] 
                = json_encode(ProxySQL::getErrorConnect(array($id_proxysql_server)));
                Debug::debug($data);
                $this->exportData($id_mysql_server, "proxysql_connect_error", $data, false);
            }

            /**** */
        // $import = array();
        // $import['table']['proxysql_connect_error'] = $this->getErrorConnect($param);
        }

        
        $db->sql_close();

    }

/*
    public function getRuntimeMysqlServer($param)
    {
        $name_connection = $param[0];
        $mysql_tested = Sgbd::sql($name_connection);

        $sql = "select * from runtime_mysql_servers;";
        $res = $mysql_tested->sql_query($sql);

        $runtime_mysql_servers = array();
        while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $runtime_mysql_servers[] = $arr; 
        }

        return $runtime_mysql_servers;
    }

*/

    public function getElemFromTable($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database = $param[1];
        $table = $param[2];
        
        $pos = strpos($id_mysql_server, "proxysql_");

        $table_exist = false;
        if ($pos === false) {


            $table_exist = $this->getTableExist($id_mysql_server, $database, $table);
        }
        else {
            //cas proxysql
            $this->getTableFromProxySQL($id_mysql_server);
            if (!empty(self::$cache[$id_mysql_server][$database][$table])) {
                $table_exist = self::$cache[$id_mysql_server][$database][$table];
            }
        }

        if ($table_exist) {
            return $this->getTableElems($id_mysql_server, $database, $table);
        }
        
        return false;
    }


    private function getTableElems($id_mysql_server, $database, $table)
    {
        if ($id_mysql_server == (int)$id_mysql_server){
            $mysql_tested = Mysql::getDbLink($id_mysql_server);
        }
        else {
            $mysql_tested = Sgbd::sql($id_mysql_server);
        }

        if (isset(self::$primary_key[$database][$table]))
        {

            Debug::debug(self::$primary_key, "TABLE PRIMARY");
            $field_name = self::$primary_key[$database][$table]['pk'];
            $value = self::$primary_key[$database][$table]['val'];
        }
        else
        {
            $field_name ='';
        }

        $table_elems = array();
        $sql2 ="SELECT * FROM `".$database."`.`".$table."`;";
        $res2 = $mysql_tested->sql_query($sql2);
        while ($ob2 = $mysql_tested->sql_fetch_array($res2, MYSQLI_ASSOC)) {

            if (empty($field_name)) {
                $table_elems[] = $ob2;
            }
            else {
                $table_elems[$ob2[$field_name]] = $ob2[$value];
            }
        }

        $data = $table_elems;
        return $data;
    }


    private function getTableExist($id_mysql_server, $database, $table)
    {
        //better with cache =)
        if (isset(self::$cache[$id_mysql_server][$database][$table])){
            return self::$cache[$id_mysql_server][$database][$table];
        }

        $mysql_tested = ($id_mysql_server == (int) $id_mysql_server) ? Mysql::getDbLink($id_mysql_server) : Sgbd::sql($id_mysql_server);

        $sql = "SELECT count(1) AS cpt
        FROM information_schema.tables 
        WHERE TABLE_SCHEMA = '".$database."' AND TABLE_NAME = '".$table."';";

        $res = $mysql_tested->sql_query($sql);
        $data = array();

        while($ob = $mysql_tested->sql_fetch_object($res)) {
            if ($ob->cpt === "1") {
                self::$cache[$id_mysql_server][$database][$table] = true;
                return true;
            }
        }

        self::$cache[$id_mysql_server][$database][$table] = false;
        return false;
    }

    private function getTableFromProxySQL($id_proxysql)
    {
        $mysql_tested = Sgbd::sql($id_proxysql);

        if (isset(self::$cache[$id_proxysql])) {
            return true;
        }

        $cache = array();

        $sql = "SHOW DATABASES";
        $res = $mysql_tested->sql_query($sql);

        while($ob = $mysql_tested->sql_fetch_object($res))
        {
            
            $sql2 ="SHOW TABLES in `".$ob->name."`";
            $res2 = $mysql_tested->sql_query($sql2);
            while($ob2 = $mysql_tested->sql_fetch_object($res2))
            {
                self::$cache[$id_proxysql][$ob->name][$ob2->tables] = true;
            }

        }
    }
    

    public function getPsMemory($id_mysql_server)
    {
        if ($id_mysql_server == (int)$id_mysql_server){
            $mysql_tested = Mysql::getDbLink($id_mysql_server);
        }
        else {
            $mysql_tested = Sgbd::sql($id_mysql_server);
        }

        $table_elems = array();
        $sql ="SELECT event_name, sum_number_of_bytes_alloc, high_number_of_bytes_used
        FROM performance_schema.memory_summary_global_by_event_name 
        WHERE current_count_used > 0 order by 2 desc;";
        $res = $mysql_tested->sql_query($sql);
        while ($ob = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $table_elems[] = $ob;
        }

        return $table_elems;


    }


    public function getDigest($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        // to put in function i think
        if ($id_mysql_server == (int)$id_mysql_server){
            $mysql_tested = Mysql::getDbLink($id_mysql_server);
        }
        else {
            $mysql_tested = Sgbd::sql($id_mysql_server);
        }

        $sql ="SELECT * FROM performance_schema.events_statements_summary_by_digest WHERE LAST_SEEN > NOW() - INTERVAL 2 DAY;";
        Debug::sql($sql);
        
        $res = $mysql_tested->sql_query($sql);
        $i = 0;

        $data = [];
        while ($arr = $mysql_tested->sql_fetch_array($res, MYSQLI_ASSOC)) {  
            $i++;
            $arr = array_change_key_case($arr);

            // Skip invalid lines (no digest)
            if (empty($arr['digest'])) {
                continue;
            }

            $data[] = $arr;
        }

        Debug::debug($data, "QUERIES");

        return $data;

    }


    public function getProcesslist($db_link)
    {
        $time = 0;

        if ($db_link->checkVersion(array('MySQL' => '5.1', 'Percona Server' => '5.1', 'MariaDB' => '5.1'))) {
            $time = intval($time);

            

            if ($db_link->checkVersion(array('MySQL' => '8.0')))
            {
                //$this->logger->alert("PROVIDER : ".$db_link->getVersion() ." - VERSION : ".$db_link->getServerType() );

                $sql  = "SELECT p.*,
                IFNULL(t.trx_rows_locked, '0')        AS trx_rows_locked,
                IFNULL(t.trx_state, '')               AS trx_state,
                IFNULL(t.trx_operation_state, '')     AS trx_operation_state,
                IFNULL(t.trx_rows_locked, '0')        AS trx_rows_locked,
                IFNULL(t.trx_rows_modified, '0')      AS trx_rows_modified,
                IFNULL(t.trx_concurrency_tickets, '') AS trx_concurrency_tickets,
                IFNULL(TIMESTAMPDIFF(SECOND, t.trx_started, NOW()), '') AS trx_time
                FROM performance_schema.processlist p
                LEFT JOIN information_schema.innodb_trx t ON p.ID = t.trx_mysql_thread_id
                WHERE p.command NOT IN ('Sleep', 'Binlog Dump')
                AND p.user NOT IN ('system user', 'event_scheduler') AND TIME > ".$time;
            }
            else
            {
                $sql  = "SELECT p.*,
                IFNULL(t.trx_rows_locked, '0')        AS trx_rows_locked,
                IFNULL(t.trx_state, '')               AS trx_state,
                IFNULL(t.trx_operation_state, '')     AS trx_operation_state,
                IFNULL(t.trx_rows_locked, '0')        AS trx_rows_locked,
                IFNULL(t.trx_rows_modified, '0')      AS trx_rows_modified,
                IFNULL(t.trx_concurrency_tickets, '') AS trx_concurrency_tickets,
                IFNULL(TIMESTAMPDIFF(SECOND, t.trx_started, NOW()), '') AS trx_time
                FROM information_schema.processlist p
                LEFT JOIN information_schema.innodb_trx t ON p.ID = t.trx_mysql_thread_id
                WHERE p.command NOT IN ('Sleep', 'Binlog Dump')
                AND p.user NOT IN ('system user', 'event_scheduler') AND TIME > ".$time;
            }

            $res  = $db_link->sql_query($sql);
            $ret  = array();
            while ($data = $db_link->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $ret[] = $data;
            }

            return $ret;
        }
    }


    static function array_values_to_lowercase(array $array): array {
        return array_map(function($value) {
            return is_string($value) ? strtolower($value) : $value;
        }, $array);
    }


    public function getTables($param)
    {
        $id_mysql_server = $param[0];

        if (Available::getMySQL($id_mysql_server) == false)
        {
            return;
        }

        $db = Mysql::getDbLink($id_mysql_server);

        // if MARIADB ask limit 10 sec max
        if ($db->checkVersion(array('MariaDB'=> '10.1.1'))) {
            $sql = "SET STATEMENT MAX_STATEMENT_TIME = 10 FOR SELECT * FROM information_schema.tables;";
        }
        else {
            $sql = "SELECT * FROM information_schema.tables;";
        }
        
        $res = $db->sql_query($sql);

        $ret  = array();
        while ($data = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $ret[] = $data;
        }

        Debug::debug($ret);

        return $ret;
    }


    public function getCreateTables($param)
    {
        Debug::parseDebug($param);
        $id_mysql_server = $param[0];

        if (Available::getMySQL($id_mysql_server) == false) {
            return;
        }

        $db = Mysql::getDbLink($id_mysql_server);

        $sql = "SHOW CREATE TABLE information_schema.tables;";
        $res = $db->sql_query($sql);

        $table = "";
        while ($arr = $db->sql_fetch_array($res, MYSQLI_NUM)) {
            $table = $arr[1];
        }

        return $table;
    }


    public function eachHour($param)
    {
        Debug::parseDebug($param);

        $list_id_mysql_servers = Available::getMySQL(0);

        $id_mysql_servers = explode(',', $list_id_mysql_servers);
        foreach($id_mysql_servers as $id_mysql_server)
        {
            Debug::debug($id_mysql_server, "id_mysql_server");

            if (Available::getMySQL($id_mysql_server) == false)
            {
                continue;
            }

            $db = Mysql::getDbLink($id_mysql_server);

            $data = array();
            $data['information_schema']['disks'] = json_encode($this->getDisks(array($id_mysql_server)));
            $data['information_schema']['tables'] = json_encode($this->getTables(array($id_mysql_server)));
            $data['information_schema']['create_tables'] = $this->getCreateTables(array($id_mysql_server));
            
            $db->sql_close();

            //Debug::debug($data);

            $this->exportData($id_mysql_server, "is_tables", $data);
        }

        //Debug::debug($data);
    }


    public function getDisks($param)
    {
        Debug::parseDebug($param);
        
        $id_mysql_server = $param[0];

        $ret  = array();

        if (Available::getMySQL($id_mysql_server) == false) {
            return $ret;
        }

        $db = Mysql::getDbLink($id_mysql_server);

        $sql2 = "select count(1) as cpt from information_schema.plugins WHERE PLUGIN_NAME='DISKS' AND PLUGIN_STATUS='ACTIVE';";
        $res2 = $db->sql_query($sql2);
        $ob = $db->sql_fetch_object($res2);
        
        if ($ob->cpt == "1")
        {
            // if MARIADB ask limit 10 sec max
            if ($db->checkVersion(array('MariaDB'=> '10.1.1'))) {
                $sql = "SET STATEMENT MAX_STATEMENT_TIME = 10 FOR SELECT * from information_schema.disks;";
            }
            else{
                $sql = "SELECT * FROM information_schema.disks;";
            }
            
            $res = $db->sql_query($sql);

            
            while ($data = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $ret[] = $data;
            }

            Debug::debug($ret,"DISK");
        }
        
        return $ret;
    }

    public function getVelocity($name_server)
    {

        $db = Sgbd::sql($name_server);
        
        $data = array();

        // upgrade because : ERROR 1690 (22003): BIGINT UNSIGNED value is out of range (in case of lot queries !)

        //ERROR SQL : (10.68.68.165:6002) {/srv/www/pmacontrol/App/Controller/Aspirateur.php:2178, 
        // ERROR: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '�S          from performance_schema.events_statements_summary_by_digest' at line 1}
        $sql = "SELECT sum(COUNT_STAR) as PS_NB_QUERY, ROUND(sum(SUM_TIMER_WAIT)/1000000,0) as PS_SUM_TIMER_WAIT_MICRO_SEC 
        FROM performance_schema.events_statements_summary_by_digest;";

        $res = $db->sql_query($sql);
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['PS_SUM_TIMER_WAIT_µS'] = $arr['PS_SUM_TIMER_WAIT_MICRO_SEC']; 
            $data['PS_NB_QUERY'] = $arr['PS_NB_QUERY'];
        }

        $sql = "SELECT CASE WHEN SUBSTRING(DIGEST_TEXT, 1, 1) = '(' THEN SUBSTRING_INDEX(SUBSTRING(DIGEST_TEXT, 3), ' ', 1) 
        ELSE SUBSTRING_INDEX(DIGEST_TEXT, ' ', 1) END AS statement_type,
        COUNT(*) AS count_statements, 
        sum(COUNT_STAR) as sum_count_star, 
        ROUND(sum(SUM_TIMER_WAIT)/1000000,0) as ps_sum_timer_wait_micro_sec, 
        ROUND(sum(SUM_TIMER_WAIT)/sum(COUNT_STAR)/1000000,0) as by_elem   
        FROM performance_schema.events_statements_summary_by_digest  
        GROUP BY statement_type ORDER BY PS_SUM_TIMER_WAIT_MICRO_SEC DESC, count_statements DESC;";
        $res = $db->sql_query($sql);

        $tmp = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $tmp[] = $arr;
        }
        $data['pma_statement_type_summary'] = json_encode($tmp);

        return $data;
    }

    public function detectDouble($param)
    {
        Debug::parseDebug($param);

        $servers = Extraction2::display(array("variables::hostname","variables::port","variables::server_uid" , "variables::is_proxysql" ));

        function find_duplicate_server_uids(array $servers): array {
            $map = [];
            foreach ($servers as $entry) {
                if (!empty($entry['server_uid'])) {
                    $map[$entry['server_uid']][] = $entry['id_mysql_server'];
                }
            }

            // Ne garder que ceux avec plus d'une occurence
            $dups = [];
            foreach ($map as $uid => $ids) {
                if (count($ids) > 1) {
                    $dups[$uid] = $ids;
                }
            }
            return $dups;
        }


        $duplicates = find_duplicate_server_uids($servers);

        // Affichage "joli" au format que tu as demandé : [UID] => (id1, id2, ...)
        foreach ($duplicates as $uid => $ids) {
            echo "[$uid] => (" . implode(', ', $ids) . ")\n";
        }

        //Debug::debug($hostnames);

    }


    public function gg($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id_mysql_server, a.date, a.date_p1, a.date_p2,a.date_p3,a.date_p4 FROM ts_max_date a
        INNER JOIN ts_file b ON a.id_ts_file = b.id
        INNER JOIN ts_variable c on c.id_ts_file = b.id
        WHERE `name` = 'server_uid' and a.id_mysql_server in(89);";

        $res = $db->sql_query($sql);

        while($ob = $db->sql_fetch_object($res))
        {
            $date = [$ob->date, $ob->date_p1, $ob->date_p2, $ob->date_p4, $ob->date_p4];
        }

        $ret= Extraction2::display(array("server_uid"), array(), $date, false);
        Debug::debug($ret);
    }

    public function tryMaxScaleConnection($param)
    {
        Debug::parseDebug($param);
        $this->view = false;

        
        //$this->logger->notice("##################".json_encode($param));
        
        $list_id_mysql_server = $param[0];
        $id_mysql_servers = explode(',',$list_id_mysql_server);

        $id_maxscale_server   = $param[1]; // or split ?
        $refresh = $param[2] ?? "";
        //$id_mysql_server = ;

        if (empty($id_maxscale_server)) {
            throw new \Exception(__function__.' should have id_proxysql_server in parameter');
        }

        Debug::debug($param, "NAME_SERVER");

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT `hostname`, `is_ssl`,`port`, `login`,`password` from maxscale_server WHERE `id` = ".$id_maxscale_server .";";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) == 0) {
            throw new \Exception("[PMACONTROL-2001] No MaxScale server found with id '{$id_maxscale_server}'. Check configuration or connection settings.");
        }

        while ($arr = $db->sql_fetch_array($res, MYSQLI_NUM)) {
            $maxscale = $arr;
        }

        try{
            $error_msg= '';

            $time_start   = microtime(true);

            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            });

            $connection = fsockopen($maxscale[0], $maxscale[2], $errno, $errstr, 3);
            if ($connection) {
                fclose($connection);
            }
        }
        catch(\Throwable $e){
            $error_msg = $e->getMessage();
            $this->logger->warning("[PMACONTROL-2005] cannot reach IP:Port : $error_msg - id_maxscale_server:$id_maxscale_server");
        }
        finally{

            restore_error_handler();

            $ping = microtime(true) - $time_start;
            $available = empty($error_msg) ? 1 : 0;

            // associate =)
            $id_mysql_server = MaxScale::getIdMysqlServer(array($id_maxscale_server));
            
            foreach($id_mysql_servers as $id_mysql_server) {
                $this->setService($id_mysql_server, $ping, $error_msg, $available, "maxscale");
            }
            // VERY important else we got error and we kill the worker and have to restart with a new one

            if (empty($available))
            {
                return false;
            }
        }
        // Maxscale have a 4003 routed to a server

        $services = array("listeners", "maxscale", "monitors", "sessions", "servers", "services", "users","filters");

        foreach($services as $service)
        {
            $maxscale[5] = $service; 

            Debug::debug($service, "SERVICE");

            try{
                $error_msg = '';
                $time_start   = microtime(true);
                $array = MaxScale::curl($maxscale) or die($service);

                Debug::debug(MaxScale::removeArraysDeeperThan( $array, 3));

                $data = array();
                $data['maxscale']['maxscale_'.$service] = json_encode($array);

                foreach($id_mysql_servers as $id_mysql_server) {
                    $this->exportData($id_mysql_server, "maxscale_".$service, $data);
                }
                
            }
            catch (\Throwable $e) {

                //echo "⚠️ Erreur inattendue capturée mais ignorée : " . $e->getMessage() . "\n";
                $this->logger->warning($e->getMessage()." id_maxscale_server:$id_maxscale_server");
                Debug::debug($e->getMessage(), "ERROR_MSG");
                $error_msg = $e->getMessage();
            }
            finally
            {
                $ping = microtime(true) - $time_start;
                $available = empty($error_msg) ? 1 : 0;

                foreach($id_mysql_servers as $id_mysql_server) {
                    $this->setService($id_mysql_server, $ping, $error_msg, $available, "maxscale_service");
                }
            }
        }
        
        $db->sql_close();
    }

    
    private function runEachMinuteAtBalancedSecond(int $id_mysql_server,int $interval, string $file_key, callable $callback): bool
    {
         // 1 minute
        $offset = crc32((string)$id_mysql_server) % $interval; // seconde cible dans la minute
        $now = time();
        $sec = $now % $interval;
        $minute = intdiv($now, $interval);

        // Tolérance de ±2 s
        $delta = abs($sec - $offset);
        if ($delta > 2 && $delta < ($interval - 2)) {
            return false;
        }

        // Fichier d’état anti-double-run
        $cache_file = "/tmp/pmacontrol_last_run_{$file_key}_{$id_mysql_server}";
        $fp = fopen($cache_file, 'c+');
        if ($fp === false) {
            $last_minute_run = null;
        } else {
            flock($fp, LOCK_EX);
            $last_minute_run = trim(stream_get_contents($fp));
        }

        if ($last_minute_run == $minute) {
            if ($fp !== false) {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
            return false;
        }

        if ($fp !== false) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, (string)$minute);
            flock($fp, LOCK_UN);
            fclose($fp);
        }

        $callback($id_mysql_server);
        return true;
    }




}


/*
=> get exemple of Query

SELECT 
  `CURRENT_SCHEMA`, 
  `DIGEST`, 
  `SQL_TEXT` 
FROM 
  `performance_schema`.`events_statements_history` 
WHERE 
  `DIGEST` IS NOT NULL 
  AND `CURRENT_SCHEMA` IS NOT NULL 
GROUP BY 
  `CURRENT_SCHEMA`, 
  `DIGEST`, 
  `SQL_TEXT`


*/