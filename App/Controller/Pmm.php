<?php

namespace App\Controller;

use App\Library\PmmDashboardCatalog;
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

    private function renderDashboard(string $dashboard, array $param): void
    {
        $idMysqlServer = isset($param[0]) && ctype_digit((string) $param[0]) ? (int) $param[0] : 1;
        $rangeOptions = [
            'range' => $_GET['range'] ?? '24h',
            'range_mode' => $_GET['range_mode'] ?? 'preset',
            'start' => $_GET['start'] ?? null,
            'end' => $_GET['end'] ?? null,
        ];

        $payload = PmmDashboardCatalog::build($dashboard, $idMysqlServer, $rangeOptions);

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
}
