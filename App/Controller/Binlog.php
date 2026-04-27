<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use App\Library\Extraction;
use App\Library\Extraction2;
use App\Library\Security\BinlogAddRequest;
use App\Library\Security\CsrfGuard;
use App\Library\Mysql;
use Glial\Security\Crypt\Crypt;
use Glial\Security\Csrf;
use App\Library\Display;
use \Glial\Sgbd\Sgbd;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


/**
 * Class responsible for binlog workflows.
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
class Binlog extends Controller {

    use \App\Library\Filter;

    CONST DELAIS_DE_RETENTION = 172800; //48 heures en secondes
    CONST DIRECTORY_BACKUP = '/data/backup/binlog';
    private const BINLOG_ADD_CSRF_SCOPE = 'binlog.add';

/**
 * Stores `$logger` for logger.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $logger;

/**
 * Render binlog state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/binlog/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {
        $data = array();
        $this->set('data', $data);
    }

/**
 * Prepare binlog state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/binlog/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function before($param)
    {
        $monolog       = new Logger("Binlog");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

/**
 * Create binlog state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/binlog/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add() {
        $this->di['js']->addJavascript(array('Binlog/index.js'));

        if (CsrfGuard::isPost($_SERVER)) {
            $outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);
            if ($outcome['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendBinlogAddError($outcome['status'], $outcome['body'], $outcome['headers']);
                return;
            }

            $db = Sgbd::sql(DB_DEFAULT);
            $db->sql_query(self::buildBinlogAddSql($outcome['binlog']));

            header('location: ' . LINK . $this->getClass() . '/index');
            return;
        }

        $data = array();
        $data['binlog_add_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['binlog_add_csrf_token'] = Csrf::issueToken($_SESSION, self::BINLOG_ADD_CSRF_SCOPE);

        $this->set('data', $data);
    }

    public static function evaluateAddRequest(array $post, array $server, array $session): array
    {
        return BinlogAddRequest::evaluate($post, $server, $session, self::BINLOG_ADD_CSRF_SCOPE);
    }

    public static function normalizeAddPayload(array $post): ?array
    {
        return BinlogAddRequest::normalize($post);
    }

    public static function buildBinlogAddSql(array $payload): string
    {
        return BinlogAddRequest::buildReplaceSql($payload);
    }

    private static function sendBinlogAddError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle binlog state through `max`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for max.
 * @phpstan-return void
 * @psalm-return void
 * @see self::max()
 * @example /fr/binlog/max
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function max() {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM binlog_max a
            INNER JOIN mysql_server b ON b.id = a.id_mysql_server";

        $res = $db->sql_query($sql);

        $data = array();

        while ($arr = $db->sql_fetch_object($res)) {
            $data['binlog'] = $arr;
        }
    }

/**
 * Retrieve binlog state through `getMaxBinlogSize`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getMaxBinlogSize.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getMaxBinlogSize()
 * @example /fr/binlog/getMaxBinlogSize
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getMaxBinlogSize($param) {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $db = Sgbd::sql(DB_DEFAULT);

        $this->layout_name = false;
        $this->view = false;

        $res = Extraction::extract(array("variables::max_binlog_size"), array($id_mysql_server));

        $data = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $data['max_binlog_size'] = $ob->value;
        }

        if (!empty($data['max_binlog_size'])) {
            echo $data['max_binlog_size'];
        } else {
            echo "N/A";
        }
    }

/**
 * Handle binlog state through `view`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for view.
 * @phpstan-return void
 * @psalm-return void
 * @see self::view()
 * @example /fr/binlog/view
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function view($param) {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);


        // need to select only Available server
        $sql = "SELECT a.*, b.libelle as organization,c.*, a.id as id_mysql_server, d.*
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id
            INNER JOIN binlog_max d on a.id = d.id_mysql_server";

        //$sql ="select 1;";

        $res = $db->sql_query($sql);

        $data['extra'] = array();
        $mysql_server = array();

        $data['max_bin_log'] = array();

        $all_id_mysql_server = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $all_id_mysql_server[] = $arr['id_mysql_server'];
            $binlog_files = Extraction2::display(array("mysql_binlog::binlog_files"), array($arr['id_mysql_server']));

            if (empty($binlog_files[$arr['id_mysql_server']]['binlog_files'])) {
                continue;
            }

            $data['extra'][$arr['id_mysql_server']]['binary_logs']['file'] = $binlog_files[$arr['id_mysql_server']]['binlog_files'];

            $binlog_sizes = Extraction2::display(array("mysql_binlog::binlog_sizes"), array($arr['id_mysql_server']));
            $data['extra'][$arr['id_mysql_server']]['binary_logs']['size'] = $binlog_sizes[$arr['id_mysql_server']]['binlog_sizes'];

            $mysql_server[] = $arr['id_mysql_server'];

            $data['max_bin_log'][] = $arr;
        }

        //$res = Extraction::extract(array("binlog::max_binlog_size"), $all_id_mysql_server);
        /* */

        $res = Extraction::extract(array("variables::max_binlog_size"), $mysql_server);

        while ($ob = $db->sql_fetch_object($res)) {
            $data['extra'][$ob->id_mysql_server]['max_binlog_size'] = $ob->value;
        }

        $this->set('data', $data);
    }

