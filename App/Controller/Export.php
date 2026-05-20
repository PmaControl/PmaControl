<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Chiffrement;
use \Glial\Security\Crypt\Crypt;
use \App\Library\Mysql;
use \App\Library\Json;
use \Glial\Sgbd\Sgbd;
use App\Library\Security\CsrfGuard;
use App\Library\Security\EncryptedExportRequest;
use Glial\Security\Csrf;

/*
for mysql_server => ADD SYSTEM VERSIONING PARTITION BY SYSTEM_TIME;
*/

class Export extends Controller
{
    private const EXPORT_CONF_CSRF_SCOPE = 'export.export_conf';
    private const EXPORT_IMPORT_CONF_CSRF_SCOPE = 'export.import_conf';
    private const EXPORT_TEST_DECHIFFREMENT_CSRF_SCOPE = 'export.test_dechiffrement';

/**
 * Stores `$table_with_data` for table with data.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $table_with_data        = array("translation_main", "geolocalisation_city",
        "geolocalisation_continent", "geolocalisation_country");
/**
 * Stores `$table_with_data_expand` for table with data expand.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $table_with_data_expand = array("menu", "menu_group", "history_etat", "ts_file","ts_variable","tag", "mysqlsys_config_export",
        "group", "environment", "daemon_main", "ts_variable", "dot3_legend","worker_queue","docker_software",
        "home_box", "backup_type", "export_option", "database_size", "mysql_type", "translation_google", "translation_glial", "benchmark_config",
    "ts_type_override");
/**
 * Stores `$exlude_table` for exlude table.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    var $exlude_table = array("translation_*", "slave_*", "master_*", "variables_*", "status_*", "ts_value_*", "ts_date_by_server", "sharding");

/**
 * Handle export state through `generateDump`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for generateDump.
 * @phpstan-return void
 * @psalm-return void
 * @see self::generateDump()
 * @example /fr/export/generateDump
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function generateDump($param)
    {
        Debug::parseDebug($param);
        $this->view = false;

        $db = Sgbd::sql(DB_DEFAULT);

        $tables = $db->getListTable()['table'];

        $table_with_data        = array();
        $table_with_data_expand = array();
        $table_without_data     = array();

        foreach ($tables as $key => $table) {
            if (in_array($table, $this->table_with_data)) {
                $table_with_data[] = $table;
                unset($tables[$key]);
            }
        }

        foreach ($tables as $key => $table) {
            if (in_array($table, $this->table_with_data_expand)) {
                $table_with_data_expand[] = $table;
                unset($tables[$key]);
            }
        }


        $to_remove = array();

        foreach ($tables as $key => $table) {

            foreach ($this->exlude_table as $table_to_exclude) {
                if (fnmatch($table_to_exclude, $table)) {
                    $to_remove[] = $table;
                }
            }
        }

        $table_without_data = array_diff($tables, $to_remove);

        Debug::debug($table_with_data);
        Debug::debug($table_with_data_expand);
        Debug::debug($table_without_data);

        $finalPath = ROOT.'/sql/full/pmacontrol.sql';

        // Issue #777 / N3: dump entirely in PHP via the existing mysqli
        // connection (no mysqldump binary, no shell pipeline, no
        // credentials file dance). NativeDumper::dump() throws a
        // RuntimeException on any failure — no more silent partial
        // dumps. See App/Library/Export/NativeDumper.php for details.
        try {
            \App\Library\Export\NativeDumper::dump(
                $db->link,
                (string) $db->getParams()['database'],
                $finalPath,
                $table_with_data,
                $table_with_data_expand,
                $table_without_data
            );
        } finally {
            // Bring ownership back to www-data so subsequent www-data
            // invocations (web UI, worker, …) can keep rewriting the
            // file. No-op when not root: chown fails silently.
            self::normalizeDumpOwnership($finalPath);
        }
    }

    /**
     * Best-effort: bring `sql/full/pmacontrol.sql` back to
     * `www-data:www-data 0664`. Only effective when the calling process
     * is root; otherwise the chown/chgrp calls fail silently and the
     * existing ownership is left in place.
     */
    public static function normalizeDumpOwnership(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        @chown($path, 'www-data');
        @chgrp($path, 'www-data');
        @chmod($path, 0664);
    }

/**
 * Render export state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/export/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function index()
    {

        $this->title = '<span class="glyphicon glyphicon-import"></span> '.__("Import / Export");

        $db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->code_javascript('
$("#export_all-all").click(function(){
    $(".form1 input:checkbox").not(this).prop("checked", this.checked);
});
$("#export_all-all2").click(function(){
    $(".form2 input:checkbox").not(this).prop("checked", this.checked);
});
');

        $sql = "SELECT * FROM `export_option` where active =1";

        $res = $db->sql_query($sql);

        $data['options'] = array();
        while ($arr             = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['options'][] = $arr;
        }

        $data['export_conf_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['export_conf_csrf_token'] = Csrf::issueToken($_SESSION, self::EXPORT_CONF_CSRF_SCOPE);
        $data['export_import_conf_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['export_import_conf_csrf_token'] = Csrf::issueToken($_SESSION, self::EXPORT_IMPORT_CONF_CSRF_SCOPE);

        $this->set('data', $data);
    }

/**
 * Handle export state through `export_conf`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for export_conf.
 * @phpstan-return void
 * @psalm-return void
 * @see self::export_conf()
 * @example /fr/export/export_conf
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function export_conf()
    {

        $this->view = false;

        $exportPost = self::evaluateExportConfPost($_POST, $_SERVER, $_SESSION);
        if ($exportPost['status'] !== 200) {
            if ($exportPost['status'] === 403 || $exportPost['status'] === 405) {
                $this->layout_name = false;
                self::sendExportError($exportPost['status'], $exportPost['body'], $exportPost['headers']);
                return;
            }

            set_flash("error", __('Error'), __($exportPost['body']));
            header("location: ".LINK.$this->getClass()."/index");
            return;
        }

        $backup = $this->_export();

        $json = json_encode($backup);
        if (!is_string($json)) {
            set_flash("error", __('Error'), __("Unable to encode export configuration"));
            header("location: ".LINK.$this->getClass()."/index");
            return;
        }

        //file_put_contents("/tmp/json", $json);
        //$compressed = gzcompress($json, 9);


        Debug::debug($json, "JSON");

        $crypted = Chiffrement::encrypt($json, $exportPost['password']);

        //$file_name = $_POST['export']['name_file'];
        $file_name = "export_".date('Y-m-d').".pmactrl";
        $tmpFile = tempnam(sys_get_temp_dir(), 'pmactrl_export_');
        if ($tmpFile === false) {
            set_flash("error", __('Error'), __("Unable to write export file"));
            header("location: ".LINK.$this->getClass()."/index");
            return;
        }

        if (file_put_contents($tmpFile, $crypted) === false) {
            unlink($tmpFile);
            set_flash("error", __('Error'), __("Unable to write export file"));
            header("location: ".LINK.$this->getClass()."/index");
            return;
        }

        header("Content-Disposition: attachment; filename=\"".$file_name."\"");
        header("Content-Length: ".filesize($tmpFile));
        header("Content-Type: application/octet-stream;");
        readfile($tmpFile);
        unlink($tmpFile);
    }

    public static function evaluateExportConfPost(array $post, array $server, array $session): array
    {
        return EncryptedExportRequest::evaluatePasswordPairPost(
            $post,
            $server,
            $session,
            self::EXPORT_CONF_CSRF_SCOPE,
            'Invalid export password payload',
            'Export passwords do not match'
        );
    }

/**
 * Handle export state through `import_conf`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for import_conf.
 * @phpstan-return void
 * @psalm-return void
 * @see self::import_conf()
 * @example /fr/export/import_conf
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function import_conf($param)
    {
        $this->view = false;

        Debug::parseDebug($param);
        //Debug::$debug = true;


        $data = array();

        if (IS_CLI) {

            $file     = $param[0];
            $password = $param[1];

            Debug::debug($file, "file");
        } else {

            if (CsrfGuard::isPost($_SERVER)) {
                $importPost = self::evaluateImportConfPost($_FILES, $_POST, $_SERVER, $_SESSION);
                if ($importPost['status'] !== 200) {
                    if ($importPost['status'] === 403 || $importPost['status'] === 405) {
                        $this->layout_name = false;
                        self::sendExportError($importPost['status'], $importPost['body'], $importPost['headers']);
                        return;
                    }

                    set_flash("error", __('Error'), __($importPost['body']));
                    header("location: ".LINK.$this->getClass()."/index");
                    return;
                }

                $file = $importPost['file'];
                $password = $importPost['password'];
            } else {
                header("location: ".LINK.$this->getClass()."/index");
                return;
            }
        }


        if (!empty($file) && !empty($password)) {
            $json = EncryptedExportRequest::decryptFile($file, $password);
            if ($json === null) {
                set_flash("error", __('Error'), __("The password is not good"));
                header("location: ".LINK.$this->getClass()."/index");
                return;
            }

            Debug::debug($json, "json");
            try {
                $data = $this->import(array($json));
            } catch (\Throwable $exception) {
                set_flash("error", __('Error'), $exception->getMessage());
                header("location: ".LINK.$this->getClass()."/index");
                return;
            }

            Debug::debug($data);
        }

        if (!empty($data['mysql']['updated'])) {
            $msg = implode(", ", $data['mysql']['updated']);
            set_flash("success", __('Server updated'), $msg);
        }


        if (!IS_CLI) {

            if (!empty($data['mysql']['updated'])) {
                $msg = implode(", ", $data['mysql']['updated']);
                set_flash("success", __('Server updated'), $msg);
            }

            if (!empty($data['mysql']['inserted'])) {
                $msg = implode(", ", $data['mysql']['inserted']);
                set_flash("success", __('Server inserted'), $msg);
            }

            if (!empty($data['error'])) {
                $msg = "<ul><li>".implode("</li><li> ", $data['error'])."</li></ul>";
                set_flash("error", __('Error'), $msg);
            }

            header("location: ".LINK.$this->getClass()."/index");
            exit;
        }

        //debug($data);

        $this->set('data', $data);
    }

    public static function evaluateImportConfPost(
        array $files,
        array $post,
        array $server,
        array $session,
        bool $requireUploadedFile = true
    ): array {
        return EncryptedExportRequest::evaluateUploadPasswordPost(
            $files,
            $post,
            $server,
            $session,
            self::EXPORT_IMPORT_CONF_CSRF_SCOPE,
            'Invalid export import payload',
            $requireUploadedFile
        );
    }

/**
 * Handle export state through `import`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for import.
 * @phpstan-return void
 * @psalm-return void
 * @see self::import()
 * @example /fr/export/import
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function import($param)
    {
        //$param[] = "--debug";
        //debug($param);

        Debug::debug(DB_DEFAULT, "DB");

        Crypt::$key = CRYPT_KEY;
        $db         = Sgbd::sql(DB_DEFAULT);
        Debug::parseDebug($param);
        $json       = $param[0];
        //debug(json_decode($json,JSON_PRETTY_PRINT));

        $data = Json::isJson($json);

        foreach ($data as $server_type => $servers) {
            foreach ($servers as $server) {
                switch ($server_type) {
                    case 'mysql':

                        Mysql::addMysqlServer($server);
                        break;
                }
            }
        }
    }


    /*
    private function addMysql($arr)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        debug($arr);

        foreach ($arr['arr']['mysql'] as $mysql) {

            $option = $arr['options']['mysql'];

            unset($mysql['id']);

            $mysql['error'] = '';

            $data                                   = array();
            $data['mysql_server']                   = $mysql;
            $data['mysql_server']['id_client']      = 1;
            $data['mysql_server']['id_environment'] = 1;

            $data['mysql_server']['is_password_crypted'] = 1;

            foreach ($option['crypted_fields'] as $crypted_field) {
                $data['mysql_server'][$crypted_field] = Chiffrement::encrypt($data['mysql_server'][$crypted_field]);
            }

            unset($data['mysql_server']['key_private_path']);
            unset($data['mysql_server']['key_private_user']);

            $uniques = $this->getUniqueKey('mysql_server');

            Debug::debug($uniques);

            $sql2 = array();
            foreach ($uniques as $unique) {

                $keys = explode(",", $unique);

                $sql = array();
                foreach ($keys as $key) {
                    $sql[] = " `".$key."` = '".$data['mysql_server'][$key]."' ";
                }

                $sql2[] = "SELECT * FROM `mysql_server` WHERE ".implode(" AND ", $sql);
            }


            if (!empty($sql2)) {
                $sql_good = '( '.implode(") UNION (", $sql2).' )';

                Debug::debug($sql_good);

                $res10 = $db->sql_query($sql_good);
                $cpt   = $db->sql_num_rows($res10);

                if ($cpt !== 0) {
                    continue;
                }
            }


            if ($data['mysql_server']['name'] !== "pmacontrol") {
                $id_mysql_server = $db->sql_save($data);
            }


            if (!$id_mysql_server) {
                debug($data);
                debug($db->sql_error());
            } else {
                Mysql::addMaxDate(array($id_mysql_server));
            }
        }

        //$this->generateMySQLConfig();
        Mysql::onAddMysqlServer();
        Mysql::generateMySQLConfig($db);
    }*/


