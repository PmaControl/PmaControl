<?php

namespace App\Controller;

use App\Library\PmmDashboardCatalog;
use App\Library\Security\ServerFilterWhere;
use Glial\Security\Crypt\Crypt;
use Glial\Sgbd\Sgbd;
use Glial\Synapse\Controller;

class Pmm extends Controller
{
    public function menu($param = [])
    {
        $this->set('param', $param);
    }

    public function index($param = [])
    {
        $this->renderDashboard('overview', $param);
    }

    public function system($param = [])
    {
        $this->renderDashboard('system', $param);
    }

    public function innodb($param = [])
    {
        $this->renderDashboard('innodb', $param);
    }

    public function binlog($param = [])
    {
        $this->renderDashboard('binlog', $param);
    }

    public function galera($param = [])
    {
        $this->renderDashboard('galera', $param);
    }

    public function performance_schema($param = [])
    {
        $this->renderDashboard('performance_schema', $param);
    }

    public function aria($param = [])
    {
        $this->renderDashboard('aria', $param);
    }

    public function myisam($param = [])
    {
        $this->renderDashboard('myisam', $param);
    }

    public function rocksdb($param = [])
    {
        $this->renderDashboard('rocksdb', $param);
    }

    public function proxysql($param = [])
    {
        $this->renderDashboard('proxysql', $param);
    }

