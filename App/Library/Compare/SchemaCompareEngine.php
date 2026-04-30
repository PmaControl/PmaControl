<?php

declare(strict_types=1);

namespace App\Library\Compare;

use App\Library\Mysql;
use App\Library\MysqlServer;
use Glial\Sgbd\Sgbd;
use Glial\Sgbd\Sql\Mysql\Compare as CompareTable;

final class SchemaCompareEngine
{
    private const OBJECTS = ["TABLE", "VIEW", "TRIGGER", "FUNCTION", "PROCEDURE", "EVENT"];

    private $dbOrigin;
    private $dbTarget;
    private int $idServerOrigin = 0;
    private int $idServerTarget = 0;

    /**
     * @return true|array<int,string>
     */
    public function checkConfig($idServer1, $db1, $idServer2, $db2)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $error = [];

        $sql = "SELECT id,name FROM mysql_server WHERE id = '" . $db->sql_real_escape_string($idServer1) . "';";
        $res = $db->sql_query($sql);
        if ($db->sql_num_rows($res) == 1) {
            while ($ob = $db->sql_fetch_object($res)) {
                $dbNameOrigin = $ob->name;
            }
        } else {
            $error[] = "The server original is unknow";
        }

        $sql = "SELECT id,name FROM mysql_server WHERE id = '" . $db->sql_real_escape_string($idServer2) . "';";
        $res2 = $db->sql_query($sql);
        if ($db->sql_num_rows($res2) == 1) {
            while ($ob = $db->sql_fetch_object($res2)) {
                $dbNameCompare = $ob->name;
            }
        } else {
            $error[] = "The server to compare is unknow";
        }

        if ($error !== []) {
            return $error;
        }

        $dbOrigin = Sgbd::sql($dbNameOrigin);
        $sql = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '" . $dbOrigin->sql_real_escape_string($db1) . "';";
        $res3 = $dbOrigin->sql_query($sql);
        $ob = $dbOrigin->sql_fetch_object($res3);
        if ($ob->cpt != 1) {
            $error[] = "The database '" . $db1 . "' original doesn't exist on server original : '" . $dbNameOrigin . "'";
        }

        $dbCompare = Sgbd::sql($dbNameCompare);
        $sql = "select count(1) as cpt from information_schema.SCHEMATA where SCHEMA_NAME = '" . $dbCompare->sql_real_escape_string($db2) . "';";
        $res4 = $dbCompare->sql_query($sql);
        $ob = $dbCompare->sql_fetch_object($res4);
        if ($ob->cpt != 1) {
            $error[] = "The database '" . $db2 . "' original doesn't exist on server original : '" . $dbNameCompare . "'";
        }

        if ($idServer1 == $idServer2 && $db1 == $db2) {
            $error[] = "The databases to compare cannot be the same on same server";
        }

