<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Cli\Color;
use \Glial\Security\Crypt\Crypt;
use \Glial\Synapse\Config;
use \Glial\Sgbd\Sgbd;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \App\Controller\Upgrade;


/****
 * userstat=ON
performance_schema=ON
performance-schema-instrument='statement/%=ON'
performance-schema-consumer-statements-digest=ON
innodb_monitor_enable=all

 */
class Install extends Controller
{
/**
 * Stores `$link` for link.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $link; /* link mysql server */

    public function out($msg, $type)
    {
        switch ($type) {
            case 'OK':
            case 'true':
                $status = Color::getColoredString("OK", "green");
                break;
            case 'KO':
            case 'false':
                $status = Color::getColoredString("KO", "red");
                break;
            case 'NA': $status = Color::getColoredString("!!", "blue");
                break;
        }

        $ret = $msg.str_repeat(".", 73 - strlen(Color::strip($msg)))." [ ".$status." ]".PHP_EOL;

        /*
          if (!empty($err)) {
          echo $ret;
          $this->onError();
          }
         */
        return $ret;
    }

/**
 * Handle install state through `onError`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for onError.
 * @phpstan-return void
 * @psalm-return void
 * @see self::onError()
 * @example /fr/install/onError
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function onError()
    {

        echo PHP_EOL."To understand what happen : ".Color::getColoredString("glial/tmp/log/error_php.log", "cyan").PHP_EOL;
        echo "To resume the setup : ".Color::getColoredString("php composer.phar update", "cyan").PHP_EOL;
        exit(10);
    }

/**
 * Handle install state through `cmd`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $cmd Input value for `cmd`.
 * @phpstan-param mixed $cmd
 * @psalm-param mixed $cmd
 * @param mixed $msg Input value for `msg`.
 * @phpstan-param mixed $msg
 * @psalm-param mixed $msg
 * @return mixed Returned value for cmd.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::cmd()
 * @example /fr/install/cmd
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function cmd($cmd, $msg)
    {
        $code_retour = 0;

        ob_start();
        passthru($cmd, $code_retour);

        if ($code_retour !== 0) {
            $fine   = 'KO';
            ob_end_flush();
            $return = false;
        } else {
            $fine   = 'OK';
            ob_end_clean();
            $return = true;
        }

        $this->displayResult($msg, $fine);

        return $return;
    }

/**
 * Handle install state through `displayResult`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $msg Input value for `msg`.
 * @phpstan-param mixed $msg
 * @psalm-param mixed $msg
 * @param mixed $fine Input value for `fine`.
 * @phpstan-param mixed $fine
 * @psalm-param mixed $fine
 * @return void Returned value for displayResult.
 * @phpstan-return void
 * @psalm-return void
 * @see self::displayResult()
 * @example /fr/install/displayResult
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    function displayResult($msg, $fine)
    {
        echo $this->out(Color::getColoredString("[".date("Y-m-d H:i:s")."] ", "purple").$msg, $fine);
    }

/**
 * Handle install state through `anonymous`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $function Input value for `function`.
 * @phpstan-param mixed $function
 * @psalm-param mixed $function
 * @param mixed $msg Input value for `msg`.
 * @phpstan-param mixed $msg
 * @psalm-param mixed $msg
 * @return void Returned value for anonymous.
 * @phpstan-return void
 * @psalm-return void
 * @see self::anonymous()
 * @example /fr/install/anonymous
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function anonymous($function, $msg)
    {
        list($fine, $message) = $function($msg);

        echo $this->out($message, $fine);
    }

/**
 * Render install state through `index`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/install/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index($param = array())
    {
        $this->view = false;
        
        echo "\n";
        echo SITE_LOGO;
        echo Color::getColoredString(SITE_NAME, "green")." version ".Color::getColoredString(SITE_VERSION, "yellow")." (".SITE_LAST_UPDATE.")\n";
        echo "Powered by Glial (https://github.com/Glial/Glial)\n";

        $this->generate_key();
        $filename = $param[0] ?? "";

        if (!empty($filename) && file_exists($filename)) {

            try {
                $config = $this->parseConfig($filename);
            }
            catch (\Exception $e) {
                trigger_error("Problem with Json parser (".$e->getMessage().")", E_USER_ERROR);
            }

            try{
                $server = $this->configMySQL($config);
            }
            catch (\Exception $e){
                trigger_error("Config problem with db.config.ini.php (".$e->getMessage().")", E_USER_ERROR);
            }
            
        } else {
            $this->cadre("Select MySQL server for PmaControl");
            $server = $this->testMysqlServer();

            $this->updateConfig($server);
            usleep(1000);
        }

        $config = new Config;
        $config->load(CONFIG);

        $db = $config->get("db");

        Sgbd::setConfig($db);

        $log = new Logger('Glial');

        $file_log = TMP.'log/glial.log';

        $handler = new StreamHandler($file_log, Logger::NOTICE);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $log->pushHandler($handler);

        Sgbd::setLogger($log);

        $this->installLanguage(Sgbd::sql(DB_DEFAULT));
        $this->importData($server);
        $this->updateCache();
        $this->cmd("echo 1", "Testing system & configuration");

        echo Color::getColoredString("\n".SITE_NAME." ".SITE_VERSION." has been successfully installed !\n", "green");
    }

/**
 * Handle install state through `prompt`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $test Input value for `test`.
 * @phpstan-param mixed $test
 * @psalm-param mixed $test
 * @return mixed Returned value for prompt.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::prompt()
 * @example /fr/install/prompt
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function prompt($test)
    {
        echo $test;
        $handle = fopen("php://stdin", "r");
        $line   = fgets($handle);
        return $line;
    }

/**
 * Handle install state through `testMysqlServer`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return mixed Returned value for testMysqlServer.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::testMysqlServer()
 * @example /fr/install/testMysqlServer
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function testMysqlServer()
    {

        $good = false;
        do {
            echo "Name of connection into configuration/db.config.ini.php : [pmacontrol]\n";

            $hostname = trim($this->prompt('Hostname/IP of MySQL [default : 127.0.0.1] : '));
            $port     = trim($this->prompt('Port of MySQL        [default : 3306]      : '));

            if (empty($port)) {
                $port = 3306;
            }
            if (empty($hostname)) {
                $hostname = "127.0.0.1";
            }


            $ret = $this->testIpPort($hostname, $port);

            if ($ret === true) {
                $this->cmd("echo 1", "MySQL server : ".$hostname.":".$port." available");
                $good = true;
            } else {
                echo Color::getColoredString($ret['errstr']." (".$ret['errno'].")", "grey", "red")."\n";
                echo "MySQL server : ".$hostname.":".$port." -> ".Color::getColoredString("KO", "grey", "red")."\n";
                echo str_repeat("-", 80)."\n";
            }

            $this->cmd("echo 1", "MySQL server : ".$hostname.":".$port." available");
        } while ($good === false);

        //login & password mysql
        $good = false;

        do {
            echo "MySQL account on (".$hostname.":".$port.")\n";

            $user = readline('User     [default : root]    : ');
            $password = readline('Password [default : (empty)] : ');

            if (empty($user)) {
                $user = "root";
            }

            $ret = $this->testMySQL($hostname, $port, $user, $password);

            if ($ret === true) {
                $this->cmd("echo 1", "Login/password for MySQL's server");
                $good = true;
            } else {
                echo Color::getColoredString($ret, "grey", "red")."\n";
                echo str_repeat("-", 80)."\n";
            }

            sleep(1);
        } while ($good === false);

        //check TokuDB
        //

        $this->testVectorDB();

        /*
         *
         * On Redhat and Centos
         * Add line GRUB_CMDLINE_LINUX_DEFAULT="transparent_hugepage=never" to file /etc/default/grub

          Update grub (boot loader):
          grub2-mkconfig -o /boot/grub2/grub.cfg "$@"

          echo never > /sys/kernel/mm/transparent_hugepage/enabled
          echo never > /sys/kernel/mm/transparent_hugepage/defrag

         */



