<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Library\Debug;
use \App\Library\Extraction2;
use \App\Library\Mysql;
use \App\Library\EngineV4;
use \Glial\Sgbd\Sgbd;

/*
 * ./glial Aspirateur testAllMysql 6 --debug
 * ./glial integrate evaluate --debug
 */

class Control extends Controller
{
    public $tables                = array("ts_value_general", "ts_value_slave");
    public $ext                   = array("int", "double", "text", "json");
    public $field_value           = array("int" => "bigint(20) unsigned NULL",
        "double" => "double NOT NULL", "text" => "text NOT NULL", "json" => "json CHECK (JSON_VALID(value))");
    public $primary_key_old           = array("ts_value_general" => "PRIMARY KEY (`id`, `date`)", "ts_value_slave" => "PRIMARY KEY (`id`,`date`)");

    public $primary_key           = array("ts_value_general" => "PRIMARY KEY (`date`,`id_ts_variable`, `id_mysql_server`)",
     "ts_value_slave" => "PRIMARY KEY (`date`,`id_ts_variable`, `id_mysql_server`)");

    public $index                 = array("ts_value_general" => " INDEX (`id_mysql_server`, `id_ts_variable`, `date`)",
        "ts_value_slave" => "INDEX (`id_mysql_server`, `id_ts_variable`, `date`)",
        "ts_date_by_server" => "UNIQUE KEY `id_mysql_server` (`id_mysql_server`,`id_ts_file`,`date`)"
    );

    //=> TODO a voir pour delete
    private $engine               = "rocksdb";
    private $engine_preference    = array("ROCKSDB");
    public $extra_field           = array("ts_value_slave" => "`connection_name` varchar(64) NOT NULL,", "ts_value_general" => "");
    //when mysql reach 80% of disk we start to drop partition
    const PERCENT_MAX_DISK_USED = 80;
    //0 = keep all partitions,
    public $partition_to_keep     = 90;


    private $logger;

    /*
     *
     * return space used on partition where is datadir of MySQL / MariaDB
     */

    public function checkSize($param)
    {
        Debug::parseDebug($param);

        $db      = Sgbd::sql(DB_DEFAULT);
        
        $id_mysql_server = 1;
        $val = Extraction2::display(array("information_schema::disks", "variables::datadir"), array($id_mysql_server));

        $data = $val[$id_mysql_server];
        $datadir = $data['datadir'];

        // Recherche du disque dont le 'Path' correspond le plus précisément à datadir
        $closestDisk = null;
        $maxPrefixLength = 0;
        
        foreach ($data['disks'] as $disk) {
            $path = $disk['Path'];
            // Vérifie si datadir commence par ce path
            if (strpos($datadir, $path) === 0) {
                $len = strlen($path);
                // On garde le path le plus long (donc le plus spécifique)
                if ($len > $maxPrefixLength) {
                    $maxPrefixLength = $len;
                    $closestDisk = $disk;
                }
            }
        }

        $percent = round($closestDisk['Used']/ $closestDisk['Total'] * 100);
        Debug::debug($percent);

        //$size = trim(shell_exec('cd '.$datadir.' && df -k . | tail -n +2 | sed ":a;N;$!ba;s/\n/ /g" | sed "s/\ +/ /g" | awk \'{print $5}\''));
        //Debug::debug($size, 'Size on /srv/mysql/data');
        //$percent = substr($size, 0, -1);
        //Debug::debug($percent);

        return $percent;
    }

    public function before($param = "")
    {
        $logger       = new Logger("Control");
        $file_log     = LOG_FILE;
        $handler      = new StreamHandler($file_log, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;

        $this->selectEngine();
    }

    public function selectEngine()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "select * from information_schema.ENGINES where SUPPORT = 'YES' and ENGINE in('".implode("','", $this->engine_preference)."');";
        $res = $db->sql_query($sql);

        $engine_possible = array();
        while ($ob              = $db->sql_fetch_object($res)) {
            $engine_possible[] = $ob->ENGINE;
        }

        foreach ($this->engine_preference as $engine) {
            if (in_array($engine, $engine_possible)) {
                $this->engine = $engine;
                return true;
            }
        }

        throw new \Exception("PMACTRL-991 : there is no engine in this list installed : '".implode(",", $this->engine_preference)."'", 80);
    }

