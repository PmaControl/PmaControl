<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class Plugin extends Controller
{

    public function index($param)
    {
        $LOCALJSONFILE = "/data/www/pmacontrol/plugins/plugin.json";
        $PMAPLUGINURL  = "http://localhost/plugins/";
        $JSONURL       = $PMAPLUGINURL."extracted/plugin.json";

        if ((!file_exists($LOCALJSONFILE)) || (filectime($LOCALJSONFILE) < date_timestamp_get(date_create('-1 day')))) {
            if ((file_exists($JSONURL)) && ($file = file_get_contents($JSONURL))) {
                $Array = json_decode($file, true);
                $this->jsontodatabase($file);

                unlink($LOCALJSONFILE);
                $fp = fopen($LOCALJSONFILE, 'w');
                fwrite($fp, $file);
                fclose($fp);
            }
        }

        $db = $this->di['db']->sql(DB_DEFAULT);

        $Query = "SELECT id, nom, description, auteur, image, fichier, date_installation, md5_zip, version, type_licence, est_actif, maxversion
        FROM plugin_main ";
        $Query .= " INNER JOIN"
            ." (SELECT nom AS tempnom, MAX(version) AS maxversion FROM plugin_main GrOUP BY nom)"
            ." AS Temp ON plugin_main.nom = Temp.tempnom";

        if (isset($param[0])) {
            if (strtolower($param[0]) == 'installed') {
                $Query .= " WHERE est_actif = 1";
            }
            if (strtolower($param[0]) == 'toupdate') {
                $Query .= " WHERE plugin_main.version < Temp.maxversion"
                    ." AND plugin_main.est_actif = 1";
            }
        }

        $res = $db->sql_query($Query);

        $plugins = array();
        while ($plugin  = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $plugins[$plugin["nom"]][$plugin["version"]] = $plugin;
        }

        $this->set('data', $plugins);
        $this->set('param', $param);
    }

    public function jsontodatabase($jsonInText)
    {
        $Array = json_decode($jsonInText, true);

        $db = $this->di['db']->sql(DB_DEFAULT);

        foreach ($Array as $key => $line):

            ksort($line);

            foreach ($line as $key2 => $line2):

                $date  = DateTime::createFromFormat('d/m/Y', $line2['CreationDate']);
                $Query = "REPLACE INTO plugin_main (nom, description, auteur, image, fichier, date_installation, md5_zip, version, type_licence )
SELECT '".addslashes($key)."', '".addslashes($line2['Description'])."','".addslashes($line2['Contributor'])."','".addslashes($line2['Picture'])."','".addslashes($line2["URL"])."','".$date->format('Y-m-d')."','".addslashes($line2['MD5'])."','".addslashes($key2)."','".addslashes($line2['LicenceType'])."'";
                $res   = $db->sql_query($Query);
            endforeach;
        endforeach;
    }

    public function install($param)
    {
        if (!isset($param[0])) {
            Throw new \Exception("No plugin Id provided");
        }

        $LOCALPLUGIN      = "/data/www/pmacontrol/plugins/";
        $LOCALAPPLICATION = "/data/www/pmacontrol/application/";

        $db = $this->di['db']->sql(DB_DEFAULT);

        $Query = "SELECT id, nom, description, auteur, image, fichier, date_installation, md5_zip, version, type_licence, est_actif
        FROM plugin_main WHERE id = ".$param[0];

        $res = $db->sql_query($Query);

        $plugin = array();
        if ($plugin = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            //Téléchargement du fichier
            if (!is_dir($LOCALPLUGIN.$plugin["nom"])) {
                //Directory does not exist, so lets create it.
                mkdir($LOCALPLUGIN.$plugin["nom"], 0755, true);
            }

            $handle  = fopen($plugin["fichier"], "r");
            $handle2 = fopen($LOCALPLUGIN.$plugin["nom"]."/".$plugin["version"].".zip", "w");
            if (FALSE === $handle) {
                Throw new \Exception("Echec lors de l'ouverture du flux vers l'URL");
            }

            while (!feof($handle)) {
                fwrite($handle2, fread($handle, 8192));
            }
            fclose($handle);
            fclose($handle2);

            $zip = new ZipArchive;
            $res = $zip->open($LOCALPLUGIN.$plugin["nom"]."/".$plugin["version"].".zip");
            if ($res === TRUE) {
                $zip->extractTo($LOCALPLUGIN."extracted/");
                $zip->close();
            } else {
                Throw new \Exception("Check your ZIP PHP extension or directory right", 99);
            }
        } else {
            Throw new \Exception("Error while loading plugin in database");
        }


        $ThisPluginDirectory = $LOCALPLUGIN."extracted/".$plugin["nom"]."-".substr($plugin["version"], 1);
        $scanned_directory   = array_diff(scandir($ThisPluginDirectory), array('..', '.', '.gitmodules', 'sql', 'install.php', 'uninstall.php', 'README.md', 'image.jpg'));

        $Retour = true;
        foreach ($scanned_directory AS $value) {
            /*
            if (copyfile($handle, $source, $target) === false)
            {
                $Retour = false;
            }
            */
        }

        if ($Retour === false)
        {
            Throw new \Exception("Error while copying plugin files");
        }
    }

    public function copyfile($handle, $source, $target)
    {
        if (is_dir($source."/".$handle)) {
            if (!file_exists($target."/".$handle)) {
                mkdir($target."/".$handle);
            }

            $scanned_directory = array_diff(scandir($source."/".$handle), array('..', '.', '.gitmodules', 'sql', 'install.php', 'uninstall.php', 'README.md', 'image.jpg'));

            foreach ($scanned_directory AS $value) {
                $Retour = copyfile($value, $source."/".$handle, $target."/".$handle);
            }
        } else {
            if (!file_exists($target."/".$handle)) {
                return copy($source."/".$handle, $target."/".$handle);
            } else {
                return false;
            }
        }

        if ($Retour === false) {
            //if false on efface
        }
        
    }
}