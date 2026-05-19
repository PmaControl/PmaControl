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
use App\Library\PluginSlot;
use App\Library\Security\PluginPackageIntegrity;
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

    const PLUGIN_SIGNATURE_PUBLIC_KEYS = array();

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

        // Pull every row up-front. Filtering by "newer than the latest
        // catalogue entry" used to happen via a `MAX(version)` SQL
        // subquery, but MySQL/MariaDB `MAX()` is a string max — once a
        // plugin shipped a v1.10.0 alongside v1.9.0 the subquery picked
        // 'v1.9.0' (because '9' > '1' lexicographically after the
        // second '1') and the Update button disappeared (#1314). The
        // filter is reapplied in PHP below using version_compare(),
        // which correctly orders dotted-numeric versions of arbitrary
        // segment width.
        $Query = "SELECT id, nom, COALESCE(title, '') AS title, description, auteur, image, fichier, date_installation, md5_zip, sha256_zip, signature_zip, version, type_licence, est_actif
                  FROM plugin_main";

        $filter = isset($param[0]) ? strtolower($param[0]) : '';
        if ($filter === 'installed') {
            $Query .= " WHERE est_actif = 1";
        }

        $res = $db->sql_query($Query);

        $plugins = array();
        while ($plugin = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $plugins[$plugin["nom"]][$plugin["version"]] = $plugin;
        }

        // "toupdate" view: keep only plugins where an active row is
        // strictly older than the catalogue max for that name. Done in
        // PHP so the version comparison uses version_compare() rather
        // than MariaDB's string compare.
        if ($filter === 'toupdate') {
            foreach ($plugins as $name => $versions) {
                $current = null;
                $latest = null;
                foreach ($versions as $v => $row) {
                    if ($latest === null || version_compare($v, $latest, '>')) {
                        $latest = $v;
                    }
                    if ((int)($row['est_actif'] ?? 0) === 1) {
                        $current = $v;
                    }
                }
                $isStale = $current !== null
                    && $latest !== null
                    && version_compare($current, $latest, '<');
                if (!$isStale) {
                    unset($plugins[$name]);
                }
            }
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

                $md5Zip = isset($line2['MD5']) ? $line2['MD5'] : (isset($line2['md5']) ? $line2['md5'] : '');
                $sha256Zip = isset($line2['SHA256']) ? $line2['SHA256'] : (isset($line2['sha256']) ? $line2['sha256'] : '');
                $signatureZip = isset($line2['Signature']) ? $line2['Signature'] : (isset($line2['signature']) ? $line2['signature'] : '');

                $title = isset($line2['Title']) ? (string)$line2['Title'] : '';

                if ($db->sql_num_rows($res) > 0) {
                    $Query = "UPDATE plugin_main SET title = '" . addslashes($title) . "', description = '" . addslashes($line2['Description']) . "', auteur = '" . addslashes($line2['Contributor']) . "', image = '" . addslashes($line2['Picture']) . "', fichier = '" . addslashes($line2["URL"]) . "', date_installation = '" . $date->format('Y-m-d') . "', md5_zip = '" . addslashes($md5Zip) . "', sha256_zip = CASE WHEN '" . addslashes($sha256Zip) . "' = '' AND sha256_zip <> '' THEN sha256_zip ELSE '" . addslashes($sha256Zip) . "' END, signature_zip = CASE WHEN '" . addslashes($signatureZip) . "' = '' AND COALESCE(signature_zip, '') <> '' THEN signature_zip ELSE '" . addslashes($signatureZip) . "' END, type_licence = '" . addslashes($line2['LicenceType']) . "' WHERE nom = '" . addslashes($key) . "' AND version = '" . addslashes($key2) . "'";
                    $db->sql_query($Query);
                } else {
                    $Query = "INSERT INTO plugin_main (nom, title, description, auteur, image, fichier, date_installation, md5_zip, sha256_zip, signature_zip, version, type_licence )
SELECT '" . addslashes($key) . "','" . addslashes($title) . "','" . addslashes($line2['Description']) . "','" . addslashes($line2['Contributor']) . "','" . addslashes($line2['Picture']) . "','" . addslashes($line2["URL"]) . "','" . $date->format('Y-m-d') . "','" . addslashes($md5Zip) . "','" . addslashes($sha256Zip) . "','" . addslashes($signatureZip) . "','" . addslashes($key2) . "','" . addslashes($line2['LicenceType']) . "'";
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

        // Snapshot the menu table before any nested-set mutation
        // (#1316). Backup file lands in tmp/plugin_menu_backup/.
        $this->backupMenu(Sgbd::sql(DB_DEFAULT), 'install-'.$pluginId);

        // Plugin archives and their extracted bundles live outside the
        // served webroot at PLUGIN_STORAGE_DIR (default /srv/www/<root>-plugin/).
        // Manifest copies still target the canonical pmacontrol tree.
        $LOCALPLUGIN = defined('PLUGIN_STORAGE_DIR') ? PLUGIN_STORAGE_DIR : (ROOT.DS.'plugins'.DS);
        $LOCALAPPLICATION = $_SERVER["DOCUMENT_ROOT"] . WWW_ROOT . "App/";

        $db = Sgbd::sql(DB_DEFAULT);

        $Query = "SELECT id, nom, description, auteur, image, fichier, date_installation, md5_zip, sha256_zip, signature_zip, version, type_licence, est_actif
        FROM plugin_main WHERE id = " . $pluginId;

        $res = $db->sql_query($Query);

        $plugin = array();
        if ($plugin = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $pluginDir = $LOCALPLUGIN . $plugin["nom"];
            if (!is_dir($pluginDir) && !@mkdir($pluginDir, 0755, true) && !is_dir($pluginDir)) {
                Throw new \Exception(
                    "Cannot create plugin storage directory ".$pluginDir.". "
                    ."Run: sudo mkdir -p ".$LOCALPLUGIN." && sudo chown -R www-data:www-data ".$LOCALPLUGIN
                );
            }

            $archiveExtension = PluginPackage::archiveExtensionFromUrl($plugin["fichier"]);
            $archivePath = $LOCALPLUGIN . $plugin["nom"] . "/" . $plugin["version"] . "." . $archiveExtension;
            $this->downloadPluginArchive($plugin["fichier"], $archivePath);

            PluginPackageIntegrity::ensureZipTrusted($archivePath, array(
                'md5' => isset($plugin['md5_zip']) ? $plugin['md5_zip'] : '',
                'sha256' => isset($plugin['sha256_zip']) ? $plugin['sha256_zip'] : '',
                'signature' => isset($plugin['signature_zip']) ? $plugin['signature_zip'] : '',
            ), self::trustedPluginSignaturePublicKeys());

            $extractedRoot = $LOCALPLUGIN . "extracted/";
            if (!is_dir($extractedRoot) && !@mkdir($extractedRoot, 0755, true) && !is_dir($extractedRoot)) {
                Throw new \Exception(
                    "Cannot create extraction directory ".$extractedRoot.". "
                    ."Run: sudo chown -R www-data:www-data ".$LOCALPLUGIN
                );
            }
            if ($archiveExtension === 'pmactrl') {
                PluginPackage::extractPmactrl($archivePath, $extractedRoot);
            } else {
                $zip = new \ZipArchive;
                $res = $zip->open($archivePath);
                if ($res === TRUE) {
                    $this->assertZipArchiveIsSafe($zip);
                    $zip->extractTo($extractedRoot);
                    $zip->close();
                } else {
                    Throw new \Exception(
                        "Cannot open ".$archivePath." as a ZIP archive (libzip error ".$res.") — "
                        ."verify PHP ZipArchive extension is loaded (php -m | grep zip) "
                        ."and that the file is readable by www-data."
                    );
                }
            }
        } else {
            Throw new \Exception("Error while loading plugin in database");
        }

        $manifest = null;

        $ThisPluginDirectory = $LOCALPLUGIN . "extracted/" . $plugin["nom"] . "-" . substr($plugin["version"], 1);
        $manifestFile = $ThisPluginDirectory . "/plugin.json";

        if (is_file($manifestFile)) {
            $manifest = PluginPackage::load($ThisPluginDirectory);

            // Recover from a stale layout (interrupted install, manual
            // copy left over, double-clicked Install, ...). Walk the
            // manifest's declared destinations, list what already
            // exists, then mechanically uninstall ONLY those manifest
            // entries plus the plugin's DB rows. The next attempt
            // re-runs from a clean slate. If the second attempt still
            // collides, surface every conflicting path so the admin
            // can finish the cleanup by hand instead of staring at a
            // PHP trace.
            $collisions = PluginPackage::detectCollisions($manifest, ROOT);
            if (!empty($collisions)) {
                $this->cleanupStaleInstall($pluginId, $manifest);
                $remaining = PluginPackage::detectCollisions($manifest, ROOT);
                if (!empty($remaining)) {
                    Throw new \Exception(
                        "Plugin install aborted — the following paths exist on disk and were not "
                        ."created by the plugin's manifest (manual changes? other plugin? race?):\n  - "
                        .implode("\n  - ", $remaining)."\n\n"
                        ."Resolve the conflict and retry, or contact the administrator. To force "
                        ."the install after backing up the listed files, remove them manually:\n  "
                        ."sudo rm -f ".implode(' ', $remaining)
                    );
                }
            }

            $plan = PluginPackage::install($manifest, $ThisPluginDirectory, ROOT);

            foreach ($plan['files'] as $file) {
                $this->logpluginfilepath($file['destination'], $pluginId);
            }

            foreach ($plan['extensions'] as $extension) {
                if ($extension['kind'] === 'partial') {
                    $this->logpluginfilepath($extension['payload'], $pluginId);
                }
                PluginSlot::register(
                    $pluginId,
                    $extension['slot'],
                    $extension['kind'],
                    $extension['payload'],
                    $extension['order']
                );
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
                // #1316 — skip-if-exists guard. The plugin_menu table
                // didn't carry a UNIQUE (id_plugin_main, url) index
                // historically (and won't on older deployments), so a
                // re-install accumulated duplicate rows which then made
                // removeCore's Tree::delete loop crash on the second
                // pass. Don't insert if (plugin, url) is already there.
                $escapedUrl = $db->sql_real_escape_string($value["url"]);
                $sql = "INSERT INTO plugin_menu (id_plugin_main, url)
                        SELECT " . $pluginId . ", '" . $escapedUrl . "' FROM DUAL
                        WHERE NOT EXISTS (
                          SELECT 1 FROM plugin_menu
                          WHERE id_plugin_main = " . $pluginId . "
                            AND url = '" . $escapedUrl . "'
                        );";
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
 * Return trusted Ed25519 public keys for plugin package signatures.
 *
 * @return array<string,string>|array<int,string>
 */
    public static function trustedPluginSignaturePublicKeys()
    {
        $keys = self::PLUGIN_SIGNATURE_PUBLIC_KEYS;
        $envKeys = getenv('PMACONTROL_PLUGIN_SIGNATURE_PUBLIC_KEYS');

        if (is_string($envKeys) && trim($envKeys) !== '') {
            $decoded = json_decode($envKeys, true);
            if (is_array($decoded)) {
                foreach ($decoded as $keyId => $publicKey) {
                    if (is_string($publicKey) && trim($publicKey) !== '') {
                        $keys[$keyId] = trim($publicKey);
                    }
                }
            } else {
                foreach (preg_split('/[\s,]+/', trim($envKeys)) as $publicKey) {
                    if ($publicKey !== '') {
                        $keys[] = $publicKey;
                    }
                }
            }
        }

        return $keys;
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
        if ($sql === false || trim($sql) === '') {
            return;
        }

        if (!$db->sql_multi_query($sql)) {
            Throw new \Exception("Plugin SQL failed in ".$filename.": ".$db->sql_error());
        }

        while (true) {
            $result = $db->sql_store_result();
            if ($result) {
                $db->sql_free_result($result);
            }

            $error = $db->sql_error();
            if ($error) {
                Throw new \Exception("Plugin SQL failed in ".$filename.": ".$error);
            }

            if (!$db->sql_more_results()) {
                break;
            }

            if (!$db->sql_next_result()) {
                $error = $db->sql_error();
                Throw new \Exception("Plugin SQL failed in ".$filename.($error ? ": ".$error : ''));
            }
        }
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
        $this->removeCore((int)$param[0]);
        $this->redirectTo(SafeRedirect::refererOrFallback($_SERVER, LINK.'plugin/index'));
    }

/**
 * In-place upgrade: uninstall the currently active version of a
 * plugin, then install the catalog row whose id is passed as the
 * route parameter. The catalog view links here when the latest known
 * version is newer than the installed one (`_updateAvailable` in
 * App/view/Plugin/index.view.php).
 *
 * Closes #1297.
 */
    public function update($param) {
        if (!isset($param[0])) {
            Throw new \Exception("No plugin Id provided");
        }
        $newId = (int)$param[0];

        $db = Sgbd::sql(DB_DEFAULT);
        $res = $db->sql_query("SELECT nom FROM plugin_main WHERE id = ".$newId);
        $target = $res ? $db->sql_fetch_array($res, MYSQLI_ASSOC) : null;
        if (!is_array($target)) {
            Throw new \Exception("Error while loading plugin in database");
        }

        // Uninstall every currently-active version of the same plugin
        // (defensive — there should only be one), then chain into the
        // existing install flow which handles archive download, signature
        // verification, preflight, copy, SQL install, menu install, ACL
        // reset, and the final redirect.
        $name = (string)$target['nom'];
        $res = $db->sql_query(
            "SELECT id FROM plugin_main WHERE nom = '".$db->sql_real_escape_string($name)
            ."' AND est_actif = 1 AND id <> ".$newId
        );
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $this->removeCore((int)$row['id']);
        }

        $this->install(array($newId));
    }

/**
 * Uninstall a plugin version without redirecting. Shared by remove()
 * (thin wrapper that adds the redirect) and update() (chains into
 * install() after this).
 */
    private function removeCore($pluginId)
    {
        $db = Sgbd::sql(DB_DEFAULT);

        // Snapshot the menu table before mutating the nested-set
        // (#1316). If anything corrupts bg/bd mid-flight, the operator
        // can roll back with `mysql pmacontrol < <backup-file>` instead
        // of hand-rebuilding the tree.
        $this->backupMenu($db, 'remove-'.$pluginId);

        //On charge le bon menu pour pouvoir l'administrer
        $Query = "SELECT group_id, id, url FROM menu WHERE title = 'Plugins'";
        $res = $db->sql_query($Query);

        $ids = array();
        $ids = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        $tree = new TreeInterval($db, "menu", array("id_parent" => "parent_id"), array("group_id" => $ids["group_id"]));

        // #1316 — deduplicate menu ids. plugin_menu can carry
        // duplicate rows for the same URL when an install ran twice
        // (each call appends without an ON DUPLICATE guard). The old
        // loop called Tree::delete once per plugin_menu row → the
        // second call hit a missing menu row and crashed on $ob->bg.
        // Collect distinct ids first, then iterate.
        $Query = "SELECT DISTINCT menu.id AS menuid
                  FROM plugin_menu
                  INNER JOIN menu ON menu.url = plugin_menu.url
                  WHERE plugin_menu.id_plugin_main = " . $pluginId;
        $res = $db->sql_query($Query);
        $menuIds = array();
        while ($r = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $menuIds[] = (int)$r['menuid'];
        }
        foreach ($menuIds as $menuId) {
            $tree->delete($menuId);   // Tree::delete is now idempotent (#1316)
        }
        // Single bulk wipe of every plugin_menu row for this plugin,
        // duplicates included — no per-iteration DELETE that risks
        // half-finishing if anything throws.
        $db->sql_query("DELETE FROM plugin_menu WHERE id_plugin_main = " . $pluginId);

        $Query = "SELECT nom, version FROM plugin_main WHERE id = " . $pluginId;
        $res = $db->sql_query($Query);

        $plugin = array();
        $plugin = $db->sql_fetch_array($res, MYSQLI_ASSOC);
        if (!is_array($plugin)) {
            Throw new \Exception("Error while loading plugin in database");
        }

        $LOCALPLUGIN = defined('PLUGIN_STORAGE_DIR') ? PLUGIN_STORAGE_DIR : (ROOT.DS.'plugins'.DS);
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

            PluginPackage::removeExtensionPartials($manifest, ROOT);
            PluginSlot::unregisterAll($pluginId);

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
    }

/**
 * Snapshot the `menu` table to a SQL file before any destructive
 * tree mutation (#1316). Cheap insurance against nested-set bg/bd
 * corruption — if anything goes wrong, the operator can roll back
 * with `mysql pmacontrol < <returned-file>`.
 *
 * Stores backups in `tmp/plugin_menu_backup/menu-YYYYMMDD-HHMMSS-<reason>.sql`.
 * Rotates older backups out so the directory never carries more than
 * 20 files (~200 KB total at ~10 KB per dump). Surfaces non-fatal: a
 * failed backup logs a warning and lets the install/remove continue —
 * a backup nobody can write is worse than no backup but not worth
 * blocking the user's operation.
 *
 * @return string|null Path of the backup file, or null on failure.
 */
    private function backupMenu($db, $reason)
    {
        try {
            $dir = (defined('TMP') ? TMP : (ROOT.DS.'tmp'.DS)).'plugin_menu_backup';
            if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                return null;
            }

            $stamp = date('Ymd-His');
            $safe  = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$reason);
            $file  = $dir.DIRECTORY_SEPARATOR.'menu-'.$stamp.'-'.$safe.'.sql';

            $fp = @fopen($file, 'w');
            if ($fp === false) {
                return null;
            }

            fwrite($fp, "-- plugin_menu_backup\n-- created: ".date('c')."\n-- reason: ".$reason."\n\n");
            fwrite($fp, "-- Restore with: mysql <db> < ".basename($file)."\n");
            fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            foreach (array('menu', 'plugin_menu') as $table) {
                $res = $db->sql_query("SELECT * FROM `".$table."`");
                if (!$res) continue;

                $cols = null;
                fwrite($fp, "DELETE FROM `".$table."`;\n");
                while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                    if ($cols === null) {
                        $cols = array_keys($row);
                    }
                    $values = array();
                    foreach ($cols as $c) {
                        $v = $row[$c];
                        if ($v === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = "'".$db->sql_real_escape_string((string)$v)."'";
                        }
                    }
                    fwrite($fp, "INSERT INTO `".$table."` (`"
                        .implode('`, `', $cols)."`) VALUES ("
                        .implode(', ', $values).");\n");
                }
                fwrite($fp, "\n");
            }
            fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($fp);

            $this->pruneOldMenuBackups($dir, 20);
            return $file;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function pruneOldMenuBackups($dir, $keep)
    {
        $files = @glob($dir.DIRECTORY_SEPARATOR.'menu-*.sql');
        if (!is_array($files) || count($files) <= $keep) {
            return;
        }
        // Oldest first by mtime, drop everything beyond the keep limit.
        usort($files, function ($a, $b) { return filemtime($a) - filemtime($b); });
        foreach (array_slice($files, 0, count($files) - $keep) as $old) {
            @unlink($old);
        }
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

    /**
     * Best-effort recovery from a half-installed plugin. Removes the
     * destinations declared by `$manifest` and purges the plugin's
     * extension / menu / file rows so the next install pass can start
     * from a clean slate. Intentionally avoids touching anything not
     * referenced by the manifest — destinations created out-of-band
     * (manual edits, other plugins) are NEVER deleted here.
     */
    private function cleanupStaleInstall($pluginId, array $manifest)
    {
        if (class_exists('\App\Library\PluginSlot')) {
            \App\Library\PluginSlot::unregisterAll($pluginId);
        }
        PluginPackage::removeExtensionPartials($manifest, ROOT);
        PluginPackage::removeFiles($manifest, ROOT);

        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_query("DELETE FROM plugin_menu WHERE id_plugin_main = ".(int)$pluginId);
        $db->sql_query("DELETE FROM plugin_file WHERE id_plugin_main = ".(int)$pluginId);
    }

    private function downloadPluginArchive($sourceUrl, $destination)
    {
        $input = @fopen($sourceUrl, 'rb');
        if ($input === false) {
            Throw new \Exception("Cannot open source URL ".$sourceUrl." — check network access, plugin_main.fichier, and allow_url_fopen.");
        }

        $output = @fopen($destination, 'wb');
        if ($output === false) {
            fclose($input);
            $dir = dirname($destination);
            Throw new \Exception(
                "Cannot write plugin archive to ".$destination.". "
                ."Verify the directory ".$dir." exists and is writable by www-data "
                ."(sudo chown -R www-data:www-data ".$dir.")."
            );
        }

        try {
            while (!feof($input)) {
                $chunk = fread($input, 8192);
                if ($chunk === false) {
                    Throw new \Exception("Echec lors de la lecture de l'archive plugin");
                }
                if ($chunk !== '' && fwrite($output, $chunk) === false) {
                    Throw new \Exception("Echec lors de l'écriture de l'archive plugin");
                }
            }
        } finally {
            fclose($input);
            fclose($output);
        }
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
