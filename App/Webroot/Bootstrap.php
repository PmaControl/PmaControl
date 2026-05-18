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
use App\Library\Audit\RequestAuditCollector;
use App\Library\Security\CookieSecurity;
use App\Library\Security\PersistentAuthSession;
use App\Library\Security\RouteExposurePolicy;
use Glial\Synapse\Glial;

$TIME_START = microtime(true);


require ROOT.DS.'vendor/autoload.php';

// Audit module #1235: RequestAuditCollector::begin() now fires from the
// Glial front controller (App/Webroot/index.php) before the favicon
// early-exit and the surrounding try/catch — so evidence is captured on
// every HTTP hit, not just the ones that survive long enough to reach
// this file. begin() is idempotent; the stampRoute()/stampUser() calls
// further down enrich the row once routing and auth have resolved.

if (!IS_CLI) {
    $cookieTrustedProxies = CookieSecurity::trustedProxies();
    CookieSecurity::configureSessionCookies($_SERVER, $cookieTrustedProxies);
    CookieSecurity::registerOutgoingCookieHardener($_SERVER, $cookieTrustedProxies);
    session_start();
}

$config = new Config;
$config->load(CONFIG);
FactoryController::addDi("config", $config);

// Issue #746: Apache mod_php and the daemon CLI may run different PHP builds
// (e.g. mod_php on 8.2 with date.timezone=Europe/Paris vs /usr/bin/php → 8.5
// with date.timezone unset, defaulting to UTC). When that drift happens,
// Integrate writes ts_value_* timestamps in UTC while Extraction::extract
// filters with NOW() in CEST, and "last hour" graph windows silently return
// zero rows. Force the same effective timezone in every PHP process that
// boots through this file, regardless of php.ini.
\App\Library\Bootstrap\TimezoneAligner::apply(
    defined('PMACONTROL_TIMEZONE') ? PMACONTROL_TIMEZONE : null
);

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
        $_SESSION['language'] = (string) $_GET['lg'];
        if (defined('LANGUAGE_AVAILABLE') && in_array($_SESSION['language'], explode(",", LANGUAGE_AVAILABLE), true)) {
            CookieSecurity::setCookie(
                "language",
                $_SESSION['language'],
                time() + 60 * 60 * 24 * 365,
                $_SERVER,
                "/",
                (string) ($_SERVER['SERVER_NAME'] ?? ''),
                true,
                $cookieTrustedProxies ?? []
            );
        }
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

        // Audit #1235: stamp the resolved CLI route on the collector so
        // the spooled row carries controller/action (the apache branch
        // does the same further down). CLI has no $_SITE → no role class.
        RequestAuditCollector::stampRoute(
            (string) $_SYSTEM['controller'],
            (string) $_SYSTEM['action'],
            null
        );
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

        $persistentAuth = PersistentAuthSession::authenticate(
            $auth,
            Sgbd::sql(DB_DEFAULT),
            $_COOKIE,
            $_SERVER,
            $cookieTrustedProxies ?? []
        );
        $legacyPersistentAuth = !$persistentAuth && PersistentAuthSession::hasLegacyCookies($_COOKIE);
        $is_auth = $persistentAuth || $auth->authenticate(false);
        if ($legacyPersistentAuth && $is_auth) {
            PersistentAuthSession::issueForAuthenticatedUser(
                $auth,
                Sgbd::sql(DB_DEFAULT),
                $_SERVER,
                $cookieTrustedProxies ?? []
            );
        }


        FactoryController::addDi("auth", $auth);
    }

    (DEBUG) ? $_DEBUG->save("User connexion") : "";

    //$_SYSTEM['controller'] = $url['controller'];
    $_SYSTEM['controller'] = \Glial\Utility\Inflector::camelize($url['controller']);
    $_SYSTEM['action']     = $url['action'];
    $_SYSTEM['param']      = $url['param'];

    // Audit #1235: route is now known — stamp it on the collector so the
    // spooled row reflects the resolved controller/action and the role
    // class of the logged-in user. CLI stamping happens earlier in the
    // CLI branch above (no $_SITE / no role class there).
    if (!IS_CLI) {
        $audit_role_class = '';
        if (isset($_SITE['id_group'])) {
            switch ((int) $_SITE['id_group']) {
                case 4: $audit_role_class = 'SuperAdmin'; break;
                case 3: $audit_role_class = 'Administrator'; break;
                case 2: $audit_role_class = 'Member'; break;
                case 1: $audit_role_class = 'Visitor'; break;
            }
        }
        RequestAuditCollector::stampRoute($_SYSTEM['controller'], $_SYSTEM['action'], $audit_role_class !== '' ? $audit_role_class : null);
        if (isset($_SITE['IdUser']) && (int) $_SITE['IdUser'] > 0) {
            RequestAuditCollector::stampUser((int) $_SITE['IdUser']);
        }
        // Surface the request_uid for client-side JS (clientMetrics.js) and
        // any controller that needs to echo it.
        $audit_uid = RequestAuditCollector::requestUid();
        if ($audit_uid !== null) {
            if (!headers_sent()) {
                header('X-Pma-Audit-Id: ' . $audit_uid);
            }
        }
        unset($audit_role_class, $audit_uid);
    }

    $routeExposureDenialReason = RouteExposurePolicy::denialReason($_SYSTEM['controller'], $_SYSTEM['action']);
    if ($routeExposureDenialReason !== null) {
        $blockedResource = RouteExposurePolicy::resourceName($_SYSTEM['controller'], $_SYSTEM['action']);
        $log->warning('Blocked non-exposed controller route', [
            'resource' => $blockedResource,
            'reason' => $routeExposureDenialReason,
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        set_flash("error", __("Error 404"),
            __("Page not found")." : ".__("Sorry, the page you requested :")." \"".$blockedResource."\" ".__("is not on this server. Please contact us if you have questions or concerns"));
        header("location: ".LINK."ErrorWeb/error404/".$_SYSTEM['controller']."/".$_SYSTEM['action']);
        Glial::getOut();
    }

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

echo $html;

(DEBUG) ? $_DEBUG->save("Layout loaded") : "";


if ((DEBUG && (!IS_CLI) && (!IS_AJAX)) && empty($_GET['ajax'])) {
    $time_end       = microtime(true);
    $execution_time = $time_end - $TIME_START;
    $query_count    = Sgbd::sql(DB_DEFAULT)->get_count_query();
    $queries        = Sgbd::sql(DB_DEFAULT)->getQuery();
    $file_list      = get_included_files();

    $maxTimeEntry = array_reduce($queries, function ($carry, $item) {
        return ($carry === null || $item["time"] > $carry["time"]) ? $item : $carry;
    });
    $maxtime = $maxTimeEntry['time'] ?? 0;

    echo '<div id="glial-debug-footer" style="margin:20px 15px; padding:15px; border-top:2px solid #888; background:#f8f8f8; font-family:monospace; font-size:12px;">';
    echo '<h3 style="margin-top:0">Debug</h3>';
    echo '<ul style="list-style:none; padding:0; margin:0 0 10px 0;">';
    echo '<li><b>Generation time :</b> '.round($execution_time, 5).' s</li>';
    echo '<li><b>Queries :</b> '.(int) $query_count.'</li>';
    echo '<li><b>Included files :</b> '.count($file_list).'</li>';
    echo '</ul>';

    if (!empty($queries)) {
        echo '<table class="display-tab table table-condensed" width="100%">';
        echo '<tr>';
        echo '<th>Cumulate</th>';
        echo '<th>Query</th>';
        echo '<th>Time</th>';
        echo '<th>File</th>';
        echo '<th>Line</th>';
        echo '<th>Rows</th>';
        echo '<th>Last_id</th>';
        echo '</tr>';

        foreach ($queries as $query) {
            $percent = $maxtime > 0 ? round($query['time'] / $maxtime * 100) : 0;

            echo '<tr>';
            echo '<td>'.$query['cumulate'].'</td>';
            echo '<td style="max-width:1200px; overflow:auto">'.SqlFormatter::highlight($query['query']);
            echo '<div class="glial-progress-bar"><div class="glial-progress" style="width: '.$percent.'%;"></div></div>';
            echo '</td>';
            echo '<td>'.$query['time'].'</td>';
            echo '<td>'.$query['file'].'</td>';
            echo '<td>'.$query['line'].'</td>';
            echo '<td>'.$query['rows'].'</td>';
            echo '<td>'.$query['last_id'].'</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    $_DEBUG->print_table();

    echo '</div>';
}
    
