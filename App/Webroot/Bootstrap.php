<?php
/**
 * Glial Bootstrap.
 *
 * Handles loading of core files needed on every request
 *
 * PHP versions 5.5
 *
 * GLIALE(tm) : Rapid Development Framework (http://gliale.com)
 * Copyright 2008-2012, Esysteme Software Foundation, Inc. (http://www.esysteme.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2007-2010, Esysteme Software Foundation, Inc. (http://www.esysteme.com)
 * @link          http://www.glial.com GLIALE(tm) Project
 * @package       gliale
 * @subpackage    gliale.app.webroot
 * @since         Gliale(tm) v 0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
header("Charset: UTF-8");

ini_set('error_log', TMP.'log'.DS.'error_php.log');
ini_set('APACHE_LOG_DIR', TMP.'log'.DS);

//tput cols tells you the number of columns.
//tput lines tells you the number of rows.

use \Glial\Synapse\Config;
use \Glial\Debug\Debug as DebugGlial;
use \Glial\Synapse\FactoryController;
use \Glial\I18n\I18n;
use \Glial\Acl\Acl;
use \Glial\Auth\Auth;
use \Glial\Sgbd\Sgbd;
use \Glial\Synapse\Javascript;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use Glial\Synapse\Glial;

$TIME_START = microtime(true);


require ROOT.DS.'vendor/autoload.php';

if (!IS_CLI) {
    session_start();
}

$config = new Config;
$config->load(CONFIG);
FactoryController::addDi("config", $config);

$log = new Logger('Glial');

$file_log = LOG_FILE;

$handler = new StreamHandler($file_log, Logger::DEBUG);
$handler->setFormatter(new LineFormatter(null, null, false, true));
$log->pushHandler($handler);

FactoryController::addDi("log", $log);

if (!IS_CLI) {
    $developer = $config->get("developer");
    if (in_array($_SERVER['REMOTE_ADDR'], $developer['ip']) || ENVIRONEMENT) {
        if (!defined('DEBUG')) {
            define("DEBUG", true);
        }
        error_reporting(-1);
        ini_set('display_errors', 1);
    } else {
        if (!defined('DEBUG')) {
            define("DEBUG", false);
        }
    }
} else {

    error_reporting(-1);
    ini_set('display_errors', 1);
    if (!defined('DEBUG')) {
        define("DEBUG", false);
    }
}

if (DEBUG) {
    $_DEBUG = new DebugGlial;
    $_DEBUG->save("Starting...");
}

//$_POST = ArrayTools::array_map_recursive("htmlentities", $_POST);
require __DIR__."/Basic.php";

//debug($_GET);
(DEBUG) ? $_DEBUG->save("Loading class") : "";

$db = $config->get("db");

Sgbd::setConfig($db);
Sgbd::setLogger($log);

(DEBUG) ? $_DEBUG->save("Init database") : "";

if (!IS_CLI) {
    include __DIR__.DS.'Router.php';

    $route = new router();
    $route->parse($_GET['glial_path']);
    $url   = $route->get_routes();

    if (isset($_GET['lg'])) {
        $_SESSION['language'] = $_GET['lg'];
        SetCookie("language", $_GET['lg'], time() + 60 * 60 * 24 * 365, "/", $_SERVER['SERVER_NAME'], false, true);
    }
}

(DEBUG) ? $_DEBUG->save("Rooter loaded") : "";

I18n::SetDefault("en");
I18n::SetSavePath(TMP."translations");

/*** Case MySQL offline */



// uniquement si la base courante est présente dans la configuration
if (Sgbd::ifExit(DB_DEFAULT)) {
    I18n::injectDb(Sgbd::sql(DB_DEFAULT));
}


if (empty($_SESSION['language'])) {
    $_SESSION['language'] = "en";
}

$lg = explode(",", LANGUAGE_AVAILABLE);

if (!in_array($_SESSION['language'], $lg)) {
    $_SESSION['URL_404'] = $_SERVER['QUERY_STRING'];
    header("location: ".WWW_ROOT.I18n::Get()."/error_web/error404/");


    Glial::getOut();
}

I18n::load($_SESSION['language']);
(DEBUG) ? $_DEBUG->save("Language loaded") : "";

