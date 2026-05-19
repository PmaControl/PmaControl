<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;
use \Glial\Security\Crypt\Crypt;
use App\Library\Extraction;
use App\Library\Extraction2;

use \App\Library\Debug;
use \App\Library\Mysql;
use \App\Library\EngineV4;
use App\Library\Display;
use App\Library\EngineMemoryBreakdown;
use App\Library\Http\HttpResponse;
use App\Library\Security\CsrfGuard;
use App\Library\Security\RouteMutationRequest;
use App\Library\ServerStateTimeline;

use App\Library\Chiffrement;
use App\Library\Cve\ServerCveImpactMatcher;
use \Glial\Sgbd\Sgbd;
use Glial\Security\Csrf;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

use App\Library\Microsecond;

use GeoIp2\Database\Reader;
/*
UPDATE performance_schema.setup_instruments
   SET ENABLED = 'YES', TIMED = 'YES'
   WHERE NAME LIKE 'statement/sql/select';

UPDATE performance_schema.setup_consumers
   SET ENABLED = 'YES' WHERE NAME IN ('events_statements_history', 'events_statements_current');


*/

class Server extends Controller
{

    use \App\Library\Filter;
    private const SERVER_SETTINGS_CSRF_SCOPE = 'server.settings';
    private const SERVER_SETTINGS_DISPLAY_NAME_MAX_LENGTH = 255;
    private const SERVER_PASSWORD_CSRF_SCOPE = 'server.password';
    private const SERVER_ACKNOWLEDGE_CSRF_SCOPE = 'server.acknowledge';
    private const SERVER_RETRACT_CSRF_SCOPE = 'server.retract';
    private const SERVER_REMOVE_CSRF_SCOPE = 'server.remove';
    private const SERVER_PASSWORD_LOGIN_MAX_LENGTH = 128;
    private const SERVER_PASSWORD_VALUE_MAX_LENGTH = 1024;
    private const SERVER_ID_FILTER_INTERVALS = [
        '5-minute',
        '15-minute',
        '1-hour',
        '2-hour',
        '6-hour',
        '12-hour',
        '1-day',
        '2-day',
        '1-week',
        '2-week',
        '1-month',
    ];
/**
 * Stores `$clip` for clip.
 *
 * @var int
 * @phpstan-var int
 * @psalm-var int
 */
    var $clip = 0;

/**
 * Stores `$logger` for logger.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    var $logger;

    public static function getGeoipCountryLookupSql($db, string $ip): string
    {
        $escapedIp = $db->sql_real_escape_string($ip);

        return "SELECT country_iso FROM data_geoip
                           WHERE network_start <= INET6_ATON('".$escapedIp."')
                           AND network_end >= INET6_ATON('".$escapedIp."')
                           ORDER BY network_start DESC
                           LIMIT 1";
    }

/**
 * Prepare server state through `before`.
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
 * @example /fr/server/before
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
        $monolog       = new Logger("Server");
        $handler      = new StreamHandler(LOG_FILE, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $monolog->pushHandler($handler);
        $this->logger = $monolog;
    }

//dba_source
/**
 * Handle server state through `hardware`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for hardware.
 * @phpstan-return void
 * @psalm-return void
 * @see self::hardware()
 * @example /fr/server/hardware
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function hardware()
    {
        $db           = Sgbd::sql(DB_DEFAULT);
        $this->title  = __("Hardware");
        $this->ariane = " > ".$this->title;

        $data['hardware'] = Extraction::display(array("ssh_hardware::cpu_thread_count",
                "ssh_hardware::cpu_frequency",
                "ssh_hardware::memory",
                "ssh_hardware::distributor",
                "ssh_hardware::os",
                "ssh_hardware::codename",
                "ssh_hardware::product_name",
                "ssh_hardware::arch",
                "ssh_hardware::kernel",
                "ssh_hardware::hostname",
                "ssh_hardware::swapiness"
        ));

        $data['service_ssh'] = Extraction::display(array("ssh_available"));

        $id_mysql_servers = array_keys($data['hardware']);

        if (!empty($id_mysql_servers)) {

            $sql = "SELECT c.libelle as client,d.libelle as environment,a.*
            FROM mysql_server a
            
                 INNER JOIN client c on c.id = a.id_client
                 INNER JOIN environment d on d.id = a.id_environment

         WHERE 1=1 ".self::getFilter()."
             AND a.id in (".implode(",", $id_mysql_servers).")
         order by `name`;";

//echo SqlFormatter::format($sql);

            $data['servers'] = $db->sql_fetch_yield($sql);
        } else {
            $data['servers'] = array();
        }


        $this->set('data', $data);
    }




    /*
      public function listing($param)
      {
      // doc : http://silviomoreto.github.io/bootstrap-select/examples/#standard-select-boxes
      $this->di['js']->addJavascript(array('bootstrap-select.min.js'));

      $db = Sgbd::sql(DB_DEFAULT);

      $sql = "SELECT * FROM daemon_main WHERE id=1";
      $res = $db->sql_query($sql);

      while ($ob = $db->sql_fetch_object($res)) {

      $data['pid']      = $ob->pid;
      $data['date']     = $ob->date;
      $data['log_file'] = $ob->log_file;
      }
      $data['client']                = $this->getClients();
      $data['environment']           = $this->getEnvironments();
      $data['menu']['main']['name']  = __('Servers');
      $data['menu']['main']['icone'] = '<i class="fa fa-server" aria-hidden="true" style="font-size:14px"></i>';
      //$data['menu']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
      $data['menu']['main']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/main';

      $data['menu']['hardware']['name']  = __('Hardware');
      $data['menu']['hardware']['icone'] = '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>';
      $data['menu']['hardware']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/hardware';

      $data['menu']['database']['name']  = __('Databases');
      $data['menu']['database']['icone'] = '<i class="fa fa-database fa-lg" style="font-size:14px"></i>';
      $data['menu']['database']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/database';

      $data['menu']['statistics']['name']  = __('Statistics');
      $data['menu']['statistics']['icone'] = '<span class="glyphicon glyphicon-signal" style="font-size:12px"></span>';
      $data['menu']['statistics']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/statistics';

      $data['menu']['memory']['name']  = __('Memory');
      $data['menu']['memory']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
      $data['menu']['memory']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/memory';

      $data['menu']['index']['name']  = __('Index');
      $data['menu']['index']['icone'] = '<span class="glyphicon glyphicon-th-list" style="font-size:12px"></span>';
      $data['menu']['index']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/index';

      $data['menu']['system']['name']  = __('System');
      $data['menu']['system']['icone'] = '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>';
      $data['menu']['system']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/system';

      $data['menu']['logs']['name']  = __('Logs');
      $data['menu']['logs']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
      $data['menu']['logs']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/index';

      $data['menu']['id']['name']  = __('Graphs');
      $data['menu']['id']['icone'] = '<i class="fa fa-line-chart" aria-hidden="true"></i>';
      $data['menu']['id']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/id';

      $data['menu']['cache']['name']  = __('Cache');
      $data['menu']['cache']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
      $data['menu']['cache']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/cache';


      $data['menu_select']['main']['name']  = __('Servers');
      $data['menu_select']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
      $data['menu_select']['main']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/main';

      $data['menu_select']['hardware']['name']  = __('Hardware');
      $data['menu_select']['hardware']['icone'] = '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>';
      $data['menu_select']['hardware']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/hardware';


      $data['menu_select']['database']['name']  = __('Databases');
      $data['menu_select']['database']['icone'] = '<i class="fa fa-database fa-lg" style="font-size:14px"></i>';
      $data['menu_select']['database']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/database';

      $data['menu_select']['statistics']['name']  = __('Statistics');
      $data['menu_select']['statistics']['icone'] = '<span class="glyphicon glyphicon-signal" style="font-size:12px"></span>';
      $data['menu_select']['statistics']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/statistics';

      $data['menu_select']['memory']['name']  = __('Memory');
      $data['menu_select']['memory']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
      $data['menu_select']['memory']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/memory';

      $data['menu_select']['index']['name']  = __('Index');
      $data['menu_select']['index']['icone'] = '<span class="glyphicon glyphicon-th-list" style="font-size:12px"></span>';
      $data['menu_select']['index']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/index';


      $data['menu_select']['system']['name']  = __('System');
      $data['menu_select']['system']['icone'] = '<span class="glyphicon glyphicon-cog" style="font-size:12px"></span>';
      $data['menu_select']['system']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/system';

      $data['menu_select']['logs']['name']  = __('Logs');
      $data['menu_select']['logs']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
      $data['menu_select']['logs']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/index';


      $data['menu_select']['id']['name']  = __('Server');
      $data['menu_select']['id']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
      $data['menu_select']['id']['path']  = LINK.$this->getClass().'/'.__FUNCTION__.'/id';



      if (!empty($param[0])) {
      if (in_array($param[0], array("main", "database", "statistics", "logs", "memory", "index", "hardware", "system", "id", "cache"))) {
      $_GET['path'] = LINK.$this->getClass().'/'.__FUNCTION__.'/'.$param[0];
      }
      }

      if (empty($_GET['path']) && empty($param[0])) {
      $_GET['path'] = $data['menu']['main']['path'];
      $param[0]     = 'main';
      }

      if (empty($_GET['path'])) {
      $_GET['path'] = 'main';
      }


      //@TODO bug with item not selected (empty) need put case by default

      $this->title = '<span class="glyphicon glyphicon glyphicon-home"></span> '.__("Dashboard");
      //$this->ariane = ' > <a href⁼"">'.'<span class="glyphicon glyphicon glyphicon-home" style="font-size:12px"></span> '.__("Dashboard").'</a> > '.$data['menu'][$param[0]]['icone'].' '.$data['menu'][$param[0]]['name'];

      $this->set('data', $data);
      }
      /*
     */

