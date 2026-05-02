<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Html\Pagination\Pagination;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use App\Library\Http\HttpResponse;
use App\Library\Security\PositiveIntegerSelection;

/**
 * Class responsible for monitoring workflows.
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
class Monitoring extends Controller
{
/**
 * Stores `$previous_data` for previous data.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    public $previous_data = array();
/**
 * Stores `$actual_data` for actual data.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    public $actual_data   = array();

/**
 * Handle monitoring state through `arrays_are_similar`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $a Input value for `a`.
 * @phpstan-param mixed $a
 * @psalm-param mixed $a
 * @param mixed $b Input value for `b`.
 * @phpstan-param mixed $b
 * @psalm-param mixed $b
 * @return mixed Returned value for arrays_are_similar.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::arrays_are_similar()
 * @example /fr/monitoring/arrays_are_similar
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function arrays_are_similar($a, $b)
    {
        // if the indexes don't match, return immediately
        if (count(array_diff_assoc($a, $b))) {
            return false;
        }
        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach ($a as $k => $v) {
            if ($v !== $b[$k]) {
                return false;
            }
        }
        // we have identical indexes, and no unequal values
        return true;
    }

/**
 * Handle monitoring state through `compare`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $tab_from Input value for `tab_from`.
 * @phpstan-param mixed $tab_from
 * @psalm-param mixed $tab_from
 * @param mixed $tab_to Input value for `tab_to`.
 * @phpstan-param mixed $tab_to
 * @psalm-param mixed $tab_to
 * @return mixed Returned value for compare.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::compare()
 * @example /fr/monitoring/compare
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function compare($tab_from = array(), $tab_to=array())
    {
        $tab_update = array_intersect_key($tab_from, $tab_to);
        foreach ($tab_update as $key => $value) {
            if ($tab_from[$key] != $tab_to[$key]) {
                $update[$key]  = $tab_to[$key];
                $update2[$key] = $tab_from[$key];
            }
        }
        foreach ($tab_to as $key => $value) {
            if (!isset($tab_update[$key])) {
                $add[$key] = $value;
            }
        }
        foreach ($tab_from as $key => $value) {
            if (!isset($tab_update[$key])) {
                $del[$key] = $value;
            }
        }

        $finale            = array();
        empty($add) ? "" : $finale['add']     = $add;
        empty($delete) ? "" : $finale['delete']  = $del;
        empty($update) ? "" : $finale['update']  = $update;
        empty($update2) ? "" : $finale2['update'] = $update2;

        $param['up']   = $finale;
        empty($finale2) ? $param['down'] = array() : $param['down'] = $finale2;

        return serialize($param);
    }

/**
 * Handle monitoring state through `query`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for query.
 * @phpstan-return void
 * @psalm-return void
 * @see self::query()
 * @example /fr/monitoring/query
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function query($param)
    {

        Debug::parseDebug($param);
        $this->title  = __("Query Analyzer");
        $this->ariane = " > ".__("Monitoring")." > ".$this->title;

        $outcome = self::evaluateQueryRequest($_SERVER);
        if (!$outcome['allowed']) {
            $this->view = false;
            $this->layout_name = false;
            HttpResponse::sendOutcome($outcome, null);
            return;
        }

        $idServer = self::normalizeQueryServerId($_GET, $param);
        if ($idServer === null) {

            $default = Sgbd::sql(DB_DEFAULT);
            $sql     = "SELECT * FROM mysql_server limit 1";
            $res     = $default->sql_query($sql);

            $ob = $default->sql_fetch_object($res);

            $idServer = (int) $ob->id;
        }
        if ($idServer !== null) {
            $param[0] = $idServer;
            $data['id_server']          = $idServer;
            $_GET['mysql_server']['id'] = $data['id_server'];
        }

        $_GET['database']['id']     = empty($_GET['database']['id']) ? "" : $_GET['database']['id'];
        $_GET['field']['id']        = empty($_GET['field']['id']) ? "" : $_GET['field']['id'];
        $_GET['database']['filter'] = empty($_GET['database']['filter']) ? "" : $_GET['database']['filter'];
        $_GET['orderby']['id']      = empty($_GET['orderby']['id']) ? "" : $_GET['orderby']['id'];


        $default = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server order by name";
        $res = $default->sql_query($sql);

        $data['server_mysql'] = [];
        while ($ob                   = $default->sql_fetch_object($res)) {
            $tmp = [];

            $tmp['id']      = $ob->id;
            $tmp['libelle'] = str_replace('_', '-', $ob->name)." (".$ob->ip.")";

            $data['server_mysql'][] = $tmp;

            if ($data['id_server'] === $ob->id) {
                $link = $ob->name;
            }
        }

        $db = Sgbd::sql(str_replace('-', '_', $link));

        $sql = "SHOW DATABASES";
        $res = $db->sql_query($sql);

        $data['databases'] = [];
        while ($ob                = $db->sql_fetch_object($res)) {
            $tmp = [];

            $tmp['id']      = $ob->Database;
            $tmp['libelle'] = $ob->Database;

            $data['databases'][] = $tmp;
        }




        $data['performance_schema'] = false;
        $sql                        = "SHOW VARIABLES LIKE 'performance_schema';";

        $res = $db->sql_query($sql);

        $data['error'] = false;

        while ($ob = $db->sql_fetch_object($res)) {
            if ($ob->Value == "ON") {
                $data['performance_schema'] = true;
            } else {
                $data['error'] = true;
            }
        }


        $data['mysql_upgrade'] = true;

        if ($data['performance_schema']) {
            $sql = "select * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = 'performance_schema' AND `TABLE_NAME` = 'events_statements_summary_by_digest'";
            $res = $db->sql_query($sql);

            //in case of table doesn't exist (mysql_upgrade not made)



            if ($db->sql_num_rows($res) == 0) {
                $data['mysql_upgrade'] = false;
                $data['error']         = true;
            }
        }


        if ($data['error'] === false) {



            $data['fields'] = [];
            while ($ob             = $db->sql_fetch_object($res)) {
                $tmp = [];

                $tmp['id']      = $ob->COLUMN_NAME;
                $tmp['libelle'] = $ob->COLUMN_NAME;

                $data['fields'][] = $tmp;
            }

            $allowedFields = array_column($data['fields'], 'id');
            $filter = self::normalizeQueryFilter($_GET, $allowedFields);
            $_GET['database']['id'] = $filter['database_id'];
            $_GET['database']['filter'] = $filter['database_filter'];
            $_GET['field']['id'] = $filter['field_id'];
            $_GET['orderby']['id'] = $filter['orderby'];

            $data['orderby'][0]['id']      = 'ASC';
            $data['orderby'][0]['libelle'] = 'ASC';
            $data['orderby'][1]['id']      = 'DESC';
            $data['orderby'][1]['libelle'] = 'DESC';

            $sql1 = "SELECT * ";
            $sql2 = "SELECT count(1) as cpt ";

            $sql = " FROM performance_schema.events_statements_summary_by_digest a
            where 1=1 ";

            $sql .= self::buildQueryWhereClause($filter, [$db, 'sql_real_escape_string']);

            $sql3 = self::buildQueryOrderClause($filter);



            //$sql3 = " order by a.COUNT_STAR DESC "; //$sql3 = " order by a.date_validated desc";
            //$sql = ""



            $res = $db->sql_query($sql2.$sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $data['count'] = $ob->cpt;
            }



            if ($data['count'] != 0) {


                //url, curent page, nb item max , nombre de lignes, nombres de pages
                if (empty($_GET['page'])) {
                    $_GET['page'] = 1;
                }

                $pagination = new Pagination(LINK.$this->getClass().'/'.__FUNCTION__.'/'.$param[0]
                    ."/database:id:".rawurlencode($filter['database_id'])
                    ."/field:id:".rawurlencode($filter['field_id'])
                    ."/database:filter:".rawurlencode($filter['database_filter'])."/orderby:id:".$filter['orderby']
                    , $_GET['page'], $data['count'], 50, 30);

                $tab = $pagination->get_sql_limit();

                $pagination->set_alignment("left");
                $pagination->set_invalid_page_number_text(__("Please input a valid page number!"));
                $pagination->set_pages_number_text(__("pages of"));
                $pagination->set_go_button_text(__("Go"));
                $pagination->set_first_page_text("« ".__("First page"));
                $pagination->set_last_page_text(__("Last page")." »");
                $pagination->set_next_page_text("»");
                $pagination->set_prev_page_text("«");

                $pagination->show_go_button(false);
                $data['pagination'] = $pagination->print_pagination();

                $limit     = " LIMIT ".$tab[0].",".$tab[1]." ";
                $data['i'] = $tab[0] + 1;
                //*****************************pagination end
            }

            empty($limit) ? $limit = "" : "";

            $sql = $sql1.$sql.$sql3.$limit;

            //debug($sql);
            $data['event_by_digest'] = $db->sql_fetch_yield($sql);
        }

        $this->set('data', $data);
    }

    public static function evaluateQueryRequest(array $server): array
    {
        if (strtoupper((string)($server['REQUEST_METHOD'] ?? 'GET')) === 'POST') {
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

    public static function normalizeQueryServerId(array $get, array $param): ?int
    {
        $value = $get['mysql_server']['id'] ?? ($param[0] ?? null);
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string)$value);
        if ($value === '' || !ctype_digit($value) || (int)$value < 1) {
            return null;
        }

        return (int)$value;
    }

    public static function normalizeQueryFilter(array $get, array $allowedFields): array
    {
        $allowed = array_fill_keys(array_map('strval', $allowedFields), true);
        $fieldId = self::normalizeQueryText($get['field']['id'] ?? '', 128, true);
        $orderby = strtoupper((string)self::normalizeQueryText($get['orderby']['id'] ?? 'ASC', 4, true));

        if ($fieldId === null || !isset($allowed[$fieldId])) {
            $fieldId = '';
        }

        if (!in_array($orderby, ['ASC', 'DESC'], true)) {
            $orderby = 'ASC';
        }

        return [
            'database_id' => self::normalizeQueryText($get['database']['id'] ?? '', 255, true) ?? '',
            'database_filter' => self::normalizeQueryText($get['database']['filter'] ?? '', 255, true) ?? '',
            'field_id' => $fieldId,
            'orderby' => $orderby,
        ];
    }

    public static function buildQueryWhereClause(array $filter, callable $escape): string
    {
        $sql = '';
        if ($filter['database_id'] !== '') {
            $sql .= " AND a.SCHEMA_NAME ='".$escape($filter['database_id'])."' ";
        }

        if ($filter['database_filter'] !== '') {
            $sql .= " AND a.DIGEST_TEXT LIKE '%".$escape($filter['database_filter'])."%' ";
        }

        return $sql;
    }

    public static function buildQueryOrderClause(array $filter): string
    {
        if ($filter['field_id'] === '') {
            return ' ';
        }

        return " ORDER BY a.`".$filter['field_id']."` ".$filter['orderby']." ";
    }

    private static function normalizeQueryText($value, int $maxLength, bool $allowEmpty): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string)$value);
        if ((!$allowEmpty && $value === '') || strlen($value) > $maxLength) {
            return null;
        }

        return $value;
    }

    public static function evaluateExplainRequest(array $get, array $server): array
    {
        if (strtoupper((string)($server['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
            return [
                'allowed' => false,
                'status' => 405,
                'body' => 'Method Not Allowed',
                'headers' => ['Allow' => 'GET'],
            ];
        }

        $idMysqlServer = self::normalizeExplainServerId($get['mysql_server']['id'] ?? null);
        $digest = self::normalizeExplainDigest($get['digest'] ?? null);

        if ($idMysqlServer === null || $digest === null) {
            return [
                'allowed' => false,
                'status' => 400,
                'body' => 'Invalid monitoring explain request.',
                'headers' => [],
            ];
        }

        return [
            'allowed' => true,
            'status' => 200,
            'body' => '',
            'headers' => [],
            'id_mysql_server' => $idMysqlServer,
            'digest' => $digest,
        ];
    }

    public static function normalizeExplainServerId($value): ?int
    {
        return PositiveIntegerSelection::normalizeSingle($value);
    }

    public static function normalizeExplainDigest($value): ?string
    {
        $digest = self::normalizeQueryText($value, 128, false);
        if ($digest === null || preg_match('/^[A-Fa-f0-9]{16,128}$/', $digest) !== 1) {
            return null;
        }

        return strtoupper($digest);
    }

    public static function buildExplainServerSql(int $idMysqlServer): string
    {
        return "SELECT * FROM mysql_server where id= ".$idMysqlServer;
    }

    public static function buildExplainDigestSql(string $digest): string
    {
        return "select * from performance_schema.events_statements_history_long where DIGEST='".$digest."'";
    }

    /*
    public function search()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM `mysql_server`";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];

            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->name;

            $data['server'][] = $tmp;
        }

        $this->set('data', $data);
    }
/****/ 
    public function explain()
    {

        // update setup_instruments SET ENABLED='YES', TIMED='YES';
        // UPDATE setup_consumers SET ENABLED = 'YES';

        $outcome = self::evaluateExplainRequest($_GET, $_SERVER);
        if (!$outcome['allowed']) {
            $this->sendExplainError($outcome);
            return;
        }

        $idMysqlServer = (int)$outcome['id_mysql_server'];
        $digest = (string)$outcome['digest'];

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = self::buildExplainServerSql($idMysqlServer);
        $res = $db->sql_query($sql);
        $remote = null;
        while ($ob  = $db->sql_fetch_object($res)) {
            $remote = Sgbd::sql($ob->name);
        }

        if ($remote === null) {
            $this->sendExplainError([
                'status' => 404,
                'body' => 'Monitoring server not found.',
                'headers' => [],
            ]);
            return;
        }

        $sql = self::buildExplainDigestSql($digest);

        $data['table'] = $remote->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    private function sendExplainError(array $outcome): void
    {
        $this->view = false;
        $this->layout_name = false;
        http_response_code((int)$outcome['status']);

        foreach (($outcome['headers'] ?? []) as $name => $value) {
            header($name.': '.$value);
        }

        echo (string)$outcome['body'];
    }



/**
 * Retrieve monitoring state through `getServer`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getServer()
 * @example /fr/monitoring/getServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function getServer()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $idMysqlServer = self::normalizeExplainServerId($_GET['mysql_server']['id'] ?? null);
        if ($idMysqlServer === null) {
            throw new \InvalidArgumentException('Invalid monitoring server id');
        }

        $sql = self::buildExplainServerSql($idMysqlServer);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $remote = Sgbd::sql($ob->name);
        }

        return $remote;
    }
}
