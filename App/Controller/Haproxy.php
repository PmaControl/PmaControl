<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for haproxy workflows.
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
class Haproxy extends Controller {

/**
 * Handle haproxy state through `refreshConfiguration`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for refreshConfiguration.
 * @phpstan-return void
 * @psalm-return void
 * @see self::refreshConfiguration()
 * @example /fr/haproxy/refreshConfiguration
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function refreshConfiguration($param) {
        Debug::parseDebug($param);


        $db = Sgbd::sql(DB_DEFAULT);
        $this->view = false;



        $sql = "SELECT * FROM `haproxy_main`";

        $res = $db->sql_query($sql);


        while ($ob = $db->sql_fetch_object($res)) {


            $config = $this->parseConfiguration($ob->config);


            Debug::debug($config);
        }
    }

/**
 * Handle haproxy state through `parseConfiguration`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $config Input value for `config`.
 * @phpstan-param mixed $config
 * @psalm-param mixed $config
 * @return mixed Returned value for parseConfiguration.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::parseConfiguration()
 * @example /fr/haproxy/parseConfiguration
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

                    if (empty($line) || substr($line,0,1) === "#") {
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