    public function main($param)
    {
        Debug::parseDebug($param);

        $db = Sgbd::sql(DB_DEFAULT);

        if (!empty($_GET['ajax']) && $_GET['ajax'] === "true") {
            $this->layout_name = false;
        }
        else {
            $this->di['js']->addJavascript(array('clipboard.min.js', 'Client/index.js', 'Server/main.js'));
            $this->di['js']->code_javascript('(function() {
                new Clipboard(".copy-button");
            })();');
    
            $this->di['js']->code_javascript('
            $(document).ready(function()
            {
                var intervalId;
                var refreshInterval = 1000; // Intervalle par défaut de 3 secondes

                function refresh()
                {
                    var queryString = window.location.search || "";
                    var myURL = GLIAL_LINK + GLIAL_URL + "/ajax:true" + queryString;
                    $("#servermain").load(myURL);
                    (function() {

                        //duplicate :/
                        new Clipboard(".copy-button");

                        $(".general_log").change(function () {
                            id_mysql_server = $(this).attr("data-id");
                            url = GLIAL_LINK + "server/toggleGeneralLog/" + id_mysql_server;
                        
                            if (this.checked) {
                                $.get(url + "/true/");
                            } else
                            {
                                $.get(url + "/false/");
                            }
                        });
                    })();


                }

                function setRefreshInterval(newInterval) {
                    refreshInterval = newInterval;
                    clearInterval(intervalId);
                    intervalId = setInterval(refresh, refreshInterval);
                }

                function stopRefresh() {
                    clearInterval(intervalId);
                    intervalId = null;
                }
    
                intervalId = setInterval(refresh, refreshInterval);

                // Rendre les fonctions accessibles globalement
                window.setRefreshInterval = setRefreshInterval;
                window.stopRefresh = stopRefresh;

    
            });'); /***/

        }

        $showSessionKey = 'server_main_show_mode';
        if (!empty($_GET['show']) && in_array($_GET['show'], ['all', 'monitored'], true)) {
            $_SESSION[$showSessionKey] = $_GET['show'];
        }

        $typeSessionKey = 'server_main_type_filter';
        if (isset($_GET['type'])) {
            $typeParam = trim((string)$_GET['type']);
            $_SESSION[$typeSessionKey] = $typeParam === '' ? 'all' : $typeParam;
        }

        $showMode = $_SESSION[$showSessionKey] ?? 'monitored';
        $typeFilter = $_SESSION[$typeSessionKey] ?? 'all';

        $data['show_mode'] = $showMode;
        $data['type_filter'] = $typeFilter;

        $effectiveMonitoredExpr = "CASE WHEN c.is_monitored = 0 THEN 0 ELSE a.is_monitored END";

        $sql = "SELECT a.*, c.libelle as client,c.is_monitored as client_monitored, d.libelle as environment,d.`class`,
                 ".$effectiveMonitoredExpr." AS effective_is_monitored
            FROM mysql_server PARTITION(pn) as a
                 INNER JOIN client c on c.id = a.id_client
                 INNER JOIN environment d on d.id = a.id_environment
                 WHERE a.is_deleted=0 ".self::getFilter();

        if ($showMode === 'monitored') {
            $sql .= " AND ".$effectiveMonitoredExpr." = 1";
        }

        $sql .= "
                 ORDER by a.id, a.is_monitored DESC, c.is_monitored DESC, a.`is_acknowledged`, 
                 FIND_IN_SET(d.`id`, '1,19,2,16,3,7,4,6,8,5,17,18'), a.ip, a.display_name;";

        $res = $db->sql_query($sql);

        $servers = array();
        $typeIndexMap = [];
        $typeFilters = [];
        $typeFilters['all'] = __('All types');

        while ($arr     = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['servers'][] = $arr;
            $servers[]         = $arr['id'];
            $typeIndexMap[(int)$arr['id']] = count($data['servers']) - 1;
        }

        $data['extra'] = Extraction2::display(array("version", "version_comment", "mysql_ping","time_server","wsrep_cluster_status","have_ssl",
         "mysql_available", "mysql_server::mysql_error" ,"general_log", "wsrep_on", "is_proxysql", "performance_schema", "read_only",
         "avg_latency","delta_sum_timer_wait", "delta_sum_lock_time", "variables::hostname"));

        

        $data['last_date'] = Extraction2::display(array("mysql_available"));
        $data['tunnel_mapping'] = Tunnel::getTunnelsMapping();

        //debug($data);

        if (!empty($data['servers'])) {
            $filteredServers = [];

            foreach ($data['servers'] as $server) {
                $serverId = (int)$server['id'];
                $extra = $data['extra'][$serverId] ?? [];

                $isVipServer = !empty($server['is_vip']) && (string)$server['is_vip'] === "1";
                $isProxyServer = !empty($server['is_proxy']) && (string)$server['is_proxy'] === "1";
                $isProxySql = !empty($extra['is_proxysql']) && (string)$extra['is_proxysql'] === "1";
                $versionInfo = [];
                if (!empty($extra['version']) || !empty($extra['version_comment'])) {
                    $versionInfo = \App\Library\Format::getMySQLNumVersion((string)$extra['version'], (string)($extra['version_comment'] ?? ''));
                }

                $typeLabel = 'MySQL';
                if ($isVipServer) {
                    $typeLabel = 'VIP';
                } elseif (!empty($versionInfo['fork']) && strtolower((string)$versionInfo['fork']) === 'mysql router') {
                    $typeLabel = 'MySQL Router';
                } elseif ($isProxyServer || $isProxySql) {
                    $typeLabel = 'ProxySQL';
                } elseif (!empty($versionInfo['fork'])) {
                    if (!empty($versionInfo['fork'])) {
                        $typeLabel = ucfirst(strtolower((string)$versionInfo['fork']));
                    }
                }

                $filterKey = strtolower($typeLabel);
                $typeFilters[$filterKey] = $typeLabel;

                if (isset($typeIndexMap[$serverId])) {
                    $data['servers'][$typeIndexMap[$serverId]]['type_label'] = $typeLabel;
                    $data['servers'][$typeIndexMap[$serverId]]['type_filter_key'] = $filterKey;
                }

                if ($typeFilter !== 'all' && $filterKey !== strtolower($typeFilter)) {
                    continue;
                }

                $filteredServers[] = $server;
            }

            $data['servers'] = $filteredServers;
        }

        $data['type_filters'] = $typeFilters;

        // get Tag
        $sql          = "SELECT * FROM link__mysql_server__tag a
            INNER JOIN tag b ON b.id =a.id_tag ORDER BY b.name";
        $res          = $db->sql_query($sql);
        $data['tags'] = array();
        while ($arr          = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['tag'][$arr['id_mysql_server']][] = $arr;
        }

        $data['processing'] = $this->getDaemonRunning(['mysql']);
        $data['worker_kill_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['worker_kill_csrf_token'] = Csrf::issueToken($_SESSION, Worker::WORKER_KILL_SERVER_CSRF_SCOPE);
        $data['server_acknowledge_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['server_acknowledge_csrf_token'] = Csrf::issueToken($_SESSION, self::SERVER_ACKNOWLEDGE_CSRF_SCOPE);
        $data['server_retract_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['server_retract_csrf_token'] = Csrf::issueToken($_SESSION, self::SERVER_RETRACT_CSRF_SCOPE);
        $data['galera_set_primary_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['galera_set_primary_csrf_token'] = Csrf::issueToken($_SESSION, GaleraCluster::SET_PRIMARY_CSRF_SCOPE);

        // GeoIP: lookup country for each server IP (IPv4 + IPv6) via range join
        // Skip loopback / private / reserved ranges — they are never in the GeoIP table
        // and the range scan is expensive.
        $data['geoip'] = [];
        if (!empty($data['servers'])) {
            $ips = [];
            foreach ($data['servers'] as $s) {
                if (filter_var($s['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    $ips[$s['ip']] = true;
                }
            }
            foreach (array_keys($ips) as $ip) {
                $sqlGeo = self::getGeoipCountryLookupSql($db, $ip);
                $resGeo = $db->sql_query($sqlGeo);
                if ($resGeo && ($row = $db->sql_fetch_array($resGeo, MYSQLI_ASSOC))) {
                    $data['geoip'][$ip] = $row['country_iso'];
                }
            }
        }

        $data['cve_impacts'] = ServerCveImpactMatcher::loadForServerMain(
            $db,
            $data['servers'] ?? [],
            $data['extra'] ?? [],
            empty($_GET['ajax'])
        );
        // Let installed plugins enrich $data. Each callback receives
        // ($data, ['db' => $db]) and must return the updated $data.
        $data = \App\Library\PluginSlot::apply('server.main.data', $data, ['db' => $db]);

        $this->set('data', $data);
    }

/**
 * Handle server state through `database`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for database.
 * @phpstan-return void
 * @psalm-return void
 * @see self::database()
 * @example /fr/server/database
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    /**
     * Load the entire GeoLite2-Country.mmdb file into the data_geoip table.
     * Iterates the IPv4 space using getWithPrefixLen to extract all network ranges.
     * CLI: php App/Webroot/index.php server loadGeoip
     */
    public function loadGeoip()
    {
        $this->layout_name = false;
        $db = Sgbd::sql(DB_DEFAULT);

        $geoDbPath = ROOT.'/data/GeoLite2-Country.mmdb';
        if (!file_exists($geoDbPath)) {
            echo "GeoLite2-Country.mmdb not found in data/\n";
            return;
        }

        $reader = new \MaxMind\Db\Reader($geoDbPath);

        $db->sql_query("TRUNCATE TABLE data_geoip");
        echo "Loading GeoLite2 IPv4 ranges...\n";

        $ip = 0;
        $endIp = 4294967295;
        $count = 0;
        $batch = [];
        $batchSize = 1000;
        $startTime = microtime(true);

        while ($ip <= $endIp) {
            $ipStr = long2ip($ip);
            try {
                [$record, $prefixLen] = $reader->getWithPrefixLen($ipStr);
            } catch (\Exception $e) {
                $ip++;
                continue;
            }

            if ($prefixLen <= 0 || $prefixLen > 32) {
                $ip++;
                continue;
            }

            $networkSize = 1 << (32 - $prefixLen);
            $networkEnd = $ip + $networkSize - 1;
            if ($networkEnd > $endIp) $networkEnd = $endIp;

            if ($record !== null && !empty($record['country']['iso_code'])) {
                $iso = $record['country']['iso_code'];
                $name = $record['country']['names']['en'] ?? '';
                $startHex = bin2hex(inet_pton($ipStr));
                $endHex = bin2hex(inet_pton(long2ip($networkEnd)));
                $batch[] = "(UNHEX('$startHex'),UNHEX('$endHex'),'"
                    .$db->sql_real_escape_string($iso)."','"
                    .$db->sql_real_escape_string($name)."')";

                if (count($batch) >= $batchSize) {
                    $db->sql_query("INSERT INTO data_geoip (network_start,network_end,country_iso,country_name) VALUES ".implode(',', $batch));
                    $count += count($batch);
                    $batch = [];
                }
            }

            $ip = $networkEnd + 1;
            if ($ip <= 0) break;
        }

        if (!empty($batch)) {
            $db->sql_query("INSERT INTO data_geoip (network_start,network_end,country_iso,country_name) VALUES ".implode(',', $batch));
            $count += count($batch);
        }

        // ── Pass 2: IPv6 global unicast (2000::/3) ──
        echo "Loading GeoLite2 IPv6 ranges (2000::/3)...\n";
        $v6count = 0;

        // Iterate top-level /16 blocks: 2000:: to 3fff::
        for ($hi = 0x20; $hi <= 0x3f; $hi++) {
            for ($lo = 0; $lo <= 0xff; $lo++) {
                $prefix = sprintf('%02x%02x::', $hi, $lo);
                $ipStr = $prefix;
                $packed = @inet_pton($ipStr);
                if ($packed === false) continue;

                try {
                    [$record, $prefixLen] = $reader->getWithPrefixLen($ipStr);
                } catch (\Exception $e) {
                    continue;
                }

                if ($prefixLen <= 0 || $prefixLen > 128) continue;
                if ($record === null || empty($record['country']['iso_code'])) continue;

                // Compute network start/end from prefix + prefixLen
                $startBin = $packed;
                // End = start with all host bits set to 1
                $endBin = $startBin;
                $byteArr = array_values(unpack('C16', $endBin));
                $fullBytes = intdiv($prefixLen, 8);
                $remainBits = $prefixLen % 8;
                if ($remainBits > 0) {
                    $byteArr[$fullBytes] |= (0xFF >> $remainBits);
                    $fullBytes++;
                }
                for ($b = $fullBytes; $b < 16; $b++) {
                    $byteArr[$b] = 0xFF;
                }
                $endBin = pack('C16', ...$byteArr);

                $iso = $record['country']['iso_code'];
                $name = $record['country']['names']['en'] ?? '';
                $startHex = bin2hex($startBin);
                $endHex = bin2hex($endBin);
                $batch[] = "(UNHEX('$startHex'),UNHEX('$endHex'),'"
                    .$db->sql_real_escape_string($iso)."','"
                    .$db->sql_real_escape_string($name)."')";

                if (count($batch) >= $batchSize) {
                    $db->sql_query("INSERT INTO data_geoip (network_start,network_end,country_iso,country_name) VALUES ".implode(',', $batch));
                    $v6count += count($batch);
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            $db->sql_query("INSERT INTO data_geoip (network_start,network_end,country_iso,country_name) VALUES ".implode(',', $batch));
            $v6count += count($batch);
            $batch = [];
        }

        $count += $v6count;
        $reader->close();
        $elapsed = round(microtime(true) - $startTime, 1);
        echo "Loaded $count network ranges ($v6count IPv6) in {$elapsed}s\n";
    }

    /**
     * Load GeoLite2-City.mmdb into data_geoip_city table.
     * CLI: php App/Webroot/index.php server loadGeoipCity
     */
    public function loadGeoipCity()
    {
        $this->layout_name = false;
        $db = Sgbd::sql(DB_DEFAULT);

        $geoDbPath = ROOT.'/data/GeoLite2-City.mmdb';
        if (!file_exists($geoDbPath)) {
            echo "GeoLite2-City.mmdb not found in data/\n";
            return;
        }

        $reader = new \MaxMind\Db\Reader($geoDbPath);

        $db->sql_query("TRUNCATE TABLE data_geoip_city");
        echo "Loading GeoLite2-City IPv4 ranges...\n";

        $ip = 0;
        $endIp = 4294967295;
        $count = 0;
        $batch = [];
        $batchSize = 500;
        $startTime = microtime(true);

        while ($ip <= $endIp) {
            $ipStr = long2ip($ip);
            try {
                [$record, $prefixLen] = $reader->getWithPrefixLen($ipStr);
            } catch (\Exception $e) {
                $ip++;
                continue;
            }

            if ($prefixLen <= 0 || $prefixLen > 32) {
                $ip++;
                continue;
            }

            $networkSize = 1 << (32 - $prefixLen);
            $networkEnd = $ip + $networkSize - 1;
            if ($networkEnd > $endIp) $networkEnd = $endIp;

            if ($record !== null && !empty($record['country']['iso_code'])) {
                $iso = $record['country']['iso_code'];
                $name = $record['country']['names']['en'] ?? '';
                $regionIso = '';
                $regionName = '';
                if (!empty($record['subdivisions'][0])) {
                    $regionIso = $record['subdivisions'][0]['iso_code'] ?? '';
                    $regionName = $record['subdivisions'][0]['names']['en'] ?? '';
                }
                $city = $record['city']['names']['en'] ?? '';
                $postal = $record['postal']['code'] ?? '';
                $lat = $record['location']['latitude'] ?? null;
                $lng = $record['location']['longitude'] ?? null;
                $tz = $record['location']['time_zone'] ?? '';

                $startHex = bin2hex(inet_pton($ipStr));
                $endHex = bin2hex(inet_pton(long2ip($networkEnd)));
                $latSql = $lat === null ? 'NULL' : (float)$lat;
                $lngSql = $lng === null ? 'NULL' : (float)$lng;

                $batch[] = "(UNHEX('$startHex'),UNHEX('$endHex'),"
                    ."'".$db->sql_real_escape_string($iso)."',"
                    ."'".$db->sql_real_escape_string($name)."',"
                    ."'".$db->sql_real_escape_string($regionIso)."',"
                    ."'".$db->sql_real_escape_string($regionName)."',"
                    ."'".$db->sql_real_escape_string($city)."',"
                    ."'".$db->sql_real_escape_string($postal)."',"
                    .$latSql.",".$lngSql.","
                    ."'".$db->sql_real_escape_string($tz)."')";

                if (count($batch) >= $batchSize) {
                    $db->sql_query("INSERT INTO data_geoip_city (network_start,network_end,country_iso,country_name,region_iso,region_name,city,postal,latitude,longitude,time_zone) VALUES ".implode(',', $batch));
                    $count += count($batch);
                    $batch = [];
                    if ($count % 50000 === 0) {
                        $elapsed = round(microtime(true) - $startTime, 1);
                        echo "  $count rows inserted ({$elapsed}s)\n";
                    }
                }
            }

            $ip = $networkEnd + 1;
            if ($ip <= 0) break;
        }

        if (!empty($batch)) {
            $db->sql_query("INSERT INTO data_geoip_city (network_start,network_end,country_iso,country_name,region_iso,region_name,city,postal,latitude,longitude,time_zone) VALUES ".implode(',', $batch));
            $count += count($batch);
        }

        // ── Pass 2: IPv6 global unicast (2000::/3) ──
        echo "Loading GeoLite2-City IPv6 ranges (2000::/3)...\n";
        $v6count = 0;

        for ($hi = 0x20; $hi <= 0x3f; $hi++) {
            for ($lo = 0; $lo <= 0xff; $lo++) {
                $prefix = sprintf('%02x%02x::', $hi, $lo);
                $packed = @inet_pton($prefix);
                if ($packed === false) continue;

                try {
                    [$record, $prefixLen] = $reader->getWithPrefixLen($prefix);
                } catch (\Exception $e) {
                    continue;
                }

                if ($prefixLen <= 0 || $prefixLen > 128) continue;
                if ($record === null || empty($record['country']['iso_code'])) continue;

                $startBin = $packed;
                $byteArr = array_values(unpack('C16', $packed));
                $fullBytes = intdiv($prefixLen, 8);
                $remainBits = $prefixLen % 8;
                if ($remainBits > 0) {
                    $byteArr[$fullBytes] |= (0xFF >> $remainBits);
                    $fullBytes++;
                }
                for ($b = $fullBytes; $b < 16; $b++) {
                    $byteArr[$b] = 0xFF;
                }
                $endBin = pack('C16', ...$byteArr);

                $iso = $record['country']['iso_code'];
                $name = $record['country']['names']['en'] ?? '';
                $regionIso = $record['subdivisions'][0]['iso_code'] ?? '';
                $regionName = $record['subdivisions'][0]['names']['en'] ?? '';
                $city = $record['city']['names']['en'] ?? '';
                $postal = $record['postal']['code'] ?? '';
                $lat = $record['location']['latitude'] ?? null;
                $lng = $record['location']['longitude'] ?? null;
                $tz = $record['location']['time_zone'] ?? '';
                $latSql = $lat === null ? 'NULL' : (float)$lat;
                $lngSql = $lng === null ? 'NULL' : (float)$lng;

                $startHex = bin2hex($startBin);
                $endHex = bin2hex($endBin);
                $batch[] = "(UNHEX('$startHex'),UNHEX('$endHex'),"
                    ."'".$db->sql_real_escape_string($iso)."',"
                    ."'".$db->sql_real_escape_string($name)."',"
                    ."'".$db->sql_real_escape_string($regionIso)."',"
                    ."'".$db->sql_real_escape_string($regionName)."',"
                    ."'".$db->sql_real_escape_string($city)."',"
                    ."'".$db->sql_real_escape_string($postal)."',"
                    .$latSql.",".$lngSql.","
                    ."'".$db->sql_real_escape_string($tz)."')";

                if (count($batch) >= $batchSize) {
                    $db->sql_query("INSERT INTO data_geoip_city (network_start,network_end,country_iso,country_name,region_iso,region_name,city,postal,latitude,longitude,time_zone) VALUES ".implode(',', $batch));
                    $v6count += count($batch);
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            $db->sql_query("INSERT INTO data_geoip_city (network_start,network_end,country_iso,country_name,region_iso,region_name,city,postal,latitude,longitude,time_zone) VALUES ".implode(',', $batch));
            $v6count += count($batch);
        }

        $count += $v6count;
        $reader->close();
        $elapsed = round(microtime(true) - $startTime, 1);
        echo "Loaded $count city ranges ($v6count IPv6) in {$elapsed}s\n";
    }

    public function database()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        //TODO add available === 1
        $sql = "SELECT a.id,a.name,a.ip,a.port,
			GROUP_CONCAT('',b.name) as dbs,
			GROUP_CONCAT('',b.id) as id_db,
			GROUP_CONCAT('',b.data_length) as data_length,
			GROUP_CONCAT('',b.data_free) as data_free,
			GROUP_CONCAT('',b.index_length) as index_length,
			GROUP_CONCAT('',b.collation_name) as collation_name,
			GROUP_CONCAT('',b.character_set_name) as character_set_name,
			GROUP_CONCAT('',b.binlog_do_db) as binlog_do_db,
			GROUP_CONCAT('',b.tables) as `tables`,
			GROUP_CONCAT('',b.rows) as `rows`
			FROM mysql_server a
			INNER JOIN mysql_database b ON b.id_mysql_server = a.id
                        ".$this->getFilter()."
			GROUP BY a.id";

        $data['servers'] = $db->sql_fetch_yield($sql);
        $this->set('data', $data);
    }

/**
 * Handle server state through `statistics`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for statistics.
 * @phpstan-return void
 * @psalm-return void
 * @see self::statistics()
 * @example /fr/server/statistics
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function statistics()
    {
        $db = Sgbd::sql(DB_DEFAULT);

//echo \SqlFormatter::format($sql);


        $data['servers'] = Extraction::display(array("status::com_select", "status::com_update", "status::com_insert", "status::com_delete",
                "status::threads_connected", "status::uptime", "status::com_commit", "status::com_rollback", "status::com_begin", "status::com_replace",
                "variables::hostname","variables::is_proxysql"));

        $data['mysql'] = $this->getServer();

        /*
          $sql = "SELECT id, ip, port from mysql_server a";
          $res = $db->sql_query($sql);

          while ($ob = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
          $data['mysql'][$ob['id']] = $ob;
          } */


        $this->set('data', $data);
    }

    public function state()
    {
        $this->title = __("Server State");
        $this->ariane = " > " . $this->title;
        $rangeOptions = $this->getServerStateRangeOptions();
        $payload = ServerStateTimeline::buildInitialPayload($this->getServerStateList(), $rangeOptions);
        $chartVersion = @filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'chart-4.5.1.umd.min.js') ?: time();
        $stateJsVersion = @filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'Server' . DS . 'state.js') ?: time();
        $queryString = http_build_query(array_filter($rangeOptions, function ($value) {
            return $value !== null && $value !== '';
        }));
        $urlSuffix = $queryString === '' ? '' : '?' . $queryString;

        $this->di['js']->addJavascript(array(
            'chart-4.5.1.umd.min.js?v=' . $chartVersion,
            'Server/state.js?v=' . $stateJsVersion,
        ));
        $this->di['js']->code_javascript('window.serverStateConfig = ' . json_encode(array(
            'initialUrl' => LINK . 'server/stateInitial/ajax:true' . $urlSuffix,
            'liveUrl' => LINK . 'server/stateLive/ajax:true' . $urlSuffix,
            'refreshIntervalMs' => 10000,
            'liveEnabled' => !empty($payload['range']['live_enabled']),
        )) . ';');
        $this->di['js']->code_javascript('window.serverStateInitialPayload = ' . json_encode($payload) . ';');
        $this->set('data', array(
            'range' => $payload['range'] ?? [],
        ));
    }

    public function stateInitial()
    {
        $this->layout_name = false;
        $this->layout = false;
        $this->view = false;
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(ServerStateTimeline::buildInitialPayload($this->getServerStateList(), $this->getServerStateRangeOptions()));
        exit;
    }

    public function stateLive()
    {
        $this->layout_name = false;
        $this->layout = false;
        $this->view = false;
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(ServerStateTimeline::buildLivePayload($this->getServerStateList(), $this->getServerStateRangeOptions()));
        exit;
    }

    private function getServerStateRangeOptions(): array
    {
        return array(
            'range' => $_GET['range'] ?? '1h',
            'range_mode' => $_GET['range_mode'] ?? 'preset',
            'start' => $_GET['start'] ?? null,
            'end' => $_GET['end'] ?? null,
        );
    }

    private function getServerStateList(): array
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT a.id, a.name, a.display_name
            FROM mysql_server a
            INNER JOIN client c ON c.id = a.id_client
            WHERE a.is_deleted = 0
            AND a.is_monitored = 1
            AND c.is_monitored = 1 " . self::getFilter([], 'a') . "
            ORDER BY a.name ASC";

        $res = $db->sql_query($sql);
        $servers = [];

        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $servers[] = [
                'id' => (int) $row['id'],
                'name' => !empty($row['display_name']) ? $row['display_name'] : $row['name'],
                'display_html' => Display::srv((int) $row['id'], true, LINK . 'MysqlServer/main/' . (int) $row['id'] . '/'),
            ];
        }

        return $servers;
    }

/**
 * Handle server state through `logs`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for logs.
 * @phpstan-return void
 * @psalm-return void
 * @see self::logs()
 * @example /fr/server/logs
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function logs()
    {
//used with recursive
    }

/**
 * Handle server state through `buildQuery`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $fields Input value for `fields`.
 * @phpstan-param mixed $fields
 * @psalm-param mixed $fields
 * @return mixed Returned value for buildQuery.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::buildQuery()
 * @example /fr/server/buildQuery
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    private function buildQuery($fields)
    {
        $sql = 'select a.ip, a.port, a.id, a.name,';

        $i   = 0;
        $tmp = [];
        foreach ($fields as $field) {
            $tmp[] = " c$i.value as $field";
            $i++;
        }

        $sql .= implode(",", $tmp);
        $sql .= " from mysql_server a ";
        $sql .= " INNER JOIN status_max_date b ON a.id = b.id_mysql_server ";

        $tmp = [];
        $i   = 0;
        foreach ($fields as $field) {
            $sql .= " LEFT JOIN status_value_int c$i ON c$i.id_mysql_server = a.id AND b.date = c$i.date";
            $sql .= " LEFT JOIN status_name d$i ON d$i.id = c$i.id_status_name ";
            $i++;
        }

        $sql .= " WHERE 1 ".$this->getFilter()."";

        $tmp = [];
        $i   = 0;
        foreach ($fields as $field) {
            $sql .= " AND d$i.name = '".$field."'  ";
            $i++;
        }

        $sql .= ";";
        return $sql;
    }

/**
 * Handle server state through `memory`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for memory.
 * @phpstan-return void
 * @psalm-return void
 * @see self::memory()
 * @example /fr/server/memory
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function memory()
    {
        $this->title  = __("Memory");
        $this->ariane = " > ".__("Tools Box")." > ".$this->title;

        $db = Sgbd::sql(DB_DEFAULT);

        $data['variables'] = Extraction::display(array("variables::innodb_buffer_pool_size", "variables::innodb_additional_mem_pool_size",
                "variables::innodb_log_buffer_size", "variables::key_buffer_size", "variables::read_buffer_size",
                "variables::query_cache_size", "variables::tmp_table_size", "variables::max_connections", "status::max_used_connections",
                "variables::sort_buffer_size", "variables::read_rnd_buffer_size", "variables::join_buffer_size", "variables::thread_stack",
                "variables::binlog_cache_size", "variables::innodb_buffer_pool_chunk_size", "variables::innodb_buffer_pool_instances", 
                "ssh_stats::memory_total", "databases::databases"));

        $data['database'] = Extraction::getSizeByEngine($data['variables']);

        $this->set('data', $data);
    }

/**
 * Handle server state through `engineMemory`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for engineMemory.
 * @phpstan-return void
 * @psalm-return void
 * @see self::engineMemory()
 * @example /fr/server/engineMemory/1
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function engineMemory($param)
    {
        if (empty($param[0]) || !ctype_digit((string) $param[0])) {
            throw new \InvalidArgumentException('Usage: /server/engineMemory/{id_mysql_server}');
        }

        $idMysqlServer = (int) $param[0];

        $this->title = __("Engine Memory");
        $this->ariane = " > ".__("Tools Box")." > ".$this->title;

        $data = EngineMemoryBreakdown::build($idMysqlServer, [
            'range' => $_GET['range'] ?? '6h',
            'range_mode' => $_GET['range_mode'] ?? 'preset',
            'start' => $_GET['start'] ?? null,
            'end' => $_GET['end'] ?? null,
        ]);

        $this->di['js']->addJavascript(array('chart-4.5.1.umd.min.js', 'Server/engineMemory.js'));
        $this->di['js']->code_javascript('window.engineMemoryChartPayload = '.json_encode($data['process_memory_chart']).';');

        $this->set('data', $data);
    }

/**
 * Render server state through `index`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for index.
 * @phpstan-return void
 * @psalm-return void
 * @see self::index()
 * @example /fr/server/index
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function index()
    {
        $this->title  = __("Index usage");
        $this->ariane = " > ".__("Tools Box")." > ".$this->title;

        $db = Sgbd::sql(DB_DEFAULT);

        $this->di['js']->code_javascript('
        $(function () {
  $(\'[data-toggle="tooltip"]\').tooltip();
})');

        $data['status'] = Extraction::display(array("status::handler_read_rnd_next", "status::handler_read_rnd", "status::handler_read_first",
                "status::handler_read_next", "status::handler_read_key", "status::handler_read_prev"));

        $data['mysql'] = $this->getServer();

        $this->set('data', $data);
    }
    /*
     *
     * graph with Chart.js
     *
     */

    public function id($param)
    {

        /*
          select avg(Column), convert((min(datetime) div 500)*500 + 230, datetime) as time
          from Databasename.tablename
          where datetime BETWEEN '2012-09-08 00:00:00' AND '2012-09-08 15:30:00'
          group by datetime div 500
         *
         * select avg(Column), convert((min(datetime) div 500)*500 + 230, datetime) as time
          from Databasename.tablename
          where datetime BETWEEN '2012-09-08 00:00:00' AND '2012-09-08 15:30:00'
          group by datetime div 500
         */
//$this->di['js']->addJavascript(array("Chart.Core.js", "Chart.Scatter.min.js"));

        $this->di['js']->addJavascript(array('bootstrap-select.min.js'));
        $this->di['js']->addJavascript(array("moment.js", "chart.min.js"));
        $db = Sgbd::sql(DB_DEFAULT);

        $data = array();

        if ($_SERVER['REQUEST_METHOD'] === "GET" && !empty($_GET['server_id_filter'])) {
            $filter = self::normalizeIdFilterPayload($_GET);
            if ($filter !== null) {
                header('location: '.LINK.$this->getClass()
                    .'/'.__FUNCTION__.'/mysql_server:id:'.$filter['id_mysql_server']
                    .'/ts_variable:name:'.$filter['ts_variable_name']
                    .'/ts_variable:date:'.$filter['ts_variable_date']
                    .'/ts_variable:derivate:'.$filter['ts_variable_derivate']);
                exit;
            }

            header('location: '.LINK.$this->getClass().'/'.__FUNCTION__);
            exit;
        }
            // get server available
            //TODO : add available ===1
            $sql             = "SELECT * FROM mysql_server a WHERE 1=1 ".$this->getFilter()." order by a.name ASC";
            $res             = $db->sql_query($sql);
            $data['servers'] = array();
            while ($ob              = $db->sql_fetch_object($res)) {
                $tmp               = [];
                $tmp['id']         = $ob->id;
                $tmp['libelle']    = $ob->name." (".$ob->ip.")";
                $data['servers'][] = $tmp;
            }

            // get variable available
            $sql            = "SELECT name,`from` FROM ts_variable 
            WHERE `type` in('INT','DOUBLE') AND `from` NOT IN ('variables', 'slave')
            order by `from`,name ASC";
            $res            = $db->sql_query($sql);
            $data['status'] = array();
            while ($ob             = $db->sql_fetch_object($res)) {
                $tmp              = [];
                $tmp['id']        = $ob->name;
                $tmp['libelle']   = '<i style="color:#bbbbbb">'.$ob->from ."</i> ". $ob->name;
                $data['status'][] = $tmp;
            }

            $interval = array('5-minute', '15-minute', '1-hour', '2-hour', '6-hour', '12-hour', '1-day', '2-day', '1-week', '2-week', '1-month');
            $libelles = array('5 minutes', '15 minutes', '1 hour', '2 hours', '6 hours', '12 hours', '1 day', '2 days', '1 week', '2 weeks',
                '1 month');
            $elems    = array(60 * 5, 60 * 15, 3600, 3600 * 2, 3600 * 6, 3600 * 12, 3600 * 24, 3600 * 48, 3600 * 24 * 7, 3600 * 24 * 14, 3600 * 24 * 30);

            $data['interval'] = array();
            $i                = 0;
            foreach ($libelles as $libelle) {
                $tmp                = [];
                $tmp['id']          = $interval[$i];
                $tmp['libelle']     = $libelle;
                $data['interval'][] = $tmp;
                $i++;
            }

            $data['derivate'][0]['id']      = 1;
            $data['derivate'][0]['libelle'] = __("Yes");
            $data['derivate'][1]['id']      = 2;
            $data['derivate'][1]['libelle'] = __("No");

            if (empty($_GET['ts_variable']['date'])) {
                $_GET['ts_variable']['date'] = "1 hour";
            }

            $activeFilter = self::normalizeIdFilterPayload($_GET);
            if ($activeFilter !== null) {
                
                
                $gg = Extraction::extract(array("version"), array(1));

                $res = Extraction::extract(
                    array($activeFilter['ts_variable_name']),
                    array($activeFilter['id_mysql_server']),
                    str_replace("-", " ", $activeFilter['ts_variable_date'])
                );


                $data['date_max'] = date('Y-m-d H:i:s');
                $res10 = $db->sql_query("SELECT NOW() as maintenant;");
                while($ob = $db->sql_fetch_object($res10)) {
                    $data['date_max'] = $ob->maintenant;
                }

                //debug($res);
                /*
                  $sql = "SELECT * FROM status_value_int a
                  WHERE a.id_mysql_server = " . $_GET['mysql_server']['id'] . "
                  AND a.id_status_name = '" . $_GET['status_name']['id'] . "'
                  and a.`date` > date_sub(now(), INTERVAL " . $_GET['status_value_int']['date'] . ") ORDER BY a.`date` ASC;";
                  $data['sql'] = $sql; */


                $data['graph'] = array();
                if ($res !== false) {
                    while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                        $data['graph'][] = $arr;
                    }
                }

                $dates = [];
                $val   = [];

                /*
                  $sql2 = "SELECT name FROM ts_variables WHERE from ='status' and id= '" . $_GET['status_name']['name'] . "'";
                  //debug($sql2);
                  $res2 = $db->sql_query($sql2);
                  while ($ob2 = $db->sql_fetch_object($res2)) {
                  $name = $ob2->name;
                  } */

                $name = $activeFilter['ts_variable_name'];
                $i    = 0;

                $old_date = "";
                $point    = [];

                if (!empty($data['graph'])) {

                    foreach ($data['graph'] as $value) {

                        if (empty($old_date) && $activeFilter['ts_variable_derivate'] == "1") {

                            $old_date  = $value['date'];
                            $old_value = $value['value'];
                            continue;
                        } elseif ($activeFilter['ts_variable_derivate'] == "1") {

                            $datetime1 = strtotime($old_date);
                            $datetime2 = strtotime($value['date']);
                            $secs = $datetime2 - $datetime1; // == <seconds between the two times>
//echo $datetime1. ' '.$datetime2 . ' : '. $secs." ".$value['value'] ." - ". $old_value." => ".($value['value']- $old_value)/ $secs."<br>";
                            //to prevent divide by zero
                            if ($secs == 0) {
                                continue;
                            }

                            $derivate = round(($value['value'] - $old_value) / $secs, 2);

                            if ($derivate < 0) {
                                $derivate = 0;
                            }

                            $val = $derivate;

//$points[] = "{ x: " . $datetime2 . ", y :" . $derivate . "}";
                        } else {
                            $val = $value['value'];
                        }

                        $point[] = "{ x: new Date('".$value['date']."'), y: ".$val."}";
                        $dates[] = $value['date'];

                        $old_date  = $value['date'];
                        $old_value = $value['value'];
                    }
                }

//$point2[] = "{ x: new Date('2017-10-28 00:38:34'), y: 50}";
//$point2[] = "{ x: new Date('2017-10-28 00:37:34'), y: 40}";
//$date = implode('","', $dates);
//$vals = implode(',', $val);
                $points = implode(',', $point);
//$points2 = implode(',', $point2);

                $this->di['js']->code_javascript('
var ctx = document.getElementById("myChart").getContext("2d");


var myChart = new Chart(ctx, {
    type: "line",
    data: {
        datasets: [{
            label: '.json_encode($name).',
            data: ['.$points.'],
                borderWidth: 1,
             pointRadius :0,
             lineTension: 0

        },
]
    },
    options: {
        bezierCurve: false,
        title: {
            display: true,
            text: " ",
            position: "top",
            padding: "0"
        },
        pointDot : false,
        scales: {
            xAxes: [{

                type: "time",
                display: true,
                scaleLabel: {
                  display: true,
                  labelString: "Date",
                },
                distribution: "linear",
                time: {

                    max: new Date("'.$data['date_max'].'"),   /* date(Y-m-d H:i:s) : '.date("Y-m-d H:i:s").' => problem with timezone why ? */
                    tooltipFormat: "dddd YYYY-MM-DD, HH:mm:ss",
                    displayFormats: {
          minute: "dddd YYYY-MM-DD, HH:mm"
        }

                }

            }],
             yAxes: [{


      scaleLabel: {
        display: true,
        labelString: "Queries by second",

      }

    }]
        }
    }
});



');

                /*
                  Chart.defaults.global.responsive = true;
                  Chart.defaults.global.animation = false;

                  $(function () {




                  var data3 = [
                  {
                  label: "temperature",
                  strokeColor: "rgba(0,0,0,0.5)",
                  data: [
                  '.$points.',



                  ]
                  }];

                  var ctx3 = document.getElementById("myChart").getContext("2d");
                  var myDateLineChart = new Chart(ctx3).Scatter(data3 ,{
                  bezierCurve: false,
                  bezierCurveTension: 0,
                  showTooltips: true,
                  scaleShowHorizontalLines: true,
                  scaleShowLabels: true,
                  scaleType: "date",
                  pointBackgroundColor: "rgba(0,0,0,0.3)",
                  borderWidth: 1,
                  pointDot : false,
                  pointRadius :1,
                  fill: true,
                  useUtc: false,
                  beginAtZero:true,
                  backgroundColor:"#00f",

                  datasetStrokeWidth:1,

                  fillColor: "rgba(151,187,205,0.2)",
                  strokeColor: "rgba(151,187,205,1)",
                  pointColor: "rgba(151,187,205,1)",
                  pointStrokeColor: "#fff",
                  pointHighlightFill: "#fff",
                  pointHighlightStroke: "rgba(151,187,205,0.8)",
                  scaleDateFormat: "mmm d",
                  scaleTimeFormat: "HH:MM",
                  scaleDateTimeFormat: "mmm d, yyyy, hh:MM",
                  pointDot: true,
                  pointDotStrokeWidth: 1,
                  pointDotRadius: 0,
                  pointHitDetectionRadius: 2,

                  });





                  });
                  ');
                  /*
                 *
                 */
            } else {
                if (empty($data['servers'])) $data['servers']  = "";
                if (empty($data['status'])) $data['status']   = "";
                if (empty($data['interval'])) $data['interval'] = "";
                if (empty($data['derivate'])) $data['derivate'] = "";


                $data['fields_required'] = 1;
            }

        $this->set('data', $data);
    }

    public static function normalizeIdFilterPayload(array $source): ?array
    {
        if (
            empty($source['mysql_server']['id'])
            || empty($source['ts_variable']['name'])
            || empty($source['ts_variable']['date'])
            || empty($source['ts_variable']['derivate'])
            || !is_scalar($source['mysql_server']['id'])
            || !is_scalar($source['ts_variable']['name'])
            || !is_scalar($source['ts_variable']['date'])
            || !is_scalar($source['ts_variable']['derivate'])
        ) {
            return null;
        }

        $idMysqlServer = self::normalizeSettingsPositiveInteger($source['mysql_server']['id']);
        if ($idMysqlServer === null) {
            return null;
        }

        $name = trim((string) $source['ts_variable']['name']);
        if ($name === '' || strlen($name) > 128 || preg_match('/^[A-Za-z0-9_.-]+$/', $name) !== 1) {
            return null;
        }

        $date = str_replace(' ', '-', trim((string) $source['ts_variable']['date']));
        if (!in_array($date, self::SERVER_ID_FILTER_INTERVALS, true)) {
            return null;
        }

        $derivate = trim((string) $source['ts_variable']['derivate']);
        if (!in_array($derivate, ['1', '2'], true)) {
            return null;
        }

        return [
            'id_mysql_server' => $idMysqlServer,
            'ts_variable_name' => $name,
            'ts_variable_date' => $date,
            'ts_variable_derivate' => $derivate,
        ];
    }

/**
 * Handle server state through `settings`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for settings.
 * @phpstan-return void
 * @psalm-return void
 * @see self::settings()
 * @example /fr/server/settings
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function settings()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        if (CsrfGuard::isPost($_SERVER) && !empty($_POST['settings'])) {
            $outcome = self::evaluateSettingsRequest($_POST, $_SERVER, $_SESSION);
            if ($outcome['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendSettingsError($outcome['status'], $outcome['body'], $outcome['headers']);
                return;
            }

            $settings = $outcome['settings'];
            foreach ($settings['servers'] as $server) {
                $server_main = array();
                $server_main['mysql_server']['id'] = $server['id'];
                $server_main['mysql_server']['display_name'] = $server['display_name'];
                $server_main['mysql_server']['id_client'] = $server['id_client'];
                $server_main['mysql_server']['id_environment'] = $server['id_environment'];
                $server_main['mysql_server']['is_monitored'] = $server['is_monitored'];
                $server_main['mysql_server']['is_proxy'] = $server['is_proxy'];
                $server_main['mysql_server']['is_vip'] = $server['is_vip'];

                $ret = $db->sql_save($server_main);

                if (!$ret) {
                    print_r($db->sql_error());
                }
            }

            if ($settings['tags_submitted']) {
                $sql = "BEGIN";
                $db->sql_query($sql);

                try {
                    $sql = "delete from link__mysql_server__tag where id_mysql_server in (".implode(",", $settings['server_ids']).")";
                    $db->sql_query($sql);
                    foreach ($settings['tags'] as $key => $tags) {
                        foreach ($tags as $tag) {
                            $sql = "INSERT INTO link__mysql_server__tag (`id_mysql_server`, `id_tag`) VALUES ('".$settings['servers'][$key]['id']."','".$tag."')";
                            $db->sql_query($sql);
                        }
                    }
                    $sql = "COMMIT";
                    $db->sql_query($sql);
                } catch (\Exception $ex) {
                    $sql = "ROLLBACK";
                    $db->sql_query($sql);
                }
            }

            header("location: ".LINK."Server/settings");
            exit;
        }

        $this->title  = '<i class="fa fa-server"></i> '.__("Servers");
        $this->ariane = ' > <a href⁼"">'.'<i class="fa fa-cog" style="font-size:14px"></i> '
            .__("Settings").'</a> > <i class="fa fa-server"  style="font-size:14px"></i> '.__("Servers");

        $data['ssh']   = Extraction::display(array("ssh_available"));
        $data['mysql'] = Extraction::display(array("mysql_available"));

        $sql             = "SELECT * FROM mysql_server a WHERE is_deleted=0 ".self::getFilter()." ORDER by name";
        $data['servers'] = $db->sql_fetch_yield($sql);

        $data['clients']      = $this->getClients();
        $data['environments'] = $this->getEnvironments();

        // tag
        $sql         = "SELECT * FROM tag order by name";
        $res         = $db->sql_query($sql);
        $data['tag'] = array();
        while ($ob          = $db->sql_fetch_object($res)) {
            $tmp            = array();
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->name;
            $tmp['extra']   = array("data-content" => "<span title='".$ob->name."' class='label' style='color:".$ob->color."; background:".$ob->background."'>".$ob->name."</span>");

            $data['tag'][] = $tmp;
        }

        $sql = "SELECT * FROM link__mysql_server__tag";
        $res = $db->sql_query($sql);
        while ($ob  = $db->sql_fetch_object($res)) {

            $data['tag_selected'][$ob->id_mysql_server][] = $ob->id_tag;
        }

        $data['server_settings_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['server_settings_csrf_token'] = Csrf::issueToken($_SESSION, self::SERVER_SETTINGS_CSRF_SCOPE);
        $data['server_remove_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['server_remove_csrf_token'] = Csrf::issueToken($_SESSION, self::SERVER_REMOVE_CSRF_SCOPE);

        $this->set('data', $data);
    }

    public static function evaluateSettingsRequest(array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::SERVER_SETTINGS_CSRF_SCOPE)) {
            return self::buildSettingsOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $settings = self::normalizeSettingsPayload($post);
        if ($settings === null) {
            return self::buildSettingsOutcome(400, 'Invalid server settings payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'settings' => $settings,
        ];
    }

    public static function normalizeSettingsPayload(array $post): ?array
    {
        if (empty($post['settings']) || empty($post['id']) || !is_array($post['id'])) {
            return null;
        }

        if (empty($post['mysql_server']) || !is_array($post['mysql_server'])) {
            return null;
        }

        $servers = [];
        $serverIds = [];
        foreach ($post['id'] as $key => $value) {
            $id = self::normalizeSettingsPositiveInteger($value);
            if ($id === null || empty($post['mysql_server'][$key]) || !is_array($post['mysql_server'][$key])) {
                return null;
            }

            $row = $post['mysql_server'][$key];
            foreach (['display_name', 'id_client', 'id_environment'] as $field) {
                if (!array_key_exists($field, $row) || !is_scalar($row[$field])) {
                    return null;
                }
            }

            $displayName = trim((string) $row['display_name']);
            if (strlen($displayName) > self::SERVER_SETTINGS_DISPLAY_NAME_MAX_LENGTH) {
                return null;
            }

            $idClient = self::normalizeSettingsPositiveInteger($row['id_client']);
            $idEnvironment = self::normalizeSettingsPositiveInteger($row['id_environment']);
            if ($idClient === null || $idEnvironment === null) {
                return null;
            }

            $isMonitored = self::normalizeSettingsFlag($row, 'is_monitored');
            $isProxy = self::normalizeSettingsFlag($row, 'is_proxy');
            $isVip = self::normalizeSettingsFlag($row, 'is_vip');
            if ($isMonitored === null || $isProxy === null || $isVip === null) {
                return null;
            }

            $servers[$key] = [
                'id' => $id,
                'display_name' => $displayName,
                'id_client' => $idClient,
                'id_environment' => $idEnvironment,
                'is_monitored' => $isMonitored,
                'is_proxy' => $isProxy,
                'is_vip' => $isVip,
            ];
            $serverIds[] = $id;
        }

        $tagsSubmitted = !empty($post['link__mysql_server_tag']);
        $tags = [];
        if ($tagsSubmitted) {
            if (!is_array($post['link__mysql_server_tag'])) {
                return null;
            }

            foreach ($post['link__mysql_server_tag'] as $key => $groups) {
                if (!array_key_exists($key, $servers) || !is_array($groups)) {
                    return null;
                }

                $tags[$key] = [];
                foreach ($groups as $tagList) {
                    if (!is_array($tagList)) {
                        return null;
                    }

                    foreach ($tagList as $tagValue) {
                        $tag = self::normalizeSettingsPositiveInteger($tagValue);
                        if ($tag === null) {
                            return null;
                        }
                        $tags[$key][$tag] = $tag;
                    }
                }
                $tags[$key] = array_values($tags[$key]);
            }
        }

        return [
            'servers' => $servers,
            'server_ids' => $serverIds,
            'tags_submitted' => $tagsSubmitted,
            'tags' => $tags,
        ];
    }

    private static function normalizeSettingsPositiveInteger($value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || !ctype_digit($value) || (int) $value < 1) {
            return null;
        }

        return (int) $value;
    }

    private static function normalizeSettingsFlag(array $row, string $field): ?int
    {
        if (!array_key_exists($field, $row) || $row[$field] === '' || $row[$field] === null) {
            return 0;
        }

        if (!is_scalar($row[$field])) {
            return null;
        }

        return 1;
    }

    private static function buildSettingsOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'settings' => null,
        ];
    }

    private static function sendSettingsError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

/**
 * Retrieve server state through `getClients`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getClients.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getClients()
 * @example /fr/server/getClients
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getClients()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from client order by libelle";
        $res = $db->sql_query($sql);

        $data['client'] = array();

        while ($ob = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['client'][] = $tmp;
        }

        return $data['client'];
    }

/**
 * Retrieve server state through `getEnvironments`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for getEnvironments.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::getEnvironments()
 * @example /fr/server/getEnvironments
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getEnvironments()
    {

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * from environment order by libelle";
        $res = $db->sql_query($sql);

        $data['environment'] = array();
        while ($ob                  = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['environment'][] = $tmp;
        }


        return $data['environment'];
    }

/**
 * Create server state through `add`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for add.
 * @phpstan-return void
 * @psalm-return void
 * @see self::add()
 * @example /fr/server/add
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function add()
    {
        $db = Sgbd::sql(DB_DEFAULT);
    }

/**
 * Handle server state through `cache`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for cache.
 * @phpstan-return void
 * @psalm-return void
 * @see self::cache()
 * @example /fr/server/cache
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function cache()
    {
        $db = Sgbd::sql(DB_DEFAULT);

// Qcache_hits / (Qcache_hits + Com_select )
    }

/**
 * Handle server state through `passwd`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for passwd.
 * @phpstan-return void
 * @psalm-return void
 * @see self::passwd()
 * @example /fr/server/passwd
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function passwd($param)
    {

        $this->di['js']->addJavascript(array('clipboard.min.js'));

        /**
         * @todo Add a new version code_javascript  => Once
         */
        /*
          $this->di['js']->code_javascript('(function() {
          new Clipboard(".copy-button");
          })();');
         */

        Crypt::$key       = CRYPT_KEY;
        $data['password'] = Crypt::decrypt($param[0]);
        $this->set('data', $data);
    }

/**
 * Handle server state through `show`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for show.
 * @phpstan-return void
 * @psalm-return void
 * @see self::show()
 * @example /fr/server/show
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function show($param)
    {
        $this->layout_name = false;
        $this->layout = false;
        $this->view = false;
    }

/**
 * Update server state through `updateHostname`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @return void Returned value for updateHostname.
 * @phpstan-return void
 * @psalm-return void
 * @see self::updateHostname()
 * @example /fr/server/updateHostname
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function updateHostname()
    {
        $db = Sgbd::sql(DB_DEFAULT);

        $data['hostname'] = Extraction::display(array("variables::hostname"));

        foreach ($data['hostname'] as $id_mysql_server => $servers) {
            $server = $servers[''];

            echo $server['hostname']."\n";
            $sql = "UPDATE mysql_server SET display_name ='".$server['hostname']."' WHERE id = ".$id_mysql_server;
            $db->sql_query($sql);
        }
    }

/**
 * Handle server state through `box`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for box.
 * @phpstan-return void
 * @psalm-return void
 * @see self::box()
 * @example /fr/server/box
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function box()
    {
        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * from mysql_server where is_available = 0;";
        $res = $db->sql_query($sql);

        $data = array();

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {


            if (strstr($arr['error'], 'Call Stack:')) {
                //echo end(explode("\n", $server['error']));
                preg_match_all("/\[[\s0-9:_-]+\]\[ERROR\](.*)/", $arr['error'], $output_array);

                if (!empty($output_array[0][0])) {
                    $arr['error'] = $output_array[0][0];
                }

                //echo $server['error'];
            }


            $data['box'][] = $arr;
        }


        $this->set('data', $data);
    }

/**
 * Handle server state through `password`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for password.
 * @phpstan-return void
 * @psalm-return void
 * @throws \Throwable When the underlying operation fails.
 * @see self::password()
 * @example /fr/server/password
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function password($param)
    {
        $id_server = self::normalizeSettingsPositiveInteger($param[0] ?? null);

        if ($id_server === null) {
            throw new \Exception("PMACTRL-748 : Impossible to get id_server, wrong URL ?");
        }

        if (CsrfGuard::isPost($_SERVER)) {
            $outcome = self::evaluatePasswordRequest($param, $_POST, $_SERVER, $_SESSION);
            if ($outcome['status'] !== 200) {
                $this->view = false;
                $this->layout_name = false;
                self::sendPasswordError($outcome['status'], $outcome['body'], $outcome['headers']);
                return;
            }

            $passwordRequest = $outcome['password'];
            $db = Sgbd::sql(DB_DEFAULT);
            $server['mysql_server']['passwd'] = Chiffrement::encrypt($passwordRequest['passwd']);
            $server['mysql_server']['login']  = $passwordRequest['login'];
            $server['mysql_server']['id']     = $passwordRequest['id_server'];

            $ret = $db->sql_save($server);

            if ($ret) {

                Mysql::onAddMysqlServer();

                set_flash("success", "Success", "Password updated !");

                header("location: ".LINK.$this->getClass().'/settings');
                return;
            } else {
                set_flash("error", "Error", "Password not updated !");

                header("location: ".LINK.$this->getClass().'/'.__FUNCTION__.'/'.$id_server);
                return;
            }
        }

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server WHERE id =".$id_server;

        $res = $db->sql_query($sql);

        $data['server'] = array();
        while ($ob             = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['server'] = $ob;
        }

        $data['server_password_csrf_field'] = Csrf::DEFAULT_FIELD;
        $data['server_password_csrf_token'] = Csrf::issueToken($_SESSION, self::SERVER_PASSWORD_CSRF_SCOPE);

        $this->set('data', $data);
    }

    public static function evaluatePasswordRequest(array $param, array $post, array $server, array $session): array
    {
        if ($failure = CsrfGuard::ensureOrFail($post, $server, $session, self::SERVER_PASSWORD_CSRF_SCOPE)) {
            return self::buildPasswordOutcome($failure['status'], $failure['body'], $failure['headers']);
        }

        $password = self::normalizePasswordPayload($param, $post);
        if ($password === null) {
            return self::buildPasswordOutcome(400, 'Invalid server password payload');
        }

        return [
            'status' => 200,
            'body' => '',
            'headers' => [],
            'password' => $password,
        ];
    }

    public static function normalizePasswordPayload(array $param, array $post): ?array
    {
        $idServer = self::normalizeSettingsPositiveInteger($param[0] ?? null);
        if ($idServer === null || empty($post['mysql_server']) || !is_array($post['mysql_server'])) {
            return null;
        }

        $mysqlServer = $post['mysql_server'];
        if (
            !array_key_exists('login', $mysqlServer)
            || !array_key_exists('passwd', $mysqlServer)
            || !is_scalar($mysqlServer['login'])
            || !is_scalar($mysqlServer['passwd'])
        ) {
            return null;
        }

        $login = trim((string) $mysqlServer['login']);
        $passwd = (string) $mysqlServer['passwd'];
        if (
            $login === ''
            || $passwd === ''
            || strlen($login) > self::SERVER_PASSWORD_LOGIN_MAX_LENGTH
            || strlen($passwd) > self::SERVER_PASSWORD_VALUE_MAX_LENGTH
        ) {
            return null;
        }

        return [
            'id_server' => $idServer,
            'login' => $login,
            'passwd' => $passwd,
        ];
    }

    private static function buildPasswordOutcome(int $statusCode, string $message, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'body' => $message,
            'headers' => $headers,
            'password' => null,
        ];
    }

    private static function sendPasswordError(int $statusCode, string $message, array $headers = []): void
    {
        http_response_code($statusCode);
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
    }

    public static function evaluateAcknowledgeRequest(array $post, array $server, array $session, array $param): array
    {
        return self::evaluateServerMutationRequest(
            $post,
            $server,
            $session,
            $param,
            self::SERVER_ACKNOWLEDGE_CSRF_SCOPE
        );
    }

    public static function evaluateRetractRequest(array $post, array $server, array $session, array $param): array
    {
        return self::evaluateServerMutationRequest(
            $post,
            $server,
            $session,
            $param,
            self::SERVER_RETRACT_CSRF_SCOPE
        );
    }

    public static function evaluateRemoveRequest(array $post, array $server, array $session, array $param): array
    {
        return self::evaluateServerMutationRequest(
            $post,
            $server,
            $session,
            $param,
            self::SERVER_REMOVE_CSRF_SCOPE
        );
    }

    public static function buildAcknowledgeSql(int $idServer, int $idUser): string
    {
        return "UPDATE mysql_server SET is_acknowledged=".$idUser." WHERE id=".$idServer." LIMIT 1;";
    }

    public static function buildRetractSql(int $idServer): string
    {
        return "UPDATE mysql_server SET is_acknowledged=0 WHERE id=".$idServer." LIMIT 1;";
    }

    public static function buildRemoveSelectSql(int $idServer): string
    {
        return "SELECT name FROM mysql_server WHERE id=".$idServer." LIMIT 1;";
    }

    public static function buildRemoveSql(int $idServer): string
    {
        return "UPDATE mysql_server SET is_deleted=1 WHERE id=".$idServer." LIMIT 1;";
    }

    private static function evaluateServerMutationRequest(
        array $post,
        array $server,
        array $session,
        array $param,
        string $scope
    ): array {
        $outcome = RouteMutationRequest::evaluate(
            $post,
            $server,
            $session,
            $param,
            $scope,
            'Invalid server id'
        );

        return [
            'status' => $outcome['status'],
            'body' => $outcome['body'],
            'headers' => $outcome['headers'],
            'id_server' => $outcome['id'],
        ];
    }

/**
 * Handle server state through `acknowledge`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for acknowledge.
 * @phpstan-return void
 * @psalm-return void
 * @see self::acknowledge()
 * @example /fr/server/acknowledge
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function acknowledge($param)
    {
        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateAcknowledgeRequest($_POST, $_SERVER, $_SESSION, is_array($param) ? $param : []);
        if ($outcome['status'] !== 200) {
            HttpResponse::sendOutcome($outcome);
            return;
        }

        $id_server = $outcome['id_server'];
        $id_user = (int) ($this->di['auth']->getUser()->id ?? 0);

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = self::buildAcknowledgeSql($id_server, $id_user);
        $db->sql_query($sql);

        header("location: ".LINK.$this->getClass()."/main/");
        exit;
    }

/**
 * Delete server state through `remove`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for remove.
 * @phpstan-return void
 * @psalm-return void
 * @see self::remove()
 * @example /fr/server/remove
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function remove($param)
    {
        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateRemoveRequest($_POST, $_SERVER, $_SESSION, is_array($param) ? $param : []);
        if ($outcome['status'] !== 200) {
            HttpResponse::sendOutcome($outcome);
            return;
        }

        Debug::parseDebug($param);
        $id_server  = $outcome['id_server'];

        $db = Sgbd::sql(DB_DEFAULT);

        // pour eviter d'effacer la base de PmaControl !!!
        $sql = self::buildRemoveSelectSql($id_server);
        $res = $db->sql_query($sql);
        Debug::sql($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            if ($ob->name != DB_DEFAULT) {
                $sql = self::buildRemoveSql($id_server);
                $db->sql_query($sql);
                Debug::sql($sql);
            }
        }

        header("location: ".LINK.$this->getClass()."/settings/");
        exit;
    }

/**
 * Handle server state through `acknowledgedBy`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for acknowledgedBy.
 * @phpstan-return void
 * @psalm-return void
 * @see self::acknowledgedBy()
 * @example /fr/server/acknowledgedBy
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function acknowledgedBy($param)
    {
        
    }

/**
 * Toggle server state through `toggleGeneralLog`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for toggleGeneralLog.
 * @phpstan-return void
 * @psalm-return void
 * @see self::toggleGeneralLog()
 * @example /fr/server/toggleGeneralLog
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function toggleGeneralLog($param)
    {
        $this->view = false;

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $general_log     = $param[1];

        $db  = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT * FROM `mysql_server` WHERE `id`=".$id_mysql_server.";";
        Debug::sql($sql);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $name = $ob->name;
        }

        $remote = Sgbd::sql($name);

        if ($general_log === "true") {

            $sql2 = "SET GLOBAL `general_log`=ON;";
        } else {
            $sql2 = "SET GLOBAL `general_log`=OFF;";
        }
        Debug::sql($sql2);

        $remote->sql_query($sql2);
        $data['return'] = 'ok';

        echo json_encode($data);
    }

    /*
        To move in Worker 
    */

    public function getDaemonRunning($param)
    {
        Debug::parseDebug($param);

        $worker_type = $param[0] ?? '';
        $type = ['mysql', 'maxscale', 'proxysql', 'ssh'];

        if (! in_array($worker_type, $type))
        {
            throw new \Exception("PMATRCL-1009 : This worker type is unknow : '$worker_type'");
        }
        //self::testGetDaemonRunning($param);

        $elems          = array();

        $lock_path = EngineV4::PATH_LOCK;
        $lock_ext  = EngineV4::EXT_LOCK;

        // Date exacte 2 secondes avant maintenant
        $two_seconds_ago = date('Y-m-d H:i:s', time() - 2);

        // Commande find : tous les fichiers *.lock modifiés il y a plus de 2 secondes
        $cmd = "find " . escapeshellarg($lock_path) . " -maxdepth 1 -name 'worker_".$worker_type."*." . $lock_ext . "' ! -newermt " . escapeshellarg($two_seconds_ago);
        Debug::debug($cmd, "PROCESSING");

        exec($cmd, $files, $return_code);

        if ($return_code === 0) {
            foreach ($files as $filename) {

                if (!file_exists($filename)) continue;
                // ici tu peux lire ton fichier JSON en toute sécurité
                $json = file_get_contents($filename);
                if ($json === false) continue;

                $data = json_decode($json, true);
                Debug::debug($data);
                if ($data === null) continue;

                Debug::debug(microtime(true));
                $data['time'] = round(Microsecond::timestamp() - $data['microtime'], 2);
                $data['pid'] = Worker::findWorkerPidForServerId(
                    'worker_'.$worker_type,
                    (int)$data['id'],
                    !empty($data['pid']) ? (int)$data['pid'] : null
                );

                Debug::debug($data);
                $seconds = round($data['time'] / 1_000_000,2);

                if ($seconds > 1) {
                    $data['time'] = $seconds;
                    $elems[$data['id']] = $data;
                }
            }
        }

        Debug::debug($elems, "ELEM");

        return $elems;
    }


/**
 * Handle server state through `testGetDaemonRunning`.
 *
 * This action may stream a direct HTTP or CLI response.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for testGetDaemonRunning.
 * @phpstan-return void
 * @psalm-return void
 * @see self::testGetDaemonRunning()
 * @example /fr/server/testGetDaemonRunning
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public static function testGetDaemonRunning($param)
    {
        define('EngineV4_PATH_LOCK', __DIR__ . '/tmp/lock/');
        define('EngineV4_EXT_LOCK', 'lock');

        // Crée le dossier tmp/lock si nécessaire
        if (!is_dir(EngineV4_PATH_LOCK)) {
            mkdir(EngineV4_PATH_LOCK, 0777, true);
        }

        // Génère un fichier lock test
        $testFile = EngineV4_PATH_LOCK . 'worker_test::3453465454567.' . EngineV4_EXT_LOCK;

        // Données JSON simulant un daemon
        $data = [
            'id'        => 1234,
            'microtime' => microtime(true) - 5, // 5 secondes avant pour dépasser le seuil
            'name'      => 'worker_test',
            'status'    => 'running'
        ];

        // Écriture dans le fichier
        file_put_contents($testFile, json_encode($data, JSON_PRETTY_PRINT));

        // Force la modification à 5 secondes avant
        

        echo "Fichier de test créé : $testFile\n";

        // === Lecture rapide pour vérifier ===
        $json = file_get_contents($testFile);
        $decoded = json_decode($json, true);
        print_r($decoded);

        sleep(3);
    }

/**
 * Handle server state through `retract`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for retract.
 * @phpstan-return void
 * @psalm-return void
 * @see self::retract()
 * @example /fr/server/retract
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function retract($param)
    {
        $this->view = false;
        $this->layout_name = false;

        $outcome = self::evaluateRetractRequest($_POST, $_SERVER, $_SESSION, is_array($param) ? $param : []);
        if ($outcome['status'] !== 200) {
            HttpResponse::sendOutcome($outcome);
            return;
        }

        $id_server = $outcome['id_server'];

        $db = Sgbd::sql(DB_DEFAULT);

        $sql = self::buildRetractSql($id_server);
        $db->sql_query($sql);

        header("location: ".LINK.$this->getClass()."/main/");
        exit;
    }


/**
 * Retrieve server state through `getFlag`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for getFlag.
 * @phpstan-return void
 * @psalm-return void
 * @see self::getFlag()
 * @example /fr/server/getFlag
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function getFlag($param)
    {
        $reader = new Reader('/path/to/maxmind-database.mmdb');

        $record = $reader->city('128.101.101.101');

        print($record->country->isoCode);


    }

/**
 * Handle server state through `ssl`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param array<int,mixed> $param Route parameters forwarded by the router.
 * @phpstan-param array<int,mixed> $param
 * @psalm-param array<int,mixed> $param
 * @return void Returned value for ssl.
 * @phpstan-return void
 * @psalm-return void
 * @see self::ssl()
 * @example /fr/server/ssl
 * @category PmaControl
 * @package App
 * @subpackage Controller
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function ssl($param)
    {
        Debug::parseDebug($param);
        $ssl = Extraction2::display(array("ssl_cipher"));

        Debug::debug($ssl, "SSL");

    }
    
}
