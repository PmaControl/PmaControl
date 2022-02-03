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


class Menu extends Controller {

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
