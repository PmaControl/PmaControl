<?php

declare(ticks=1);

namespace App\Controller;

use App\Library\Extraction;
use App\Library\Extraction2;
use \Glial\Synapse\Controller;
use \App\Library\Mysql;
use \App\Library\Debug;
use \Glial\Cli\SetTimeLimit;
use \Glial\Sgbd\Sgbd;

class Query extends Controller {

    const TABLE_NAME = 'tmp_setdefault';
    const TABLE_SCHEMA = 'dba';
    const LOG_FILE = TMP . "log/query.log";

            // ajouter tout mot-clé à exclure
    static $exceptions = ['ALL','AND','AS','CAST','COALESCE','COUNT', 'CURRENT_USER','DATE','DATE_ADD','DATE_SUB','DECIMAL','FROM',
    'GROUP_CONCAT','IF','IN','JOIN','NOW', 'MAX', 'MIN', 'PARTITION',
        'PASSWORD','SCHEMA','SUBSTRING','SUM','TIMESTAMPDIFF','UNION','USING','VALUES','WARNINGS', 'WHERE']; 


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



    public static function statement_digest_text_strict(string $sql, int $max_digest_length = 1024, int $show_length = 1024, bool $ansi_quotes = false): string {
        $norm = self::normalize_sql_strict($sql, $ansi_quotes);
        // maintenir la version pour affichage (troncature moins stricte)
        $visible = (mb_strlen($norm, '8bit') > $show_length) ? mb_substr($norm, 0, $show_length, '8bit') : $norm;
        return $visible;
    }

    public static function statement_digest_strict(string $sql, int $max_digest_length = 1024, bool $ansi_quotes = false): string {
        $norm = self::normalize_sql_strict($sql, $ansi_quotes);
        $forHash = (mb_strlen($norm, '8bit') > $max_digest_length) ? mb_substr($norm, 0, $max_digest_length, '8bit') : $norm;
        $forHash = trim($forHash); // connais le problème du trim manquant
        $forHash = mb_convert_encoding($forHash, 'UTF-8', 'auto');
        $forHash = preg_replace('/^\xEF\xBB\xBF/', '', $forHash); // enlever BOM
        return md5($forHash);
    }


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
    public static function digest2(string $query): string {
        $normalized = self::normalize($query);
        // MariaDB digest est 16 octets hex (md5-like)
        return md5($normalized);
    }

    public static function digestText(string $query): string {
        return self::normalize($query);
    }

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





    
public function digest($param)
{
    Debug::parseDebug($param);

    $id_mysql_server = $param[0];
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
            WHERE digest = '".$db->sql_real_escape_string($digest)."'";
              


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
        $sql_fb = "SELECT SCHEMA_NAME, DIGEST_TEXT, DIGEST
                   FROM performance_schema.events_statements_summary_by_digest
                   WHERE DIGEST = '".$extra->sql_real_escape_string($digest)."'
                   LIMIT 1";

        $res_fb = $extra->sql_query($sql_fb);
        $data = $extra->sql_fetch_array($res_fb, MYSQLI_ASSOC);




        if (empty($data)) {
            throw new \Exception("Digest '$digest' not found locally or on server $id_mysql_server");
        }
    }

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
    $data['tables'] = $this->extractTablesWithOffsets($data['sql_text']);
    foreach ($data['tables'] as $key => $tbl) {
        if (empty($tbl['alias'])) {
            $data['tables'][$key]['alias'] = $tbl['table'];
        }
        $data['alias'][$data['tables'][$key]['alias']] = $data['tables'][$key];
    }

    // 4) Index & cardinalité
    $data['index_info'] = [];
    foreach ($data['tables'] as $tbl) {
        $table_name = $tbl['table'];

        $sql_idx = "SHOW INDEX FROM `".$extra->sql_real_escape_string($table_name)."`";
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
  @@wsrep_desync wsrep_desync, 
  @@wsrep_reject_queries wsrep_reject_queries, 
  @@wsrep_sst_donor_rejects_queries wsrep_sst_donor_rejects_queries, 
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
  @@ `wsrep_desync` `wsrep_desync`, 
  @@ `wsrep_reject_queries` `wsrep_reject_queries`, 
  @@ `wsrep_sst_donor_rejects_queries` `wsrep_sst_donor_rejects_queries`, 
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
