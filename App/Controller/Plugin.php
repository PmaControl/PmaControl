<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Tree as TreeInterval;
use App\Library\PluginPackage;
use App\Library\Security\SafeRedirect;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for plugin workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Plugin extends Controller {

/**
 * Render plugin state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/plugin/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param) {
        $LOCALJSONFILE = ROOT . "/plugins/plugin.json";

        /*
        $PMAPLUGINURL = "http://localhost/plugins/"; //Il faut mettre dans un fichier de conf
        $JSONURL = $PMAPLUGINURL . "extracted/plugin.json";
        if ((!file_exists($LOCALJSONFILE)) || (filectime($LOCALJSONFILE) < date_timestamp_get(date_create('-1 day')))) {
            if ($file = file_get_contents($JSONURL)) {
                $Array = json_decode($file, true);
                $this->jsontodatabase($file);

                $fp = fopen($LOCALJSONFILE, 'w');
                fwrite($fp, $file);
                fclose($fp);
            }
        }*/
        if (file_exists($LOCALJSONFILE))
        {
            $file = file_get_contents($LOCALJSONFILE);
            $this->jsontodatabase($file);

        }


        $db = Sgbd::sql(DB_DEFAULT);

        $Query = "SELECT id, nom, description, auteur, image, fichier, date_installation, md5_zip, version, type_licence, est_actif, maxversion
        FROM plugin_main ";
        $Query .= " INNER JOIN"
                . " (SELECT nom AS tempnom, MAX(version) AS maxversion FROM plugin_main GROUP BY nom)"
                . " AS Temp ON plugin_main.nom = Temp.tempnom";

        if (isset($param[0])) {
            if (strtolower($param[0]) == 'installed') {
                $Query .= " WHERE est_actif = 1";
            }
            if (strtolower($param[0]) == 'toupdate') {
                $Query .= " WHERE plugin_main.version < Temp.maxversion"
                        . " AND plugin_main.est_actif = 1";
            }
        }

        $res = $db->sql_query($Query);

        $plugins = array();
        while ($plugin = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $plugins[$plugin["nom"]][$plugin["version"]] = $plugin;
        }

        $this->set('data', $plugins);
        $this->set('param', $param);
    }

