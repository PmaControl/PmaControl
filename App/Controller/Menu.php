<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Synapse\FactoryController;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for menu workflows.
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
class Menu extends Controller {

/**
 * Handle menu state through `show`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $params Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $params
 * @psalm-param array<int,mixed> $params
 * @return void Returned value for show.
 * @phpstan-return void
 * @psalm-return void
 * @see self::show()
 * @example /fr/menu/show
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function show($params) {
        $id_menu = $params[0];
        //debug($id_menu);

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT b.*, MAX(c.id) AS dropdown FROM `menu_group` a
INNER JOIN `menu` b ON a.id = b.group_id
LEFT JOIN `menu` c ON b.bg < c.bg AND b.bd > c.bd AND c.active = 1
WHERE b.active = 1 and b.parent_id is not null and a.id='" . $id_menu . "' GROUP BY b.id "
                . "ORDER BY b.bg";



        $data['sql'] = $sql;
        $data['menu'] = $db->sql_fetch_yield($sql);

        switch ($id_menu) {
            case 1:
                $data['position'] = "top";
                break;

            case 2:
                $data['position'] = "bottom";
                break;
            case 3:
                $data['position'] = "top";
                break;
        }

        $data['selectedmenu'] = $this->getSelectedLevelOneMenu($id_menu);

        $this->set('data', $data);
    }

/**
 * Retrieve menu state through `getSelectedLevelOneMenu`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_menu Input value for `id_menu`.
 * @phpstan-param int $id_menu
 * @psalm-param int $id_menu
 * @return mixed Returned value for getSelectedLevelOneMenu.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getSelectedLevelOneMenu()
 * @example /fr/menu/getSelectedLevelOneMenu
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getSelectedLevelOneMenu($id_menu) {
        $Array = FactoryController::getRootNode();

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "WITH a as (SELECT bg,bd, group_id FROM menu where `class`='" . $Array[0] . "' AND `method` = '" . $Array[1] . "' AND parent_id is not null LIMIT 1)
            SELECT * FROM menu b,a WHERE b.bg <= a.bg AND b.bd >= a.bg AND a.group_id = b.group_id AND parent_id is not null ORDER by b.bg";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            return $ob->id;
        }
    }
}

