<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \phpseclib3\Crypt\RSA;
use \phpseclib3\Net\SSH2;
use \phpseclib3\Crypt\PublicKeyLoader;
use \App\Library\Debug;
use \App\Library\Post;
use App\Library\Security\CsrfGuard;
use \Glial\Sgbd\Sgbd;
use Glial\Security\Csrf;

/*
 *
 * Module de gestion des espaces de stockage
 * 
 *
 */

class StorageArea extends Controller {
    private const STORAGE_AREA_ADD_CSRF_SCOPE = 'storage_area.add';
    private const STORAGE_AREA_ADD_INTEGER_FIELDS = [
        'id_ssh_key',
        'id_geolocalisation_city',
        'id_geolocalisation_country',
    ];
    private const STORAGE_AREA_UPDATE_CSRF_SCOPE = 'storage_area.update';
    private const STORAGE_AREA_UPDATE_FIELDS = ['libelle'];
    private const STORAGE_AREA_LIBELLE_MAX_LENGTH = 64;
    private const STORAGE_AREA_IP_MAX_LENGTH = 15;

/**
 * Render storage area state through `index`.
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
 * @example /fr/storagearea/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param) {

        $db = Sgbd::sql(DB_DEFAULT);


        $this->di['js']->addJavascript(array('bootstrap-editable.min.js', 'Tree/index.js'));


        if (empty($param[0])) {
            $data['menu'] = "listStorage";
        } else {
            $data['menu'] = $param[0];
        }

        $sql = "SELECT count(1) as cpt from backup_storage_area";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $data['cpt'] = $ob->cpt;
        }
        $this->set('data', $data);
    }

/**
 * Create storage area state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/storagearea/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add($param) {
        //df -Ph . | tail -1 | awk '{print $2}' => to know space

        set_include_path(get_include_path() . ROOT . 'vendor/phpseclib/phpseclib');

        if (!empty($_GET['backup_storage_area']['path'])) {
            $_GET['backup_storage_area']['path'] = str_replace("[DS]", "/", $_GET['backup_storage_area']['path']);
        }

        $db = Sgbd::sql(DB_DEFAULT);
        if (CsrfGuard::isPost($_SERVER)) {
            $outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);
            if ($outcome['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendStorageAreaAddError($outcome['status'], $outcome['body'], $outcome['headers']);
                return;
            }

            Crypt::$key = CRYPT_KEY;

            $storage_area = $outcome['storage_area'];

            //debug($storage_area);

            $db = Sgbd::sql(DB_DEFAULT);

            $sql = self::buildStorageAreaAddSshKeySql($storage_area['backup_storage_area']['id_ssh_key']);

            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $ssh_private_key = Crypt::decrypt($ob->private_key, CRYPT_KEY);
                $ssh_user = $ob->user;
            }

            // clef ssh ou password ?
            if (!empty($ssh_private_key)) {
                $key = PublicKeyLoader::load($ssh_private_key);
            }

            //deploy public key by SCP
            $ssh = new SSH2($storage_area['backup_storage_area']['ip'], $this->getSshPort($storage_area['backup_storage_area']));

            //tentative connexion au serveur ssh
            if (!$ssh->login($ssh_user, $key)) {

                $elems = Post::getToPost();

                $title = I18n::getTranslation(__("Failed to connect on ssh/scp"));
                $msg = I18n::getTranslation(__("Please check your hostname and you credentials !"));

                set_flash("error", $title, $msg);

                header("location: " . LINK . "storageArea/add/" . $elems);
                exit;
            }

            if (!$this->isRemoteStoragePathAvailable($ssh, $storage_area['backup_storage_area']['path'])) {

                $elems = Post::getToPost();

                $title = I18n::getTranslation(__("Invalid storage path"));
                $msg = I18n::getTranslation(__("The storage path does not exist or is not readable on the remote server."));

                set_flash("error", $title, $msg);

                header("location: " . LINK . "storageArea/add/" . $elems);
                exit;
            }

            $id_storage_area = $db->sql_save($storage_area);

            if (!$id_storage_area) {


                $error = $db->sql_error();
                $_SESSION['ERROR'] = $error;

                $title = I18n::getTranslation(__("Fail to add this storage area"));
                $msg = I18n::getTranslation(__("One or more problem came when you try to add this storage, please verify your informations"));

                set_flash("error", $title, $msg);

                $elems = Post::getToPost();

                header("location: " . LINK . "storageArea/add/" . $elems);
                exit;
            } else {


                $php = explode(" ", shell_exec("whereis php"))[1];
                $cmd = $php . " " . GLIAL_INDEX . " StorageArea getStorageSpace " . $id_storage_area . "";
                shell_exec($cmd);

                $title = I18n::getTranslation(__("Successfull"));
                $msg = I18n::getTranslation(__("You storage area has been successfull added !"));

                set_flash("success", $title, $msg);
                header("location: " . LINK . "storageArea/listStorage");
                exit;
            }
        }

        $this->di['js']->addJavascript(array("jquery.browser.min.js", "jquery.autocomplete.min.js"));
        $this->di['js']->code_javascript('$("#backup_storage_area-id_geolocalisation_city-auto").autocomplete("' . LINK . 'user/city/none>none", {
		extraParams: {
			country: function() {return $("#backup_storage_area-id_geolocalisation_country").val();}
		},
        mustMatch: true,
        autoFill: true,
        max: 100,
        scrollHeight: 302,
        delay:0
		});
		$("#backup_storage_area-id_geolocalisation_city-auto").result(function(event, data, formatted) {
			if (data)
				$("#backup_storage_area-id_geolocalisation_city").val(data[1]);
		});
		$("#backup_storage_area-id_geolocalisation_country").change( function() 
		{
			$("#backup_storage_area-id_geolocalisation_city-auto").val("");
			$("#backup_storage_area-id_geolocalisation_city").val("");
		} );

		');

        $sql = "SELECT id, libelle from geolocalisation_country where libelle != '' order by libelle asc";
        $res = $db->sql_query($sql);
        $data['geolocalisation_country'] = $db->sql_to_array($res);

        $sql = "SELECT * FROM ssh_key order by name";
        $res = $db->sql_query($sql);

        $data['ssh_key'] = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $tmp = array();
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->name . " (" . $ob->type . ":" . $ob->bit . " bit) " . $ob->fingerprint;
            $data['ssh_key'][] = $tmp;
        }

        $data['storage_area_add_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['storage_area_add_csrf_token'] = Csrf::issueToken($_SESSION, self::STORAGE_AREA_ADD_CSRF_SCOPE);
        $data['menu'] = __FUNCTION__;

        $this->set('data', $data);
    }

    public static function evaluateAddRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::STORAGE_AREA_ADD_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return self::buildStorageAreaAddOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $storageArea = self::normalizeAddPayload($post);
        if ($storageArea === null) {
            return self::buildStorageAreaAddOutcome(400, "Invalid storage area add payload");
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'storage_area' => $storageArea,
        ];
    }

    public static function normalizeAddPayload(array $post): ?array
    {
        if (empty($post['backup_storage_area']) || ! is_array($post['backup_storage_area'])) {
            return null;
        }

        $storageArea = $post['backup_storage_area'];
        foreach ($storageArea as $field => $value) {
            if (! is_string($field) || ! is_scalar($value)) {
                return null;
            }
        }

        foreach (self::STORAGE_AREA_ADD_INTEGER_FIELDS as $field) {
            $value = isset($storageArea[$field]) && is_scalar($storageArea[$field])
                ? (string) $storageArea[$field]
                : '';

            if ($value === '' || ! ctype_digit($value) || (int) $value < 1) {
                return null;
            }

            $storageArea[$field] = (int) $value;
        }

        if (! isset($storageArea['libelle']) || strlen((string) $storageArea['libelle']) > self::STORAGE_AREA_LIBELLE_MAX_LENGTH) {
            return null;
        }

        $ip = isset($storageArea['ip']) ? (string) $storageArea['ip'] : '';
        if (
            $ip === ''
            || strlen($ip) > self::STORAGE_AREA_IP_MAX_LENGTH
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false
        ) {
            return null;
        }
        $storageArea['ip'] = $ip;

        if (! isset($storageArea['path']) || (string) $storageArea['path'] === '') {
            return null;
        }

        if (! array_key_exists('port', $storageArea) || (string) $storageArea['port'] === '') {
            $storageArea['port'] = 22;
        } else {
            $port = (string) $storageArea['port'];
            if (! ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
                return null;
            }
            $storageArea['port'] = (int) $port;
        }

        return ['backup_storage_area' => $storageArea];
    }

    public static function buildStorageAreaAddSshKeySql(int $idSshKey): string
    {
        return "SELECT * FROM ssh_key WHERE id =" . $idSshKey;
    }

    private static function buildStorageAreaAddOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'storage_area' => null,
        ];
    }

    private static function sendStorageAreaAddError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($message !== '') {
            echo $message;
        }
    }

/**
 * Retrieve storage area state through `listStorage`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for listStorage.
 * @phpstan-return void
 * @psalm-return void
 * @see self::listStorage()
 * @example /fr/storagearea/listStorage
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function listStorage() {
        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT *,c.libelle as city, a.libelle as name,a.id as id_backup_storage_area
        FROM backup_storage_area a
        INNER JOIN geolocalisation_country b ON a.id_geolocalisation_country = b.id
        INNER JOIN geolocalisation_city c ON c.id = a.id_geolocalisation_city
        ORDER BY a.libelle";

        $data['storage'] = $db->sql_fetch_yield($sql);
        
        $data['storage2'] = $db->sql_fetch_yield($sql);
        $data['storage_area_update_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['storage_area_update_csrf_token'] = Csrf::issueToken($_SESSION, self::STORAGE_AREA_UPDATE_CSRF_SCOPE);

        $sql = "SELECT * FROM backup_storage_space b  
        JOIN (select max(id) as id from backup_storage_space a group by id_backup_storage_area) a ON a.id = b.id";

        $res = $db->sql_query($sql);

        while ($tab = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['space'][$tab['id_backup_storage_area']] = $tab;
        }

        $this->set('data', $data);
    }

/**
 * Retrieve storage area state through `getStorageSpace`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return mixed Returned value for getStorageSpace.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getStorageSpace()
 * @example /fr/storagearea/getStorageSpace
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getStorageSpace($param) {

        Debug::parseDebug($param);

        $this->layout_name = false;
        $this->view = false;

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT a.*, b.`user`, b.`private_key` FROM `backup_storage_area` a
            INNER JOIN `ssh_key` b ON a.`id_ssh_key` = b.id ";

        if (!empty($param[0])) {
            $sql .= " WHERE a.`id` = " . intval($param[0]) . "";
        }
        $sql .= ";";

        Debug::sql($sql);

        $storages = $db->sql_fetch_yield($sql);

        $success = true;

        foreach ($storages as $storage) {

            $login = $storage['user'];
            $key_ssh = Crypt::decrypt($storage['private_key'], CRYPT_KEY);
            
            $rsa = PublicKeyLoader::load($key_ssh);
            $password = $rsa;

            $ssh = new SSH2($storage['ip'], $this->getSshPort($storage));

            //$publicHostKey = $ssh->getServerPublicHostKey();

            if (!$ssh->login($login, $password)) {

                Debug::debug("SSH FAILED ! ");
                $success = false;
            } else {
                Debug::debug("SSH ok !");

                if (!$this->isRemoteStoragePathAvailable($ssh, $storage['path'])) {
                    Debug::debug("Storage path not found or not readable: ".$storage['path']);
                    $success = false;
                    continue;
                }

                /*
                 * df -k . => get file systeme for current directory
                 * tail -n +2 => remove the first line
                 * sed ':a;N;$!ba;s/\n/ /g' => remove \n (in case of the name of partition is really big and need to be on 2 lines)
                 * sed \"s/\ +/ /g\" => remove + in some case
                 * awk '{print $2 \" \" $3 \" \" $4 \" \" $5}' => split result by space
                 */