/**
 * Handle plugin state through `jsontodatabase`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $jsonInText Input value for `jsonInText`.
 * @phpstan-param mixed $jsonInText
 * @psalm-param mixed $jsonInText
 * @return void Returned value for jsontodatabase.
 * @phpstan-return void
 * @psalm-return void
 * @see self::jsontodatabase()
 * @example /fr/plugin/jsontodatabase
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function jsontodatabase($jsonInText) {
        $Array = json_decode($jsonInText, true);

        $db = Sgbd::sql(DB_DEFAULT);

        foreach ($Array as $key => $line):

            ksort($line);

            foreach ($line as $key2 => $line2):

                $date = \DateTime::createFromFormat('d/m/Y', $line2['CreationDate']);

                $Query = "SELECT * FROM plugin_main WHERE nom = '" . addslashes($key) . "' AND version = '" . addslashes($key2) . "'";
                $res = $db->sql_query($Query);

                if ($db->sql_num_rows($res) > 0) {
                    $Query = "UPDATE plugin_main SET description = '" . addslashes($line2['Description']) . "', auteur = '" . addslashes($line2['Contributor']) . "', image = '" . addslashes($line2['Picture']) . "', fichier = '" . addslashes($line2["URL"]) . "', date_installation = '" . $date->format('Y-m-d') . "', md5_zip = '" . addslashes($line2['MD5']) . "', type_licence = '" . addslashes($line2['LicenceType']) . "' WHERE nom = '" . addslashes($key) . "' AND version = '" . addslashes($key2) . "'";
                    $db->sql_query($Query);
                } else {
                    $Query = "INSERT INTO plugin_main (nom, description, auteur, image, fichier, date_installation, md5_zip, version, type_licence )
SELECT '" . addslashes($key) . "', '" . addslashes($line2['Description']) . "','" . addslashes($line2['Contributor']) . "','" . addslashes($line2['Picture']) . "','" . addslashes($line2["URL"]) . "','" . $date->format('Y-m-d') . "','" . addslashes($line2['MD5']) . "','" . addslashes($key2) . "','" . addslashes($line2['LicenceType']) . "'";
                    $db->sql_query($Query);
                }
            endforeach;
        endforeach;
    }

/**
 * Handle plugin state through `install`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for install.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::install()
 * @example /fr/plugin/install
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function install($param) {
        if (!isset($param[0])) {
            Throw new \Exception("No plugin Id provided");
        }
        $pluginId = (int)$param[0];

        $LOCALPLUGIN = $_SERVER["DOCUMENT_ROOT"] . WWW_ROOT . "plugins/";
        $LOCALAPPLICATION = $_SERVER["DOCUMENT_ROOT"] . WWW_ROOT . "App/";

        $db = Sgbd::sql(DB_DEFAULT);

        $Query = "SELECT id, nom, description, auteur, image, fichier, date_installation, md5_zip, version, type_licence, est_actif
        FROM plugin_main WHERE id = " . $pluginId;

        $res = $db->sql_query($Query);

        $plugin = array();
        if ($plugin = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            //Téléchargement du fichier
            if (!is_dir($LOCALPLUGIN . $plugin["nom"])) {
                //Directory does not exist, so lets create it.
                mkdir($LOCALPLUGIN . $plugin["nom"], 0755, true);
            }

            $zipPath = $LOCALPLUGIN . $plugin["nom"] . "/" . $plugin["version"] . ".zip";
            $handle = fopen($plugin["fichier"], "r");
            $handle2 = fopen($zipPath, "w");
            if (FALSE === $handle) {
                Throw new \Exception("Echec lors de l'ouverture du flux vers l'URL");
            }
            if (FALSE === $handle2) {
                Throw new \Exception("Echec lors de l'ouverture du fichier ZIP local");
            }

            while (!feof($handle)) {
                fwrite($handle2, fread($handle, 8192));
            }
            fclose($handle);
            fclose($handle2);

            if (!empty($plugin['md5_zip']) && md5_file($zipPath) !== $plugin['md5_zip']) {
                Throw new \Exception("Plugin ZIP checksum mismatch");
            }

            $zip = new \ZipArchive;
            $res = $zip->open($zipPath);
            if ($res === TRUE) {
                $this->assertZipArchiveIsSafe($zip);
                $zip->extractTo($LOCALPLUGIN . "extracted/");
                $zip->close();
            } else {
                Throw new \Exception("Check your ZIP PHP extension or directory right", 99);
            }
        } else {
            Throw new \Exception("Error while loading plugin in database");
        }

        $manifest = null;

        $ThisPluginDirectory = $LOCALPLUGIN . "extracted/" . $plugin["nom"] . "-" . substr($plugin["version"], 1);
        $manifestFile = $ThisPluginDirectory . "/plugin.json";

        if (is_file($manifestFile)) {
            $manifest = PluginPackage::load($ThisPluginDirectory);
            $plan = PluginPackage::install($manifest, $ThisPluginDirectory, ROOT);

            foreach ($plan['files'] as $file) {
                $this->logpluginfilepath($file['destination'], $pluginId);
            }

            foreach ($plan['sql'] as $sqlFile) {
                $this->sqlexecute($sqlFile);
            }

            foreach ($plan['scripts'] as $scriptFile) {
                PluginPackage::runScript($scriptFile);
            }
        } else {
            //Copy Plugin ungeneric files
            $scanned_directory = array_diff(scandir($ThisPluginDirectory), array('..', '.', '.gitmodules', 'sql', 'install.php', 'upgrade.php', 'uninstall.php', 'README.md', 'image.jpg'));

            $Return = array();
            $Return[0] = true;

            $source = $ThisPluginDirectory . "/";
            $target = $LOCALAPPLICATION;

            foreach ($scanned_directory AS $value) {
                if ($Return[0] == true) {
                    $Return = $this->copyfile($value, $source, $target);
                }
            }

            if ($Return[0] == false) {
                Throw new \Exception("Error while copying plugin files : " . $Return[1]);
            }

            //Log plugin file to database
            foreach ($scanned_directory AS $value) {
                $this->logpluginfile($value, $pluginId, $source, $target);
            }

            //Installation INSTALL.SQL
            $installSql = $this->findSqlScript($ThisPluginDirectory, 'install.sql');
            if ($installSql !== null) {
                $this->sqlexecute($installSql);
            }
        }

        $Query = "SELECT group_id, id FROM menu WHERE title = 'Plugins'";
        $res = $db->sql_query($Query);

        $ids = array();
        $ids = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $ids["group_id"]));

        $lastinstallmenu = LINK.'plugin/index';
        foreach ($this->loadPluginMenu($ThisPluginDirectory, 'install.php', 'install', 'menu_install') as $value) {
                $tree->add($value, $ids["id"]);

                //On met en base le fait que le plugin est installé.
                $sql = "INSERT INTO plugin_menu (id_plugin_main, url) SELECT " . $pluginId . ", '" . $db->sql_real_escape_string($value["url"]) . "';";
                $db->sql_query($sql);

                $lastinstallmenu = $value["url"];
        }

        //On met en base le fait que le plugin est installé.
        $sql = "UPDATE plugin_main SET est_actif = 1 WHERE id = " . $pluginId . ";";
        $db->sql_query($sql);
        $this->clearAclCache();

        $this->redirectTo(str_replace("{LINK}", LINK, $lastinstallmenu));
    }

/**
 * Handle plugin state through `copyfile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $file Input value for `file`.
 * @phpstan-param mixed $file
 * @psalm-param mixed $file
 * @param mixed $source Input value for `source`.
 * @phpstan-param mixed $source
 * @psalm-param mixed $source
 * @param mixed $target Input value for `target`.
 * @phpstan-param mixed $target
 * @psalm-param mixed $target
 * @param mixed $nest Input value for `nest`.
 * @phpstan-param mixed $nest
 * @psalm-param mixed $nest
 * @return mixed Returned value for copyfile.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::copyfile()
 * @example /fr/plugin/copyfile
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function copyfile($file, $source, $target, $nest = 1) {
        $Return = array();
        $Return[0] = true;
        $Mode = "";

        if (is_dir($source . $file)) {
            //echo $nest." DIR ".$source.$file." -> ".$target.$file."<br>";
            //Fonctionnement si répertoire
            if (!file_exists($target . $file)) {
                mkdir($target . $file);
                $Mode = "DirMaking";
            }

            //On scan l'archive pour faire les copy de fichier.
            $scanned_directory = array_diff(scandir($source . $file), array('..', '.', '.gitmodules', 'sql', 'install.php', 'uninstall.php', 'README.md', 'image.jpg'));

            foreach ($scanned_directory AS $value) {
                if ($Return[0] == true) {
                    $Return = $this->copyfile($value, $source . $file . "/", $target . $file . "/", $nest + 1);
                }
            }
        } else {
            //echo $nest." FILE ".$source.$file." -> ".$target.$file."<br>";
            //Fonction si fichier
            if (!file_exists($target . $file)) {
                if (false === copy($source . $file, $target . $file)) {
                    $Return[0] = false;
                    $Return[1] = "Abort : error during copy of " . $target . $file;
                } else {
                    $Mode = "FileCoping";
                }
            } else {
                $Return[0] = false;
                $Return[1] = "Abort : a file already exists " . $target . $file;
            }
        }

        if ($Return[0] == false) {
            //if false on efface si on a ajouter
            if ($Mode == "DirMaking") {
                rmdir($target . $file);
            }
            if ($Mode == "FileCoping") {
                unlink($target . $file);
            }
        }

        return $Return;
    }

/**
 * Handle plugin state through `logpluginfile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $handle Input value for `handle`.
 * @phpstan-param mixed $handle
 * @psalm-param mixed $handle
 * @param mixed $pluginid Input value for `pluginid`.
 * @phpstan-param mixed $pluginid
 * @psalm-param mixed $pluginid
 * @param mixed $source Input value for `source`.
 * @phpstan-param mixed $source
 * @psalm-param mixed $source
 * @param mixed $target Input value for `target`.
 * @phpstan-param mixed $target
 * @psalm-param mixed $target
 * @return void Returned value for logpluginfile.
 * @phpstan-return void
 * @psalm-return void
 * @see self::logpluginfile()
 * @example /fr/plugin/logpluginfile
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function logpluginfile($handle, $pluginid, $source, $target) {
        $db = Sgbd::sql(DB_DEFAULT);

        if (is_dir($source . "/" . $handle)) {
            //Fonctionnement si répertoire
            //On scan l'archive pour faire les inserts dans la date de données de fichier.
            $scanned_directory = array_diff(scandir($source . "/" . $handle), array('..', '.', '.gitmodules', 'sql', 'install.php', 'uninstall.php', 'README.md', 'image.jpg'));

            foreach ($scanned_directory AS $value) {
                $this->logpluginfile($value, $pluginid, $source . $handle . "/", $target . $handle . "/");
            }
        } else {
            //Fonctionnement si fichier
            $sql = "REPLACE INTO plugin_file (id_plugin_main, file, md5) SELECT " . (int)$pluginid . ", '" . $db->sql_real_escape_string($target . $handle) . "', '" . md5_file($target . $handle) . "'";
            $db->sql_query($sql);
        }
    }

    public function logpluginfilepath($path, $pluginid) {
        $db = Sgbd::sql(DB_DEFAULT);

        if (is_file($path)) {
            $sql = "REPLACE INTO plugin_file (id_plugin_main, file, md5) SELECT " . (int)$pluginid . ", '" . $db->sql_real_escape_string($path) . "', '" . md5_file($path) . "'";
            $db->sql_query($sql);
        }
    }

/**
 * Handle plugin state through `sqlexecute`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $filename Input value for `filename`.
 * @phpstan-param mixed $filename
 * @psalm-param mixed $filename
 * @return void Returned value for sqlexecute.
 * @phpstan-return void
 * @psalm-return void
 * @see self::sqlexecute()
 * @example /fr/plugin/sqlexecute
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function sqlexecute($filename) {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = file_get_contents($filename);
        $db->sql_query($sql);
    }

/**
 * Delete plugin state through `remove`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for remove.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::remove()
 * @example /fr/plugin/remove
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function remove($param) {
        if (!isset($param[0])) {
            Throw new \Exception("No plugin Id provided");
        }
        $pluginId = (int)$param[0];

        $db = Sgbd::sql(DB_DEFAULT);

        //On charge le bon menu pour pouvoir l'administrer
        $Query = "SELECT group_id, id, url FROM menu WHERE title = 'Plugins'";
        $res = $db->sql_query($Query);

        $ids = array();
        $ids = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $ids["group_id"]));

        //On charge les entrées menus à retirer
        $Query = "SELECT menu.id AS menuid, plugin_menu.id AS pluginmenuid FROM plugin_menu INNER JOIN menu ON menu.url = plugin_menu.url WHERE plugin_menu.id_plugin_main = " . $pluginId;
        $res = $db->sql_query($Query);

        $menu = array();
        while ($menu = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            //On efface le menu
            $tree->delete($menu["menuid"]);
            // On purge le menu effacé de la base de données.
            $QueryDelete = "DELETE FROM plugin_menu WHERE id = " . $menu["pluginmenuid"];
            $db->sql_query($QueryDelete);
        }

        $Query = "SELECT nom, version FROM plugin_main WHERE id = " . $pluginId;
        $res = $db->sql_query($Query);

        $plugin = array();
        $plugin = $db->sql_fetch_array($res, MYSQLI_ASSOC);
        if (!is_array($plugin)) {
            Throw new \Exception("Error while loading plugin in database");
        }

        $LOCALPLUGIN = $_SERVER["DOCUMENT_ROOT"] . WWW_ROOT . "plugins/";
        $ThisPluginDirectory = $LOCALPLUGIN . "extracted/" . $plugin["nom"] . "-" . substr($plugin["version"], 1);
        $manifestFile = $ThisPluginDirectory . "/plugin.json";
        $this->loadPluginMenu($ThisPluginDirectory, 'uninstall.php', 'uninstall', 'menu_uninstall');

        if (is_file($manifestFile)) {
            $manifest = PluginPackage::load($ThisPluginDirectory);

            foreach (PluginPackage::phaseScripts($manifest, $ThisPluginDirectory, 'uninstall') as $scriptFile) {
                PluginPackage::runScript($scriptFile);
            }

            foreach (PluginPackage::phaseFiles($manifest, $ThisPluginDirectory, 'uninstall') as $sqlFile) {
                $this->sqlexecute($sqlFile);
            }

            PluginPackage::removeFiles($manifest, ROOT);

            $QueryDelete = "DELETE FROM plugin_file WHERE id_plugin_main = " . $pluginId;
            $db->sql_query($QueryDelete);
        } else {
            $Query = "SELECT id, file FROM plugin_file WHERE id_plugin_main = " . $pluginId;

            $res = $db->sql_query($Query);

            $pluginFile = array();
            while ($pluginFile = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                // On efface
                if (is_file($pluginFile["file"])) {
                    unlink($pluginFile["file"]);
                }
                // On purge le fichier effacé de la base de données.
                $QueryDelete = "DELETE FROM plugin_file WHERE id = " . $pluginFile["id"];
                $db->sql_query($QueryDelete);
            }

            //execution du script SQL de UNINSTALL.
            $uninstallSql = $this->findSqlScript($ThisPluginDirectory, 'uninstall.sql');
            if ($uninstallSql !== null) {
                $this->sqlexecute($uninstallSql);
            }
        }

        //On met en base le fait que le plugin est installé.
        $sql = "UPDATE plugin_main SET est_actif = 0 WHERE id = " . $pluginId . ";";
        $db->sql_query($sql);
        $this->clearAclCache();

        $this->redirectTo(SafeRedirect::refererOrFallback($_SERVER, LINK.'plugin/index'));
    }

    private function findSqlScript($pluginDirectory, $filename)
    {
        foreach (array('sql', 'SQL') as $directory) {
            foreach (array($filename, strtoupper($filename)) as $candidate) {
                $path = rtrim($pluginDirectory, '/').'/'.$directory.'/'.$candidate;
                if (is_file($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    private function loadPluginMenu($pluginDirectory, $fileName, $className, $methodName)
    {
        $script = rtrim($pluginDirectory, '/').'/'.$fileName;
        if (!is_file($script)) {
            return array();
        }

        include_once $script;
        if (!class_exists($className)) {
            return array();
        }

        $hook = new $className();
        if (!method_exists($hook, $methodName)) {
            return array();
        }

        return (array)$hook->$methodName();
    }

    private function assertZipArchiveIsSafe(\ZipArchive $zip)
    {
        $uncompressedSize = 0;
        $maxUncompressedSize = 100 * 1024 * 1024;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = str_replace('\\', '/', (string)$zip->getNameIndex($i));
            if ($name === '' || $name[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $name)) {
                Throw new \Exception("Unsafe plugin ZIP entry: ".$name);
            }

            $stat = $zip->statIndex($i);
            $uncompressedSize += (int)($stat['size'] ?? 0);
            if ($uncompressedSize > $maxUncompressedSize) {
                Throw new \Exception("Plugin ZIP is too large after extraction");
            }
        }
    }

    private function redirectTo($url)
    {
        header('Location: '.$url, true, 303);
        exit;
    }

    private function clearAclCache()
    {
        $aclCache = $_SERVER["DOCUMENT_ROOT"].WWW_ROOT."tmp/acl/acl.ser";
        if (is_file($aclCache)) {
            unlink($aclCache);
        }
    }

}