        //check Spider
        //

        //$this->testSpider();

        wrong_db:
        $good = false;
        do {
            echo "Name of database who will be used by PmaControl\n";

            $database = readline('Database     [default : pmacontrol]    : ');

            if (empty($database)) {
                $database = "pmacontrol";
            }

            $ret = $this->testDatabase($database);

            if ($ret === true) {
                $good = true;
                $this->cmd("echo 1", "Database's name");
            } else {
                echo Color::getColoredString($ret, "grey", "red")."\n";
                echo str_repeat("-", 80)."\n";
            }
        } while ($good === false);

        //create database


        $ret = $this->createDatabase($database);

        if ($ret === true) {


            $this->cmd("echo 1", 'The database "'.mysqli_real_escape_string($this->link, $database).'" has been created');
        } else {
            echo Color::getColoredString($ret, "black", "red")."\n";
            goto wrong_db;
            echo str_repeat("-", 80)."\n";
        }


        Crypt::$key = CRYPT_KEY;

        $passwd = Crypt::encrypt($password);

        $mysql['hostname'] = $hostname;
        $mysql['port']     = $port;
        $mysql['user']     = $user;
        $mysql['password'] = $passwd;
        $mysql['database'] = $database;
        $mysql['database'] = 0;
        return $mysql;
    }

