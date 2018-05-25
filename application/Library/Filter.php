<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

trait Filter {

    static private function getFilter() {

        $where = "";
        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }

        if (!empty($environment)) {
            $where .= " AND a.id_environment IN (" . implode(',', json_decode($environment, true)) . ")";
        }
        if (!empty($client)) {
            $where .= " AND a.id_client IN (" . implode(',', json_decode($client, true)) . ")";
        }

        return $where;
    }

    public function getSelectServerAvailable($param = array()) {

        if (!empty($param[0])) {
            $data['table'] = $param[0];
        } else {
            $data['table'] = "mysql_server";
        }

        if (!empty($param[1])) {
            $data['field'] = $param[1];
        } else {
            $data['field'] = "id";
        }



        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT case error WHEN error='' THEN 1 ELSE 0 END AS error, id, display_name,ip FROM mysql_server a WHERE 1 " . self::getFilter() . " ORDER by name";

        $res = $db->sql_query($sql);

        $data['list_servers'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['error'] = $ob->error;
            $tmp['libelle'] = $ob->display_name . " (" . $ob->ip . ")";

            $tmp['extra'] = array("data-content" => "<span class='label label-success'>".$ob->display_name . " (" . $ob->ip . ")"."</span> ");
            
            $data['list_server'][] = $tmp;
        }


        $this->set('data', $data);

        return $data['list_server'];
    }


    public function getServer()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);

        
        $sql = "SELECT a.*,d.libelle, d.class FROM mysql_server a
            INNER JOIN environment d on d.id = a.id_environment
            WHERE 1=1 ".self::getFilter();

        $res = $db->sql_query($sql);

        $server = array();
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server[$arr['id']] = $arr;

            $server[$arr['id']]['link'] = '<span class="label label-'.$arr['class'].'">'
            .substr($arr['libelle'], 0, 1).'</span> '
            .' <a href="">'.$arr['display_name'].'</a>';
        }

        return $server;
    }

}
