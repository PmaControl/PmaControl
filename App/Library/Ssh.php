<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;
use \App\Library\Debug;
use \Glial\Security\Crypt\Crypt;
use \Glial\Sgbd\Sgbd;

/**
 * Class responsible for ssh workflows.
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
class Ssh
{
    /*
     * les connection active ssh
     */
    static $ssh    = array();
    /*
     * Server with active ssh key 
     */
    static $server = array();


/**
 * Stores `$mock` for mock.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    public static $mock = null;

/**
 * Handle ssh state through `setMockInstance`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $instance Input value for `instance`.
 * @phpstan-param mixed $instance
 * @psalm-param mixed $instance
 * @return void Returned value for setMockInstance.
 * @phpstan-return void
 * @psalm-return void
 * @see self::setMockInstance()
 * @example /fr/ssh/setMockInstance
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function setMockInstance($instance) {
        self::$mock = $instance;
    }
    
/**
 * Handle ssh state through `formatPrivateKey`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $key Input value for `key`.
 * @phpstan-param mixed $key
 * @psalm-param mixed $key
 * @return mixed Returned value for formatPrivateKey.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::formatPrivateKey()
 * @example /fr/ssh/formatPrivateKey
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function formatPrivateKey($key)
    {
        $key = str_replace('\n', "", $key);
        $key = str_replace("\n", "", $key);
        $key = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $key);
        $key = str_replace("-----END RSA PRIVATE KEY-----", "", $key);

        return $key;
    }
    /*
     * 
     * return un objet de type phpseclib\Net\SSH2
     */

    static function connect($ip, $port, $user, $password)
    {

        /*
          $debug = Debug::$debug;
          Debug::$debug = false;
         */

        Debug::Debug($ip, "ip");
        Debug::Debug($port, "port");
        Debug::Debug($user, "user");
        Debug::Debug($password, "password / private key");

        $ssh               = new SSH2($ip, $port, 30);
        $login_successfull = true;
        $rsa               = PublicKeyLoader::load($password);

        if (PublicKeyLoader::load($password) === false) {
            $login_successfull = false;
            Debug::debug("private key loading failed!");
        }

        if (!$ssh->login($user, $rsa)) {
            Debug::debug("Login Failed");
            $login_successfull = false;
            return false;
        }

        if ($login_successfull === true) {
            return $ssh;
        } else {
            return false;
        }
    }

