<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;

class Format extends Controller
{

    public function index($param)
    {


        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            header("location: ".LINK.__CLASS__."/".__FUNCTION__."/".urlencode($_POST['sql']));
        }

        if (!empty($param[0])) {

            $data['sql']= $param[0];

            $data['sql_formated'] = \SqlFormatter::format($param[0]);

            $this->set('data', $data);
        }
    }
}