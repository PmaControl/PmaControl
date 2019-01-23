<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Synapse\FactoryController;

class Menu extends Controller
{
    public function show($params)
    {
        $id_menu = $params[0];
        //debug($id_menu);

        $db = $this->di['db']->sql(DB_DEFAULT);
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

    public function getSelectedLevelOneMenu($id_menu)
    {
        $Array = FactoryController::GetRootNode();

        $db = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT IFNULL(e.id,IFNULL(d.id,IFNULL(c.id,IFNULL(b.id,a.id)))) AS levelonemenu_id "
                . "FROM `menu` AS a "
                . "LEFT JOIN `menu` AS b ON a.parent_id = b.id AND b.parent_id IS NOT NULL "
                . "LEFT JOIN `menu` AS c ON b.parent_id = c.id AND c.parent_id IS NOT NULL "
                . "LEFT JOIN `menu` AS d ON c.parent_id = d.id AND d.parent_id IS NOT NULL "
                . "LEFT JOIN `menu` AS e ON d.parent_id = e.id AND e.parent_id IS NOT NULL "
                . "WHERE a.group_id= " . $id_menu . " AND a.class = '".$Array[0]."' AND a.method = '".$Array[1]."'";
        $ThisData = $db->sql_fetch_all($sql);
        
        if (isset($ThisData[0]["levelonemenu_id"]))
            return $ThisData[0]["levelonemenu_id"];
        else
            return null;
    }
}
