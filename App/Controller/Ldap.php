<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\I18n\I18n;
//use \Glial\Ldap\Ldap as gg;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \Glial\Sgbd\Sgbd;


class Ldap extends Controller
{
    public $module_group          = "Ldap";
    public $method_administration = array('Ldap', "roles");
    public $without_quote         = array('LDAP_CHECK', 'LDAP_PORT');

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

        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (!empty($_POST['ldap']['url'])) {

                $error = array();

                $this->log("info", "POST", json_encode($_POST));

                if (isset($_POST['ldap']['check'])) {
                    $_POST['ldap']['check'] = 'on';
                } else {
                    $_POST['ldap']['check'] = 'off';
                }

                $url = $this->postToGet($_POST);

                if ($this->testLdap($_POST['ldap']['url'], $_POST['ldap']['port']) !== true) {
                    $error[] = "We cannot to connect to LDAP server.";
                }

                if ($this->testLdapCredential($_POST['ldap']['url'], $_POST['ldap']['port'], $_POST['ldap']['bind_dn'], $_POST['ldap']['bind_passwd']) !== true) {
                    $error[] = "Invalid credentials (Check Bind DN and/or Bind password).";
                }


                $err = $this->UpdateConfigFile($_POST['ldap']);

                $error = array_merge($err, $error);

                $check = $this->testLdapCredential($_POST['ldap']['url'], $_POST['ldap']['port'], $_POST['ldap']['bind_dn'], $_POST['ldap']['bind_passwd']);


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

            if (!empty($_POST['ldap_group'])) {
                $db = Sgbd::sql(DB_DEFAULT);


                foreach ($_POST['ldap_group'] as $ldap_group) {
                    if (empty($ldap_group['id']) || empty($ldap_group['name'])) {
                        $sql = "DELETE FROM `ldap_group` WHERE id_group =".$ldap_group['id'];
                        $db->sql_query($sql);
                    } else {
                        $sql = "REPLACE INTO `ldap_group` (`id_group`,`cn`) VALUES ('".$ldap_group['id']."','".$ldap_group['name']."')";
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

    private function testLdap($url, $port)
    {
        $fp = @fsockopen($url, $port, $errno, $errstr, 1);
        if (!$fp) {
            return "$errstr ($errno)";
        } else {
            return true;
        }
    }

    private function postToGet($post, $exclude = array())
    {
        global $ret, $way;

        foreach ($post as $key => $val) {
            if (is_array($val)) {
                $way .= trim(":".$key, ":");
                $this->postToGet($val, $exclude, array(), "");
            } else {
                $ret[] = $way.':'.$key.':'.urlencode($val);
            }
        }

        return implode('/', $ret);
    }

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

    private function putUl($error)
    {
        $ret = "<ul>";
        foreach ($error as $msg) {
            $ret .= "<li>".__($msg)."</li>";
        }
        $ret .= "</ul>";

        return $ret;
    }

    public function before($param)
    {
        $logger       = new Logger('ldap');
        $file_log     = LOG_FILE;
        $handler      = new StreamHandler($file_log, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);
        $this->logger = $logger;
    }

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
                $results = ldap_search($ds, LDAP_ROOT_DN, "(samaccountname=".$ob->login.")", array("memberof"));

                $entries = ldap_get_entries($ds, $results);

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

    private function OraganiseNiveau()
    {
        $tree = $this->di['acl']->obtenirHierarchie();

        //debug($tree);
        //foreach($tree as )
    }

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
                    if (isset($_POST['ldap']['bind_passwd_confirm'])) {
                        if ($val !== $_POST['ldap']['bind_passwd_confirm']) {
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
                        $val = $_POST['ldap']['root_dn_search'];
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

    public function parseConfig($configFile)
    {
        $config = json_decode(file_get_contents($configFile), true);
        return $config;
    }

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

                $results2 = ldap_search($ds, LDAP_ROOT_DN_SEARCH, "(samaccountname={$command})", array("memberof"));
                $entries2 = ldap_get_entries($ds, $results2);

                return $entries2;
            }
        }
    }

    public function getGroupFromUser()
    {
        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            if (!empty($_POST['ldap']['user'])) {
                $result       = $this->requestLdap($_POST['ldap']['user']);
                $data['list'] = $result[0]['memberof'];


                $data['user'] = $_POST['ldap']['user'];

                if (!empty($data['list']['count'])) {
                    unset($data['list']['count']);
                }
            }
        }


        $this->set('data', $data);
    }
}