/**
 * Handle install state through `cadre`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param mixed $text Input value for `text`.
 * @phpstan-param mixed $text
 * @psalm-param mixed $text
 * @param mixed $elem Input value for `elem`.
 * @phpstan-param mixed $elem
 * @psalm-param mixed $elem
 * @return void Returned value for cadre.
 * @phpstan-return void
 * @psalm-return void
 * @see self::cadre()
 * @example /fr/install/cadre
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function cadre($text, $elem = '#')
    {
        echo str_repeat($elem, 80)."\n";
        echo $elem.str_repeat(' ', ceil((80 - strlen($text) - 2) / 2)).$text.str_repeat(' ', floor((80 - strlen($text) - 2) / 2)).$elem."\n";
        echo str_repeat($elem, 80)."\n";
    }

/**
 * Handle install state through `importData`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $server Input value for `server`.
 * @phpstan-param mixed $server
 * @psalm-param mixed $server
 * @return void Returned value for importData.
 * @phpstan-return void
 * @psalm-return void
 * @see self::importData()
 * @example /fr/install/importData
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function importData($server)
    {
        //$path = ROOT."/sql/*.sql";
        $path = ROOT."/sql/full/pmacontrol.sql";

        Crypt::$key         = CRYPT_KEY;
        $server['password'] = Crypt::decrypt($server['password']);

        foreach (glob($path) as $filename) {
            //echo "$filename size ".filesize($filename)."\n";
            $cmd = "mysql -h ".$server["hostname"]." -u ".$server['user']." -P ".$server['port']." -p'".$server['password']."' ".$server['database']." < ".$filename."";
            $ret = $this->cmd($cmd, "Loading ".pathinfo($filename)['basename']);

            if ($ret === false) {
                exit(1);
            }
        }
    }

/**
 * Update install state through `updateConfig`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $server Input value for `server`.
 * @phpstan-param mixed $server
 * @psalm-param mixed $server
 * @return void Returned value for updateConfig.
 * @phpstan-return void
 * @psalm-return void
 * @see self::updateConfig()
 * @example /fr/install/updateConfig
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function updateConfig($server)
    {
        //update DB config

        $config = "
;[name_of_connection] => will be acceded in framework with \Sgbd::sql('name_of_connection')->method()
;driver => list of SGBD avaible {mysql, pgsql, sybase, oracle}
;hostname => server_name of ip of server SGBD (better to put localhost or real IP)
;user => user who will be used to connect to the SGBD
;password => password who will be used to connect to the SGBD
;database => database / schema witch will be used to access to datas

[pmacontrol]
driver=mysql
hostname=".$server["hostname"]."
user=".$server['user']."
password='".$server['password']."'
crypted='1'
database=".$server['database']."
ssl=".($server['is_ssl'] ?? 0)."";

        $fp = fopen(CONFIG."/db.config.ini.php", 'w');
        fwrite($fp, $config);
        fclose($fp);

        $this->cmd("echo 1", "Generate config file for DB");
    }

/**
 * Update install state through `updateCache`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for updateCache.
 * @phpstan-return void
 * @psalm-return void
 * @see self::updateCache()
 * @example /fr/install/updateCache
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function updateCache()
    {
        $this->cmd("php glial administration admin_index_unique", "Generating DDL cash for index");
        $this->cmd("php glial administration admin_table", "Generating DDL cash for databases");
        $this->cmd("php glial administration generate_model", "Making model with reverse engineering of databases");
    }

/**
 * Create install state through `createAdmin`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for createAdmin.
 * @phpstan-return void
 * @psalm-return void
 * @see self::createAdmin()
 * @example /fr/install/createAdmin
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function createAdmin($param = array())
    {
        $this->view = false;

        /*
         * email
         * first name
         * last name
         * country
         * city
         * password
         * password repeat
         */

        $db = Sgbd::sql(DB_DEFAULT);

        $filename = $param[0] ?? "";

        if (!empty($filename) && file_exists($filename)) {

            $config = $this->parseConfig($filename);
            $admin  = $config['user']['Super administrator'][0];

            $email     = $admin['email'];
            $login     = $admin['login'];
            $pwd       = $admin['password'];
            $firstname = $admin['firstname'];
            $lastname  = $admin['lastname'];

            $sql = "select id from geolocalisation_country where libelle = '".$db->sql_real_escape_string($admin['country'])."'";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $id_country = $ob->id;
            }


            $sql = "SELECT id FROM geolocalisation_city where libelle = '".$db->sql_real_escape_string($admin['city'])."' AND id_geolocalisation_country=".$id_country." ORDER BY libelle";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $id_city = $ob->id;
            }
        } else {


            createUser:
            $this->cadre("create administrator user");

            $email_is_valid = false;
            do {
                $email = readline('Your email : ');

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->displayResult("This email considered as valid !", "KO");
                } else {
                    $this->displayResult("This email considered as valid !", "OK");

                    $email_is_valid = true;
                    /*
                      $domain = explode('@', $email)[1];
                      if (checkdnsrr($domain, 'MX')) {

                      $this->displayResult("This MX records exists !", "OK");
                      $email_is_valid = true;
                      } else {
                      $this->displayResult("This MX records exists !", "KO");
                      }
                     */
                }
            } while ($email_is_valid === false);

            //login
            $login = readline('Your login : ');

            //first name
            $firstname = readline('Your firstname : ');

            //last name
            $lastname = readline('Your lastname : ');

            //country
            $sql = "SELECT libelle FROM geolocalisation_country where libelle != '' ORDER BY libelle";
            $DB  = Sgbd::sql(DB_DEFAULT);

            $res     = $db->sql_query($sql);
            $country = [];
            while ($ob      = $db->sql_fetch_object($res)) {
                $country[] = $ob->libelle;
            }

            do {
                $country2 = readline('Your country [First letter in upper case, then tab for help] : ');

                $sql = "select id from geolocalisation_country where libelle = '".$db->sql_real_escape_string($country2)."'";
                $res = $db->sql_query($sql);

                if ($db->sql_num_rows($res) == 1) {
                    $ob         = $db->sql_fetch_object($res);
                    $id_country = $ob->id;
                    $this->displayResult("Country found in database !", "OK");
                } else {
                    $this->displayResult("Country found in database !", "KO");
                }
            } while ($db->sql_num_rows($res) != 1);

            //city
            $sql = "SELECT libelle FROM geolocalisation_city where id_geolocalisation_country = '".$id_country."' ORDER BY libelle";
            $db  = Sgbd::sql(DB_DEFAULT);

            $res  = $db->sql_query($sql);
            $city = [];
            while ($ob   = $db->sql_fetch_object($res)) {
                $city[] = $ob->libelle;
            }

            do {
                $city2 = readline('Your city [First letter in upper case, then tab for help] : ');

                $sql = "select id from geolocalisation_city where libelle = '".$db->sql_real_escape_string($city2)."'";
                $res = $db->sql_query($sql);

                if ($db->sql_num_rows($res) == 1) {
                    $ob      = $db->sql_fetch_object($res);
                    $id_city = $ob->id;
                    $this->displayResult("City found in database !", "OK");
                } else {
                    $this->displayResult("City found in database !", "KO");
                }
            } while ($db->sql_num_rows($res) != 1);

            $good = false;
            do {
                $pwd  = readline('Password : ');
                $pwd2 = readline('Password (repeat) : ');

                if (!empty($pwd) && $pwd === $pwd2) {
                    $good = true;
                    $this->displayResult("The passwords must be the same & not empty", "OK");
                } else {
                    $this->displayResult("The passwords must be the same & not empty", "KO");
                }
            } while ($good !== true);
        }



        $ip = trim(@file_get_contents("http://icanhazip.com"));

        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = "127.0.0.1";
        }


        $data['user_main']['is_valid'] = 1;
        $data['user_main']['email']    = $email;

        if (empty($login)) {
            $login = $email;
        }

        $data['user_main']['login']    = $login;
        $data['user_main']['password'] = \Glial\Auth\Auth::hashPassword($login, $pwd);

        //to set uppercase to composed name like 'Jean-Louis'
        $firstname = str_replace("-", " - ", $firstname);
        $firstname = mb_convert_case($firstname, MB_CASE_TITLE, "UTF-8");

        $data['user_main']['firstname'] = str_replace(" - ", "-", $firstname);

        $data['user_main']['name']                       = mb_convert_case($lastname, MB_CASE_UPPER, "UTF-8");
        $data['user_main']['ip']                         = $ip;
        $data['user_main']['date_created']               = date('Y-m-d H:i:s');
        $data['user_main']['id_group']                   = 4; // 4 = super admin
        $data['user_main']['id_geolocalisation_country'] = $id_country;
        $data['user_main']['id_geolocalisation_city']    = $id_city;
        $data['user_main']['id_client']                  = 1;
        $data['user_main']['date_last_login']            = date('Y-m-d H:i:s');
        $data['user_main']['date_last_connected']        = date('Y-m-d H:i:s');
        $data['user_main']['key_auth']                   = '';

        $id_user = $db->sql_save($data);

        if ($id_user) {
            $this->displayResult("Admin account successfully created", "OK");
        } else {

            print_r($data);
            $error = $db->sql_error();
            print_r($error);

            $this->displayResult("Admin account successfully created", "KO");

            goto createUser;
        }

        echo Color::getColoredString("\nAdministrator successfully created !\n", "green");

        //$ip_list = shell_exec('ifconfig -a | grep "inet ad" | cut -d ":" -f 2 | cut -d " " -f 1');
	$ip_list = trim(shell_exec("hostname -I"));

	$ips = explode(" ", $ip_list);
        //$ips = explode("\n", $ip_list);

        foreach ($ips as $ip) {
            if (empty($ip)) {
                continue;
            }

            echo "You can connect to the application on this url : ".Color::getColoredString("http://".$ip.WWW_ROOT, "yellow")."\n";
        }


        echo "You can connect to the application on this url : ".Color::getColoredString("http://".gethostname().WWW_ROOT, "yellow")."\n";
    }