//mode with php-cli
if (IS_CLI) {
    if ($_SERVER["argc"] >= 3) {
        $_SYSTEM['controller'] = $_SERVER["argv"][1];
        $_SYSTEM['action']     = $_SERVER["argv"][2];
        $_SYSTEM['param']      = !empty($_SERVER["argv"][3]) ? $_SERVER["argv"][3] : '';

        if ($_SERVER["argc"] > 3) {
            $params = array();
            for ($i = 3; $i < $_SERVER["argc"]; $i++) {
                $params[] = $_SERVER["argv"][$i];
            }
            $_SYSTEM['param'] = $params;
        }

        //cli_set_process_title("glial-" . $_SYSTEM['controller'] . "-" . $_SYSTEM['action']." (".$name.")");
    } else {

        Throw new InvalidArgumentException('usage : gial <controlleur> <action> [params]');
    }
    define('LINK', WWW_ROOT."en"."/");
} else {  //mode with apache
    define('LINK', WWW_ROOT.I18n::Get()."/");


    if (AUTH_ACTIVE) {
        $auth = new Auth();
        $auth->setInstance(Sgbd::sql(DB_DEFAULT), "user_main", array("login", "password"));

        $auth->setLog($log);

        //not used yet
        $auth->setFctToHashCookie(function ($password) {
            return password_hash($password.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'], PASSWORD_DEFAULT);
        });

        $is_auth = $auth->authenticate(false);


        FactoryController::addDi("auth", $auth);
    }

    (ENVIRONEMENT) ? $_DEBUG->save("User connexion") : "";

    //$_SYSTEM['controller'] = $url['controller'];
    $_SYSTEM['controller'] = \Glial\Utility\Inflector::camelize($url['controller']);
    $_SYSTEM['action']     = $url['action'];
    $_SYSTEM['param']      = $url['param'];

    $acl = new Acl(CONFIG."acl.config.ini");

    FactoryController::addDi("acl", $acl);

    $js = new Javascript();
    FactoryController::addDi("js", $js);

    if ($acl->checkIfResourceExist($_SYSTEM['controller']."/".$_SYSTEM['action'])) {

        if (AUTH_ACTIVE) {
            if (!$acl->isAllowed($auth->getAccess(), $_SYSTEM['controller']."/".$_SYSTEM['action'])) {
                if ($auth->getAccess() == 1) {

                    $url = ROUTE_LOGIN;
                    $msg = $_SYSTEM['controller']."/".$_SYSTEM['action']."<br />".__("You have to be registered to acces to this page");
                } else {
                    //die("here");
                    $url = ROUTE_DEFAULT;
                    $msg = $_SYSTEM['controller']."/".$_SYSTEM['action']."<br />".__("Your rank to this website is not enough to acess to this page");
                }

                set_flash("error", __("Acess denied"), __("Acess denied")." : ".$msg);
                header("location: ".LINK.$url);

                Glial::getOut();
            }
        }
    } else {
        if (strtolower($_SYSTEM['controller']) === "errorweb") {
            Throw new \Exception('GLI-404 : Impossible to connect to page 404, by security we broken loop');
            exit;
        }

        set_flash("error", __("Error 404"),
            __("Page not found")." : ".__("Sorry, the page you requested :")." \"".$_SYSTEM['controller']."/".$_SYSTEM['action']."\" ".__("is not on this server. Please contact us if you have questions or concerns"));
        header("location: ".LINK."ErrorWeb/error404/".$_SYSTEM['controller']."/".$_SYSTEM['action']);
        Glial::getOut();
    }
}

(DEBUG) ? $_DEBUG->save("ACL loaded") : "";

//demarre l'application
$html = FactoryController::rootNode($_SYSTEM['controller'], $_SYSTEM['action'], $_SYSTEM['param']);

if ((DEBUG && (!IS_CLI) && (!IS_AJAX))) {
    $debug = FactoryController::addNode("Debug", "toolbar", array(TIME_START), FactoryController::EXPORT);
    //$html  = str_replace("[GLIAL_DEBUG_TOOLBAR]", $debug, $html);
}

echo $html;


$i = 10;

(DEBUG) ? $_DEBUG->save("Layout loaded") : "";

if ((DEBUG && (!IS_CLI) && (!IS_AJAX))) {//ENVIRONEMENT
    echo "<hr />";

    $time_end = microtime(true);
    $execution_time = $time_end - $TIME_START;

    echo "Temps d'exéution de la page : " . round($execution_time, 5) . " seconds";
    echo "<br />Nombre de requette : " . Sgbd::sql(DB_DEFAULT)->get_count_query();
    $file_list = get_included_files();
    echo "<br />Nombre de fichier loaded : <b>" . count($file_list) . "</b><br />";


    $queries = Sgbd::sql(DB_DEFAULT)->getQuery();

    echo '<table class="display-tab table table-condensed" width="100%">';
    echo '<tr>';
    echo '<th>Query</th>';
    echo '<th>time</th>';
    echo '<th>File</th>';
    echo '<th>Line</th>';
    echo '<th>Rows</th>';
    echo '<th>Last_is</th>';
    echo '</tr>';
    foreach($queries as $query)
    {
        echo '<tr>';
        echo '<td>'.SqlFormatter::highlight($query['query']).'</td>';
        echo '<td>'.$query['time'].'</td>';
        echo '<td>'.$query['file'].'</td>';
        echo '<td>'.$query['line'].'</td>';
        echo '<td>'.$query['rows'].'</td>';
        echo '<td>'.$query['last_id'].'</td>';
        echo '</tr>';
    }
    echo '</table>';

    debug($file_list);

    $_DEBUG->print_table();

    
     // echo $_DEBUG->graph();
     // echo $_DEBUG->graph2();
     

    //debug(get_declared_classes());

    echo "SESSION ";
    debug($_SESSION);
    echo "GET ";
    debug($_GET);
    echo "POST ";
    debug($_POST);
    echo "COOKIE ";
    debug($_COOKIE);
    echo "REQUEST ";
    debug($_REQUEST);
    echo "SERVER ";
    debug($_SERVER);

    //debug($_SITE);


    echo "CONSTANTES : <br />";


    $display = false;
    $constantes = get_defined_constants();
    foreach ($constantes as $constante => $valeur) {
        if ($constante == "TIME_START") {
            $display = true;
        }

        if ($display) {
            echo 'Constante: <b>' . $constante . '</b> Valeur: ' . $valeur . '<br/>';
        }
    }
}
    

