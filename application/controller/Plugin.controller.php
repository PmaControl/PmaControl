<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class Plugin extends Controller {

    public function index() {
        $PMAPLUGINURL = "http://localhost/plugins/";
        $JSONURL = $PMAPLUGINURL."extracted/plugin.json";

        if ($file = @file_get_contents($JSONURL))
        {
            $Array = json_decode($file,true);
        }
        else
        {
            $Array = null;
        }
        $this->set('data', $Array);
    }

    public function installed() {
        $PMAPLUGINURL = "http://localhost/plugins/";
        $JSONURL = $PMAPLUGINURL."extracted/plugin.json";

        if ($file = @file_get_contents($JSONURL))
        {
            $Array = json_decode($file,true);
        }
        else
        {
            $Array = null;
        }
        $this->set('data', $Array);
    }

    public function toupdate() {
        $PMAPLUGINURL = "http://localhost/plugins/";
        $JSONURL = $PMAPLUGINURL."extracted/plugin.json";

        if ($file = @file_get_contents($JSONURL))
        {
            $Array = json_decode($file,true);
        }
        else
        {
            $Array = null;
        }
        $this->set('data', $Array);
    }

    public function import() {
        
    }

}
