<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use \Glial\Synapse\Controller;

class Daemon extends Controller
{
    public function index()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * from daemon_main order by id";
        $res = $db->sql_query($sql);

        $data['daemon'] = [];
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['daemon'][] =$arr;
        }

        $this->set('data',$data);
    }
}