    public function addPartition($param)
    {
        $partition_number = $param[0];
        $db               = Sgbd::sql(DB_DEFAULT);

        $combi = $this->makeCombinaison();

        foreach ($combi as $table) {
            $sql = "ALTER TABLE `".$table."` ADD PARTITION (PARTITION `p".$partition_number."` VALUES LESS THAN (".$partition_number.") ENGINE = ".$this->engine.");";

            Debug::sql($sql);
            $db->sql_query($sql);
            $this->logger->info($sql);
        }
    }

    private function makeCombinaison()
    {
        $combinaisons = array();

        foreach ($this->tables as $table) {
            foreach ($this->ext as $ext) {
                $combinaisons[] = $table."_".$ext;
            }
        }

        $combinaisons[] = "ts_date_by_server";
        return $combinaisons;
    }

    public function dropPartition($param)
    {
        $partition_number = $param[0];
        $db               = Sgbd::sql(DB_DEFAULT);


        $this->logger->warning("We gonna drop the partition : $partition_number");

        $combi = $this->makeCombinaison();

        foreach ($combi as $table) {
            $sql = "ALTER TABLE `".$table."` DROP PARTITION `p".$partition_number."`;";
            Debug::sql($sql);

            $db->sql_query($sql);

            $this->logger->info($sql);
        }
    }
    /*
     * récupérer la partition la plus vieille dans le but de l'effacé
     *
     * et la dernière
     */

    public function getMinMaxPartition()
    {
        $db    = Sgbd::sql(DB_DEFAULT);
        $combi = $this->makeCombinaison();

        $sql = "SELECT DISTINCT `PARTITION_NAME` FROM information_schema.partitions
            where table_name IN ('".implode("','", $combi)."') AND `PARTITION_NAME` IS NOT NULL;";
        $res = $db->sql_query($sql);

        $partitions = array();
        while ($ob         = $db->sql_fetch_object($res)) {
            $partitions[] = substr($ob->PARTITION_NAME, 1);
        }

        $older_partition['min']   = min($partitions);
        $older_partition['max']   = max($partitions);
        $older_partition['other'] = $partitions;

        return $older_partition;
    }

    public function getToDays($param)
    {
        $date = $param[0];
        $db   = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT TO_DAYS('".$date."') as number";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $partition = $ob->number;
        }

