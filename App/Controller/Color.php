<?php

namespace App\Controller;

use \Glial\I18n\I18n;
use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


class Color extends Controller
{
    public function index($param)
    {
        $this->di['js']->addJavascript(array('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.3.6/js/bootstrap-colorpicker.min.js'));
    
        $this->di['js']->code_javascript('$(".colorpicker").colorpicker({format: "hex"});');

        


        $_GET['type'] = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM dot3_legend ORDER BY `order`";

        $res = $db->sql_query($sql);

        $type = [];
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC))
        {
            $type[] = $arr['type'];

            $data['legend'][$arr['type']][$arr['const']] = $arr;
        }

        $data['type'] = array_unique($type);
        sort($data['type']);

        $this->set('data', $data);
    }


}