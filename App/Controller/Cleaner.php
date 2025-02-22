<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK
 *
 * 15% moins bien lors du chargement par rapport à une sauvegarde générer avec mysqldump
 * le temps de load peut être optimisé
 */

// Installation des gestionnaires de signaux
declare(ticks=1);

namespace App\Controller;

use Glial\Synapse\Controller;
use Glial\Synapse\FactoryController;
use Glial\Cli\Table;
use \Glial\I18n\I18n;
use \Glial\Cli\Color;
use App\Library\Chiffrement;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \Glial\Sgbd\Sql\Mysql\Compare;
use \Glial\Synapse\Basic;
use \App\Library\Debug;
use \App\Library\Mysql;
use App\Library\Display;
use App\Controller\Test\CleanerTest;
use \Glial\Sgbd\Sgbd;

class Cleaner extends Controller
{

    use \App\Library\Scp;
    use \App\Library\Filter;
    var $id_cleaner  = 0;
//status to check
    private $com_status = array();

    const FIELD_LOOP = "pmactrol_purge_loop";

    public $color                  = true;
    public $prefix                 = "DELETE_";
    public $link_to_purge;
    public $libelle; //name of cleaner
    public $schema_to_purge;
    public $schema_main;
    public $schema_delete          = "CLEANER";
    public $table_to_purge         = array();
    public $main_field             = array(); // => needed
    public $main_table;
    public $init_where;
    private $table_in_error        = array();
    private $rows_to_delete        = array();
    public $foreign_keys           = array();
    private $table_impacted        = array();
    public $backup_dir             = DATA."cleaner/";
    private $path_to_orderby_tmp   = "";
    private $orderby               = array();
    public $id_backup_storage_area = 0;
    private $sql_hex_for_binary    = false;
    private $fk_circulaire         = array();
    var $logger;
    private $cache_table           = array();
    private $primary_key           = array();
    private $com_to_check          = array("Com_create_table", "Com_alter_table", "Com_rename_table", "Com_drop_table");
    private $id_mysql_server       = 0;
    private $limit                 = 1000;
    private $table_filter          = array();
    private $children              = array();
    private $wait_time             = 1;
    public $db;
    public $testfk                 = array();

    //public $ariane_module = '<i class="glyphicon glyphicon-trash"></i> '.__("Cleaner");
    //pblic $ariane = '> <a href="'.LINK.'setting/plugin"><i class="fa fa-puzzle-piece"></i> '.__('Plugins').'</a> > ';


    public function statistics($param)
    {
        $this->title = '<i class="fa fa-area-chart" aria-hidden="true"></i> '.__("Statistics");

        $id_cleaner = $this->get_id_cleaner($param);

        if (Basic::from(__FILE__)) {
            return $this->title;
        }

        $data['id_cleaner'] = $id_cleaner;

        //https://github.com/chartjs/chartjs-plugin-zoom

        $this->di['js']->addJavascript(array("moment.js", "Chart.bundle.js", "hammer.min.js", "chartjs-plugin-zoom.js")); //, "hammer.min.js", "chartjs-plugin-zoom.js")
        $db = Sgbd::sql(DB_DEFAULT);

        // si qqn a qq chose de mieux je suis preneur

        $sql = "select `table`  as t,sum(b.`row`),avg(b.`row`),min(b.`row`),max(b.`row`)  from pmacli_drain_process a
            INNER JOIN  pmacli_drain_item b ON a.id = b.id_pmacli_drain_process
            WHERE a.id_cleaner_main = ".$data['id_cleaner']." AND a.item_deleted !=0
           GROUP BY `table`";

        $res = $db->sql_query($sql);

        $labels = array();
        while ($ob     = $db->sql_fetch_object($res)) {
            $labels[] = $ob->t;
        }

        sort($labels);

        $sql2 = "SELECT a.date_start, a.time, b.`table` ,b.`row` as `row` FROM pmacli_drain_process a
            INNER JOIN pmacli_drain_item b ON b.id_pmacli_drain_process = a.id
            WHERE a.id_cleaner_main=".$data['id_cleaner']."  "
            ."AND a.date_start >= date_add(now(),INTERVAL-1 DAY);";

        /*
         *         $sql2 = "SELECT a.date_start, a.time, b.`table` ,b.`row` as `row` FROM pmacli_drain_process a
          INNER JOIN pmacli_drain_item b ON b.id_pmacli_drain_process = a.id
          WHERE a.id_cleaner_main=9
          AND a.date_start >= date_add(now(),INTERVAL-1 DAY);";
         */

        $datasets = array();
        $res2     = $db->sql_query($sql2);

        $date = "";
        while ($ob   = $db->sql_fetch_object($res2)) {


            if ($date !== $ob->date_start) { // pour focer le remplissage à 0 si la ligne n'existe pas
                foreach ($labels as $label) {
                    $data[$label][$ob->date_start] = "{ x: new Date('".$ob->date_start."'), y: 0}";
                    //$data[$label][$ob->date_start] = "0";
                }
            }

            $data[$ob->table][$ob->date_start] = "{ x: new Date('".$ob->date_start."'), y: ".$ob->row."}";
            $date                              = $ob->date_start;
        }

        $datajs = "";

        $i = 0;
        foreach ($labels as $label) {
            if (!empty($data[$label])) {
                $points[$label] = implode(',', $data[$label]);

                $datajs .= '{
    label: "'.$label.'",
    data: ['.$points[$label].'],

    backgroundColor: "'.$this->getrgba($label, 0.3).'",
    borderColor: "'.$this->getrgba($label, 0.5).'",
    pointBorderColor: "'.$this->getrgba($label, 0.1).'",
    pointBackgroundColor: "'.$this->getrgba($label, 0.2).'",
    borderWidth: 1,
    pointBorderWidth: 2,
    pointRadius :3,
    lineTension:0,
    ';

                if ($i != 0) {
                    $datajs .= 'fill: "-1"';
                }
                $datajs .= '
},';
            }
            $i++;
        }


        //stacked


        $this->di['js']->code_javascript('
var ctx = document.getElementById("myChart").getContext("2d");

var myChart = new Chart(ctx, {
    type: "line",
    data: {
    datasets: ['.$datajs.']
    },
    pan: {
        enabled: true,
        mode: "xy"
    },

    zoom: {
        enabled: true,
        mode: "xy",
    },

    options: {
        tooltips: {
                mode: "index",
        },
        hover: {
                mode: "index"
        },
        spanGaps: false,
        title: {

            display: true,
            text: "Cleaner (Number of rows deleted by run)",
            position: "top",
            padding: "0"
        },
        pointDot : false,
        scales: {
            xAxes: [{



                type: "time",
                display: true,
                distribution: "linear",

                scaleLabel: {
                  display: true,
                  labelString: "Date",
                },

                time: {
                    tooltipFormat: "dddd YYYY-MM-DD, HH:mm:ss",
                    displayFormats: {
                        minute: "dddd YYYY-MM-DD, HH:mm"
                    }
                }
            }],
        yAxes: [{


      stacked: true,
      scaleLabel: {
        display: true,
        labelString: "rows deleted",

      }

    }]
        }
    }
});



');

        //stacked: true,


        $this->set('data', $data);

        return $this->title;
    }

    /*
    function getIdMysqlServer($name)
    {

        $default = Sgbd::sql(DB_DEFAULT);

        $sql                 = "SELECT id FROM mysql_server WHERE name ='".$name."';";
        $res_id_mysql_server = $default->sql_query($sql);
        if ($default->sql_num_rows($res_id_mysql_server) == 1) {
            $ob              = $default->sql_fetch_object($res_id_mysql_server);
            $id_mysql_server = $ob->id;
        } else {
            throw new \Exception("PMACTRL-001 : Impossible to find the MySQL server");
        }

        return $id_mysql_server;
    }*/

    function getMsgStartDaemon($ob)
    {
        $table = new Table(2);

        echo "[".date("Y-m-d H:i:s")."] Starting deamon for cleaner ...".PHP_EOL;

        $table->addHeader(array("Parameter", "Value"));
        $table->addLine(array("SERVER_TO_PURGE", $ob->link_to_purge));
        $table->addLine(array("DATABASE_TO_PURGE", $ob->schema_to_purge));
        //$table->addLine(array("TABLES_TO_SET_FIRST", implode(",", $ob->table_to_purge)));
        $table->addLine(array("INIT_DATA_WITH", $ob->main_table));
        $table->addLine(array("QUERY", $ob->query));
        $table->addLine(array("wait_time", $ob->wait_time_in_sec));

        echo $table->display();
    }

    public function showDaemon()
    {
        $db            = Sgbd::sql(DB_DEFAULT);
        $sql           = "SELECT * FROM `pmacli_drain_process` order by date_start DESC LIMIT 5000;";
        $data['clean'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

    public function index($param)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        /** new cleaner with UI * */
        $sql = "SELECT *,a.id as id_cleaner_main,
            b.name as mysql_server_name,c.`libelle` as env, c.`class`, a.libelle as name_cleaner, a.database as db
        FROM cleaner_main a
        INNER JOIN mysql_server b ON a.id_mysql_server = b.id
        INNER JOIN environment c ON b.id_environment = c.id
        WHERE 1=1 ".self::getFilter(array(), "b").";";

        $data['cleaner_main'] = $db->sql_fetch_yield($sql);
        $sql                  = "SELECT DISTINCT `name` FROM pmacli_drain_process;";
        $data['cleaner_name'] = $db->sql_fetch_yield($sql);
        $data['cleaner_name'] = iterator_to_array($data['cleaner_name']);
        $data['id_cleaner']   = empty($param[0]) ? 0 : $param[0];
        $data['menu']         = empty($param[1]) ? "log" : $param[1];

        $this->title = '<i class="glyphicon glyphicon-erase"></i> '.__("Cleaner");
        $this->di['js']->addJavascript(array('jquery-latest.min.js',
            'cleaner/index.cleaner.js'
        ));

        $this->set('data', $data);
    }

    public function treatment($param)
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql                = "SELECT * FROM `pmacli_drain_process` WHERE `id_cleaner_main`='".$param[0]."' ORDER BY date_start DESC LIMIT 100";
        $data['treatment']  = $db->sql_fetch_yield($sql);
        $data['id_cleaner'] = $param[0];
        $this->set('data', $data);
    }

    public function detail($param)
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $tmp = explode('/', $_GET['url']);
        $var = end($tmp);

        $sql            = "SELECT * FROM pmacli_drain_item WHERE id_pmacli_drain_process = '".$var."' order by `table`";
        $data['detail'] = $db->sql_fetch_yield($sql);

        $sql         = "SELECT a.`table`, avg(row) as row FROM pmacli_drain_item a
        INNER JOIN pmacli_drain_process b ON a.id_pmacli_drain_process = b.id
        WHERE b.id_cleaner_main = '".$param[0]."'
        GROUP BY a.`table`";
        $data['avg'] = $db->sql_fetch_yield($sql);
//var_dump($sql);

        $this->set('data', $data);
    }

    public function add($param)
    {

        $db = Sgbd::sql(DB_DEFAULT);
        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js",
            "jquery.autocomplete.min.js", "cleaner/add.cleaner.js"));

        $this->title  = '<i class="glyphicon glyphicon-plus"></i> '.__('Add a cleaner');
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins'
            .' <i class="glyphicon glyphicon-trash"></i> '.__("Cleaner")." > ".$this->title;

        if (!empty($param[0])) {
            $id_cleaner = $param[0];
            $data       = $param[1];
        } else {
            $data['databases'] = array();
            $data['table']     = array();
        }

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            $cleaner_main['cleaner_main']                 = $_POST['cleaner_main'];
            $cleaner_main['cleaner_main']['id_user_main'] = $this->di['auth']->getUser()->id;

            if (empty($cleaner_main['cleaner_main']['id'])) {
                unset($cleaner_main['cleaner_main']['id']);
            }

            if (!empty($cleaner_main['cleaner_main']['is_crypted']) && $cleaner_main['cleaner_main']['is_crypted'] === "on") {
                $cleaner_main['cleaner_main']['is_crypted'] = 1;
            } else {
                $cleaner_main['cleaner_main']['is_crypted'] = 0;
            }


            $id_cleaner_main = $db->sql_save($cleaner_main);

            if ($id_cleaner_main) {


                if (!empty($cleaner_main['cleaner_main']['id'])) {


                    $sql = "SELECT * FROM cleaner_main WHERE id =".$id_cleaner_main;
                    $res = $db->sql_query($sql);

                    while ($ob = $db->sql_fetch_object($res)) {

                        if ($ob->pid !== "0") {

                            if ($this->isRunning($this->id_cleaner) === true) {
                                $this->id_cleaner = $cleaner_main['cleaner_main']['id'];
                                $this->log("INFO", "RESTART", "We restart cleaner after successfull update of paramters");
                                $this->restart(array($cleaner_main['cleaner_main']['id']));
                            }
                        }
                    }
                }

                $msg   = I18n::getTranslation(__("Cleaner updated with success"));
                $title = I18n::getTranslation(__("Success"));
                set_flash("success", $title, $msg);

                
                if (! IS_CLI)
                {
                    header("location: ".LINK."cleaner/index");
                }
                exit;
                //$this->exit();
            } else {

                $msg   = I18n::getTranslation(__("One storage engine is missing on this MySQL server"));
                $title = I18n::getTranslation(__("Error"));
                set_flash("error", $title, $msg);

                $elems = explode('/', $_GET['glial_path']);
                unset($elems[0]);
                header('location: '.LINK.implode('/', $elems));
                exit;
            }
        }



        $sql     = "SELECT * FROM backup_storage_area order by `libelle`;";
        $servers = $db->sql_fetch_yield($sql);

        $data['backup_storage_area'] = [];
        foreach ($servers as $server) {
            $tmp                           = [];
            $tmp['id']                     = $server['id'];
            $tmp['libelle']                = $server['libelle']." (".$server['ip'].")";
            $data['backup_storage_area'][] = $tmp;
        }

        $sql     = "SELECT * FROM mysql_server order by `name`";
        $servers = $db->sql_fetch_yield($sql);

        $data['server'] = [];
        foreach ($servers as $server) {
            $tmp              = [];
            $tmp['id']        = $server['id'];
            $tmp['libelle']   = str_replace('_', '-', $server['name'])." (".$server['ip'].")";
            $data['server'][] = $tmp;
        }


        $data['wait_time'] = [];
        for ($i = 1; $i < 101; $i++) {
            $tmp                 = [];
            $tmp['id']           = $i;
            $tmp['libelle']      = $i;
            $data['wait_time'][] = $tmp;
        }

        $this->set('data', $data);

        return $data;
    }


    /*
        To replace with the one from common
    */
    function getDatabaseByServer($param)
    {

        $data = array();

        if (FactoryController::getRootNode()[1] === __FUNCTION__) {
            $this->layout_name = false;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($param[0])."';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {


            $db_to_get_db = Sgbd::sql($ob->name);
        }

        $sql = "SHOW DATABASES";
        $res = $db_to_get_db->sql_query($sql);

        $data['databases'] = [];
        while ($ob                = $db_to_get_db->sql_fetch_object($res)) {
            $tmp                 = [];
            $tmp['id']           = $ob->Database;
            $tmp['libelle']      = $ob->Database;
            $data['databases'][] = $tmp;
        }


        $this->set("data", $data);
        return $data;
    }
    /*
     *
     * deprecated !!
     */

    function getTableByDatabase($param)
    {
        $database = $param[0];

        if (FactoryController::getRootNode()[1] === __FUNCTION__) {
            $this->layout_name = false;
        }


        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($_GET['id_mysql_server'])."';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_server = $ob->id;
            $db_clean  = Sgbd::sql($ob->name);
        }

        $sql = "SELECT TABLE_NAME from `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '".$database."' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME";

        $res = $db_clean->sql_query($sql);

        $data['table'] = [];
        while ($ob            = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->TABLE_NAME;
            $tmp['libelle'] = $ob->TABLE_NAME;

            $data['table'][] = $tmp;
        }

        $this->set("data", $data);
        return $data;
    }

    function getColumnByTable($param)
    {

        $this->layout_name = false;
        $db                = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id,name FROM mysql_server WHERE id = '".$db->sql_real_escape_string($_GET['id_mysql_server'])."';";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_server = $ob->id;
            $db_clean  = Sgbd::sql($ob->name);
        }

        $sql = "show index from `".$_GET['schema']."`.`".$param[0]."`";