        return $partition;
    }


    /*
     * each 4 hour, used in crontab => # crontab -l -u www-data
     * check space and delete old partition
     * and create new parttion
     */

    public function service($param = "")
    {
        Debug::parseDebug($param);

        $partitions = $this->getMinMaxPartition();

        Debug::debug($partitions, "Partition  min & max");

        //we drop oldest parttion if free space is low

        
        $current_percent = $this->checkSize(array());

        if ($current_percent > self::PERCENT_MAX_DISK_USED) {
            $this->logger->notice('Usage of disk : '.$current_percent.' %');
            Debug::debug($partitions['min'], "Drop Partition");

            Debug::debug("if more than 2 partitions we gonna drop one");
            if (count($partitions['other']) > 2) {   //minimum we let two partitions
                
                $this->logger->warning('We will drop partition in 10sec : '.$partitions['min']." (size on disk $current_percent% > ".self::PERCENT_MAX_DISK_USED."%)");
                //delete server_*
                //System::deleteFiles("server");
                $this->refreshVariable($param);

                //pour laisser le temps de reintégrer les variables pour les serveurs dont les dernières infos se retrouveraient dans cette partitions
                Sleep(10);
                
                $this->dropPartition(array($partitions['min']));
            }
        }

        Debug::debug(count($partitions['other']), "nombre de partitions");

        //On drop les partitions supérieur a X jours
        if (count($partitions['other']) > $this->partition_to_keep && $this->partition_to_keep != 0) {
            //System::deleteFiles("server");
            $this->logger->warning("Max partition to keep reeched : ".$this->partition_to_keep);
                
            //pour laisser le temps de reintégrer les variables pour les serveurs dont les dernières infos se retrouveraient dans cette partitions
            Sleep(10);

            $this->dropPartition(array($partitions['min']));
        }

        $part = $this->getDates();

        Debug::debug($part);

        // check partition of today and tomorow and create it if it's not exist
        foreach ($part as $date) {
            $partition_to_check = $this->getToDays(array($date));

            Debug::debug($partition_to_check);

            if (!in_array($partition_to_check, $partitions['other'])) {
                $this->addPartition(array($partition_to_check));
            }
        }

        $this->refreshVariable(array());

        $this->delMd5File($param);
    }

    public function dropTsTable($param = array())
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $combi = $this->makeCombinaison();

        foreach ($combi as $table) {
            $sql = "DROP TABLE IF EXISTS `".$table."`;";
            $db->sql_query($sql);
            Debug::sql($sql);

            $this->logger->info($sql);
        }

        $this->truncateTsVariable();
        $this->truncateTsMaxDate();
        $this->truncateTsFile();
        //System::deleteFiles("server");
    }

    public function createTsTable()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $dates = $this->getDates();

        foreach ($this->tables as $table) {
            foreach ($this->ext as $ext) {
                $table_name = $table."_".$ext;

                $sql = "CREATE TABLE `".$table_name."` (
  `date` datetime NOT NULL,
  `id_mysql_server` int(11) NOT NULL,
  `id_ts_variable` int(11) NOT NULL,
  ".$this->extra_field[$table]."
  `value` ".$this->field_value[$ext].",
  ".$this->primary_key[$table]."
) ENGINE=".$this->engine." DEFAULT CHARSET=latin1
PARTITION BY RANGE (to_days(`date`))
(";

                $partition = array();
                foreach ($dates as $date) {
                    $partition_nb = $this->getToDays(array($date));
                    $partition[]  = "PARTITION `p".$partition_nb."` VALUES LESS THAN (".$partition_nb.") ENGINE = ".$this->engine."";
                }
                $sql .= implode(",", $partition).")";

                $db->sql_query($sql);
                echo Debug::sql($sql);

                $db->sql_query("ALTER TABLE `".$table_name."` ADD ".$this->index[$table].";");

                echo Debug::sql($sql);
                $this->logger->info($sql);
            }
        }



        $sql = "CREATE TABLE `ts_date_by_server` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_mysql_server` int(11) NOT NULL,
  `id_ts_file` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `is_listened` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`,`date`),
  UNIQUE KEY `id_mysql_server` (`id_mysql_server`,`id_ts_file`,`date`)
) ENGINE=".$this->engine." DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT
            PARTITION BY RANGE (to_days(`date`))
(";

        $partition = array();
        foreach ($dates as $date) {
            $partition_nb = $this->getToDays(array($date));
            $partition[]  = "PARTITION `p".$partition_nb."` VALUES LESS THAN (".$partition_nb.") ENGINE = ".$this->engine."";
        }
        $sql .= implode(",", $partition).")\n";

        echo Debug::sql($sql);

        $db->sql_query($sql);
    }

    public function rebuildAll($param = "")
    {
        $db = Sgbd::sql(DB_DEFAULT);

        Debug::parseDebug($param);

        $php = explode(" ", shell_exec("whereis php"))[1];
        $cmd = $php." ".GLIAL_INDEX." Daemon stopAll";
        Debug::debug($cmd);
        shell_exec($cmd);

        usleep(500);

        $this->dropTsTable();

        $this->createTsTable();

        //drop lock sur
        Mysql::onAddMysqlServer();
        $this->dropAllFile();

        //$cmd = $php." ".GLIAL_INDEX." Daemon startAll";
        //Debug::debug($cmd);
        //shell_exec($cmd);

        //sleep(1);
        //$this->dropLock();
    }

    public function statistique($param = "")
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $combi = $this->makeCombinaison();

        $sql = "SELECT `TABLE_NAME`,`PARTITION_NAME`,`SUBPARTITION_NAME` ,`TABLE_ROWS` FROM information_schema.partitions
            where table_name IN ('".implode("','", $combi)."') AND `PARTITION_NAME` IS NOT NULL;";
            
        Debug::sql($sql);
    }

    private function getDates()
    {
        $today = date("Y-m-d");

        $date   = new \DateTime($today);
        $date->modify('+1 day');
        $part[] = $date->format('Y-m-d');
        $date->modify('+1 day');
        $part[] = $date->format('Y-m-d');

        return $part;
    }


    private function dropFile($diretory)
    {
        Debug::parseDebug($param);

        foreach(glob($diretory."*") as $filename) {
            if (! is_dir($filename)) {
                if ($filename == ".gitignore") {
                    continue;
                }

                Debug::debug($filename, "file deleted");
                unlink($filename);
            }
        }
    }

    public function dropAllFile($param = "")
    {
        Debug::parseDebug($param);

        // drop variables
        $directories = array(EngineV4::PATH_LOCK, EngineV4::PATH_MD5, EngineV4::PATH_PIVOT_FILE);
        foreach($directories as $directory) {
            $this->dropFile($directory);
        }
    }

    /*
     * Rafraichie les variables qui ont été dropé avec la partition
     *
     *
     */

    public function refreshVariable($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "WITH `z` as (select `id`,id_ts_file from `ts_variable` where `name` = 'version' and `from`='variables')
SELECT `a`.`id_mysql_server`, b.file_name,a.date, a.date_p4 FROM `ts_max_date` `a`
INNER JOIN `ts_file` `b` ON `a`.`id_ts_file` = `b`.`id`
LEFT JOIN `ts_value_general_text` c ON c.date = a.date_p4 AND a.id_mysql_server = c.id_mysql_server AND c.id_ts_variable = (SELECT id from z)
WHERE b.id in (select id_ts_file from z) AND c.id is null;";

        Debug::sql($sql);
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $file = EngineV4::getFileMd5($ob->file_name, $ob->id_mysql_server);
            
            if (file_exists($file)) {

                if ($file == ".gitignore") {
                    continue;
                }

                unlink($file);
                Debug::debug("Drop du fichier de variable pour le serveur : ".$ob->id_mysql_server);
            }
        }
    }

    public function purgefrm($param)
    {
        Debug::parseDebug($param);

        shell_exec("apt purge mariadb-plugin-rocksdb");

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SHOW GLOBAL VARIABLES LIKE 'datadir'";

        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res)) {
            $datadir = $arr[1];
        }

        $sql = "SELECT `database` FROM mysql_server where name ='".DB_DEFAULT."';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $database = $ob->database;
        }

        $combi = $this->makeCombinaison();

        foreach ($combi as $table) {
            $file = $datadir.$database."/".$table.".frm";

            if (file_exists($file)) {
                $cmd = "rm ".$file;

                Debug::debug($cmd);
                shell_exec($cmd);
            }
        }

        $file = $datadir.'#rocksdb';

        if (is_dir($file)) {
            $cmd = "rm -rvf ".$file;

            Debug::debug($cmd);
            shell_exec($cmd);
        }

        $cmd2 = "apt install mariadb-plugin-rocksdb";
        Debug::debug($cmd2);
        shell_exec($cmd2);
    }


    public function truncateTsVariable()
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "TRUNCATE TABLE `ts_variable`";

        $db->sql_query($sql);
    }


    public function truncateTsMaxDate()
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "TRUNCATE TABLE `ts_max_date`";

        $db->sql_query($sql);
    }

    public function truncateTsFile()
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "DELETE FROM `ts_file`;";

        $db->sql_query($sql);
    }

    public function delMd5File($param)
    {
        Debug::parseDebug($param);

        $directory = TMP."md5/";

        //   delete md5 more than 1 day
        $cmd = 'find "'.$directory.'" -type f -mtime +0 ! -name ".gitignore" -delete';
        //$cmd = 'find "'.$directory.'" -type f -mmin +60 -exec rm -f {} \;';

        Debug::debug($cmd);

        shell_exec($cmd);
    }
}