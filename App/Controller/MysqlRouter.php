<?php

namespace App\Controller;

use App\Library\Debug;
use App\Library\Http\HttpResponse;
use App\Library\Mysql;
use App\Library\Security\CsrfGuard;
use Glial\Sgbd\Sgbd;
use Glial\Security\Csrf;
use Glial\Synapse\Controller;

class MysqlRouter extends Controller
{
    public static string $version_api = 'api/20190715';
    public static array $mysqlrouter_mysql_server_links_cache = [];
    private const MYSQLROUTER_ADD_CSRF_SCOPE = 'mysqlrouter.add';
    private const MYSQLROUTER_ADD_TEXT_MAX_LENGTH = 255;
    private const MYSQLROUTER_ADD_PASSWORD_MAX_LENGTH = 1024;

    public function index()
    {
        $this->title = 'MySQL Router';

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT a.*, GROUP_CONCAT(b.id_mysql_server) AS id_mysql_servers
                FROM mysqlrouter_server a
                LEFT JOIN mysqlrouter_server__mysql_server b ON a.id = b.id_mysqlrouter_server
                GROUP BY a.id
                ORDER BY a.display_name";
        $res = $db->sql_query($sql);

        $linkedServers = [];
        $resLinked = $db->sql_query(
            "SELECT a.id_mysqlrouter_server, b.id, b.display_name, b.ip, b.port
             FROM mysqlrouter_server__mysql_server a
             INNER JOIN mysql_server b ON a.id_mysql_server = b.id
             WHERE b.is_deleted = 0
             ORDER BY a.id_mysqlrouter_server, b.ip, b.port"
        );

        while ($arr = $db->sql_fetch_array($resLinked, MYSQLI_ASSOC)) {
            $linkedServers[(int) $arr['id_mysqlrouter_server']][] = $arr;
        }

        $data = [];
        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $arr['mysql_servers'] = $linkedServers[(int) $arr['id']] ?? [];
            $arr['router_status'] = [];
            $arr['routing_status'] = [];
            $arr['routes'] = [];
            $arr['metadata'] = [];
            $arr['api_error'] = '';

            try {
                $config = [
                    'hostname' => $arr['hostname'],
                    'is_ssl' => $arr['is_ssl'],
                    'port' => $arr['port'],
                    'login' => $arr['login'],
                    'password' => $arr['password'],
                ];

                $arr['router_status'] = self::curl([
                    $config['hostname'],
                    $config['is_ssl'],
                    $config['port'],
                    $config['login'],
                    $config['password'],
                    'router/status',
                ]);

                $arr['routing_status'] = self::curl([
                    $config['hostname'],
                    $config['is_ssl'],
                    $config['port'],
                    $config['login'],
                    $config['password'],
                    'routing/status',
                ]);

                $arr['routes'] = self::fetchRouteDefinitions($config);

                foreach (self::fetchMetadataNames($config) as $metadataName) {
                    $arr['metadata'][$metadataName] = [
                        'config' => self::curl([
                            $config['hostname'],
                            $config['is_ssl'],
                            $config['port'],
                            $config['login'],
                            $config['password'],
                            'metadata/' . $metadataName . '/config',
                        ]),
                        'status' => self::curl([
                            $config['hostname'],
                            $config['is_ssl'],
                            $config['port'],
                            $config['login'],
                            $config['password'],
                            'metadata/' . $metadataName . '/status',
                        ]),
                    ];
                }
            } catch (\Throwable $e) {
                $arr['api_error'] = $e->getMessage();
            }

            $data['mysqlrouter'][] = $arr;
        }

