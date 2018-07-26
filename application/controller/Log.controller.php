<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\I18n\I18n;
use App\Library\Extraction;

class Log extends Controller
{

    public function index()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);

        $data = array();

        Extraction::setDb($db);
        $res = Extraction::extract(array("last_io_error", "last_sql_error", "master_log_file", "exec_master_log_pos"), array(7), array("2018-07-26 10:00:00","2018-07-26 11:48:33"));

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['log'][] = $arr;
        }


        $this->set('data', $data);
    }
}