//$sql = "SELECT TABLE_NAME from `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '".$database."' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME";

        $res2 = $db_clean->sql_query($sql);

        $data['column'] = [];
        while ($ob             = $db->sql_fetch_object($res2)) {
            $tmp            = [];
            $tmp['id']      = $ob->Column_name;
            $tmp['libelle'] = $ob->Column_name;

            $data['column'][] = $tmp;
        }

        $this->set("data", $data);
    }

    function delete($param)
    {

        $this->view        = false;
        $this->layout_name = false;

        $id_cleaner = $param[0];
        $db         = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM cleaner_main WHERE id ='".$id_cleaner."'";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg   = I18n::getTranslation(__("The cleaner with the id :")." '".$id_cleaner."' ".__("doesn't exist"));
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
        } else {

            $ob = $db->sql_fetch_object($res);

            $res = $sql = "DELETE FROM cleaner_main where id ='".$id_cleaner."'";
            $db->sql_query($sql);

            $msg   = I18n::getTranslation(__("The cleaner has been deleted : ")."'".$ob->libelle."'");
            $title = I18n::getTranslation(__("Cleaner deleted"));
            set_flash("success", $title, $msg);
        }

        header("location: ".LINK."cleaner/index");
    }

    public function settings($param)
    {


        $db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js",
            "jquery.autocomplete.min.js", "cleaner/add.cleaner.js"));

        $this->title = __('Add a cleaner');

        $this->ariane = " > ".'<a href="'.LINK.'Cleaner/index/">'.__('Cleaner')."</a> > ".$this->title;

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            $data['cleaner_main'] = $_POST['cleaner_main'];

            $id_cleaner_main = $db->sql_save($data);

            if ($id_cleaner_main) {
                foreach ($_POST['cleaner_foreign_key'] as $data) {
                    $ob_foreign_key['cleaner_foreign_key']                    = $data;
                    $ob_foreign_key['cleaner_foreign_key']['id_cleaner_main'] = $id_cleaner_main;

                    $id_cleaner_foreign_key = $db->sql_save($ob_foreign_key);
                }

                if ($id_cleaner_foreign_key) {
                    header('location: '.LINK.'Cleaner/index/');
                }
            }
        }


        $sql     = "SELECT * FROM mysql_server order by `name`;";
        $servers = $db->sql_fetch_yield($sql);

        $data['server'] = [];
        foreach ($servers as $server) {
            $tmp = [];

            $tmp['id']      = $server['id'];
            $tmp['libelle'] = str_replace('_', '-', $server['name'])." (".$server['ip'].")";

            $data['server'][] = $tmp;
        }


        $data['wait_time'] = [];
        for ($i = 1; $i < 101; $i++) {
            $tmp = [];

            $tmp['id']      = $i;
            $tmp['libelle'] = $i;

            $data['wait_time'][] = $tmp;
        }

        $this->set('data', $data);
    }

    public function launch($param)
    {
        Debug::parseDebug($param);

        $id_cleaner       = $param[0];
        $this->id_cleaner = $id_cleaner;

        $default           = Sgbd::sql(DB_DEFAULT);
        $this->view        = false;
        $this->layout_name = false;
        $this->schema_main = $default->getDb();

        //$this->debug = true;

        pcntl_signal(SIGTERM, array($this, 'sig_handler'));
        pcntl_signal(SIGHUP, array($this, 'sig_handler'));
        pcntl_signal(SIGUSR1, array($this, 'sig_handler')); // active / desactive debug
        pcntl_signal(SIGUSR2, array($this, 'sig_handler')); // rechargement de la configuration ?

        $sql = "SELECT *,a.database, b.name as nameserver,a.id as id_cleaner_main
            FROM cleaner_main a
                INNER JOIN mysql_server b ON a.id_mysql_server = b.id
                WHERE a.id = '".$id_cleaner."';";

        $res = $default->sql_query($sql);

        while ($ob = $default->sql_fetch_object($res)) {

            $this->link_to_purge          = $ob->nameserver;
            $this->schema_to_purge        = $ob->database;
            $this->schema_delete          = $ob->cleaner_db;
            $this->prefix                 = $ob->prefix;
            $this->main_table             = $ob->main_table;
            $this->id_backup_storage_area = $ob->id_backup_storage_area;
            $this->backup_dir             = DATA."cleaner/".$this->id_cleaner;
            $this->init_where             = $ob->query;
            $this->libelle                = $ob->libelle;
            $this->wait_time              = $ob->wait_time_in_sec;
            $this->id_mysql_server        = $ob->id_mysql_server;
            $this->limit                  = $ob->limit;
        }

        $i = 1;

        $this->init();

// get COM

        while (true) {

            //export to archive controller ?
            //$this->pushArchive();

            $date_start = date("Y-m-d H:i:s");
            $time_start = microtime(true);

            Debug::checkPoint("Cleaner Start !!!");
            $ret = $this->purge();

            Debug::checkPoint("Cleaner Terminé !!!");

            if (empty($ret[$this->main_table])) {
                $ret[$this->main_table] = 0;
            }

            $this->stats_for_log($ret);

            $time_end = microtime(true);
            $date_end = date("Y-m-d H:i:s");

            $default = Sgbd::sql(DB_DEFAULT);

            $data                                            = array();
            $data['pmacli_drain_process']['id_mysql_server'] = $this->id_mysql_server;
            $data['pmacli_drain_process']['date_start']      = $date_start;
            $data['pmacli_drain_process']['date_end']        = $date_end;
            $data['pmacli_drain_process']['time']            = round($time_end - $time_start, 2);
            $data['pmacli_drain_process']['item_deleted']    = $ret[$this->main_table];
            $data['pmacli_drain_process']['id_cleaner_main'] = $id_cleaner;
            $data['pmacli_drain_process']['name']            = $this->libelle;
            $data['pmacli_drain_process']['time_by_item']    = 1; //@todo to remove from DB

            $res = $default->sql_save($data);

            if (!$res) {
                Debug::debug($default->sql_error());
                Debug::debug($data);

                throw new \Exception("PMACTRL-002 : Impossible to insert stat for cleaner (ID : ".$id_cleaner.")");
            } else {

                $id_pmacli_drain_process = $default->sql_insert_id();

                foreach ($ret as $table => $val) {

                    if (!empty($val)) {

                        $data                                                 = [];
                        $data['pmacli_drain_item']['id_pmacli_drain_process'] = $id_pmacli_drain_process;
                        $data['pmacli_drain_item']['row']                     = $val;
                        $data['pmacli_drain_item']['table']                   = $table;

                        $res = $default->sql_save($data);

                        if (!$res) {
                            Debug::debug($default->sql_error());
                            Debug::debug($data);

                            throw new \Exception("PMACTRL-003 : Impossible to insert an item for cleaner (ID : ".$id_cleaner.")");
                        }
                    }
                }
            }

            $i++;

            Debug::checkPoint("End loop");

            Debug::debug("Execution time : ".round($time_end - $time_start, 2)." sec - rows deleted : ".$ret[$this->main_table]);

            Debug::debugShowTime();
            Debug::debugPurge();

            sleep($this->wait_time);

            //to prevent mysql gone away (if stay long time connected)
            $default->sql_close();
            usleep(700);
        } // end while
    }

    function start($param)
    {
        
        Debug::parseDebug($param);

        $id_cleaner        = $this->get_id_cleaner($param);
        $db                = Sgbd::sql(DB_DEFAULT);
        $this->view        = false;
        $this->layout_name = false;

        $sql = "SELECT * FROM cleaner_main where id ='".$id_cleaner."'";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg   = I18n::getTranslation(__("Impossible to find the cleaner with the id : ")."'".$id_cleaner."'");
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            header("location: ".LINK."cleaner/index");
            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if ($ob->pid === "0") {

            $php = explode(" ", shell_exec("whereis php"))[1];

            //todo add error flux in the log

            $log_file = TMP."log/cleaner_".$ob->id.".log";

            $cmd = $php." ".GLIAL_INDEX." Cleaner launch ".$id_cleaner." >> ".$log_file." & echo $!";
            $pid = intval(trim(shell_exec($cmd)));

            if (IS_CLI) {
                $this->logger->info('[id_cleaner_main:'.$ob->id.'][START][pid:'.getmypid().'] "'.$ob->libelle.'" '."by".' [SYSTEM]');
            } else {
                $this->logger->info('[id_cleaner_main:'.$ob->id.'][START][pid:'.getmypid().'] "'.$ob->libelle.'" '."by".' '
                    .$this->di['auth']->getUser()->firstname." ".$this->di['auth']->getUser()->name." (id:".$this->di['auth']->getUser()->id.")");
            }

            $sql = "UPDATE cleaner_main SET pid ='".$pid."',log_file='".$log_file."' WHERE id = '".$id_cleaner."'";
            $db->sql_query($sql);

            $msg   = I18n::getTranslation(__("The cleaner id (".$id_cleaner.") successfully started with")." pid : ".$pid);
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);
            header("location: ".LINK."cleaner/index");
        } else {

            $this->logger->error('[id:'.$ob->id.'][ALREADY STARTED][pid:'.getmypid().'] "'.$ob->libelle.'" '."by".' [SYSTEM]');

            $msg   = I18n::getTranslation(__("Impossible to launch the cleaner with the id : ")."'".$id_cleaner."'"." ("."Already running !)");
            $title = I18n::getTranslation(__("Error"));
            set_flash("caution", $title, $msg);
            header("location: ".LINK."cleaner/index");
        }
    }

    function stop($param)
    {

        $id_cleaner        = $this->get_id_cleaner($param);
        $db                = Sgbd::sql(DB_DEFAULT);
        $this->view        = false;
        $this->layout_name = false;

        $sql = "SELECT * FROM cleaner_main where id ='".$id_cleaner."'";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) !== 1) {
            $msg   = I18n::getTranslation(__("Impossible to find the cleaner with the id : ")."'".$id_cleaner."'");
            $title = I18n::getTranslation(__("Error"));
            set_flash("error", $title, $msg);
            header("location: ".LINK."cleaner/index");
            exit;
        }

        $ob = $db->sql_fetch_object($res);

        if ($this->isRunning($ob->pid) === true) {
            $msg   = I18n::getTranslation(__("The cleaner was successfully stopped "))."pid : ".$ob->pid;
            $title = I18n::getTranslation(__("Success"));
            set_flash("success", $title, $msg);

            $cmd = "kill ".$ob->pid;
            shell_exec($cmd);
            shell_exec("echo '[".date("Y-m-d H:i:s")."] CLEANER STOPED !' >> ".$ob->log_file);

            $this->log('info', 'STOP', $ob->libelle." was stopped");
        } else {

            $this->log('info', 'ACKNOWLEDGE', $ob->libelle." was acknowledge");

            if (!IS_CLI) {

                $msg   = I18n::getTranslation(__("Impossible to find the cleaner with the pid : ")."'".$ob->pid."'");
                $title = I18n::getTranslation(__("Cleaner was already stopped or in error"));
                set_flash("caution", $title, $msg);
            }
        }

        $this->purge_clean_db(array());

        sleep(1);
        $db = Sgbd::sql(DB_DEFAULT);

        if ($this->isRunning($ob->pid) === false) {


            $sql = "UPDATE cleaner_main SET pid ='0' WHERE id = '".$id_cleaner."'";
            $res = $db->sql_query($sql);

            $this->log('debug', 'SQL', $sql);
            $this->log('warning', 'TRIED_KILL', $ob->libelle.'" CLEANER with pid ('.$ob->pid.') was killed');
        } else {


            $this->log('error', 'TRIED_KILL', $ob->libelle.'" IMPOSSIBLE TO KILL CLEANER with pid ('.$ob->pid.')');
            //$this->logger->error('[id:'.$ob->id.'][KILL][pid:'.getmypid().'] );
            throw new \Exception('PMACTRL-875 : Impossible to stop cleaner with pid : "'.$ob->pid.'"');
        }

        if (! IS_CLI)
        {
            header("location: ".LINK."cleaner/index");
        }
    }

    public function restart($param)
    {
        $id_cleaner = $this->get_id_cleaner($param);

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM cleaner_main where id ='".$id_cleaner."'";
        $res = $db->sql_query($sql);

        $ob = $db->sql_fetch_object($res);

        if (IS_CLI) {
            $this->logger->info('[id:'.$ob->id.'][RESTART][pid:'.getmypid().'] "'.$ob->libelle.'" '.__("by").' '."[SYSTEM]");
        }
        $this->stop(array($id_cleaner));
        $this->start(array($id_cleaner));
    }

    private function isRunning($pid)
    {

        if (empty($pid)) {
            return false;
        }

        $cmd   = "ps -p ".$pid;
        $alive = shell_exec($cmd);

        if (strpos($alive, $pid) !== false) {
            return true;
        }
        return false;
    }

    public function stats_for_log($data)
    {
        $table = new Table(0);

        $table->addHeader(array("Table", "Rows deleted"));

        foreach ($data as $table_name => $row_deleted) {
            $table->addLine(array($table_name, $row_deleted));
        }

        echo $table->display();

        //echo "errror : ".pcntl_strerror(pcntl_get_last_error())." : ".pcntl_get_last_error()."\n";

        Debug::debug("mypid : ".getmypid());
    }
    /*
     * utiliser seulement pour par le MPD
     */

    public function getTableImpacted($param)
    {
        Debug::parseDebug($param);
        Debug::debug($param);

        $id_cleaner = $param[0];
        $this->view = false; // required cannot be call directly from navigator
        $default    = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *, b.name as nameserver,a.id as id_cleaner_main, a.database as db
            FROM cleaner_main a
                INNER JOIN mysql_server b ON a.id_mysql_server = b.id
                WHERE a.id = '".$id_cleaner."'";

        $res = $default->sql_query($sql);

        while ($ob = $default->sql_fetch_object($res)) {
            $this->id_cleaner      = $id_cleaner;
            $this->link_to_purge   = $ob->nameserver;
            $this->schema_to_purge = $ob->db;
            $this->schema_delete   = $ob->cleaner_db;
            $this->prefix          = $ob->prefix;
            $this->main_table      = $ob->main_table;
        }

        return $this->getImpactedTable();
    }

    private function checkFileToPush($path)
    {
        $files = glob($path."/*_log.sql");

        foreach ($files as $key => $file) {
            if (pathinfo($file)['basename'] === date('Y-m-d')."_log.sql") {
                unset($files[$key]);
            }
        }

        Debug::debug($files, "Files to push");
        return $files;
    }

    private function compressAndCrypt($file, $is_cryted = true)
    {
        $stats['normal'] = $this->getFileinfo($file);

//compression
        $time_start      = microtime(true);
        $file_compressed = $this->compressFile($file);

        $stats['compressed']                   = $this->getFileinfo($file_compressed);
        $stats['compressed']['execution_time'] = round(microtime(true) - $time_start, 0);

        $stats['file_path'] = $file_compressed;
//chiffrement

        if ($is_cryted === true) {

            $time_start2                        = microtime(true);
            $file_crypted                       = $this->cryptFile($file_compressed);
            $stats['crypted']                   = $this->getFileinfo($file_crypted);
            $stats['crypted']['execution_time'] = round(microtime(true) - $time_start2, 0);
            $stats['file_path']                 = $file_crypted;

            Debug::debug("File has been crypted !");
        }


        return $stats;
    }

    private function getFileinfo($filename)
    {
        $data['size'] = filesize($filename);
        $data['md5']  = md5_file($filename); //Warning: md5_file(/data/www/pmacontrol/data/cleaner/1/2019-10-24_log.sql): failed to open stream: Permission denied in /data/www/pmacontrol/application/controller/Cleaner.controller.php on line 1116

        return $data;
    }

    public function cryptFile($file_name)
    {
        $this->view = false;
        $chiffre    = new Chiffrement(CRYPT_KEY);
        $chiffre->chiffre_fichier($file_name);
        return $file_name;
    }

    public function decryptFile($file_name)
    {
        $this->view = false;
        $chiffre    = new Chiffrement(CRYPT_KEY);
        $chiffre->dechiffre_fichier($file_name);

        return $file_name;
    }

    public function compressFile($path_file)
    {
        $path      = pathinfo($path_file)['dirname'];
        $file_name = pathinfo($path_file)['basename'];

        shell_exec("cd ".$path." && nice gzip ".$file_name);

        return $path."/".$file_name.".gz";
    }

    public function unCompressFile($path_file)
    {
        $path      = pathinfo($path_file)['dirname'];
        $file_name = pathinfo($path_file)['basename'];

        shell_exec("cd ".$path." && nice gzip -d ".$file_name);

        return substr($path_file, 0, -3);
    }

    public function uncc($param)
    {
        if (IS_CLI) {
            $file = $param[0];

            if (file_exists($file)) {
                $this->decryptFile($file);
                $this->unCompressFile($file);
            } else {
                echo "Impossible to find the file : ".$file;
            }
        }
    }

    public function purge_clean_db($param = array())
    {
        Debug::parseDebug($param);
        $this->get_id_cleaner($param);

        $db                = Sgbd::sql(DB_DEFAULT);
        $this->view        = false;
        $this->layout_name = false;

        $sql = "SELECT a.`cleaner_db`, a.`prefix`, b.`name` as nameserver,a.`id` as id_cleaner_main
            FROM `cleaner_main` a
                INNER JOIN `mysql_server` b ON a.`id_mysql_server` = b.`id`
                WHERE a.`id` = '".$this->id_cleaner."'";

        $res = $db->sql_query($sql);

        Debug::debug("On drop les tables de purge ...");

        while ($ob = $db->sql_fetch_object($res)) {

            $db_to_clean = Sgbd::sql($ob->nameserver,'purge');
            $db_to_clean->sql_select_db($ob->cleaner_db);
            $tables      = $db_to_clean->getListTable();

            if (!empty($tables['table'])) {
                foreach ($tables['table'] as $table) {

                    if (substr($table, 0, strlen($ob->prefix)) === $ob->prefix) {
                        $sql = "DROP TABLE IF EXISTS `".$ob->cleaner_db."`.`".$table."`;";

                        Debug::debug($sql);
                        $db_to_clean->sql_query($sql);
                    } else {
                        Debug::debug("We keep the table : '".$table."'");
                    }
                }
            }
        }

        $db->sql_close();

        Debug::checkPoint("Tables de purge effacé");
    }

