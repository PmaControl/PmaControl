<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class Plugin extends Controller
{

    public function index()
    {
        $PMAPLUGINURL = "http://localhost/plugins/";
        $JSONURL      = $PMAPLUGINURL."extracted/plugin.json";

        if ($file = @file_get_contents($JSONURL)) {
            $Array = json_decode($file, true);
            $this->jsontodatabase($file);
        } else {
            $Array = null;
        }
        $this->set('data', $Array);

        //$db = $this->di['db']->sql(DB_DEFAULT);
    }

    public function jsontodatabase($jsonInText)
    {
        $Array = json_decode($jsonInText, true);

        $db = $this->di['db']->sql(DB_DEFAULT);

        foreach ($Array as $key => $line):

            ksort($line);

            foreach ($line as $key2 => $line2):

                /*
                  nom	varchar(32)
                  description	varchar(255)
                  image	varchar(255) $line2["URL"]
                  repertoire	varchar(32)
                  date_installation	datetime
                  md5_zip	binary(16)
                  version	char(10)
                  est_actif	int(11)
                  numero_licence	varchar(255)
                 */
                $date = DateTime::createFromFormat('d/m/Y', $line2['CreationDate']);
                $Query = "REPLACE INTO plugin_main (nom, description, auteur, image, fichier, date_installation, md5_zip, version, typelicence )
SELECT '".addslashes($key)."', '".addslashes($line2['Description'])."','".addslashes($line2['Contributor'])."','".addslashes($line2['Picture'])."','".addslashes($line2["URL"])."','".$date->format('Y-m-d')."','".addslashes($line2['MD5'])."','".addslashes($key2)."','".addslashes($line2['LicenceType'])."'";
                $res = $db->sql_query($Query);
            endforeach;
        endforeach;
    }

    public function install($param)
    {

    }

    public function installed()
    {
        $PMAPLUGINURL = "http://localhost/plugins/";
        $JSONURL      = $PMAPLUGINURL."extracted/plugin.json";

        if ($file = @file_get_contents($JSONURL)) {
            $Array = json_decode($file, true);
        } else {
            $Array = null;
        }
        $this->set('data', $Array);
    }

    public function toupdate()
    {
        $PMAPLUGINURL = "http://localhost/plugins/";
        $JSONURL      = $PMAPLUGINURL."extracted/plugin.json";

        if ($file = @file_get_contents($JSONURL)) {
            $Array = json_decode($file, true);
        } else {
            $Array = null;
        }
        $this->set('data', $Array);
    }

    public function import()
    {
        
    }
}