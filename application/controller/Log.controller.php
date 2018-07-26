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
        $data['log'] = Extraction::display(array("slave::"), array(7), array(0=>"2018-07-27 10:00:00",1=>"2018-07-27 12:08:10"), true);

        $data['db'] = $this->di['db']->sql(DB_DEFAULT);

        $this->set('data', $data);
    }
}