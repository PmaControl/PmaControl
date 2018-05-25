<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class Menu extends Controller
{

    public function show($params)
    {
        $id_menu = $params[0];
        //debug($id_menu);

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM `menu_group` a INNER JOIN `menu` b ON a.id = b.group_id WHERE b.active = 1 and parent_id is not null and a.id='" . $id_menu . "' ORDER BY b.bg";
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
        $this->set('data', $data);
    }

}
