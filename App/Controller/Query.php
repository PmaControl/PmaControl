<?php

declare(ticks=1);

namespace App\Controller;

use App\Library\Extraction;
use App\Library\Extraction2;
use App\Library\Graphviz;
use App\Library\MysqlVersion;
use App\Library\Security\Identifier;
use App\Library\Security\PositiveIntegerSelection;
use App\Library\Sql\QueryGraphDotBuilder;
use App\Library\Sql\QueryGraphExtractor;
use \Glial\Synapse\Controller;
use \App\Library\Mysql;
use \App\Library\Debug;
use \Glial\Cli\SetTimeLimit;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for query workflows.
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
class Query extends Controller {

    const TABLE_NAME = 'tmp_setdefault';
    const TABLE_SCHEMA = 'dba';
    const LOG_FILE = TMP . "log/query.log";
    private const TEXT_BLOB_DEFAULT_MIN_VERSION = '10.2';
    private const WORKER_STATE_PENDING = -1;
    private const WORKER_STATE_RUNNING = 0;
    private const WORKER_STATE_DONE = 1;
    private const WORKER_STATE_KILLED = 2;
    private const WORKER_STATE_REJECTED = 3;

            // ajouter tout mot-clé à exclure
/**
 * Stores `$exceptions` for exceptions.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    static $exceptions = ['ALL','AND','AS','CAST','COALESCE','COUNT', 'CURRENT_USER','DATE','DATE_ADD','DATE_SUB','DECIMAL','FROM',
    'GROUP_CONCAT','IF','IN','JOIN','NOW', 'MAX', 'MIN', 'PARTITION',
        'PASSWORD','SCHEMA','SUBSTRING','SUM','TIMESTAMPDIFF','UNION','USING','VALUES','WARNINGS', 'WHERE']; 


/**
 * Retrieve query state through `getFielsWithoutDefault`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @param array<int|string,mixed> $databases Input value for `databases`.
 * @phpstan-param array<int|string,mixed> $databases
 * @psalm-param array<int|string,mixed> $databases
 * @return void Returned value for getFielsWithoutDefault.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getFielsWithoutDefault()
 * @example /fr/query/getFielsWithoutDefault
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getFielsWithoutDefault($id_mysql_server, $databases = "") {
        /*
         * If a field is NULLABLE we will not get it there.
         * 
         */

        $db = Mysql::getDbLink($id_mysql_server);
        $sql = <<<SQL
            SELECT
                c.TABLE_SCHEMA as db_name,
                c.TABLE_NAME as table_name,
                c.COLUMN_NAME as column_name,
                c.DATA_TYPE as data_type,
                c.COLUMN_TYPE as data_type2
            FROM information_schema.columns c
            WHERE
                NOT EXISTS(
                    SELECT 1 FROM information_schema.tables t
                    WHERE
                        t.TABLE_SCHEMA = c.TABLE_SCHEMA
                        AND t.TABLE_NAME = c.TABLE_NAME
                        AND t.TABLE_TYPE <> 'BASE TABLE'
                )
                AND c.TABLE_SCHEMA NOT IN ('information_schema', 'sys', 'performance_schema','mysql')
                AND c.COLUMN_DEFAULT IS NULL
                AND c.IS_NULLABLE = "NO"
                AND c.EXTRA <> 'auto_increment'
SQL;

        if (!empty($databases) && $databases != "ALL") {

            $dbs = explode(",", $databases);
            $sql .= " AND c.TABLE_SCHEMA IN ('" . implode("','", $dbs) . "') ";
        }

        $sql .= ";";

        Debug::sql($sql);
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server, __METHOD__);

        while ($ob = $db->sql_fetch_object($res)) {
            yield $ob;
        }
    }

/**
 * Retrieve query state through `getDefaultValueByType`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $type Input value for `type`.
 * @phpstan-param mixed $type
 * @psalm-param mixed $type
 * @param mixed $typeExtra Input value for `typeExtra`.
 * @phpstan-param mixed $typeExtra
 * @psalm-param mixed $typeExtra
 * @return mixed Returned value for getDefaultValueByType.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getDefaultValueByType()
 * @example /fr/query/getDefaultValueByType
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getDefaultValueByType($type, $typeExtra = '') {
        // All default values below have been tested in current engine (DEV environment)
        // They are default values forced by the current engine (DEV environment)

        switch ($type) {
            case 'year':
                return '0000';
            case 'time':
                return '00:00:00';
            case 'date':
                return '0000-00-00';
            case 'datetime':
                return '0000-00-00 00:00:00';
            case 'double':
            case 'int':
            case 'float':
            case 'smallint':
            case 'tinyint':
            case 'mediumint':
            case 'bigint':
            case 'decimal':
                return 0;
            case 'varchar':
            case 'longtext':
            case 'mediumtext':
            case 'tinytext':
            case 'text':
            case 'char':
                return '';
            case 'enum':
                Debug::debug($type, "type");
                Debug::debug($typeExtra, "typeExtra");
                $matches = "";
                if (!preg_match('/^enum\((.*)\)$/', $typeExtra, $matches)) {

                    throw new \Exception(sprintf('Could not retrieve enum list from: "%s"', (string) $typeExtra));
                }
                if (false === ($enum = preg_split('/,\s?/', $matches[1]))) {
                    throw new \Exception(sprintf('Could not retrieve enum items from: "%s"', $matches[1]));
                }

                return trim($enum[0], "'");
            case 'set':
                return ''; // This is the behavior in current engine even when '' is not part of list
            case 'blob':
            case 'mediumblob':
            case 'bit':
            case 'varbinary':
            case 'binary':
                return '';
            default:
                throw new \Exception(sprintf(
                                'Encountered a type which is not referenced: "%s"', (string) $type
                ));
        }
    }

    public static function supportsTextBlobDefaults(?string $version): bool
    {
        return MysqlVersion::atLeast($version, self::TEXT_BLOB_DEFAULT_MIN_VERSION);
    }

    public static function evaluateRunQueryRequest(array $param, bool $isCli): array
    {
        if ($isCli === false) {
            return self::runQueryOutcome(403, 'Query runner is CLI-only.');
        }

        $idMysqlServer = PositiveIntegerSelection::normalizeSingle($param[0] ?? null);
        $jobId = PositiveIntegerSelection::normalizeSingle($param[1] ?? null);
        $runNumber = PositiveIntegerSelection::normalizeSingle($param[2] ?? null);

        if ($idMysqlServer === null || $jobId === null || $runNumber === null) {
            return self::runQueryOutcome(
                400,
                'Usage: php App/Webroot/index.php Query runQuery <id_mysql_server> <job_id> <run_number>'
            );
        }

        return self::runQueryOutcome(200, '', [
            'id_mysql_server' => $idMysqlServer,
            'job_id' => $jobId,
            'run_number' => $runNumber,
        ]);
    }

    public static function isSetDefaultWorkerQueryAllowed(string $query): bool
    {
        $query = trim($query);
        if ($query === '' || preg_match('/(?:--|#|\/\*)/', $query) === 1) {
            return false;
        }

        $identifier = '`[^`\\x00]{1,64}`';
        $numericDefault = '-?\d+(?:\.\d+)?';
        $quotedDefault = "'[^'\\\\;\\x00]*'";
        $pattern = '/^ALTER TABLE ' . $identifier . '\.' . $identifier
            . ' ALTER COLUMN ' . $identifier
            . ' SET DEFAULT (?:' . $numericDefault . '|' . $quotedDefault . ');$/';

        return preg_match($pattern, $query) === 1;
    }

    public static function buildRunQueryCommand(int $idMysqlServer, int $jobId, int $runNumber): array
    {
        $controller = substr(__CLASS__, strrpos(__CLASS__, '\\') + 1);
        $index = defined('GLIAL_INDEX') ? GLIAL_INDEX : 'App/Webroot/index.php';

        return [
            PHP_BINARY,
            $index,
            $controller,
            'runQuery',
            (string)$idMysqlServer,
            (string)$jobId,
            (string)$runNumber,
            '--debug',
        ];
    }

    private static function runQueryOutcome(int $status, string $body, ?array $payload = null): array
    {
        return ['status' => $status, 'body' => $body, 'payload' => $payload];
    }

    /**
     * setDefault
     *
     * @param string $id_mysql_server
     * @param string $list_databases (coma separated or ALL for all databases, if omited same as ALL)
     * @return array
     * 
     * @example ./glial Query setDefault 1 test1,test2
     * 
     * 1 => id of the server
     * test1 => database (coma separated), if all databases remove this paramters or set it to ALL
     * 
     */
    public function setDefault($param) {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $list_databases = $param[1] ?? "ALL"; // separated by coma
        $fields = $this->getFielsWithoutDefault($id_mysql_server, $list_databases);

        $db = Mysql::getDbLink($id_mysql_server);

        $default = array();

        foreach ($fields as $field) {
            Debug::debug($field, "field");
            // remove default value for blob and text : https://mariadb.com/kb/en/blob/
            if (in_array($field->data_type, array('tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob'))) {
                if (!self::supportsTextBlobDefaults($db->getVersion())) {
                    continue;
                }
            }

            $default_value = $this->getDefaultValueByType($field->data_type, $field->data_type2);

            if (in_array($field->data_type, array("int", "double", "float", "smallint", "tinyint", "mediumint", "bigint", "decimal"))) {
                $quote = "";
            } else {
                $quote = "'";
            }
            //echo "--" . $field->data_type . "\n";
            $alter = "ALTER TABLE `" . $field->db_name . "`.`" . $field->table_name . "` ALTER COLUMN `" . $field->column_name . "` SET DEFAULT " . $quote . $default_value . $quote . ";";
            $default[] = $alter;
            echo $alter . "\n";
        }
        echo "--Total : " . count($default) . "\n";

        return $default;
    }

