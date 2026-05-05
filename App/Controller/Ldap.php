<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\I18n\I18n;
//use \Glial\Ldap\Ldap as gg;
use App\Library\Security\CsrfGuard;
use Glial\Security\Csrf;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \Glial\Sgbd\Sgbd;


/**
 * Class responsible for ldap workflows.
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
class Ldap extends Controller
{
    private const LDAP_INDEX_CSRF_SCOPE = 'ldap.index';
    private const LDAP_CONFIG_FIELDS = [
        'url',
        'port',
        'bind_dn',
        'root_dn',
        'root_dn_search',
        'bind_passwd',
        'bind_passwd_confirm',
        'check',
    ];
    private const LDAP_FIELD_MAX_LENGTH = 4096;
    private const LDAP_GET_GROUP_FROM_USER_CSRF_SCOPE = 'ldap.getGroupFromUser';
    private const LDAP_USERNAME_MAX_LENGTH = 128;

/**
 * Stores `$module_group` for module group.
 *
 * @var string
 * @phpstan-var string
 * @psalm-var string
 */
    public $module_group          = "Ldap";
/**
 * Stores `$method_administration` for method administration.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    public $method_administration = array('Ldap', "roles");
/**
 * Stores `$without_quote` for without quote.
 *
 * @var array<int|string,mixed>
 * @phpstan-var array<int|string,mixed>
 * @psalm-var array<int|string,mixed>
 */
    public $without_quote         = array('LDAP_CHECK', 'LDAP_PORT');

/**
 * Stores `$logger` for logger.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    public $logger;

    const LDAP_CONFIG = ROOT."/configuration/ldap.config.php";

    /*
     * recupére recurssivement toutes les valeurs avec la clefs donnée en argument
     */

    private function recursiveArraySearchByKey($haystack, $needle, &$ret)
    {

        if (!is_array($haystack)) {

            //debug($haystack);
            throw new \Exception('PMACTRL-015 : $haystack must be an array !');
        }


        foreach ($haystack as $key => $val) {
            if (is_array($val)) {
                $this->recursiveArraySearchByKey($val, $needle, $ret);
            } else if ($needle === $key) {
                $ret[] = $val;
            }
        }
    }

