<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for home workflows.
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
class Home extends Controller {

/**
 * Prepare home state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/home/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function before($param) {
        $this->di['js']->addJavascript(array("jquery-latest.min.js", "bootstrap.min.js", "http://getbootstrap.com/assets/js/docs.min.js"));
    }

/**
 * Render home state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/home/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function index() {
        $this->title = __("Home");
        $this->ariane = " > " . __("Welcome to PmaControl !");

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM `home_box` ORDER BY `order`;";

        $res = $db->sql_query($sql);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['item'][] = $arr;
        }

        $this->set('data', $data);


        //$this->javascript = array("");
    }

/**
 * Retrieve home state through `list_server`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for list_server.
 * @phpstan-return void
 * @psalm-return void
 * @see self::list_server()
 * @example /fr/home/list_server
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function list_server($param) {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server ORDER BY ip";
        $data['server'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);
    }

}