/**
 * Handle query state through `dropDefault`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for dropDefault.
 * @phpstan-return void
 * @psalm-return void
 * @see self::dropDefault()
 * @example /fr/query/dropDefault
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function dropDefault($param) {

        Debug::parseDebug($param);
        $id_mysql_server = $param[0];
        $list_databases = $param[1] ?? "ALL"; // separated by coma
        $fields = $this->getFielsWithoutDefault($id_mysql_server, $list_databases);

        $db = Mysql::getDbLink($id_mysql_server);

        foreach ($fields as $field) {
            Debug::debug($field, "field");
            // remove default value for blob and text : https://mariadb.com/kb/en/blob/
            if (in_array($field->data_type, array('tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob'))) {
                if (!self::supportsTextBlobDefaults($db->getVersion())) {
                    continue;
                }
            }

            echo "ALTER TABLE `" . $field->db_name . "`.`" . $field->table_name . "` ALTER COLUMN `" . $field->column_name . "` DROP DEFAULT;\n";
        }
    }

/**
 * Handle query state through `runSetDefault`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for runSetDefault.
 * @phpstan-return void
 * @psalm-return void
 * @see self::runSetDefault()
 * @example /fr/query/runSetDefault
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function runSetDefault($param) {

        Debug::parseDebug($param);
        $id_mysql_server = PositiveIntegerSelection::normalizeSingle($param[0] ?? null);
        if ($id_mysql_server === null) {
            throw new \InvalidArgumentException('Invalid id_mysql_server.');
        }
        $param[0] = $id_mysql_server;

        do {
            $defaults = $this->setDefault($param);

            pcntl_signal(SIGTERM, array($this, 'sigHandler')); //
            pcntl_signal(SIGHUP, array($this, 'sigHandler'));
            pcntl_signal(SIGUSR1, array($this, 'sigHandler')); // active / desactive debug
            pcntl_signal(SIGUSR2, array($this, 'sigHandler')); // rechargement de la configuration ?

            $run_number = (int)$this->getMaxRun($param);
            if ($run_number <= 0) {
                throw new \UnexpectedValueException('Invalid run_number.');
            }
            Debug::debug($run_number, "run_number");

            foreach ($defaults as $default) {

                //begin if MariaDB > 10.1.2
                //$query = "SET STATEMENT max_statement_time=1 FOR " . $default;
                //echo Date("Y-m-d H:i:s") . " " . $query . "\n";
                //end if MariaDB > 10.1.2

                $db = Mysql::getDbLink($id_mysql_server);

                Debug::sql($default);
                $job_id = $this->queueSetDefaultJob($db, $id_mysql_server, $run_number, $default);
                $worker = $this->startRunQueryWorker($id_mysql_server, $job_id, $run_number);
                $process = $worker['process'];

                try {
                    do {

                        usleep(100000);

                        $sql = "SELECT id, thread_id, state, TIME_TO_SEC(timediff (now(),`date`)) as sec FROM `"
                            . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE state IN ("
                            . self::WORKER_STATE_PENDING . "," . self::WORKER_STATE_RUNNING . ") and id_mysql_server="
                            . $id_mysql_server . " and run_number=" . $run_number . " and id=" . $job_id;
                        $res = $db->sql_query($sql);

                        $num_rows = intval($db->sql_num_rows($res));
                        echo $num_rows . " ";
                        //Debug::debug($num_rows, 'num_rows');

                        while ($ob = $db->sql_fetch_object($res)) {
                            if ((int)$ob->sec > 10) {
                                $sql2 = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME
                                    . "`  SET `state`=" . self::WORKER_STATE_KILLED . " WHERE id=" . (int)$ob->id
                                    . " and id_mysql_server=" . $id_mysql_server;
                                $db->sql_query($sql2);

                                //kill mysql process
                                if ((int)$ob->state === self::WORKER_STATE_RUNNING && (int)$ob->thread_id > 0) {
                                    $sql3 = "KILL " . (int)$ob->thread_id . ";";
                                    $db->sql_query($sql3);
                                }

                                //kill php process
                                $this->terminateWorkerProcess($process);

                                $num_rows = 0;
                            }
                        }
                    } while ($num_rows !== 0);
                } finally {
                    if (is_resource($process)) {
                        proc_close($process);
                    }
                }
                
                echo "\n";
            }
            
            
            $db = Mysql::getDbLink($id_mysql_server);
            

            $sql4 = "SELECT count(1) as cpt FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME
                . "` WHERE `state`=" . self::WORKER_STATE_KILLED . " and run_number=" . $run_number
                . " and `id_mysql_server`=" . $id_mysql_server;
            Debug::sql($sql4);

            $cpt = 0;
            $res4 = $db->sql_query($sql4);
            while ($ob4 = $db->sql_fetch_object($res4)) {
                $cpt = $ob4->cpt;
            }
        } while ($cpt > 0);
    }

    private function queueSetDefaultJob($db, int $idMysqlServer, int $runNumber, string $query): int
    {
        if (!self::isSetDefaultWorkerQueryAllowed($query)) {
            throw new \UnexpectedValueException('Rejected set-default worker query.');
        }

        $sql = "INSERT INTO `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME
            . "` (`id_mysql_server`,`run_number`,`date`,`query`,`thread_id`,`state`) VALUES ("
            . $idMysqlServer . "," . $runNumber . ", '" . date("Y-m-d H:i:s") . "','"
            . $db->sql_real_escape_string($query) . "',0," . self::WORKER_STATE_PENDING . ")";
        $db->sql_query($sql);

        return (int)$db->sql_insert_id();
    }

    private function startRunQueryWorker(int $idMysqlServer, int $jobId, int $runNumber): array
    {
        $logDirectory = dirname(self::LOG_FILE);
        if (!is_dir($logDirectory)) {
            mkdir($logDirectory, 0775, true);
        }

        $descriptorSpec = [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', self::LOG_FILE, 'a'],
            2 => ['file', self::LOG_FILE, 'a'],
        ];

        $process = proc_open(
            self::buildRunQueryCommand($idMysqlServer, $jobId, $runNumber),
            $descriptorSpec,
            $pipes,
            null,
            null,
            ['bypass_shell' => true]
        );

        if (!is_resource($process)) {
            throw new \RuntimeException('Unable to start Query runQuery worker.');
        }

        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }

        $status = proc_get_status($process);
        $pid = (int)($status['pid'] ?? 0);
        if ($pid <= 0) {
            proc_close($process);
            throw new \RuntimeException('Unable to retrieve Query runQuery worker PID.');
        }

        return ['process' => $process, 'pid' => $pid];
    }

    private function terminateWorkerProcess($process): void
    {
        if (is_resource($process)) {
            proc_terminate($process);
        }
    }

/**
 * Handle query state through `runQuery`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for runQuery.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::runQuery()
 * @example /fr/query/runQuery
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function runQuery($param) {
        Debug::parseDebug($param);
        $this->view = false;
        $this->layout_name = false;

        $request = self::evaluateRunQueryRequest($param, defined('IS_CLI') && IS_CLI === true);
        if ($request['status'] !== 200) {
            if (!defined('IS_CLI') || IS_CLI !== true) {
                http_response_code($request['status']);
            }
            echo $request['body'] . "\n";
            return;
        }

        $payload = $request['payload'];
        $id_mysql_server = (int)$payload['id_mysql_server'];
        $job_id = (int)$payload['job_id'];
        $run_number = (int)$payload['run_number'];

        $db = Mysql::getDbLink($id_mysql_server);

        // to be sure no other process working
        $sql3 = "SELECT count(1) as cpt FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME
            . "` WHERE state=" . self::WORKER_STATE_RUNNING . " AND id_mysql_server=" . $id_mysql_server
            . " AND id <> " . $job_id;
        $res3 = $db->sql_query($sql3);
        while ($ob3 = $db->sql_fetch_object($res3)) {
            if ($ob3->cpt > 0) {
                $sql = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME
                    . "` SET `state`=" . self::WORKER_STATE_KILLED . " WHERE id=" . $job_id
                    . " AND id_mysql_server=" . $id_mysql_server . " AND run_number=" . $run_number;
                $db->sql_query($sql);
                $db->sql_close();
                throw new \Exception("One query already working to prevent any problem we kill this one");
            }
        }

        $query = null;
        $sql = "SELECT `query` FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE id=" . $job_id
            . " AND id_mysql_server=" . $id_mysql_server . " AND run_number=" . $run_number
            . " AND state=" . self::WORKER_STATE_PENDING . " LIMIT 1";
        $res = $db->sql_query($sql);
        while ($ob = $db->sql_fetch_object($res)) {
            $query = (string)$ob->query;
        }

        if ($query === null) {
            $db->sql_close();
            throw new \RuntimeException('No pending set-default query job found.');
        }

        if (!self::isSetDefaultWorkerQueryAllowed($query)) {
            $sql = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME
                . "` SET `state`=" . self::WORKER_STATE_REJECTED . " WHERE id=" . $job_id
                . " AND id_mysql_server=" . $id_mysql_server . " AND run_number=" . $run_number;
            $db->sql_query($sql);
            $db->sql_close();
            throw new \UnexpectedValueException('Rejected set-default worker query.');
        }

        $thread_id = $db->sql_thread_id();
        $sql = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` SET `date`='"
            . date("Y-m-d H:i:s") . "',`thread_id`=" . $thread_id . ",`state`=" . self::WORKER_STATE_RUNNING
            . " WHERE id=" . $job_id . " AND id_mysql_server=" . $id_mysql_server . " AND run_number="
            . $run_number . " AND state=" . self::WORKER_STATE_PENDING;
        $db->sql_query($sql);
        if ((int)$db->sql_affected_rows() !== 1) {
            $db->sql_close();
            throw new \RuntimeException('Unable to reserve set-default query job.');
        }

        Debug::debug($thread_id, "THREAD_ID");
        Debug::sql($query);

        try {
            $db->sql_query($query);

            $sql2 = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`  SET `state`="
                . self::WORKER_STATE_DONE . " WHERE id=" . $job_id . " AND id_mysql_server=" . $id_mysql_server;
            $db->sql_query($sql2);
        } catch (\Throwable $exception) {
            $sql2 = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`  SET `state`="
                . self::WORKER_STATE_KILLED . " WHERE id=" . $job_id . " AND id_mysql_server=" . $id_mysql_server;
            $db->sql_query($sql2);
            throw $exception;
        } finally {
            $db->sql_close();
        }
    }

/**
 * Create query state through `createWorkTable`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for createWorkTable.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::createWorkTable()
 * @example /fr/query/createWorkTable
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function createWorkTable($param) {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);

        $sql4 = "SELECT count(1) as cpt FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . self::TABLE_SCHEMA . "'";
        Debug::sql($sql4);
        $res4 = $db->sql_query($sql4);

        $db_exist = false;
        while ($ob4 = $db->sql_fetch_object($res4)) {
            $db_exist = $ob4->cpt;
        }

        if ($db_exist === "0") {

            $sql = "CREATE DATABASE IF NOT EXISTS `" . self::TABLE_SCHEMA . "`;";
            Debug::sql($sql);
            $db->sql_query($sql);
        }

        $sql2 = "select count(1) as cpt FROM information_schema.tables where table_name = '" . self::TABLE_NAME . "' and table_schema='" . self::TABLE_SCHEMA . "';";
        Debug::sql($sql2);
        $res2 = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql2, $id_mysql_server, __METHOD__);

        while ($ob2 = $db->sql_fetch_object($res2)) {
            if ($ob2->cpt > 0) {
                return true;
                //throw new \Exception("One treatement already working, wait it's finish or delete table with ./glial " . substr(__CLASS__, strrpos(__CLASS__, '\\') + 1) . " deleteWorkTable " . $id_mysql_server);
            }
        }

        $sql3 = '
        CREATE TABLE `' . self::TABLE_SCHEMA . '`.`' . self::TABLE_NAME . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `run_number` int(11) NOT NULL DEFAULT 0,
  `date` datetime NOT NULL,
  `query` text NOT NULL,
  `thread_id` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
        Debug::debug($sql3);
        $db->sql_query($sql3);
    }

/**
 * Delete query state through `deleteWorkTable`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for deleteWorkTable.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::deleteWorkTable()
 * @example /fr/query/deleteWorkTable
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function deleteWorkTable($param) {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);
        $sql = "select count(1) as cpt FROM information_schema.tables where table_name = '" . self::TABLE_NAME . "' and table_schema='" . self::TABLE_SCHEMA . "';";
        Debug::debug($sql);
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server, __METHOD__);

        while ($ob = $db->sql_fetch_object($res)) {

            if ($ob->cpt === "1") {
                $sql = "DROP TABLE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`";
                Debug::sql($sql);
                $db->sql_query($sql);
            } else {
                throw new \Exception("We cannot found the table to drop '`" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`'");
            }
        }
    }

// gestionnaire de signaux système
/**
 * Handle query state through `sigHandler`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $signo Input value for `signo`.
 * @phpstan-param mixed $signo
 * @psalm-param mixed $signo
 * @return void Returned value for sigHandler.
 * @phpstan-return void
 * @psalm-return void
 * @see self::sigHandler()
 * @example /fr/query/sigHandler
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function sigHandler($signo) {
        switch ($signo) {
            case SIGTERM:
                echo "Reçu le signe SIGTERM...\n";
                
                //exit;
                break;

            case SIGUSR1:
                break;

            case SIGUSR2:
                break;

            case SIGHUP:

                // gestion du redémarrage
                //ne marche pas au second run pourquoi ?
                echo "Reçu le signe SIGHUP...\n";
                //$this->sighup();

                break;

            default:

                echo "RECU LE SIGNAL : " . $signo;
// gestion des autres signaux
        }
    }

/**
 * Retrieve query state through `getMaxRun`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getMaxRun.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getMaxRun()
 * @example /fr/query/getMaxRun
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getMaxRun($param) {

        $id_mysql_server = $param[0];
        $db = Mysql::getDbLink($id_mysql_server);
        $this->createWorkTable($param);

        $sql = "SELECT max(run_number) as netxtrun FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE id_mysql_server=" . $id_mysql_server;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $current_run = $ob->netxtrun + 1;
        }

        Debug::debug($current_run, "current_run");
        return $current_run;
    }



/**
 * Handle query state through `byDigest`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for byDigest.
 * @phpstan-return void
 * @psalm-return void
 * @see self::byDigest()
 * @example /fr/query/byDigest
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function byDigest($param)
    {
        Debug::parseDebug($param);

        $digest = $param[0];
        $id_mysql_server = $param[1];
        $schema_name = $param[2];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * 
        FROM mysql_query a
        INNER JOIN ts_mysql_query b ON a.id = b.id_mysql_query 
        INNER JOIN mysql_database c ON c.id = b.id_mysql_database
        WHERE a.digest_mariadb = '".$digest."'
        AND c.schema_name = '".$schema_name."'
        AND b.id_mysql_server = ".$id_mysql_server." LIMIT 100";
        
        $res = $db->sql_query($sql);

        $data['query'] = [];
        while ($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC)) {
            $data['query'][] = $arr;
        }

        Debug::debug($data);

        $this->set('data', $data);
    }




/**
 * Handle query state through `extractTablesFromSQL`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $sql Input value for `sql`.
 * @phpstan-param string $sql
 * @psalm-param string $sql
 * @return array Returned value for extractTablesFromSQL.
 * @phpstan-return array
 * @psalm-return array
 * @see self::extractTablesFromSQL()
 * @example /fr/query/extractTablesFromSQL
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function extractTablesFromSQL(string $sql): array {
        return QueryGraphExtractor::extractTablesFromSql($sql);
    }



/**
 * Handle query state through `extractTablesAndAliases`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $sql Input value for `sql`.
 * @phpstan-param string $sql
 * @psalm-param string $sql
 * @return array Returned value for extractTablesAndAliases.
 * @phpstan-return array
 * @psalm-return array
 * @see self::extractTablesAndAliases()
 * @example /fr/query/extractTablesAndAliases
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function extractTablesAndAliases(string $sql): array {
        return QueryGraphExtractor::extractTablesAndAliases($sql);
    }


/**
 * Handle query state through `extractTablesWithOffsets`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $sql Input value for `sql`.
 * @phpstan-param string $sql
 * @psalm-param string $sql
 * @return array Returned value for extractTablesWithOffsets.
 * @phpstan-return array
 * @psalm-return array
 * @see self::extractTablesWithOffsets()
 * @example /fr/query/extractTablesWithOffsets
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function extractTablesWithOffsets(string $sql): array {
        return QueryGraphExtractor::extractTablesWithOffsets($sql);
    }

/**
 * Handle query state through `extract`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for extract.
 * @phpstan-return void
 * @psalm-return void
 * @see self::extract()
 * @example /fr/query/extract
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function extract($param)
    {
        Debug::parseDebug($param);

        $sql = "( SELECT ? AS ? , `a` . `id_mysql_server` , `a` . `id_ts_variable` , ? AS `connection_name` , `a` . `date` , `a` . `value` 
        FROM `ts_value_general_text` PARTITION ( `p739740` ) `a` 
        WHERE `id_ts_variable` IN (...) AND `a` . `id_mysql_server` = ? AND `a` . `date` = ? ) 
        UNION ALL ( SELECT ? AS ? , `a` . `id_mysql_server` , `a` . `id_ts_variable` , ? AS `connection_name` , `a` . `date` , `a` . `value` 
        FROM `ts_value_general_double` PARTITION ( `p739740` ) `a` 
        WHERE `id_ts_variable` IN (...) AND `a` . `id_mysql_server` = ? AND `a` . `date` = ? ) 
        UNION ALL ( SELECT ? AS ? , `a` . `id_mysql_server` , `a` . `id_ts_variable` , ? AS `connection_name` , `a` . `date` , `a` . `value` 
        FROM `pmacontrol`.`ts_value_general_int` PARTITION ( `p739740` ) `a` WHERE `id_ts_variable` IN (...) AND `a` . `id_mysql_server` = ? AND `a` . `date` = ? ) ";

        $sql = "SELECT SQL_CALC_FOUND_ROWS asReference,asDateHeureDebut,
                fichiers_entree.afReference AS afReferenceEntree,
                fichiers_entree.afNomFichier AS afNomFichierEntree,
                fichiers_entree.afErreur AS afErreurEntree,
                fichiers_traduits.afReference AS afReferenceTraduit,
                fichiers_traduits.afNomFichier AS afNomFichierTraduit,
                asTest,
            IF(msg_sortie.amTypeDocument='ORDERS',msg_sortie.amNumeroDocument,
                REGEXP_REPLACE(
                    REGEXP_SUBSTR(msg_entree.amExtraEnveloppes, '\"commande_numero\";s:[0-9]+:\"[a-zA-Z0-9 _/-]+'),
                    '\"commande_numero\";s:[0-9]+:\"',
                    ''
                )
            )  as numero_commande_entree,
            IF(mdLibelleTracking='',mdLibelleFR,mdLibelleTracking) as nom_module,
            configSens.acValeur as sens,
            IF(configSens.acValeur='Emission',msg_sortie.amNumeroDocument,msg_entree.amNumeroDocument) as amNumeroMessage,
            IF(configSens.acValeur='Emission',msg_sortie.amTypeDocument,msg_entree.amTypeDocument) as amTypeMessage,
            IF(configSens.acValeur='Emission',emetteur_sortie.soRaisonSociale,emetteur_entree.soRaisonSociale) as emetteurMessageRaisonSociale,
            IF(configSens.acValeur='Emission',msg_sortie.amCodeEanSocieteEmetteur,msg_entree.amCodeEanSocieteEmetteur) as emetteurMessage,
            IF(configSens.acValeur='Emission',destinataire_sortie.soRaisonSociale,destinataire_entree.soRaisonSociale) as destinataireMessageRaisonSociale,
            IF(configSens.acValeur='Emission',msg_sortie.amCodeEanSocieteDestinataire,msg_entree.amCodeEanSocieteDestinataire) as destinataireMessage,
            IF(configSens.acValeur='Emission',msg_sortie.amDateHeureDocument,msg_entree.amDateHeureDocument) as dateMessage,
            IF(configSens.acValeur='Emission',msg_sortie.amCodeEanSocieteFournisseur,msg_entree.amCodeEanSocieteFournisseur) as glnFournisseur,
            IF(configSens.acValeur='Emission',msg_sortie.amCodeEanSocieteClient,msg_entree.amCodeEanSocieteClient) as glnClient,
            IF(configSens.acValeur='Emission',fournisseur_sortie.soRaisonSociale,fournisseur_entree.soRaisonSociale) as libelleFournisseur,
            IF(configSens.acValeur='Emission',client_sortie.soRaisonSociale,client_entree.soRaisonSociale) as libelleClient 
            FROM autosessions
            LEFT JOIN automodules ON asRefModule=mdReference
            LEFT JOIN autoconfiguration as `configSens`  ON asRefModule=acRefModule and acParametre='Mode transmission'
                INNER JOIN autofichiers AS fichiers_entree ON
                    fichiers_entree.afRefSession = asReference
                    AND fichiers_entree.afEntreeSortie = ''
                LEFT JOIN autofichiersliens AS lientrad ON
                    lientrad.flRefFichierAvant = fichiers_entree.afReference
                    AND lientrad.flTypeLien = 'traduit'
                LEFT JOIN autofichiers AS fichiers_traduits ON
                    lientrad.flRefFichierApres = fichiers_traduits.afReference
                    AND fichiers_traduits.afRefSession=asReference
                    AND fichiers_traduits.afEntreeSortie='traduits'
                LEFT JOIN automessagesliens AS lienmsg_entree ON
                    lienmsg_entree.alRefFichier = fichiers_entree.afReference
                LEFT JOIN automessages AS msg_entree ON msg_entree.amReference = lienmsg_entree.alRefMessage
                LEFT JOIN automessagesliens AS lienmsg_sortie ON
                    lienmsg_sortie.alRefFichier = fichiers_traduits.afReference
                LEFT JOIN automessages AS msg_sortie ON msg_sortie.amReference = lienmsg_sortie.alRefMessage
            LEFT JOIN societes as emetteur_entree ON emetteur_entree.soCodeEan = msg_entree.amCodeEanSocieteEmetteur
            LEFT JOIN societes as destinataire_entree ON destinataire_entree.soCodeEan = msg_entree.amCodeEanSocieteDestinataire
            LEFT JOIN societes as emetteur_sortie ON emetteur_sortie.soCodeEan = msg_sortie.amCodeEanSocieteEmetteur
            LEFT JOIN societes as destinataire_sortie ON destinataire_sortie.soCodeEan = msg_sortie.amCodeEanSocieteDestinataire
            LEFT JOIN societes as fournisseur_entree ON fournisseur_entree.soCodeEan = msg_entree.amCodeEanSocieteFournisseur
            LEFT JOIN societes as client_entree ON client_entree.soCodeEan = msg_entree.amCodeEanSocieteClient
            LEFT JOIN societes as fournisseur_sortie ON fournisseur_sortie.soCodeEan = msg_sortie.amCodeEanSocieteFournisseur
            LEFT JOIN societes as client_sortie ON client_sortie.soCodeEan = msg_sortie.amCodeEanSocieteClient
        
            WHERE
                 mdRefSociete=11 AND asDateHeureDebut BETWEEN '2025-03-03 00:00:00' AND '2025-06-03 23:59:59' AND IF(configSens.acValeur='Emission',msg_sortie.amTypeDocument,msg_entree.amTypeDocument) = 'INVOIC' AND IF(configSens.acValeur='Emission',msg_sortie.amNumeroDocument,msg_entree.amNumeroDocument) LIKE '%251001835%'
                GROUP BY msg_entree.amReference,
            msg_sortie.amReference,
            fichiers_entree.afReference,
            fichiers_traduits.afReference ORDER BY asDateHeureDebut
                LIMIT 0,50;";

        $gg = $this->extractTablesWithOffsets($sql);

        Debug::debug($gg);

    }

    /* extraire les tables avec les alias d'une requete SQL */






        /**
     * Approximation PHP de STATEMENT_DIGEST_TEXT() et STATEMENT_DIGEST() pour MariaDB 10.6+
     *
     * Limitations / hypothèses :
     *  - by default double quotes are treated as string delimiters (si tu utilises ANSI_QUOTES, passe $ansi_quotes = true)
     *  - l'algorithme serveur exact (tokenization fine) peut différer : ceci est une normalisation basée sur regexp/token heuristique
     */

    public static function normalize_sql_for_digest(string $sql, bool $ansi_quotes = false): string {
        // 1) remove C-style comments /* ... */
        $sql = preg_replace('#/\*.*?\*/#s', ' ', $sql);

        // 2) remove -- and # line comments
        $sql = preg_replace('/--[ \t]*[^\r\n]*/', ' ', $sql);
        $sql = preg_replace('/#[^\r\n]*/', ' ', $sql);

        // 3) collapse strings (single-quoted)
        $sql = preg_replace("/'(?:\\\\.|[^\\\\'])*'/s", '?', $sql);

        // 4) collapse double-quoted strings UNLESS ansi_quotes mode (then double quotes are identifiers -> keep them)
        if (!$ansi_quotes) {
            $sql = preg_replace('/"(?:\\\\.|[^\\\\"])*"/s', '?', $sql);
        }

        // 5) collapse bit literals b'0101' and hex 0xABC... and binary x'..'
        $sql = preg_replace("/b'[01]+'/i", '?', $sql);
        $sql = preg_replace("/0x[0-9A-Fa-f]+/i", '?', $sql);
        $sql = preg_replace("/x'(?:\\\\.|[^\\\\'])*'/i", '?', $sql);

        // 6) replace numeric literals (integers, floats, exponentials)
        $sql = preg_replace('/\b[0-9]+(?:\.[0-9]+)?(?:[eE][+-]?[0-9]+)?\b/', '?', $sql);

        // 7) compact IN-lists of literals to (...) -> MariaDB digest_text tends to show IN (...)
        //    Detect IN (literal, literal, ...) and replace content by '...'
        $sql = preg_replace_callback('/\bIN\s*\(\s*(?:\?|\'.*?\'|".*?"|[0-9A-Fa-fxX]+)(?:\s*,\s*(?:\?|\'.*?\'|".*?"|[0-9A-Fa-fxX]+))*\s*\)/is',
            function($m){ return 'IN (...)'; }, $sql);

        // 8) remove redundant whitespace, keep single space, trim
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = trim($sql);

        // 9) optionally; ensure spacing around parentheses is normalized (aesthetic)
        $sql = preg_replace('/\s*\(\s*/', '(', $sql);
        $sql = preg_replace('/\s*\)\s*/', ') ', $sql);
        $sql = trim($sql);

        return $sql;
    }