        return $error === [] ? true : $error;
    }

    public function analyse($idServer1, $db1, $idServer2, $db2): array
    {
        $this->setServers($idServer1, $idServer2);

        $data = [];
        foreach (self::OBJECTS as $object) {
            if ($object === "EVENT" && ($db1 === "performance_schema" || $db2 === "performance_schema")) {
                $data["EVENT"] = [];
                continue;
            }

            $data[$object] = $this->compareListObject($db1, $db2, $object);
        }

        return $data;
    }

    public function compareDiffForMenu($idServer1, $idServer2, string $menu, $db1, $db2, array $data): array
    {
        $this->setServers($idServer1, $idServer2);

        if ($menu === "TABLE") {
            return $this->compareTable($db1, $db2, $data);
        }

        return $this->compareObject($menu, $db1, $db2, $data);
    }

    public function compareTable($original, $compare, array $data): array
    {
        $queries = [];
        foreach ($data as $table => $elem) {
            if (!empty($elem[0])) {
                $queries[$table] = "SHOW CREATE TABLE `" . $original . "`.`" . $table . "`";
            }
        }
        $resultat = $this->execMulti($queries, $this->dbOrigin);

        $queries2 = [];
        foreach ($data as $table => $elem) {
            if (!empty($elem[1])) {
                $queries2[$table] = "SHOW CREATE TABLE `" . $compare . "`.`" . $table . "`";
            }
        }
        $resultat2 = $this->execMulti($queries2, $this->dbTarget);

        foreach ($data as $table => $elem) {
            if (!empty($elem[0])) {
                $data[$table]['ori'] = $resultat[$table][0]['Create Table'] . ";";
            } else {
                $data[$table]['ori'] = "";
            }

            if (!empty($elem[1])) {
                $data[$table]['cmp'] = $resultat2[$table][0]['Create Table'] . ";";
            } else {
                $data[$table]['cmp'] = "";
            }

            if ($data[$table]['cmp'] === $data[$table]['ori']) {
                $data[$table]['script'] = [];
                $data[$table]['script2'] = [];
            } elseif (empty($data[$table]['cmp'])) {
                $data[$table]['script'][0] = str_replace(
                    "CREATE TABLE",
                    "CREATE TABLE IF NOT EXISTS",
                    $data[$table]['ori']
                );
                $data[$table]['script2'][0] = "DROP TABLE IF EXISTS `" . $table . "`;";
            } elseif (empty($data[$table]['ori'])) {
                $data[$table]['script2'][0] = str_replace(
                    "CREATE TABLE",
                    "CREATE TABLE IF NOT EXISTS",
                    $data[$table]['cmp']
                );
                $data[$table]['script'][0] = "DROP TABLE IF EXISTS `" . $table . "`;";
            } else {
                $updater = new CompareTable();
                $data[$table]['script'] = $updater->getUpdates($data[$table]['cmp'], $data[$table]['ori']);
                $updater = new CompareTable();
                $data[$table]['script2'] = $updater->getUpdates($data[$table]['ori'], $data[$table]['cmp']);
            }
        }

        return $data;
    }

    public function execMulti($queries, $dbLink): array
    {
        if (!is_array($queries)) {
            throw new \Exception("PMACTRL-652 : first parameter should be an array !");
        }

        $query = implode(";", $queries);
        $ret = [];

        if ($query === '') {
            return $ret;
        }

        if ($dbLink->sql_multi_query($query)) {
            foreach ($queries as $table => $elem) {
                $result = $dbLink->sql_store_result();

                if (!$result) {
                    printf("Error: %s\n", mysqli_error($dbLink->link));
                    debug($query);
                    exit();
                }

                while ($row = $dbLink->sql_fetch_array($result, MYSQLI_ASSOC)) {
                    $ret[$table][] = $row;
                }

                if ($dbLink->sql_more_results()) {
                    $dbLink->sql_next_result();
                }
            }
        }

        return $ret;
    }

    public function compareListObject($db1, $db2, $typeObject): array
    {
        $query = $this->listObjectQueries();

        if (!in_array($typeObject, array_keys($query), true)) {
            throw new \Exception("PMACTRL-095 : this type of object is not supported : '" . $typeObject . "'", 80);
        }

        $dbs = [
            ['database' => $db1, 'db_link' => $this->dbOrigin, 'id_mysql_server' => $this->idServerOrigin],
            ['database' => $db2, 'db_link' => $this->dbTarget, 'id_mysql_server' => $this->idServerTarget],
        ];

        $i = 0;
        $data = [];

        foreach ($dbs as $dbUnique) {
            $dbName = $dbUnique['database'];
            $dbLink = $dbUnique['db_link'];
            $idMysqlServer = $dbUnique['id_mysql_server'];

            // Callers must pass database names validated by CompareMainSelection.
            $sql = str_replace('{DB}', $dbName, $query[$typeObject]['query']);
            $res = Mysql::sqlQueryWithInformationSchemaTablesTimeout($dbLink, $sql, $idMysqlServer, __METHOD__);

            while ($row = $dbLink->sql_fetch_array($res, MYSQLI_ASSOC)) {
                $data[$row[$query[$typeObject]['field']]][$i] = 1;
            }

            $i++;
        }

        ksort($data);
        return $data;
    }

    public function compareObject(string $menu, $db1, $db2, array $data): array
    {
        $query = $this->objectQueries();

        if (!empty($data['diagnostics']) && $menu === 'PROCEDURE') {
            unset($data['diagnostics']);
        }

        if (!in_array($menu, array_keys($query), true)) {
            throw new \Exception("PMACTRL-096 : this type of object is not supported : '" . $menu . "'", 80);
        }

        $queries = [];
        foreach ($data as $object => $elem) {
            if (!empty($elem[0])) {
                $queries[$object] = str_replace(
                    ['{DB}', '{OBJECT}'],
                    [$db1, $object],
                    $query[$menu]['query']
                );
            }
        }

        $resultat = $this->execMulti($queries, $this->dbOrigin);

        $queries2 = [];
        foreach ($data as $object => $elem) {
            if (!empty($elem[1])) {
                $queries2[$object] = str_replace(
                    ['{DB}', '{OBJECT}'],
                    [$db2, $object],
                    $query[$menu]['query']
                );
            }
        }
        $resultat2 = $this->execMulti($queries2, $this->dbTarget);

        foreach ($data as $object => $elem) {
            if (!empty($elem[0])) {
                $data[$object]['ori'] = $resultat[$object][0][$query[$menu]['field']];
            } else {
                $data[$object]['ori'] = "";
            }

            if (!empty($elem[1])) {
                $data[$object]['cmp'] = $resultat2[$object][0][$query[$menu]['field']];
            } else {
                $data[$object]['cmp'] = "";
            }

            if ($data[$object]['cmp'] === $data[$object]['ori']) {
                $data[$object]['script'] = [];
                $data[$object]['script2'] = [];
            } elseif (empty($data[$object]['cmp'])) {
                $data[$object]['script2'][0] = $data[$object]['ori'];
                $data[$object]['script'][0] = str_replace('{OBJECT}', $object, $query[$menu]['drop']);
            } elseif (empty($data[$object]['ori'])) {
                $data[$object]['script'][0] = $data[$object]['cmp'];
                $data[$object]['script2'][0] = str_replace('{OBJECT}', $object, $query[$menu]['drop']);
            } else {
                $data[$object]['script'][0] = $data[$object]['cmp'];
                $data[$object]['script2'][0] = $data[$object]['ori'];
            }
        }

        return $data;
    }

    public function getDbLinkFromId($idDb)
    {
        // TODO #561: Compare did not filter mysql_server.is_deleted=0 before
        // the #559 mutualization. Preserve the legacy behavior with
        // excludeDeleted=false until the followup audits the workflow.
        return MysqlServer::getDbLinkFromId($idDb, false);
    }

    private function setServers($idServer1, $idServer2): void
    {
        $this->dbOrigin = $this->getDbLinkFromId($idServer1);
        $this->dbTarget = $this->getDbLinkFromId($idServer2);
        $this->idServerOrigin = (int) $idServer1;
        $this->idServerTarget = (int) $idServer2;
    }

    private function listObjectQueries(): array
    {
        return [
            'TRIGGER' => [
                'query' => "select trigger_schema, trigger_name, action_statement from information_schema.triggers where trigger_schema ='{DB}'",
                'field' => 'trigger_name',
            ],
            'FUNCTION' => [
                'query' => "show function status WHERE Db ='{DB}';",
                'field' => 'Name',
            ],
            'PROCEDURE' => [
                'query' => "show procedure status WHERE Db ='{DB}'",
                'field' => 'Name',
            ],
            'TABLE' => [
                'query' => "select TABLE_NAME from information_schema.tables where TABLE_SCHEMA = '{DB}' AND (TABLE_TYPE='BASE TABLE' OR TABLE_TYPE='SYSTEM VERSIONED') order by TABLE_NAME;",
                'field' => 'TABLE_NAME',
            ],
            'VIEW' => [
                'query' => "select TABLE_NAME from information_schema.tables where TABLE_SCHEMA = '{DB}' AND TABLE_TYPE='VIEW' order by TABLE_NAME;",
                'field' => 'TABLE_NAME',
            ],
            'EVENT' => [
                'query' => "SHOW EVENTS FROM `{DB}`",
                'field' => 'Name',
            ],
        ];
    }

    private function objectQueries(): array
    {
        return [
            'TRIGGER' => [
                'query' => "SHOW CREATE TRIGGER `{DB}`.`{OBJECT}`",
                'field' => "SQL Original Statement",
                'drop' => "DROP TRIGGER `{OBJECT}`",
            ],
            'FUNCTION' => [
                'query' => "SHOW CREATE FUNCTION `{DB}`.`{OBJECT}`",
                'field' => "Create Function",
                'drop' => "DROP FUNCTION `{OBJECT}`",
            ],
            'PROCEDURE' => [
                'query' => "SHOW CREATE PROCEDURE `{DB}`.`{OBJECT}`",
                'field' => "Create Procedure",
                'drop' => "DROP PROCEDURE `{OBJECT}`",
            ],
            'TABLE' => [
                'query' => "SHOW CREATE TABLE `{DB}`.`{OBJECT}`",
                'field' => "Create Table",
                'drop' => "DROP TABLE `{OBJECT}`",
            ],
            'VIEW' => [
                'query' => "SHOW CREATE VIEW `{DB}`.`{OBJECT}`",
                'field' => "Create View",
                'drop' => "DROP VIEW `{OBJECT}`",
            ],
            'EVENT' => [
                'query' => "SHOW CREATE EVENT `{DB}`.`{OBJECT}`",
                'field' => "Create Event",
                'drop' => "DROP EVENT `{OBJECT}`",
            ],
        ];
    }
}