/**
 * Render ldap state through `index`.
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
 * @example /fr/ldap/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function index($param)
    {

        /*
          if (!empty($param[0]) && $param[0] === "success") {

          sleep(1);
          header("location: ".LINK.$this->getClass()."/".__FUNCTION__);
          exit;
          }
         */


        $this->title = '<i class="fa fa-address-book" aria-hidden="true"></i> LDAP';

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

        $this->di['js']->code_javascript('$(function () {  $(\'[data-toggle="popover"]\').popover({trigger:"hover"}) });');
        $this->di['js']->code_javascript('
            $(\'[data-toggle="popover"]\').each(function(index, element) {
    var contentElementId = $(element).data().target;
    var contentHtml = $(contentElementId).html();
    $(element).popover({
        content: contentHtml,
        trigger:"hover",
        html:true
    });
});');

        $data = [
            'ldap_index_csrf_field' => Csrf::DEFAULT_FIELD,
            'ldap_index_csrf_token' => Csrf::issueToken($_SESSION, self::LDAP_INDEX_CSRF_SCOPE),
        ];

        if (CsrfGuard::isPost($_SERVER)) {
            $postOutcome = self::evaluateIndexPostRequest($_POST, $_SERVER, $_SESSION);
            if ($postOutcome['status'] !== 200) {
                $this->view        = false;
                $this->layout_name = false;
                self::sendIndexError($postOutcome['status'], $postOutcome['body'], $postOutcome['headers']);
                return;
            }

            $post = $postOutcome['post'];

            if (!empty($post['ldap']['url'])) {

                $error = array();

                $this->log("info", "POST", json_encode(self::redactLdapSecrets($post)));

                $url = $this->postToGet(array('ldap' => $post['ldap']), array('bind_passwd', 'bind_passwd_confirm'));

                if ($this->testLdap($post['ldap']['url'], $post['ldap']['port']) !== true) {
                    $error[] = "We cannot to connect to LDAP server.";
                }

                if ($this->testLdapCredential($post['ldap']['url'], $post['ldap']['port'], $post['ldap']['bind_dn'], $post['ldap']['bind_passwd']) !== true) {
                    $error[] = "Invalid credentials (Check Bind DN and/or Bind password).";
                }


                $err = $this->UpdateConfigFile($post['ldap']);

                $error = array_merge($err, $error);

                $check = $this->testLdapCredential($post['ldap']['url'], $post['ldap']['port'], $post['ldap']['bind_dn'], $post['ldap']['bind_passwd']);


                clearstatcache();
                //sleep(1);
                if (count($error) === 0 && $check) {
                    $msg   = I18n::getTranslation(__("LDAP configuration has been updated"));
                    $title = I18n::getTranslation(__("Sucess"));
                    set_flash("success", $title, $msg);

                    header("location: ".LINK.$this->getClass()."/".__FUNCTION__."/success");
                    exit;
                } else {

                    $msg   = I18n::getTranslation($this->putUl($error));
                    $title = I18n::getTranslation(__("Error"));
                    set_flash("error", $title, $msg);

                    header("location: ".LINK.$this->getClass()."/".__FUNCTION__."/".$url);
                    exit;
                }
            }

            if (!empty($post['ldap_group'])) {
                $db = Sgbd::sql(DB_DEFAULT);


                foreach ($post['ldap_group'] as $ldap_group) {
                    if (empty($ldap_group['name'])) {
                        $sql = "DELETE FROM `ldap_group` WHERE id_group =".$ldap_group['id'];
                        $db->sql_query($sql);
                    } else {
                        $sql = "REPLACE INTO `ldap_group` (`id_group`,`cn`) VALUES (".$ldap_group['id'].",'".$db->sql_real_escape_string($ldap_group['name'])."')";
                        $db->sql_query($sql);

                        $this->update_group($ldap_group['id']);
                    }
                }

                $msg   = I18n::getTranslation(__("LDAP groups has been updated"));
                $title = I18n::getTranslation(__("Sucess"));
                set_flash("success", $title, $msg);



                header("location: ".LINK.$this->getClass()."/".__FUNCTION__);
                exit;
            }
            //second form
        }


        if (empty($_GET['ldap'])) {
            $_GET['ldap']['url']                 = LDAP_URL;
            $_GET['ldap']['port']                = LDAP_PORT;
            $_GET['ldap']['bind_dn']             = LDAP_BIND_DN;
            $_GET['ldap']['root_dn']             = LDAP_ROOT_DN;
            $_GET['ldap']['root_dn_search']      = LDAP_ROOT_DN_SEARCH;
            $_GET['ldap']['bind_passwd']         = LDAP_BIND_PASSWD;
            $_GET['ldap']['bind_passwd_confirm'] = LDAP_BIND_PASSWD;
            $_GET['ldap']['check']               = LDAP_CHECK;
        }

        $data['check_server'] = $this->testLdap($_GET['ldap']['url'], $_GET['ldap']['port']);

        $data['check_credential'] = $this->testLdapCredential($_GET['ldap']['url'], $_GET['ldap']['port'], $_GET['ldap']['bind_dn'], $_GET['ldap']['bind_passwd']);

        if ($data['check_credential'] === true) {
            //test ldap
            $ds = ldap_connect(LDAP_URL, LDAP_PORT);  // doit être un serveur LDAP valide !
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
            $r  = ldap_bind($ds, LDAP_BIND_DN, LDAP_BIND_PASSWD);     // connexion anonyme, typique


            if ($r) {

                $results2 = ldap_search($ds, LDAP_ROOT_DN_SEARCH, "(objectcategory=group)");

                $entries2 = ldap_get_entries($ds, $results2);

                $ret = array();
                $this->recursiveArraySearchByKey($entries2, 'dn', $ret);

                $data['cn'] = array();

                foreach ($ret as $cn) {

                    $tmp = array();

                    $tmp['id']      = $cn;
                    $tmp['libelle'] = $cn;
                    $data['cn'][]   = $tmp;
                }
            }

            //debug($this->di['auth']);

            $db = Sgbd::sql(DB_DEFAULT);


            $sql = "select a.id, a.name, b.cn from `group` a
                LEFT JOIN ldap_group b ON a.id = b.id_group;";


            $res = $db->sql_query($sql);


            while ($ob = $db->sql_fetch_object($res)) {
                $tmp             = array();
                $tmp['id']       = $ob->id;
                $tmp['name']     = $ob->name;
                $tmp['cn']       = $ob->cn;
                $data['group'][] = $tmp;
            }
        }






        $this->set('data', $data);
    }

    public static function evaluateIndexPostRequest(array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::LDAP_INDEX_CSRF_SCOPE)) {
            return self::buildIndexPostOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $payload = self::normalizeIndexPostPayload($post);
        if ($payload === null) {
            return self::buildIndexPostOutcome(400, 'Invalid LDAP index payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'post' => $payload,
        ];
    }

    public static function normalizeIndexPostPayload(array $post): ?array
    {
        $payload = [];

        if (array_key_exists('ldap', $post)) {
            $ldap = self::normalizeLdapConfigPayload($post['ldap']);
            if ($ldap === null) {
                return null;
            }

            $payload['ldap'] = $ldap;
        }

        if (array_key_exists('ldap_group', $post)) {
            $ldapGroup = self::normalizeLdapGroupPayload($post['ldap_group']);
            if ($ldapGroup === null) {
                return null;
            }

            $payload['ldap_group'] = $ldapGroup;
        }

        if ($payload === []) {
            return null;
        }

        return $payload;
    }

    public static function normalizeLdapConfigPayload($source): ?array
    {
        if (!is_array($source)) {
            return null;
        }

        $ldap = array_fill_keys(self::LDAP_CONFIG_FIELDS, '');
        foreach ($source as $field => $value) {
            if (!is_string($field) || !in_array($field, self::LDAP_CONFIG_FIELDS, true)) {
                return null;
            }

            if ($field === 'check') {
                continue;
            }

            if (!is_scalar($value)) {
                return null;
            }

            $value = trim((string) $value);
            if (strlen($value) > self::LDAP_FIELD_MAX_LENGTH) {
                return null;
            }

            $ldap[$field] = $value;
        }

        $ldap['check'] = array_key_exists('check', $source) ? 'on' : 'off';

        return $ldap;
    }

    public static function normalizeLdapGroupPayload($source): ?array
    {
        if (!is_array($source)) {
            return null;
        }

        $groups = [];
        foreach ($source as $ldapGroup) {
            if (!is_array($ldapGroup)) {
                return null;
            }

            $id = self::normalizeLdapGroupId($ldapGroup['id'] ?? null);
            $name = self::normalizeLdapGroupName($ldapGroup['name'] ?? '');
            if ($id === null || $name === null) {
                return null;
            }

            $groups[] = [
                'id' => $id,
                'name' => $name,
            ];
        }

        return $groups;
    }

    public static function redactLdapSecrets(array $post): array
    {
        $redacted = $post;
        foreach (['bind_passwd', 'bind_passwd_confirm'] as $field) {
            if (isset($redacted['ldap'][$field])) {
                $redacted['ldap'][$field] = '[redacted]';
            }
        }

        return $redacted;
    }

    private static function normalizeLdapGroupId($value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $id = trim((string) $value);
        if (!ctype_digit($id) || (int) $id < 1) {
            return null;
        }

        return (int) $id;
    }

    private static function normalizeLdapGroupName($value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $name = trim((string) $value);
        if (strlen($name) > self::LDAP_FIELD_MAX_LENGTH) {
            return null;
        }

        return $name;
    }

    private static function buildIndexPostOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'post' => null,
        ];
    }

    private static function sendIndexError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Handle ldap state through `testLdap`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $url Input value for `url`.
 * @phpstan-param mixed $url
 * @psalm-param mixed $url
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @return mixed Returned value for testLdap.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::testLdap()
 * @example /fr/ldap/testLdap
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function testLdap($url, $port)
    {
        $fp = @fsockopen($url, $port, $errno, $errstr, 1);
        if (!$fp) {
            return "$errstr ($errno)";
        } else {
            return true;
        }
    }

