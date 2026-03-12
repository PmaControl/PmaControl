<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use \App\Library\Mysql;
use \App\Library\Git;


/**
 * Class responsible for upgrade workflows.
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
class Upgrade extends Controller
{
    const PATH_PATCH = ROOT."/App/Patch/";

/**
 * Handle upgrade state through `now`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for now.
 * @phpstan-return void
 * @psalm-return void
 * @see self::now()
 * @example /fr/upgrade/now
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function now($param)
    {
        Debug::parseDebug($param);

        if ($this->needUpgrade($param))
        {

            // stop all daemon
            //git pull
            // composer update

            $id_version__before = $this->executePatch($param);

            if ($id_version__before !== false){

                $param[0] = $id_version__before;
                $this->setNewVersion($param);
            }

            $this->updateConfig($param);
            
            // start all daemon
        }   
    }

/**
 * Handle upgrade state through `setNewVersion`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for setNewVersion.
 * @phpstan-return void
 * @psalm-return void
 * @see self::setNewVersion()
 * @example /fr/upgrade/setNewVersion
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function setNewVersion($param)
    {
        Debug::parseDebug($param);

        $id_version__before = $param[0];

        $db = Sgbd::sql(DB_DEFAULT);
        $commit = Git::getCurrentCommit();

        $sql = "INSERT IGNORE INTO `version` (`date`,`version`,`build`,`comment`) 
        VALUES ('".$commit['date']."','".$commit['version']."','".$commit['build']."','".$commit['comment']."');";
        Debug::sql($sql);
        
        $db->sql_query($sql);
        $lastid = $db->sql_insert_id();
        
        $sql = "UPDATE `patch` SET id_version__after=".$lastid." WHERE id_version__before=".$id_version__before." AND id_version__after IS NULL";
        $db->sql_query($sql);

        Debug::debug($lastid, "LASt inserted id");
    }


/**
 * Handle upgrade state through `executePatch`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for executePatch.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::executePatch()
 * @example /fr/upgrade/executePatch
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function executePatch($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id,build from `version` ORDER BY id desc LIMIT 1";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
            $patchs = $this->getPatchFrom($ob->build);

            $success = true;
            foreach($patchs as $patch) {
                $log_error = Mysql::execute(1,$patch );
                if (! empty($log_error)){
                    $success = false;
                    echo "patch : $patch => FAILED !\n";
                    echo "$log_error\n";
                }
                else{
                    $log_error = "";
                    echo "patch : $patch => OK\n";
                }

                $file_name = pathinfo($patch)['basename'];

                $sql = "REPLACE INTO `patch` (`id_version__before`, `file`, `date_executed`, `error`)
                VALUES (".$ob->id.", '".$file_name."', '".date('Y-m-d H:i:s')."','".$db->sql_real_escape_string($log_error)."' );";

                $db->sql_query($sql);

                Debug::debug($log_error , "Log");
            }

            if ($success) {
                return $ob->id;
            }
            else{
                return false;
            }
        }
    }

/**
 * Retrieve upgrade state through `getPatchFrom`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $build Input value for `build`.
 * @phpstan-param mixed $build
 * @psalm-param mixed $build
 * @return mixed Returned value for getPatchFrom.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getPatchFrom()
 * @example /fr/upgrade/getPatchFrom
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getPatchFrom($build)
    {
        Debug::debug($build,"last build");
        $commits = Git::getNewCommit($build);
        $files_to_execute = array();

        foreach ($commits as $commit)
        {
            $file = self::PATH_PATCH.$commit.".sql";
            
            if (file_exists($file)) {
                $files_to_execute[] = $file;
            }
        }

        Debug::debug($files_to_execute, 'file');
        return $files_to_execute;
    }


/**
 * Handle upgrade state through `needUpgrade`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for needUpgrade.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::needUpgrade()
 * @example /fr/upgrade/needUpgrade
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function needUpgrade($param)
    {
        Debug::parseDebug($param);
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT id,build from `version` ORDER BY id desc LIMIT 1";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res))
        {
            $commit = Git::getCurrentCommit();

            if ($ob->build  === $commit['build']) {
                Debug::debug("false");
                return false;
            }
            else {
                Debug::debug("true");
                return true;
            }
        }
    }

/**
 * Update upgrade state through `updateConfig`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for updateConfig.
 * @phpstan-return void
 * @psalm-return void
 * @see self::updateConfig()
 * @example /fr/upgrade/updateConfig
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function updateConfig($param)
    {
        $commit = Git::getCurrentCommit();

        //$file = ROOT."/configuration/site.config.php";
        $file = ROOT."configuration/site.config.php";
        $text = file_get_contents($file);

        $date = explode(" ", $commit['date'])[0];
        
        $text = preg_replace('/"SITE_VERSION", "(\S+)"/','"SITE_VERSION", "'.$commit['version'].'"' ,$text);
        $text = preg_replace('/"SITE_LAST_UPDATE", "(\S+)"/','"SITE_LAST_UPDATE", "'.$date.'"' ,$text);
        $text = preg_replace('/"SITE_BUILD", "(\S+)"/','"SITE_BUILD", "'.$commit['build'].'"' ,$text);
        
        file_put_contents($file, $text);
    }
}