/**
 * Handle query state through `normalize_sql_strict`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $sql Input value for `sql`.
 * @phpstan-param string $sql
 * @psalm-param string $sql
 * @param bool $ansi_quotes Input value for `ansi_quotes`.
 * @phpstan-param bool $ansi_quotes
 * @psalm-param bool $ansi_quotes
 * @return string Returned value for normalize_sql_strict.
 * @phpstan-return string
 * @psalm-return string
 * @see self::normalize_sql_strict()
 * @example /fr/query/normalize_sql_strict
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function normalize_sql_strict(string $sql, bool $ansi_quotes = false): string {
        // 1) Supprimer tous les commentaires
        $sql = preg_replace('#/\*.*?\*/#s', ' ', $sql);
        $sql = preg_replace('/--[ \t]*[^\r\n]*/', ' ', $sql);
        $sql = preg_replace('/#[^\r\n]*/', ' ', $sql);

        // 2) Remplacer les littéraux (chaînes, nombres, hex, binaires, etc.)
        $sql = preg_replace("/'(?:\\\\.|[^\\\\'])*'/s", '?', $sql);
        if (!$ansi_quotes) {
            $sql = preg_replace('/"(?:\\\\.|[^\\\\"])*"/s', '?', $sql);
        }
        $sql = preg_replace("/b'[01]+'/i", '?', $sql);
        $sql = preg_replace("/0x[0-9A-Fa-f]+/i", '?', $sql);
        $sql = preg_replace("/x'(?:\\\\.|[^\\\\'])*'/i", '?', $sql);
        $sql = preg_replace('/\b[0-9]+(?:\.[0-9]+)?(?:[eE][+-]?[0-9]+)?\b/', '?', $sql);

        // 3) Réduire les listes IN (..)
        $sql = preg_replace_callback(
            '/\bIN\s*\(\s*(?:\?|\'.*?\'|".*?"|[0-9A-Fa-fxX]+)(?:\s*,\s*(?:\?|\'.*?\'|".*?"|[0-9A-Fa-fxX]+))*\s*\)/is',
            fn($m) => 'IN (...)',
            $sql
        );

        // 4) Espaces / casse / trim
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = trim($sql);

        return $sql;
    }

    /**
     * Retourne le texte normalisé (comme DIGEST_TEXT). 
     * $max_digest_length = valeur max utilisée pour la création (serveur a aussi max_digest_length); 
     * $perf_max_digest_length = longueur conservée en performance_schema.DIGEST_TEXT (si plus petit, truncation).
     */
    public function statement_digest_text(string $sql, int $max_digest_length = 1024, int $perf_max_digest_length = 1024, bool $ansi_quotes = false): string {
        // Normalise
        $norm = self::normalize_sql_for_digest($sql, $ansi_quotes);

        // Appliquer la limite max_digest_length (le serveur tronque à max_digest_length avant hash)
        if (mb_strlen($norm, '8bit') > $max_digest_length) {
            $norm_for_hash = mb_substr($norm, 0, $max_digest_length, '8bit');
        } else {
            $norm_for_hash = $norm;
        }

        // Le texte stocké dans performance_schema peut être plus court (performance_schema_max_digest_length)
        if (mb_strlen($norm_for_hash, '8bit') > $perf_max_digest_length) {
            return mb_substr($norm_for_hash, 0, $perf_max_digest_length, '8bit');
        }
        return $norm_for_hash;
    }

    /**
     * Retourne le DIGEST (valeur hex MD5 32 caractères) similaire à la colonne DIGEST de MariaDB.
     * IMPORTANT: on calcule le MD5 sur la forme normalisée **avant** la troncature éventuelle de performance_schema.
     */
    public static function statement_digest(string $sql, int $max_digest_length = 1024, bool $ansi_quotes = false): string {
        $norm = self::normalize_sql_for_digest($sql, $ansi_quotes);

        // Tronquer à max_digest_length car c'est sur cette version complète que le serveur calcule le hash.
        if (mb_strlen($norm, '8bit') > $max_digest_length) {
            $norm_for_hash = mb_substr($norm, 0, $max_digest_length, '8bit');
        } else {
            $norm_for_hash = $norm;
        }

        // MD5 en hex, en lowercase — correspond au format usuel de MariaDB DIGEST (HEX)
        return md5($norm_for_hash);
    }