/**
 * Handle binlog state through `search`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for search.
 * @phpstan-return void
 * @psalm-return void
 * @see self::search()
 * @example /fr/binlog/search
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function search($param) {
        
    }

/**
 * Handle binlog state through `backupAll`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for backupAll.
 * @phpstan-return void
 * @psalm-return void
 * @see self::backupAll()
 * @example /fr/binlog/backupAll
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function backupAll($param) {
        
    }

/**
 * Handle binlog state through `backup`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for backup.
 * @phpstan-return void
 * @psalm-return void
 * @see self::backup()
 * @example /fr/binlog/backup
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function backup($param) {
        
    }

/**
 * Handle binlog state through `backupServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for backupServer.
 * @phpstan-return void
 * @psalm-return void
 * @see self::backupServer()
 * @example /fr/binlog/backupServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function backupServer($param) {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);

        $id_mysql_server = intval($param[0]);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT d.*
            FROM mysql_server a
            INNER JOIN binlog_backup d on a.id = d.id_mysql_server 
            WHERE a.id = " . $id_mysql_server;

        $res = $db->sql_query($sql);

        //get available binlogs

        $last_binlog_file = array();
        $bin_backup = array();

        $last_file = '';
        $last_id = '';

        while ($ob = $db->sql_fetch_object($res)) {

            $bin_backup[$ob->logfile_name] = $ob->logfile_size;
            $last_file = $ob->logfile_name;
            $last_id = $ob->id;
        }


        if (!empty($last_file)) {
            $last_binlog_file[$last_file] = $last_id;
        }

        array_pop($bin_backup);
        Debug::debug($last_binlog_file, "last element");

        $result = Extraction::display(array("binlog::file_first", "binlog::file_last", "binlog::files", "binlog::sizes", "binlog::total_size", "binlog::nb_files"),
                        array($id_mysql_server));

        $binarylogs = $result[$id_mysql_server][''];
        $bin_logs = array_combine(json_decode($binarylogs['files'], true), json_decode($binarylogs['sizes'], true));

        Debug::debug($bin_backup, "Binlog Backuped");
        Debug::debug($bin_logs, "Binlog available");

        $bin_to_backup = array_diff_key($bin_logs, $bin_backup);

        Debug::debug($bin_to_backup, "Binlog to backup");

        if (count($bin_to_backup) > 0) {

            $sql = "SELECT * FROM mysql_server where id=" . $id_mysql_server;
            $res = $db->sql_query($sql);

            $directory = self::DIRECTORY_BACKUP . "/" . $id_mysql_server . "/";

            if (!file_exists($directory)) {
                $cmd = "mkdir -p " . $directory;

                shell_exec($cmd);
            }

            while ($server = $db->sql_fetch_object($res)) {
                foreach ($bin_to_backup as $file => $size) {

                    $password = Crypt::decrypt($server->passwd, CRYPT_KEY);

                    $cmd = "cd " . $directory . " && mysqlbinlog -R --raw --host=" . $server->ip . " -u " . $server->login . " -p" . $password . " " . $file;
                    Debug::debug($cmd);

                    $db->sql_close();

                    $gg = shell_exec($cmd);

                    if (empty($gg)) {

                        $db = Sgbd::sql(DB_DEFAULT);
                        $bck = array();

                        if (!empty($last_binlog_file[$file])) {

                            $bck['binlog_backup']['id'] = $last_binlog_file[$file];
                        }

                        $bck['binlog_backup']['id_mysql_server'] = $id_mysql_server;
                        $bck['binlog_backup']['logfile_name'] = $file;
                        $bck['binlog_backup']['logfile_size'] = $size;
                        $bck['binlog_backup']['md5'] = md5_file($directory . $file);
                        $bck['binlog_backup']['date_backup'] = date('Y-m-d H:i:s');

                        $err = $db->sql_save($bck);

                        if (!$err) {

                            Debug::debug($db->sql_error(), "Error Insert / update");
                        }

                        if (!empty($last_binlog_file[$file])) {
                            Debug($bck, $bck);
                        }

                        $db->sql_close();
                    }
                }
            }
        }
    }

/**
 * Handle binlog state through `purgeAll`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for purgeAll.
 * @phpstan-return void
 * @psalm-return void
 * @see self::purgeAll()
 * @example /fr/binlog/purgeAll
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function purgeAll($param) {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.id as id_mysql_server
            FROM mysql_server a
            INNER JOIN binlog_max d on a.id = d.id_mysql_server";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $this->purgeServer(array($ob->id_mysql_server));
        }
    }

    /* Purge les binlog qui dépasse la valeur max pour être juste en dessous */


    public function purgeServer($param) {
        Debug::parseDebug($param);

        $id_mysql_server = intval($param[0]);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT d.*
            FROM mysql_server a
            INNER JOIN binlog_max d on a.id = d.id_mysql_server WHERE a.id = " . $id_mysql_server;

        $res = $db->sql_query($sql);

        //only one server at once
        while ($ob = $db->sql_fetch_object($res)) {

            $result = Extraction::display(array("mysql_binlog::binlog_file_first", "mysql_binlog::binlog_file_last", "mysql_binlog::binlog_files",
             "mysql_binlog::binlog_sizes", "mysql_binlog::binlog_total_size", "mysql_binlog::binlog_nb_files"),array($ob->id_mysql_server));

            Debug::debug($result);

            if (empty($result[$ob->id_mysql_server]['']))
            {
                break;
            }

            $binarylogs = $result[$ob->id_mysql_server][''];

            if (empty($binarylogs['binlog_files'])) {
                break;
            }
            if (empty($binarylogs['binlog_sizes'])){
                break;
            }

            $bin_logs = array_combine(json_decode($binarylogs['binlog_files'], true), json_decode($binarylogs['binlog_sizes'], true));

            Debug::debug($binarylogs);

            $binlog_reverse = array_reverse($bin_logs, true);

            Debug::debug($binlog_reverse);

            $total_size = 0;

            $loop = 0;
            foreach ($binlog_reverse as $file => $size) {
                $total_size += $size;
                $loop++;

                if ($loop == 1) {
                    $file_previous = $file;
                    continue;
                }

                if ($total_size > $ob->size_max) {

                    $db_remote = Mysql::getDbLink($ob->id_mysql_server);

                    // MariaDB 10.6.1+: slave_connections_needed_for_purge blocks purge
                    // if no slave is connected. Set to 0 to allow purge regardless.
                    $isMariaDB = (stripos($db_remote->getServerType(), 'mariadb') !== false);
                    if ($isMariaDB) {
                        $db_remote->sql_query_silent("SET GLOBAL slave_connections_needed_for_purge = 0;");
                    }

                    $sql = "PURGE BINARY LOGS TO '" . $file_previous . "';";
                    Debug::sql($sql);
                    $this->logger->notice('We purged binary logs on id_mysql_server:'.$ob->id_mysql_server.' "'.$sql.'"');
                    $db_remote->sql_query($sql);
                    break;
                }

                $file_previous = $file;
            }
        }
    }