                $cmd = $this->getStorageDfCommand($storage['path']);
                $resultats = $ssh->exec($cmd);

                $cmd2 = $this->getStorageBackupSizeCommand($storage['path']);
                $used_by_backup = $ssh->exec($cmd2);

                $space = $this->parseStorageSpace($resultats, $used_by_backup);

                if ($space === false) {
                    Debug::debug($cmd, "Storage size command failed");
                    Debug::debug($resultats);
                    Debug::debug($cmd2, "Backup size command failed");
                    Debug::debug($used_by_backup);
                    $success = false;
                    continue;
                }

                $data = [];
                $data['backup_storage_space']['id_backup_storage_area'] = $storage['id'];
                $data['backup_storage_space']['date'] = date('Y-m-d H:i:s');
                $data['backup_storage_space']['size'] = $space['size'];
                $data['backup_storage_space']['used'] = $space['used'];
                $data['backup_storage_space']['available'] = $space['available'];
                $data['backup_storage_space']['percent'] = $space['percent'];
                $data['backup_storage_space']['backup'] = $space['backup'];

                if (!$db->sql_save($data)) {

                    debug($cmd . "\n");
                    debug($resultats);
                    debug($space);
                    debug($data);
                    debug($db->sql_error());
                    echo "\n";

                    return false;
                }
            }
        }

        return $success;
    }

    private function getSshPort($storage)
    {
        $port = empty($storage['port']) ? 0 : (int)$storage['port'];

        if ($port <= 0) {
            return 22;
        }

        return $port;
    }

    private function isRemoteStoragePathAvailable($ssh, $path)
    {
        $result = trim((string)$ssh->exec($this->getStoragePathCheckCommand($path)));

        return $ssh->getExitStatus() === 0 || $result === "PMACTRL_STORAGE_OK";
    }

    private function getStoragePathCheckCommand($path)
    {
        return "cd ".escapeshellarg($path)." && test -d . && test -r . && printf PMACTRL_STORAGE_OK";
    }

    private function getStorageDfCommand($path)
    {
        return "cd ".escapeshellarg($path)." && df -Pk . | awk 'NR==2 {print $2 \" \" $3 \" \" $4 \" \" $5}'";
    }

    private function getStorageBackupSizeCommand($path)
    {
        return "cd ".escapeshellarg($path)." && du -s . | awk '{print $1}'";
    }

    private function parseStorageSpace($df_output, $du_output)
    {
        $results = preg_split('/\s+/', trim((string)$df_output));
        $backup = trim((string)$du_output);

        if (count($results) < 4) {
            return false;
        }

        $percent = rtrim($results[3], "%");

        if (!is_numeric($results[0]) || !is_numeric($results[1]) || !is_numeric($results[2]) || !is_numeric($percent) || !is_numeric($backup)) {
            return false;
        }

        return array(
            'size' => (int)$results[0],
            'used' => (int)$results[1],
            'available' => (int)$results[2],
            'percent' => (int)$percent,
            'backup' => (int)$backup,
        );
    }