/**
 * Handle query state through `statement_digest_text_strict`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $sql Input value for `sql`.
 * @phpstan-param string $sql
 * @psalm-param string $sql
 * @param int $max_digest_length Input value for `max_digest_length`.
 * @phpstan-param int $max_digest_length
 * @psalm-param int $max_digest_length
 * @param int $show_length Input value for `show_length`.
 * @phpstan-param int $show_length
 * @psalm-param int $show_length
 * @param bool $ansi_quotes Input value for `ansi_quotes`.
 * @phpstan-param bool $ansi_quotes
 * @psalm-param bool $ansi_quotes
 * @return string Returned value for statement_digest_text_strict.
 * @phpstan-return string
 * @psalm-return string
 * @see self::statement_digest_text_strict()
 * @example /fr/query/statement_digest_text_strict
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function statement_digest_text_strict(string $sql, int $max_digest_length = 1024, int $show_length = 1024, bool $ansi_quotes = false): string {
        $norm = self::normalize_sql_strict($sql, $ansi_quotes);
        // maintenir la version pour affichage (troncature moins stricte)
        $visible = (mb_strlen($norm, '8bit') > $show_length) ? mb_substr($norm, 0, $show_length, '8bit') : $norm;
        return $visible;
    }

/**
 * Handle query state through `statement_digest_strict`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $sql Input value for `sql`.
 * @phpstan-param string $sql
 * @psalm-param string $sql
 * @param int $max_digest_length Input value for `max_digest_length`.
 * @phpstan-param int $max_digest_length
 * @psalm-param int $max_digest_length
 * @param bool $ansi_quotes Input value for `ansi_quotes`.
 * @phpstan-param bool $ansi_quotes
 * @psalm-param bool $ansi_quotes
 * @return string Returned value for statement_digest_strict.
 * @phpstan-return string
 * @psalm-return string
 * @see self::statement_digest_strict()
 * @example /fr/query/statement_digest_strict
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function statement_digest_strict(string $sql, int $max_digest_length = 1024, bool $ansi_quotes = false): string {
        $norm = self::normalize_sql_strict($sql, $ansi_quotes);
        $forHash = (mb_strlen($norm, '8bit') > $max_digest_length) ? mb_substr($norm, 0, $max_digest_length, '8bit') : $norm;
        $forHash = trim($forHash); // connais le problème du trim manquant
        $forHash = mb_convert_encoding($forHash, 'UTF-8', 'auto');
        $forHash = preg_replace('/^\xEF\xBB\xBF/', '', $forHash); // enlever BOM
        return md5($forHash);
    }


/**
 * Handle query state through `testDigest`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for testDigest.
 * @phpstan-return void
 * @psalm-return void
 * @see self::testDigest()
 * @example /fr/query/testDigest
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function testDigest($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.DIGEST, a.SQL_TEXT, b.DIGEST_TEXT 
        FROM performance_schema.events_statements_history a 
        INNER JOIN performance_schema.events_statements_summary_by_digest b on a.DIGEST = b.DIGEST";

        $sql = "SELECT `digest` as `DIGEST`, 
        TRIM(`query`) as `SQL_TEXT`,
        TRIM(`digest_text`) as `DIGEST_TEXT`
        FROM query_sample WHERE `truncated` = 0";

        $res = $db->sql_query($sql);

        $good = 0;
        $total = 0;

        while ($ob = $db->sql_fetch_object($res))
        {
            $text3 = trim(self::digestText(trim($ob->SQL_TEXT)));

            if ($text3 == $ob->DIGEST_TEXT) {
                //echo "OK !".PHP_EOL;
                $good++;
            }
            else {
                echo "_____________________________________________________________________________________\n";
                echo "DIGEST MariaDB    : " . $ob->DIGEST.PHP_EOL;
                echo "VERSION ORIGINAL  : " . $ob->SQL_TEXT."--".PHP_EOL.PHP_EOL;
                echo "VERSION MariaDB   : " . $ob->DIGEST_TEXT."--".PHP_EOL.PHP_EOL;
                //echo "VERSION 1         : " . $generated_digest.PHP_EOL;
                //echo "VERSION 2 (strict): " . $generated_digest_strict.PHP_EOL;
                echo "VERSION 3         : " . $text3."--".PHP_EOL; 
                echo "_____________________________________________________________________________________\n";
            }

            $total++;
        }

        $percent = round($good/$total*100,2);

        echo "Taux de réussite : ".$percent."%\n";
    } 




    // new version 



    // old digest
/**
 * Handle query state through `digest2`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $query Input value for `query`.
 * @phpstan-param string $query
 * @psalm-param string $query
 * @return string Returned value for digest2.
 * @phpstan-return string
 * @psalm-return string
 * @see self::digest2()
 * @example /fr/query/digest2
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function digest2(string $query): string {
        $normalized = self::normalize($query);
        // MariaDB digest est 16 octets hex (md5-like)
        return md5($normalized);
    }

/**
 * Handle query state through `digestText`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $query Input value for `query`.
 * @phpstan-param string $query
 * @psalm-param string $query
 * @return string Returned value for digestText.
 * @phpstan-return string
 * @psalm-return string
 * @see self::digestText()
 * @example /fr/query/digestText
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function digestText(string $query): string {
        return self::normalize($query);
    }

/**
 * Handle query state through `normalize`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $query Input value for `query`.
 * @phpstan-param string $query
 * @psalm-param string $query
 * @return string Returned value for normalize.
 * @phpstan-return string
 * @psalm-return string
 * @see self::normalize()
 * @example /fr/query/normalize
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function normalize(string $query): string {

        $q = $query;

        $q = str_ireplace('/*!40003 GLOBAL*/', 'GLOBAL', $q);

                // 1. Supprimer les commentaires
        $q = preg_replace('/--.*(\r?\n|$)/', ' ', $q);
        $q = preg_replace('/#.*(\r?\n|$)/', ' ', $q);
        $q = preg_replace('/\/\*.*?\*\//s', ' ', $q);

        // 2. Remplacer littéraux par ?
        $q = preg_replace("/'(?:''|[^'])*'/", '?', $q);
        $q = preg_replace('/"(?:\\"|[^"])*"/', '?', $q);
        $q = preg_replace('/\b[0-9]+(\.[0-9]+)?\b/', '?', $q);

        $q = str_ireplace('show databases', 'SHOW SCHEMAS', $q);
        $q = str_ireplace('select database', 'SELECT SCHEMA', $q);
        $q = str_ireplace('/', ' / ', $q);
        $q = str_ireplace('+', ' + ', $q);
        $q = str_ireplace('-', ' - ', $q);
        $q = str_ireplace('*', ' * ', $q);
        
        

        // 3. Découpage
        $tokens = preg_split('/(\s+|[\(\),.=])/u', $q, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach($tokens as $key => $token) {
            if (trim($token) === "") {
                unset($tokens[$key]);
            }
        }

        $tokens = array_values($tokens);

        //Debug::debug($tokens);


        $normalizedTokens = [];
        foreach ($tokens as $key => $t) {
            if (preg_match('/^\s+$/', $t)) {
                // garder espaces
                $normalizedTokens[] = $t;
            } elseif (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $t)) {
                
                //function
                if (!empty($tokens[$key+1]) && $tokens[$key+1] === "(" && $tokens[$key-1] != "INTO")
                {
                    // case des alias + upper case (il doit y en avoir d'autre)
                    if (strtoupper($t) === "DATABASE" )
                    {
                        $normalizedTokens[] = "SCHEMA";
                    }
                    // cas des fonction mis en majuscule sinon laisser comme c'était
                    else if (in_array(strtoupper($t), self::$exceptions)){
                        $normalizedTokens[] = "".strtoupper($t)."";
                    }
                    else{
                        $normalizedTokens[] = "".$t."";
                    }
                }
                else if(!empty($tokens[$key-2]) && $tokens[$key-2] === "FORMAT" && $t === "JSON") {
                    $normalizedTokens[] = $t;
                }
                //case des mots clef suivi par un prefix
                else if (!empty($tokens[$key-1]) && $tokens[$key-1] == ".")
                {
                    $normalizedTokens[] = "`" . $t . "`";
                }
                // Identifiant ou mot-clé
                else if (self::isKeyword($t)) {
                    $normalizedTokens[] = "".strtoupper($t)."";
                } else {
                    $normalizedTokens[] = "`" . $t . "`";
                }
            } else {
                // symboles, ?, ponctuation → garder
                $normalizedTokens[] = $t;
            }
        }

        $string =  implode(' ', $normalizedTokens);
        
        $string = preg_replace('/VALUES\s*\(\s*(\?\s*(,\s*\?)*)\s*\)/i', 'VALUES (...)', $string);
        
        $string = str_replace('`! =', '` !=', $string);
        $string = str_replace('! =', '!=', $string);
        $string = str_replace('< =', '<=', $string);
        $string = str_replace('> =', '>=', $string);

        $string = str_replace('( ? )', '(?)', $string);
        $string = preg_replace('/ {2,}/', ' ', $string);
        $string = preg_replace('/, ? {2,}/', ', ?, ...', $string);
        $string = preg_replace('/(,\s*\?)(\s*,\s*\?){1,}/', ', ?, ...', $string);
        
        $string = preg_replace('/IN\s*\(\s*\?(\s*,\s*\?)+(\s*,\s*\.\.\.)?\s*\)/i', 'IN (...)', $string);;

        $string = preg_replace('/\?(\s*,\s*\?)+/', '?, ...', $string);
        //$string =  preg_replace('/@@`([^`]+)`/', '@@$1', $string);
        //$string = preg_replace('/IN\s*\(\s*\?(\s*,\s*\?)+(\s*,\s*\.\.\.)?\s*\)/i', 'IN (...)', $string);;

        //cas des VALUES avec une parenthère non fermé
        $string = preg_replace('/VALUES\s*\(\s*(\?|\{\'\?)\s*.*$/i', 'VALUES (...)', $string);

        $string = str_replace('. . .', '...', $string);
        
        $string = self::replaceAlias($string);

        $string = str_replace('DECIMAL ( ?, ... )', 'DECIMAL (...)', $string);
        

        //###################################################################################

        // only for fix mariadb

        $string = preg_replace('/@@([a-zA-Z0-9_]+)/', '@@`$1`', $string);

        $string = self::degradeMariaDB($string);

        //no other choice
        $string = str_replace(' @@`read_only` ', ' @@READ_ONLY ', $string);

         

        /*
        $string = str_replace('. PROCESSLIST', '. `PROCESSLIST`', $string);
        //$string = str_replace('WHERE `name`', 'WHERE NAME', $string);

        //$string = preg_replace('/, `name` ,/', ', NAME ,', $string);
        $string = preg_replace('/`USER`/', 'SYSTEM_USER', $string);
        $string = preg_replace('/`HOST`/', 'HOST', $string);
        $string = preg_replace('/, `port` ,/', ', PORT ,', $string);
        $string = preg_replace('/, `port` AS/', ', PORT AS', $string);
        $string = preg_replace('/`client`/', 'CLIENT', $string);
        $string = preg_replace('/(?<!\.)(?<!\. )`schema_name`/', 'SCHEMA_NAME', $string);
        //$string = preg_replace('/(?<!\.)(?<!\. )`date`/', 'DATE', $string);
        $string = preg_replace('/(?<!\.)(?<!\. )`TABLE_NAME`/', 'TABLE_NAME', $string);
        
        $string = str_replace(' DISTINCT ', ' DISTINCTROW ', $string);

        //$string = preg_replace('/(?<!\.)(?<!\. )`id`/', 'ID', $string);
        $string = str_replace('` . ID', '` . `id`', $string);


        //$string = str_replace('WHERE ID', 'WHERE `id`', $string);
        $string = preg_replace('/(?<!\.)(?<!\. )`UNSIGNED`/', 'UNSIGNED', $string);
        /**** END FIX MARIA DB */


        return $string;
    }


