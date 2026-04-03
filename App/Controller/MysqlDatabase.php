<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Mysql;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for mysql database workflows.
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
class MysqlDatabase extends Controller
{
/**
 * Create mysql database state through `addNoDatabaseCautionFlash`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for addNoDatabaseCautionFlash.
 * @phpstan-return void
 * @psalm-return void
 * @see self::addNoDatabaseCautionFlash()
 * @example /fr/mysqldatabase/addNoDatabaseCautionFlash
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function addNoDatabaseCautionFlash()
    {
        static $is_flash_added = false;

        if ($is_flash_added) {
            return;
        }

        set_flash(
            "caution",
            __("Aucune base disponible"),
            __("Aucune base n’est actuellement disponible pour ce serveur. La liste des bases est importée une fois par heure.")
        );

        $is_flash_added = true;
    }

/**
 * Handle mysql database state through `menu`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for menu.
 * @phpstan-return void
 * @psalm-return void
 * @see self::menu()
 * @example /fr/mysqldatabase/menu
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function menu($param)
    {
        $id_mysql_server = (int) ($param[0] ?? 0);
        $table_schema = trim((string) ($param[1] ?? ""));

        $_GET['mysql_server']['id'] = $id_mysql_server;

        if (empty($table_schema)) {
            $this->addNoDatabaseCautionFlash();
            $this->set('param', $param);
            return;
        }

        $default = Sgbd::sql(DB_DEFAULT);
        $table_schema_escaped = $default->sql_real_escape_string($table_schema);
        $sql = "SELECT id, schema_name
        FROM mysql_database
        WHERE id_mysql_server = ".$id_mysql_server."
        AND schema_name IS NOT NULL
        AND schema_name <> ''
        ORDER BY (schema_name = '".$table_schema_escaped."') DESC, schema_name ASC
        LIMIT 1";

        $res = $default->sql_query($sql);
        $found_schema = false;

        while ($ob = $default->sql_fetch_object($res)){
            $found_schema = true;
            $_GET['mysql_database']['id'] = $ob->id;

            if ($table_schema !== $ob->schema_name) {
                $url = str_replace("/".$table_schema."/", "/".$ob->schema_name."/", $_GET['url']);
                if ($url !== $_GET['url']) {
                    header("location: ".LINK.$url);
                    exit;
                }
            }
            break;
        }

        if (!$found_schema) {
            $this->addNoDatabaseCautionFlash();
        }

        $this->di['js']->code_javascript('
        $("#mysql_database-id").change(function () {
            data = $("#mysql_database-id option:selected").text();
            var segments = GLIAL_URL.split("/");

            if(segments.length > 3) {
                segments[3] = data;
            }
            newPath = GLIAL_LINK + segments.join("/");

            window.location.href=newPath;
        });');

        $this->set('param', $param);
    }

/**
 * Retrieve mysql database state through `getDatabaseByServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getDatabaseByServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getDatabaseByServer()
 * @example /fr/mysqldatabase/getDatabaseByServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function getDatabaseByServer($param)
    {
        $this->di['js']->addJavascript(array('bootstrap-select.min.js', 'Common/getDatabaseByServer.js'));

        $id_mysql_server = $param[0];
        $options = (array) $param[1];
        $data['options'] = $options;

        if (!empty($id_mysql_server)) {
            $db_to_get_db = Sgbd::sql(DB_DEFAULT);

            $sql  = "select id,schema_name from mysql_database where id_mysql_server=".$id_mysql_server." ORDER BY schema_name;";
            $res2 = $db_to_get_db->sql_query($sql);

            $data['databases'] = [];
            while ($ob                = $db_to_get_db->sql_fetch_object($res2)) {
                $tmp                 = [];
                $tmp['id']           = $ob->id;
                $tmp['libelle']      = $ob->schema_name;
                $data['databases'][] = $tmp;

            }
        } else {
            $data['databases'] = array();
        }

        $this->set("data", $data);
        return $data;
    }

/**
 * Handle mysql database state through `mpd`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for mpd.
 * @phpstan-return void
 * @psalm-return void
 * @see self::mpd()
 * @example /fr/mysqldatabase/mpd
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function mpd($param)
    {
        $data['param'] = $param;

        $id_mysql_server = !empty($param[0]) ? (int) $param[0] : 1;
        $_GET['mysql_server']['id'] = $id_mysql_server;

        $database = trim((string) ($param[1] ?? ""));

        if (empty($database))
        {
            $db = Sgbd::sql(DB_DEFAULT);

            $sql = "SELECT id_mysql_server,schema_name FROM mysql_database WHERE id_mysql_server = ".$id_mysql_server."
            AND schema_name NOT in ('information_schema','performance_schema')
            AND schema_name IS NOT NULL
            AND schema_name <> ''
            ORDER BY schema_name
            LIMIT 1";
            $res = $db->sql_query($sql);

            while($ob = $db->sql_fetch_object($res)) {
                header("location: ".LINK."MysqlDatabase/mpd/".$ob->id_mysql_server."/".$ob->schema_name."/");
                exit;
            }

            $this->addNoDatabaseCautionFlash();
        }

        $this->set('data', $data);
        $this->set('param', $param);
    }

/**
 * Handle mysql database state through `foreignKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for foreignKey.
 * @phpstan-return void
 * @psalm-return void
 * @see self::foreignKey()
 * @example /fr/mysqldatabase/foreignKey
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function foreignKey($param)
    {
        $data = array();

        $_GET['mysql_server']['id'] = $param[0];

        $data['param'] = $param;
        $this->set('data', $data);
        $this->set('param', $param);
    }


/**
 * Handle mysql database state through `table`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for table.
 * @phpstan-return void
 * @psalm-return void
 * @see self::table()
 * @example /fr/mysqldatabase/table
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function table($param)
    {
        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $_GET['mysql_server']['id'] = $id_mysql_server;

        $db              = Mysql::getDbLink($id_mysql_server);
        $default         = Sgbd::sql(DB_DEFAULT);

        $allowedSortColumns = [
            'table' => 'TABLE_NAME',
            'rows' => 'TABLE_ROWS',
            'engine' => 'ENGINE',
            'row_format' => 'ROW_FORMAT',
            'collation' => 'TABLE_COLLATION',
            'size' => 'DATA_LENGTH',
            'index' => 'INDEX_LENGTH',
            'overhead' => 'DATA_FREE',
            'total' => 'TOTAL_LENGTH',
        ];

        $sort = $_GET['sort'] ?? 'total';
        $order = strtolower($_GET['order'] ?? 'desc');

        if (!isset($allowedSortColumns[$sort])) {
            $sort = 'total';
        }

        if (!in_array($order, ['asc', 'desc'], true)) {
            $order = 'desc';
        }

        $orderByColumn = $allowedSortColumns[$sort];
        $orderByDirection = strtoupper($order);

        $sql = "SELECT *,
            (IFNULL(DATA_LENGTH,0) + IFNULL(INDEX_LENGTH,0) + IFNULL(DATA_FREE,0)) AS TOTAL_LENGTH
            FROM information_schema.tables
            WHERE table_schema='".$table_schema."'
            ORDER BY ".$orderByColumn." ".$orderByDirection.", TABLE_NAME";
        $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($db, $sql, $id_mysql_server, __METHOD__);

        $data['table'] = array();
        while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)){
            $data['table'][] = $arr;
        }

        $data['table_schema'] = $table_schema;
        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['param'] = $param;
        $this->set('data',$data);
        $this->set('param', $param);
    }


    public function list($param)
    {

        $id_mysql_server = $param[0];
        $db              = Mysql::getDbLink($id_mysql_server);

        $sql = "SELECT * FROM ";


        
    }


}
