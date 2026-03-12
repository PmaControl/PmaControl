<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

//use phpseclib\Crypt\RSA;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;
use App\Library\Chiffrement;
use App\Library\Debug;

/**
 * Class responsible for transfer workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Transfer
{
    /* used for link MySQL */
    static $db;

/**
 * Handle transfer state through `setDb`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db Input value for `db`.
 * @phpstan-param mixed $db
 * @psalm-param mixed $db
 * @return void Returned value for setDb.
 * @phpstan-return void
 * @psalm-return void
 * @see self::setDb()
 * @example /fr/transfer/setDb
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function setDb($db)
    {
        self::$db = $db;
    }

/**
 * Handle transfer state through `sendFile`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param int $id_backup_storage_area Input value for `id_backup_storage_area`.
 * @phpstan-param int $id_backup_storage_area
 * @psalm-param int $id_backup_storage_area
 * @param mixed $src Input value for `src`.
 * @phpstan-param mixed $src
 * @psalm-param mixed $src
 * @param mixed $dst Input value for `dst`.
 * @phpstan-param mixed $dst
 * @psalm-param mixed $dst
 * @return mixed Returned value for sendFile.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::sendFile()
 * @example /fr/transfer/sendFile
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function sendFile($id_backup_storage_area, $src, $dst)
    {

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT *, b.id FROM backup_storage_area a
                INNER JOIN ssh_key b ON a.id_ssh_key = b.id
                WHERE a.id = ".$id_backup_storage_area;
        $res = $db->sql_query($sql);



        while ($ob = $db->sql_fetch_object($res)) {

            $start = microtime(true);

            if (substr($dst, 0, 1) === "/") {
                throw new \Exception("PMACTRL-576 : The path must be relative, we use the main path of storage area : '".$ob->path."'");
                exit;
            }

            $dst = $ob->path."/".$dst;



            if (!empty($ob->private_key)) {
                $pv_key = Chiffrement::decrypt($ob->private_key);
            }

            $sftp = new SFTP($ob->ip);
            $ssh  = new SSH2($ob->ip);

            // priorité a la clef privé si les 2 sont remplie
            if (!empty($pv_key)) {
                $key = new RSA();
                $key->loadKey($pv_key);
            }

            if (!$sftp->login($ob->user, $key)) {
                echo 'SCP Login Failed';
                return false;
            }

            if (!$ssh->login($ob->user, $key)) {
                echo 'SSH Login Failed';
                return false;
            }


            $file_name = pathinfo($dst)['basename'];
            $dst_dir   = pathinfo($dst)['dirname'];


            Debug::debug(pathinfo($dst), "Path_info");

            $ssh->exec("mkdir -p ".$dst_dir);

            Debug::debug($dst_dir, "mkdir -p");


            $db->sql_close();
            $sftp->put($dst, $src, SFTP::SOURCE_LOCAL_FILE);
            $data['execution_time'] = round(microtime(true) - $start, 0);


            $data['size'] = $sftp->size($dst);

            $md5 = $ssh->exec("md5sum ".$dst);

            $data['md5']      = explode(" ", $md5)[0];
            $data['pathfile'] = $dst;


            Debug::debug($data, "data");

            $files = $sftp->rawlist($dst_dir);


            Debug::debug($files, "files");

            foreach ($files as $file) {
                if ($file['filename'] === $file_name) {

                    return $data;
                }
            }

            return false;
        } //end while
    }

/**
 * Retrieve transfer state through `getFile`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $server Input value for `server`.
 * @phpstan-param mixed $server
 * @psalm-param mixed $server
 * @param mixed $src Input value for `src`.
 * @phpstan-param mixed $src
 * @psalm-param mixed $src
 * @param mixed $dst Input value for `dst`.
 * @phpstan-param mixed $dst
 * @psalm-param mixed $dst
 * @return mixed Returned value for getFile.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getFile()
 * @example /fr/transfer/getFile
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private static function getFile($server, $src, $dst)
    {
        $start = microtime(true);

        if (substr($dst, 0, 1) != "/") {
            throw new \Exception("PMACTRL-577 : The path must be fully qualified");
            exit;
        }




        $sftp = new SFTP($server['hostname']);
        $ssh  = new SSH2($server['hostname']);


        // priorité a la clef privé si les 2 sont remplie
        if (!empty($server['private_key'])) {
            $key = new RSA();
            $key->loadKey($server['private_key']);
        }

        if (!$sftp->login($server['user'], $key)) {
            echo 'SCP Login Failed';
            return false;
        }

        if (!$ssh->login($server['user'], $key)) {
            echo 'SSH Login Failed';
            return false;
        }


        $sftp->get($src, $dst);
        $data['execution_time'] = round(microtime(true) - $start, 0);


        $data['size'] = $sftp->size($src);

        $md5 = $ssh->exec("md5sum ".$src." 2>1 >> /dev/null");

        $data['md5'] = explode(" ", $md5)[0];

        return $data;
    }

/**
 * Retrieve transfer state through `getFileFromMysql`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_mysql_server Input value for `id_mysql_server`.
 * @phpstan-param int $id_mysql_server
 * @psalm-param int $id_mysql_server
 * @param mixed $src Input value for `src`.
 * @phpstan-param mixed $src
 * @psalm-param mixed $src
 * @param mixed $dst Input value for `dst`.
 * @phpstan-param mixed $dst
 * @psalm-param mixed $dst
 * @return mixed Returned value for getFileFromMysql.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getFileFromMysql()
 * @example /fr/transfer/getFileFromMysql
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getFileFromMysql($id_mysql_server, $src, $dst)
    {

        $sql = "select b.user,b.private_key,b.`type`,c.ip  , c.ssh_port
            from link__mysql_server__ssh_key a
        INNER JOIN ssh_key b ON a.id_ssh_key = b.id
        INNER JOIN mysql_server c on c.id = a.id_mysql_server
        where id_mysql_server = ".$id_mysql_server." and active='1' LIMIT 1;";

        $res = self::$db->sql_query($sql);

        while ($ob = self::$db->sql_fetch_object($res)) {

            $server                = array();
            $server['hostname']    = $ob->ip;
            $server['port']        = $ob->ssh_port;
            $server['user']        = $ob->user;
            $server['private_key'] = Chiffrement::decrypt($ob->private_key, CRYPT_KEY); ;

            $ret = self::getFile($server, $src, $dst);
        }

        return $ret;
    }
}