/**
 * Handle query state through `isKeyword`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param string $word Input value for `word`.
 * @phpstan-param string $word
 * @psalm-param string $word
 * @return bool Returned value for isKeyword.
 * @phpstan-return bool
 * @psalm-return bool
 * @see self::isKeyword()
 * @example /fr/query/isKeyword
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function isKeyword(string $word): bool {
        $keywords = [
            "SELECT","FROM","WHERE","AND","OR","NOT","AS","ON","JOIN",
            "LEFT","RIGHT","INNER","OUTER","GROUP","BY","ORDER","LIMIT",
            "OFFSET","INSERT","INTO","VALUES","UPDATE","SET","DELETE",
            "CREATE","ALTER","DROP","TABLE","DATABASE","SCHEMA","NOT",
             "SHOW", "FULL",  "PROCESSLIST", "COUNT", "TABLES", "SCHEMAS",
             "GLOBAL", "VARIABLES", "PARTITION", "STATUS", "IS",
             "NULL", "BEGIN", "COMMIT", "ENGINE", "MAX", "EXPLAIN",
             "SESSION", "COALESCE", "TIME", "DISTINCT", "ID", "UNION", 
             "UNION ALL", "ALL", "TRUNCATE", "WITH",
             "VALUE"
        ];

        $keywords = ["ACCOUNT","ACTION","ADMIN","AFTER","AGAINST","AGGREGATE","ALGORITHM","ALL","ALWAYS","ANALYSE","ANALYZE","AND",
        "ANY","AS","ASC","ASCII","ASENSITIVE","AT","AUTO_INCREMENT","AUTOEXTEND_SIZE","AVG","AVG_ROW_LENGTH","BACKUP","BEFORE","BEGIN",
        "BETWEEN","BIGINT","BINARY","BINLOG","BIT","BLOB","BLOCK","BOOL","BOOLEAN","BOTH","BTREE","BY","CALL","CASCADE","CASCADED","CASE",
        "CATALOG_NAME","CHAIN","CHANGE","CHANGED","CHANNEL","CHAR","CHARACTER","CHARSET","CHECK","CHECKSUM","CIPHER","CLASS_ORIGIN","CLIENT",
        "CLOSE","COALESCE","COLLATE","COLLATION","COLUMN","COLUMNS","COLUMN_FORMAT","COLUMN_NAME","COMMENT","COMMIT","COMMITTED","COMPACT",
        "COMPLETION","COMPRESSED","COMPRESSION","CONCURRENT","CONDITION","CONNECTION","CONSISTENT","CONSTRAINT","CONTAINS","CONTEXT",
        "CONTINUE","CONVERT","CPU","CONSTRAINT_NAME","CONSTRAINT_SCHEMA","CREATE","CROSS","CUBE","CURRENT", "CURRENT_USER","CURSOR",
        "DATABASE","DATABASES","DATA","DATE","DATETIME","DAY",
        "DEALLOCATE","DEC","DECIMAL","DECLARE","DEFAULT","DEFAULT_AUTH","DEFINED","DEFINER","DELAYED","DELAY_KEY_WRITE","DELETE","DESC",
        "DESCRIBE","DES_KEY_FILE","DETERMINISTIC","DIAGNOSTICS","DIRECTORY","DISABLE","DISCARD","DISK","DISTINCT","DISTINCTROW","DIV",
        "DO","DUMPFILE", "DUPLICATE","ELSE","ELSEIF","EMPTY","ENABLE","ENCLOSED","ENCRYPTION","END","ENGINE","ENGINES","ENUM","ERROR","ERRORS","ESCAPE",
        "ESCAPED","EVENT","EVENTS","EVERY","EXCEPT","EXCHANGE","EXECUTE","EXISTS","EXIT","EXPANSION","EXPLAIN","EXPIRATION","EXPIRE","EXPORT",
        "EXTENDED","EXTENT_SIZE","FALSE","FAST","FAULTS","FETCH","FIELDS","FILE","FILE_BLOCK_SIZE","FILTER","FIRST","FIXED","FLUSH",
        "FOLLOWING","FOR","FORCE","FOREIGN","FORMAT","FOUND","FROM","FULL","GENERAL","GENERATED","GET","GLOBAL","GRANT", "GRANTS","GROUP","HANDLER","HASH",
        "HELP","HIGH_PRIORITY","HISTORY","HOLD","HOST","HOSTS","HOUR","HOUR_MICROSECOND","HOUR_MINUTE","HOUR_SECOND","IDENTIFIED","IF",
        "IGNORE","IGNORE_SERVER_IDS","IGNORE_SPACE","IMPORT","IN","INDEX","INDEXES","INLINE","INNER","INSERT","INSERT_METHOD","INSTANCE","INT",
        "INTEGER","INTERVAL","INTO","INVISIBLE","IO","IO_AFTER_GTIDS","IO_BEFORE_GTIDS","IO_THREAD","IPC","IS","ISOLATION","ISSUER","ITERATE",
        "JOIN","JSON_TABLE","KEY","KEYS","KILL","LANGUAGE","LAST","LEADING","LEAVE","LEAVES","LEFT","LESS","LEVEL","LIKE","LIMIT","LINEAR",
        "LINES","LOAD","LOCAL","LOCALTIME","LOCALTIMESTAMP","LOCK","LOCKED","LOCKFILE","LOGS","MASTER","MATCH","MAXVALUE","MAX_CONNECTIONS_PER_HOUR",
        "MAX_QUERIES_PER_HOUR","MAX_ROWS","MAX_SIZE","MAX_STATEMENT_TIME","MAX_UPDATES_PER_HOUR","MAX_USER_CONNECTIONS","MEDIUMINT","MEMBER",
        "MEMORY","MERGE","MESSAGE_TEXT","MIDDLEINT","MIGRATE","MINUTE","MINUTE_MICROSECOND","MINUTE_SECOND","MIN_ROWS","MOD","MODE","MODIFIES",
        "MODIFY","MODULE","MONTH","MULTILINESTRING","MULTIPOINT","MULTIPOLYGON","MUTEX","MYSQL_ERRNO","NAME","NAMES","NATIONAL","NATURAL","NCHAR",
        "NDB","NDBCLUSTER","NEVER","NEW","NEXT","NO","NODEGROUP","NONE","NORMAL","NOT","NOWAIT","NTH_VALUE","NTILE","NULL","NUMBER","OFFSET",
        "OLD_PASSWORD","ON","ONE","ONLY","OPEN","OPTIMIZE","OPTION","OPTIONALLY","OPTIONS","OR","ORDER","OTHERS","OUT","OUTER","OUTFILE","OVER",
        "OWNER","PACK_KEYS","PAGE","PARTIAL","PARTITION","PARTITIONING","PARTITIONS","PASSWORD","PHASE","PLUGIN_DIR","PLUGIN","PLUGINS","POINT","POLYGON",
        "PORT","PRECEDES","PRECISION","PREFIX","PREPARE","PRESERVE","PREV","PRIMARY","PRIVILEGES","PROCEDURE","PROCESSLIST","PROFILE","PROFILES",
        "PURGE","QUERY","QUICK","QUIT","RANGE","READ","READ_ONLY","READ_WRITE","REAL","REBUILD","RECOVER","REDO_BUFFER_SIZE","REDOFILE","REDUNDANT",
        "REFERENCES","REGEXP","RELAY","RELAY_LOG_FILE","RELAY_LOG_POS","RELAY_THREAD","RELEASE","REMOTE","RENAME","REORGANIZE","REPAIR","REPEAT",
        "REPLACE","REPLICATION","REQUIRE","RESET","RESIGNAL","RESOURCE","RESPECT","RESTART","RESTRICT","RESULT","RESUME","RETAIN","RETURN",
        "RETURNED_SQLSTATE","REUSE","REVERSE","REVOKE","RIGHT","RLIKE","ROLE","ROLLBACK","ROLLUP","ROW","ROWS","ROW_FORMAT","SAVEPOINT",
        "SCHEDULE","SCHEMA","SCHEMAS", "SCHEMA_NAME","SECOND","SECOND_MICROSECOND","SECURITY","SELECT","SERIAL","SERIALIZABLE","SERVER","SESSION","SET",
        "SHARE","SHOW","SIGNAL","SIGNED","SIMPLE","SLAVE", "SLAVES","SLOW","SMALLINT","SONAME","SOUNDS","SOURCE","SPATIAL","SPECIFIC","SQL","SQLEXCEPTION",
        "SQLSTATE","SQLWARNING","SQL_AFTER_GTIDS","SQL_BEFORE_GTIDS","SQL_BIG_RESULT","SQL_BUFFER_RESULT","SQL_CACHE","SQL_CALC_FOUND_ROWS",
        "SQL_NO_CACHE","SQL_SMALL_RESULT","SQL_THREAD","SQL_TSI_DAY","SQL_TSI_HOUR","SQL_TSI_MINUTE","SQL_TSI_MONTH","SQL_TSI_QUARTER",
        "SQL_TSI_SECOND","SQL_TSI_WEEK","SQL_TSI_YEAR","SSL","STACKED","START","STARTING","STARTS","STATEMENT","STATS_AUTO_RECALC","STATS_PERSISTENT",
        "STATS_SAMPLE_PAGES","STATUS","STOP","STORAGE","STORED","STRAIGHT_JOIN","STRING","SUBCLASS_ORIGIN","SUBJECT","SUBPARTITION",
        "SUBPARTITIONS","SUPER","SUSPEND","SWAPS","SWITCHES","SYSTEM","TABLE","TABLES","TABLESPACE","TABLE_CHECKSUM","TABLE_NAME",
        "TEMPORARY","TEMPTABLE","TERMINATED","TEXT","THAN","THEN","TIES","TIME","TIMESTAMP","TIMESTAMPADD","TIMESTAMPDIFF","TINYBLOB",
        "TINYINT","TINYTEXT","TO","TRAILING","TRANSACTION","TRIGGER","TRIGGERS","TRUE","TRUNCATE","TYPE","TYPES","UNCOMMITTED","UNDEFINED",
        "UNDO","UNDOFILE","UNDO_BUFFER_SIZE","UNINSTALL","UNION","UNIQUE","UNKNOWN","UNLOCK","UNSIGNED","UNTIL","UPDATE","UPGRADE","USAGE",
        "USE","USER","USER_RESOURCES","USE_FRM","USING","UTC_DATE","UTC_TIME","UTC_TIMESTAMP","VALUE","VALUES","VARBINARY","VARCHAR",
        "VARCHARACTER","VARIABLES","VARYING","VIEW","WHEN","WHERE","WHILE","WITH","WORK","X509","XA","XOR","YEAR_MONTH","ZEROFILL", "ID"];

        return in_array(strtoupper($word), $keywords, true);
    }

/**
 * Handle query state through `replaceAlias`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @return mixed Returned value for replaceAlias.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::replaceAlias()
 * @example /fr/query/replaceAlias
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function replaceAlias($string)
    {
        // add space between to be sur to match a token
        $map = [
            ' SECOND'   => ' SQL_TSI_SECOND',
            ' DAY'      => ' SQL_TSI_DAY',
            ' HOUR'     => ' SQL_TSI_HOUR',
            ' MINUTE'   => ' SQL_TSI_MINUTE',
            ' MONTH'    => ' SQL_TSI_MONTH',
            ' QUARTER'  => ' SQL_TSI_QUARTER',
            ' USER '     => ' SYSTEM_USER ',
            ' DISTINCT ' => ' DISTINCTROW ',
            ' <> '       => ' != ',
        ];

        // Remplace chaque clé par sa valeur
        return strtr($string, $map);
    }


    /*

        UPDATE performance_schema.setup_instruments SET ENABLED = 'YES' WHERE NAME LIKE 'statement/%';
        UPDATE performance_schema.setup_consumers   SET ENABLED = 'YES'   
        WHERE NAME IN (
        'events_statements_current',
        'events_statements_history',
        'events_statements_history_long',
        'statements_digest'
        );


                    UPDATE performance_schema.setup_consumers   SET ENABLED = 'YES'   
            WHERE NAME IN (
            'events_statements_current',
            'events_statements_history_long',
            'statements_digest'
            );


        SELECT * FROM performance_schema.setup_consumers WHERE NAME IN (
        'events_statements_current',
        'events_statements_history',
        'events_statements_history_long',
        'statements_digest'
        );


    UPDATE performance_schema.setup_consumers   SET ENABLED = 'NO'   
        WHERE NAME IN (
        'events_statements_current',
        'events_statements_history',
        'events_statements_history_long'
        );

    */


        // MAX_SQLTEXT_LENGTH => 1024 
        // storage/perfschema/pfs_events_statements.h
        // #define MAX_SQLTEXT_LENGTH 1024
        // #define MAX_DIGEST_TEXT_LENGTH 1024
