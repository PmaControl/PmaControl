<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\EngineV4;
use \Glial\Synapse\Controller;
use \App\Library\Debug;
use \App\Library\Microsecond;
use \Glial\I18n\I18n;
use \Glial\Sgbd\Sgbd;

class Dashboard extends Controller
{

    public function json($param)
    {
        $this->di['js']->addJavascript(array(
        'moment.js',
        'bootstrap-datetimepicker.min.js',
        ));

        $this->di['js']->code_javascript("
        $(document).ready(function() {
            $('.datepick').datetimepicker({
                format: 'YYYY-MM-DD',
                ignoreReadonly: true
            });
        });");


        $this->di['js']->code_javascript("
        $(document).ready(function() {
            $('#datetimepicker3').datetimepicker({
                 format: 'hh:mm:ss'
             });
        });");
 
        $id_mysql_server = $param[0] ?? 1;
        $id_ts_variable = $param[1] ?? 148;
        $date = $param[2] ?? '';
        $limit = $param[3] ?? 10;
        
        $db = Sgbd::sql(DB_DEFAULT);


        if ($_SERVER['REQUEST_METHOD'] === "POST")
        {

            
        }

        $sql = "";



    }





}

        