        $this->set('data', $data);
    }

    public function add()
    {
        $data = [
            'router' => [
                'display_name' => 'MySQL Router Admin',
                'hostname' => '',
                'port' => '8443',
                'login' => '',
                'password' => '',
                'is_ssl' => 1,
            ],
            'mysqlrouter_add_csrf_field' => Csrf::DEFAULT_FIELD,
            'mysqlrouter_add_csrf_token' => Csrf::issueToken($_SESSION, self::MYSQLROUTER_ADD_CSRF_SCOPE),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $outcome = self::evaluateAddRequest($_POST, $_SERVER, $_SESSION);
            if (!$outcome['allowed']) {
                $this->view = false;
                $this->layout_name = false;
                HttpResponse::sendOutcome($outcome, null);
                return;
            }

            $data['router'] = $outcome['router'];
            $param = self::buildInsertParamFromRouter($outcome['router']);
            $this->insertMysqlRouterAdmin($param);
            return;
        }

        $this->set('data', $data);
    }

    public static function evaluateAddRequest(array $post, array $server, array $session): array
    {
        $guard = CsrfGuard::check($post, $server, $session, self::MYSQLROUTER_ADD_CSRF_SCOPE);
        if (!$guard['allowed']) {
            return [
                'allowed' => false,
                'status' => $guard['status'],
                'body' => $guard['body'],
                'headers' => $guard['headers'],
                'router' => null,
            ];
        }

        $router = self::normalizeAddPayload($post);
        if ($router === null) {
            return [
                'allowed' => false,
                'status' => 400,
                'body' => 'Invalid MySQL Router add payload',
                'headers' => [],
                'router' => null,
            ];
        }

        return [
            'allowed' => true,
            'status' => 200,
            'body' => '',
            'headers' => [],
            'router' => $router,
        ];
    }

    public static function normalizeAddPayload(array $post): ?array
    {
        if (empty($post['mysqlrouter_server']) || !is_array($post['mysqlrouter_server'])) {
            return null;
        }

        $source = $post['mysqlrouter_server'];
        foreach (['display_name', 'hostname', 'port', 'login', 'password', 'is_ssl'] as $field) {
            if (array_key_exists($field, $source) && !is_scalar($source[$field])) {
                return null;
            }
        }

        $hostname = self::normalizeRouterHost($source['hostname'] ?? '');
        $port = self::normalizeRouterPort($source['port'] ?? null);
        $login = self::normalizeRouterText($source['login'] ?? '', self::MYSQLROUTER_ADD_TEXT_MAX_LENGTH, false);
        $password = self::normalizeRouterText(
            $source['password'] ?? '',
            self::MYSQLROUTER_ADD_PASSWORD_MAX_LENGTH,
            false
        );
        $displayName = self::normalizeRouterText(
            $source['display_name'] ?? 'MySQL Router Admin',
            self::MYSQLROUTER_ADD_TEXT_MAX_LENGTH,
            false
        );
        $isSsl = self::normalizeRouterSsl($source['is_ssl'] ?? '1');

        if (
            $hostname === null
            || $port === null
            || $login === null
            || $password === null
            || $displayName === null
            || $isSsl === null
        ) {
            return null;
        }

        return [
            'hostname' => $hostname,
            'port' => $port,
            'login' => $login,
            'password' => $password,
            'display_name' => $displayName,
            'is_ssl' => $isSsl,
        ];
    }

    public static function buildInsertParamFromRouter(array $router): array
    {
        return [
            $router['hostname'],
            $router['port'],
            $router['login'],
            $router['password'],
            $router['display_name'],
            $router['is_ssl'],
        ];
    }

    private static function normalizeRouterHost($value): ?string
    {
        $host = self::normalizeRouterText($value, self::MYSQLROUTER_ADD_TEXT_MAX_LENGTH, false);
        if (
            $host === null
            || preg_match('/[\\x00-\\x1F\\x7F\\s\\/\\\\?#@:]/', $host) === 1
            || strpos($host, '://') !== false
        ) {
            return null;
        }

        return $host;
    }

    private static function normalizeRouterText($value, int $maxLength, bool $allowEmpty): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);
        if ((!$allowEmpty && $text === '') || strlen($text) > $maxLength) {
            return null;
        }

        return $text;
    }

    private static function normalizeRouterPort($value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $port = trim((string) $value);
        if ($port === '' || !ctype_digit($port)) {
            return null;
        }

        $port = (int) $port;
        if ($port < 1 || $port > 65535) {
            return null;
        }

        return $port;
    }

    private static function normalizeRouterSsl($value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = strtolower(trim((string) $value));
        if (in_array($value, ['1', 'true', 'on'], true)) {
            return 1;
        }

        if (in_array($value, ['0', 'false', 'off'], true)) {
            return 0;
        }

        return null;
    }

    public function insertMysqlRouterAdmin($param)
    {
        $param[4] = $param[4] ?? 'MySQL Router Admin';
        $param[5] = $param[5] ?? 1;

        try {
            $this->testMysqlRouterAdmin($param);

            $table = [];
            $table['mysqlrouter_server']['display_name'] = $param[4];
            $table['mysqlrouter_server']['is_ssl'] = (int) $param[5];
            $table['mysqlrouter_server']['hostname'] = $param[0];
            $table['mysqlrouter_server']['port'] = (int) $param[1];
            $table['mysqlrouter_server']['login'] = $param[2];
            $table['mysqlrouter_server']['password'] = $param[3];
            $table['mysqlrouter_server']['date_inserted'] = date('Y-m-d H:i:s');

            $db = Sgbd::sql(DB_DEFAULT);
            $db->sql_save($table);

            Mysql::generateMySQLConfig();
            set_flash('success', __('Success'), __('The MySQL Router server has been added'));
        } catch (\Throwable $e) {
            set_flash('error', 'Error', $e->getMessage());
        } finally {
            if (!IS_CLI) {
                header('location: ' . LINK . 'MysqlRouter/index');
            }
        }
    }

    public function testMysqlRouterAdmin($param): bool
    {
        Debug::parseDebug($param);

        $payload = self::curl([
            $param[0],
            $param[5] ?? 1,
            $param[1],
            $param[2],
            $param[3],
            'swagger.json',
        ]);

        if (($payload['info']['title'] ?? '') !== 'MySQL Router') {
            throw new \Exception('[PMACONTROL-3003] Connected endpoint is not a MySQL Router admin API.');
        }

        return true;
    }

    public static function curl($param)
    {
        Debug::parseDebug($param);

        $host = $param[0] ?: '127.0.0.1';
        $protocol = ((string) ($param[1] ?? '1')) === '1' ? 'https' : 'http';
        $port = $param[2] ?: '8443';
        $user = $param[3] ?: 'user';
        $password = $param[4] ?: 'pass';
        $command = ltrim((string) ($param[5] ?? 'routes'), '/');

        $url = "{$protocol}://{$host}:{$port}/" . self::$version_api . "/{$command}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if ($protocol === 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        if ($response === false) {
            throw new \Exception("[PMACONTROL-2002] cURL error when contacting MySQL Router at {$url}: {$curlErr}");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \Exception("[PMACONTROL-3001] HTTP error {$httpCode} from MySQL Router at {$url}. Response: {$response}");
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("[PMACONTROL-3002] JSON decode error from MySQL Router at {$url}: " . json_last_error_msg());
        }

        return $data;
    }

    public static function extractRouteConfig(array $payload): array
    {
        if (isset($payload['bindAddress']) || isset($payload['bindPort'])) {
            return $payload;
        }

        foreach ($payload as $value) {
            if (!is_array($value)) {
                continue;
            }

            $found = self::extractRouteConfig($value);
            if (!empty($found)) {
                return $found;
            }
        }

        return [];
    }

    public static function fetchRouteDefinitions(array $config): array
    {
        $routesPayload = self::curl([
            $config['hostname'],
            $config['is_ssl'],
            $config['port'],
            $config['login'],
            $config['password'],
            'routes',
        ]);

        $routes = $routesPayload['items'] ?? [];
        $definitions = [];

        foreach ($routes as $route) {
            $routeName = $route['name'] ?? $route['id'] ?? null;

            if (empty($routeName)) {
                continue;
            }

            $configPayload = self::curl([
                $config['hostname'],
                $config['is_ssl'],
                $config['port'],
                $config['login'],
                $config['password'],
                'routes/' . $routeName . '/config',
            ]);

            $statusPayload = self::curl([
                $config['hostname'],
                $config['is_ssl'],
                $config['port'],
                $config['login'],
                $config['password'],
                'routes/' . $routeName . '/status',
            ]);

            $destinationsPayload = self::curl([
                $config['hostname'],
                $config['is_ssl'],
                $config['port'],
                $config['login'],
                $config['password'],
                'routes/' . $routeName . '/destinations',
            ]);

            $healthPayload = self::curl([
                $config['hostname'],
                $config['is_ssl'],
                $config['port'],
                $config['login'],
                $config['password'],
                'routes/' . $routeName . '/health',
            ]);

            $routeConfig = self::extractRouteConfig($configPayload);

            $definitions[] = [
                'route' => $routeName,
                'bind_address' => (string) ($routeConfig['bindAddress'] ?? ''),
                'bind_port' => (int) ($routeConfig['bindPort'] ?? 0),
                'destinations' => $routeConfig['destinations'] ?? null,
                'config' => $configPayload,
                'status' => $statusPayload,
                'destinations_payload' => $destinationsPayload,
                'health' => $healthPayload,
                'route_payload' => $route,
            ];
        }

        return $definitions;
    }

    public static function extractMetadataNames(array $metadataPayload): array
    {
        $metadataNames = [];
        foreach (($metadataPayload['items'] ?? []) as $metadata) {
            if (empty($metadata['name'])) {
                continue;
            }

            $metadataNames[] = (string) $metadata['name'];
        }

        $metadataNames = array_values(array_unique(array_filter($metadataNames)));
        sort($metadataNames);

        return $metadataNames;
    }

    public static function fetchMetadataNames(array $config): array
    {
        $metadataPayload = self::curl([
            $config['hostname'],
            $config['is_ssl'],
            $config['port'],
            $config['login'],
            $config['password'],
            'metadata',
        ]);

        return self::extractMetadataNames($metadataPayload);
    }

    public static function getMysqlServerMatches($param): array
    {
        Debug::parseDebug($param);

        $id_mysqlrouter_server = (int) ($param[0] ?? 0);
        if ($id_mysqlrouter_server <= 0) {
            throw new \Exception('[PMACONTROL-1003] getMysqlServerMatches() requires a valid id_mysqlrouter_server.');
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT `hostname`,`is_ssl`,`port`,`login`,`password` FROM mysqlrouter_server WHERE `id`=" . $id_mysqlrouter_server;
        $res = $db->sql_query($sql);
        $config = $db->sql_fetch_array($res, MYSQLI_ASSOC);

        if (empty($config)) {
            throw new \Exception("[PMACONTROL-2001] No MySQL Router server found with id '{$id_mysqlrouter_server}'.");
        }

        $routeDefinitions = self::fetchRouteDefinitions($config);

        $mysqlServers = [];
        $resMysqlServer = $db->sql_query("SELECT id, ip, port FROM mysql_server WHERE is_deleted = 0");
        while ($row = $db->sql_fetch_array($resMysqlServer, MYSQLI_ASSOC)) {
            $mysqlServers[] = $row;
        }

        $aliases = [];
        $resAlias = $db->sql_query("SELECT id_mysql_server, dns, port FROM alias_dns PARTITION (pn)");
        while ($row = $db->sql_fetch_array($resAlias, MYSQLI_ASSOC)) {
            $aliases[] = $row;
        }

        $tunnels = [];
        $resTunnel = $db->sql_query("SELECT id_mysql_server, remote_host, remote_port FROM ssh_tunnel WHERE date_end IS NULL AND id_mysql_server IS NOT NULL");
        while ($row = $db->sql_fetch_array($resTunnel, MYSQLI_ASSOC)) {
            $tunnels[] = $row;
        }

        $inventory = MaxScale::buildMysqlServerEndpointInventory($mysqlServers, $aliases, $tunnels);
        if (!isset(self::$mysqlrouter_mysql_server_links_cache[$id_mysqlrouter_server])) {
            self::$mysqlrouter_mysql_server_links_cache[$id_mysqlrouter_server] = [];

            $resScope = $db->sql_query(
                "SELECT id_mysql_server FROM mysqlrouter_server__mysql_server WHERE id_mysqlrouter_server = " . $id_mysqlrouter_server
            );
            while ($row = $db->sql_fetch_array($resScope, MYSQLI_ASSOC)) {
                self::$mysqlrouter_mysql_server_links_cache[$id_mysqlrouter_server][(int) $row['id_mysql_server']] = true;
            }
        }

        $existingLinks = &self::$mysqlrouter_mysql_server_links_cache[$id_mysqlrouter_server];
        $scopedMysqlServerIds = array_keys($existingLinks);

        $result = [
            'id_mysqlrouter_server' => $id_mysqlrouter_server,
            'matched_ids' => [],
            'listeners' => [],
            'routes' => $routeDefinitions,
        ];

        foreach ($routeDefinitions as $definition) {
            $resolution = MaxScale::resolveMysqlServerMatchesForListener(
                (string) ($definition['bind_address'] ?? ''),
                (int) ($definition['bind_port'] ?? 0),
                $inventory,
                $scopedMysqlServerIds
            );

            $matches = $resolution['matches'];
            $matchedIds = array_values(array_unique(array_map(static function (array $match): int {
                return (int) $match['id_mysql_server'];
            }, $matches)));

            $sources = [];
            foreach ($matches as $match) {
                $sources = array_merge($sources, $match['sources']);
            }

            foreach ($matchedIds as $id_mysql_server) {
                $id_mysql_server = (int) $id_mysql_server;
                $result['matched_ids'][] = $id_mysql_server;

                if (!isset($existingLinks[$id_mysql_server])) {
                    $db->sql_query(
                        "INSERT INTO mysqlrouter_server__mysql_server (id_mysqlrouter_server, id_mysql_server) VALUES ("
                        . $id_mysqlrouter_server . ", " . $id_mysql_server . ")"
                    );
                    $existingLinks[$id_mysql_server] = true;
                }

                $db->sql_query(
                    "UPDATE mysql_server SET is_proxy = 1 WHERE id = " . $id_mysql_server . " AND is_proxy != 1"
                );
            }

            $result['listeners'][] = [
                'route' => $definition['route'] ?? 'unknown',
                'endpoint' => MaxScale::normalizeEndpointHost((string) ($definition['bind_address'] ?? '')) . ':' . (int) ($definition['bind_port'] ?? 0),
                'matched_ids' => $matchedIds,
                'candidates' => $matches,
                'reason' => empty($matchedIds)
                    ? $resolution['reason']
                    : $resolution['reason'] . ' via ' . implode(', ', array_values(array_unique($sources))),
            ];
        }

        $result['matched_ids'] = array_values(array_unique(array_map('intval', $result['matched_ids'])));
        sort($result['matched_ids']);

        return $result;
    }
}