/**
 * Retrieve binlog state through `liste`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for liste.
 * @phpstan-return void
 * @psalm-return void
 * @see self::liste()
 * @example /fr/binlog/liste
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function liste($param) {

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $result = Extraction::display(array("mysql_binlog::binlog_file_first", "mysql_binlog::binlog_file_last", 
        "mysql_binlog::binlog_files","variables::binlog_expire_logs_seconds",
         "mysql_binlog::binlog_sizes", "mysql_binlog::binlog_total_size", "mysql_binlog::binlog_nb_files", "variables::expire_logs_days"));

        $sql = "SELECT a.*, b.libelle as organization,c.*, d.*, a.id as id_mysql_server
            FROM mysql_server a
            INNER JOIN client b ON a.id_client = b.id
            INNER JOIN environment c ON a.id_environment = c.id
            LEFT JOIN binlog_max d on a.id = d.id_mysql_server
            WHERE 1 " . self::getFilter() . " AND a.is_proxy = 0
            ORDER BY display_name";

        //debug($sql);

        $res = $db->sql_query($sql);

        $data['server'] = array();
        $data['max_size'] = 0;

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            if (!empty($result[$arr['id_mysql_server']][''])) {

                $arr['mysql_binlog'] = $result[$arr['id_mysql_server']][''];
                Debug::debug($arr['mysql_binlog']);
            }

            $data['server'][] = $arr;
            $arr['mysql_binlog']['total_size'] = $arr['mysql_binlog']['total_size'] ?? 0;
        
            if (!empty($arr['mysql_binlog']['binlog_total_size'])) {
                if ($arr['mysql_binlog']['binlog_total_size'] > $data['max_size']) {
                    $data['max_size'] = $arr['mysql_binlog']['binlog_total_size'];
                }
            }
        }

        $this->set('data', $data);
    }

/* récupére une portion de binlog directement depuis le serveur */

    public function getBinlog($param) {

        //
        //Exec_Master_Log_Pos 65557710
        //end_log_pos         65558192


        $id_mysql_server = $param[0];
        $binlog_file = $param[1];
        $binlog_position_start = $param[2];
        $binlog_position_end = $param[3];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id=" . $id_mysql_server . ";";

        $res = $db->sql_query($sql);

        Crypt::$key = CRYPT_KEY;
        $password = Crypt::decrypt($ob->passwd);

        while ($ob = $db->sql_fetch_object($res)) {
            $cmd = "mysqlbinlog -R -h " . $ob->ip . " -u " . $ob->login . " -p" . $password . " -j " . $binlog_position_start . " --stop-position=" . $binlog_position_end . " $binlog_file";
        }



        //
    }

/**
 * Handle binlog state through `binlog2sql`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for binlog2sql.
 * @phpstan-return void
 * @psalm-return void
 * @see self::binlog2sql()
 * @example /fr/binlog/binlog2sql
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function binlog2sql($param) {
        
    }

/**
 * Retrieve binlog state through `getLastSqlError`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getLastSqlError.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getLastSqlError()
 * @example /fr/binlog/getLastSqlError
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getLastSqlError($param) {
        
    }

}

//glyphicon glyphicon-list