/**
 * Delete storage area state through `delete`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for delete.
 * @phpstan-return void
 * @psalm-return void
 * @see self::delete()
 * @example /fr/storagearea/delete
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function delete($param) {
        
        $this->view = false;
        
        $id_backup_storage_area = $param[0];
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "DELETE FROM  backup_storage_area WHERE id ='" . $id_backup_storage_area . "'";

        $db->sql_query($sql);
        header("location: " . LINK . "StorageArea/index");
        exit;
    }

/**
 * Handle storage area state through `menu`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for menu.
 * @phpstan-return void
 * @psalm-return void
 * @see self::menu()
 * @example /fr/storagearea/menu
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function menu($param) {

        if (empty($param[0])) {
            $data['menu'] = "listStorage";
        } else {
            $data['menu'] = $param[0];
        }

        $this->set("data", $data);
    }

/**
 * Update storage area state through `update`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/storagearea/update
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function update($param) {

        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateUpdateRequest($_POST, $_SERVER, $_SESSION);
        if ($outcome['status'] !== 200) {
            self::sendStorageAreaUpdateError($outcome['status'], $outcome['body'], $outcome['headers']);
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = self::buildStorageAreaUpdateSql($outcome['update'], [$db, 'sql_real_escape_string']);
        $db->sql_query($sql);

        if ($db->sql_affected_rows() === 1) {
            echo "OK";
        } else {
            self::sendStorageAreaUpdateError(503, "Storage area not updated");
        }
    }

    public static function evaluateUpdateRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::STORAGE_AREA_UPDATE_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return self::buildStorageAreaUpdateOutcome($guard['status'], $guard['body'], $guard['headers']);
        }

        $update = self::normalizeUpdatePayload($post);
        if ($update === null) {
            return self::buildStorageAreaUpdateOutcome(400, "Invalid storage area update payload");
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'update' => $update,
        ];
    }

    public static function normalizeUpdatePayload(array $post): ?array
    {
        if (
            ! array_key_exists('name', $post)
            || ! array_key_exists('pk', $post)
            || ! array_key_exists('value', $post)
            || ! is_scalar($post['name'])
            || ! is_scalar($post['pk'])
            || ! is_scalar($post['value'])
        ) {
            return null;
        }

        $field = (string) $post['name'];
        $id = (string) $post['pk'];
        $value = (string) $post['value'];

        if (! in_array($field, self::STORAGE_AREA_UPDATE_FIELDS, true)) {
            return null;
        }

        if (! ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        if (strlen($value) > self::STORAGE_AREA_LIBELLE_MAX_LENGTH) {
            return null;
        }

        return [
            'field' => $field,
            'value' => $value,
            'id' => (int) $id,
        ];
    }

    public static function buildStorageAreaUpdateSql(array $update, callable $escape): string
    {
        return sprintf(
            "UPDATE backup_storage_area SET `%s` = '%s' WHERE id = %d",
            $update['field'],
            $escape($update['value']),
            $update['id']
        );
    }

    private static function buildStorageAreaUpdateOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'update' => null,
        ];
    }

    private static function sendStorageAreaUpdateError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($message !== '') {
            echo $message;
        }
    }
}
