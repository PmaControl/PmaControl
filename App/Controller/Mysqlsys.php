<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;
use App\Library\Security\CsrfGuard;
use App\Library\Http\HttpResponse;
use App\Library\Mysql;
use App\Library\MysqlVersion;
use App\Library\Debug;
use Glial\Security\Csrf;


/**
 * Class responsible for mysqlsys workflows.
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
class Mysqlsys extends Controller {

    use \App\Library\Filter;

    private const MYSQLSYS_INSTALL_CSRF_SCOPE = 'mysqlsys.install';
    private const MYSQLSYS_UPDATE_CONFIG_CSRF_SCOPE = 'mysqlsys.update_config';
    private const MYSQLSYS_UPDATE_CONFIG_NAME_MAX_LENGTH = 128;
    private const MYSQLSYS_UPDATE_CONFIG_VALUE_MAX_LENGTH = 4096;

/**
 * Render mysqlsys state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/mysqlsys/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index() {

        $this->title = '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> ' . "MySQL-sys";

        $db = Sgbd::sql(DB_DEFAULT);
        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));

        $outcome = self::evaluateIndexRequest($_SERVER);
        if (!$outcome['allowed']) {
            HttpResponse::sendOutcome($outcome, null);
            return;
        }

        $data = [];
        $selectedMysqlServerId = self::normalizeIndexMysqlServerId($_GET);
        $data['selected_mysql_server_id'] = $selectedMysqlServerId;
        $data['selected_mysql_server_found'] = false;
        $data['variables'] = '';
        $data['mysqlsys_version_unsupported'] = false;

        // get server available
        $available = Common::getAvailable();
        $case = $available['case'];

        $sql = "SELECT *,".$case." FROM mysql_server a WHERE 1=1 " . self::getFilter() . " order by a.name ASC";

        $res = $db->sql_query($sql);
        $data['servers'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name . " (" . $ob->ip . ")";
            $data['servers'][] = $tmp;

            if ($selectedMysqlServerId !== null && (int) $ob->id === $selectedMysqlServerId) {
                $link_name = $ob->name;
                $data['selected_mysql_server_found'] = true;
            }
        }

        //Debug::debug($link_name);

        if (!empty($link_name)) {
            $id_mysql_server = $selectedMysqlServerId;

            $remote = Sgbd::sql($link_name);
            $sql = "select TABLE_NAME from information_schema.tables "
                    . "WHERE table_schema = 'sys' and table_name not like 'x$%' ORDER BY table_name ASC;";
            $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($remote, $sql, $id_mysql_server, __METHOD__);
            $data['view_available'] = [];
            while ($ob = $remote->sql_fetch_object($res)) {
                //$data['view_available'][] = str_replace('x$','',$ob->table_name);
                $data['view_available'][] = $ob->TABLE_NAME;
            }

            //test if InnoDB activated
            $sql = "select * from information_schema.engines where engine = 'InnoDB';";
            $res = $remote->sql_query($sql);

            $data['innodb'] = 0;
            while ($ob = $remote->sql_fetch_object($res)) {
                if ($ob->SUPPORT == "YES" || $ob->SUPPORT == "DEFAULT") {
                    $data['innodb'] = 1;
                }
            }

            //test if spider / rocksdb etc...
            if (!empty($_GET['mysqlsys']) && in_array($_GET['mysqlsys'], $data['view_available'])) {

            //patch
            //$sql = "UPDATE sys.sys_config SET value = '100000' where variable ='statement_truncate_len';";
            //$remote->sql_query($sql);
            //fin patch
                if ($remote->checkVersion(array('MariaDB'=> '10.1.1'))) {
                    $sql = "SET STATEMENT MAX_STATEMENT_TIME = 10 FOR SELECT * FROM `sys`.`" . $_GET['mysqlsys'] . "` LIMIT 200";
                }
                else {
                    $sql = "SELECT * FROM `sys`.`" . $_GET['mysqlsys'] . "` LIMIT 200";
                }
                
                $data['table'] = $remote->sql_fetch_yield($sql);
                if ($_GET['mysqlsys'] === 'schema_unused_indexes') {
                    $data['table'] = $this->enrichSchemaUnusedIndexes(
                        $remote,
                        iterator_to_array($data['table'], false),
                        $id_mysql_server
                    );
                }
                $data['name_table'] = $_GET['mysqlsys'];
            }

            $data['variables'] = $remote->getVersion();
            $data['mysqlsys_version_unsupported'] = self::isMysqlSysUnsupportedVersion($data['variables']);
        }
        $data['mysqlsys_update_config_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['mysqlsys_update_config_csrf_token'] = Csrf::issueToken($_SESSION, self::MYSQLSYS_UPDATE_CONFIG_CSRF_SCOPE);
        $this->set('data', $data);
    }

    public static function evaluateIndexRequest(array $server): array
    {
        if (CsrfGuard::isPost($server)) {
            return [
                'allowed' => false,
                'status' => 405,
                'body' => 'Method Not Allowed',
                'headers' => ['Allow' => 'GET'],
            ];
        }

        return [
            'allowed' => true,
            'status' => 200,
            'body' => '',
            'headers' => [],
        ];
    }

    public static function normalizeIndexMysqlServerId(array $get): ?int
    {
        if (
            empty($get['mysql_server'])
            || !is_array($get['mysql_server'])
            || !array_key_exists('id', $get['mysql_server'])
        ) {
            return null;
        }

        return self::normalizePositiveInteger($get['mysql_server']['id']);
    }

    public static function isMysqlSysUnsupportedVersion(?string $version): bool
    {
        return MysqlVersion::compare($version, '5.6', '<=');
    }

    private function enrichSchemaUnusedIndexes($remote, array $rows, int $idMysqlServer): array
    {
        if (empty($rows)) {
            return $rows;
        }

        $stats = $this->fetchSchemaUnusedIndexTableStats($remote, $rows, $idMysqlServer);

        return self::appendSchemaUnusedIndexEstimates($rows, $stats);
    }

    private function fetchSchemaUnusedIndexTableStats($remote, array $rows, int $idMysqlServer): array
    {
        $conditions = [];
        foreach ($rows as $row) {
            if (empty($row['object_schema']) || empty($row['object_name'])) {
                continue;
            }

            $schema = $remote->sql_real_escape_string($row['object_schema']);
            $table = $remote->sql_real_escape_string($row['object_name']);
            $conditions[] = "(t.TABLE_SCHEMA = '".$schema."' AND t.TABLE_NAME = '".$table."')";
        }

        $conditions = array_values(array_unique($conditions));
        if (empty($conditions)) {
            return [];
        }

        $sql = "SELECT t.TABLE_SCHEMA AS object_schema,
                       t.TABLE_NAME AS object_name,
                       t.TABLE_ROWS AS table_rows,
                       t.INDEX_LENGTH AS table_index_bytes,
                       COUNT(DISTINCT CASE WHEN s.INDEX_NAME <> 'PRIMARY' THEN s.INDEX_NAME END) AS secondary_index_count
                FROM information_schema.TABLES t
                LEFT JOIN information_schema.STATISTICS s
                  ON s.TABLE_SCHEMA = t.TABLE_SCHEMA
                 AND s.TABLE_NAME = t.TABLE_NAME
                WHERE ".implode(' OR ', $conditions)."
                GROUP BY t.TABLE_SCHEMA, t.TABLE_NAME, t.TABLE_ROWS, t.INDEX_LENGTH";

        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($remote, $sql, $idMysqlServer, __METHOD__);
        $stats = [];
        while ($row = $remote->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $stats[self::schemaTableKey($row['object_schema'], $row['object_name'])] = [
                'table_rows' => $row['table_rows'],
                'table_index_bytes' => $row['table_index_bytes'],
                'secondary_index_count' => $row['secondary_index_count'],
            ];
        }

        return $stats;
    }

    private static function appendSchemaUnusedIndexEstimates(array $rows, array $stats): array
    {
        foreach ($rows as $key => $row) {
            $schema = (string)($row['object_schema'] ?? '');
            $table = (string)($row['object_name'] ?? '');
            $stat = $stats[self::schemaTableKey($schema, $table)] ?? null;

            if ($stat === null) {
                $rows[$key]['table_rows'] = 'n/a';
                $rows[$key]['table_index_size'] = 'n/a';
                $rows[$key]['estimated_gain'] = 'n/a';
                continue;
            }

            $tableRows = is_numeric($stat['table_rows']) ? (int)$stat['table_rows'] : 0;
            $indexBytes = is_numeric($stat['table_index_bytes']) ? (int)$stat['table_index_bytes'] : 0;
            $secondaryIndexCount = is_numeric($stat['secondary_index_count'])
                ? max(0, (int)$stat['secondary_index_count'])
                : 0;
            $estimatedGain = $secondaryIndexCount > 0 ? (int)ceil($indexBytes / $secondaryIndexCount) : 0;

            $rows[$key]['table_rows'] = number_format($tableRows, 0, '.', ' ');
            $rows[$key]['table_index_size'] = self::formatBytes($indexBytes);
            $rows[$key]['estimated_gain'] = $estimatedGain > 0 ? self::formatBytes($estimatedGain) : 'n/a';
        }

        return $rows;
    }

    private static function schemaTableKey(string $schema, string $table): string
    {
        return $schema.'.'.$table;
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 o';
        }

        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        $factor = min(count($units) - 1, (int)floor(log($bytes, 1024)));

        return number_format($bytes / (1024 ** $factor), 2, '.', ' ').' '.$units[$factor];
    }


/**
 * Handle mysqlsys state through `install`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for install.
 * @phpstan-return void
 * @psalm-return void
 * @see self::install()
 * @example /fr/mysqlsys/install
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function install() {
        $this->title = '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> ' . "MySQL-sys";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > ' . $this->title . ' > <i style="font-size: 16px" class="fa fa-upload"></i> Install';

        $db = Sgbd::sql(DB_DEFAULT);

        $idMysqlServer = self::normalizeInstallMysqlServerId($_GET);
        if ($idMysqlServer === null) {
            http_response_code(400);
            echo 'Invalid MySQL server id';
            return;
        }

        if (CsrfGuard::isPost($_SERVER)) {
            $outcome = self::evaluateInstallRequest($_POST, $_SERVER, $_SESSION, IS_CLI);
            if (!$outcome['allowed']) {
                HttpResponse::sendOutcome($outcome, null);
                return;
            }
        }

        $sql = "SELECT * FROM mysql_server where id=" . $idMysqlServer;
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $idMysqlServer, __METHOD__);

//test si "vendor/esysteme/mysql-sys/gen/" est crée et writable


        $data = [
            'mysqlsys_install_csrf_field' => Csrf::DEFAULT_FIELD,
            'mysqlsys_install_csrf_token' => Csrf::issueToken($_SESSION, self::MYSQLSYS_INSTALL_CSRF_SCOPE),
        ];
        while ($ob = $db->sql_fetch_object($res)) {
            $cmd = self::buildGenerateSqlFileCommand((string) $ob->login, ROOT);
            $ret = shell_exec($cmd);

            $data['file_name'] = self::extractGeneratedSqlFileName((string) $ret);
            if ($data['file_name'] === '') {
                http_response_code(500);
                echo 'Unable to generate MySQL-sys install file';
                return;
            }

            if (CsrfGuard::isPost($_SERVER)) {

                Crypt::$key = CRYPT_KEY;

                $cmd = self::buildMysqlSysInstallCommand(
                    (string) $ob->ip,
                    (string) $ob->login,
                    (int) $ob->port,
                    Crypt::decrypt($ob->passwd),
                    $data['file_name']
                );
                $ret = shell_exec($cmd);

                if (!empty($ret)) {

                    header('location: ' . LINK . 'mysqlsys/install/mysql_server:id:' . $idMysqlServer . '/error_msg:' . base64_encode($ret) . '/');
                } else {
                    header('location: ' . LINK . 'mysqlsys/index/mysql_server:id:' . $idMysqlServer);
                }
                return;
            } else {
                $data['file'] = file_get_contents($data['file_name']);
            }
        }

        $this->set('data', $data);
    }

    public static function evaluateInstallRequest(
        array $post,
        array $server,
        array $session,
        bool $isCli = false
    ): array {
        if (!$isCli) {
            $guard = CsrfGuard::check($post, $server, $session, self::MYSQLSYS_INSTALL_CSRF_SCOPE);
            if (!$guard['allowed']) {
                return [
                    'allowed' => false,
                    'status' => $guard['status'],
                    'body' => $guard['body'],
                    'headers' => $guard['headers'],
                    'install' => false,
                ];
            }
        }

        if (!self::normalizeInstallPayload($post)) {
            return [
                'allowed' => false,
                'status' => 400,
                'body' => 'Invalid MySQL-sys install payload',
                'headers' => [],
                'install' => false,
            ];
        }

        return [
            'allowed' => true,
            'status' => 200,
            'body' => '',
            'headers' => [],
            'install' => true,
        ];
    }

    public static function normalizeInstallMysqlServerId(array $get): ?int
    {
        if (
            empty($get['mysql_server'])
            || !is_array($get['mysql_server'])
            || !array_key_exists('id', $get['mysql_server'])
        ) {
            return null;
        }

        return self::normalizePositiveInteger($get['mysql_server']['id']);
    }

    public static function buildGenerateSqlFileCommand(string $login, string $root): string
    {
        $mysqlUser = "'" . str_replace("'", "\\'", $login) . "'@'localhost'";

        return 'cd ' . escapeshellarg(rtrim($root, '/') . '/vendor/esysteme/mysql-sys')
            . ' && ./generate_sql_file.sh -v 100 -u ' . escapeshellarg($mysqlUser) . ' 2>&1';
    }

    public static function buildMysqlSysInstallCommand(
        string $host,
        string $login,
        int $port,
        string $password,
        string $fileName
    ): string {
        return 'mysql -h ' . escapeshellarg($host)
            . ' -u ' . escapeshellarg($login)
            . ' -P ' . $port
            . ' -p' . escapeshellarg($password)
            . ' < ' . escapeshellarg($fileName) . ' 2>&1';
    }

    public static function extractGeneratedSqlFileName(string $output): string
    {
        foreach (explode("\n", $output) as $line) {
            if (strpos($line, 'Wrote file:') === 0) {
                return trim(str_replace('Wrote file:', '', $line));
            }
        }

        return '';
    }

    public static function normalizeInstallPayload(array $post): bool
    {
        return array_key_exists('install', $post)
            && is_scalar($post['install'])
            && trim((string) $post['install']) === '1';
    }

/**
 * Create mysqlsys state through `addFormat`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $tab Input value for `tab`.
 * @phpstan-param mixed $tab
 * @psalm-param mixed $tab
 * @return void Returned value for addFormat.
 * @phpstan-return void
 * @psalm-return void
 * @see self::addFormat()
 * @example /fr/mysqlsys/addFormat
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function addFormat($tab) {
        foreach ($tab as $key => $elem) {
            if ($key == "value") {
                yield "gg";
            }
        }
    }

/**
 * Handle mysqlsys state through `reset`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for reset.
 * @phpstan-return void
 * @psalm-return void
 * @see self::reset()
 * @example /fr/mysqlsys/reset
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function reset($param) {

        $this->view = false;
        $this->layout_name = false;


        $id_mysql_server = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);


// get server available

        $available = Common::getAvailable();
        $case = $available['case'];

        $sql = "SELECT name, ".$case." FROM mysql_server a WHERE 1=1 " . self::getFilter() . " AND id=" . $id_mysql_server;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $remote = Sgbd::sql($ob->name);

            $sql = "set sql_log_bin=0; call `sys`.ps_truncate_all_tables(false);";
            $remote->sql_multi_query($sql);
        }

        //$msg = I18n::getTranslation(__("The statistics has been reseted"));
        //$title = I18n::getTranslation(__("Success"));
        //set_flash("success", $title, $msg);

        header("location: " . $_SERVER['HTTP_REFERER']);
    }

/**
 * Handle mysqlsys state through `drop`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for drop.
 * @phpstan-return void
 * @psalm-return void
 * @see self::drop()
 * @example /fr/mysqlsys/drop
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function drop($param) {

        $this->view = false;
        $this->layout_name = false;


        $id_mysql_server = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);


// get server available
        $sql = "SELECT name FROM mysql_server a WHERE 1=1 " . self::getFilter() . " AND id=" . $id_mysql_server;
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            $remote = Sgbd::sql($ob->name);

            $sql = "set sql_log_bin=0; DROP DATABASE IF EXISTS `sys`;";
            $remote->sql_multi_query($sql);
        }

        $msg = I18n::getTranslation(__("MySQL-sys has been uninstalled"));
        $title = I18n::getTranslation(__("Success"));
        set_flash("success", $title, $msg);

        header("location: " . $_SERVER['HTTP_REFERER']);
    }

/**
 * Update mysqlsys state through `updateConfig`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for updateConfig.
 * @phpstan-return void
 * @psalm-return void
 * @see self::updateConfig()
 * @example /fr/mysqlsys/updateConfig
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function updateConfig($param) {
        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateUpdateConfigRequest($_POST, $_SERVER, $_SESSION, IS_CLI);
        if (!$outcome['allowed']) {
            HttpResponse::sendOutcome($outcome, null);
            return;
        }

        $config = $outcome['config'];
        $db = Mysql::getDbLink($config['id_mysql_server']);

        $sql = self::buildUpdateConfigSql($config, [$db, 'sql_real_escape_string']);
        $db->sql_query($sql);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            header("HTTP/1.0 503 Internal Server Error");
        }
    }

    public static function evaluateUpdateConfigRequest(
        array $post,
        array $server,
        array $session,
        bool $isCli = false
    ): array {
        if (!$isCli) {
            $guard = CsrfGuard::check($post, $server, $session, self::MYSQLSYS_UPDATE_CONFIG_CSRF_SCOPE);
            if (!$guard['allowed']) {
                return [
                    'allowed' => false,
                    'status' => $guard['status'],
                    'body' => $guard['body'],
                    'headers' => $guard['headers'],
                    'config' => null,
                ];
            }
        }

        $config = self::normalizeUpdateConfigPayload($post);
        if ($config === null) {
            return [
                'allowed' => false,
                'status' => 400,
                'body' => 'Invalid MySQL-sys config payload',
                'headers' => [],
                'config' => null,
            ];
        }

        return [
            'allowed' => true,
            'status' => 200,
            'body' => '',
            'headers' => [],
            'config' => $config,
        ];
    }

    public static function normalizeUpdateConfigPayload(array $post): ?array
    {
        if (
            !array_key_exists('pk', $post)
            || !array_key_exists('name', $post)
            || !array_key_exists('value', $post)
            || !is_scalar($post['pk'])
            || !is_scalar($post['name'])
            || !is_scalar($post['value'])
        ) {
            return null;
        }

        $idMysqlServer = self::normalizePositiveInteger($post['pk']);
        $name = self::normalizeSysConfigName($post['name']);
        $value = (string) $post['value'];

        if (
            $idMysqlServer === null
            || $name === null
            || strlen($value) > self::MYSQLSYS_UPDATE_CONFIG_VALUE_MAX_LENGTH
        ) {
            return null;
        }

        return [
            'id_mysql_server' => $idMysqlServer,
            'name' => $name,
            'value' => $value,
        ];
    }

    public static function buildUpdateConfigSql(array $config, callable $escape): string
    {
        return "UPDATE sys.sys_config SET `value` = '".$escape($config['value'])."' "
            ."WHERE `variable` = '".$escape($config['name'])."'";
    }

    private static function normalizePositiveInteger($value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || !ctype_digit($value) || (int) $value < 1) {
            return null;
        }

        return (int) $value;
    }

    private static function normalizeSysConfigName($value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $name = trim((string) $value);
        if (
            $name === ''
            || strlen($name) > self::MYSQLSYS_UPDATE_CONFIG_NAME_MAX_LENGTH
            || preg_match('/^[A-Za-z0-9_.-]+$/', $name) !== 1
        ) {
            return null;
        }

        return $name;
    }


/**
 * Handle mysqlsys state through `export`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for export.
 * @phpstan-return void
 * @psalm-return void
 * @see self::export()
 * @example /fr/mysqlsys/export
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function export($param)
    {
        //$this->view = true;
        $this->layout_name = false;

        $_GET['ajax'] = true;
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $rapport = $param[1];
        $where = "";

        $db = Mysql::getDbLink($id_mysql_server, "mysqlsys");

        $def = Sgbd::sql(DB_DEFAULT);
        $sql2 = "SELECT * FROM mysqlsys_config_export WHERE rapport ='".$rapport."'";
        $res2 = $def->sql_query($sql2);
        
        $select = '*';
        $limit = 50;
        while ($arr = $def->sql_fetch_array($res2, MYSQLI_ASSOC))
        {
            $select = $arr['select'];
            $limit = $arr['limit'];
        }




        switch ($rapport)
        {

            /*
            case 'schema_auto_increment_columns':
                $sql = "SELECT ".$select." FROM `sys`.`".$rapport."` WHERE auto_increment_ratio > 0.5 LIMIT ".$limit;
                break;
*/

            case 'engines':
                $sql ="SELECT 
  ENGINE as Engine, 
  group_concat(distinct table_schema) AS `Database`, 
  ROUND( SUM(INDEX_LENGTH) / 1024 / 1024 / 1024, 2 ) AS `Index (Go)`, 
  ROUND( SUM(DATA_LENGTH) / 1024 / 1024 / 1024, 2 ) AS `data (Go)`, 
  ROUND( SUM(DATA_FREE) / 1024 / 1024 / 1024, 2 ) AS `Free (Go)`, 
  COUNT(*) AS `Tables`, 
  SUM(TABLE_ROWS) as `Rows (sum)`