/**
 * Handle ldap state through `postToGet`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $post Input value for `post`.
 * @phpstan-param mixed $post
 * @psalm-param mixed $post
 * @param mixed $exclude Input value for `exclude`.
 * @phpstan-param mixed $exclude
 * @psalm-param mixed $exclude
 * @return mixed Returned value for postToGet.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::postToGet()
 * @example /fr/ldap/postToGet
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function postToGet($post, $exclude = array())
    {
        $ret = array();
        $this->appendPostToGet($post, '', $exclude, $ret);

        return implode('/', array_filter($ret));
    }

    private function appendPostToGet($post, $prefix, $exclude, &$ret)
    {
        foreach ($post as $key => $val) {
            $key = (string) $key;
            $way = $prefix === '' ? $key : $prefix.':'.$key;
            if (is_array($val)) {
                $this->appendPostToGet($val, $way, $exclude, $ret);
                continue;
            }

            if (in_array($key, $exclude, true)) {
                continue;
            }

            $ret[] = $way.':'.urlencode((string) $val);
        }
    }

/**
 * Handle ldap state through `testLdapCredential`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $url Input value for `url`.
 * @phpstan-param mixed $url
 * @psalm-param mixed $url
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @param mixed $bind_dn Input value for `bind_dn`.
 * @phpstan-param mixed $bind_dn
 * @psalm-param mixed $bind_dn
 * @param mixed $bind_passwd Input value for `bind_passwd`.
 * @phpstan-param mixed $bind_passwd
 * @psalm-param mixed $bind_passwd
 * @return mixed Returned value for testLdapCredential.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::testLdapCredential()
 * @example /fr/ldap/testLdapCredential
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function testLdapCredential($url, $port, $bind_dn, $bind_passwd)
    {
        if ($this->testLdap($url, $port)) {
            $ds = ldap_connect(LDAP_URL, LDAP_PORT);  // doit être un serveur LDAP valide !
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

            if (empty(LDAP_BIND_DN)) {
                return false;
            }

            $r = @ldap_bind($ds, LDAP_BIND_DN, LDAP_BIND_PASSWD);     // connexion anonyme, typique

            if ($r) {
                return true;
            } else {
                $ret = ldap_error($ds)." (".ldap_errno($ds).")";
                return $ret;
            }
        }
    }

/**
 * Handle ldap state through `putUl`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $error Input value for `error`.
 * @phpstan-param mixed $error
 * @psalm-param mixed $error
 * @return mixed Returned value for putUl.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::putUl()
 * @example /fr/ldap/putUl
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function putUl($error)
    {
        $ret = "<ul>";
        foreach ($error as $msg) {
            $ret .= "<li>".__($msg)."</li>";
        }
        $ret .= "</ul>";

        return $ret;
    }

/**
 * Prepare ldap state through `before`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for before.
 * @phpstan-return void
 * @psalm-return void
 * @see self::before()
 * @example /fr/ldap/before
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function before($param)
    {
        $logger       = new Logger('ldap');
        $file_log     = LOG_FILE;
        $handler      = new StreamHandler($file_log, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
    }

/**
 * Handle ldap state through `log`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $level Input value for `level`.
 * @phpstan-param mixed $level
 * @psalm-param mixed $level
 * @param mixed $type Input value for `type`.
 * @phpstan-param mixed $type
 * @psalm-param mixed $type
 * @param mixed $msg Input value for `msg`.
 * @phpstan-param mixed $msg
 * @psalm-param mixed $msg
 * @return void Returned value for log.
 * @phpstan-return void
 * @psalm-return void
 * @see self::log()
 * @example /fr/ldap/log
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function log($level, $type, $msg)
    {

        if (IS_CLI) {
            $this->logger->{$level}('['.$type.'][pid:'.getmypid().'] "'.$msg.'" '.__("by").' [CLI]');
        } else {
            $this->logger->{$level}('['.$type.'][pid:'.getmypid().'] "'.$msg.'" '.__("by").' '
                .$this->di['auth']->getUser()->firstname." ".$this->di['auth']->getUser()->name." (id:".$this->di['auth']->getUser()->id.")");
        }
    }
    /*
     * A remplacer par le fait qu'un user peut avoir plusieurs group et qu'il faut tout tester
     *
     */

    private function update_group($id_group)
    {


        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT id_group, cn FROM `ldap_group`";
        $res = $db->sql_query($sql);

        $cn = array();
        while ($ob = $db->sql_fetch_object($res)) {
            $cn[$ob->id_group] = $ob->cn;
        }


        $sql = "SELECT * FROM `user_main` WHERE is_ldap = 1 and id_group = ".$id_group;
        $res = $db->sql_query($sql);



        $tree  = $this->di['acl']->obtenirHierarchie();
        $alias = $this->di['acl']->getAlias();
        $alias = array_flip($alias);

        while ($ob = $db->sql_fetch_object($res)) {



            $ds = ldap_connect(LDAP_URL, LDAP_PORT);  // doit être un serveur LDAP valide !
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
            $r  = ldap_bind($ds, LDAP_BIND_DN, LDAP_BIND_PASSWD);     // connexion anonyme, typique

            if ($r) {
                $login = isset($ob->login) ? (string) $ob->login : '';
                if ($login === '') {
                    continue;
                }

                $escapedLogin = self::escapeLdapFilterValue($login);
                $results = ldap_search($ds, LDAP_ROOT_DN, "(samaccountname={$escapedLogin})", array("memberof"));
                if ($results === false) {
                    continue;
                }

                $entries = ldap_get_entries($ds, $results);
                if (!is_array($entries) || !isset($entries[0]['memberof']) || !is_array($entries[0]['memberof'])) {
                    continue;
                }

                $memberof = $entries[0]['memberof'];


                unset($memberof['count']);

                foreach ($memberof as $key => $value) {
                    $memberof[$key] = $value;
                }

                $resultat           = array_intersect($cn, $memberof);
                $id_group_available = array_keys($resultat);

                $id_group = 1;

                foreach ($tree as $levels) {
                    foreach ($levels as $level) {
                        if (in_array($alias[$level], $id_group_available)) {
                            $id_group = $alias[$level];
                        }
                    }
                }

                $sql = "UPDATE `user_main` SET `id_group`=".$id_group." WHERE id=".$ob->id.";";
                $db->sql_query($sql);
            }
        }
    }

