<?php

namespace App\Controller;


use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


class Release extends Controller {
    /*
     *
     * git config --get remote.origin.url
     * 
     */

    public function make($params) {
        
    }

    public function bdd() {
        
    }

    public function getLastVersion() {
        $ret = shell_exec("cd " . ROOT . " && git log -1");

        $output_array = array();
        preg_match("/commit\s([a-f0-9]{40})/", $ret, $output_array);

        if (!empty($output_array[1])) {
            $numrevision = $output_array[1];

            return $numrevision;
        }
    }

    public function publishVersion() {
        $version = $this->getLastVersion();

        file_put_contents(ROOT . DS . "version", $version);
    }

    public function getOldsql() {
        $actual = $this->getLastVersion();
        echo "Actual version : " . $actual . "\n";

        $file = ROOT . DS . "version";


        if (file_exists($file)) {
            $version = file_get_contents(ROOT . DS . "version");
            $cmd = "cd " . ROOT . " && git checkout " . $version;

            $gg = shell_exec($cmd);

            echo $gg;

            $db = Sgbd::sql(DB_DEFAULT);
            $database = "cmp_" . $version;

            $db->sql_query("DROP DATABASE IF EXISTS " . $database);
            $db->sql_query("CREATE DATABASE IF NOT EXISTS " . $database);

            $cmd = "pv " . ROOT . "/sql/full/pmacontrol.sql | mysql " . $database;
            passthru($cmd, $exit);
        }
    }

}