/**
 * Create install state through `createOrganisation`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for createOrganisation.
 * @phpstan-return void
 * @psalm-return void
 * @see self::createOrganisation()
 * @example /fr/install/createOrganisation
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function createOrganisation($param)
    {

        $filename = $param[0] ?? "";

        $this->view = false;
        $db         = Sgbd::sql(DB_DEFAULT);

        if (!empty($filename) && file_exists($filename)) {
            $config = $this->parseConfig($filename);
            $orgas  = $config['organization'];
        } else {

            createOrganization:
            $this->cadre("create organization");

            do {
                $organization = readline('Your Organization : ');
            } while (strlen($organization) < 3);

            $orgas = array($organization);
        }


        foreach ($orgas as $oraga) {
            $sql = "INSERT INTO client (`libelle`,`date`) VALUES ('".$oraga."', '".date('Y-m-d H:i:s')."')";
            $db->sql_query($sql);
        }
    }

/**
 * Handle install state through `rand_char`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $length Input value for `length`.
 * @phpstan-param mixed $length
 * @psalm-param mixed $length
 * @return mixed Returned value for rand_char.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::rand_char()
 * @example /fr/install/rand_char
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function rand_char($length)
    {
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= chr(mt_rand(33, 126));
        }
        return $random;
    }

/**
 * Handle install state through `generate_key`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for generate_key.
 * @phpstan-return void
 * @psalm-return void
 * @see self::generate_key()
 * @example /fr/install/generate_key
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function generate_key()
    {

        $key = str_replace("'", "", $this->rand_char(256));

        $data = "<?php

if (! defined('CRYPT_KEY'))
{
    define('CRYPT_KEY', '".$key."');
}
";

        $path = "configuration/crypt.config.php";
        $msg  = "Generate key for encryption";

        if (!file_exists($path)) {
            file_put_contents($path, $data);
            $this->displayResult($msg, "OK");
            require_once $path;
        } else {
            $this->displayResult($msg, "NA");
        }
    }

/**
 * Handle install state through `installLanguage`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $db Input value for `db`.
 * @phpstan-param mixed $db
 * @psalm-param mixed $db
 * @return void Returned value for installLanguage.
 * @phpstan-return void
 * @psalm-return void
 * @see self::installLanguage()
 * @example /fr/install/installLanguage
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function installLanguage($db)
    {

        \Glial\I18n\I18n::injectDb($db);
        //\Glial\I18n\I18n::install();
    }

/**
 * Handle install state through `parseConfig`.
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
 * @example /fr/install/parseConfig
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function parseConfig($configFile)
    {
        //debug($configFile);

        $config = json_decode(file_get_contents($configFile), true);
        //$config = Yaml::parse(file_get_contents($configFile));
        //$config = yaml_parse_file($configFile);
        //debug($config);

        return $config;
    }

/**
 * Handle install state through `testIpPort`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $hostname Input value for `hostname`.
 * @phpstan-param mixed $hostname
 * @psalm-param mixed $hostname
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @return mixed Returned value for testIpPort.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::testIpPort()
 * @example /fr/install/testIpPort
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function testIpPort($hostname, $port)
    {

        $fp = @fsockopen($hostname, $port, $errno, $errstr, 30);
        if (!$fp) {
            trigger_error("problem fsockopen $hostname:$port - $errstr($errno)", E_USER_ERROR);
            
            $ret['errno']  = $errno;
            $ret['errstr'] = $errstr;

            return $ret;
        } else {

            fclose($fp);
            return true;
        }
    }

/**
 * Handle install state through `testMySQL`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $hostname Input value for `hostname`.
 * @phpstan-param mixed $hostname
 * @psalm-param mixed $hostname
 * @param mixed $port Input value for `port`.
 * @phpstan-param mixed $port
 * @psalm-param mixed $port
 * @param mixed $user Input value for `user`.
 * @phpstan-param mixed $user
 * @psalm-param mixed $user
 * @param mixed $password Input value for `password`.
 * @phpstan-param mixed $password
 * @psalm-param mixed $password
 * @return mixed Returned value for testMySQL.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::testMySQL()
 * @example /fr/install/testMySQL
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function testMySQL($hostname, $port, $user, $password)
    {

        $this->link = mysqli_connect($hostname.":".$port, $user, trim($password));

        //mysqli_set_charset($this->link, "utf8mb4");

        mysqli_query($this->link, "SET NAMES utf8");



        if ($this->link) {
            return true;
        } else {
            return 'Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error();
        }
    }

/**
 * Handle install state through `testVectorDB`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for testVectorDB.
 * @phpstan-return void
 * @psalm-return void
 * @see self::testVectorDB()
 * @example /fr/install/testVectorDB
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function testVectorDB()
    {
        if (empty($this->link)) {
            //throw new \Exception("MySQL link not defined");
        }



        $sql = "select count(1) as cpt from information_schema.engines where engine in ('ROCKSDB') and (SUPPORT = 'YES' OR SUPPORT = 'DEFAULT');";

        $res = mysqli_query($this->link, $sql);

        while ($ob = mysqli_fetch_object($res)) {

            if ($ob->cpt < "1") {


                echo Color::getColoredString('Engine "ROCKSDB" is not installed yet', "grey", "red")."\n";
                echo "To install ROCKSDB :\n";
                echo "\t- apt install mariadb-plugin-rocksdb\n";

                exit(2);
            }
        }
    }

/**
 * Handle install state through `testSpider`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for testSpider.
 * @phpstan-return void
 * @psalm-return void
 * @see self::testSpider()
 * @example /fr/install/testSpider
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function testSpider()
    {
        $sql = "select count(1) as cpt from information_schema.engines where engine = 'SPIDER' and (SUPPORT = 'YES' OR SUPPORT = 'DEFAULT');";

        $res = mysqli_query($this->link, $sql);

        while ($ob = mysqli_fetch_object($res)) {

            if ($ob->cpt !== "1") {
                echo Color::getColoredString('Engine "SPIDER" is not installed yet', "grey", "red")."\n";

                echo "To install Spider, run the install_spider.sql script, located in the share directory, for example, from the command line:\n\n";
                echo "\tmysql -uroot -p < /usr/share/mysql/install_spider.sql\n";
                echo "\n";
                echo "or, from within mysql\n\n";
                echo "\tsource /usr/share/mysql/install_spider.sql\n\n";

                exit(2);
            }
        }
    }

/**
 * Handle install state through `testDatabase`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $database Input value for `database`.
 * @phpstan-param array<int|string,mixed> $database
 * @psalm-param array<int|string,mixed> $database
 * @return mixed Returned value for testDatabase.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::testDatabase()
 * @example /fr/install/testDatabase
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function testDatabase($database)
    {
        $sql    = "SELECT count(1) as cpt FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".mysqli_real_escape_string($this->link, $database)."'";
        $result = mysqli_query($this->link, $sql);

        $ob = mysqli_fetch_object($result);

        if ($ob->cpt == "1") {
            return "Database already exists";
        }

        return true;


        exit;
    }

/**
 * Create install state through `createDatabase`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int|string,mixed> $database Input value for `database`.
 * @phpstan-param array<int|string,mixed> $database
 * @psalm-param array<int|string,mixed> $database
 * @return mixed Returned value for createDatabase.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::createDatabase()
 * @example /fr/install/createDatabase
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function createDatabase($database)
    {

        $sql = "CREATE DATABASE IF NOT EXISTS ".mysqli_real_escape_string($this->link, $database)."";
        $res = mysqli_query($this->link, $sql);

        if ($res) {
            return true;
        } else {
            return 'The database "'.mysqli_real_escape_string($this->link, $database).'" couldn\'t be created';
        }
    }

/**
 * Handle install state through `configMySQL`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $config Input value for `config`.
 * @phpstan-param mixed $config
 * @psalm-param mixed $config
 * @return mixed Returned value for configMySQL.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @throws \Throwable When the underlying operation fails.
 * @see self::configMySQL()
 * @example /fr/install/configMySQL
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function configMySQL($config)
    {

        $error = array();

        $ret = $this->testIpPort($config['mysql']['ip'], $config['mysql']['port']);

        if ($ret !== true) {
            throw new \Exception($ret['errstr']." (".$ret['errno'].")");
        }
        $this->cmd("echo 1", "MySQL server : ".$config['mysql']['ip'].":".$config['mysql']['port']." available");

        $this->testMySQL($config['mysql']['ip'], $config['mysql']['port'], $config['mysql']['user'], $config['mysql']['password']);
        if ($ret !== true) {
            throw new \Exception($ret);
        }

        $this->testVectorDB();
        //$this->testSpider();

        $ret = $this->testDatabase($config['mysql']['database']);
        if ($ret !== true && $ret !== "Database already exists") {
            throw new \Exception($ret);
        }

        $this->createDatabase($config['mysql']['database']);

        Crypt::$key = CRYPT_KEY;

        $config['mysql']['password'] = Crypt::encrypt($config['mysql']['password']);
        $config['mysql']['hostname'] = $config['mysql']['ip'];

        $this->updateConfig($config['mysql']);

        return $config['mysql'];
    }

/**
 * Handle install state through `webroot`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for webroot.
 * @phpstan-return void
 * @psalm-return void
 * @see self::webroot()
 * @example /fr/install/webroot
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function webroot($param)
    {
        $filename = $param[0] ?? "";

        if (!empty($filename) && file_exists($filename)) {


            $config = $this->parseConfig($filename);

            $webroot = "<?php

/*
 * if you use a direrct DNS set : define('WWW_ROOT', \"/\");
 * if you dev in local or other use : define('WWW_ROOT', \"/path_to_the_final_directory/\");
 * example : http://127.0.0.1/directory/myapplication/ => define('WWW_ROOT', \"/directory/myapplication/\");
 * Don't forget the final \"/\"
 */