/**
 * Handle ldap state through `change`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for change.
 * @phpstan-return void
 * @psalm-return void
 * @see self::change()
 * @example /fr/ldap/change
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function change()
    {
        $this->update_group(1);
        $this->update_group(2);
        $this->update_group(3);
        $this->update_group(4);
        $this->view = false;
    }
    /*
     *
     *
     * to move in ACL
     */

    public function show()
    {
        $this->view = false;
        $this->getHightestRank();
    }

/**
 * Handle ldap state through `obtenirRangLePlusHaut`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for obtenirRangLePlusHaut.
 * @phpstan-return void
 * @psalm-return void
 * @see self::obtenirRangLePlusHaut()
 * @example /fr/ldap/obtenirRangLePlusHaut
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function obtenirRangLePlusHaut()
    {
        $acl = $this->di['acl'];

        $data['alias'] = $acl->getAlias();
        $parsed        = parse_ini_file($acl->getPathIniFile(), true);

        $roles = $parsed['role'];

        unset($roles['add']);

        //debug($roles);
        $lowest = $this->getLowestRank($roles);

        $tree = $this->hierarchie($roles, $lowest);

        //debug($tree);
    }

/**
 * Handle ldap state through `OraganiseNiveau`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for OraganiseNiveau.
 * @phpstan-return void
 * @psalm-return void
 * @see self::OraganiseNiveau()
 * @example /fr/ldap/OraganiseNiveau
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function OraganiseNiveau()
    {
        $tree = $this->di['acl']->obtenirHierarchie();

        //debug($tree);
        //foreach($tree as )
    }

/**
 * Handle ldap state through `UpdateConfigFile`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $var Input value for `var`.
 * @phpstan-param mixed $var
 * @psalm-param mixed $var
 * @return void Returned value for UpdateConfigFile.
 * @phpstan-return void
 * @psalm-return void
 * @see self::UpdateConfigFile()
 * @example /fr/ldap/UpdateConfigFile
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function UpdateConfigFile($var)
    {

        //debug(self::LDAP_CONFIG);
        //echo "\n";


        $ldap = file_get_contents(self::LDAP_CONFIG);



        $error = array();

        foreach ($var as $key => $val) {
            $ldap_const = "LDAP_".strtoupper($key);




            preg_match_all("/define\(\"".preg_quote($ldap_const)."\"\,\s(.*)\);/", $ldap, $output_array);

            if (empty($output_array[1][0])) {
                continue;
            }



            if (!in_array($ldap_const, $this->without_quote)) {
                $new_value = '"'.str_replace('"', '', $val).'"';
            } else {
                //debug($val);

                $new_value = str_replace('"', '', $val);
            }



            switch ($ldap_const) {
                case "LDAP_URL":
                    if (empty($val)) {
                        $error[] = "The name of server LDAP is required.";
                    }
                    break;

                case "LDAP_BIND_PASSWD":
                    if (isset($var['bind_passwd_confirm'])) {
                        if ($val !== $var['bind_passwd_confirm']) {
                            $error[] = "The password are not the same.";
                        }
                    }
                    break;

                case 'LDAP_PORT':

                    if (empty($new_value)) {
                        $new_value = 398;
                    }
                    break;

                case 'LDAP_BIND_DN':
                    if (empty($val)) {
                        $error[] = "The bind DN is required.";
                    }
                    break;

                case 'LDAP_ROOT_DN':
                    if (empty($val)) {
                        $error[] = "The root DN is required.";
                    }
                    break;

                case 'LDAP_ROOT_DN_SEARCH':
                    if (empty($val)) {
                        $fallbackRootDnSearch = $var['root_dn_search'] ?? $var['ROOT_DN_SEARCH'] ?? '';
                        $new_value = '"'.str_replace('"', '', (string) $fallbackRootDnSearch).'"';
                    }
                    break;


                case 'LDAP_CHECK':


                    if ($new_value === "on" || $new_value === "1") {
                        $new_value = "true";
                    } else {
                        $new_value = "false";
                    }
                    break;
            }


            $replace = "define(\"".$ldap_const."\", ".$new_value.");";
            $search  = $output_array[0][0];

            //echo $search." ---> ".$replace."\n";

            $ldap = str_replace($search, $replace, $ldap);
        }


        //debug($error);


        file_put_contents(self::LDAP_CONFIG, $ldap);
    }
    /*
     *
     * ./glial ldap updateFromInstall config.json
     */

    public function updateFromInstall($param)
    {
        $this->layout_name = false;
        $this->view        = false;


        $filename = $param[0] ?? "";

        if (!empty($filename) && file_exists($filename)) {

            $config = $this->parseConfig($filename);

            $mapping = array("url" => "URL", "port" => "PORT", "bind dn" => "BIND_DN", "bind passwd" => "BIND_PASSWD",
                "user base" => "ROOT_DN", "group base" => "ROOT_DN_SEARCH", "enabled" => "CHECK");

            $search  = array_keys($mapping);
            $replace = array_values($mapping);

            if (empty($config['ldap'])) {


                return true;
            }

            $ldap = array();
            foreach ($config['ldap'] as $option => $val) {
                if (in_array($option, $search)) {
                    $ldap[$mapping[$option]] = $val;
                }
            }


            //var_dump($ldap['CHECK']);

            $this->UpdateConfigFile($ldap);


            // update link with group

            $db = Sgbd::sql(DB_DEFAULT);


            //debug($config['ldap']);

            if (!empty($config['ldap']['mapping group']) && count($config['ldap']['mapping group']) > 0) {

                //debug($config['ldap']['mapping group']);

                foreach ($config['ldap']['mapping group'] as $group => $val) {

                    $sql = "SELECT id from `group` where name= '".$group."'";
                    $res = $db->sql_query($sql);

                    while ($ob = $db->sql_fetch_object($res)) {
                        $sql = "REPLACE INTO `ldap_group` (`id_group`, `cn`) VALUES (".$ob->id.", '".$val."');";

                        $db->sql_query($sql);
                    }
                }
            }
        }
    }

