<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;

class Haproxy extends Controller {

    public function refreshConfiguration($param) {
        Debug::parseDebug($param);


        $db = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;



        $sql = "SELECT * FROM `haproxy_main`";

        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res)) {


            $config = $this->parseConfiguration($ob->config);


            Debug::debug($config);
        }
    }

    public function parseConfiguration($config) {
        $galeras = explode("listen", $config);

        unset($galeras[0]);

        $i = 0;
        foreach ($galeras as $galera) {
            $lines = explode("\n", $galera);
            $lines[0] = trim($lines[0]);

            $nb_elem = explode(" ", $lines[0]);
            Debug::debug($nb_elem);

            if (count($nb_elem) == 1) {
                //cas pour un server

                $haproxy[$i]['name'] = trim($lines[0]);

                foreach ($lines as $line) {
                    $line = trim($line);

                    if (empty($line) || $line{0} === "#") {
                        continue;
                    }

                    $elems = explode(" ", $line);
                    $variable = $elems[0];

                    switch ($variable) {
                        case 'server':

                            $tmp = array();

                            $tmp['name'] = $elems[1];
                            $tmp['ip'] = explode(':', $elems[2])[0];
                            $tmp['port'] = explode(':', $elems[2])[1];
                            $tmp['check_port'] = $elems[5];

                            if (!empty($elems[6])) {
                                $tmp['extra'] = $elems[6];
                            }

                            $haproxy[$i]['server'][] = $tmp;
                            break;

                        case 'bind':

                            $bind = explode(":", $elems[1]);
                            $haproxy[$i]['mask'] = $bind[0];
                            $haproxy[$i]['port'] = $bind[1];
                            break;
                    }
                }
            } else {

                //GESTION DES STATS (REFRAICHISSEMENT & LOGIN & PASSWORD)
            }

            $i++;
        }

        return $haproxy;
    }

}