FROM 
  information_schema.tables 
WHERE 
  TABLE_SCHEMA NOT IN (
    'mysql', 'performance_schema', 'information_schema', 
    'sys'
  ) 
  AND TABLE_TYPE NOT IN ('SYSTEM VIEW', 'VIEW') 
GROUP BY 
  ENGINE;";
                break;


            case 'top10tables':

                $sql = "SELECT
  t.TABLE_SCHEMA AS 'Schéma',
  t.TABLE_NAME AS 'Table',
  ROUND(t.DATA_LENGTH / 1024 / 1024 / 1024, 2) AS 'Données (Go)',
  ROUND(t.INDEX_LENGTH / 1024 / 1024 / 1024, 2) AS 'Index (Go)',
  ROUND(t.DATA_FREE / 1024 / 1024 / 1024, 2) AS 'Libre (Go)',
  t.TABLE_ROWS AS 'Nb lignes',
  t.ENGINE AS 'Moteur',
  COUNT(s.INDEX_NAME) AS 'Nb index'
FROM
  information_schema.TABLES t
LEFT JOIN
  information_schema.STATISTICS s
  ON t.TABLE_SCHEMA = s.TABLE_SCHEMA AND t.TABLE_NAME = s.TABLE_NAME
GROUP BY
  t.TABLE_SCHEMA, t.TABLE_NAME
ORDER BY
  (t.DATA_LENGTH + t.INDEX_LENGTH) DESC
LIMIT 10;";

                break;

            case "alter_table_redundant_index":

                break;

            case "statements_with_errors_or_warnings__errors":

                $sql = "SELECT LEFT(query,60),db,exec_count,errors,error_pct,last_seen 
                FROM `sys`.`statements_with_errors_or_warnings` 
                WHERE errors != 0 AND db NOT IN ('sys', 'mysql'); ";
                break;
            
            case "statements_with_errors_or_warnings__warnings":

                $sql = "SELECT LEFT(query,60),db,exec_count,warnings,warning_pct,last_seen 
                FROM `sys`.`statements_with_errors_or_warnings` 
                WHERE warnings != 0 AND db NOT IN ('sys', 'mysql'); ";
                break;


            default:

                


                $sql = "SELECT ".$select." FROM `sys`.`".$rapport."` LIMIT ".$limit;

            break;
        }


        
        $res = $db->sql_query($sql);

        $data['export'] = array();
        while ($arr = $db->sql_fetch_array($res , MYSQLI_ASSOC))
        {
            $data['export'][] = $arr;
        }

        //Debug::debug($data['export']);

        $this->set('data', $data);/***/

    }




}
