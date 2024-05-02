<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Post;
use App\Library\Debug;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

class Log extends Controller {


    const LOG_DIRECTORY=TMP."log/";

    const SIZE_MAX = 30 * 1024 * 1024;

    const EXT_OLD = ".1";
    const EXT_LOG = ".log";

    var $logger;

    public function index() {

        // http://eonasdan.github.io/bootstrap-datetimepicker/  <= date time picker

        $this->di['js']->addJavascript(array('moment.js', 'bootstrap-datetimepicker.js'));

        $this->di['js']->code_javascript(
                "$(function () {
            $('#datetimepicker1').datetimepicker({sideBySide:true,format:'YYYY-MM-DD HH:mm:ss'});
        });

        $(function () {
            $('#datetimepicker2').datetimepicker({sideBySide:true,format:'YYYY-MM-DD HH:mm:ss'});
        });

        ");

        $db = Sgbd::sql(DB_DEFAULT);

        $data = array();



        if ($_SERVER['REQUEST_METHOD'] == "POST") {



            if (!empty($_POST['mysql_server']['id'])) {
                $_POST['mysql_server']['id'] = "[" . implode(",", $_POST['mysql_server']['id']) . "]";
            }
            if (!empty($_POST['ts_variable']['id'])) {
                $_POST['ts_variable']['id'] = "[" . implode(",", $_POST['ts_variable']['id']) . "]";
            }

            header("location: " . LINK .$this->getClass(). "/" . __FUNCTION__ . "/" . Post::getToPost());
        }

        $data['log'] = array();

        if (!empty($_GET['mysql_server']['id']) && !empty($_GET['ts_variable']['id'])) {

            $id_mysql_servers = explode(',', substr($_GET['mysql_server']['id'], 1, -1));
            $id_ts_variables = explode(',', substr($_GET['ts_variable']['id'], 1, -1));


            $data['log'] = Extraction::display($id_ts_variables, $id_mysql_servers, array($_GET['ts']['date_start'], $_GET['ts']['date_end']), true);
        }


        $this->set('data', $data);
    }

    public function before($param)
    {
        $monolog       = new Logger("Aspirateur");
        $handler      = new StreamHandler(LOG_FILE, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

    public function rotate($param)
    {
        Debug::parseDebug($param);

        $files = glob(self::LOG_DIRECTORY."*".self::EXT_LOG);
        foreach($files as $file)
        {
            $file_size = filesize($file);
            if ($file_size > self::SIZE_MAX)
            {
                $previous_file = $file.self::EXT_OLD;

                $this->logger->notice("log rotate on file : $file [".self::formatFileSize($file_size)."]");

                if (file_exists($previous_file)){
                    unlink($previous_file);
                    Debug::debug("unlink $previous_file");
                }
                
                Debug::debug("rename $file TO $previous_file");
                rename($file, $file.self::EXT_OLD);
            }
        }
    }

    static function formatFileSize($size) {
        if ($size < 1024) {
            return $size . ' octets';
        } else if ($size < 1024 * 1024) {
            return round($size / 1024, 2) . ' Ko';  // Kilo-octets
        } else if ($size < 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024), 2) . ' Mo';  // Méga-octets
        } else {
            return round($size / (1024 * 1024 * 1024), 2) . ' Go';  // Giga-octets
        }
    }
}