/**
 * Handle ssh state through `close`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for close.
 * @phpstan-return void
 * @psalm-return void
 * @see self::close()
 * @example /fr/ssh/close
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function close()
    {
        
    }

/**
 * Handle ssh state through `isValid`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $pubkeyssh Input value for `pubkeyssh`.
 * @phpstan-param mixed $pubkeyssh
 * @psalm-param mixed $pubkeyssh
 * @return mixed Returned value for isValid.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isValid()
 * @example /fr/ssh/isValid
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function isValid($pubkeyssh)
    {
        Debug::debug(str_repeat("#", 80));
        //Debug::debug($pubkeyssh);

        if (file_exists($pubkeyssh)) {

            Debug::debug("get key from from file !");
            $pubkeyssh = file_get_contents($pubkeyssh);
        }

        Debug::debug($pubkeyssh, "public key");

        // remove ^M (happen with php 7.3 ? )
        $pubkeyssh = str_ireplace("\x0D", "", $pubkeyssh);

        $path_puplic_key = TMP."trash/key".uniqid();
        file_put_contents($path_puplic_key, $pubkeyssh."\n");

        Debug::debug($path_puplic_key, "PATH of key");
        shell_exec("chmod 600 ".$path_puplic_key);

        $file_error = TMP."trash/generate_key.error";

        if (file_exists($file_error)) {
            unlink($file_error);
        }

        $cmd = "ssh-keygen -l -f ".$path_puplic_key."  2> ".$file_error;
        Debug::debug($cmd, "CMD");

        $result = shell_exec($cmd);

        Debug::debug($result, "RESULT");

        if (file_exists($file_error)) {

            $error = file_get_contents($file_error);

            if (!empty($error)) {
                Debug::debug($error, "[ERROR]");
            }
            unlink($file_error);
        }

        unlink($path_puplic_key);

        if (empty($result)) {


            return false;
        } else {

            $elems = explode(" ", $result);

            $data['bit']    = $elems[0];
            $data['pubkey'] = $elems[1];
            $data['type']   = substr(end($elems), 1, -2);

            unset($elems[count($elems) - 1]);
            unset($elems[0]);
            unset($elems[1]);

            $data['name'] = implode(" ", $elems);
        }

        Debug::debug($data);

        return $data;
    }

/**
 * Handle ssh state through `generate`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $type Input value for `type`.
 * @phpstan-param mixed $type
 * @psalm-param mixed $type
 * @param mixed $bit Input value for `bit`.
 * @phpstan-param mixed $bit
 * @psalm-param mixed $bit
 * @return mixed Returned value for generate.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::generate()
 * @example /fr/ssh/generate
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function generate($type, $bit)
    {

        $key = TMP."trash/key".uniqid();

        $cmd = "ssh-keygen -t ".$type." -C 'PmaControl' -N \"\" -f ".$key." -b ".$bit;

        Debug::debug($cmd);

        shell_exec($cmd);

        $data['key_priv'] = trim(file_get_contents($key));
        $data['key_pub']  = trim(file_get_contents($key.".pub"));

        unlink($key);
        unlink($key.".pub");

        return $data;
    }

/**
 * Handle ssh state through `put`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $server Input value for `server`.
 * @phpstan-param mixed $server
 * @psalm-param mixed $server
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @param mixed $login Input value for `login`.
 * @phpstan-param mixed $login
 * @psalm-param mixed $login
 * @param mixed $private_key Input value for `private_key`.
 * @phpstan-param mixed $private_key
 * @psalm-param mixed $private_key
 * @param mixed $src Input value for `src`.
 * @phpstan-param mixed $src
 * @psalm-param mixed $src
 * @param mixed $dst Input value for `dst`.
 * @phpstan-param mixed $dst
 * @psalm-param mixed $dst
 * @return mixed Returned value for put.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::put()
 * @example /fr/ssh/put
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function put($server, $port, $login, $private_key, $src, $dst)
    {

        $start = microtime(true);

        $sftp = new SFTP($server, $port);
        $ssh  = new SSH2($server, $port);

        // priorité a la clef privé si les 2 sont remplie
        if (!empty($private_key)) {
            $rsa = PublicKeyLoader::load($private_key);
        }

        if (!$sftp->login($login, $key)) {
            Debug::debug("SCP Login Failed");
            return false;
        }

        if (!$ssh->login($login, $key)) {
            Debug::debug('SSH Login Failed');
            return false;
        }

        $file_name = pathinfo($dst)['basename'];
        $dst_dir   = pathinfo($dst)['dirname'];

        $ssh->exec("mkdir -p ".$dst_dir);

        $sftp->put($dst, $src, SFTP::SOURCE_LOCAL_FILE);
        $data['execution_time'] = round(microtime(true) - $start, 0);
        $data['size']           = $sftp->size($dst);

        $md5 = $ssh->exec("md5sum ".$dst);

        $data['md5']      = explode(" ", $md5)[0];
        $data['pathfile'] = $dst;

        $files = $sftp->rawlist($dst_dir);

        foreach ($files as $file) {
            if ($file['filename'] === $file_name) {

                return $data;
            }
        }

        return false;
    }

/**
 * Handle ssh state through `spaceAvailable`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for spaceAvailable.
 * @phpstan-return void
 * @psalm-return void
 * @see self::spaceAvailable()
 * @example /fr/ssh/spaceAvailable
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function spaceAvailable($param)
    {
        
    }

/**
 * Handle ssh state through `ssh`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id_server Input value for `id_server`.
 * @phpstan-param int $id_server
 * @psalm-param int $id_server
 * @param mixed $type Input value for `type`.
 * @phpstan-param mixed $type
 * @psalm-param mixed $type
 * @return mixed Returned value for ssh.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::ssh()
 * @example /fr/ssh/ssh
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function ssh($id_server, $type = 'mysql')
    {
        if (self::$mock) {
            return self::$mock;
        }

        $server = self::getSsh($id_server, $type);

        if ($server === false) {
            return false;
        }
        
        $ssh = self::connect($server['ip'], $server['port'], $server['user'], Crypt::decrypt($server['private_key'], CRYPT_KEY));

        if ($ssh) {

            Debug::debug($ssh->exec("ls -l"), "ls -l");

            self::$ssh[$id_server] = $ssh;
            return $ssh;
        }


        return false;
    }

/**
 * Retrieve ssh state through `getSsh`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param int $id Input value for `id`.
 * @phpstan-param int $id
 * @psalm-param int $id
 * @param string $type Input value for `type`.
 * @phpstan-param string $type
 * @psalm-param string $type
 * @return mixed Returned value for getSsh.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::getSsh()
 * @example /fr/ssh/getSsh
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function getSsh(int $id, string $type = 'mysql')
    {
        if (empty(self::$server[$type])) {
            self::$server[$type] = [];
        }

        if (empty(self::$server[$type][$id])) {

            $db  = Sgbd::sql(DB_DEFAULT);

            if ($type === 'mysql') {
                $sql = "SELECT a.id, a.ip, c.user, a.ssh_port as port,c.public_key,c.private_key, c.type, c.fingerprint   
                FROM mysql_server a
                LEFT JOIN link__mysql_server__ssh_key b ON a.id = b.id_mysql_server
                INNER JOIN ssh_key c ON b.id_ssh_key = c.id
                WHERE `active` = 1";
            }elseif ($type === 'docker') {

                $sql = "SELECT ds.id, ds.hostname AS ip, sk.user, ds.port, sk.public_key, sk.private_key, sk.type, sk.fingerprint
                FROM docker_server ds
                INNER JOIN ssh_key sk ON ds.id_ssh_key = sk.id";

            } else {
                throw new \Exception("Unknown SSH lookup type `$type`");
            }

            Debug::sql($sql);

            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$server[$type][$ob['id']] = $ob;
            }
        }

        if (empty(self::$server[$type][$id])) {
            return false;
        }

        return self::$server[$type][$id];
    }
}