    /*
    public function generateMySQLConfig($param = '')
    {
        $this->view = false;

        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server a ORDER BY id_client";
        $res = $db->sql_query($sql);

        $config = ';[name_of_connection] => will be acceded in framework with $this->di[\'db\']->sql(\'name_of_connection\')->method()
;driver => list of SGBD avaible {mysql, pgsql, sybase, oracle}
;hostname => server_name of ip of server SGBD (better to put localhost or real IP)
;user => user who will be used to connect to the SGBD
;password => password who will be used to connect to the SGBD
;database => database / schema witch will be used to access to datas
';

        while ($ob = $db->sql_fetch_object($res)) {
            $string = "[".$ob->name."]\n";
            $string .= "driver=mysql\n";
            $string .= "hostname=".$ob->ip."\n";
            $string .= "port=".$ob->port."\n";
            $string .= "user=".$ob->login."\n";
            $string .= "password=".$ob->passwd."\n";
            $string .= "crypted=1\n";
            $string .= "database=".$ob->database."\n";

            $config .= $string."\n\n";

//Debug::debug($string);
        }

        file_put_contents(ROOT."/configuration/db.config.ini.php", $config);
    }
    */

    public function test_import($param)
    {
        $file = $param[0];
        $json = file_get_contents($file);
        $this->import($json);
    }

/**
 * Handle export state through `_export`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $options Input value for `options`.
 * @phpstan-param mixed $options
 * @psalm-param mixed $options
 * @return mixed Returned value for _export.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::_export()
 * @example /fr/export/_export
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function _export($options = array())
    {
        $db     = Sgbd::sql(DB_DEFAULT);
        $sql1   = "SELECT * FROM `export_option` where active ='1'";
        $res1   = $db->sql_query($sql1);
        $backup = array();

        while ($ob = $db->sql_fetch_object($res1, MYSQLI_ASSOC)) {

            $tables  = explode(",", trim($ob->table_name));
            $crypted = explode(",", $ob->crypted_fields);
            $splited = explode(",", $ob->splited_fields);

            Debug::debug("--------------");
            Debug::debug($ob->config_file, "config file");

            if (!empty($tables)) {
                foreach ($tables as $table) {
                    if (empty($table)) {
                        continue;
                    }

                    Debug::debug($table, "table MySQL");

                    if (!empty($ob->sql)) {


                        $sql2 = str_replace('{$DB_DEFAULT}', DB_DEFAULT, $ob->sql);
                    } else {
                        $sql2 = "SELECT * FROM `".$table."`";
                    }


                    $res2 = $db->sql_query($sql2);

                    while ($arr2 = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                        if (!empty($ob->crypted_fields)) {

                            $fields = explode(",", $ob->crypted_fields);

                            foreach ($fields as $field) {
                                $arr2[$field] = Chiffrement::decrypt($arr2[$field]);
                            }
                        }




                        if (!empty($ob->splited_fields)) {
                            foreach ($splited as $field_split) {


                                if (empty($arr2[$field_split])) {
                                    unset($arr2[$field_split]);
                                } else {

                                    $arr2[$field_split] = explode(",", $arr2[$field_split]);
                                }
                            }
                        }



                        $backup[$ob->key][] = $arr2;
                    }
                }
            } else if (!empty($ob->config_file)) {
//cas des fichiers de configurations (ldap etc...)


                Debug::debug($ob->config_file, "config file");
//Debug::debug($tables);

                $config = file_get_contents(CONFIG.$ob->config_file);
                preg_match_all("/define\(\"(\w+)\"\s*,\s*\"(.*)\"/", $config, $output_array);

                foreach ($output_array[1] as $key => $val) {
                    $backup[$ob->key][$val] = $output_array[2][$key];
                }
            } else {
// error
                Debug::debug($ob, "PROBLEME !!");
            }
        }


        return $backup;
    }

/**
 * Handle export state through `test_export`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for test_export.
 * @phpstan-return void
 * @psalm-return void
 * @see self::test_export()
 * @example /fr/export/test_export
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function test_export($param)
    {

        Debug::parseDebug($param);

        $backup = $this->_export();

        Debug::debug(json_encode($backup, JSON_PRETTY_PRINT));
    }

/**
 * Retrieve export state through `getExportOption`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getExportOption.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getExportOption()
 * @example /fr/export/getExportOption
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getExportOption()
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM export_option";

        $res = $db->sql_query($sql);

        $export_option = array();
        while ($arr           = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $arr['table_name']     = explode(",", trim($arr['table_name']));
            $arr['crypted_fields'] = explode(",", trim($arr['crypted_fields']));

            if (empty($arr['table_name'][0])) {
                $arr['table_name'] = array();
            }

            if (empty($arr['crypted_fields'][0])) {
                $arr['crypted_fields'] = array();
            }

            $export_option[$arr['key']] = $arr;
        }


        if (count($export_option) == 0) {
            throw new \Exception('PMACTRL-158 : The table export_option is empty !');
        }



        return $export_option;
    }

/**
 * Retrieve export state through `getUniqueKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $table_name Input value for `table_name`.
 * @phpstan-param mixed $table_name
 * @psalm-param mixed $table_name
 * @return mixed Returned value for getUniqueKey.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getUniqueKey()
 * @example /fr/export/getUniqueKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getUniqueKey($table_name)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "select group_concat(COLUMN_NAME) as colonne from information_schema.KEY_COLUMN_USAGE where TABLE_schema = 'pmacontrol' and table_name ='".$table_name."' and POSITION_IN_UNIQUE_CONSTRAINT is null and CONSTRAINT_NAME != 'PRIMARY' group by CONSTRAINT_NAME;";

        $res = $db->sql_query($sql);

        $unique = array();
        while ($arr    = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $unique[] = $arr['colonne'];
        }

        return $unique;
    }

/**
 * Handle export state through `encrypt`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for encrypt.
 * @phpstan-return void
 * @psalm-return void
 * @see self::encrypt()
 * @example /fr/export/encrypt
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function encrypt()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id,passwd from mysql_server where id !=1";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $sql = "UPDATE mysql_server SET passwd = '".Chiffrement::encrypt($ob->passwd)."' WHERE id=".$ob->id;
            $db->sql_query($sql);
        }
    }

/**
 * Handle export state through `option`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for option.
 * @phpstan-return void
 * @psalm-return void
 * @see self::option()
 * @example /fr/export/option
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function option()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `export_option` order by `active` desc;";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $data['export_option'][] = $ob;
        }


        $this->set('data', $data);
    }

//a mettre dans une librairy


/**
 * Handle export state through `test_dechiffrement`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for test_dechiffrement.
 * @phpstan-return void
 * @psalm-return void
 * @see self::test_dechiffrement()
 * @example /fr/export/test_dechiffrement
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function test_dechiffrement($param)
    {
        $data = array();

        if (IS_CLI) {

            $file     = $param[0];
            $password = $param[1];

            Debug::debug($file, "file");
        } else {

            if (CsrfGuard::isPost($_SERVER)) {
                $testPost = self::evaluateTestDechiffrementPost($_FILES, $_POST, $_SERVER, $_SESSION);
                if ($testPost['status'] !== 200) {
                    if ($testPost['status'] === 403 || $testPost['status'] === 405) {
                        $this->view = false;
                        $this->layout_name = false;
                        self::sendExportError($testPost['status'], $testPost['body'], $testPost['headers']);
                        return;
                    }

                    set_flash("error", __('Error'), __($testPost['body']));
                    header("location: ".LINK.$this->getClass()."/".__FUNCTION__);
                    return;
                }

                $file = $testPost['file'];
                $password = $testPost['password'];
            }

            $data['export_test_dechiffrement_csrf_field'] = Csrf::DEFAULT_FIELD;
            $data['export_test_dechiffrement_csrf_token'] = Csrf::issueToken($_SESSION, self::EXPORT_TEST_DECHIFFREMENT_CSRF_SCOPE);
        }

        if (!empty($file) && !empty($password)) {
            $json = self::decryptTestDechiffrementFile($file, $password);
            if ($json !== null) {
                $data['json'] = $json;
            } else {
                set_flash("error", __('Error'), __("The password is not good"));
                header("location: ".LINK.$this->getClass()."/".__FUNCTION__);
                return;
            }
            //false
        }

        $this->set('data', $data);
    }

    public static function evaluateTestDechiffrementPost(
        array $files,
        array $post,
        array $server,
        array $session,
        bool $requireUploadedFile = true
    ): array {
        return EncryptedExportRequest::evaluateUploadPasswordPost(
            $files,
            $post,
            $server,
            $session,
            self::EXPORT_TEST_DECHIFFREMENT_CSRF_SCOPE,
            'Invalid export test payload',
            $requireUploadedFile
        );
    }

    public static function normalizeTestDechiffrementPayload(
        array $files,
        array $post,
        bool $requireUploadedFile = true
    ): ?array {
        return EncryptedExportRequest::normalizeUploadPasswordPayload($files, $post, $requireUploadedFile);
    }

    public static function decryptTestDechiffrementFile(string $file, string $password): ?string
    {
        $compressed = EncryptedExportRequest::decryptFile($file, $password);
        if (!self::isGzippedPayload($compressed)) {
            return null;
        }

        $json = gzuncompress($compressed);
        return is_string($json) ? $json : null;
    }

    private static function sendExportError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $message;
    }

/**
 * Handle export state through `is_gzipped`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $in Input value for `in`.
 * @phpstan-param mixed $in
 * @psalm-param mixed $in
 * @return mixed Returned value for is_gzipped.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::is_gzipped()
 * @example /fr/export/is_gzipped
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function is_gzipped($in)
    {
        return self::isGzippedPayload($in);
    }

    private static function isGzippedPayload($in): bool
    {
        if (!is_string($in)) {
            return false;
        }

        return mb_strpos($in, "\x1f"."\x8b"."\x08") === 0
            || @gzuncompress($in) !== false
            || @gzinflate($in) !== false;
    }
}
/* $compressed   = gzcompress('Compresse moi', 9);
  $uncompressed = gzuncompress($compressed);
  echo $uncompressed; */
