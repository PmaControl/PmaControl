<?php

use \Glial\Synapse\Controller;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ToolsBox extends Controller
{

    public function memory()
    {
        $this->title = __("Memory");
        $this->ariane = " > " . __("Tools Box") . " > " . $this->title;

        $default = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server a
            INNER JOIN `mysql_replication_stats` b ON a.id = b.id_mysql_server 
            WHERE is_available = 1 
            order by a.`name`";
        $res50 = $default->sql_query($sql);

        while ($ob50 = $default->sql_fetch_object($res50)) {
            $db = $this->di['db']->sql($ob50->name);
            $data['variables'][$ob50->name] = $db->getVariables();
        }

        $this->set('data', $data);
    }

    public function indexUsage()
    {
        $this->title = __("Index usage");
        $this->ariane = " > " . __("Tools Box") . " > " . $this->title;

        $default = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server a
            INNER JOIN `mysql_replication_stats` b ON a.id = b.id_mysql_server
            WHERE is_available = 1
            order by `name`";
        $res50 = $default->sql_query($sql);

        $data = [];
        while ($ob50 = $default->sql_fetch_object($res50)) {

            $db = $this->di['db']->sql($ob50->name);
            $data['status'][$ob50->name] = $db->getStatus();
          
        }


        $this->set('data', $data);
    }

}