    public function export($param = [])
    {
        $this->view = false;
        $this->layout = false;
        $this->layout_name = false;

        if (!defined('IS_CLI') || IS_CLI !== true) {
            header('HTTP/1.1 403 Forbidden');
            echo "This action is available in CLI mode only.\n";
            return;
        }

        try {
            $options = self::parseExportOptions(is_array($param) ? $param : [$param]);
        } catch (\InvalidArgumentException $exception) {
            echo "Error: ".$exception->getMessage()."\n\n";
            echo self::getExportUsage();
            return;
        }

        if ($options['help'] || empty($options['server_ids'])) {
            echo self::getExportUsage();
            return;
        }

        $db = Sgbd::sql(DB_DEFAULT);
        $serverIds = implode(',', array_map('intval', $options['server_ids']));

        $sql = "SELECT * FROM mysql_server WHERE id IN (".$serverIds.") ORDER BY display_name";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $password = null;
            if ($options['include_passwords']) {
                Crypt::$key = CRYPT_KEY;
                $password = Crypt::decrypt($ob->passwd);
            }

            echo self::formatPmmAdminMysqlCommand([
                'login' => $ob->login,
                'ip' => $ob->ip,
                'port' => $ob->port,
                'display_name' => $ob->display_name,
            ], $password)."\n\n";
        }
    }

    public static function parseExportOptions(array $param): array
    {
        $serverIds = [];
        $includePasswords = false;
        $confirmedSecretExport = false;
        $help = false;

        foreach ($param as $argument) {
            $argument = trim((string) $argument);
            if ($argument === '') {
                continue;
            }

            if ($argument === '--help' || $argument === '-h') {
                $help = true;
                continue;
            }

            if ($argument === '--include-passwords') {
                $includePasswords = true;
                continue;
            }

            if ($argument === '--confirm-secret-export') {
                $confirmedSecretExport = true;
                continue;
            }

            if (ctype_digit($argument)) {
                $serverIds[] = (int) $argument;
                continue;
            }

            if (preg_match('/^--(?:id|server)=(\d+)$/', $argument, $matches) === 1) {
                $serverIds[] = (int) $matches[1];
                continue;
            }

            if (preg_match('/^--(?:ids|servers)=([0-9,]+)$/', $argument, $matches) === 1) {
                foreach (explode(',', $matches[1]) as $serverId) {
                    if ($serverId !== '') {
                        $serverIds[] = (int) $serverId;
                    }
                }
                continue;
            }

            throw new \InvalidArgumentException('Unknown option: '.$argument);
        }

        $serverIds = array_values(array_unique(array_filter($serverIds, static function ($serverId) {
            return $serverId > 0;
        })));

        if ($includePasswords && !$confirmedSecretExport) {
            throw new \InvalidArgumentException('Password export requires --confirm-secret-export');
        }

        if ($includePasswords && count($serverIds) !== 1) {
            throw new \InvalidArgumentException('Password export requires exactly one selected server');
        }

        return [
            'server_ids' => $serverIds,
            'include_passwords' => $includePasswords,
            'help' => $help,
        ];
    }

    public static function getExportUsage(): string
    {
        return "Usage: ./glial Pmm export <id_mysql_server> [--include-passwords --confirm-secret-export]\n"
            ."       ./glial Pmm export --servers=1,2\n\n"
            ."By default passwords are redacted. Decrypted password export is limited to one selected server.\n";
    }

    public static function formatPmmAdminMysqlCommand(array $server, ?string $password = null): string
    {
        $password = $password ?? '<redacted>';

        return "pmm-admin add mysql \\\n"
            ."  --username=".escapeshellarg((string) ($server['login'] ?? ''))." \\\n"
            ."  --password=".escapeshellarg($password)." \\\n"
            ."  --host=".escapeshellarg((string) ($server['ip'] ?? ''))." \\\n"
            ."  --port=".escapeshellarg((string) ($server['port'] ?? ''))." \\\n"
            ."  --service-name=".escapeshellarg((string) ($server['display_name'] ?? ''));
    }

    private static function resolveDashboardServerId(array $param, ?callable $fallbackResolver = null): ?int
    {
        $routeId = self::normalizePositiveInteger($param[0] ?? null);
        if ($routeId !== null) {
            return $routeId;
        }

        $fallbackResolver = $fallbackResolver ?? [self::class, 'getDefaultDashboardServerId'];
        $fallbackId = $fallbackResolver();

        return self::normalizePositiveInteger($fallbackId);
    }

    private static function getDefaultDashboardServerSql(string $filterWhere = ''): string
    {
        return "SELECT a.id
            FROM mysql_server a
            WHERE a.is_deleted = 0
              AND a.is_monitored = 1"
            . $filterWhere . "
            ORDER BY a.display_name, a.id
            LIMIT 1";
    }

    private function renderDashboard(string $dashboard, array $param): void
    {
        $idMysqlServer = self::resolveDashboardServerId($param);
        $rangeOptions = [
            'range' => $_GET['range'] ?? '24h',
            'range_mode' => $_GET['range_mode'] ?? 'preset',
            'start' => $_GET['start'] ?? null,
            'end' => $_GET['end'] ?? null,
        ];

        $payload = $idMysqlServer === null
            ? self::buildEmptyDashboardPayload($dashboard, $rangeOptions)
            : PmmDashboardCatalog::build($dashboard, $idMysqlServer, $rangeOptions);

        $this->di['js']->addJavascript([
            'chart-4.5.1.umd.min.js?v=' . (@filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'chart-4.5.1.umd.min.js') ?: time()),
            'Server/engineMemory.js?v=' . (@filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'Server' . DS . 'engineMemory.js') ?: time()),
            'Pmm/dashboard.js?v=' . (@filemtime(APP_DIR . DS . 'Webroot' . DS . 'js' . DS . 'Pmm' . DS . 'dashboard.js') ?: time()),
        ]);

        $this->title = 'PMM / ' . ($payload['dashboard']['title'] ?? ucfirst($dashboard));
        $this->ariane = ' > PMM > ' . $this->title;

        $this->set('param', $param);
        $this->set('payload', $payload);
    }

    private static function normalizePositiveInteger($value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (!is_string($value) || trim($value) === '' || ctype_digit(trim($value)) === false) {
            return null;
        }

        $id = (int) trim($value);

        return $id > 0 ? $id : null;
    }

    private static function getDefaultDashboardServerId(): ?int
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $get = $_GET;
        $session = $_SESSION ?? [];
        $filterWhere = ServerFilterWhere::build($get, $session, [], 'a');
        $res = $db->sql_query(self::getDefaultDashboardServerSql($filterWhere));
        $row = $db->sql_fetch_object($res);

        return is_object($row) && isset($row->id) ? self::normalizePositiveInteger($row->id) : null;
    }

    private static function buildEmptyDashboardPayload(string $dashboard, array $rangeOptions): array
    {
        $dashboards = PmmDashboardCatalog::getDashboards();
        if (!isset($dashboards[$dashboard])) {
            throw new \InvalidArgumentException('Unknown PMM dashboard: ' . $dashboard);
        }

        return [
            'dashboard' => $dashboards[$dashboard],
            'server' => [
                'id' => 0,
                'display_name' => 'No server selected',
            ],
            'range' => PmmDashboardCatalog::normalizeRange($rangeOptions),
            'summary_cards' => [],
            'sections' => [
                [
                    'title' => 'No monitored MySQL server available',
                    'description' => 'Add or enable a monitored MySQL server before opening PMM dashboards from the main menu.',
                    'cards' => [],
                    'charts' => [],
                    'tables' => [],
                    'notes' => [],
                ],
            ],
        ];
    }
}