/**
 * Handle query state through `collectDigest`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for collectDigest.
 * @phpstan-return void
 * @psalm-return void
 * @see self::collectDigest()
 * @example /fr/query/collectDigest
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function collectDigest($param)
    {
   
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];


        $default = Sgbd::sql(DB_DEFAULT);
        $db = Mysql::getDbLink($id_mysql_server);


        $sql = "SELECT a.DIGEST, a.SQL_TEXT, b.DIGEST_TEXT 
        FROM performance_schema.events_statements_history_long a 
        INNER JOIN performance_schema.events_statements_summary_by_digest b on a.DIGEST = b.DIGEST";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {


            $sql3 = "SELECT count(1) as cpt from query_sample WHERE digest = '$ob->DIGEST'";
            $res3 = $default->sql_query($sql3);

            while($ob3 = $default->sql_fetch_object($res3))
            {
                if ($ob3->cpt === "0")
                {
                    $digest_text = trim($ob->DIGEST_TEXT);

                    $is_truncated = 0;
                    if (substr($ob->SQL_TEXT, -3) == "...") {
                        $is_truncated = 1;
                    }

                    $sql2 = "INSERT IGNORE INTO query_sample VALUES(NULL,
                    '".trim($default->sql_real_escape_string($ob->SQL_TEXT))."' ,
                    '".trim($default->sql_real_escape_string($digest_text))."',
                    '".trim($default->sql_real_escape_string($ob->DIGEST))."',
                    '".$is_truncated."')";

                    Debug::sql($sql2);

                    $default->sql_query($sql2);
                }
            }


        } 
         
    
    }
    

/**
 * Handle query state through `collectAll`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for collectAll.
 * @phpstan-return void
 * @psalm-return void
 * @see self::collectAll()
 * @example /fr/query/collectAll
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function collectAll($param)
    {
        Debug::parseDebug($param);

        $servers = Extraction2::display(
            array("mysql_available")
        );

        foreach($servers as $id_mysql_server => $server)
        {
            Debug::debug($id_mysql_server);

            if ($server['mysql_available'] === "1")
            {
                $this->collectDigest(array($id_mysql_server));
            }

        }
    }

/**
 * Handle query state through `degradeMariaDB`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $string Input value for `string`.
 * @phpstan-param mixed $string
 * @psalm-param mixed $string
 * @return mixed Returned value for degradeMariaDB.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::degradeMariaDB()
 * @example /fr/query/degradeMariaDB
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function degradeMariaDB($string)
    {
        // Regex pour capturer le mot avant la parenthèse
        $regex = '/\b([A-Z_][A-Z0-9_]*)\s*(?=\()/i';

        $exceptions = self::$exceptions;

        $result = preg_replace_callback($regex, function($matches) use ($exceptions) {
            $word = strtoupper($matches[1]);

            // si le mot est dans la liste d'exceptions => pas de backquotes
            if (in_array($word, self::$exceptions)) {
                return $matches[1]." ";
            }

            // sinon ajouter les backquotes
            return "`{$matches[1]}` ";
        }, $string);

        return $result;

    }

/**
 * Handle `all2`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for all2.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example all2(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function all2($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0] ?? 1;
        $db = Sgbd::sql(DB_DEFAULT);

        $_GET['mysql_server']['id'] = $id_mysql_server;

        // TRI
        $sort = $_GET['sort'] ?? 'sum_timer_wait';
        $order = $_GET['order'] ?? 'desc';

        $elems = Extraction2::display(["performance_schema"], [$id_mysql_server]);
        $query = Extraction2::display(
            ["sum_timer_wait", "count_star", "avg_timer_wait", "sum_rows_examined", "sum_rows_sent", "sum_rows_affected", 
            "sum_no_index_used","sum_no_good_index_used", "sum_errors", "sum_warnings" ],
            [$id_mysql_server]
        );

        // Ajout colonne virtual "rows_query" pour tri correct
        if (isset($query[$id_mysql_server]['@digest'])) {
            foreach ($query[$id_mysql_server]['@digest'] as $k => $v) {
                $count = $v['count_star'] ?? 0;
                $exam  = $v['sum_rows_sent'] ?? 0;


                // Virtual rows/query
                $v['rows_query'] = ($count > 0) ? ($exam / $count) : 0;

                // Flags (déjà demandés)
                $v['has_errors']      = !empty($v['sum_errors']);
                $v['has_warnings']    = !empty($v['sum_warnings']);
                $v['has_no_index_used'] = !empty($v['sum_no_index_used']) && $v['sum_no_index_used'] > 0;
                $v['has_no_good_index_used'] = !empty($v['sum_no_good_index_used']) && $v['sum_no_good_index_used'] > 0;

                $v['has_table_scan'] = $v['has_no_index_used'] || $v['has_no_good_index_used'];
                
                // ---------------------------------------------------------------------
                //  AJOUT : RATIO normalisé par requête (0.xx)
                // ---------------------------------------------------------------------
                $v['errors_ratio'] = ($count > 0 && !empty($v['sum_errors']))
                    ? round($v['sum_errors'] / $count, 2)
                    : 0.00;

                $v['warnings_ratio'] = ($count > 0 && !empty($v['sum_warnings']))
                    ? round($v['sum_warnings'] / $count, 2)
                    : 0.00;

                $v['no_index_ratio'] = ($count > 0 && !empty($v['sum_no_index_used']))
                    ? round($v['sum_no_index_used'] / $count, 2)
                    : 0.00;

                $v['no_good_index_ratio'] = ($count > 0 && !empty($v['sum_no_good_index_used']))
                    ? round($v['sum_no_good_index_used'] / $count, 2)
                    : 0.00;

                $query[$id_mysql_server]['@digest'][$k]['rows_query'] = ($count > 0) ? ($exam / $count) : 0;
            }

            $rows =& $query[$id_mysql_server]['@digest'];
            uasort($rows, function ($a, $b) use ($sort, $order) {

                // tri spécial rows/query
                if ($sort === 'rows_query') {
                    return ($order === 'asc')
                        ? ($a['rows_query'] <=> $b['rows_query'])
                        : ($b['rows_query'] <=> $a['rows_query']);
                }

                $aVal = $a[$sort] ?? 0;
                $bVal = $b[$sort] ?? 0;

                return ($order === 'asc')
                    ? ($aVal <=> $bVal)
                    : ($bVal <=> $aVal);
            });
        }

        $data['queries'] = [];
        $data['performance_schema'] = $elems[$id_mysql_server]['performance_schema'] ?? 0;

        $sql = "SELECT * FROM ts_mysql_query WHERE id_mysql_server = $id_mysql_server";
        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['queries'][$arr['id']] = $arr;
        }

        $this->set('id_mysql_server', $id_mysql_server);
        $this->set('data', $data);
        $this->set('param', $param);
        $this->set('query', $query);
        $this->set('sort', $sort);
        $this->set('order', $order);
    }

/**
 * Handle `digest_old`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for digest_old.
 * @phpstan-return void
 * @psalm-return void
 * @example digest_old(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function digest_old($param)
    {
        Debug::parseDebug($parma);

        $id_mysql_server = $param[0];
        $digest = $param[1];

        $db = Sgbd::sql(DB_DEFAULT);
        
        // if mysql server available
        $extra = Mysql::getDbLink($id_mysql_server, "EXTRA");


$sql ="WITH stats AS (
  SELECT
    b.date,
    JSON_UNQUOTE(JSON_EXTRACT(b.value, '$.data.$digest.COUNT_STAR')) + 0 AS count_star,
    JSON_UNQUOTE(JSON_EXTRACT(b.value, '$.data.$digest.SUM_TIMER_WAIT')) + 0 AS sum_timer_wait,
    LAG(
      JSON_UNQUOTE(JSON_EXTRACT(b.value, '$.data.$digest.COUNT_STAR')) + 0
    ) OVER (ORDER BY b.date) AS prev_count_star,
    LAG(
      JSON_UNQUOTE(JSON_EXTRACT(b.value, '$.data.$digest.SUM_TIMER_WAIT')) + 0
    ) OVER (ORDER BY b.date) AS prev_sum_timer_wait
  FROM ts_variable a
  JOIN ts_value_general_json b
    ON b.id_ts_variable = a.id
   AND b.id_mysql_server = ".$id_mysql_server."
   AND b.date BETWEEN NOW() - INTERVAL 1 HOUR AND NOW()
  WHERE a.`from` = 'performance_schema'
    AND a.`name` = 'events_statements_summary_by_digest'
)
SELECT
  date,
  count_star,
  sum_timer_wait,
  count_star - prev_count_star AS diff_count_star,
  (sum_timer_wait - prev_sum_timer_wait) / 1000000000.0 AS diff_sum_timer_wait,
  -- conversion en millisecondes, arrondi
  ROUND(
    (sum_timer_wait - prev_sum_timer_wait) / 1000000000.0
    / NULLIF((count_star - prev_count_star), 0)
  ) AS avg_ms_per_count
FROM stats
WHERE prev_count_star IS NOT NULL
ORDER BY date;
";

        //debug($sql);

        //$res = $db->sql_query($sql);

        $elems = Extraction2::display(array("events_statements_summary_by_digest") , array($id_mysql_server));
        
        $data = [];
        $data['query'] = $elems[$id_mysql_server]['events_statements_summary_by_digest']['data'][$digest];

        
        $sql2 = "select * from performance_schema.events_statements_history_long where DIGEST= '".$digest."' LIMIT 1";
        $res2 = $extra->sql_query($sql2);

        while($arr2 = $extra->sql_fetch_array($res2, MYSQLI_ASSOC))
        {
            $data['sql_text'] = $arr2['SQL_TEXT'];
        }

        $data['tables'] = $this->extractTablesWithOffsets($data['sql_text']);
        
        foreach($data['tables']  as $key => $table)
        {   
            if (empty($table['alias']))
            {
                $data['tables'][$key]['alias'] = $table['table']; 
            }
            $data['alias'][$data['tables'][$key]['alias']] = $table;
        }
        
        Debug::debug($data['alias']);

        $data['explain'] = array();
        if (! str_ends_with(trim($data['sql_text']), '...')) {

            $sql3 = "EXPLAIN extended ".$data['sql_text'];
            $res3 = $extra->sql_query($sql3);

            
            while($arr3 = $extra->sql_fetch_array($res3, MYSQLI_ASSOC))
            {
                $data['explain'][] = $arr3;
            }
        }





        $this->set('data', $data);
    }





    
/**
 * Handle `digest`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for digest.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @example digest(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
public function digest($param)
{
    Debug::parseDebug($param);

    $id_mysql_server = (int)$param[0];
    $schema_name     = $param[1];
    $digest          = $param[2];

    $db    = Sgbd::sql(DB_DEFAULT);
    $extra = Mysql::getDbLink($id_mysql_server, "EXTRA");

    if ($schema_name === "NULL"){
        $schema_name = "";
    }

    if (!empty($schema_name)) {
        $extra->sql_select_db($schema_name);
    }

    // 1) Charger métadonnées digest (local)
    $sql = "SELECT id, digest_text, digest
            FROM mysql_digest
            WHERE digest = '".$db->sql_real_escape_string($digest)."'
            LIMIT 1";
              


              debug($sql);

    $res = $db->sql_query($sql);
    $data = $db->sql_fetch_array($res, MYSQLI_ASSOC);


    $query = Extraction2::display(
        ["count_star","sum_timer_wait","min_timer_wait","avg_timer_wait","max_timer_wait","sum_lock_time","sum_errors","sum_warnings",
        "sum_rows_affected","sum_rows_sent","sum_rows_examined","sum_created_tmp_disk_tables","sum_created_tmp_tables","sum_select_full_join",
        "sum_select_full_range_join","sum_select_range","sum_select_range_check","sum_select_scan","sum_sort_merge_passes","sum_sort_range",
        "sum_sort_rows","sum_sort_scan","sum_no_index_used","sum_no_good_index_used","id_ts_mysql_query","sum_cpu_time","max_controlled_memory",
        "max_total_memory","count_secondary","quantile_95","quantile_99","quantile_999","query_sample_text","query_sample_seen","query_sample_timer_wait"],
        [$id_mysql_server]
    );

    if (!empty($data) && isset($query[$id_mysql_server]['@digest'][$data['id']])) {
        debug($query[$id_mysql_server]['@digest'][$data['id']]);
    }
    //debug($stats);


    // Fallback si digest non présent localement
    if (empty($data)) {
        $sql_fb = "SELECT SCHEMA_NAME AS schema_name, DIGEST_TEXT AS digest_text, DIGEST AS digest
                   FROM performance_schema.events_statements_summary_by_digest
                   WHERE DIGEST = '".$extra->sql_real_escape_string($digest)."'
                   LIMIT 1";

        $res_fb = $extra->sql_query($sql_fb);
        $data = $extra->sql_fetch_array($res_fb, MYSQLI_ASSOC);




        if (empty($data)) {
            throw new \Exception("Digest '$digest' not found locally or on server $id_mysql_server");
        }
    }

    $data = self::normalizeDigestRecord($data, $id_mysql_server, $schema_name, $digest);

    // 2) Récupérer l'exemple réel (non normalisé)
    $sql_real = "SELECT SQL_TEXT
                 FROM performance_schema.events_statements_history_long
                 WHERE DIGEST = '".$extra->sql_real_escape_string($digest)."'
                 ORDER BY EVENT_ID DESC
                 LIMIT 1";

    $res2 = $extra->sql_query($sql_real);
    $arr2 = $extra->sql_fetch_array($res2, MYSQLI_ASSOC);

    if (empty($arr2['SQL_TEXT'])) {
        // Pas d'exemple → pas d'EXPLAIN possible
        $data['sql_text']     = $data['digest_text'];
        $data['tables']       = [];
        $data['alias']        = [];
        $data['index_info']   = [];
        $data['explain']      = [];
        $data['explain_json'] = null;

        $this->set('data', $data);
        return;
    }

    $data['sql_text'] = $arr2['SQL_TEXT'];

    // 3) Extraction tables + alias
    $data['tables'] = QueryGraphExtractor::extractTablesWithOffsets($data['sql_text']);
    foreach ($data['tables'] as $key => $tbl) {
        if (empty($tbl['alias'])) {
            $data['tables'][$key]['alias'] = $tbl['table'];
        }
        $data['alias'][$data['tables'][$key]['alias']] = $data['tables'][$key];
    }

    // 4) Index & cardinalité
    $data['index_info'] = [];
    foreach ($data['tables'] as $tbl) {
        $table_name = (string) ($tbl['table'] ?? '');

        $sql_idx = self::buildDigestShowIndexSql($table_name);
        if ($sql_idx === null) {
            continue;
        }
        $res_idx = $extra->sql_query($sql_idx);

        while ($row = $extra->sql_fetch_array($res_idx, MYSQLI_ASSOC)) {
            $data['index_info'][$table_name][] = [
                'index_name'  => $row['Key_name'],
                'seq'         => $row['Seq_in_index'],
                'column_name' => $row['Column_name'],
                'cardinality' => $row['Cardinality'],
                'non_unique'  => $row['Non_unique'],
            ];
        }
    }

    // 5) Vérification type requête pour autoriser EXPLAIN
    $normalized = ltrim($data['sql_text']);
    $prefix = strtoupper(substr($normalized, 0, 6));

    $allowed = ["SELECT", "UPDATE", "INSERT", "DELETE"];
    if (!in_array($prefix, $allowed)) {
        $data['explain']      = [];
        $data['explain_json'] = null;
        $this->set('data', $data);
        return;
    }

    // 6) EXPLAIN classique
    $data['explain'] = [];
    $sql_explain = "EXPLAIN " . $data['sql_text'];
    if ($res3 = $extra->sql_query($sql_explain)) {
        while ($r3 = $extra->sql_fetch_array($res3, MYSQLI_ASSOC)) {
            $data['explain'][] = $r3;
        }
    }

    // 7) EXPLAIN JSON
    $data['explain_json'] = null;
    $sql_explain_json = "EXPLAIN FORMAT=JSON " . $data['sql_text'];
    if ($res4 = $extra->sql_query($sql_explain_json)) {
        $row = $extra->sql_fetch_array($res4, MYSQLI_ASSOC);
        $data['explain_json'] = json_decode($row['EXPLAIN'], true);
    }

    $this->set('data', $data);
}

    private static function buildDigestShowIndexSql(string $tableName): ?string
    {
        if (!Identifier::isStrictSqlIdentifier($tableName)) {
            return null;
        }

        return "SHOW INDEX FROM ".Identifier::quoteStrictSqlIdentifier($tableName);
    }

    public static function buildDigestPath(int $idMysqlServer, string $schemaName, string $digest): string
    {
        return 'Query/digest/'.$idMysqlServer.'/'.rawurlencode($schemaName === '' ? 'NULL' : $schemaName).'/'.rawurlencode($digest).'/';
    }

    public static function buildGraphPath(int $idMysqlServer, string $schemaName, string $digest): string
    {
        return 'Query/graph/'.$idMysqlServer.'/'.rawurlencode($schemaName === '' ? 'NULL' : $schemaName).'/'.rawurlencode($digest).'/';
    }

    public static function evaluateGraphRequest(array $get, array $server, array $param): array
    {
        if (strtoupper((string)($server['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
            return [
                'allowed' => false,
                'status' => 405,
                'body' => 'Method Not Allowed',
                'headers' => ['Allow' => 'GET'],
            ];
        }

        if (array_key_exists('sql', $get)) {
            return [
                'allowed' => false,
                'status' => 400,
                'body' => 'Raw SQL input is not accepted on this route.',
                'headers' => [],
            ];
        }

        $route = self::normalizeGraphRoute($param);
        if ($route === null) {
            return [
                'allowed' => false,
                'status' => 400,
                'body' => 'Invalid query graph route.',
                'headers' => [],
            ];
        }

        return [
            'allowed' => true,
            'status' => 200,
            'body' => '',
            'headers' => [],
            'id_mysql_server' => $route['id_mysql_server'],
            'schema_name' => $route['schema_name'],
            'digest' => $route['digest'],
        ];
    }

    public function graph($param)
    {
        Debug::parseDebug($param);

        $outcome = self::evaluateGraphRequest($_GET, $_SERVER, $param);
        if (!$outcome['allowed']) {
            $this->sendGraphError($outcome);
            return;
        }

        $idMysqlServer = (int)$outcome['id_mysql_server'];
        $schemaName = (string)$outcome['schema_name'];
        $digest = (string)$outcome['digest'];

        $db = Sgbd::sql(DB_DEFAULT);
        $extra = Mysql::getDbLink($idMysqlServer, "EXTRA");

        if ($schemaName !== '') {
            $extra->sql_select_db($schemaName);
        }

        $data = self::loadDigestRecord($db, $extra, $idMysqlServer, $schemaName, $digest);
        $data['sql_text'] = self::loadDigestSqlText($extra, $digest, (string)$data['digest_text']);
        $data['graph'] = QueryGraphExtractor::extract($data['sql_text']);
        $data['dot'] = QueryGraphDotBuilder::build($data['graph']);
        $data['digest_href'] = self::buildDigestPath($idMysqlServer, $schemaName, $digest);

        $reference = 'query_graph_'.substr(sha1($idMysqlServer."\0".$schemaName."\0".$digest), 0, 16);
        if (!is_dir(TMP.'dot')) {
            mkdir(TMP.'dot', 0775, true);
        }
        $data['svg_path'] = Graphviz::generateDot($reference, $data['dot']);
        $data['graphviz_error'] = Graphviz::getLastGenerateDotError();
        $data['svg'] = is_file($data['svg_path']) ? (string)file_get_contents($data['svg_path']) : '';

        $this->set('data', $data);
    }

    private static function normalizeGraphRoute(array $param): ?array
    {
        $idMysqlServer = self::normalizeGraphId($param[0] ?? null);
        $schemaName = self::normalizeGraphText($param[1] ?? null, 255, true);
        $digest = self::normalizeGraphDigest($param[2] ?? null);

        if ($idMysqlServer === null || $schemaName === null || $digest === null) {
            return null;
        }

        if (strtoupper($schemaName) === 'NULL') {
            $schemaName = '';
        }

        return [
            'id_mysql_server' => $idMysqlServer,
            'schema_name' => $schemaName,
            'digest' => $digest,
        ];
    }

    private static function normalizeGraphId($value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string)$value);
        if ($value === '' || !ctype_digit($value) || (int)$value < 1) {
            return null;
        }

        return (int)$value;
    }

    private static function normalizeGraphText($value, int $maxLength, bool $allowEmpty): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim(rawurldecode((string)$value));
        if ((!$allowEmpty && $value === '') || strlen($value) > $maxLength) {
            return null;
        }

        return $value;
    }

    private static function normalizeGraphDigest($value): ?string
    {
        $digest = self::normalizeGraphText($value, 128, false);
        if ($digest === null || preg_match('/^[A-Fa-f0-9]{16,128}$/', $digest) !== 1) {
            return null;
        }

        return strtoupper($digest);
    }

    private function sendGraphError(array $outcome): void
    {
        $this->view = false;
        $this->layout_name = false;
        http_response_code((int)$outcome['status']);

        foreach ($outcome['headers'] as $name => $value) {
            header($name.': '.$value);
        }

        header('Content-Type: text/plain; charset=UTF-8');
        echo $outcome['body'];
    }

    private static function loadDigestRecord($db, $extra, int $idMysqlServer, string $schemaName, string $digest): array
    {
        $sql = "SELECT id, digest_text, digest
            FROM mysql_digest
            WHERE digest = '".$db->sql_real_escape_string($digest)."'
            LIMIT 1";

        $res = $db->sql_query($sql);
        $data = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (empty($data)) {
            $sqlFallback = "SELECT NULL AS id, SCHEMA_NAME AS schema_name, DIGEST_TEXT AS digest_text, DIGEST AS digest
                FROM performance_schema.events_statements_summary_by_digest
                WHERE DIGEST = '".$extra->sql_real_escape_string($digest)."'
                LIMIT 1";

            $resFallback = $extra->sql_query($sqlFallback);
            $data = $extra->sql_fetch_array($resFallback, MYSQLI_ASSOC);

            if (empty($data)) {
                throw new \Exception("Digest '$digest' not found locally or on server $idMysqlServer");
            }
        }

        return self::normalizeDigestRecord($data, $idMysqlServer, $schemaName, $digest);
    }

    private static function normalizeDigestRecord(array $data, int $idMysqlServer, string $schemaName, string $digest): array
    {
        $data['id_mysql_server'] = $idMysqlServer;
        $data['schema_name'] = $schemaName;
        $data['digest'] = (string)($data['digest'] ?? $data['DIGEST'] ?? $digest);
        $data['digest_text'] = (string)($data['digest_text'] ?? $data['DIGEST_TEXT'] ?? '');

        return $data;
    }

    private static function loadDigestSqlText($extra, string $digest, string $fallbackSql): string
    {
        $sql = "SELECT SQL_TEXT
            FROM performance_schema.events_statements_history_long
            WHERE DIGEST = '".$extra->sql_real_escape_string($digest)."'
            AND SQL_TEXT IS NOT NULL
            ORDER BY EVENT_ID DESC
            LIMIT 1";

        $res = $extra->sql_query($sql);
        $row = $extra->sql_fetch_array($res, MYSQLI_ASSOC);

        return empty($row['SQL_TEXT']) ? $fallbackSql : (string)$row['SQL_TEXT'];
    }



/**
 * Handle `parseExplainJson`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array $json Input value for `json`.
 * @phpstan-param array $json
 * @psalm-param array $json
 * @return mixed Returned value for parseExplainJson.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example parseExplainJson(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function parseExplainJson(array $json)
    {
        // Path change selon version MariaDB/MySQL
        $plan = $json['query_block'] ?? $json['join'] ?? $json;

        $tables = [];

        $extract = function($node) use (&$extract, &$tables) {
            if (isset($node['table_name'])) {
                $tables[] = [
                    'table'         => $node['table_name'] ?? '?',
                    'access_type'   => $node['access_type'] ?? '?',
                    'rows'          => $node['rows'] ?? '?',
                    'filtered'      => isset($node['filtered']) ? $node['filtered'].'%' : '?',
                    'possible_keys' => $node['possible_keys'] ?? '',
                    'key'           => $node['key'] ?? '',
                    'cost'          => $node['cost_info']['query_cost'] ?? '',
                ];
            }

            foreach ($node as $k => $v) {
                if (is_array($v)) {
                    $extract($v);
                }
            }
        };

        $extract($plan);
        return $tables;
    }


/**************************** */



    public function all($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = (int)($param[0] ?? 1);
        $db = Sgbd::sql(DB_DEFAULT);

        $_GET['mysql_server']['id'] = $id_mysql_server;

        // TRI
        $sort  = $_GET['sort']  ?? 'sum_timer_wait';
        $order = $_GET['order'] ?? 'desc';

        // Fenêtre de temps (zoom) en secondes : min 1 min, max 2 jours
        $window = isset($_GET['window']) ? (int)$_GET['window'] : 86400; // défaut 24h
        if ($window < 60) {
            $window = 60;
        } elseif ($window > 172800) {
            $window = 172800; // max 2 jours
        }

        // ts_file.id pour les digests (ps_sum_summary_by_digest)
        $db_ts = Sgbd::sql(DB_DEFAULT);
        $sql_id = "SELECT id FROM ts_file WHERE file_name = 'ps_sum_summary_by_digest' LIMIT 1";
        $res_id = $db_ts->sql_query($sql_id);
        $TS_FILE_DIGEST = $db_ts->sql_fetch_array($res_id, MYSQLI_ASSOC)['id'] ?? 3790;

        

        // Récupère l'uptime du serveur MySQL (en secondes)
        $uptime = 0;
        $tmp = null;
        try {
            $uptimeData = Extraction2::display(["uptime"], [$id_mysql_server]);
            $tmp = null;
            $tmp = $uptimeData[$id_mysql_server]['uptime'] ?? null;
            
            $test = is_numeric($tmp);
            if ($test) {
                $uptime = intval($tmp);
            }
        } catch (\Exception $e) {
            // si problème, uptime = 0 => on ignore simplement la contrainte reboot
            $uptime = 0;
        }
        //debug($uptime);

        // Dernière date disponible pour ce serveur / ts_file (fin de fenêtre)
        $sql = "SELECT b.id,a.last_date_listener as `date`
        from ts_max_date a INNER JOIN ts_file b ON a.id_ts_file=b.id
        WHERE b.file_name ='performance_schema' and a.id_mysql_server=$id_mysql_server;";

        $res = $db->sql_query($sql);
        $end_date = null;
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $end_date = $row['date'];
        }

        $data = [];
        $query = [];

        if (empty($end_date)) {
            // Pas de données…
            $this->set('id_mysql_server', $id_mysql_server);
            $this->set('data', $data);
            $this->set('param', $param);
            $this->set('query', $query);
            $this->set('sort', $sort);
            $this->set('order', $order);
            $this->set('window', $window);
            $this->set('start_date', null);
            $this->set('end_date', null);
            return;
        }

        $end_ts = strtotime($end_date);

        // Fenêtre théorique (end_date - window)
        $start_ts = $end_ts - $window;

        // Si uptime plus petit que window => on ne remonte pas avant reboot
        if ($uptime > 0) {
            $reboot_ts = $end_ts - $uptime;
            if ($reboot_ts > $start_ts) {
                $start_ts = $reboot_ts;
            }
        }

        $start_candidate = date('Y-m-d H:i:s', $start_ts);

        // Aligner la date de début sur une date réellement présente
        $start_date = null;
        $start_candidate_esc = $db->sql_real_escape_string($start_candidate);

        $sql = "SELECT MIN(`date`) AS date_min
                FROM ts_date_by_server
                WHERE id_mysql_server = ".$id_mysql_server."
                AND id_ts_file      = ".$TS_FILE_DIGEST."
                AND `date`         >= '".$start_candidate_esc."'";



        $res = $db->sql_query($sql);
        if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $start_date = $row['date_min'];
        }

        if (empty($start_date)) {
            // Aucun point >= start_candidate, on prend la plus ancienne date connue
            $sql = "SELECT MIN(`date`) AS date_min
                    FROM ts_date_by_server
                    WHERE id_mysql_server = ".$id_mysql_server."
                    AND id_ts_file      = ".$TS_FILE_DIGEST;


            $res = $db->sql_query($sql);
            if ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $start_date = $row['date_min'];
            } else {
                // Toujours rien => on quitte
                $this->set('id_mysql_server', $id_mysql_server);
                $this->set('data', $data);
                $this->set('param', $param);
                $this->set('query', $query);
                $this->set('sort', $sort);
                $this->set('order', $order);
                $this->set('window', $window);
                $this->set('start_date', null);
                $this->set('end_date', $end_date);
                return;
            }
        }

        // Sécurisation dates pour les requêtes
        $start_esc = $db->sql_real_escape_string($start_date);
        $end_esc   = $db->sql_real_escape_string($end_date);

        
        