/**
 * Handle ldap state through `parseConfig`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $configFile Input value for `configFile`.
 * @phpstan-param mixed $configFile
 * @psalm-param mixed $configFile
 * @return mixed Returned value for parseConfig.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::parseConfig()
 * @example /fr/ldap/parseConfig
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function parseConfig($configFile)
    {
        $config = json_decode(file_get_contents($configFile), true);
        return $config;
    }

/**
 * Handle ldap state through `requestLdap`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $command Input value for `command`.
 * @phpstan-param mixed $command
 * @psalm-param mixed $command
 * @return mixed Returned value for requestLdap.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::requestLdap()
 * @example /fr/ldap/requestLdap
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function requestLdap($command)
    {

        if (empty($_GET['ldap'])) {
            $_GET['ldap']['url']                 = LDAP_URL;
            $_GET['ldap']['port']                = LDAP_PORT;
            $_GET['ldap']['bind_dn']             = LDAP_BIND_DN;
            $_GET['ldap']['root_dn']             = LDAP_ROOT_DN;
            $_GET['ldap']['root_dn_search']      = LDAP_ROOT_DN_SEARCH;
            $_GET['ldap']['bind_passwd']         = LDAP_BIND_PASSWD;
            $_GET['ldap']['bind_passwd_confirm'] = LDAP_BIND_PASSWD;
            $_GET['ldap']['check']               = LDAP_CHECK;
        }

        $data['check_server'] = $this->testLdap($_GET['ldap']['url'], $_GET['ldap']['port']);

        $data['check_credential'] = $this->testLdapCredential($_GET['ldap']['url'], $_GET['ldap']['port'], $_GET['ldap']['bind_dn'], $_GET['ldap']['bind_passwd']);

        if ($data['check_credential'] === true) {

            //test ldap
            $ds = ldap_connect(LDAP_URL, LDAP_PORT);  // doit être un serveur LDAP valide !
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

            $r = ldap_bind($ds, LDAP_BIND_DN, LDAP_BIND_PASSWD);     // connexion anonyme, typique



            if ($r) {

                $escapedCommand = self::escapeLdapFilterValue((string) $command);
                $results2 = ldap_search($ds, LDAP_ROOT_DN_SEARCH, "(samaccountname={$escapedCommand})", array("memberof"));
                $entries2 = ldap_get_entries($ds, $results2);

                return $entries2;
            }
        }
    }

/**
 * Retrieve `getGroupFromUser`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for getGroupFromUser.
 * @phpstan-return void
 * @psalm-return void
 * @example getGroupFromUser(...);
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getGroupFromUser()
    {
        $data = array(
            'ldap_get_group_from_user_csrf_field' => Csrf::DEFAULT_FIELD,
            'ldap_get_group_from_user_csrf_token' => Csrf::issueToken($_SESSION, self::LDAP_GET_GROUP_FROM_USER_CSRF_SCOPE),
        );

        if (CsrfGuard::isPost($_SERVER)) {
            $postOutcome = self::evaluateGetGroupFromUserRequest($_POST, $_SERVER, $_SESSION);
            if ($postOutcome['status'] !== 200) {
                $this->view        = false;
                $this->layout_name = false;
                self::sendIndexError($postOutcome['status'], $postOutcome['body'], $postOutcome['headers']);
                return;
            }

            $user = $postOutcome['user'];
            $result = $this->requestLdap($user);
            $data['list'] = $result[0]['memberof'] ?? array();
            $data['user'] = $user;

            if (!empty($data['list']['count'])) {
                unset($data['list']['count']);
            }
        }


        $this->set('data', $data);
    }

    public static function evaluateGetGroupFromUserRequest(array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::LDAP_GET_GROUP_FROM_USER_CSRF_SCOPE)) {
            return self::buildGetGroupFromUserOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $user = self::normalizeGetGroupFromUserPayload($post);
        if ($user === null) {
            return self::buildGetGroupFromUserOutcome(400, 'Invalid LDAP user payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'user' => $user,
        ];
    }

    public static function normalizeGetGroupFromUserPayload(array $post): ?string
    {
        $value = $post['ldap']['user'] ?? null;
        if (!is_scalar($value)) {
            return null;
        }

        $user = trim((string) $value);
        if ($user === '' || strlen($user) > self::LDAP_USERNAME_MAX_LENGTH) {
            return null;
        }

        if (preg_match('/\A[A-Za-z0-9._@-]+\z/', $user) !== 1) {
            return null;
        }

        return $user;
    }

    public static function escapeLdapFilterValue(string $value): string
    {
        if (function_exists('ldap_escape')) {
            return ldap_escape($value, '', LDAP_ESCAPE_FILTER);
        }

        return addcslashes($value, "\\*()\0");
    }

    private static function buildGetGroupFromUserOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'user' => null,
        ];
    }
}