// gestionnaire de signaux système
    private function sig_handler($signo)
    {

        switch ($signo) {
            case SIGTERM:

                if (empty($this->di['auth'])) {
                    $this->logger->warning('[id:'.$this->id_cleaner.'][SIGTERM][pid:'.getmypid().'] "'.$this->libelle.'" '."by".' '
                        ."[SYSTEM]");
                } else {
                    $this->logger->warning('[id:'.$this->id_cleaner.'][SIGTERM][pid:'.getmypid().'] "'.$this->libelle.'" '."by".' '
                        .$this->di['auth']->getUser()->firstname." ".$this->di['auth']->getUser()->name." (id:".$this->di['auth']->getUser()->id);
                }
                echo "Reçu le signe SIGTERM...\n";
                //$this->purge_clean_db(array());
                // gestion de l'extinction
                exit;

                break;

            case SIGUSR1:

                $this->logger->debug('[id:'.$this->id_cleaner.'][SIGUSR1][pid:'.getmypid().'] DEBUG : ON');
                Debug::$debug = true;
                Debug::debug("DEBUG : [ON] OFF");
                break;

            case SIGUSR2:

                //toggle active // desactive le mode debug
                $this->logger->debug('[id:'.$this->id_cleaner.'][SIGUSR2][pid:'.getmypid().'] DEBUG : OFF');
                Debug::$debug = false;
                Debug::debug("DEBUG : ON [OFF]");
                break;

            case SIGHUP:

                // gestion du redémarrage
                //ne marche pas au second run pourquoi ?

                echo "Reçu le signe SIGHUP...\n";
                $this->sighup();

                break;

            default:

                echo "RECU LE SIGNAL : ".$signo;
// gestion des autres signaux
        }
    }

    private function get_id_cleaner($param)
    {
        if (empty($param[0])) {

            if (empty($this->id_cleaner)) {
                throw new \Exception("PMACTRL-057 : id cleaner missing", 80);
            }

            $id_cleaner = $this->id_cleaner;
        } else {
            $id_cleaner       = $param[0];
            $this->id_cleaner = $id_cleaner;
        }

        return $id_cleaner;
    }
    /*     * *********************************** */
    /*     * *********************************** */
    /*     * *********************************** */
    /*     * *********************************** */
    /*     * *********************************** */
    /*     * *********************************** */
    /*     * *********************************** */
    /*     * *********************************** */
    /*     * *********************************** */
    /*     * *********************************** */

    public function purge()
    {
        $db = Sgbd::sql($this->link_to_purge,'purge');
        $db->sql_select_db($this->schema_to_purge);

        // to not affect history server, read : https://mariadb.com/kb/en/mariadb/documentation/replication/standard-replication/selectively-skipping-replication-of-binlog-events/
        // only if MariaDB (need try purge on MySQL)


        $this->skipReplication($db);

        $this->rows_to_delete = array();

        // a mettre ailleurs =)
        $this->compareComStatus();
        $this->compareDdl();

//feed id with main table


        Debug::debug($this->fk_circulaire);

        $pri          = $this->getPrimaryKey($this->main_table, $this->schema_to_purge);
        $primary_key  = "a.`".implode('`, a.`', $pri)."`";
        $primary_key2 = "`".implode('`, `', $pri)."`";

        $sql3 = "SELECT ".$primary_key." FROM `".$this->main_table."` a ".$this->init_where." LIMIT ".$this->limit.";"; // LOCK IN SHARE MODE



        Debug::sql($sql3);

        $sql2 = "SELECT * FROM `information_schema`.`KEY_COLUMN_USAGE` "
            ."WHERE `CONSTRAINT_SCHEMA` ='".$this->schema_to_purge."' "
            ."AND `REFERENCED_TABLE_SCHEMA`='".$this->schema_to_purge."' "
            ."AND `TABLE_NAME` ='".$this->main_table."' "
            ."AND REFERENCED_TABLE_NAME = '".$this->main_table."';";

        Debug::sql($sql2);

        $res2 = $db->sql_query($sql2);

        $nbline = $db->sql_num_rows($res2);

        $recursive = false;

        if ($nbline == 1) {
            $recursive = true;

            while ($ob2 = $db->sql_fetch_object($res2)) {
                $field_to   = $ob2->REFERENCED_COLUMN_NAME;
                $field_from = $ob2->COLUMN_NAME;
            }
        } else if ($nbline > 1) {
            throw new \Exception("PMACTRL-334 : more than one key on circular definition");
        }


        $have_data = false;

        if ($recursive) {

            $circular_field = ",`".self::FIELD_LOOP."`";
        } else {
            $circular_field = "";
        }

        $loop = 1;

        $nb_loop = 1;
        do {

            if ($recursive === true) {
                $circular_data = ",".($nb_loop);
            } else {
                $circular_data = "";
            }

            if ($nb_loop > 1 && $recursive === true) {

                // si la table principale a une pk sur 2 champs ca va peter :( a corriger
                $sql3 = "SELECT ".$primary_key." FROM `".$this->main_table."` a
                inner join      `".$this->main_table."` c ON c.`".$field_to."` = a.`".$field_from."`
                INNER JOIN  `".$this->schema_delete."`.`".$this->prefix.$this->main_table."` b ON c.$primary_key2 = b.$primary_key2
                and b.`".self::FIELD_LOOP."` = ".($nb_loop - 1)."";
            }

            $loop = false;

            Debug::sql($sql3);
            $res3 = $db->sql_query($sql3);

            $have_data = false;

            $sql = "REPLACE INTO `".$this->schema_delete."`.`".$this->prefix.$this->main_table."` (".$primary_key2."".$circular_field.") VALUES ";
            while ($arr = $db->sql_fetch_array($res3, MYSQLI_ASSOC)) {
                $have_data = true;
                $loop      = true;
                $sql       .= "('".implode("','", $arr)."'".$circular_data."),";
            }

            if ($have_data) {

                $sql = rtrim($sql, ",");

                Debug::debug($sql);
                $db->sql_query($sql);

                //Debug::debug($db->query, "BEFORE setAffectedRows");
                $this->setAffectedRows($this->main_table);

                if ($recursive === false) {
                    break;
                }

                //$this->exportToFile($this->main_table);
                //
            } else {
                if ($nb_loop === 1) {
                    $this->rows_to_delete[$this->main_table] = 0;
                    $this->end_loop();
                    sleep(1);

                    return $this->rows_to_delete;
                }
            }

            $nb_loop++;
        } while ($have_data === true);

        $this->exportToFile($this->main_table);

//feed table in the right order to delete later

        $this->feedDeleteTableWithFk();
        $this->compareComStatus();

        //delete items
        $temp = $this->rows_to_delete;
        //purge table with empty row

        foreach ($temp as $key => $val) {
            if (empty($val)) {
                Debug::debug($key, "purge table with empty row");
                unset($this->rows_to_delete[$key]);
            }
        }


        Debug::debug($this->rows_to_delete);

        if (!empty($this->rows_to_delete[$this->main_table])) {


            Debug::debug($this->rows_to_delete[$this->main_table], "IF NOT EMPTY MAIN TABLE : ".$this->main_table);
            $this->delete_rows();
        }

        $this->end_loop();

        Debug::debug($this->rows_to_delete, "DELETE");

        return $this->rows_to_delete;
    }

    public function createTemporaryTable($table)
    {
        $db = Sgbd::sql($this->link_to_purge,'purge');
        $db->sql_select_db($this->schema_to_purge);

        $fields = $db->getTypeOfPrimaryKey($table, $this->schema_to_purge);

        if (count($fields) === 0) {

            $this->logger->emergency('[id:'.$this->id_cleaner.'][INIT][pid:'.getmypid().'] No primary key found on table "'.$table.'"');

            throw new \Exception('GLI-071 : No primary key found on table "'.$table.'"');

            $this->table_in_error[] = $table;
            return false;
        }

        $line  = array();
        $index = array();

        foreach ($fields as $field) {
            $line[]  = "`".$field['name']."` ".$field['type'];
            $index[] = "`".$field['name']."`";
        }

        $sql = "CREATE TABLE `".$this->schema_delete."`.`".$this->prefix.$table."`(";
        $sql .= implode(",", $line);

        if (in_array($table, $this->fk_circulaire)) {
            $sql .= ", `".self::FIELD_LOOP."` int(11) DEFAULT 0";
            $sql .= ", KEY `idx_".uniqid()."` (`".self::FIELD_LOOP."`)";
        }
        $sql .= ", PRIMARY KEY (".implode(",", $index)."));";

        $db->sql_query($sql);
    }

    public function feedDeleteTableWithFk()
    {
        Debug::checkPoint("FEED FROM FK");

        $db = Sgbd::sql($this->link_to_purge,'purge');
        $db->sql_select_db($this->schema_to_purge);

        $list_tables = $this->getOrderBy2(array($this->getForeignKeys(), $this->main_table));

        Debug::debug($list_tables);

        $tables_impacted = $this->getAffectedTables();

        Debug::debug($tables_impacted, '$tables_impacted');

        foreach ($list_tables as $sub_array) {

            foreach ($sub_array as $table_name) {


                // pourquoi j'ai ecris ça ?
                if ($table_name === $this->main_table) {
                    continue;
                }


                //get virtual FK and merge to real FK  TODO a déporté dans getVirtualForeignKey
                Debug::debug($this->table_to_purge);
                //exit;
                $sql = "SELECT * FROM `information_schema`.`KEY_COLUMN_USAGE` "
                    ."WHERE `CONSTRAINT_SCHEMA` ='".$this->schema_to_purge."' "
                    ."AND `REFERENCED_TABLE_SCHEMA`='".$this->schema_to_purge."'
                     AND REFERENCED_TABLE_NAME IN ('".implode("','", $tables_impacted)."') "
                    ."AND `TABLE_NAME` ='".$table_name."';";

                Debug::sql($sql);

                $res = $db->sql_query($sql);
                $fks = $db->sql_to_array($res);

                $fk_circular = array();

//get virtual FK and merge to real FK  TODO a déporté dans getVirtualForeignKey
                if (!empty($this->foreign_keys[$this->schema_to_purge][$table_name])) {
                    foreach ($this->foreign_keys[$this->schema_to_purge][$table_name] as $constraint_column => $line) {
                        $tab = explode("-", $line);

                        $tmp                            = [];
                        $tmp['REFERENCED_TABLE_SCHEMA'] = $tab[0];
                        $tmp['REFERENCED_TABLE_NAME']   = $tab[1];
                        $tmp['REFERENCED_COLUMN_NAME']  = $tab[2];
                        $tmp['COLUMN_NAME']             = $constraint_column;

//cas des deifinition circulaire à gérer en dernier (afin de remplir toute la table avant de boucler sur elle même)
                        if ($table_name !== $tmp['REFERENCED_TABLE_NAME']) {
                            $fks[] = $tmp;
                        } else {
                            $fk_circular[] = $tmp;
                        }
                    }
                }

                if (count($fk_circular) == 2) {
                    throw new \Exception("PMACTRL-549 : We do not support 2 circulars definitions in same time on same table");
                }


                $fks = array_merge($fks, $fk_circular);

                foreach ($fks as $fk) {

//don't take in consideration the table not impacted by cleaner

                    /*
                      if (!in_array($fk['REFERENCED_TABLE_NAME'], $tables_impacted)) {
                      continue;
                      } */

                    $pri          = $this->getPrimaryKey($table_name, $this->schema_to_purge);
                    $primary_key  = "a.`".implode('`,a.`', $pri)."`";
                    $primary_keys = "`".implode('`,`', $pri)."`";

                    if ($fk['REFERENCED_TABLE_NAME'] == $table_name) {
                        $circular = true;

                        Debug::debug("detected Circular FK on table '".$table_name."'");
                    } else {
                        $circular = false;
                    }

                    $loop = 1;
                    do {

                        $sql = "SELECT ".$primary_key." FROM `".$this->schema_to_purge."`.`".$table_name."` a
                        INNER JOIN `".$this->schema_delete."`.`".$this->prefix.$fk['REFERENCED_TABLE_NAME']."` b ON b.`".$fk['REFERENCED_COLUMN_NAME']."` = a.`".$fk['COLUMN_NAME']."`";

                        if ($circular) {
                            $sql .= " WHERE b.`".self::FIELD_LOOP."` = ".($loop - 1).";";

                            $circular_field = ",`".self::FIELD_LOOP."`";
                            $circular_data  = ",".$loop;
                        } else {
                            $circular_field = "";
                            $circular_data  = "";
                            $sql            .= ";";
                        }

                        Debug::sql($sql);

                        $data = $db->sql_fetch_yield($sql);

                        $have_data = false;
                        $sql       = "INSERT IGNORE INTO `".$this->schema_delete."`.`".$this->prefix.$table_name."` (".$primary_keys."".$circular_field.") VALUES ";

                        $count = 0;

                        $step_sql = "";

                        $value_sql = $sql;
                        foreach ($data as $line) {

                            $have_data = true;
                            $value_sql .= "('".implode("','", $line)."'".$circular_data."),";
                            $count++;

                            if ($count % 10000 === 0) {

                                $step_sql = rtrim($value_sql, ",").";";

                                //Debug::sql($step_sql);

                                $db->sql_query($step_sql);
                                $this->setAffectedRows($table_name);

                                Debug::debug($count, "Nombre de lignes");

                                $value_sql = $sql;
                            }
                        }


                        if ($circular) {
                            Debug::debug(Color::getColoredString("COUNT(1) = ".$count." - LOOP = ".$loop, "yellow"));
                        }

                        if ($have_data) {
                            $step_sql = rtrim($value_sql, ",").";";

                            //insertion dans la table de purge
                            //Debug::sql($step_sql);
                            $db->sql_query($step_sql);
                            $this->setAffectedRows($table_name);
                            Debug::debug($count, "Nombre de lignes");

                            $this->query = array();
                        }

                        $loop++;
                    } while ($circular && $count !== 0);

                    // archivation en fichier plat
                    //export to file
                    if ($have_data) {
                        $this->exportToFile($table_name);
                    }
                    // fin export
                }
            }
        }

        Debug::checkPoint("Feed delete tables from FKs");
    }

    public function createAllTemporaryTable()
    {
        $db     = Sgbd::sql($this->link_to_purge,'purge');
        $tables = $this->getImpactedTable();
        $db->getTypeOfPrimaryKey($tables, $this->schema_to_purge);

        foreach ($tables as $table) {
            if (substr($table, 0, 7) !== $this->prefix) {
                $this->createTemporaryTable($table);
            }
        }

        Debug::debug("Create all temporary tables.");
    }

    public function getTableError()
    {
        return $this->table_in_error;
    }

    private function getRealForeignKeys()
    {
//get list of FK and put in array
        $db = Sgbd::sql($this->link_to_purge,'purge');

//$db->sql_select_db($this->schema_to_purge);

        $sql = "SELECT * FROM `information_schema`.`KEY_COLUMN_USAGE` "
            ."WHERE `CONSTRAINT_SCHEMA` ='".$this->schema_to_purge."' "
            ."AND `REFERENCED_TABLE_SCHEMA`='".$this->schema_to_purge."' "
            ."AND `REFERENCED_TABLE_NAME` IS NOT NULL ";

        if (!empty($this->prefix)) {
            $sql .= "AND TABLE_NAME not like '".$this->prefix."%';";
        }

        Debug::sql($sql);

        $res           = $db->sql_query($sql);
        $order_to_feed = array();

        while ($ob = $db->sql_fetch_object($res)) {
            $order_to_feed[$ob->REFERENCED_TABLE_NAME][] = $ob->TABLE_NAME;
        }

        //Debug::debug($order_to_feed, "REAL FOREIGN KEY");


        return $order_to_feed;
    }

    private function getVirtualForeignKeys()
    {

        Debug::debug("get the virtual foreign keys");

        $default = Sgbd::sql(DB_DEFAULT);

//get and set virtual Foreign keys.
        $params = Sgbd::sql(DB_DEFAULT)->getParams();
        $sql    = "SELECT * FROM `".$params['database']."`.`cleaner_foreign_key` WHERE `id_cleaner_main` = ".$this->id_cleaner.";";

        $foreign_keys = $default->sql_fetch_yield($sql);

        $fk = array();
        foreach ($foreign_keys as $line) {

            if (empty($line['constraint_schema']) || empty($line['constraint_table']) || empty($line['constraint_column']) || empty($line['referenced_schema']) || empty($line['referenced_table'])
                || empty($line['referenced_column'])) {
                throw new \Exception("PMACTRL-334 : Value empty in virtual FK");
            }

            $fk[$line['constraint_schema']][$line['constraint_table']][$line['constraint_column']] = $line['referenced_schema']."-".$line['referenced_table']."-".$line['referenced_column'];
        }


        if (count($fk) != 0) {
            $this->foreign_keys = $fk;
        }

        $order_to_feed = array();

        foreach ($this->foreign_keys as $db => $tab_table) {
            foreach ($tab_table as $constraint_table => $lines) {

                foreach ($lines as $line) {
                    $tab_referenced                      = explode('-', $line);
                    $order_to_feed[$tab_referenced[1]][] = $constraint_table;
                }
            }
        }


        Debug::debug($order_to_feed, "VIRTUAL FOREIGN KEY");

        return $order_to_feed;
    }
    /*
     * return the list of table in order where
     *
     *
     */

    public function getOrderBy($param)
    {


        $foreign_keys = $param[0];
        $main_table   = $param[1];
        $order        = $param[2] ?? 'ASC';

        $this->setCacheFile();

        Debug::debug($foreign_keys, 'json');

        if (!file_exists($this->path_to_orderby_tmp)) {

            $tmp = $foreign_keys;

            //On retire les FKs en double
            foreach ($tmp as $key => $tab) {
                $foreign_keys[$key] = array_unique($foreign_keys[$key]);
            }


            //remove all tables with no father from $this->main_table
            //$foreign_keys = $this->removeTableNotImpacted($foreign_keys);
            //Debug::debug($fks);

            $level   = array();
            $level[] = $this->table_to_purge;

            $all_childs = $this->addChild($foreign_keys, $main_table, array($main_table));

            Debug::debug($all_childs);

            $foreign_keys = $this->filterFkWithChildren($all_childs, $foreign_keys);

            $array = $foreign_keys;

            // test des tables qui boucle sur elle même
            $tmp2 = $array;
            foreach ($tmp2 as $table_name => $childs) {

                foreach ($childs as $key => $child) {
                    if ($table_name === $child) {

                        $this->fk_circulaire[] = $table_name;
                        unset($array[$table_name][$key]);
                        $cas_found             = true;
                    }
                }
            }

            //debug($array);

            $i    = 0;
            while ($last = count($array) != 0) {

                //echo "level " . $i . PHP_EOL;
                $temp = $array;

                foreach ($temp as $father_name => $tab_father) {
                    foreach ($tab_father as $key_child => $table_child) {
                        if (!in_array($table_child, array_keys($array))) {

                            if (empty($level[$i]) || !in_array($table_child, $level[$i])) {
                                $level[$i][] = $table_child;
                            }
                            //debug($level);
                            unset($array[$father_name][$key_child]);
                            //debug($array);
                        }
                    }
                }

                $temp = $array;

// retirer les tableaux vides, et remplissage avec clefs


                Debug::debug($temp, 'temp');

                foreach ($temp as $key => $tmp) {
                    if (count($tmp) == 0) {
                        unset($array[$key]);
                        if (empty($level[$i + 1]) || !in_array($key, $level[$i + 1])) {
                            $level[$i + 1][] = $key;
                        }
                    }
                }

                if ($last == count($array)) {
                    $cas_found = false;

                    //cas de deux chemins differents pour arriver à la même table enfant
                    $temp = $array;
                    foreach ($temp as $key1 => $tab2) {
                        foreach ($tab2 as $key2 => $val) {
                            foreach ($level as $tab3) {
                                if (in_array($val, $tab3)) {
                                    unset($array[$key1][$key2]);
                                    $cas_found = true;
                                }
                            }

                            //debug($val);
                        }
                    }

                    if (!$cas_found) {
                        echo "\n";

                        Debug::debug($temp);
                        Debug::debug($tab2);
                        Debug::debug($level);
                        Debug::debug($array);

                        $tables = array();
                        foreach ($array as $key => $tab) {
                            $tables[] = $key;

                            foreach ($tab as $elem) {
                                $tables[] = $elem;
                            }
                        }

                        $tables = array_unique($tables);

                        sort($tables);

                        Debug::debug($level, "LEVEL");
                        Debug::debug($tables);
                        //echo implode("','", $tables);
                        //Debug::debug($this->fk_circulaire);
                        throw new \Exception("\nPMACTRL-333 Circular definition (table <-> table)");
                    }
                }

                sort($level[$i]);
                $i++;
            }

//dans le cas où il a pas au moins table fille on ajoute la table principale
            if (count($level[0]) == 0) {
                $level[0][0] = $this->main_table;
            }

            if ($order === "ASC") {
                krsort($level);
            } else {
                ksort($level);
            }

            $this->orderby = $level;

            Debug::debug($level, "LEVEL");
            exit;

            file_put_contents($this->path_to_orderby_tmp, serialize($this));

            Debug::checkPoint("générer l'ordre de remplissasage et d'effacement");
        } else {

//on load le fichier précédement enregistré
            if (is_file($this->path_to_orderby_tmp)) {
                $s             = implode('', file($this->path_to_orderby_tmp));
                $tmp           = unserialize($s);
                $this->orderby = $tmp->orderby;
            }
        }


        if ($order === "ASC") {
            krsort($this->orderby);
        } else {
            ksort($this->orderby);
        }

        return $this->orderby;
    }

    private function delete_rows()
    {
        
        $db  = Sgbd::sql($this->link_to_purge,'purge');
        $db->sql_select_db($this->schema_to_purge);

        $list_tables = $this->getOrderBy2(array($this->getForeignKeys(), $this->main_table, "DESC"));

        foreach ($list_tables as $levels) {
            foreach ($levels as $table) {

                $primary_keys = $this->getPrimaryKey($table, $this->schema_to_purge);

                $join   = array();
                $fields = array();
                foreach ($primary_keys as $primary_key) {
                    $join[]   = " `a`.`".$primary_key."` = b.`".$primary_key."` ";
                    $fields[] = " `b`.`".$primary_key."` ";
                }

                $field = implode(" ", $join);

                if ($table === "local") {
                    $db->sql_query("SET FOREIGN_KEY_CHECKS=0;");
                    Debug::debug("SET FOREIGN_KEY_CHECKS=0;");
                }

                $circular = false;

                $loop = 0;
                if (in_array($table, $this->fk_circulaire)) {
                    $circular = true;

                    // moyen d'enregistrer le nombre en cash au lieu de refaire une requette
                    $sql = "SELECT MAX(`".self::FIELD_LOOP."`) as max FROM `".$this->schema_delete."`.".$this->prefix.$table."";
                    $res = $db->sql_query($sql);

                    while ($ob = $db->sql_fetch_object($res)) {
                        $max = $ob->max;
                    }

                    $loop = $max;
                }

                $affected_rows = 0;
                do {
                    //seulement si il y a des lignes pour gagner du temps
                    $sql = "DELETE a FROM ".$table." a
                  INNER JOIN `".$this->schema_delete."`.".$this->prefix.$table." as b ON  ".implode(" AND ", $join); // LIMIT 50000

                    if ($circular) {
                        $sql .= " WHERE `".self::FIELD_LOOP."`=".$loop;
                    }
                    $sql .= ";";

                    Debug::sql($sql);
                    $db->sql_query($sql);

                    $affected_rows += end($db->query)['rows'];

                    $loop--;
                } while ($circular && $loop >= 0);

                if ($table === "local") {
                    $db->sql_query("SET FOREIGN_KEY_CHECKS=1;");
                    Debug::debug("SET FOREIGN_KEY_CHECKS=1;");
                }

                Debug::debug($affected_rows, $table);

                if ($affected_rows == "-1") {


                    $this->logger->error('[id:'.$this->id_cleaner.'][FOREIGN KEY][pid:'.getmypid().'] have to update lib of cleaner or order of table set in param'.$sql);
                    throw new \Exception('PMACLI-666 : Foreign key error, have to update lib of cleaner or order of table set in param');
                }
                //} while ($affected_rows > 0);

                $sql = "TRUNCATE TABLE `".$this->schema_delete."`.`".$this->prefix.$table."`;";
                $db->sql_query($sql);
            }
        }

        Debug::checkPoint("On efface les données et on purge les tables de travail");
    }

    private function setAffectedRows($table)
    {
        $db = Sgbd::sql($this->link_to_purge,'purge');
        $db->sql_select_db($this->schema_to_purge);

        if (empty($this->rows_to_delete[$table])) {
            $this->rows_to_delete[$table] = end($db->query)['rows'];
        } else {
            $this->rows_to_delete[$table] += end($db->query)['rows'];
        }
    }
    /*
     * liste des tables impacté par la purge (en rapport avec les FK réel et virtuel)
     *
     */

    private function getAffectedTables()
    {
        if (count($this->table_impacted) === 0) {
            $list_tables = $this->getOrderBy2(array($this->getForeignKeys(), $this->main_table));

            foreach ($list_tables as $tables) {
                $this->table_impacted = array_merge($this->table_impacted, $tables);
            }
        }

        return $this->table_impacted;
    }

    private function removeTableNotImpacted($fks)
    {
        do {
            $tmp     = $fks;
            $tmp2    = $fks;
            $nbfound = 0;
            foreach ($fks as $table => $data) {


                //we want keep main table
                if (trim($table) == $this->main_table) {
                    continue;
                }
                $found = false;
                foreach ($tmp2 as $data_to_cmp) {
                    if (in_array($table, $data_to_cmp)) {
                        $found = true;
                    }
                }
                if (!$found) {

                    Debug::debug(Color::getColoredString("We removed this table (Not a child of : `".$this->schema_to_purge."`.`".$this->main_table."`) : ".$table,
                            'yellow'));
                    unset($tmp2[$table]);
                    $nbfound++;
                }
            }
            $fks = $tmp2;
        } while ($nbfound != 0);
        return $tmp2;
    }

    private function exportToFile($table)
    {
        if (!empty($this->id_backup_storage_area)) {

            $db = Sgbd::sql($this->link_to_purge, 'purge');

            $primary_keys = $this->getPrimaryKey($table, $this->schema_to_purge);

            $max      = 0;
            $circular = false;
            if (in_array($table, $this->fk_circulaire)) {
                $circular = true;
                $loop     = 0;

                // moyen d'enregistrer le nombre en cash au lieu de refaire une requette
                $sql = "SELECT MAX(`".self::FIELD_LOOP."`) as max FROM `".$this->schema_delete."`.".$this->prefix.$table."";
                $res = $db->sql_query($sql);

                while ($ob = $db->sql_fetch_object($res)) {
                    $max = $ob->max;
                }
            }

            $loop = $max;

            do {

                $join = array();
                foreach ($primary_keys as $primary_key) {
                    $join[] = " `a`.`".$primary_key."` = b.`".$primary_key."` ";
                }

                $sql = "SELECT a.* FROM ".$table." a
                    INNER JOIN `".$this->schema_delete."`.".$this->prefix.$table." as b ON  ".implode(" AND ", $join)."";

                if ($circular) {
                    $sql .= " WHERE `".self::FIELD_LOOP."`=".$loop.";";
                }

                $res = $db->sql_query($sql);

                $tab_fields = $db->getFieldsMeta($res);

                foreach ($tab_fields as $field) {
                    $fields[] = "`".$field->name."`";
                }

                $fields_list = implode(",", $fields);

                //TODO : generer par batch de 50 000
                $query = "INSERT IGNORE INTO ".$table." (".$fields_list.") VALUES ".$this->get_rows($res).";\n";

                $this->testDirectory($this->backup_dir);

                $file = $this->backup_dir."/".date('Y-m-d')."_log.sql";

                $this->initFileWithCreateTable($file);

                $fp = fopen($file, "a");

                if ($fp) {
                    fwrite($fp, $query);
                    fclose($fp);
                }

                $loop--;
            } while ($circular && $loop >= 0);
        }
    }
    /*
     * this have to move in glial (static)
     */

    private function testDirectory($path)
    {

        if (!is_dir($path)) {
            mkdir($path, 0700, true);
        }

        if (!is_writable($path)) {
            throw new \Exception("GLI-985 : Impossible to write in directory : (".$path.")", 80);
        }
    }
    /*
     * return an array with data to serialize
     * @since Glial 4.2.11
     * @version 4.2.11
     * @return array contain the data to be serialized
     * @author Aurélien LEQUOY <aurelien.lequoy@esysteme.com>
     * @description return an array to be serialized in a flat file
     * @access public
     */

    public function __sleep()
    {
        return array('orderby');
    }
    /*
     * get type of mysql field, the goal is to know where we have to put quotes and save a lot of space
     * move to glial / mysql
     */

    private function get_rows($result)
    {
        $db = Sgbd::sql($this->link_to_purge,'purge');
        $current_row = 0;

        $fields_cnt = $db->sql_num_fields($result);

        // Get field information
        $fields_meta = $db->getFieldsMeta($result);

        $field_flags = array();
        for ($j = 0; $j < $fields_cnt; $j++) {
            $field_flags[$j] = $db->fieldFlags($result, $j);
        }

        $i = 0;

        $insert_elem = array();
        while ($row = $db->sql_fetch_array($result, MYSQLI_NUM)) {
            $values = array();
            $i++;

            for ($j = 0; $j < $fields_cnt; $j++) {
                // NULL
                if (!isset($row[$j]) || is_null($row[$j])) {
                    $values[] = 'NULL';
                } elseif ($fields_meta[$j]->numeric && $fields_meta[$j]->type != 'timestamp' && !$fields_meta[$j]->blob) {
                    // a number
                    // timestamp is numeric on some MySQL 4.1, BLOBs are
                    // sometimes numeric
                    $values[] = $row[$j];
                } elseif (stristr($field_flags[$j], 'BINARY') !== false && $this->sql_hex_for_binary) {
                    // a true BLOB
                    // - mysqldump only generates hex data when the --hex-blob
                    //   option is used, for fields having the binary attribute
                    //   no hex is generated
                    // - a TEXT field returns type blob but a real blob
                    //   returns also the 'binary' flag
                    // empty blobs need to be different, but '0' is also empty
                    // :-(
                    if (empty($row[$j]) && $row[$j] != '0') {
                        $values[] = '\'\'';
                    } else {
                        $values[] = '0x'.bin2hex($row[$j]);
                    }
                } elseif ($fields_meta[$j]->type == 'bit') {
                    // detection of 'bit' works only on mysqli extension
                    $values[] = "b'".$db->sql_real_escape_string(
                            $this->printableBitValue(
                                $row[$j], $fields_meta[$j]->length
                            )
                        )
                        ."'";
                } else {
                    // something else -> treat as a string
                    $values[] = '\''.$db->sql_real_escape_string($row[$j]).'\'';
                } // end if
            } // end for

            $insert_elem[] = '('.implode(',', $values).')';
        }

        $insert_line = implode(',', $insert_elem);
        return $insert_line;
    }
    /*
     *
     * this function should move
     *
     */

    private static function printableBitValue($value, $length)
    {
// if running on a 64-bit server or the length is safe for decbin()
        if (PHP_INT_SIZE == 8 || $length < 33) {
            $printable = decbin($value);
        } else {
// FIXME: does not work for the leftmost bit of a 64-bit value
            $i         = 0;
            $printable = '';
            while ($value >= pow(2, $i)) {
                ++$i;
            }
            if ($i != 0) {
                --$i;
            }

            while ($i >= 0) {
                if ($value - pow(2, $i) < 0) {
                    $printable = '0'.$printable;
                } else {
                    $printable = '1'.$printable;
                    $value     = $value - pow(2, $i);
                }
                --$i;
            }
            $printable = strrev($printable);
        }
        $printable = str_pad($printable, $length, '0', STR_PAD_LEFT);
        return $printable;
    }

    private function getImpactedTable()
    {
        $list = $this->getOrderBy2(array($this->getForeignKeys(), $this->main_table));

        //Debug::debug($list);


        $tables = array();
        foreach ($list as $elem) {
            $tables = array_merge($elem, $tables);
        }
        return $tables;
    }
    /*
     * log file Daemon
     */

    public function before($param)
    {
        $logger       = new Logger('cleaner');
        $file_log     = LOG_FILE;
        $handler      = new StreamHandler($file_log, Logger::DEBUG);
        $handler2      = new StreamHandler(TMP.'log/cleaner.log', Logger::DEBUG);

        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $handler2->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $logger->pushHandler($handler2);
        
        $this->logger = $logger;

        Debug::parseDebug($param);

        /*
          $id_mysql_server = 104;


          $dblink   = Sgbd::sql(DB_DEFAULT);
          $name     = Mysql::getDbLink($dblink, $id_mysql_server);
          $this->db = Sgbd::sql($name);

          $this->schema_to_purge = 'mydb';
         *
         */
    }

