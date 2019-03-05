<?php
/*
 * j'ai ajouté un mail automatique en cas d'erreur ou de manque sur une PK 
 */

use \Glial\Synapse\Controller;
use \Glial\I18n\I18n;
use \Glial\Security\Crypt\Crypt;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use App\Library\Chiffrement;
use \App\Library\Debug;

use App\Library\System;

class Archives extends Controller
{

    use App\Library\Filter;
    use App\Library\Scp;
    use App\Library\File;
    
    var $id_user_main    = 0;
    var $id_archive_load = 0;
    var $user            = array();

    public function index($param)
    {

        $this->di['js']->addJavascript(array('jquery-latest.min.js',
            'archives/index.js'
        ));

        $this->di['js']->addJavascript(array("Chart.min.js"));
        $this->title  = '<span class="glyphicon glyphicon-book" aria-hidden="true"></span> '.__("Archives");
        $this->ariane = ' > <a href⁼"">'.'<i class="fa fa-puzzle-piece"></i> '
            .__("Plugins").'</a> > '.$this->title;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT a.id_cleaner_main, sum(a.size_sql) as size_sql, sum(a.size_remote) as size_remote, count(1) as cpt,
            c.display_name, b.libelle, b.database, b.main_table, c.id
                FROM archive a
                INNER JOIN cleaner_main b ON a.id_cleaner_main = b.id
                INNER JOIN mysql_server c ON c.id = b.id_mysql_server
                group by a.id_cleaner_main;";

        $res = $db->sql_query($sql);

        $data = array();
        $cleaner = array();
        $size = array();

        while ($row = $db->sql_fetch_row($res)) {
            $data['cleaner'][] = $row;
            $cleaner[]         = $row[5];
            $size[]            = $row[2];
        }

//        $data['list_server'] = $this->getSelectServerAvailable();

        $data['databases'] = array();
        if (!empty($_GET['spider']['database'])) {
            $select1           = $this->getDatabaseByServer(array($_GET['mysql_server']['id']));
            $data['databases'] = $select1['databases'];
        }

        $this->di['js']->code_javascript("
            
function FileConvertSize(aSize){
	aSize = Math.abs(parseInt(aSize, 10));
        if (aSize == 0)
        {
            return 0;
        }

	var def = [[1, 'o'], [1024, 'Ko'], [1024*1024, 'Mo'], [1024*1024*1024, 'Go'], [1024*1024*1024*1024, 'To']];
	for(var i=0; i< def.length; i++){
		if(aSize<def[i][0]) return (aSize/def[i-1][0]).toFixed(2)+' '+def[i-1][1];
	}
}

var ctx = document.getElementById('myChart').getContext('2d');            
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['".implode("','", $cleaner)."'],
        datasets: [{
            label: '".__("size on storage area")."',
            scaleLabel : '<%= FileConvertSize(value) %>',
            data: [".implode(",", $size)."],
            backgroundColor: [
                'rgba(54, 162, 235, 0.5)',
                'rgba(54, 162, 235, 0.5)',
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(54, 162, 235, 1)',

            ],
            borderWidth: 1
        }]
    },
    options: {
    
        
        legend: { display: false },
      title: {
        display: true,
        text: '".__("Size on storage area")."'
      },
      
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    return FileConvertSize(tooltipItem.yLabel);
                }
            }
        },
        scales: {
            yAxes: [{
                type: 'logarithmic',
                ticks: {
                    userCallback: function(tick) {
                    var remain = tick / (Math.pow(10, Math.floor(Chart.helpers.log10(tick))));
                        if (remain === 1 || remain === 2 || remain === 5) {
                                return FileConvertSize(tick.toString() );
                        }
                        return '';
	            	
	        }
                    
                },
                scaleLabel: {
                        display: true,
                        labelString: 'size'
                    }
            }]
        }
    }
});
");

        $this->set('data', $data);
    }

    public function file_available($param)
    {



        $this->title  = '<span class="glyphicon glyphicon-book" aria-hidden="true"></span> '.__("Archives");
        $this->ariane = ' > <a href⁼"">'.'<i class="fa fa-puzzle-piece"></i> '
            .__("Plugins").'</a> > '.$this->title;


        $id_cleaner = $param[0];

        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "select a.`id`,a.`md5_sql`,a.size_sql,a.`date`, b.`ip`, a.pathfile,
            a.size_remote , a. time_to_compress, a.time_to_crypt, a.time_to_transfert
            FROM `archive` a
            INNER JOIN backup_storage_area b ON a.id_backup_storage_area =b.id
            WHERE `id_cleaner_main`= ".$id_cleaner." ORDER BY `date` DESC;";


        $res = $db->sql_query($sql);

        $data['archive'] = array();
        while ($row             = $db->sql_fetch_row($res)) {
            $data['archive'][] = $row;
        }

//$data['list_server'] = $this->getSelectServerAvailable();

        $this->set('data', $data);
    }
    /*
     * 
     * @example : /usr/bin/php7.0 /data/www/pmacontrol/application/webroot/index.php Archives load 6 1 CLEAN
     */

    public function load($param)
    {

        Debug::parseDebug($param);

        if (IS_CLI) {
            $id_archive_load = (int) $param[0];
            $this->view      = false;

            Debug::debug($id_archive_load, "archive_load");


            $db = $this->di['db']->sql(DB_DEFAULT);

// to delete
            $db->sql_query("update archive_load set status='NOT_STARTED' WHERE id=15;");
            $db->sql_query("delete from archive_load_detail where id_archive_load=15;");

            $this->id_archive_load = $id_archive_load;



//problem d'invertion si on lance un reload au même moment
            $sql = "SELECT a.*,b.name as mysqlserver,sum(size_sql) as total_size
                FROM archive_load a
                INNER JOIN mysql_server b on a.id_mysql_server = b.id
                INNER JOIN archive c on c.id_cleaner_main = a.id_cleaner_main
                   WHERE a.id = ".$id_archive_load." AND a.status = 'NOT_STARTED'
                GROUP BY a.id;";

            $res = $db->sql_query($sql);


            Debug::debug($sql);

            while ($ob2 = $db->sql_fetch_object($res)) {

                Debug::debug($ob2, "ob2");

                $id_cleaner_main    = $ob2->id_cleaner_main;
                $database           = $ob2->database;
                $mysqlservertoload  = $ob2->mysqlserver;
                $total_size         = $ob2->total_size;
                $this->id_user_main = $ob2->id_user_main;


                Debug::debug("The load of archive is started");
                $this->log("info", "START", "The load of archive is started");
                $sql3 = "UPDATE archive_load SET status = 'STARTED' WHERE id = ".$id_archive_load."";

                Debug::debug($sql3, "sql3");



                $db->sql_query($sql3);
            }

            $sql2 = "SELECT `a`.*,`ssh_login`,`ssh_password`,`ssh_key`,`c`.`ip`,`c`.`port`
                from `archive` `a`
                INNER JOIN `backup_storage_area` `c` on `c`.`id` = `a`.`id_backup_storage_area`
            where `a`.`id_cleaner_main` = ".$id_cleaner_main.";";


            Debug::sql($sql2, "sql2");

            $res2 = $db->sql_query($sql2);

            Crypt::$key = CRYPT_KEY;
            $size       = 0;



            $archive_load_detail = array();


            while ($arr = $db->sql_fetch_array($res2, MYSQLI_ASSOC)) {
                $save                                           = array();
                $save['archive_load_detail']['id_archive']      = $arr['id'];
                $save['archive_load_detail']['id_archive_load'] = $id_archive_load;
                $save['archive_load_detail']['status']          = "NOT STARTED";

                $id_archive_load_detail = $db->sql_save($save);

                if ($id_archive_load_detail) {


                    $arr['id_archive_load_detail'] = $id_archive_load_detail;
                    $archives[]                    = $arr;
                } else {
                    Debug::debug($save);
                    Debug::debug($db->sql_error());
                    exit;
                }
            }






            foreach ($archives as $archive) {




                Debug::debug($archive);

                $start     = microtime(true);
                $file_name = pathinfo($archive['pathfile'])['basename'];

                $file = TMP."trash/".$id_cleaner_main."_".$file_name;


                $save                                           = array();
                $save['archive_load_detail']['id']              = $archive['id_archive_load_detail'];
                $save['archive_load_detail']['id_archive']      = $archive['id'];
                $save['archive_load_detail']['id_archive_load'] = $id_archive_load;
                $save['archive_load_detail']['status']          = "STARTED";
                $save['archive_load_detail']['date_start']      = date('Y-m-d H:i:s');
                $db->sql_save($save);





                Debug::debug($file, "FILE");


                $remote = $this->getFile($archive['id_backup_storage_area'], $archive['pathfile'], $file);

                $save['archive_load_detail']['time_to_transfert '] = $remote['execution_time'];
                $save['archive_load_detail']['size_remote ']       = $remote['size'] ?? 0;
                $save['archive_load_detail']['md5']                = $remote['md5'] ?? "";

                Debug::debug($remote);



                if (!file_exists($file)) {


                    $msg = "The remote file is not found (".$file.")";

                    $save['archive_load_detail']['error_msg'] = $msg;
                    $save['archive_load_detail']['status']    = "FILE_NOT_FOUND";
                    $save['archive_load_detail']['date_end']  = date('Y-m-d H:i:s');
                    $db->sql_save($save);


                    $this->log("error", "FILE NOT FOUND", $msg);
                    continue;
                } else {

                    $msg = "We donwloaded the file from : ".$archive['pathfile'];
                    $this->log("info", "SCP", $msg);
                }

                Debug::debug($msg, "SCP");

                if ($archive['md5_crypted'] !== md5_file($file)) {
                    $this->log("error", "CMP_MD5_REMOTE", "The remote file is corrupted the md5 don't correspond (".$archive['md5_crypted']." != ".md5_file($file).")");
                    continue;
                }

                $stats = $this->unCompressAndUnCrypt($file);

                if ($archive['md5_compressed'] !== $stats['uncompressed']['md5']) {
                    $this->log("error", "MD5_FILE", "The decrypted and uncompressed file is corrupted the md5 don't correspond (".$archive['md5_compressed']." != ".$stats['uncompressed']['md5'].")");
                    continue;
                }

                $data['execution_time'] = round(microtime(true) - $start, 0);


                $conf = $this->di['db']->getParam($mysqlservertoload);


                Debug::debug($conf);

                if (!empty($conf['crypted']) && $conf['crypted'] === "1") {
                    $conf['password'] = Crypt::decrypt($conf['password']);
                }


                if (empty($conf['port'])) {
                    $conf['port'] = 3306;
                }


                //to prevent old stuff we archived, like enum with empty choice or duble choice
                shell_exec("sed -i '1iSET sql_mode=\"ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\";' ".$stats['file_path']);

                $cmd = "pv ".$stats['file_path']." | mysql -h ".$conf['hostname']." -P ".$conf['port']." -u ".$conf['user']." -p'{password}' ".$database;

                Debug::debug($cmd);
                $cmd = str_replace("{password}", $conf['password'], $cmd);
                passthru($cmd, $exit);


                if ($exit !== 0) {

                    $status = "SUCCESSFULL";
                    $this->log("error", "MYSQL", " Error to load the file '".$stats['file_path']."' with mysql");
                } else {
                    $status = "ERROR";
                    $this->log("info", "MYSQL", "We loaded this file '".$stats['file_path']."' to mysql");


                    $size += $archive['size_sql'];
                }

                unlink($stats['file_path']);


                $percent = floor($size / $total_size * 100);





                $sql4 = "UPDATE archive_load SET status = 'RUNNING', progression = ".$percent." WHERE id = ".$id_archive_load."";
                $db->sql_query($sql4);



                $sql6 = "SELECT * FROM `archive_load_detail` WHERE id= ".$id_archive_load_detail.";";
                $res6 = $db->sql_query($sql6);

                while ($ob6 = $db->sql_fetch_object($res6)) {
                    $error_msg = $ob6->error_msg;
                }

                if (empty($ob->error_msg)) {
                    $status = "COMPLETED";
                } else {
                    $status = "ERROR";
                }

                $save['archive_load_detail']['status']   = $status;
                $save['archive_load_detail']['date_end'] = date('Y-m-d H:i:s');
                $db->sql_save($save);




                Debug::checkPoint("traitement : ".$stats['file_path']);
            }

            $this->log("info", "COMPLETED", "The load of archive is completed");
        }
    }

    public function history($param)
    {
        $_GET['path'] = __FUNCTION__;

        $this->title  = '<span class="glyphicon glyphicon-book" aria-hidden="true"></span> '.__("Archives");
        $this->ariane = ' > <a href⁼"">'.'<i class="fa fa-puzzle-piece"></i> '
            .__("Plugins").'</a> > '.$this->title;


        $db = $this->di['db']->sql(DB_DEFAULT);


        $this->testPid(); // put in error all previous script started with no running pid anymore


        $sql = "SELECT a.*, b.libelle, c.display_name, b.main_table, d.firstname, d.name,
 e.id as id_mysql_server_src, e.display_name as src, lower(iso) as iso, a.id_user_main
FROM `archive_load` a
INNER JOIN cleaner_main b ON a.id_cleaner_main = b.id
INNER JOIN mysql_server c ON c.id = a.id_mysql_server
INNER JOIN mysql_server e ON e.id = b.id_mysql_server
INNER JOIN user_main d ON d.id = a.id_user_main
INNER JOIN geolocalisation_country f on f.id = d.id_geolocalisation_country
ORDER BY a.id DESC";


        $res = $db->sql_query($sql);


        $data['history'] = array();
        while ($ob              = $db->sql_fetch_object($res)) {
            $data['history'][] = $ob;
        }

        $this->set('data', $data);
    }

    public function restore($param)
    {

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            $db = $this->di['db']->sql(DB_DEFAULT);

            foreach ($_POST['mysql_server'] as $arr) {
                if (!empty($arr['database'])) {

                    $this->load_archive(array($arr['id'], $arr['database'], $_POST['id_cleaner_main']));

                    header("location: ".LINK.'archives/');
                    exit;
                }
            }
        }
    }

    public function menu($param)
    {
        if (empty($_GET['path'])) {
            $_GET['path'] = "index";
        }


        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT max(`id`) as `last` from `archive_load`;";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_archive_load = $ob->last;
        }


        $data['menu']['index']['name']  = __('Total archives by cleaner');
        $data['menu']['index']['icone'] = '<i class="fa fa-file-archive-o" aria-hidden="true"></i>';
        //$data['menu']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
        $data['menu']['index']['path']  = LINK.__CLASS__.'/index';

        $data['menu']['history']['name']  = __('Restoration history');
        $data['menu']['history']['icone'] = '<i class="fa fa-history" aria-hidden="true"></i>';
        $data['menu']['history']['path']  = LINK.__CLASS__.'/history';

        $data['menu']['detail']['name']  = __('Restoration detail');
        $data['menu']['detail']['icone'] = '<i class="fa fa-tasks" aria-hidden="true"></i>';
        $data['menu']['detail']['path']  = LINK.__CLASS__.'/detail/'.$id_archive_load;


        $this->set('data', $data);
    }

    public function testPid()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id, pid FROM `archive_load` WHERE status = 'STARTED'";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if (!System::isRunningPid($ob->pid)) {

                $sql = "UPDATE `archive_load` SET status = 'ERROR' WHERE id = ".$ob->id.";";
                //debug($sql);
                $db->sql_query($sql);
            }
        }
    }

    public function before($param)
    {
        $logger       = new Logger('archive');
        $file_log     = LOG_FILE;
        $handler      = new StreamHandler($file_log, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
    }

    private function log($level, $type, $msg)
    {
        if (empty($this->id_user_main)) {
            throw new \Exception("ARCHIVE-001 : Impossible to get id_user_main");
        }

        if (empty($this->id_archive_load)) {
            throw new \Exception("ARCHIVE-002 : Impossible to get id_archive_load");
        }

        $user = $this->getUser();

        $this->logger->{$level}('[id:'.$this->id_archive_load.']['.$type.'][pid:'.getmypid().'] "'.$msg.'" '.__("by").' '
            .$user['firstname']." ".$user['name']." (id:".$user['id'].")");
    }

    private function getUser()
    {
        if (empty($this->user[$this->id_user_main])) {

            $db  = $this->di['db']->sql(DB_DEFAULT);
            $sql = "SELECT * FROM user_main where id = ".$this->id_user_main;
            $res = $db->sql_query($sql);

            $user = array();
            while ($ob   = $db->sql_fetch_object($res)) {
                $user['id']        = $ob->id;
                $user['firstname'] = $ob->firstname;
                $user['name']      = $ob->name;
            }

            $this->user[$this->id_user_main] = $user;
        }

        return $this->user[$this->id_user_main];
    }

    public function detail($param)
    {
        $db = $this->di['db']->sql(DB_DEFAULT);



        $_GET['path'] = "detail";

        $id_archive_load = $param[0];

        $sql = "SELECT * FROM archive_load a
            INNER JOIN archive_load_detail b ON a.id = b.id_archive_load
            INNER JOIN archive c ON c.id = b.id_archive
            WHERE a.id = ".$id_archive_load."
            ";

        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res)) {
            $data['details'][] = $arr;
        }



        //logs
        $cmd   = "cat ".LOG_FILE." | grep -F 'archive.' | grep -F '[id:".$id_archive_load."]' | tail -n 500";
        $lines = shell_exec($cmd);



        $data['logs'] = $this->format($lines, $id_archive_load);


        $this->set('data', $data);
    }

    public function load_archive($param)
    {
        Debug::parseDebug($param);



        $db = $this->di['db']->sql(DB_DEFAULT);

        //$tb = explode("-", $arr['database']);

        $id_mysql_server = $param[0];
        $database        = $param[1];
        $id_cleaner_main = $param[2];

        $php = explode(" ", shell_exec("whereis php"))[1];

        $archive_load                                    = array();
        $archive_load['archive_load']['id_cleaner_main'] = $id_cleaner_main;
        $archive_load['archive_load']['id_mysql_server'] = $id_mysql_server;
        $archive_load['archive_load']['database']        = $database;
        $archive_load['archive_load']['date_start']      = date('Y-m-d H:i:s');
        //$archive_load['archive_load']['date_end'] = "0000-00-00 00:00:00";
        $archive_load['archive_load']['progression']     = 0;
        $archive_load['archive_load']['duration']        = 0;
        $archive_load['archive_load']['pid']             = 0;
        $archive_load['archive_load']['status']          = "NOT_STARTED";
        $archive_load['archive_load']['id_user_main']    = 1;
        //$archive_load['archive_load']['id_archive']    = 1;

        $id_archive_load = $db->sql_save($archive_load);


        if ($id_archive_load) {


            $sql = "SELECT * FROM archive WHERE id_cleaner = ".$id_cleaner_main;

            $cmd = $php." ".GLIAL_INDEX." Archives load ".$id_archive_load." >> ".TMP."/archive_".$id_cleaner_main."_".$database.".sql & echo $!";

            Debug::debug($cmd);

            $pid = shell_exec($cmd);


            $archive_load['archive_load']['pid'] = (int) $pid;
            $archive_load['archive_load']['id']  = $id_archive_load;
            $db->sql_save($archive_load);


            $msg   = I18n::getTranslation(__("The loading on database is currently in progress ..."));
            $title = I18n::getTranslation(__("Loading"));
            set_flash("success", $title, $msg);
        } else {

            debug($db->sql_error());
            debug($archive_load);

            debug($_POST);
            exit;

            $msg   = I18n::getTranslation(__("Impossible to save : ")."'".print_r($db->sql_error())."'");
            $title = I18n::getTranslation(__("Loading"));
            set_flash("error", $title, $msg);
        }
    }

    // to move in class logs
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
            $tmp['type'] = $output_array[1];
            $input_line  = str_replace('[id:'.$id_cleaner.']', '', $line);
            preg_match("/^\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\] archive\.([a-zA-Z]+)/", $input_line, $output_array);

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

    private function hexToRgb($colorName)
    {
        list($r, $g, $b) = array_map(
            function($c) {
            return hexdec(str_pad($c, 2, $c));
        }, str_split(ltrim($colorName, '#'), strlen($colorName) > 4 ? 2 : 1)
        );

        return array($r, $g, $b);
    }
}