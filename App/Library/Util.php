<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Util
{

    static private function getFilter($id_mysql_server = array(), $alias = 'a')
    {

        $where = "";
        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment                    = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client                    = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }

        if (!empty($environment)) {
            $where .= " AND `".$alias."`.id_environment IN (".implode(',', json_decode($environment, true)).")";
        }
        if (!empty($client)) {
            $where .= " AND `".$alias."`.id_client IN (".implode(',', json_decode($client, true)).")";
        }

        if (!empty($id_mysql_server)) {
            $where .= " AND `".$alias."`.id IN (".implode(',', $id_mysql_server).") ";
        }

        return $where;
    }

    public function getServer()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);


        $sql = "SELECT a.*,d.libelle, d.class FROM mysql_server a
            INNER JOIN environment d on d.id = a.id_environment
            WHERE 1=1 ".self::getFilter();

        $res = $db->sql_query($sql);

        $server = array();
        while ($arr    = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server[$arr['id']] = $arr;

            $server[$arr['id']]['link'] = '<span class="label label-'.$arr['class'].'">'
                .substr($arr['libelle'], 0, 1).'</span> '
                .' <a href="">'.$arr['display_name'].'</a>';
        }

        return $server;
    }
    
    
    /*
     * 
     * Retourne le nom de la classe sans l'espace de nom
     * 
     */
    
    
    static public function getController($class)
    {
        $elems = explode('\\', $class);
        return end($elems);
    }
}