// --- Snapshot courant (fin de fenêtre) ---
        $sql_current =   "SELECT s.* 
        FROM ts_mysql_digest_stat s 
        INNER JOIN mysql_database__mysql_digest m ON m.id = s.id_mysql_database__mysql_digest 
        WHERE m.id_mysql_server = ".$id_mysql_server." AND s.date = '".$end_esc."';";


        Debug::debug($sql_current);

        $res = $db->sql_query($sql_current);
        $current = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $current[(int)$row['id_mysql_database__mysql_digest']] = $row;
        }

        // --- Snapshot "début" (début de fenêtre aligné) ---

        $sql_previous =   "SELECT s.* 
        FROM ts_mysql_digest_stat s 
        INNER JOIN mysql_database__mysql_digest m ON m.id = s.id_mysql_database__mysql_digest 
        WHERE m.id_mysql_server = ".$id_mysql_server." AND s.date = '".$start_esc."';";



        $res = $db->sql_query($sql_previous);
        $previous = [];
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $previous[(int)$row['id_mysql_database__mysql_digest']] = $row;
        }


        // --- Calcul des deltas par digest_schema ---
        $digest_rows = [];

        // champs cumulés sur lesquels on fait un delta
        $deltaFields = [
            'count_star',
            'sum_timer_wait',
            'min_timer_wait',     // on recalculera ensuite min/max si besoin, mais on peut les ignorer
            'avg_timer_wait',     // ne servira pas tel quel
            'max_timer_wait',
            'sum_lock_time',
            'sum_errors',
            'sum_warnings',
            'sum_rows_affected',
            'sum_rows_sent',
            'sum_rows_examined',
            'sum_created_tmp_disk_tables',
            'sum_created_tmp_tables',
            'sum_select_full_join',
            'sum_select_full_range_join',
            'sum_select_range',
            'sum_select_range_check',
            'sum_select_scan',
            'sum_sort_merge_passes',
            'sum_sort_range',
            'sum_sort_rows',
            'sum_sort_scan',
            'sum_no_index_used',
            'sum_no_good_index_used',
            'sum_cpu_time',
            'max_controlled_memory',
            'max_total_memory',
            'count_secondary',
            'quantile_95',
            'quantile_99',
            'quantile_999',
            'query_sample_timer_wait'
        ];

        foreach ($current as $id_digest_schema => $cur) {

            $prev = $previous[$id_digest_schema] ?? [];

            $row = [
                'id_mysql_database__mysql_digest' => $id_digest_schema,
                'date'                            => $cur['date'],
            ];

            // deltas
            foreach ($deltaFields as $field) {
                $c = isset($cur[$field])  ? (float)$cur[$field]  : 0.0;
                $p = isset($prev[$field]) ? (float)$prev[$field] : 0.0;
                $delta = $c - $p;
                if ($delta < 0) {
                    $delta = 0; // sécurité si reset de stats
                }
                $row[$field] = $delta;
            }

            // Si aucune requête sur la période => on skip
            if ($row['count_star'] <= 0) {
                continue;
            }

            // Recalcul avg_latency = sum_timer_wait_delta / count_star_delta
            if ($row['sum_timer_wait'] > 0 && $row['count_star'] > 0) {
                $row['avg_timer_wait'] = (float)($row['sum_timer_wait'] / $row['count_star']);
            } else {
                $row['avg_timer_wait'] = 0.0;
            }

            // rows per query (basé sur DELTA)
            $row['rows_query'] = ($row['count_star'] > 0)
                ? (float)($row['sum_rows_sent'] / $row['count_star'])
                : 0.0;

            // Flags
            $row['has_errors']              = !empty($row['sum_errors']);
            $row['has_warnings']            = !empty($row['sum_warnings']);
            $row['has_no_index_used']       = !empty($row['sum_no_index_used']) && $row['sum_no_index_used'] > 0;
            $row['has_no_good_index_used']  = !empty($row['sum_no_good_index_used']) && $row['sum_no_good_index_used'] > 0;
            $row['has_table_scan']          = $row['has_no_index_used'] || $row['has_no_good_index_used'];

            // Ratios normalisés (par requête) sur la période
            $count = $row['count_star'];

            $row['errors_ratio'] = ($count > 0 && !empty($row['sum_errors']))
                ? round($row['sum_errors'] / $count, 2)
                : 0.00;

            $row['warnings_ratio'] = ($count > 0 && !empty($row['sum_warnings']))
                ? round($row['sum_warnings'] / $count, 2)
                : 0.00;

            $row['no_index_ratio'] = ($count > 0 && !empty($row['sum_no_index_used']))
                ? round($row['sum_no_index_used'] / $count, 2)
                : 0.00;

            $row['no_good_index_ratio'] = ($count > 0 && !empty($row['sum_no_good_index_used']))
                ? round($row['sum_no_good_index_used'] / $count, 2)
                : 0.00;

            // Wrong index indicator
            $line = $data['queries'][$id_digest_schema] ?? [];
            $digest_text = $line['digest_text'] ?? '';
            if (strtoupper(substr(ltrim($digest_text), 0, 6)) === 'SELECT' && !$row['has_table_scan']) {
                $examined = $row['sum_rows_examined'] ?? 0;
                $sent = $row['sum_rows_sent'] ?? 0;
                if ($examined > 0 && ($sent / $examined) > 0.1) {
                    $row['has_wrong_index'] = true;
                    $row['wrong_index_ratio'] = ($sent / $examined);
                }
            }

            $digest_rows[$id_digest_schema] = $row;
        }

        // TRI (comme avant)
        $rows = $digest_rows;
        uasort($rows, function ($a, $b) use ($sort, $order) {

            if ($sort === 'rows_query') {
                $cmp = $a['rows_query'] <=> $b['rows_query'];
            } else {
                $aVal = $a[$sort] ?? 0;
                $bVal = $b[$sort] ?? 0;
                $cmp = $aVal <=> $bVal;
            }

            return ($order === 'asc') ? $cmp : -$cmp;
        });

        // On le remet dans la structure $query[$id]['@digest'] pour la vue existante
        $query = [];
        $query[$id_mysql_server]['@digest'] = $rows;

        // Récupérer les infos texte (schema_name, digest, digest_text) pour les id présents
        $ids_digest_schema = array_keys($rows);
        $data['queries']   = [];

        if (!empty($ids_digest_schema)) {
            $in = implode(',', array_map('intval', $ids_digest_schema));

            $sql = "
                SELECT
                    m.id                                   AS id_mysql_database__mysql_digest,
                    db.schema_name                         AS schema_name,
                    d.digest                               AS digest,
                    d.digest_text                          AS digest_text
                FROM mysql_database__mysql_digest m
                INNER JOIN mysql_database db ON db.id = m.id_mysql_database
                INNER JOIN mysql_digest   d  ON d.id  = m.id_mysql_digest
                WHERE m.id_mysql_server = ".$id_mysql_server."
                AND m.id IN (".$in.")
            ";

            $res = $db->sql_query($sql);
            while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $id = (int)$arr['id_mysql_database__mysql_digest'];
                $data['queries'][$id] = $arr;
            }
        }

        // (optionnel) : info performance_schema si tu veux garder le warning
        $elems = Extraction2::display(["performance_schema"], [$id_mysql_server]);
        $data['performance_schema'] = $elems[$id_mysql_server]['performance_schema'] ?? 0;

        // Passage à la vue
        $this->set('id_mysql_server', $id_mysql_server);
        $this->set('data', $data);
        $this->set('param', $param);
        $this->set('query', $query);
        $this->set('sort', $sort);
        $this->set('order', $order);
        $this->set('window', $window);
        $this->set('start_date', $start_date);
        $this->set('end_date', $end_date);
    }






}
/*
SELECT 
  (
    SELECT 
      VARIABLE_VALUE 
    FROM 
      INFORMATION_SCHEMA.GLOBAL_STATUS 
    WHERE 
      VARIABLE_NAME = 'WSREP_LOCAL_STATE'
  ) wsrep_local_state, 
  @@read_only read_only, 
  (
    SELECT 
      VARIABLE_VALUE 
    FROM 
      INFORMATION_SCHEMA.GLOBAL_STATUS 
    WHERE 
      VARIABLE_NAME = 'WSREP_LOCAL_RECV_QUEUE'
  ) wsrep_local_recv_queue, 
  (
    SELECT
      VARIABLE_VALUE
    FROM
      INFORMATION_SCHEMA.GLOBAL_VARIABLES
    WHERE
      VARIABLE_NAME = 'WSREP_DESYNC'
  ) wsrep_desync,
  (
    SELECT
      VARIABLE_VALUE
    FROM
      INFORMATION_SCHEMA.GLOBAL_VARIABLES
    WHERE
      VARIABLE_NAME = 'WSREP_REJECT_QUERIES'
  ) wsrep_reject_queries,
  (
    SELECT
      VARIABLE_VALUE
    FROM
      INFORMATION_SCHEMA.GLOBAL_VARIABLES
    WHERE
      VARIABLE_NAME = 'WSREP_SST_DONOR_REJECTS_QUERIES'
  ) wsrep_sst_donor_rejects_queries,
  (
    SELECT
      VARIABLE_VALUE
    FROM
      INFORMATION_SCHEMA.GLOBAL_STATUS
    WHERE
      VARIABLE_NAME = 'WSREP_CLUSTER_STATUS'
  ) wsrep_cluster_status,
  (
    SELECT
      'DISABLED'
  ) pxc_maint_mode |
SELECT
  (
    SELECT
      `VARIABLE_VALUE`
    FROM
      `INFORMATION_SCHEMA`.`GLOBAL_STATUS`
    WHERE
      `VARIABLE_NAME` = ?
  ) `wsrep_local_state`,
  @@READ_ONLY READ_ONLY,
  (
    SELECT
      `VARIABLE_VALUE`
    FROM
      `INFORMATION_SCHEMA`.`GLOBAL_STATUS`
    WHERE
      `VARIABLE_NAME` = ?
  ) `wsrep_local_recv_queue`,
  (
    SELECT
      `VARIABLE_VALUE`
    FROM
      `INFORMATION_SCHEMA`.`GLOBAL_VARIABLES`
    WHERE
      `VARIABLE_NAME` = ?
  ) `wsrep_desync`,
  (
    SELECT
      `VARIABLE_VALUE`
    FROM
      `INFORMATION_SCHEMA`.`GLOBAL_VARIABLES`
    WHERE
      `VARIABLE_NAME` = ?
  ) `wsrep_reject_queries`,
  (
    SELECT
      `VARIABLE_VALUE`
    FROM
      `INFORMATION_SCHEMA`.`GLOBAL_VARIABLES`
    WHERE
      `VARIABLE_NAME` = ?
  ) `wsrep_sst_donor_rejects_queries`,
  (
    SELECT 
      `VARIABLE_VALUE` 
    FROM 
      `INFORMATION_SCHEMA`.`GLOBAL_STATUS` 
    WHERE 
      `VARIABLE_NAME` = ?
  ) `wsrep_cluster_status`, 
  (
    SELECT 
      ?
  ) `pxc_maint_mode`


SELECT CURRENT_SCHEMA, DIGEST, SQL_TEXT FROM performance_schema.events_statements_history 
WHERE DIGEST IS NOT NULL AND CURRENT_SCHEMA IS NOT NULL 
GROUP BY CURRENT_SCHEMA, DIGEST, SQL_TEXT



*/
