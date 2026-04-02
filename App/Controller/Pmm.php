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

    public function export()
    {
        $this->view = false;

        $db = SGBD::sql(DB_DEFAULT);

        $sql = "SELECT * FROM mysql_server ORDER BY display_name";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            Crypt::$key = CRYPT_KEY;
            $password = Crypt::decrypt($ob->passwd);

            echo "pmm-admin add mysql \\\n";
            echo "  --username={$ob->login} \\\n";
            echo "  --password='{$password}' \\\n";
            echo "  --host={$ob->ip} \\\n";
            echo "  --port={$ob->port} \\\n";
            echo "  --service-name={$ob->display_name}\n\n";
        }
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
