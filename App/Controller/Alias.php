<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use App\Library\Debug;
use App\Library\Extraction;
use App\Library\System;


class Alias extends Controller
{

    public function index()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *,ROW_START,ROW_END FROM alias_dns a
        ORDER BY dns, port";

        $res = $db->sql_query($sql);


        $data['alia_dns'] = array();

        while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['alia_dns'][] = $ob;
        }

        $this->set('data', $data);
    }

    /*
     * met en relation un slave avec son master
     * 
     */
    public function updateAlias($param)
    {
        Debug::parseDebug($param);
        $db   = Sgbd::sql(DB_DEFAULT);
        $list = Extraction::display(array("slave::master_host", "slave::master_port"));

        $list_host = array();
        foreach ($list as $masters) {
            foreach ($masters as $master) {

                $key = $master['master_host'].':'.$master['master_port'];

                $host[$key]  = $master;
                $list_host[] = $master['master_host'];
            }
        }
        
        //Debug::debug($host);
        Debug::debug($list_host);

        $sql = "SELECT dns, port, id_mysql_server FROM `alias_dns`;";
        $res = $db->sql_query($sql);

        $all_dns = array();
        while ($ob      = $db->sql_fetch_object($res)) {

            $uniq           = $ob->dns.':'.$ob->port;
            $all_dns[$uniq] = $ob->id_mysql_server;
        }

        Debug::debug($all_dns);

        $sql = "SELECT id, ip, port FROM mysql_server";
        $res = $db->sql_query($sql);

        $mysql_server = array();
        while ($ob           = $db->sql_fetch_object($res)) {
            $uniq                = $ob->ip.':'.$ob->port;
            $mysql_server[$uniq] = $ob->id;
        }

        foreach ($host as $dns) {
            $uniq = $dns['master_host'].':'.$dns['master_port'];

            if (!empty($mysql_server[$uniq])) {
                continue;
            }

            if (!empty($all_dns[$uniq])) {
                continue;
            }

            $ip   = System::getIp($dns['master_host']);
            $uniq = $ip.':'.$dns['master_port'];

            if (!empty($mysql_server[$uniq])) {

                $alias_dns                                 = array();
                $alias_dns['alias_dns']['id_mysql_server'] = $mysql_server[$uniq];
                $alias_dns['alias_dns']['dns']             = $dns['master_host'];
                $alias_dns['alias_dns']['port']            = $dns['master_port'];
                $alias_dns['alias_dns']['destination']     = $ip;
                $db->sql_save($alias_dns);
            }
        }

        if (!IS_CLI) {
            header("location: ".LINK."alias/index");
        }
    }
    
}