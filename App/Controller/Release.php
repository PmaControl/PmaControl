<?php

namespace App\Controller;


use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for release workflows.
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
class Release extends Controller {
    /*
     *
     * git config --get remote.origin.url
     * 
     */

    public function make($params) {
        
    }

/**
 * Handle release state through `bdd`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for bdd.
 * @phpstan-return void
 * @psalm-return void
 * @see self::bdd()
 * @example /fr/release/bdd
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function bdd() {
        
    }

/**
 * Retrieve release state through `getLastVersion`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getLastVersion.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getLastVersion()
 * @example /fr/release/getLastVersion
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getLastVersion() {
        $ret = shell_exec("cd " . ROOT . " && git log -1");

        $output_array = array();
        preg_match("/commit\s([a-f0-9]{40})/", $ret, $output_array);

        if (!empty($output_array[1])) {
            $numrevision = $output_array[1];

            return $numrevision;
        }
    }

/**
 * Handle release state through `publishVersion`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for publishVersion.
 * @phpstan-return void
 * @psalm-return void
 * @see self::publishVersion()
 * @example /fr/release/publishVersion
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function publishVersion() {
        $version = $this->getLastVersion();

        file_put_contents(ROOT . DS . "version", $version);
    }

/**
 * Retrieve release state through `getOldsql`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for getOldsql.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getOldsql()
 * @example /fr/release/getOldsql
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
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

