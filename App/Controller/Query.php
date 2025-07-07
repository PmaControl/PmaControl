<?php

declare(ticks=1);

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Mysql;
use \App\Library\Debug;
use \Glial\Cli\SetTimeLimit;
use \Glial\Sgbd\Sgbd;

class Query extends Controller {

    const TABLE_NAME = 'tmp_setdefault';
    const TABLE_SCHEMA = 'dba';
    const LOG_FILE = TMP . "log/query.log";

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
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            yield $ob;
        }
    }

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

    /**
     * setDefault
     *
     * @param string $id_mysql_server
     * @param string $list_databases (coma separated or ALL for all databases, if omited same as ALL)
     * @return void
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
                if (version_compare($db->getVersion(), 10.2, '<')) {
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
                if (version_compare($db->getVersion(), 10.2, '<')) {
                    continue;
                }
            }

            echo "ALTER TABLE `" . $field->db_name . "`.`" . $field->table_name . "` ALTER COLUMN `" . $field->column_name . "` DROP DEFAULT;\n";
        }
    }

    public function runSetDefault($param) {

        Debug::parseDebug($param);
        $id_mysql_server = $param[0];



        do {
            $defaults = $this->setDefault($param);

            pcntl_signal(SIGTERM, array($this, 'sigHandler')); //
            pcntl_signal(SIGHUP, array($this, 'sigHandler'));
            pcntl_signal(SIGUSR1, array($this, 'sigHandler')); // active / desactive debug
            pcntl_signal(SIGUSR2, array($this, 'sigHandler')); // rechargement de la configuration ?

            $run_number = $this->getMaxRun($param);
            Debug::debug($run_number, "run_number");

            foreach ($defaults as $default) {

                //begin if MariaDB > 10.1.2
                //$query = "SET STATEMENT max_statement_time=1 FOR " . $default;
                //echo Date("Y-m-d H:i:s") . " " . $query . "\n";
                //end if MariaDB > 10.1.2

                $db = Mysql::getDbLink($id_mysql_server);

                $php = explode(" ", shell_exec("whereis php"))[1];

                Debug::sql($default);
                
                $cmd = $php . " " . GLIAL_INDEX . " " . substr(__CLASS__, strrpos(__CLASS__, '\\') + 1) . " runQuery " . $id_mysql_server . " " . base64_encode($default) . " " . $run_number . " --debug >> " . self::LOG_FILE . " & echo $!";
                $pid = intval(trim(shell_exec($cmd)));

                do {

                    usleep(100000);

                    $sql = "SELECT thread_id, TIME_TO_SEC(timediff (now(),`date`)) as sec FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE state=0 and id_mysql_server=" . $id_mysql_server;
                    $res = $db->sql_query($sql);

                    $num_rows = intval($db->sql_num_rows($res));
                    echo $num_rows." ";
                    //Debug::debug($num_rows, 'num_rows');

                    while ($ob = $db->sql_fetch_object($res)) {
                        if ($ob->sec > 10) {
                            $sql2 = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`  SET `state`=2 WHERE thread_id=" . $ob->thread_id . " and id_mysql_server=" . $id_mysql_server;
                            $db->sql_query($sql2);

                            
                            //kill mysql process
                            $sql3 = "KILL " . $ob->thread_id . ";";
                            $db->sql_query($sql3);

                            //kill php process
                            shell_exec("kill -9 " . $pid);



                            $num_rows = 0;
                        }
                    }
                } while ($num_rows !== 0);
                
                echo "\n";
            }
            
            
            $db = Mysql::getDbLink($id_mysql_server);
            

            $sql4 = "SELECT count(1) as cpt FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE `state`=2 and run_number=" . $run_number . " and `id_mysql_server`=" . $id_mysql_server;
            Debug::sql($sql4);

            $cpt = 0;
            $res4 = $db->sql_query($sql4);
            while ($ob4 = $db->sql_fetch_object($res4)) {
                $cpt = $ob4->cpt;
            }
        } while ($cpt > 0);
    }

    public function runQuery($param) {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $query = base64_decode($param[1]);
        $run_number = intval($param[2]);

        $db = Mysql::getDbLink($id_mysql_server);

        // to be sure no other process working
        $sql3 = "SELECT count(1) as cpt FROM `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` WHERE state=0 AND id_mysql_server=" . $id_mysql_server;
        $res3 = $db->sql_query($sql3);
        while ($ob3 = $db->sql_fetch_object($res3)) {
            if ($ob3->cpt > 0) {
                throw new \Exception("One query already working to prevent any problem we kill this one");
            }
        }

        $thread_id = $db->sql_thread_id();
        $sql = "INSERT INTO `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "` (`id_mysql_server`,`run_number`,`date`,`query`,`thread_id`,`state`) "
                . "VALUES (" . $id_mysql_server . "," . $run_number . ", '" . date("Y-m-d H:i:s") . "','" . $db->sql_real_escape_string($query) . "'," . $thread_id . ", 0)";
        $db->sql_query($sql);

        Debug::debug($thread_id, "THREAD_ID");
        Debug::sql($query);

        $db->sql_query($query);

        $sql2 = "UPDATE `" . self::TABLE_SCHEMA . "`.`" . self::TABLE_NAME . "`  SET `state`=1 WHERE thread_id=" . $thread_id;
        $db->sql_query($sql2);
        $db->sql_close();
    }

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
        $res2 = $db->sql_query($sql2);

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

    public function deleteWorkTable($param) {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];

        $db = Mysql::getDbLink($id_mysql_server);
        $sql = "select count(1) as cpt FROM information_schema.tables where table_name = '" . self::TABLE_NAME . "' and table_schema='" . self::TABLE_SCHEMA . "';";
        Debug::debug($sql);
        $res = $db->sql_query($sql);

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
    private function sigHandler($signo) {
        switch ($signo) {
            case SIGTERM:
                echo "Reçu le signe SIGTERM...\n";
                
                exit;
                break;

            case SIGUSR1:
                break;

            case SIGUSR2:
                break;

            case SIGHUP:

                // gestion du redémarrage
                //ne marche pas au second run pourquoi ?
                echo "Reçu le signe SIGHUP...\n";
                $this->sighup();

                break;

            default:

                echo "RECU LE SIGNAL : " . $signo;
// gestion des autres signaux
        }
    }

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




    function extractTablesFromSQL(string $sql): array {
        $tables = [];

        // Nettoyage sommaire pour éviter les faux positifs dans les commentaires ou chaînes
        $sql = preg_replace('/--.*?(\r?\n|$)|\/\*.*?\*\//s', ' ', $sql); // commentaires
        $sql = preg_replace('/(["\']).*?\1/s', '?', $sql); // chaînes en quote

        // Pattern général : FROM, JOIN, INTO, UPDATE, DELETE FROM
        $regex = '/
            (?i)\b
            (?:from|join|update|into|delete\s+from)      # mots-clés SQL
            \s+
            (?:                                           # soit base.table
                `?([a-zA-Z0-9_]+)`?\.`?([a-zA-Z0-9_]+)`?  # avec base
            |
                `?([a-zA-Z0-9_]+)`?                       # ou juste table
            )
            (?:\s+partition\s*\([^)]+\))?                 # ignore PARTITION si présent
            (?:\s+as)?\s+`?[a-zA-Z0-9_]+`?                # ignore alias éventuel
            ?
            /x';

        $matches = [];

        if (preg_match_all($regex, $sql, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (!empty($match[1]) && !empty($match[2])) {
                    // base.table
                    $tables[] = [
                        'database' => $match[1],
                        'table'    => $match[2]
                    ];
                } elseif (!empty($match[3])) {
                    // juste table
                    $tables[] = [
                        'database' => null,
                        'table'    => $match[3]
                    ];
                }
            }
        }

        return $tables;
    }



    function extractTablesAndAliases(string $sql): array {
        $tables = [];

        // Nettoyer : commentaires et chaînes de caractères
        $sql = preg_replace('/--.*?(\r?\n|$)|\/\*.*?\*\//s', ' ', $sql);
        $sql = preg_replace('/(["\']).*?\1/s', '?', $sql);

        // REGEX corrigée
        $regex = '/
            \b(?:from|join|update|into|delete\s+from)\s+       # mots-clés
            (?:
                `?([a-zA-Z0-9_]+)`?\.`?([a-zA-Z0-9_]+)`?        # base.table
                |
                `?([a-zA-Z0-9_]+)`?                             # ou juste table
            )
            (?:\s+partition\s*\([^)]+\))?                       # PARTITION ignoré
            \s+(?:as\s+)?`?([a-zA-Z0-9_]+)`?                    # alias après partition
        /ix';

        if (preg_match_all($regex, $sql, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $database = $m[1] ?? null;
                $table    = $m[2] ?? $m[3];
                $alias    = $m[4] ?? null;

                $tables[] = [
                    'database' => $database,
                    'table'    => $table,
                    'alias'    => $alias,
                ];
            }
        }

        return $tables;
    }


    function extractTablesWithOffsets(string $sql): array {
        $results = [];

        $reserved = [
        'on','left','right','inner','outer','cross','where','group','having','order','limit',
        'union','join','from','into','using','natural','and','or','not','when','then','else',
        'end','case','as','select','desc','asc','window','over'
        ];

        // Nettoyage (on garde les positions, donc on n'altère pas les longueurs)
        $cleanSql = preg_replace_callback('/(["\']).*?\1|--.*?$|\/\*.*?\*\//ms', function ($m) {
            return str_repeat(' ', strlen($m[0])); // preserve length with spaces
        }, $sql);

        // REGEX : FROM / JOIN / etc + [db.]table + partition + alias
        $regex = '/
            \b(from|join|update|into|delete\s+from)\s+                   # clause
            (?:`?([a-zA-Z0-9_]+)`?\.`?([a-zA-Z0-9_]+)`?                  # db.table
            |
            `?([a-zA-Z0-9_]+)`?)                                         # ou table seule
            (?:\s+partition\s*\([^)]+\))?                                # partition ignorée
            (?:\s+(?:as\s+)?(["\'`]?)([a-zA-Z0-9_]+)\\5)?                # alias
        /ix';

        if (preg_match_all($regex, $cleanSql, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {

            Debug::debug($matches);

            foreach ($matches as $m) {
                $offset = $m[0][1];
                $line = substr_count(substr($cleanSql, 0, $offset), "\n") + 1;

                $database = $m[2][0] ?? null;
                $table    = empty($m[3][0])?  $m[4][0] : $m[3][0]; 
                //$alias    = $m[5][0] ?? null;

                $quote    = $m[5][0] ?? null; // ' " `
                $aliasRaw = $m[6][0] ?? null;

                if ($aliasRaw !== null) {
                    $alias = $aliasRaw;
                    $isQuoted = in_array($quote, ['`', '"', "'"], true);

                    // Si non quoted ET mot-clé => on ignore
                    if (!$isQuoted && in_array(strtolower($alias), $reserved, true)) {
                        $alias = null;
                    }
                } else {
                    $alias = null;
                }

                $results[] = [
                    'database' => $database,
                    'table'    => $table,
                    'alias'    => $alias,
                    'offset'   => $offset,
                    'line'     => $line,
                ];
            }
        }

        // test case : https://chatgpt.com/c/685d6a2d-56e4-8006-a371-bb7682c00f9f

        return $results;
    }

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

}