// move to archive controller ? avec toute les fonctions qui sont appeller dedant
    public function pushArchive($param = array())
    {
        $this->view = false;
        $id_cleaner = $this->get_id_cleaner($param);

        Debug::parseDebug($param);

        $default                      = Sgbd::sql(DB_DEFAULT);
        $storage_area                 = $this->getIdStorageArea($id_cleaner);
        $this->id_backup_storage_area = $storage_area->id;

        if (!empty($this->id_backup_storage_area)) {

            $this->backup_dir = DATA."cleaner/".$this->id_cleaner;

            Debug::debug($this->backup_dir, "Backup directory");

            $files = $this->checkFileToPush($this->backup_dir);

            Debug::debug($files, "Files to push");

            foreach ($files as $file) {


                $stats = $this->compressAndCrypt($file, $storage_area->is_crypted);

                $path = "pmacontrol/cleaner/".$id_cleaner."/";
                $dst  = $path.pathinfo($stats['file_path'])['basename'];

                // to prevent MySQL timeout ?
                $default->sql_close();

                $scp = $this->sendFile($this->id_backup_storage_area, $stats['file_path'], $dst);

                // to prevent MySQL timeout ?
                $default->sql_close();
                $default = Sgbd::sql(DB_DEFAULT);

                $archive                                      = array();
                $archive['archive']['id_cleaner_main']        = (int) $id_cleaner;
                $archive['archive']['id_backup_storage_area'] = (int) $this->id_backup_storage_area;
                $archive['archive']['md5_sql']                = $stats['normal']['md5'];
                $archive['archive']['size_sql']               = $stats['normal']['size'];
                $archive['archive']['md5_compressed']         = $stats['compressed']['md5'];
                $archive['archive']['size_compressed']        = $stats['compressed']['size'];

                if ($storage_area->is_crypted === "1") {
                    $archive['archive']['md5_crypted']   = $stats['crypted']['md5'];
                    $archive['archive']['size_crypted']  = $stats['crypted']['size'];
                    $archive['archive']['time_to_crypt'] = $stats['crypted']['execution_time'];
                    $archive['archive']['is_crypted']    = 1;
                } else {
                    $archive['archive']['md5_crypted']   = '0';
                    $archive['archive']['size_crypted']  = 0;
                    $archive['archive']['time_to_crypt'] = 0;
                    $archive['archive']['is_crypted']    = 0;
                }

                $archive['archive']['md5_remote']       = $scp['md5'];
                $archive['archive']['size_remote']      = $scp['size'];
                $archive['archive']['time_to_compress'] = $stats['compressed']['execution_time'];

                $archive['archive']['time_to_transfert'] = $scp['execution_time'];
                $archive['archive']['date']              = date('Y-m-d H:i:s');
                $archive['archive']['pathfile']          = $scp['pathfile'];

                $err = $default->sql_save($archive);

                if (!$err) {
                    Debug::debug($archive);
                    Debug::debug($default->sql_error());

                    exit;
                }

                if ($archive['archive']['md5_remote'] === $archive['archive']['md5_crypted'] && (int) $archive['archive']['size_crypted'] === (int) $archive['archive']['size_remote']) {

                    $this->logger->info('[id:'.$id_cleaner.'][ARCHIVE][pid:'.getmypid().'] push file to (StorageArea:'.$this->id_backup_storage_area.') '.$storage_area->ip.":".$storage_area->path.'/'.$dst);
                }
            }
        }
    }

    private function getIdStorageArea($id_cleaner)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT b.*, a.is_crypted FROM cleaner_main a
            INNER JOIN backup_storage_area b ON a.id_backup_storage_area = b.id
            where a.id ='".$id_cleaner."'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            return $ob;
        }
    }

    private function init()
    {

        $this->logger->info('[id:'.$this->id_cleaner.'][INIT][pid:'.getmypid().'] Init of cleaner');
        Debug::debug("REAL INIT !");

        $db = Sgbd::sql($this->link_to_purge,'purge');
        $db->sql_select_db($this->schema_to_purge);

        $this->skipReplication($db);

        $sql = "CREATE DATABASE IF NOT EXISTS `".$this->schema_delete."`;";
        $db->sql_query($sql);

        Debug::checkPoint("Create database `".$this->schema_delete."`");

        $this->purge_clean_db();

        $this->setCacheFile();

        $this->testDirectory(pathinfo($this->path_to_orderby_tmp)['dirname']);

        if (file_exists($this->path_to_orderby_tmp)) {
            unlink($this->path_to_orderby_tmp);
        }

        $this->createAllTemporaryTable();
        $this->generateCreateTable();

        $this->cacheComStatus();

        sleep(1); //temporisation nécessaire entre le temps d'un DROP TABLE IF EXISTS et un CREATE TABLE IF NOT EXISTS
        $this->initDdlOnDisk();
    }

    private function generateCreateTable()
    {

        $db = Sgbd::sql($this->link_to_purge,'purge');

        $tables = $this->getImpactedTable();

        foreach ($tables as $table) {
            $sql = "SHOW CREATE TABLE `".$this->schema_to_purge."`.`".$table."`";
            $res = $db->sql_query($sql);

            // we test object type in case of lock or drop table
            if (is_object($res) && get_class($res) == 'mysqli_result') {
                while ($arr = $db->sql_fetch_array($res, MYSQLI_NUM)) {
                    $this->cache_table[$this->schema_to_purge][$table] = str_replace("CREATE TABLE ", "CREATE TABLE IF NOT EXISTS ", $arr[1]);
                }
            }
        }

        Debug::checkPoint("generate create table from actual DB");

        return $this->cache_table[$this->schema_to_purge];
    }
    /*
     * On garde les derniers create table en cache pour les comparer aux nouvelles pour générer les ALTER TABLE en cas de divergence
     */

    public function cacheDdlOnDisk()
    {
        $path_dir = $this->backup_dir."/DDL";

        if (!is_dir($path_dir)) {
            shell_exec("mkdir -p ".$path_dir);
        }

        //c'est pas sexy
        $tables = $this->generateCreateTable();

        foreach ($tables as $table => $create) {
            file_put_contents($path_dir."/".$table.".sql", $create);
        }

        //debug($this->cacheComStatus());

        file_put_contents($path_dir."/global_status.json", $this->cacheComStatus());
    }
    /*
     * On ajoute les create table au début de chaque nouveau fichier (dans le cas où elles n'existeraient pas)
     */

    private function initFileWithCreateTable($file)
    {
        if (!file_exists($file)) {

            if (!$handle = fopen($file, "a")) {
                echo "Impossible d'ouvrir le fichier ($file)";
                exit;
            }

            $data        = "";
            $list_tables = $this->getOrderBy2(array($this->getForeignKeys(), $this->main_table));

            foreach ($list_tables as $sub_array) {

                foreach ($sub_array as $table) {
                    $data .= $this->cleanTableFromNoNeedConstraint($this->cache_table[$this->schema_to_purge][$table]).";\n";
                }
            }

            if (fwrite($handle, $data) === FALSE) {
                echo "Impossible d'écrire dans le fichier ($file)";
                exit;
            }
            fclose($handle);

            $user = trim(shell_exec("ps -ef | egrep '(httpd|apache2|apache)' | grep -v `whoami` | grep -v root | head -n1 | awk '{print $1}'"));

            shell_exec("chown ".$user.". ".$file);
            //chown($file, $user);

            $this->log("INFO", "TABLES", "Création des tables en cas de leur non présence");

            Debug::checkPoint("Création des tables en cas de leur non présence");
        }
    }
    /*
     * On retire les contraintes sur les tables qui ne sont pas concerné par la purge
     */

    private function cleanTableFromNoNeedConstraint($tableCreate)
    {
        $tables = $this->getImpactedTable();

        $lines = explode("\n", $tableCreate);

        $new_table = "";

        foreach ($lines as $line) {
            $keyword = "CONSTRAINT";

            $keep = true;
            if (substr(trim($line), 0, mb_strlen($keyword)) == $keyword) {
                preg_match("/REFERENCES\s`([\w]+)`/", $line, $output_array);

                if (!in_array($output_array[1], $tables)) {
                    $keep = false;
                }
            }

            if ($keep) {
                if (substr(trim($line), 0, 1) === ")") {
                    $trimed = trim($new_table);
                    if (substr($trimed, -1) === ",") {
                        $trimed = substr($trimed, 0, -1);
                    }
                    $new_table = $trimed."\n";
                }
                $new_table .= $line."\n";
            }
        }

        return trim($new_table);
    }

    private function cacheComStatus()
    {

        Debug::debug("Refresh GLOBAL STATUS");

        $db = Sgbd::sql($this->link_to_purge,'purge');

        $this->com_status['Com_create_table'] = $db->getStatus("Com_create_table", true);
        $this->com_status['Com_alter_table']  = $db->getStatus("Com_alter_table");
        $this->com_status['Com_rename_table'] = $db->getStatus("Com_rename_table");
        $this->com_status['Com_drop_table']   = $db->getStatus("Com_drop_table");

        $this->com_status['Uptime'] = $db->getStatus("Uptime");

        return json_encode($this->com_status);
    }

    private function compareComStatus()
    {
        $db = Sgbd::sql($this->link_to_purge,'purge');

        $Coms = array("Com_create_table", "Com_alter_table", "Com_rename_table", "Com_drop_table");

        $restart = false;
        $db->getStatus("Com_create_table", true); //to force refresh GLOBAL STATUS

        foreach ($Coms as $com) {
            if ($this->com_status[$com] != $db->getStatus($com)) {

                $tmp[$com]['before'] = $this->com_status[$com];
                $tmp[$com]['after']  = $db->getStatus($com);
                $restart             = true;

                break;
            }
        }

        if ($restart) {

            $this->cacheComStatus();
            $this->logger->info('[id:'.$this->id_cleaner.'][DDL_UPDATE][pid:'.getmypid().'] DDL updated', $tmp);

            echo "Call SIGHUP ".getmypid()."\n";

            $this->sighup();

            /*
              $ret = posix_kill(getmypid(), SIGHUP);
              echo "posix_kill : ".(bool)$ret."\n";
             */
            exit;

            //pcntl_signal_dispatch();
        }
    }

    public function sighup()
    {
        $pid = getmypid();

        if (empty($this->di['auth'])) {
            $this->logger->info('[id:'.$this->id_cleaner.'][SIGHUP][pid:'.$pid.'] "'.$this->libelle.'" '."by".' '
                ."[SYSTEM]");
        } else {
            $this->logger->info('[id:'.$this->id_cleaner.'][SIGHUP][pid:'.$pid.'] "'.$this->libelle.'" '."by".' '
                .$this->di['auth']->getUser()->firstname." ".$this->di['auth']->getUser()->name." (id:".$this->di['auth']->getUser()->id);
        }

        $log_file = TMP."log/cleaner_".$this->id_cleaner.".log";

        $php = explode(" ", shell_exec("whereis php"))[1];
        $cmd = $php." ".GLIAL_INDEX." Cleaner restart ".$this->id_cleaner." >> ".$log_file." & echo $!";
        shell_exec($cmd);
    }

    public function getPrimaryKey($table, $database)
    {
        $db = Sgbd::sql($this->link_to_purge, 'purge');

        if (empty($this->primary_key[$database][$table])) {

            $sql = "SHOW INDEX FROM `".$database."`.`".$table."` WHERE `Key_name` ='PRIMARY';";
            $res = $db->sql_query($sql);

            if ($db->sql_num_rows($res) == "0") {
                throw new \Exception("CLEANER-067 : this table '".$table."' haven't primary key !");
            } else {

                $index = array();

                while ($ob = $db->sql_fetch_object($res)) {
                    $this->primary_key[$database][$table][] = $ob->Column_name;
                }
            }
        }

        return $this->primary_key[$database][$table];
    }

    public function end_loop()
    {
        $db = Sgbd::sql($this->link_to_purge, 'purge');

        //debug::debug($db->query);
        //Debug::showQueries();
        $db->query = array();
        Debug::checkPoint("[DEBUG] Time to generate showQueries");
        $db->sql_close();
    }
    /*
     * Dans le cas d'un répertoire vide (utiliser une fois par cleaner à l'init
     */

    public function initDdlOnDisk()
    {
        $path_dir = $this->backup_dir."/DDL";
        $files    = glob($path_dir."/*.sql");

        if (count($files) == 0) {
            $this->cacheDdlOnDisk();
        }

        $this->compareComStatus();
    }

    private function compareDdl()
    {
        $path_dir = $this->backup_dir."/DDL";

        $json = file_get_contents($path_dir."/global_status.json");

        $data_ori = json_decode($json, true);
        $data_new = json_decode($this->cacheComStatus(), true);

        $sqls = array();
        foreach ($this->com_to_check as $com) {
            if ($data_ori[$com] != $data_new[$com]) {

                echo "Compare all DDL of tables possibly impacted by cleaner / archive\n";

                $this->generateCreateTable();
                $sqls = $this->compareTables();

                break;
            }
        }


        if (count($sqls) > 0) {

            $file = $this->backup_dir."/".date('Y-m-d')."_log.sql";
            $this->initFileWithCreateTable($file);
            $fp   = fopen($file, "a");
            if ($fp) {
                foreach ($sqls as $query) {
                    $this->logger->warning('[id:'.$this->id_cleaner.'][ALTER][pid:'.getmypid().'] '.$query);
                    fwrite($fp, $query."\n");
                }
                fclose($fp);
            }
        }

        $this->cacheDdlOnDisk();
    }

    private function compareTables()
    {

        $sql    = array();
        $tables = $this->getImpactedTable();
        foreach ($tables as $table) {
            $sql = array_merge($this->compareTable($table), $sql);
        }

        return $sql;
    }

    private function compareTable($table)
    {
        $path_dir = $this->backup_dir."/DDL";

        $file = $path_dir."/".$table.".sql";

        $file_to_cmp = '';
        if (file_exists($file)) {
            $file_to_cmp = file_get_contents($file);
        }

        $updater = new Compare;
        $sql     = $updater->getUpdates($file_to_cmp, $this->cache_table[$this->schema_to_purge][$table]);

        return $sql;
    }

    public function edit($param)
    {
        $id_cleaner = $this->get_id_cleaner($param);

        $this->title = '<span class="glyphicon glyphicon-edit"></span> '.__('Edit');

        if (Basic::from(__FILE__)) {
            return $this->title;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM cleaner_main WHERE id = ".$id_cleaner;

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $_GET['cleaner_main']['id']                     = $ob->id;
            $_GET['cleaner_main']['libelle']                = $ob->libelle;
            $_GET['cleaner_main']['id_mysql_server']        = $ob->id_mysql_server;
            $_GET['cleaner_main']['database']               = $ob->database;
            $_GET['cleaner_main']['main_table']             = $ob->main_table;
            $_GET['cleaner_main']['query']                  = $ob->query;
            $_GET['cleaner_main']['wait_time_in_sec']       = $ob->wait_time_in_sec;
            $_GET['cleaner_main']['cleaner_db']             = $ob->cleaner_db;
            $_GET['cleaner_main']['prefix']                 = $ob->prefix;
            $_GET['cleaner_main']['id_backup_storage_area'] = $ob->id_backup_storage_area;
            $_GET['cleaner_main']['limit']                  = $ob->limit;
            $_GET['cleaner_main']['is_crypted']             = $ob->is_crypted;

            $_GET['id_mysql_server'] = $ob->id_mysql_server;

            $data = $this->getDatabaseByServer(array($ob->id_mysql_server));
            $data = array_merge($data, $this->getTableByDatabase(array($ob->database)));
        }


        $data2 = $this->add(array($id_cleaner, $data));

        $data               = array_merge($data2, $data);
        $data['id_cleaner'] = $id_cleaner;
        $this->set('data', $data);
    }

    public function install()
    {

    }

    public function uninstall()
    {

    }

    public function view($param)
    {
        $id_cleaner = $this->get_id_cleaner($param);

        $this->title = '<span class="glyphicon glyphicon-eye-open"></span> '.__('View');

        if (Basic::from(__FILE__)) {

            return $this->title;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *,a.database, b.name as nameserver,b.ip, b.display_name,a.id as id_cleaner_main
            FROM cleaner_main a
                INNER JOIN mysql_server b ON a.id_mysql_server = b.id
                WHERE a.id = '".$id_cleaner."';";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $data['id']                     = $ob->id;
            $data['libelle']                = $ob->libelle;
            $data['id_mysql_server']        = $ob->id_mysql_server;
            $data['database']               = $ob->database;
            $data['main_table']             = $ob->main_table;
            $data['query']                  = $ob->query;
            $data['wait_time_in_sec']       = $ob->wait_time_in_sec;
            $data['cleaner_db']             = $ob->cleaner_db;
            $data['prefix']                 = $ob->prefix;
            $data['id_backup_storage_area'] = $ob->id_backup_storage_area;
            $data['limit']                  = $ob->limit;
            $data['display_name']           = $ob->display_name;
            $data['ip']                     = $ob->ip;

            $this->link_to_purge = $ob->nameserver;
        }

        $pri         = $this->getPrimaryKey($data['main_table'], $data['database']);
        $primary_key = "a.`".implode('`,a.`', $pri)."`";

        $sql         = "SELECT ".$primary_key." FROM `".$data['database']."`.`".$data['main_table']."` a ".$data['query']." LIMIT ".$data['limit'].";";
        $data['sql'] = \SqlFormatter::format($sql);

        $db2 = Sgbd::sql($this->link_to_purge,'purge');

        $db_origin = $db->db;
        $db2->sql_select_db($data['database']);

        $sql2 = "explain ".$sql;
        $res  = $db2->sql_query($sql2);

        while ($arr = $db2->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $tmp = array();
            foreach ($arr as $key => $val) {

                $tmp[$key] = $val;
            }
            $data['explain'][] = $tmp;
        }


        $sql = "explain SELECT COUNT(1) as cpt FROM `".$data['database']."`.`".$data['main_table']."` a ".$data['query'];

        $data['nb_line_to_purge'] = 0;
        $res = $db2->sql_query($sql);
        while ($ob  = $db2->sql_fetch_object($res)) {
            
            if ($ob->rows > $data['nb_line_to_purge']) {
                $data['nb_line_to_purge'] = $ob->rows;
            }
        }

        if ($data['nb_line_to_purge'] < 100000)
        {
            $sql = "explain SELECT COUNT(1) as cpt FROM `".$data['database']."`.`".$data['main_table']."` a ".$data['query'];
            $res = $db2->sql_query($sql);
            while ($ob  = $db2->sql_fetch_object($res)) {
                $data['nb_line_to_purge'] = $ob->rows;
            }
        }

        $sql = "SELECT TABLE_ROWS FROM `information_schema`.`tables` WHERE table_name = '".$data['main_table']."' and table_schema = '".$data['database']."'";
        $res = $db2->sql_query($sql);
        while ($ob  = $db2->sql_fetch_object($res)) {
            $data['nb_line_total'] = $ob->TABLE_ROWS;
        }

        if ($data['nb_line_to_purge'] > $data['nb_line_total']) {
            $data['nb_line_total'] = $data['nb_line_to_purge'];
        }

        $data['percent'] = 0;
        if ($data['nb_line_total'] > 0) {
            $data['percent'] = round($data['nb_line_to_purge'] / $data['nb_line_total'] * 100, 2);
        }
        $data['id_cleaner'] = $id_cleaner;

        $data['estimation'] = floor($data['nb_line_to_purge'] / $data['limit']) * (5 + $data['wait_time_in_sec']);

        $this->set('data', $data);
    }

    public function logs($param)
    {
        $id_cleaner = $this->get_id_cleaner($param);

        $this->title = '<span class="glyphicon glyphicon-file"></span> '.__('Logs');
        if (Basic::from(__FILE__)) {
            return $this->title;
        }

        $filter = "";
        if (!empty($param[1])) {
            $filter = "| grep -F '[".$param[1]."]' ";
        }

        $log_file = TMP.'log/cleaner.log';


        $cmd = "cat ".$log_file.' | tr -d \'\\000\' | grep -F \'cleaner.\' | grep -F \'[id:'.$id_cleaner."]' ".$filter."| tail -n 500";

        //debug($cmd);

        $lines              = shell_exec($cmd);
        $data['logs']       = $this->format($lines, $id_cleaner);
        $data['logs']       = array_reverse($data['logs']);
        $data['id_cleaner'] = $id_cleaner;

        $this->set('data', $data);
    }

    public function details($param)
    {
        $id_cleaner = $this->get_id_cleaner($param);

        $this->title = '<i class="fa fa-file-text-o" aria-hidden="true"></i> '.__('Details');
        if (Basic::from(__FILE__)) {

            return $this->title;
        }

        $data['id_cleaner'] = $id_cleaner;

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM cleaner_main where id ='".$id_cleaner."'";
        $res = $db->sql_query($sql);

        $ob = $db->sql_fetch_object($res);



        $data['log']      = shell_exec("tail -n2000 ".$ob->log_file);
        $data['log_file'] = $ob->log_file;

        $this->di['js']->code_javascript("var objDiv = document.getElementById('data_log');
objDiv.scrollTop = objDiv.scrollHeight;
");

        $this->set('data', $data);
    }

    public function menu($param)
    {
        $id_cleaner = $this->get_id_cleaner($param);

        $menu = array("view", "edit", "statistics", "logs", "details", "impacted");

        foreach ($menu as $elem) {

            $data['menu'][$elem]['title'] = $this->$elem($param);
            $data['menu'][$elem]['url']   = LINK.$this->getClass()."/".$elem."/".$id_cleaner;
        }


        $this->set('data', $data);
    }

    private function format($lines, $id_cleaner)
    {
        // cette fonction a besoin d'être optimisé !!

        $tab_line = explode("\n", trim($lines));
        $data     = array();



        foreach ($tab_line as $line) {

            if (empty(trim($line))) {
                continue;
            }

            $tmp = array();

            preg_match("/\[id:\d+\]\[(\w+)\]/", $line, $output_array);

            if (!empty($output_array[1])) {

                $tmp['type'] = $output_array[1];
            } else {
                $tmp['type'] = "UNKNOW !!!";
            }


            $input_line = str_replace('[id:'.$id_cleaner.']', '', $line);
            preg_match("/^\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\] cleaner\.([a-zA-Z]+)/", $input_line, $output_array);

            $tmp['date']  = $output_array[1];
            $tmp['level'] = $output_array[2];

            preg_match("/\[pid:(\d+)\](.*)/", $input_line, $output_array);

            $tmp['pid'] = $output_array[1];
            $tmp['msg'] = $output_array[2];

            $tmp['msg'] = ($tmp['type'] === "ALTER") ? SqlFormatter::highlight($tmp['msg']) : $tmp['msg'];

            $tmp['background'] = $this->setColor($tmp['pid']);

            preg_match("/by\s([\sa-zA-Z]+)\s+\(id:(\d+)\)/", $tmp['msg'], $output_array);

            if (!empty($output_array[2])) {
                $tmp['msg'] = str_replace($output_array[0], '', $tmp['msg']);
                $tmp['msg'] = $tmp['msg'].$this->getUser($output_array[1]);
            }


            $data[] = $tmp;
        }

        return $data;
    }

    private function setColor($type)
    {
        $hex = substr(md5($type), 0, 6);

        return $this->hexToRgb($hex);

        //return $hex['background'];
    }

    private function label($text)
    {
        $hex = $this->setColor($text);

        return '<span class="label" style="background:rgb('.$hex[0].', '.$hex[1].', '.$hex[2].',0.1);">'.$text.'</span>';
    }

    private function hexToRgb($colorName)
    {
        list($r, $g, $b) = array_map(
            function ($c) {
                return hexdec(str_pad($c, 2, $c));
            }, str_split(ltrim($colorName, '#'), strlen($colorName) > 4 ? 2 : 1)
        );

        return array($r, $g, $b);
    }

    private function getUser($id)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *,a.id as id_user FROM user_main a
            INNER JOIN geolocalisation_country b ON a.id_geolocalisation_country = b.id";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            return 'by <img class="country" src="'.IMG.'country/type1/'.strtolower($ob->iso).'.gif" width="18" height="12"> <a href="'.LINK.'user/id/'.$ob->id_user.'">'.$ob->firstname." ".$ob->name.'</a> ('.$ob->email.')';
        }
    }

    private function log($level, $type, $msg)
    {
        if (empty($this->id_cleaner)) {
            throw new \Exception("CLEANER-001 : Impossible to get id_cleaner");
        }

        if (IS_CLI) {
            $this->logger->{$level}('[id:'.$this->id_cleaner.']['.$type.'][pid:'.getmypid().'] '.$msg.' '."by".' [CLI]');
        } else {
            $this->logger->{$level}('[id:'.$this->id_cleaner.']['.$type.'][pid:'.getmypid().'] '.$msg.' '."by".' '
                .$this->di['auth']->getUser()->firstname." ".$this->di['auth']->getUser()->name." (id:".$this->di['auth']->getUser()->id.")");
        }
    }

    private function getrgba($label, $alpha)
    {
        list($r, $g, $b) = $this->setColor($label);
        return "rgba(".$r.", ".$g.", ".$b.", ".$alpha.")";
    }

    public function impacted($param)
    {


        Debug::parseDebug($param);

        $data['id_cleaner'] = $this->get_id_cleaner($param);

        $this->title = '<i class="fa fa-table" aria-hidden="true"></i> '.__('Tables impacted');
        if (Basic::from(__FILE__)) {
            return $this->title;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *,a.database,a.id as id_cleaner_main,b.id as id_mysql_server,
            b.name as mysql_server_name
        FROM cleaner_main a
        INNER JOIN mysql_server b ON a.id_mysql_server = b.id
        WHERE a.id = ".$data['id_cleaner'].";";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $data['database']    = $ob->database;
            $data['server_name'] = $ob->id_mysql_server;
        }

        $this->set('data', $data);
    }

    private function setCacheFile()
    {
        $this->path_to_orderby_tmp = TMP."cleaner/orderby_".$this->id_cleaner.".ser";
    }
    /*
     *
     * table comportant les FKs  fils => père
     * $this->table_filter   => hitorique des tables deja parcouru (pour eviter les boucles circulaires)
     * $childs  => lite de tous les enfants qui découle de la première table
     * $ref_table  la table pere qu'on est en train de vérifier
     */

    public function addChild($fks, $ref_table, $childs = array())
    {


        $this->table_filter[] = $ref_table;

        //Debug::debug($fks);
//        Debug::debug($ref_table, '<b style="color:#0000ff">REF TABLE</b>');
        Debug::debug($ref_table, "\$ref_table");

        if (!empty($fks[$ref_table])) {

            //Debug::debug($fks[$ref_table], 'table parent : '.$ref_table);

            $children = $fks[$ref_table];

            foreach ($children as $child) {

                if (!in_array($child, $this->table_filter)) {   //pour eviter les boucle infinie !
                    $childs = $this->addChild($fks, $child, $childs);
                }
            }

            $childs[] = $ref_table;
            return $childs;
        } else {

            $childs[] = $ref_table;
            return $childs;
        }
    }

    public function filterFkWithChildren($children, $foreign_keys)
    {
        foreach ($foreign_keys as $parent => $childs) {
            if (!in_array($parent, $children)) {
                unset($foreign_keys[$parent]);
                Debug::debug($parent, "delete");
            }
        }
        return $foreign_keys;
    }

    public function getForeignKeys()
    {

        $virtual_fk = $this->getVirtualForeignKeys();
        $real_fk    = $this->getRealForeignKeys();

        $fks = array_merge_recursive($real_fk, $virtual_fk);

        ksort($fks);
        $to_drop = $this->detectCircularDefinition(array());

        //Debug::debug($fks);
        Debug::debug($to_drop);

        foreach ($to_drop as $table => $arr) {

            foreach ($arr as $table_ref) {

                if (!empty($fks[$table_ref]))
                {
                    if (in_array($table, $fks[$table_ref])) {
                        $fliped = array_flip($fks[$table_ref]);
    
                        unset($fks[$table_ref][$fliped[$table]]);
                        echo "DELETE $table -> $table_ref \n";
                    }
                }
            }
        }
        //exit;

        return $fks;
    }

    public function generateMock($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT name FROM mysql_server WHERE id=".$id_mysql_server;
        $res = $db->sql_query($sql);
        while ($ob  = $db->sql_fetch_object($res)) {
            $remote_link = Sgbd::sql($ob->name);

            $sql = "SELECT `REFERENCED_TABLE_NAME` as `refrence_table`,`TABLE_NAME` as `table_name` FROM `information_schema`.`KEY_COLUMN_USAGE` "
                ."WHERE `CONSTRAINT_SCHEMA` ='".$database."' "
                ."AND `REFERENCED_TABLE_SCHEMA`='".$database."' "
                ."AND `REFERENCED_TABLE_NAME` IS NOT NULL;";

            Debug::sql($sql);

            $res           = $db->sql_query($sql);
            $order_to_feed = array();

            while ($ob = $db->sql_fetch_object($res)) {
                $order_to_feed[$ob->refrence_table][] = $ob->table_name;
            }


            echo json_encode($order_to_feed);
        }
    }

    public function skipReplication($db)
    {


        if ($db->checkVersion(array("MariaDB" => "5.5.21"))) {
            // https://mariadb.com/kb/en/selectively-skipping-replication-of-binlog-events/
            $sql = 'SET @@skip_replication = ON;';
            Debug::debug(Color::getColoredString($sql, "yellow"));

            $db->sql_query($sql);

            return true;
        } else {
            Debug::debug(Color::getColoredString("SET @@skip_replication = ON; in not spupported ! be carrfull if you want an history server as Slave !",
                    "yellow"));
            return false;
        }
    }

    public function testOrder($param)
    {


        $this->getOrderBy2(array($data, "ipe", 'ASC'));

        Debug::parseDebug($param);
        CleanerTest::testGetOrderBy();
    }

    public function getOrderBy2($param)
    {


        $foreign_keys = $param[0];
        $main_table   = $param[1];
        $order        = $param[2] ?? 'ASC';

        $this->setCacheFile();

        Debug::debug(count($foreign_keys), 'origin');

        if (!file_exists($this->path_to_orderby_tmp)) {

            $tmp = $foreign_keys;

            //On retire les FKs en double
            foreach ($tmp as $key => $tab) {
                $foreign_keys[$key] = array_unique($foreign_keys[$key]);
            }

            $tmp = $foreign_keys;

            //remove all tables with no father from $this->main_table
            $foreign_keys = $this->removeTableNotImpacted($foreign_keys);

            ksort($foreign_keys);
            Debug::debug($foreign_keys);

            Debug::debug(count($foreign_keys), 'after removeTableNotImpacted');

            Debug::debug($this->table_to_purge);

            $level   = array();
            $level[] = $this->table_to_purge;

            // le but est de récupérer tous les enfants de la table principale
            $all_childs = $this->addChild($foreign_keys, $main_table, array($main_table));

            sort($all_childs);

            Debug::debug($all_childs, "ALL CHILDS");
            $foreign_keys = $this->filterFkWithChildren($all_childs, $foreign_keys);

            Debug::debug(count($foreign_keys), 'after filterFkWithChildren');
            /*             * ** */


            //exit;
            $array = $foreign_keys;

            // test des tables qui boucle sur elle même
            $tmp2 = $array;
            foreach ($tmp2 as $table_name => $childs) {

                foreach ($childs as $key => $child) {
                    if ($table_name === $child) {

                        $this->fk_circulaire[] = $table_name;
                        unset($array[$table_name][$key]);
                        $cas_found             = true;
                    }
                }
            }

            //debug($array);

            $i    = 0;
            while ($last = count($array) != 0) {

                //echo "level " . $i . PHP_EOL;
                $temp = $array;

                foreach ($temp as $father_name => $tab_father) {
                    foreach ($tab_father as $key_child => $table_child) {
                        if (!in_array($table_child, array_keys($array))) {

                            if (empty($level[$i]) || !in_array($table_child, $level[$i])) {
                                $level[$i][] = $table_child;
                            }
                            //debug($level);
                            unset($array[$father_name][$key_child]);
                            //debug($array);
                        }
                    }
                }

                $temp = $array;

// retirer les tableaux vides, et remplissage avec clefs


                Debug::debug($temp, 'temp');

                foreach ($temp as $key => $tmp) {
                    if (count($tmp) == 0) {
                        unset($array[$key]);
                        if (empty($level[$i + 1]) || !in_array($key, $level[$i + 1])) {
                            $level[$i + 1][] = $key;
                        }
                    }
                }

                if ($last == count($array)) {
                    $cas_found = false;

                    //cas de deux chemins differents pour arriver à la même table enfant
                    $temp = $array;
                    foreach ($temp as $key1 => $tab2) {
                        foreach ($tab2 as $key2 => $val) {
                            foreach ($level as $tab3) {
                                if (in_array($val, $tab3)) {
                                    unset($array[$key1][$key2]);
                                    $cas_found = true;
                                }
                            }

                            //debug($val);
                        }
                    }

                    if (!$cas_found) {
                        echo "\n";

                        Debug::debug($temp);
                        Debug::debug($tab2);
                        Debug::debug($level);
                        Debug::debug($array);

                        $tables = array();
                        foreach ($array as $key => $tab) {
                            $tables[] = $key;

                            foreach ($tab as $elem) {
                                $tables[] = $elem;
                            }
                        }

                        $tables = array_unique($tables);

                        sort($tables);

                        Debug::debug($level, "LEVEL");
                        Debug::debug($tables);

                        //$this->getCircular();
                        //echo implode("','", $tables);
                        //Debug::debug($this->fk_circulaire);
                        throw new \Exception("\nPMACTRL-333 Circular definition (table <-> table)");
                    }
                }

                sort($level[$i]);
                $i++;
            }

//dans le cas où il a pas au moins table fille on ajoute la table principale
            if (count($level[0]) == 0) {
                $level[0][0] = $this->main_table;
            }

            if ($order === "ASC") {
                krsort($level);
            } else {
                ksort($level);
            }

            $this->orderby = $level;

            Debug::debug($level, "LEVEL");
            //exit;

            file_put_contents($this->path_to_orderby_tmp, serialize($this));

            Debug::checkPoint("générer l'ordre de remplissasage et d'effacement");
        } else {

//on load le fichier précédement enregistré
            if (is_file($this->path_to_orderby_tmp)) {
                $s             = implode('', file($this->path_to_orderby_tmp));
                $tmp           = unserialize($s);
                $this->orderby = $tmp->orderby;
            }
        }


        if ($order === "ASC") {
            krsort($this->orderby);
        } else {
            ksort($this->orderby);
        }




        return $this->orderby;
    }

    public function dropFk($child, $parent)
    {

    }

    public function detectCircularDefinition($param)
    {

        Debug::parseDebug($param);

        /*
          $id_mysql_server = $param[0] ?? ;
          $database        = $param[1];
         */


        $db = Sgbd::sql($this->link_to_purge,'purge');

        $sql = "SELECT a.table_name, a.referenced_table_name,a.column_name, a.referenced_column_name
                FROM `information_schema`.`KEY_COLUMN_USAGE` a
                INNER JOIN `information_schema`.`KEY_COLUMN_USAGE` b ON a.table_name = b.referenced_table_name AND a.REFERENCED_TABLE_NAME = b.TABLE_NAME
                WHERE a.REFERENCED_TABLE_SCHEMA = '".$this->schema_to_purge."' and a.table_name != a.referenced_table_name
                GROUP BY a.table_name, a.referenced_table_name;";

        Debug::sql($sql);

        $res = $db->sql_query($sql);

        $fks = array();
        $all = array();

        $order = array();

        $i  = 0;
        while ($ob = $db->sql_fetch_object($res)) {

            $sql2 = "SELECT count(1) as cpt FROM `".$this->schema_to_purge."`.`".$ob->table_name."` WHERE  `".$ob->column_name."` IS NULL";
            $sql3 = "SELECT count(1) as cpt FROM `".$this->schema_to_purge."`.`".$ob->table_name."` WHERE  `".$ob->column_name."` IS NOT NULL";

            $res2 = $db->sql_query($sql2);
            $res3 = $db->sql_query($sql3);

            while ($ob2 = $db->sql_fetch_object($res2)) {

                $ob3 = $db->sql_fetch_object($res3);

                $is_null     = $ob2->cpt;
                $is_not_null = $ob3->cpt;

                //echo $ob->table_name." => ".$ob->referenced_table_name." (".$is_not_null." / ".($is_null + $is_not_null).") ".round($is_not_null / ($is_null + $is_not_null) * 100, 2)."%\n";



                $fks['TABLE_NAME']             = $ob->table_name;
                $fks['column_name']            = $ob->column_name;
                $fks['REFERENCED_TABLE_NAME']  = $ob->referenced_table_name;
                $fks['REFERENCED_COLUMN_NAME'] = $ob->referenced_column_name;
                $fks['is_null']                = $is_null;
                $fks['is_not_null']            = $is_not_null;
                $fks['total']                  = $is_null + $is_not_null;

                $percent        = round($is_not_null / ($is_null + $is_not_null) * 100, 2);
                $fks['percent'] = $percent;

                $all[$fks['TABLE_NAME']][$fks['REFERENCED_TABLE_NAME']] = $fks;
            }
        }


        $table = new Table(1);

        $table->addHeader(array('TABLE_NAME.COLUMN_NAME => REFERENCED_TABLE_NAME.REFERENCED_COLUMN_NAME', 'Feed', 'Taux d\'utilisation', 'Vs', 'Orphans', 'Choice'));

        $to_drop = array();

        foreach ($all as $table_name => $keys) {
            foreach ($keys as $referenced_table => $val) {
                $drop_fk          = "";
                //$referenced_table = $referenced_table;
                if ($val['percent'] < $all[$val['REFERENCED_TABLE_NAME']][$val['TABLE_NAME']]['percent']) {
                    $drop_fk = "DROP FK";

                    $to_drop[$table_name][] = $val['REFERENCED_TABLE_NAME'];
                }

                $table->addLine(array($table_name.'.'.$val['column_name'].' => '.$val['REFERENCED_TABLE_NAME'].'.'.$val['REFERENCED_COLUMN_NAME'],
                    $val['percent'].'%', "(".$val['is_not_null']."/".$val['total'].")", $all[$val['REFERENCED_TABLE_NAME']][$val['TABLE_NAME']]['percent']."%", $val['is_null'],
                    $drop_fk));
            }
        }

        //print_r($all);
        if (Debug::$debug) {
            echo $table->display();
        }

        Debug::debug($to_drop, "Table to drop");

        //$to_drop['equipement']   = array();
        //$to_drop['equipement'][] = "local";

//        $to_drop['batiment'] = array();
//        $to_drop['site'] = array();
//        $to_drop['batiment'][] = "equipement";
//        $to_drop['site'][] = "equipement";
        return $to_drop;
    }

    public function getCircularMulti($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = 108;
        $database        = 'test';

        $dblink = Sgbd::sql(DB_DEFAULT);
        $name   = Mysql::getDbLink($dblink, $id_mysql_server);

        $db = Sgbd::sql($name,'purge');
        $db->sql_select_db('test');

        $tables = array('batiment', 'equipement', 'escalier', 'etage', 'local', 'site');

        $tables2  = $tables;
        $doublons = array();

        $k = 0;
        foreach ($tables as $table) {
            foreach ($tables2 as $table2) {
                if ($table === $table2) {
                    continue;
                }

                if (!empty($doublons[$table][$table2])) {
                    continue;
                }
                $k++;

                $way1 = $this->getPath($table, $table2, $db);
                $way2 = $this->getPath($table2, $table, $db);

                if (count($way1) > 0 && count($way2) > 0) {

                    $dtable = new Table(2);

                    echo "#".$k." Boucle circulaire detecter ( ".$table." <=> ".$table2.")\n";

                    $dtable->addHeader(array("#", "DEPART", "ARRIVEE", "CHEMIN"));

                    $i = 1;
                    foreach ($way1 as $path) {
                        $dtable->addLine(array($i, $path['cur_from'], $path['cur_dest'], $path['cur_path']));
                        $i++;
                    }

                    $dtable->addHr();
                    $i = 1;

                    foreach ($way2 as $path) {
                        $dtable->addLine(array($i, $path['cur_from'], $path['cur_dest'], $path['cur_path']));
                        //Debug::debug($path);
                        $i++;
                    }

                    echo $dtable->display();

                    echo ' '."\n";

                    $this->testOneToOne($way1);
                    $this->testOneToOne($way2);
                }

                if (strcasecmp($table, $table2) == 0) {
                    $doublons[$table][$table2] = 1;
                } else {
                    $doublons[$table2][$table] = 1;
                }
            }
        }


        $i = 1;
        foreach ($this->testfk as $fk) {
            echo "#".$i." ".$fk;
            $i++;
        }
    }
    /*
     * CTE permetant de tester tous les chemin possible entre 2 tables
     *
     *
     */

    public function getPath($table, $table2, $db)
    {
        $sql = "WITH RECURSIVE paths (cur_from, cur_path, cur_dest) AS (
 SELECT '".$table."',`table`, `table` FROM `path` WHERE `table`='".$table."'
UNION
 SELECT '".$table."', CONCAT(paths.cur_path, ',', `path`.ref_table), `path`.ref_table
  FROM paths, `path`
  WHERE paths.cur_dest = `path`.`table` AND
  NOT FIND_IN_SET(`path`.ref_table, paths.cur_path)
)
SELECT * FROM paths where cur_dest = '".$table2."';";

        //Debug::sql($sql);
        $res = $db->sql_query($sql);

        $rows = array();
        while ($ob   = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {

            $rows[] = $ob;
        }


        //debug($rows);

        return $rows;
    }

    public function testOneToOne($way, $order = 'ASC')
    {
        if (count($way) > 0) {

            foreach ($way as $path) {
                $tables = explode(",", $path['cur_path']);

                if (count($tables) === 2) {
                    $this->testFk($tables[0], $tables[1]);
                }
            }
        }
    }

    public function testFk($table_a, $table_b)
    {

        $sql = "SELECT table_name, column_name, referenced_table_name, referenced_column_name  FROM `information_schema`.`KEY_COLUMN_USAGE`
            WHERE TABLE_NAME ='".$table_a."'
            AND REFERENCED_TABLE_NAME = '".$table_b."' AND TABLE_SCHEMA='".$this->schema_to_purge."' AND REFERENCED_TABLE_SCHEMA='".$this->schema_to_purge."' ";

        $db = $this->db;

        $res = $db->sql_query($sql);

        $i = 0;

        while ($ob = $db->sql_fetch_object($res)) {
            $i++;

            $sql2 = "SELECT count(1) as cpt FROM `".$this->schema_to_purge."`.`".$ob->table_name."` WHERE  `".$ob->column_name."` IS NULL";
            $sql3 = "SELECT count(1) as cpt FROM `".$this->schema_to_purge."`.`".$ob->table_name."` WHERE  `".$ob->column_name."` IS NOT NULL";

            $res2 = $db->sql_query($sql2);
            $res3 = $db->sql_query($sql3);

            while ($ob2 = $db->sql_fetch_object($res2)) {

                $ob3 = $db->sql_fetch_object($res3);

                $is_null     = $ob2->cpt;
                $is_not_null = $ob3->cpt;

                $this->testfk[] = $ob->table_name.".".$ob->column_name."\t=>\t".$ob->referenced_table_name.".".$ob->referenced_column_name."\t(".$is_not_null."\t/\t".($is_null
                    + $is_not_null).") ".round($is_not_null / ($is_null + $is_not_null) * 100, 2)."%\t(orphans ".($is_null).")\n";

                /*
                  $fks['TABLE_NAME']             = $ob->table_name;
                  $fks['column_name']            = $ob->column_name;
                  $fks['REFERENCED_TABLE_NAME']  = $ob->referenced_table_name;
                  $fks['REFERENCED_COLUMN_NAME'] = $ob->referenced_column_name;
                  $fks['is_null']                = $is_null;
                  $fks['is_not_null']            = $is_not_null;
                  $fks['total']                  = $is_null + $is_not_null;

                  $percent        = round($is_not_null / ($is_null + $is_not_null) * 100, 2);
                  $fks['percent'] = $percent;


                  $all[$fks['TABLE_NAME']][$fks['REFERENCED_TABLE_NAME']] = $fks;
                 *
                 */
            }
        }
    }

    public function comptage($param)
    {
        Debug::parseDebug($param);

        $table = new Table(2);

        $table->addHeader(array('Table', 'Count'));

        $db = Sgbd::sql($this->link_to_purge,'purge');

        $sql = "select table_name, table_rows from information_schema.tables where table_schema ='".$this->schema_delete."'  order by table_name;";

        $res = $db->sql_query($sql);

        $tables = array();
        while ($ob     = $db->sql_fetch_object($res)) {

            $sql2 = "SELECT count(1) as cpt FROM `".$this->schema_delete."`.`".$ob->table_name."`";
            Debug::debug($sql2);
            $res2 = $db->sql_query($sql2);

            while ($ob2 = $db->sql_fetch_object($res2)) {
                if ($ob2->cpt !== "0") {
                    $table->addLine(array($ob->table_name, $ob2->cpt));
                }
            }

            $tables[] = $ob->table_name;
        }

        echo $table->display();

        echo implode("','", $tables);
    }

    public function feedCircularFk($table)
    {

    }



    public function setDebug($param)
    {
        $id_cleaner_main = (int)$param[0] ?? "";

        if (empty($id_cleaner_main)) {
            throw new \Exception('This function take id_cleaner_main in parameter');
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM cleaner_main WHERE id=".$id_cleaner_main;
        $res = $db->sql_query($sql);
        while($ob = $db->sql_fetch_object($res))
        {
            shell_exec("kill -SIGUSR1 ".$ob->pid);
        }

        header("location: ".LINK."Cleaner/details/1");
    
    }


}