if (! defined('WWW_ROOT'))
{
    define('WWW_ROOT', \"".$config['webroot']."\");
}";
            file_put_contents("configuration/webroot.config.php", $webroot);
        }
    }

/**
 * Update install state through `updateVersion`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for updateVersion.
 * @phpstan-return void
 * @psalm-return void
 * @see self::updateVersion()
 * @example /fr/install/updateVersion
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function updateVersion()
    {
        //echo "UPDATE site.config.php";
        shell_exec("cp -a config_sample/site.config.php configuration/site.config.php");

        $php = explode(" ", shell_exec("whereis php"))[1];
        $cmd = $php." ".GLIAL_INDEX." administration all";

        shell_exec($cmd);

        $filename = TMP."acl/acl.ser";

        if (file_exists($filename)) {
            unlink($filename);
        }

        $upgrade = new Upgrade();

        $upgrade->updateConfig(array());


        //load DB and compare => upgradet
    }

/**
 * Update install state through `update`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for update.
 * @phpstan-return void
 * @psalm-return void
 * @see self::update()
 * @example /fr/install/update
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function update($param)
    {



        shell_exec("cd ".ROOT." && chown www-data. -R tmp/");
        shell_exec("cd ".ROOT." && chown www-data. -R configuration/");
    }
}
//https://www.programmez.com/actualites/yak-pro-php-obfuscator-cachez-ce-code-que-je-ne-saurais-voir-23454
